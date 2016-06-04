<?php
/**
*
* @package phpBB Extension - GPC Rename User
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace gpc\rename_user\migrations;

use gpc\rename_user\constants;

class release_1_0_0 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}

	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . constants::RENAME_USERS_TABLE => array(
					'COLUMNS'	=> array(
						'id'	=> array('UINT', null, 'auto_increment'),
						'user_id_old'	=> array('UINT', null),
						'user_id_new'	=> array('UINT', null),
						'username_old'	=> array('VCHAR:255', ''),
						'username_new'	=> array('VCHAR:255', ''),
						'date_time'	=> array('TIMESTAMP', null),
					),
					'PRIMARY_KEY'	=> 'id',
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . constants::RENAME_USERS_TABLE
			),
		);
	}
}
