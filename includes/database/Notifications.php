<?php
/**
 * Notifications.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\Database;

use EDD\Models\Notification;
use EDD\Utils\NotificationImporter;

class Notifications extends \EDD_DB {

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'edd_notifications';
		$this->primary_key = 'id';
		$this->version     = '0.1'; // @todo update to 1.0 pre-release

		add_action( 'edd_daily_scheduled_events', static function () {
			$importer = new NotificationImporter();
			$importer->run();
		} );

		$db_version = get_option( "{$this->table_name}_db_version" );
		if ( version_compare( $db_version, $this->version, '>=' ) ) {
			return;
		}
		$this->create_table();
	}

	/**
	 * Columns and their formats.
	 *
	 * @return string[]
	 */
	public function get_columns() {
		return array(
			'id'           => '%d',
			'remote_id'    => '%d',
			'title'        => '%s',
			'content'      => '%s',
			'buttons'      => '%s',
			'type'         => '%s',
			'start'        => '%s',
			'end'          => '%s',
			'dismissed'    => '%d',
			'date_created' => '%s',
			'date_updated' => '%s',
		);
	}

	/**
	 * Let MySQL handle the defaults.
	 *
	 * @return array
	 */
	public function get_column_defaults() {
		return array(
			'date_created' => gmdate( 'Y-m-d H:i:s' ),
			'date_updated' => gmdate( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Inserts a new notification.
	 *
	 * @param array  $data
	 * @param string $type
	 *
	 * @return int
	 */
	public function insert( $data, $type = 'notification' ) {
		if ( ! empty( $data['buttons'] ) && is_array( $data['buttons'] ) ) {
			$data['buttons'] = json_encode( $data['buttons'] );
		}

		$result = parent::insert( $data, $type );

		wp_cache_delete( 'edd_active_notification_count', 'edd_notifications' );

		return $result;
	}

	/**
	 * Updates an existing notification.
	 *
	 * @param int    $row_id
	 * @param array  $data
	 * @param string $where
	 *
	 * @return bool
	 */
	public function update( $row_id, $data = array(), $where = '' ) {
		if ( ! empty( $data['buttons'] ) && is_array( $data['buttons'] ) ) {
			$data['buttons'] = json_encode( $data['buttons'] );
		}

		return parent::update( $row_id, $data, $where );
	}

	/**
	 * Returns all notifications that have not been dismissed.
	 *
	 * @return Notification[]
	 */
	public function getActiveNotifications() {
		global $wpdb;

		$notifications = $wpdb->get_results( $this->getActiveQuery() );

		$models = array();
		if ( is_array( $notifications ) ) {
			foreach ( $notifications as $notification ) {
				$models[] = new Notification( (array) $notification );
			}
		}

		unset( $notifications );

		return $models;
	}

	/**
	 * Builds the query for selecting or counting active notifications.
	 *
	 * @param bool $count
	 *
	 * @return string
	 */
	private function getActiveQuery( $count = false ) {
		global $wpdb;

		$select = $count ? 'COUNT(*)' : '*';

		return $wpdb->prepare(
			"SELECT {$select} FROM {$this->table_name}
			WHERE dismissed = 0
			AND (start <= %s OR start IS NULL)
			AND (end >= %s OR end IS NULL)
			ORDER BY start DESC, id DESC",
			gmdate( 'Y-m-d H:i:s' ),
			gmdate( 'Y-m-d H:i:s' )
		);
	}

	/**
	 * Counts the number of active notifications.
	 *
	 * @return int
	 */
	public function countActiveNotifications() {
		global $wpdb;

		$numberActive = wp_cache_get( 'edd_active_notification_count', 'edd_notifications' );
		if ( false === $numberActive ) {
			$numberActive = (int) $wpdb->get_var( $this->getActiveQuery( true ) );

			wp_cache_set( 'edd_active_notification_count', $numberActive, 'edd_notifications' );
		}

		return $numberActive;
	}

	/**
	 * Creates the table.
	 */
	public function create_table() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( "CREATE TABLE {$this->table_name} (
	    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	    remote_id bigint(20) UNSIGNED DEFAULT NULL,
	    title text NOT NULL,
	    content longtext NOT NULL,
	    buttons longtext DEFAULT NULL,
	    type varchar(64) NOT NULL,
	    start datetime DEFAULT NULL,
	    end datetime DEFAULT NULL,
	    dismissed tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
	    date_created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	    date_updated datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	    PRIMARY KEY (id),
	    KEY dismissed_start_end (dismissed, start, end)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;" );

		update_option( $this->table_name . '_db_version', $this->version );
	}

}
