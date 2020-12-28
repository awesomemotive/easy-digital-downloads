<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register the tabs for customer management
 *
 * @since  2.3
 * @return array Array of tabs for the customer
 */
function edd_customer_tabs() {
	$tabs = array();
	return apply_filters( 'edd_customer_tabs', $tabs );

}

/**
 * Display a notice on customer account if they are pending verification
 *
 * @since  2.4.8
 * @return void
 */
function edd_verify_customer_notice( $customer ) {

	if ( ! edd_user_pending_verification( $customer->user_id ) ) {
		return;
	}

	$url = wp_nonce_url( admin_url( 'edit.php?post_type=download&page=edd-customers&view=overview&edd_action=verify_user_admin&id=' . $customer->id ), 'edd-verify-user' );

	echo '<div class="update error"><p>';
	_e( 'This customer\'s user account is pending verification.', 'easy-digital-downloads' );
	echo ' ';
	echo '<a href="' . $url . '" title="' . esc_attr( __( 'Verify this customer\'s account', 'easy-digital-downloads' ) ) . '">' . __( 'Verify account.', 'easy-digital-downloads' ) . '</a>';
	echo "\n\n";

	echo '</p></div>';
}
add_action( 'edd_customer_card_top', 'edd_verify_customer_notice', 10, 1 );
