<?php
/**
 * Promo Handler
 *
 * Handles logic for displaying and dismissing promotional notices.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.10.6
 */

namespace EDD\Admin\Promos;

use EDD\EventManagement\SubscriberInterface;
use EDD\Admin\Promos\Notices\Notice;
use Sandhills\Utils\Persistent_Dismissible;

class PromoHandler implements SubscriberInterface {

	/**
	 * Registered notices.
	 *
	 * @var string[]
	 */
	protected $notices = array(
		'\\EDD\\Admin\\Promos\\Notices\\License_Upgrade_Notice',
		'\\EDD\\Admin\\Promos\\Notices\\Five_Star_Review_Dashboard',
		'\\EDD\\Admin\\Promos\\Notices\\Five_Star_Review_Settings',
		'\\EDD\\Admin\\Promos\\Notices\\Lite',
		'\\EDD\\Admin\\Onboarding\\Notice',
	);

	/**
	 * Notices constructor.
	 */
	public function __construct() {
		$this->load_notices();
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'wp_ajax_edd_dismiss_promo_notice' => 'dismiss_notice',
		);
	}

	/**
	 * Loads and displays all registered promotional notices.
	 *
	 * @since 2.10.6
	 */
	private function load_notices() {
		foreach ( $this->get_notices() as $notice_class_name ) {
			if ( ! class_exists( $notice_class_name ) ) {
				$file_name = strtolower( str_replace( '_', '-', basename( str_replace( '\\', '/', $notice_class_name ) ) ) );
				$file_path = EDD_PLUGIN_DIR . 'includes/admin/promos/notices/class-' . $file_name . '.php';

				if ( file_exists( $file_path ) ) {
					require_once $file_path;
				}
			}

			if ( ! class_exists( $notice_class_name ) ) {
				continue;
			}

			add_action( $notice_class_name::DISPLAY_HOOK, function () use ( $notice_class_name ) {
				/** @var Notice $notice */
				$notice = new $notice_class_name();
				if ( $notice->should_display() ) {
					$notice->display();
				}
			}, $notice_class_name::DISPLAY_PRIORITY );
		}
	}

	/**
	 * Gets the notices.
	 * Implemented as a method so that extending classes can access.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	protected function get_notices() {
		return $this->notices;
	}

	/**
	 * Determines whether or not a notice has been dismissed.
	 *
	 * @since 2.10.6
	 *
	 * @param string $id ID of the notice to check.
	 *
	 * @return bool
	 */
	public static function is_dismissed( $id ) {
		$is_dismissed = (bool) Persistent_Dismissible::get( array(
			'id' => 'edd-' . $id
		) );

		return true === $is_dismissed;
	}

	/**
	 * Dismisses a notice.
	 *
	 * @since 2.10.6
	 *
	 * @param string $id               ID of the notice to dismiss.
	 * @param int    $dismissal_length Number of seconds to dismiss the notice for, or `0` for forever.
	 */
	public static function dismiss( $id, $dismissal_length = 0 ) {
		Persistent_Dismissible::set( array(
			'id'   => 'edd-' . $id,
			'life' => $dismissal_length
		) );
	}

	/**
	 * AJAX callback for dismissing a notice.
	 *
	 * @since 2.10.6
	 */
	public function dismiss_notice() {
		$notice_id = ! empty( $_POST['notice_id'] ) ? sanitize_text_field( $_POST['notice_id'] ) : false;
		if ( empty( $notice_id ) ) {
			wp_send_json_error( __( 'Missing notice ID.', 'easy-digital-downloads' ), 400 );
		}

		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'edd-dismiss-notice-' . sanitize_key( $_POST['notice_id'] ) ) ) {
			wp_send_json_error( __( 'You do not have permission to perform this action.', 'easy-digital-downloads' ), 403 );
		}

		$notice_class_name = $this->get_notice_class_name( $notice_id );

		// No matching notice class was found.
		if ( ! $notice_class_name ) {
			wp_send_json_error( __( 'You do not have permission to perform this action.', 'easy-digital-downloads' ), 403 );
		}

		// Check whether the current user can dismiss the notice.
		if ( ! defined( $notice_class_name . '::CAPABILITY' ) || ! current_user_can( $notice_class_name::CAPABILITY ) ) {
			wp_send_json_error( __( 'You do not have permission to perform this action.', 'easy-digital-downloads' ), 403 );
		}

		$dismissal_length = ! empty( $_POST['lifespan'] ) ? absint( $_POST['lifespan'] ) : 0;

		self::dismiss( sanitize_key( $_POST['notice_id'] ), $dismissal_length );

		wp_send_json_success();
	}

	/**
	 * Gets the notice class name for a given notice ID.
	 *
	 * @since 2.11.4
	 * @param string $notice_id The notice ID to match.
	 * @return bool|string The class name or false if no matching class was found.
	 */
	private function get_notice_class_name( $notice_id ) {
		$notice_class_name = false;
		// Look through the registered notice classes for the one being dismissed.
		foreach ( $this->notices as $notice_class_to_check ) {
			if ( ! class_exists( $notice_class_to_check ) ) {
				$file_name = strtolower( str_replace( '_', '-', basename( str_replace( '\\', '/', $notice_class_to_check ) ) ) );
				$file_path = EDD_PLUGIN_DIR . 'includes/admin/promos/notices/class-' . $file_name . '.php';

				if ( file_exists( $file_path ) ) {
					require_once $file_path;
				}
			}

			if ( ! class_exists( $notice_class_to_check ) ) {
				continue;
			}
			$notice = new $notice_class_to_check();
			if ( $notice->get_id() === $notice_id ) {
				$notice_class_name = $notice_class_to_check;
				break;
			}
		}

		return $notice_class_name;
	}
}
