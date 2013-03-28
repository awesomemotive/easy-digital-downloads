<?php
/**
 * Error Tracking
 *
 * @package     Easy Digital Downloads
 * @subpackage  Error Tracking
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Print Errors
 *
 * Prints all stored errors. For use during checkout.
 * If errors exist, they are returned.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function edd_print_errors() {
	$errors = edd_get_errors();
	if ( $errors ) {
		$classes = apply_filters( 'edd_error_class', array(
			'edd_errors'
		) );
		echo '<div class="' . implode( ' ', $classes ) . '">';
		    // Loop error codes and display errors
		   foreach ( $errors as $error_id => $error ){
		        echo '<p class="edd_error" id="edd_error_' . $error_id . '"><strong>' . __('Error', 'edd') . '</strong>: ' . $error . '</p>';
		   }
		echo '</div>';
		edd_clear_errors();
	}
}
add_action( 'edd_purchase_form_before_submit', 'edd_print_errors' );
add_action( 'edd_ajax_checkout_errors', 'edd_print_errors' );

/**
 * Get Errors
 *
 * Retrieves all error messages stored during the checkout process.
 * If errors exist, they are returned.
 *
 * @access      public
 * @since       1.0
 * @return      mixed - array if errors are present, false if none found
 */
function edd_get_errors() {
	return EDD()->session->get( 'edd_errors' );
}

/**
 * Set Error
 *
 * Stores an error in a session var.
 *
 * @access      public
 * @since       1.0
 * @param       $error_id string - the ID of the error being set
 * @param       $error_message - the message to store with the error
 * @return      void
*/
function edd_set_error( $error_id, $error_message ) {
	$errors = edd_get_errors();
	if ( ! $errors ) {
		$errors = array();
	}
	$errors[ $error_id ] = $error_message;
	EDD()->session->set( 'edd_errors', $errors );
}

/**
 * Clear Errors
 *
 * Clears all stored errors.
 *
 * @access      public
 * @since       1.0
 * @return      void
 */
function edd_clear_errors() {
	EDD()->session->set( 'edd_errors', null );
}

/**
 * Unset an Error
 *
 * Removes a stored error
 *
 * @access      public
 * @since       1.3.4
 * @param       $error_id string - the ID of the error being set
 * @return      void
 */
function edd_unset_error( $error_id ) {
	$errors = edd_get_errors();
	if ( $errors ) {
		unset( $errors[ $error_id ] );
		EDD()->session->set( 'edd_errors', $errors );
	}
}