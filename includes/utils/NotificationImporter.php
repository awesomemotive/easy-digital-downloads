<?php
/**
 * NotificationImporter.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\Utils;

class NotificationImporter {

	/**
	 * Fetches notifications from the API and imports them locally.
	 */
	public function run() {
		try {
			$notifications = $this->fetchNotifications();
		} catch ( \Exception $e ) {
			edd_debug_log( sprintf( 'Notification fetch exception: %s', $e->getMessage() ) );

			return;
		}

		foreach ( $notifications as $notification ) {
			try {
				$this->validateNotification( $notification );

				$existingId = EDD()->notifications->get_column_by( 'id', 'remote_id', $notification->id );
				if ( $existingId ) {
					$this->updateExistingNotification( $existingId, $notification );
				} else {
					$this->insertNewNotification( $notification );
				}
			} catch ( \Exception $e ) {
				edd_debug_log( sprintf( 'Notification processing failure: %s', $e->getMessage() ) );
			}
		}
	}

	/**
	 * Returns the API endpoint to query.
	 *
	 * @return string
	 */
	protected function getApiEndpoint() {
		if ( defined( 'EDD_NOTIFICATIONS_API_URL' ) ) {
			return EDD_NOTIFICATIONS_API_URL;
		}

		return ''; // @todo
	}

	/**
	 * Retrieves notifications from the remote API endpoint.
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
			'notification_type',
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
		$dateActivated = date( 'Y-m-d H:i:s', strtotime( '-1 week' ) ); // @todo real value
		if ( ! empty( $notification->start ) && $dateActivated > strtotime( $notification->start ) ) {
			throw new \Exception( 'Notification created prior to EDD activation.' );
		}
	}

	/**
	 * Retrieves the array of notification data to insert into the database.
	 * Use in both inserts and updates.
	 *
	 * @param object $notification
	 *
	 * @return array
	 */
	protected function getNotificationData( $notification ) {
		return array(
			'remote_id' => $notification->id,
			'title'     => $notification->title,
			'content'   => $notification->content,
			'type'      => $notification->notification_type,
			'start'     => ! empty( $notification->start ) ? $notification->start : null,
			'end'       => ! empty( $notification->end ) ? $notification->end : null,
		);
	}

	/**
	 * Inserts a new notification into the database.
	 *
	 * @param object $notification
	 */
	protected function insertNewNotification( $notification ) {
		EDD()->notifications->insert( $this->getNotificationData( $notification ), 'notification' );
	}

	/**
	 * Updates an existing notification.
	 *
	 * @param int    $existingId
	 * @param object $notification
	 */
	protected function updateExistingNotification( $existingId, $notification ) {
		EDD()->notifications->update( $existingId, wp_parse_args( $this->getNotificationData( $notification ), array(
			'date_updated' => gmdate( 'Y-m-d H:i:s' ),
		) ) );
	}

}
