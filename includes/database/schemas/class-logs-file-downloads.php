<?php
/**
 * File Download Logs Schema Class.
 *
 * @package     EDD
 * @subpackage  Database\Schemas
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */
namespace EDD\Database\Schemas;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * File Download Logs Schema Class.
 *
 * @since 3.0.0
 */
class Logs_File_Downloads extends Base {

	/**
	 * Array of database column objects
	 *
	 * @since 3.0.0
	 * @access public
	 * @var array
	 */
	public $columns = array(

		// id
		array(
			'name'       => 'id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'extra'      => 'auto_increment',
			'primary'    => true,
			'sortable'   => true
		),

		// download_id
		array(
			'name'       => 'download_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0'
		),

		// file_id
		array(
			'name'       => 'file_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0'
		),


		// payment_id
		array(
			'name'       => 'payment_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0'
		),

		// price_id
		array(
			'name'       => 'price_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0'
		),

		// user_id
		array(
			'name'       => 'user_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0'
		),

		// email
		array(
			'name'       => 'email',
			'type'       => 'varchar',
			'length'     => '100',
			'searchable' => true,
			'sortable'   => true
		),

		// ip
		array(
			'name'       => 'ip',
			'type'       => 'varchar',
			'length'     => '60',
			'default'    => '',
			'sortable'   => true
		),

		// date_created
		array(
			'name'       => 'date_created',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'date_query' => true,
			'sortable'   => true
		),

		// date_modified
		array(
			'name'       => 'date_modified',
			'type'       => 'datetime',
			'default'    => 'CURRENT_TIMESTAMP',
			'extra'      => 'ON UPDATE CURRENT_TIMESTAMP',
			'date_query' => true,
			'sortable'   => true
		)
	);
}
