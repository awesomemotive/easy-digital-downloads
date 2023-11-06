<?php
/**
 * Functions to handle the checkout related pages.
 *
 * @package EDD
 *
 */

/**
 * Retrieves the Success page URI.
 *
 * @since 1.6
 * @since 3.1 Updated to check for the new confirmation page if no query string is provided.
 * @return      string
*/
function edd_get_success_page_uri( $query_string = null ) {
	$page_id = edd_get_option( 'success_page', 0 );
	if ( empty( $query_string ) ) {
		$page_id = edd_get_option( 'confirmation_page', $page_id );
	}
	$page_id      = absint( $page_id );
	$success_page = get_permalink( $page_id );

	if ( $query_string ) {
		$success_page .= $query_string;
	}

	return apply_filters( 'edd_get_success_page_uri', $success_page );
}

/**
 * Determines if we're currently on the Success page.
 *
 * @since 1.9.9
 * @return bool True if on the Success page, false otherwise.
 */
function edd_is_success_page() {
	$is_success_page = edd_get_option( 'confirmation_page', false );
	if ( empty( $is_success_page ) ) {
		$is_success_page = edd_get_option( 'success_page', false );
	}
	$is_success_page = isset( $is_success_page ) ? is_page( $is_success_page ) : false;

	return (bool) apply_filters( 'edd_is_success_page', $is_success_page );
}

/**
 * Send To Success Page
 *
 * Sends the user to the succes page.
 *
 * @param string $query_string
 * @since       1.0
 * @return      void
*/
function edd_send_to_success_page( $query_string = null ) {
	$redirect = edd_get_success_page_uri( $query_string );
	$gateway  = isset( $_REQUEST['edd-gateway'] ) ? $_REQUEST['edd-gateway'] : '';

	edd_redirect( apply_filters( 'edd_success_page_redirect', $redirect, $gateway, $query_string ) );
}

/**
 * Gets the confirmation page URI.
 * If a confirmation page is not set, returns the success page URI with no parameters.
 *
 * @since 3.1
 * @return string
 */
function edd_get_confirmation_page_uri() {
	$confirmation_page_id = edd_get_option( 'confirmation_page', false );
	if ( empty( $confirmation_page_id ) ) {
		return edd_get_success_page_uri();
	}

	return get_permalink( $confirmation_page_id );
}

/**
 * Gets the receipt page URI.
 *
 * @since 3.1
 * @param int $order_id
 * @return string
 */
function edd_get_receipt_page_uri( $order_id ) {
	$page_id = absint( edd_get_option( 'success_page', 0 ) );
	$order   = edd_get_order( $order_id );
	if ( ! $order ) {
		return get_permalink( $page_id );
	}

	$query_args = array(
		'id'    => $order_id,
		'order' => urlencode( md5( $order_id . $order->payment_key . $order->email ) ),
	);

	return add_query_arg( $query_args, get_permalink( $page_id ) );
}
