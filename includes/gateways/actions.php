<?php
/**
 * Gateway Actions
 *
 * @package     EDD
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.7
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Processes gateway select on checkout. Only for users without ajax / javascript
 *
 * @since 1.7
 *
 * @param $data
 */
function edd_process_gateway_select( $data ) {
	if ( isset( $_POST['gateway_submit'] ) && ! empty( $_POST['payment-mode'] ) ) {
		edd_redirect( add_query_arg( 'payment-mode', urlencode( $_POST['payment-mode'] ) ) );
	}
}
add_action( 'edd_gateway_select', 'edd_process_gateway_select' );

/**
 * Loads a payment gateway via AJAX.
 *
 * @since 1.3.4
 * @since 2.9.4 Added nonce verification prior to loading the purchase form.
 */
function edd_load_ajax_gateway() {
	if ( ! isset( $_POST['nonce'] ) ) {
		edd_debug_log( __( 'Missing nonce when loading the gateway fields. Please read the following for more information: https://easydigitaldownloads.com/development/2018/07/05/important-update-to-ajax-requests-in-easy-digital-downloads-2-9-4', 'easy-digital-downloads' ), true );
	}

	if ( isset( $_POST['edd_payment_mode'] ) && isset( $_POST['nonce'] ) ) {
		$payment_mode = sanitize_text_field( $_POST['edd_payment_mode'] );
		$nonce        = sanitize_text_field( $_POST['nonce'] );

		$nonce_verified = wp_verify_nonce( $nonce, 'edd-gateway-selected-' . $payment_mode );

		if ( false !== $nonce_verified ) {
			do_action( 'edd_purchase_form' );
		}

		exit();
	}
}
add_action( 'wp_ajax_edd_load_gateway', 'edd_load_ajax_gateway' );
add_action( 'wp_ajax_nopriv_edd_load_gateway', 'edd_load_ajax_gateway' );

/**
 * Sets an error on checkout if no gateways are enabled
 *
 * @since 1.3.4
 * @since 3.1.2 Updated to include a different message for users who do not have the manage_shop_settings capability.
 *
 * @return void
 */
function edd_no_gateway_error() {
	$gateways = edd_get_enabled_payment_gateways();

	if ( empty( $gateways ) && edd_get_cart_total() > 0 ) {
		remove_action( 'edd_after_cc_fields', 'edd_default_cc_address_fields' );
		remove_action( 'edd_cc_form', 'edd_get_cc_form' );
		if ( current_user_can( 'manage_shop_settings' ) ) {
			$error_message = __( 'You must enable a payment gateway to use Easy Digital Downloads', 'easy-digital-downloads' );
		} else {
			$error_message = __( 'Your order cannot be completed at this time. Please try again or contact site support.', 'easy-digital-downloads' );
		}
		edd_set_error( 'no_gateways', $error_message );
	} else {
		edd_unset_error( 'no_gateways' );
	}
}
add_action( 'init', 'edd_no_gateway_error' );
