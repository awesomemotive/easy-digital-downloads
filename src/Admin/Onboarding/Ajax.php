<?php
/**
 * Onboarding Wizard ajax functions.
 *
 * @package     EDD
 * @subpackage  Onboarding
 * @copyright   Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.1.1
 */

namespace EDD\Admin\Onboarding;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Ajax class.
 *
 * @since 3.1.1
 */
class Ajax implements \EDD\EventManagement\SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'wp_ajax_edd_onboarding_telemetry_settings' => 'ajax_save_telemetry_settings',
			'wp_ajax_edd_onboarding_create_product'     => 'create_product',
			'wp_ajax_edd_onboarding_started'            => 'ajax_onboarding_started',
			'wp_ajax_edd_onboarding_completed'          => 'ajax_onboarding_completed',
			'wp_ajax_edd_onboarding_skipped'            => 'ajax_onboarding_skipped',
			'wp_ajax_edd_onboarding_save_email'         => 'save_email',
		);
	}

	/**
	 * Ajax callback for saving telemetry option.
	 *
	 * @since 3.1.1
	 */
	public function ajax_save_telemetry_settings() {
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'edd_onboarding_wizard' ) ) {
			exit();
		}

		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			exit;
		}

		if ( isset( $_REQUEST['telemetry_toggle'] ) ) {
			edd_update_option( 'allow_tracking', filter_var( $_REQUEST['telemetry_toggle'], FILTER_VALIDATE_BOOLEAN ) );
		}

		if ( isset( $_REQUEST['auto_register'] ) ) {
			edd_update_option( 'logged_in_only', 'auto' );
		}

		update_option( 'edd_tracking_notice', true );
		exit;
	}

	/**
	 * Ajax callback for creating a product.
	 *
	 * @since 3.1.1
	 */
	public function create_product() {
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'edd_onboarding_wizard' ) ) {
			exit();
		}

		if ( ! current_user_can( 'edit_products' ) ) {
			return;
		}

		$response = array( 'success' => false );

		// Prepare product post details.
		$product = array(
			'post_title'  => wp_strip_all_tags( $_REQUEST['product_title'] ),
			'post_status' => 'draft',
			'post_type'   => 'download',
		);

		// Insert the product into the database.
		$post_id = wp_insert_post( $product );
		if ( $post_id ) {
			$post = get_post( $post_id );

			// Save meta fields.
			edd_download_meta_box_fields_save( $post_id, $post );

			// Set featured image.
			if ( ! empty( $_REQUEST['product_image_id'] ) ) {
				set_post_thumbnail( $post_id, absint( $_REQUEST['product_image_id'] ) );
			}

			$response['success']      = true;
			$response['redirect_url'] = get_edit_post_link( $post_id );
		}

		wp_send_json( $response );
		exit;
	}

	/**
	 * Ajax callback when user started the Onboarding flow.
	 *
	 * @since 3.1.1
	 */
	public function ajax_onboarding_started() {
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'edd_onboarding_wizard' ) ) {
			exit;
		}

		if ( get_option( 'edd_onboarding_completed' ) ) {
			exit;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			exit;
		}

		update_option( 'edd_onboarding_started', current_time( 'Y-m-d H:i:s' ), false );
		exit;
	}

	/**
	 * Ajax callback for completing the Onboarding.
	 *
	 * @since 3.1.1
	 */
	public function ajax_onboarding_completed() {
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'edd_onboarding_wizard' ) ) {
			exit;
		}

		if ( get_option( 'edd_onboarding_completed' ) ) {
			exit;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			exit;
		}

		update_option( 'edd_onboarding_completed', current_time( 'Y-m-d H:i:s' ), false );
		update_option( 'edd_tracking_notice', true );

		$this->clean_onboarding_options();

		exit;
	}

	/**
	 * Ajax callback for skipping the Onboarding.
	 *
	 * @since 3.1.1
	 */
	public function ajax_onboarding_skipped() {
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'edd_onboarding_wizard' ) ) {
			exit();
		}

		if ( get_option( 'edd_onboarding_completed' ) ) {
			exit;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			exit;
		}

		update_option( 'edd_onboarding_completed', current_time( 'Y-m-d H:i:s' ), false );

		$this->clean_onboarding_options();
		exit;
	}

	public function save_email() {
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'edd_onboarding_wizard' ) ) {
			exit;
		}

		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			exit;
		}

		$settings = array(
			'email_logo',
			'from_name',
			'from_email',
		);

		foreach ( $settings as $setting ) {
			$value = isset( $_POST[ $setting ] ) ? sanitize_text_field( wp_unslash( $_POST[ $setting ] ) ) : '';
			edd_update_option( $setting, $value );
		}

		if ( isset( $_POST['content'] ) ) {
			$email = edd_get_email( 'order_receipt' );
			if ( $email ) {
				edd_update_email(
					$email->id,
					array(
						'content' => wp_kses_post( wp_unslash( $_POST['content'] ) ),
					)
				);
			}
		}

		exit;
	}

	/**
	 * Clean onboarding options.
	 *
	 * @since 3.1.1
	 */
	private function clean_onboarding_options() {
		delete_option( 'edd_onboarding_latest_step' );
	}

	/**
	 * Filtered the Stripe disconnect URL for the onboarding wizard.
	 *
	 * The wizard no longer offers a Disconnect link, so this does nothing and is no longer hooked.
	 *
	 * @since 3.1.1
	 * @deprecated 3.7.3
	 * @return void
	 */
	public function disconnect_url() {
		_edd_deprecated_function( __METHOD__, '3.7.3' );
	}
}
