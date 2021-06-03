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

use EDD\Admin\Promos\Notices\Notice;
use Sandhills\Utils\Persistent_Dismissible;

class PromoHandler {

	/**
	 * Registered notices.
	 *
	 * @var string[]
	 */
	private $notices = array(
		'\\EDD\\Admin\\Promos\\Notices\\License_Upgrade_Notice'
	);

	/**
	 * Notices constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_edd_dismiss_promo_notice', array( $this, 'dismiss_notice' ) );

		$this->load_notices();
	}

	/**
	 * Loads and displays all registered promotional notices.
	 *
	 * @since 2.10.5
	 */
	private function load_notices() {
		require_once EDD_PLUGIN_DIR . 'includes/admin/promos/notices/abstract-notice.php';

		foreach ( $this->notices as $notice ) {
			if ( ! class_exists( $notice ) ) {
				$file_name = strtolower( str_replace( '_', '-', basename( str_replace( '\\', '/', $notice ) ) ) );
				$file_path = EDD_PLUGIN_DIR . 'includes/admin/promos/notices/class-' . $file_name . '.php';

				if ( file_exists( $file_path ) ) {
					require_once $file_path;
				}
			}

			if ( ! class_exists( $notice ) ) {
				continue;
			}

			add_action( $notice::DISPLAY_HOOK, function () use ( $notice ) {
				/** @var Notice $notice_object */
				$notice_object = new $notice;
				if ( $notice_object->should_display() ) {
					$notice_object->display();
				}
			} );
		}
	}

	/**
	 * Determines whether or not a notice has been dismissed.
	 *
	 * @since 2.10.5
	 *
	 * @param string $id ID of the notice to check.
	 *
	 * @return bool
	 */
	public static function is_dismissed( $id ) {
		$is_dismissed = (bool) Persistent_Dismissible::get( array(
			'id' => $id
		) );

		return true === $is_dismissed;
	}

	/**
	 * Dismisses a notice.
	 *
	 * @since 2.10.5
	 *
	 * @param string $id               ID of the notice to dismiss.
	 * @param int    $dismissal_length Number of seconds to dismiss the notice for, or `0` for forever.
	 */
	public static function dismiss( $id, $dismissal_length = 0 ) {
		Persistent_Dismissible::set( array(
			'id'   => $id,
			'life' => $dismissal_length
		) );
	}

	/**
	 * AJAX callback for dismissing a notice.
	 *
	 * @since 2.10.5
	 */
	public function dismiss_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You do not have permission to perform this action.', 'easy-digital-downloads' ), 403 );
		}

		if ( empty( $_POST['notice_id'] ) ) {
			wp_send_json_error( __( 'Missing notice ID.', 'easy-digital-downloads' ), 400 );
		}

		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'edd-dismiss-notice-' . sanitize_key( $_POST['notice_id'] ) ) ) {
			wp_send_json_error( __( 'You do not have permission to perform this action.', 'easy-digital-downloads' ), 403 );
		}

		$dismissal_length = ! empty( $_POST['lifespan'] ) ? absint( $_POST['lifespan'] ) : 0;

		self::dismiss( sanitize_key( $_POST['notice_id'] ), $dismissal_length );

		wp_send_json_success();
	}

}

new PromoHandler();
