<?php
/**
 *
 * @package phpBB Extension - GPC Rename User
 * @copyright (c) 2014 Robet Heim
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace gpc\rename_user\controller;

use gpc\rename_user\constants;

class main
{
	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\auth\auth */
	protected $auth;

	protected $request;

	protected $db;

	protected $phpEx;

	protected $phpbb_root_path;

	protected $table_prefix;

	protected $pagination;

	/* @var \phpbb\cache\service */
	protected $cache;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config				$config
	 * @param \phpbb\controller\helper			$helper
	 * @param \phpbb\template\template			$template
	 * @param \phpbb\user						$user
	 * @param \phpbb\auth\auth					$auth
	 * @param \phpbb\request\request			$request
	 * @param \phpbb\db\driver\driver_interface	$db
	 * @param string							$phpEx
	 * @param string							$root_path	phpBB root path
	 * @param string							$table_prefix
	 * @param \phpbb\pagination					$pagination
	 * @param \phpbb\cache\service				$cache
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, \phpbb\auth\auth $auth, \phpbb\request\request $request, \phpbb\db\driver\driver_interface $db, $php_ext, $root_path, $table_prefix, $pagination, \phpbb\cache\service $cache)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
		$this->auth = $auth;
		$this->request = $request;
		$this->db = $db;
		$this->phpEx = $php_ext;
		$this->phpbb_root_path = $root_path;
		$this->table_prefix = $table_prefix;
		$this->pagination = $pagination;
		$this->cache = $cache;
	}

	/**
	 * Demo controller for route /rename_user
	 *
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function show()
	{
		$this->user->add_lang_ext('gpc/rename_user', 'rename_user');
		// user permissions: only admins may see the rename-options
		if ( $this->auth->acl_gets('a_'))
		{
			$this->template->assign_var('S_ADMIN', true);
		}
		$mode = $this->request->variable('mode', '');
		if ($mode == 'rename')
		{
			$this->rename();
		}
		if ($mode == 'takeover')
		{
			$this->takeover();
		}

		// display rename-table

		// get total_renamings
		$sql = "SELECT COUNT(r.id) AS total_renamings
				FROM `". $this->table_prefix . constants::RENAME_USERS_TABLE ."` r;";
		$result = $this->db->sql_query($sql);
		$total_renamings = (int) $this->db->sql_fetchfield('total_renamings');
		$this->db->sql_freeresult($result);

		$limit = $this->config['topics_per_page'];
		$start = $this->request->variable('start', 0);

		$base_url = $this->helper->route('gpc_rename_user_controller');
		$pagination = $this->pagination;
		$pagination->generate_template_pagination($base_url, 'PAGINATION', 'start', $total_renamings, $limit, $start);
		$this->template->assign_vars(array(
			'ON_PAGE'	=> $pagination->on_page($total_renamings, $limit, $start),
		));

		// get concrete renamings for page
		$sql = 'SELECT r.*
				FROM `'. $this->table_prefix . constants::RENAME_USERS_TABLE ."` r
				ORDER BY r.date_time DESC
				LIMIT $start, $limit";
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$new_account = $row['user_id_old'] != $row['user_id_new'];

			$this->template->assign_block_vars('rename_table', array(
				'OLD_USERNAME'	=> $new_account ? '<a href="'.append_sid("{$this->phpbb_root_path}memberlist.{$this->phpEx}?mode=viewprofile&amp;u=".$row['user_id_old']).'">'.$row['username_old'].'</a>' : $row['username_old'],
				'NEW_USERNAME'	=> '<a href="'.append_sid("{$this->phpbb_root_path}memberlist.{$this->phpEx}?mode=viewprofile&amp;u=".$row['user_id_new']).'">'.$row['username_new'].'</a>',
				'DATE'			=> $this->user->format_date($row['date_time']),
				'NEW_ACCOUNT'	=> $this->user->lang(($new_account ? 'YES' : 'NO')),
			));
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars(array(
			'RENAME_ACTION' => $this->helper->route('gpc_rename_user_controller', array('mode' => 'rename')),
			'TAKEOVER_ACTION' => $this->helper->route('gpc_rename_user_controller', array('mode' => 'takeover')),
			'U_FIND_USERNAME' => $this->helper->route('gpc_rename_user_controller', array('mode' => 'searchuser')),
		));

		return $this->helper->render('rename_user.html', $this->user->lang('RENAME_USER'));
	}

	private function takeover()
	{
		// takeover the old account to the new ons
		$takeover_success = false;

		$old_username = utf8_normalize_nfc($this->request->variable('old_username', '', true));
		$new_username = utf8_normalize_nfc($this->request->variable('new_username', '', true));

		$this->template->assign_vars(array(
			'OLD_USERNAME' => $old_username,
			'NEW_USERNAME' => $new_username,
		));

		if (empty($old_username) || empty($new_username))
		{
			$this->template->assign_var('ERROR_MSG', $this->user->lang('USE_FORM'));
		}
		else
		{
			// both accounts must exist
			if ( ! ($old_user = $this->getUserByUsername($old_username)) )
			{
				$this->template->assign_var('ERROR_MSG', $this->user->lang('USER_DOES_NOT_EXIST', $old_username));
			}
			elseif ( ! ($new_user = $this->getUserByUsername($new_username)) )
			{
				$this->template->assign_var('ERROR_MSG', $this->user->lang('USER_DOES_NOT_EXIST', $new_username));
			}
			else
			{
				// both accounts exist

				// Notice if old account is banned or not
				if ($old_account_is_banned = $this->userIsBanned($old_user['user_id']))
				{
					$this->template->assign_var('NOTICE_MSG', $this->user->lang('USER_IS_BANNED', $old_user['username']));
				}
				else
				{
					$notice_msg = $this->user->lang('USER_IS_NOT_BANNED', $old_user['username']);
					$ban_old_account_automatically = $this->request->variable('ban_old_account_automatically', false);
					// ban old account
					// boolean   user_ban  (string $mode, mixed $ban, int $ban_len, string $ban_len_other, boolean $ban_exclude, string $ban_reason, [ $ban_give_reason  = ''])
					// ban_len == 0 is a permanent ban.
					// ban_reason = reason showed in mcp/acp
					// ban_give_reason = reason showed to user
					$this->template->assign_var('NOTICE_MSG', $this->user->lang('USER_IS_NOT_BANNED', $old_user['username']));
					if ($ban_old_account_automatically)
					{
						require_once($this->phpbb_root_path ."includes/functions_user." . $this->phpEx);
						if (user_ban('user', $old_user['username'], 0, '', false, $this->user->lang('USER_HAS_BEEN_RENAMED_TO', $new_user['username']), $this->user->lang('YOU_HAVE_BEEN_RENAMED_TO', $new_user['username'])))
						{
							$notice_msg = $this->user->lang('OLD_ACCOUNT_HAS_BEEN_BANNED', $old_user['username']);
						}
						else
						{
							$this->template->assign_var('ERROR_MSG', $this->user->lang('CANNOT_BAN_OLD_USER', $old_user['username']));
						}
					}
					$this->template->assign_var('NOTICE_MSG', $notice_msg);
				}

				// take over the Blacklist-points ( = negative & neutral ratings but not the positiv ratings.
				$sql = 'UPDATE ' . USERS_TABLE . "
						SET whitelist_neutral = '".(((int) $new_user['whitelist_neutral']) + ((int) $old_user['whitelist_neutral']))."',
							whitelist_negativ = '".(((int) $new_user['whitelist_negativ']) + ((int) $old_user['whitelist_negativ']))."'
						WHERE user_id = ". ((int) $new_user['user_id']) .';';
				$result = $this->db->sql_query($sql);

				if ( ! $result)
				{
					$this->template->assign_var('ERROR_MSG', $this->user->lang('WHITELIST_POINTS_CANNOT_BE_MERGED', $old_user['username'], $new_user['username']));
				}
				else
				{
					$takeover_success = true;
				}
				$this->db->sql_freeresult($result);

				if ($takeover_success)
				{
					// insert renaming in RENAME_USERS_TABLE
					$result = $this->db->sql_query("INSERT INTO `". $this->table_prefix . constants::RENAME_USERS_TABLE . "`
														(user_id_old,
														user_id_new,
														username_old,
														username_new,
														date_time )
													VALUES ('". ((int) $old_user['user_id']) ."',
														'". ((int) $new_user['user_id']) ."',
														'". $this->db->sql_escape($old_user['username']) ."',
														'". $this->db->sql_escape($new_user['username']) ."',
														'".time()."');");
					if ( ! $result)
					{
						$this->template->assign_var('ERROR_MSG', $this->user->lang['SHOULD_NOT_HAPPEN'].' (code: 2)');
					}
					else
					{
						$this->template->assign_var('SUCCESS_TAKEOVER', $this->user->lang('SUCCESS_TAKEOVER'));
						// no need for an email here
					}
					$this->db->sql_freeresult($result);
				}
			}
		}
	}

	private function rename()
	{
		// what's the true for? => multibyte
		$current_username = utf8_normalize_nfc($this->request->variable('current_username', '', true));
		$new_username = utf8_normalize_nfc($this->request->variable('new_username', '', true));

		$this->template->assign_vars(array(
			'CURRENT_USERNAME' => $current_username,
			'NEW_USERNAME' => $new_username,
		));

		if (empty($current_username) || empty($new_username))
		{
			$this->template->assign_var('ERROR_MSG', $this->user->lang('USE_FORM'));
		}
		else
		{
			require_once($this->phpbb_root_path ."includes/functions_user." . $this->phpEx);
			$username_invalid = validate_username($current_username);

			if ($username_invalid != 'USERNAME_TAKEN' && $username_invalid != false)
			{
				$this->template->assign_var('ERROR_MSG', $username_invalid.' '.$this->user->lang($username_invalid));
			}
			else
			{

				if ( ! ($current_user = $this->getUserByUsername($current_username)) )
				{
					$this->template->assign_var('ERROR_MSG', $this->user->lang('USER_DOES_NOT_EXIST', $current_username));

				}
				elseif ( ($new_user = $this->getUserByUsername($new_username)) )
				{
					$this->template->assign_var('ERROR_MSG', $this->user->lang('USER_ALREADY_EXISTS', $new_user['username']));
				}
				else
				{
					$current_user_post_count = (int) $current_user['user_posts'];
					if ($current_user_post_count > constants::MAX_POSTS)
					{
						$this->template->assign_var('ERROR_MSG', $this->user->lang('USER_HAS_TOO_MANY_POSTS', $current_user['username'], constants::MAX_POSTS, $current_user_post_count));
					}
					else
					{
						$new_username_clean = utf8_clean_string($new_username);
						// rename user in all tables except USERS_TABLE
						user_update_name($current_user['username'], $new_username);

						// update users_table
						$sql = 'UPDATE ' . USERS_TABLE . '
						   SET
								username = "' . $this->db->sql_escape($new_username) . '",
								username_clean = "' . $this->db->sql_escape($new_username_clean) . '"
						   WHERE user_id = ' . $current_user['user_id'];
						$this->db->sql_query($sql);
						$this->cache->destroy('sql');
						// insert renaming in RENAME_TABLE
						$result = $this->db->sql_query("INSERT INTO `". $this->table_prefix . constants::RENAME_USERS_TABLE . "` (user_id_old, user_id_new,
																					username_old, username_new,
																					date_time )
													VALUES ('". ((int) $current_user['user_id']) ."',
														'". ((int) $current_user['user_id']) ."',
														'". $this->db->sql_escape($current_user['username']) ."',
														'". $this->db->sql_escape($new_username) ."',
													'".time()."');");
						if ( ! $result)
						{
							$this->template->assign_var('ERROR_MSG', $this->user->lang('SHOULD_NOT_HAPPEN').' (code: 1)');
						}
						else
						{
							$this->template->assign_var('SUCCESS_RENAME', $this->user->lang('SUCCESS_RENAME'));

							// Load the messenger
							if (!class_exists('messenger'))
							{
								include($this->phpbb_root_path . 'includes/functions_messenger.' . $this->phpEx);
							}
							// Write email
							$messenger = new \messenger(false);
							$messenger->template('@gpc_rename_user/rename_email', $current_user['user_lang']);
							$messenger->to($current_user['user_email'], $current_user['username']);
							$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
							$messenger->headers('X-AntiAbuse: User_id - ' . $this->user->data['user_id']);
							$messenger->headers('X-AntiAbuse: Username - ' . $this->user->data['username']);
							$messenger->headers('X-AntiAbuse: User IP - ' . $this->user->ip);
							$messenger->assign_vars(array(
								'NEW_USERNAME'		=> $new_username,
								'ADMIN_USERNAME'	=> $this->user->data['username'])
							);
							$messenger->send(NOTIFY_EMAIL);
						}
						$this->db->sql_freeresult($result);
					}
				}
			}
		}
	}

	private function getUserByUsername($username)
	{
		$result = $this->db->sql_query("SELECT *
								FROM " .USERS_TABLE. "
								WHERE UCASE(username_clean) = UCASE('". $this->db->sql_escape(utf8_clean_string($username)) ."')
								LIMIT 1; ");
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		return $row;
	}

	/**
	 * Checks if a user is banned.
	 *
	 * @param int $user_id the id of the user
	 * @return boolean
	 */
	private function userIsBanned($user_id)
	{
		if ($user_id === false)
		{
			throw new Exception('Illegal Parameter');
		}
		//function check_ban($user_id = false, $user_ips = false, $user_email = false, $return = false)
		return $this->user->check_ban($user_id, false, false, true);
	}
}
