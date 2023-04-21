<?php
/**
 * Notifications Schema Class.
 *
 * @package     EDD
 * @subpackage  Database\Schemas
 * @copyright   Copyright (c) 2023, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.1.1
 */
namespace EDD\Database\Schemas;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Database\Schema;

/**
 * Notifications Schema Class.
 *
 * @since 3.1.1
 */
class Notifications extends Schema {

	/**
	 * Array of database column objects
	 *
	 * @since 3.1.1
	 * @access public
	 * @var array
	 */
	public $columns = array(

		// id
		array(
			'name'     => 'id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'extra'    => 'auto_increment',
			'primary'  => true,
			'sortable' => true,
		),

		// remote_id
		array(
			'name'    => 'remote_id',
			'type'    => 'varchar',
			'length'  => '20',
			'default' => null,
		),

		// source
		array(
			'name'       => 'source',
			'type'       => 'varchar',
			'default'    => 'api',
			'allow_null' => false,
		),

		// title
		array(
			'name'       => 'title',
			'type'       => 'text',
			'default'    => '',
			'allow_null' => false,
		),

		// content
		array(
			'name'       => 'content',
			'type'       => 'longtext',
			'default'    => '',
			'allow_null' => false,
		),

		// buttons
		array(
			'name'       => 'buttons',
			'type'       => 'longtext',
			'default'    => null,
			'allow_null' => true,
		),

		// type
		array(
			'name'       => 'type',
			'type'       => 'varchar',
			'length'     => '64',
			'allow_null' => true,
		),

		// conditions
		array(
			'name'       => 'conditions',
			'type'       => 'longtext',
			'default'    => null,
			'allow_null' => true,
		),

		// start
		array(
			'name'       => 'start',
			'type'       => 'datetime',
			'default'    => null,
			'date_query' => true,
			'sortable'   => true,
			'allow_null' => true,
		),

		// end
		array(
			'name'       => 'end',
			'type'       => 'datetime',
			'default'    => null,
			'date_query' => true,
			'sortable'   => true,
			'allow_null' => true,
		),

		// dismissed
		array(
			'name'       => 'dismissed',
			'type'       => 'tinyint',
			'length'     => '1',
			'unsigned'   => true,
			'allow_null' => true,
			'default'    => 0,
		),

		// date_created
		array(
			'name'       => 'date_created',
			'type'       => 'datetime',
			'default'    => '', // Defaults to current time in query class
			'date_query' => true,
			'sortable'   => true,
			'created'    => true,
		),

		// date_updated
		array(
			'name'       => 'date_updated',
			'type'       => 'datetime',
			'default'    => '', // Defaults to current time in query class
			'date_query' => true,
			'sortable'   => true,
			'modified'   => true,
		),
	);
}
