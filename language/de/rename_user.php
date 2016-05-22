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
	'RENAME_USER'			=> 'User umbenennen',
	'TAKEOVER_USER'			=> 'User Account übertragen',
	'ERROR'					=> 'Fehler',
	'NOTICE'				=> 'Hinweis',
	'CURRENT_USERNAME'		=> 'Aktueller Username',
	'NEW_USERNAME'			=> 'Neuer Username',
	'OLD_USERNAME'			=> 'Alter Username',
	'USE_FORM'				=> 'Du musst das gesamte Formular ausfüllen.',
	'USER_DOES_NOT_EXIST'	=> 'Der User <i>%s</i> existiert nicht.',
	'USER_ALREADY_EXISTS'	=> 'Der User <i>%s</i> existiert bereits.',
	'USER_HAS_TOO_MANY_POSTS' => 'Der User <i>%s</i> hat schon mehr als %s Beiträge: %s Stück',
	'SHOULD_NOT_HAPPEN' 	=> 'This should not happen... Please contact admin!',
	'SUCCESS_RENAME'		=> 'Der User wurde erfolgreich umbenannt.',
	'SUCCESS_RENAME_HEAD'	=> 'Erfolgreich umbenannt',
	'WHITELIST_POINTS_CANNOT_BE_MERGED' => 'Die Whitelistpunkte konnten nicht übertragen werden. (alter Username: <i>%s</i>, neuer Username: <i>%s</i>)',
	'SUCCESS_TAKEOVER'		=> 'Die (negtiven und neutralen) Whitelist Punkte des Users wurden erfolgreich übertragen.',
	'SUCCESS_TAKEOVER_HEAD'	=> 'Punkte erfolgreich übertragen',
	'USER_IS_BANNED'		=> 'Der Benutzer <i>%s</i> ist gesperrt.',
	'USER_IS_NOT_BANNED'	=> 'Der Benutzer <i>%s</i> ist noch <u>nicht</u> gesperrt. Bitte sperre ihn jetzt falls er nicht automatisch gesperrt wird!',
	'RENAMINGS'				=> 'Umbenennungen',
	'DATE'					=> 'Datum',
	'NEW_ACCOUNT'			=> 'Neuer Account',
	'YES'					=> 'Ja',
	'NO'					=> 'Nein',
	'BAN_OLD_ACCOUNT_AUTOMATICALLY'		=> 'Alten Account automatisch sperren, falls er noch nicht gebannt ist?',
	'CANNOT_BAN_OLD_USER'	=> 'Der alte Account (<i>%s</i>) konnte nicht gesperrt werden. Bitte melde es dem Administrator und sperre den User per Hand.',
	'OLD_ACCOUNT_HAS_BEEN_BANNED'	=> 'Der alte Account (<i>%s</i>) wurde automatisch gesperrt.',
	'USER_HAS_BEEN_RENAMED_TO'	=> 'Der User wurde umbenannt. Der neue Account ist: <i>%s</i>',
	'YOU_HAVE_BEEN_RENAMED_TO'	=> 'Du wurdest umbenannt. Dein neuer Account ist: <i>%s</i>',
));
