<?php

namespace EDD\Database\Schemas;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Database\Schema;

/**
 * Class LogsEmails
 *
 * @since 3.3.0
 * @package EDD\Database\Schemas
 */
class LogsEmails extends Schema {

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

		// object_id.
		array(
			'name'       => 'object_id',
			'type'       => 'bigint',
			'length'     => 20,
			'unsigned'   => true,
			'sortable'   => true,
			'allow_null' => false,
		),

		// object type.
		array(
			'name'       => 'object_type',
			'type'       => 'varchar',
			'length'     => 20,
			'allow_null' => false,
			'default'    => 'order',
		),

		// email.
		array(
			'name'       => 'email',
			'type'       => 'varchar',
			'length'     => 100,
			'default'    => '',
			'searchable' => true,
			'sortable'   => true,
		),

		// email_id.
		array(
			'name'       => 'email_id',
			'type'       => 'varchar',
			'length'     => 32,
			'unsigned'   => true,
			'sortable'   => true,
			'allow_null' => false,
		),

		// subject.
		array(
			'name'       => 'subject',
			'type'       => 'varchar',
			'length'     => 200,
			'default'    => '',
			'sortable'   => true,
			'searchable' => true,
			'allow_null' => false,
		),

		// date_created.
		array(
			'name'       => 'date_created',
			'type'       => 'datetime',
			'default'    => '',
			'created'    => true,
			'date_query' => true,
			'sortable'   => true,
		),

		// date_modified.
		array(
			'name'       => 'date_modified',
			'type'       => 'datetime',
			'default'    => '',
			'modified'   => true,
			'date_query' => true,
			'sortable'   => true,
		),

		// uuid.
		array(
			'uuid' => true,
		),
	);
}
