<?php
/**
 * Notifications API Endpoint
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 * @since     2.11.4
 */

namespace EDD\API\v3;

use EDD\Models\Notification;

class Notifications extends Endpoint {

	/**
	 * Registers the endpoints.
	 *
	 * @since 2.11.4
	 */
	public function register() {
		register_rest_route(
			self::$namespace,
			'notifications',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'listNotifications' ),
					'permission_callback' => array( $this, 'canViewNotification' ),
				)
			)
		);

		register_rest_route(
			self::$namespace,
			'notifications/(?P<id>\d+)',
			array(
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'dismissNotification' ),
					'permission_callback' => array( $this, 'canViewNotification' ),
					'args'                => array(
						'id' => array(
							'description'       => __( 'ID of the notification.', 'easy-digital-downloads' ),
							'type'              => 'integer',
							'required'          => true,
							'validate_callback' => function ( $param, $request, $key ) {
								$notification = EDD()->notifications->get( intval( $param ) );

								return ! empty( $notification );
							},
							'sanitize_callback' => function ( $param, $request, $key ) {
								return intval( $param );
							}
						)
					),
				)
			)
		);
	}

	/**
	 * Whether the current user can view (and dismiss) notifications.
	 *
	 * @since 2.11.4
	 *
	 * @return bool
	 */
	public function canViewNotification() {
		return current_user_can( 'manage_shop_settings' );
	}

	/**
	 * Returns a list of notifications.
	 *
	 * @since 2.11.4
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function listNotifications( \WP_REST_Request $request ) {
		$active = array_map( function ( Notification $notification ) {
			return $notification->toArray();
		}, EDD()->notifications->getActiveNotifications() );

		// @todo At a later date we may want to receive dismissed notifications too.
		/*$dismissed = array_map( function ( Notification $notification ) {
			return $notification->toArray();
		}, EDD()->notifications->getDismissedNotifications() );*/

		return new \WP_REST_Response( array(
			'active'    => $active,
			//'dismissed' => $dismissed,
		) );
	}

	/**
	 * Dismisses a single notification.
	 *
	 * @since 2.11.4
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function dismissNotification( \WP_REST_Request $request ) {
		$result = EDD()->notifications->update(
			$request->get_param( 'id' ),
			array( 'dismissed' => 1 )
		);

		if ( ! $result ) {
			return new \WP_REST_Response( array(
				'error' => __( 'Failed to dismiss notification.', 'easy-digital-downloads' ),
			), 500 );
		}

		wp_cache_delete( 'edd_active_notification_count', 'edd_notifications' );

		return new \WP_REST_Response( null, 204 );
	}
}
