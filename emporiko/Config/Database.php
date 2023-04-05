<?php

namespace Config;

use CodeIgniter\Database\Config;
use EMPORIKO\Helpers\Strings as Str;

/**
 * Database Configuration
 */
class Database extends Config
{
	/**
	 * The directory that holds the Migrations
	 * and Seeds directories.
	 *
	 * @var string
	 */
	public $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

	/**
	 * Lets you choose which connection group to
	 * use if no other is specified.
	 *
	 * @var string
	 */
	public $defaultGroup = 'default';
        

	/**
	 * The default database connection.
	 *
	 * @var array
	 */
	
	public $default = [
		'DSN'      => '',
		'hostname' => 'mysql.server.com',
		'username' => 'mysql_user',
		'password' => 'mysql_pass',
		'database' => 'mysql_db',
		'DBDriver' => 'MySQLi',
		'DBPrefix' => '',
		'pConnect' => false,
		'DBDebug'  => (ENVIRONMENT !== 'production'),
		'charset'  => 'utf8',
		'DBCollat' => 'utf8_general_ci',
		'swapPre'  => '',
		'encrypt'  => false,
		'compress' => false,
		'strictOn' => false,
		'failover' => [],
		'port'     => 3306,
	];
	

}
