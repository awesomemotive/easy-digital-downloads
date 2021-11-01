<?php
/**
 * Notifications Database
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 * @since     2.11.4
 */

namespace EDD\Database;

use EDD\Models\Notification;
use EDD\Utils\EnvironmentChecker;
use EDD\Utils\NotificationImporter;

class NotificationsDB extends \EDD_DB {

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'edd_notifications';
		$this->primary_key = 'id';
		$this->version     = '0.2'; // @todo update to 1.0 pre-release

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
	 * @since 2.11.4
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
			'conditions'   => '%s',
			'start'        => '%s',
			'end'          => '%s',
			'dismissed'    => '%d',
			'date_created' => '%s',
			'date_updated' => '%s',
		);
	}

	/**
	 * Let MySQL handle most of the defaults.
	 * We just set the dates here to ensure they get saved in UTC.
	 *
	 * @since 2.11.4
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
	 * JSON-encodes any relevant columns.
	 *
	 * @since 2.11.4
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected function maybeJsonEncode( $data ) {
		$jsonColumns = array( 'buttons', 'conditions' );

		foreach ( $jsonColumns as $column ) {
			if ( ! empty( $data[ $column ] ) && is_array( $data[ $column ] ) ) {
				$data[ $column ] = json_encode( $data[ $column ] );
			}
		}

		return $data;
	}

	/**
	 * Inserts a new notification.
	 *
	 * @since 2.11.4
	 *
	 * @param array  $data
	 * @param string $type
	 *
	 * @return int
	 */
	public function insert( $data, $type = 'notification' ) {
		$result = parent::insert( $this->maybeJsonEncode( $data ), $type );

		wp_cache_delete( 'edd_active_notification_count', 'edd_notifications' );

		return $result;
	}

	/**
	 * Updates an existing notification.
	 *
	 * @since 2.11.4
	 *
	 * @param int    $row_id
	 * @param array  $data
	 * @param string $where
	 *
	 * @return bool
	 */
	public function update( $row_id, $data = array(), $where = '' ) {
		return parent::update( $row_id, $this->maybeJsonEncode( $data ), $where );
	}

	/**
	 * Returns all notifications that have not been dismissed and should be
	 * displayed on this site.
	 *
	 * @since 2.11.4
	 *
	 * @param bool $conditionsOnly If set to true, then only the `conditions` column is retrieved
	 *                             for each notification.
	 *
	 * @return Notification[]
	 */
	public function getActiveNotifications( $conditionsOnly = false ) {
		global $wpdb;

		$environmentChecker = new EnvironmentChecker();
		$notifications      = $wpdb->get_results( $this->getActiveQuery( $conditionsOnly ) );

		$models = array();
		if ( is_array( $notifications ) ) {
			foreach ( $notifications as $notification ) {
				$model = new Notification( (array) $notification );

				try {
					// Only add to the array if all conditions are met or if the notification has no conditions.
					if (
						! $model->conditions ||
						( is_array( $model->conditions ) && $environmentChecker->meetsConditions( $model->conditions ) )
					) {
						$models[] = $model;
					}
				} catch ( \Exception $e ) {

				}
			}
		}

		unset( $notifications );

		return $models;
	}

	/**
	 * Builds the query for selecting or counting active notifications.
	 *
	 * @since 2.11.4
	 *
	 * @param bool $conditionsOnly
	 *
	 * @return string
	 */
	private function getActiveQuery( $conditionsOnly = false ) {
		global $wpdb;

		$select = $conditionsOnly ? 'conditions' : '*';

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
	 * Note: We can't actually do a real `COUNT(*)` on the database, because we want
	 * to double-check the conditions are met before displaying. That's why we use
	 * `getActiveNotifications()` which runs the conditions through the EnvironmentChecker.
	 *
	 * @since 2.11.4
	 *
	 * @return int
	 */
	public function countActiveNotifications() {
		$numberActive = wp_cache_get( 'edd_active_notification_count', 'edd_notifications' );
		if ( false === $numberActive ) {
			$numberActive = count( $this->getActiveNotifications( true ) );

			wp_cache_set( 'edd_active_notification_count', $numberActive, 'edd_notifications' );
		}

		return $numberActive;
	}

	/**
	 * Creates the table.
	 *
	 * @since 2.11.4
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
	    conditions longtext DEFAULT NULL,
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
