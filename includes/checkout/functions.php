<?php
/**
 * Checkout Functions
 *
 * @package     EDD
 * @subpackage  Checkout
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Determines if a user can checkout or not
 *
 * @since 1.3.3
 * @global $edd_options Array of all the EDD Options
 * @return bool Can user checkout?
 */
function edd_can_checkout() {
	global $edd_options;

	$can_checkout = true; // Always true for now

	return (bool) apply_filters( 'edd_can_checkout', $can_checkout );
}


/**
 * Retrieve the Success page URI
 *
 * @access      public
 * @since       1.6
 * @return      string
*/
function edd_get_success_page_uri() {
	global $edd_options;

	$page_id = isset( $edd_options['success_page'] ) ? absint( $edd_options['success_page'] ) : 0;

	return apply_filters( 'edd_get_success_page_uri', get_permalink( $edd_options['success_page'] ) );
}


/**
 * Send To Success Page
 *
 * Sends the user to the succes page.
 *
 * @param string $query_string
 * @access      public
 * @since       1.0
 * @return      void
*/
function edd_send_to_success_page( $query_string = null ) {
	global $edd_options;

	$redirect = edd_get_success_page_uri();

	if ( $query_string )
		$redirect .= $query_string;

	wp_redirect( apply_filters('edd_success_page_redirect', $redirect, $_POST['edd-gateway'], $query_string) );
	edd_die();
}

/**
 * Send back to checkout.
 *
 * Used to redirect a user back to the purchase
 * page if there are errors present.
 *
 * @param array $args
 * @access public
 * @since  1.0
 * @return Void
 */
function edd_send_back_to_checkout( $args = array() ) {
	$redirect = edd_get_checkout_uri();

	if ( ! empty( $args ) ) {
		// Check for backward compatibility
		if ( is_string( $args ) )
			$args = str_replace( '?', '', $args );

		$args = wp_parse_args( $args );

		$redirect = add_query_arg( $args, $redirect );
	}

	wp_redirect( apply_filters( 'edd_send_back_to_checkout', $redirect, $args ) );
	edd_die();
}

/**
 * Get Success Page URL
 *
 * Gets the success page URL.
 *
 * @param string $query_string
 * @access      public
 * @since       1.0
 * @return      string
*/
function edd_get_success_page_url( $query_string = null ) {
	global $edd_options;

	$success_page = get_permalink($edd_options['success_page']);
	if ( $query_string )
		$success_page .= $query_string;

	return apply_filters( 'edd_success_page_url', $success_page );
}


/**
 * Check if a field is required
 *
 * @param string $field
 * @access      public
 * @since       1.7
 * @return      bool
*/
function edd_field_is_required( $field = '' ) {
	$required_fields = edd_purchase_form_required_fields();
	return array_key_exists( $field, $required_fields );
}