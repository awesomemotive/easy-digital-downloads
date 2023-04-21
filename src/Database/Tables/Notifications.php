<?php
/**
 * Notifications Table.
 *
 * @package     EDD
 * @subpackage  Database\Tables
 * @copyright   Copyright (c) 2023, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.1.1
 */
namespace EDD\Database\Tables;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Database\Table;

/**
 * Setup the notifications database table.
 *
 * @since 3.1.1
 */
final class Notifications extends Table {

	/**
	 * Table name.
	 *
	 * @access protected
	 * @since 3.1.1
	 * @var string
	 */
	protected $name = 'notifications';

	/**
	 * Database version.
	 *
	 * @access protected
	 * @since 3.1.1
	 * @var int
	 */
	protected $version = 202303220;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.1.1
	 *
	 * @var array
	 */
	protected $upgrades = array(
		'202303220' => 202303220,
	);

	/**
	 * Setup the database schema.
	 *
	 * @access protected
	 * @since 3.1.1
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	    remote_id varchar(20) DEFAULT NULL,
	    source varchar(20) NOT NULL DEFAULT 'api',
	    title text NOT NULL,
	    content longtext NOT NULL,
	    buttons longtext DEFAULT NULL,
	    type varchar(64) NOT NULL DEFAULT 'success',
	    conditions longtext DEFAULT NULL,
	    start datetime DEFAULT NULL,
	    end datetime DEFAULT NULL,
	    dismissed tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
	    date_created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	    date_updated datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	    PRIMARY KEY (id),
	    KEY dismissed_start_end (dismissed, start, end),
		KEY remote_id (remote_id)";
	}

	/**
	 * Deletes the original database version option.
	 *
	 * @since 3.1.1
	 * @return bool
	 */
	protected function __202301251() {
		return delete_option( "{$this->table_name}_db_version" );
	}

	/**
	 * Upgrade to version 202302131
	 * - Add the `source` text column and modify the remote_id column.
	 *
	 * @since 3.1.1
	 *
	 * @return boolean
	 */
	protected function __202302131() {

		$updates = array(
			'add-source' => false,
			'remote-id'  => false,
		);

		if ( false === $this->column_exists( 'source' ) ) {
			$source = $this->get_db()->query(
				"ALTER TABLE {$this->table_name} ADD COLUMN `source` varchar(20) NOT NULL DEFAULT 'api' AFTER `remote_id`;"
			);

			if ( $this->is_success( $source ) ) {
				$updates['add-source'] = $this->get_db()->query( "UPDATE {$this->table_name} SET `source` = 'api'" );
			}
		} else {
			$updates['add-source'] = true;
		}

		$remote_id_column = $this->get_db()->get_row( "SHOW FIELDS FROM {$this->table_name} WHERE Field = 'remote_id'" );
		if ( 'varchar(20)' !== $remote_id_column->Type ) {
			$updates['remote-id'] = $this->get_db()->query(
				"ALTER TABLE {$this->table_name} MODIFY COLUMN `remote_id` varchar(20) DEFAULT NULL;"
			);
		} else {
			$updates['remote-id'] = true;
		}

		foreach ( $updates as $query_key => $result ) {
			if ( ! $this->is_success( $result ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Runs another database upgrade for sites which got into a bit of a snarl with the database versions.
	 *
	 * @since 3.1.1.3
	 * @return bool
	 */
	protected function __202303220() {
		$this->__202301251();

		return $this->__202302131();
	}
}
