<?php
/**
 * NotificationImporter.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 * @since     2.11.4
 */

namespace EDD\Utils;

class NotificationImporter {

	/**
	 * @var EnvironmentChecker
	 */
	protected $environmentChecker;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->environmentChecker = new EnvironmentChecker();
	}

	/**
	 * Fetches notifications from the API and imports them locally.
	 *
	 * @since 2.11.4
	 */
	public function run() {
		$request_timeout = get_option( 'edd_notification_req_timeout', false );
		if ( false !== $request_timeout && current_time( 'timestamp' ) < $request_timeout ) {
			edd_debug_log( 'Skipping notifications API request, timeout not reached' );
			return;
		}

		edd_debug_log( 'Fetching notifications via ' . $this->getApiEndpoint() );

		try {
			$notifications = $this->fetchNotifications();

			// If successful, make it so we don't request for another 23 hours.
			update_option( 'edd_notification_req_timeout', current_time( 'timestamp' ) + ( HOUR_IN_SECONDS * 23 ), false );
		} catch ( \Exception $e ) {
			edd_debug_log( sprintf( 'Notification fetch exception: %s', $e->getMessage() ) );

			// If for some reason our request failed, delay for 4 hours.
			update_option( 'edd_notification_req_timeout', current_time( 'timestamp' ) + ( HOUR_IN_SECONDS * 4 ), false );
			return;
		}

		foreach ( $notifications as $notification ) {
			$notificationId = isset( $notification->id ) ? $notification->id : 'unknown';

			edd_debug_log( sprintf( 'Processing notification ID %s', $notificationId ) );

			try {
				$this->validateNotification( $notification );

				$existingId = $this->get_column_by( 'id', 'remote_id', $notification->id );
				if ( $existingId ) {
					edd_debug_log( '-- Updating existing notification.' );

					$this->updateExistingNotification( $existingId, $notification );
				} else {
					edd_debug_log( '-- Inserting new notification.' );

					$this->insertNewNotification( $notification );
				}
			} catch ( \Exception $e ) {
				edd_debug_log( sprintf( '-- Notification processing failure for ID %s: %s', $notificationId, $e->getMessage() ) );
			}
		}
	}

	/**
	 * Returns the API endpoint to query.
	 *
	 * @since 2.11.4
	 *
	 * @return string
	 */
	protected function getApiEndpoint() {
		if ( defined( 'EDD_NOTIFICATIONS_API_URL' ) ) {
			return EDD_NOTIFICATIONS_API_URL;
		}

		return 'https://plugin.easydigitaldownloads.com/wp-content/notifications.json';
	}

	/**
	 * Retrieves notifications from the remote API endpoint.
	 *
	 * @since 2.11.4
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function fetchNotifications() {
		$response = wp_remote_get( $this->getApiEndpoint() );

		if ( is_wp_error( $response ) ) {
			throw new \Exception( $response->get_error_message() );
		}

		$notifications = wp_remote_retrieve_body( $response );

		return ! empty( $notifications ) ? json_decode( $notifications ) : array();
	}

	/**
	 * Validates the notification from the remote API to make sure we actually
	 * want to save it.
	 *
	 * @since 2.11.4
	 *
	 * @param object $notification
	 *
	 * @throws \Exception
	 */
	public function validateNotification( $notification ) {
		// Make sure we have all the required data.
		$requiredProperties = array(
			'id',
			'title',
			'content',
		);

		$missing = array_diff( $requiredProperties, array_keys( get_object_vars( $notification ) ) );
		if ( $missing ) {
			throw new \Exception( sprintf( 'Missing required properties: %s', json_encode( array_values( $missing ) ) ) );
		}

		// Don't save the notification if it has expired.
		if ( ! empty( $notification->end ) && time() > strtotime( $notification->end ) ) {
			throw new \Exception( 'Notification has expired.' );
		}

		// Ignore if notification was created before EDD was installed.
		if ( ! empty( $notification->start ) && edd_get_activation_date() > strtotime( $notification->start ) ) {
			throw new \Exception( 'Notification created prior to EDD activation.' );
		}

		if (
			! empty( $notification->type ) &&
			is_array( $notification->type ) &&
			! $this->environmentChecker->meetsConditions( $notification->type )
		) {
			throw new \Exception( 'Condition(s) not met.' );
		}
	}

	/**
	 * Retrieves the array of notification data to insert into the database.
	 * Use in both inserts and updates.
	 *
	 * @since 2.11.4
	 *
	 * @param object $notification
	 *
	 * @return array
	 */
	protected function getNotificationData( $notification ) {
		return array(
			'remote_id'  => $notification->id,
			'title'      => $notification->title,
			'content'    => $notification->content,
			'buttons'    => $this->parseButtons( $notification ),
			'type'       => ! empty( $notification->notification_type ) ? $notification->notification_type : 'success',
			'conditions' => ! empty( $notification->type ) ? $notification->type : null,
			'start'      => ! empty( $notification->start ) ? $notification->start : null,
			'end'        => ! empty( $notification->end ) ? $notification->end : null,
		);
	}

	/**
	 * Parses and formats buttons from the remote notification object.
	 *
	 * @since 2.11.4
	 *
	 * @param object $notification
	 *
	 * @return array|null
	 */
	protected function parseButtons( $notification ) {
		if ( empty( $notification->btns ) ) {
			return null;
		}

		$buttons = array();

		foreach ( (array) $notification->btns as $buttonType => $buttonInfo ) {
			if ( empty( $buttonInfo->url ) || empty( $buttonInfo->text ) ) {
				continue;
			}

			$buttons[] = array(
				'type' => ( 'main' === $buttonType ) ? 'primary' : 'secondary',
				'url'  => $buttonInfo->url,
				'text' => $buttonInfo->text,
			);
		}

		return ! empty( $buttons ) ? $buttons : null;
	}

	/**
	 * Inserts a new notification into the database.
	 *
	 * @since 2.11.4
	 *
	 * @param object $notification
	 * @throws \Exception
	 */
	protected function insertNewNotification( $notification ) {
		$result = EDD()->notifications->insert( $this->getNotificationData( $notification ) );

		if ( ! $result ) {
			throw new \Exception( 'Failed to insert into database.' );
		}
	}

	/**
	 * Updates an existing notification.
	 *
	 * @since 2.11.4
	 *
	 * @param int    $existingId
	 * @param object $notification
	 */
	protected function updateExistingNotification( $existingId, $notification ) {
		EDD()->notifications->update( $existingId, wp_parse_args( $this->getNotificationData( $notification ), array(
			'date_updated' => gmdate( 'Y-m-d H:i:s' ),
		) ) );
	}

	/**
	 * Retrieve a specific column's value by the the specified column / value
	 *
	 * @since   3.1.1
	 * @return  string
	 */
	private function get_column_by( $column, $column_where, $column_value ) {
		global $wpdb;
		$column_where = esc_sql( $column_where );
		$column       = esc_sql( $column );
		return $wpdb->get_var( $wpdb->prepare( "SELECT $column FROM {$wpdb->edd_notifications} WHERE $column_where = %s LIMIT 1;", $column_value ) );
	}
}
