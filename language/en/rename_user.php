<?php
/**
*
* @package phpBB Extension - GPC Rename User
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'RENAME_USER'			=> 'User rename',
	'TAKEOVER_USER'			=> 'Takeover User Account',
	'ERROR'					=> 'Error',
	'NOTICE'				=> 'Notice',
	'CURRENT_USERNAME'		=> 'Current Username',
	'NEW_USERNAME'			=> 'New Username',
	'OLD_USERNAME'			=> 'Old Username',
	'USE_FORM'				=> 'You must fill the whole form.',
	'USER_DOES_NOT_EXIST'	=> 'The User <i>%s</i> does not exist.',
	'USER_ALREADY_EXISTS'	=> 'The User <i>%s</i> exist already.',
	'USER_HAS_TOO_MANY_POSTS' => 'The User <i>%s</i> has more than %s posts: %s in total',
	'SHOULD_NOT_HAPPEN' 	=> 'This should not happen... Please contact admin!',
	'SUCCESS_RENAME'		=> 'The User was successfully renamed.',
	'SUCCESS_RENAME_HEAD'	=> 'Renaming successful',
	'WHITELIST_POINTS_CANNOT_BE_MERGED' => 'The Whitelistpoints could not be taken over. (old Username: <i>%s</i>, new Username: <i>%s</i>)',
	'SUCCESS_TAKEOVER'		=> 'The (negtive and neutral) Whitelist-Points have been successfully taken over.',
	'SUCCESS_TAKEOVER_HEAD'	=> 'Takeover successful',
	'USER_IS_BANNED'		=> 'The User <i>%s</i> is banned.',
	'USER_IS_NOT_BANNED'	=> 'The User <i>%s</i> is <u>not</u> banned, yet. Please ban him now!',
	'RENAMINGS'				=> 'Renamings',
	'DATE'					=> 'Date',
	'NEW_ACCOUNT'			=> 'New Account',
	'YES'					=> 'Yes',
	'NO'					=> 'No',
	'BAN_OLD_ACCOUNT_AUTOMATICALLY'		=> 'Ban old account automatically, if not banned already?',
	'CANNOT_BAN_OLD_USER'	=> 'The old account (<i>%s</i>) could not be banned. Please inform the administrator and do the ban by hand.',
	'OLD_ACCOUNT_HAS_BEEN_BANNED'	=> 'The old account (<i>%s</i>) was banned automatically.',
	'USER_HAS_BEEN_RENAMED_TO'	=> 'The user has been renamed. The new account is: <i>%s</i>',
	'YOU_HAVE_BEEN_RENAMED_TO'	=> 'You have been renamed. Your new account is: <i>%s</i>',
));
