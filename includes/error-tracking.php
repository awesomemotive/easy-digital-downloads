<?php
/**
 * Error Tracking
 *
 * @package     EDD
 * @subpackage  Functions/Errors
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
 * @since 1.0
 * @uses edd_get_errors()
 * @uses edd_clear_errors()
 * @return void
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
 * @since 1.0
 * @uses EDD_Session::get()
 * @return mixed array if errors are present, false if none found
 */
function edd_get_errors() {
	return EDD()->session->get( 'edd_errors' );
}

/**
 * Set Error
 *
 * Stores an error in a session var.
 *
 * @since 1.0
 * @uses EDD_Session::get()
 * @param int $error_id ID of the error being set
 * @param string $error_message Message to store with the error
 * @return void
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
 * Clears all stored errors.
 *
 * @since 1.0
 * @uses EDD_Session::set()
 * @return void
 */
function edd_clear_errors() {
	EDD()->session->set( 'edd_errors', null );
}

/**
 * Removes (unsets) a stored error
 *
 * @since 1.3.4
 * @uses EDD_Session::set()
 * @param int $error_id ID of the error being set
 * @return void
 */
function edd_unset_error( $error_id ) {
	$errors = edd_get_errors();
	if ( $errors ) {
		unset( $errors[ $error_id ] );
		EDD()->session->set( 'edd_errors', $errors );
	}
}

/**
 * Register die handler for edd_die()
 *
 * @author Sunny Ratilal
 * @since 1.6
 * @return void
 */
function _edd_die_handler() {
	if ( defined( 'EDD_UNIT_TESTS' ) )
		return '_edd_die_handler';
	else
		die();
}

/**
 * Wrapper function for wp_die(). This function adds filters for wp_die() which
 * kills execution of the script using wp_die(). This allows us to then to work
 * with functions using edd_die() in the unit tests.
 *
 * @author Sunny Ratilal
 * @since 1.6
 * @return void
 */
function edd_die() {
	add_filter( 'wp_die_ajax_handler', '_edd_die_handler', 10, 3 );
	add_filter( 'wp_die_handler', '_edd_die_handler', 10, 3 );
	wp_die('');
}