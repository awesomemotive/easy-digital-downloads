<?php
/**
 * Error Tracking
 *
 * @package     Easy Digital Downloads
 * @subpackage  Error Tracking
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/


// make sure a session is started
if( !session_id() ) {
	add_action( 'init', 'session_start', -1 );
}

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
	if( $errors ) {
		echo '<div class="edd_errors">';
		    // Loop error codes and display errors
		   foreach( $errors as $error_id => $error ){
		        echo '<p class="edd_error" id="edd_error_' . $error_id . '"><strong>' . __('Error', 'edd') . '</strong>: ' . $error . '</p>';
		    }
		echo '</div>';
		edd_clear_errors();
	}
}
add_action( 'edd_payment_mode_bottom', 'edd_print_errors' );
add_action( 'edd_before_purchase_form', 'edd_print_errors' );
add_action( 'edd_before_checkout_register_form', 'edd_print_errors' );


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
	if( isset( $_SESSION['edd-errors'] ) ) {
		$errors = $_SESSION['edd-errors'];
		return $errors;
	}
	return false;
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
	if( !$errors ) {
		$errors = array();
	}
	$errors[ $error_id ] = $error_message;
	$_SESSION['edd-errors'] = $errors;
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
	if( isset( $_SESSION['edd-errors'] ) ) $_SESSION['edd-errors'] = null;
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
	if( $errors ) {
		unset( $errors[ $error_id ] );
	}
}