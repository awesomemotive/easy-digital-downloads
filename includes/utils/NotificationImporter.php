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
	 * @since 2.11.4
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
			'type'       => $notification->notification_type,
			'conditions' => $notification->type,
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
		if ( empty( $notification->btns ) || ! is_array( $notification->btns ) ) {
			return null;
		}

		$buttons = array();

		foreach ( $notification->btns as $buttonType => $buttonInfo ) {
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
	 */
	protected function insertNewNotification( $notification ) {
		EDD()->notifications->insert( $this->getNotificationData( $notification ) );
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

}
