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

class NotificationsDB {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'edd_daily_scheduled_events', array( $this, 'schedule_daily_notification_checks' ) );
	}

	/**
	 * Add a cron event to check for new notifications.
	 *
	 * @since 2.11.4
	 */
	public static function schedule_daily_notification_checks() {
		$importer = new NotificationImporter();
		$importer->run();
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
	 * Adds or updates a local notification.
	 *
	 * @param array $data
	 * @return false|int Returns false if the notification could not be added/updated; the ID of the notification if it could.
	 */
	public function maybe_add_local_notification( $data = array() ) {

		// A remote_id is required and it cannot be numeric for local notifications.
		if ( empty( $data['remote_id'] ) || is_numeric( $data['remote_id'] ) ) {
			return false;
		}
		// The source is always always local.
		$data['source'] = 'local';

		$existing = $this->get_item_by( 'remote_id', $data['remote_id'] );
		if ( $existing ) {
			return $this->update(
				$existing->id,
				$data
			);
		}

		return $this->insert( $data );
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

		$data          = $this->maybeJsonEncode( $data );
		$notifications = new \EDD\Database\Queries\Notification();

		$result = $notifications->add_item( $data );

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
		$notifications = new \EDD\Database\Queries\Notification();

		return $notifications->update_item( $row_id, $this->maybeJsonEncode( $data ) );
	}

	/**
	 * Gets a notification by ID.
	 *
	 * @param int $id
	 * @return false|Notification
	 */
	public function get( $id ) {
		$notifications = new \EDD\Database\Queries\Notification();

		return $notifications->get_item( $id );
	}

	/**
	 * Gets an item by the column name and value.
	 *
	 * @param string $column_name
	 * @param string $column_value
	 * @return false|Notification
	 */
	public function get_item_by( $column_name = '', $column_value = '' ) {
		$notifications = new \EDD\Database\Queries\Notification();

		return $notifications->get_item_by( $column_name, $column_value );
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
			"SELECT {$select} FROM {$wpdb->edd_notifications}
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
}
