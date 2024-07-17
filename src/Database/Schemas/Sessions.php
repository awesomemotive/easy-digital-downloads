<?php
/**
 * Sessions Schema Class.
 *
 * @package     EDD
 * @subpackage  Database\Schemas
 * @copyright   Copyright (c) 2023, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.0
 */

namespace EDD\Database\Schemas;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Database\Schema;

/**
 * Sessions Schema Class.
 *
 * @since 3.3.0
 */
class Sessions extends Schema {

	/**
	 * Array of database column objects
	 *
	 * @since 3.3.0
	 * @access public
	 * @var array
	 */
	public $columns = array(

		// session_id.
		array(
			'name'           => 'session_id',
			'type'           => 'bigint',
			'length'         => 20,
			'unsigned'       => true,
			'auto_increment' => true,
			'primary_key'    => true,
		),

		// session_key.
		array(
			'name'       => 'session_key',
			'type'       => 'varchar',
			'length'     => 64,
			'allow_null' => false,
			'unique'     => true,
			'cache_key'  => true,
		),

		// session_value.
		array(
			'name'       => 'session_value',
			'type'       => 'longtext',
			'allow_null' => false,
		),

		// session_expiry.
		array(
			'name'       => 'session_expiry',
			'type'       => 'bigint',
			'length'     => 20,
			'unsigned'   => true,
			'allow_null' => false,
			'compare'    => true,
		),
	);
}
