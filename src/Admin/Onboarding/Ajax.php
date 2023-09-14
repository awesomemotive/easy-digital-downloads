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
			'wp_ajax_edds_stripe_connect_account_info'  => array( 'disconnect_url', 5 ),
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

	/**
	 * Filters the Stripe disconnect URL.
	 * This has to be hooked into the ajax action before the main ajax work is run.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function disconnect_url() {
		add_filter(
			'edds_stripe_connect_disconnect_url',
			function( $url ) {
				if ( empty( $_REQUEST['onboardingWizard'] ) ) {
					return $url;
				}
				$stripe_connect_disconnect_url = edd_get_admin_url(
					array(
						'page'                   => 'edd-onboarding-wizard',
						'current_step'           => 'payment_methods',
						'edds-stripe-disconnect' => true,
					)
				);
				return wp_nonce_url( $stripe_connect_disconnect_url, 'edds-stripe-connect-disconnect' );
			},
			15
		);
	}

	/**
	 * Clean onboarding options.
	 *
	 * @since 3.1.1
	 */
	private function clean_onboarding_options() {
		delete_option( 'edd_onboarding_latest_step' );
	}
}
