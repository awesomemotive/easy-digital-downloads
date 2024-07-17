<?php
/**
 * Emails Schema Class.
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
 * Emails Schema Class.
 *
 * @since 3.3.0
 */
class Emails extends Schema {

	/**
	 * The database columns.
	 *
	 * @since 3.3.0
	 * @var array
	 */
	public $columns = array(

		// id.
		array(
			'name'     => 'id',
			'type'     => 'bigint',
			'length'   => 20,
			'unsigned' => true,
			'extra'    => 'auto_increment',
			'primary'  => true,
			'sortable' => true,
		),

		// email_id.
		array(
			'name'       => 'email_id',
			'type'       => 'varchar',
			'length'     => 32,
			'allow_null' => false,
			// 'uuid'       => true,
		),

		// context.
		array(
			'name'       => 'context',
			'type'       => 'varchar',
			'length'     => 32,
			'allow_null' => false,
			'default'    => 'order',
		),

		// sender.
		array(
			'name'       => 'sender',
			'type'       => 'varchar',
			'length'     => 32,
			'allow_null' => false,
			'default'    => 'edd',
		),

		// recipient.
		array(
			'name'       => 'recipient',
			'type'       => 'varchar',
			'length'     => 32,
			'allow_null' => false,
			'default'    => 'customer',
		),

		// subject.
		array(
			'name'       => 'subject',
			'type'       => 'text',
			'allow_null' => false,
		),

		// heading.
		array(
			'name'       => 'heading',
			'type'       => 'text',
			'allow_null' => true,
			'default'    => null,
		),

		// content.
		array(
			'name'       => 'content',
			'type'       => 'longtext',
			'allow_null' => false,
		),

		// status.
		array(
			'name'       => 'status',
			'type'       => 'tinyint',
			'length'     => 1,
			'unsigned'   => true,
			'allow_null' => false,
			'default'    => 0,
		),

		// date_created.
		array(
			'name'       => 'date_created',
			'type'       => 'datetime',
			'allow_null' => false,
			'default'    => '',
			'created'    => true,
		),

		// date_modified.
		array(
			'name'       => 'date_modified',
			'type'       => 'datetime',
			'allow_null' => false,
			'default'    => '',
			'modified'   => true,
		),
	);
}
