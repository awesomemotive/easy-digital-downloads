<?php
/**
 * Error Tracking
 *
 * @package     EDD\Functions\ErrorTracking
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Utils\Messages;

/**
 * Print Errors
 *
 * Prints all stored errors. For use during checkout.
 * If errors exist, they are returned.
 *
 * @since 1.0
 * @uses EDD\Utils\Messages::to_html()
 * @uses EDD\Utils\Messages::clear()
 * @return void
 */
function edd_print_errors() {
	if ( ! Messages::has_any() ) {
		return;
	}
	echo Messages::to_html();
	Messages::clear();
}
add_action( 'edd_purchase_form_before_submit', 'edd_print_errors' );
add_action( 'edd_ajax_checkout_errors', 'edd_print_errors' );
add_action( 'edd_print_errors', 'edd_print_errors' );
add_action( 'edd_cart_empty', 'edd_print_errors' );

/**
 * Formats error messages and returns an HTML string.
 *
 * @param array $errors Array of error messages.
 *
 * @since 2.11
 * @return string
 */
function edd_build_errors_html( $errors ) {
	return Messages::build_html_for_messages( is_array( $errors ) ? $errors : array(), 'error' );
}

/**
 * Builds the HTML output for the sucess messages.
 *
 * @since 3.1
 * @param array $successes Array of success messages.
 * @return string
 */
function edd_build_successes_html( $successes ) {
	return Messages::build_html_for_messages( is_array( $successes ) ? $successes : array(), 'success' );
}

/**
 * Get Errors
 *
 * Retrieves all error messages stored during the checkout process.
 * If errors exist, they are returned.
 *
 * @since 1.0
 * @uses EDD\Utils\Messages::get_by_type()
 * @return mixed array if errors are present, false if none found
 */
function edd_get_errors() {
	$errors = Messages::get_by_type( 'error' );
	$errors = apply_filters( 'edd_errors', $errors );

	return ! empty( $errors ) ? $errors : null;
}

/**
 * Set Error
 *
 * Stores an error in a session var.
 *
 * @since 1.0
 * @uses EDD\Utils\Messages::add()
 * @param int    $error_id ID of the error being set.
 * @param string $error_message Message to store with the error.
 * @return void
 */
function edd_set_error( $error_id, $error_message ) {
	Messages::add( 'error', (string) $error_id, $error_message );
}

/**
 * Stores an array of success messages in a session variable.
 *
 * @since 3.1
 * @uses EDD\Utils\Messages::add()
 * @param string $error_id      ID of the error being set.
 * @param string $error_message Message to store with the error.
 * @return void
 */
function edd_set_success( $error_id, $error_message ) {
	Messages::add( 'success', (string) $error_id, $error_message );
}

/**
 * Clears all stored errors (error-type messages only).
 *
 * Success, info, and warn messages are left intact so that gateways and other
 * code can clear errors from a previous attempt without wiping success notices
 * (e.g. cart recovery restored).
 *
 * @since 1.0
 * @uses EDD\Utils\Messages::clear_by_type()
 * @return void
 */
function edd_clear_errors() {
	Messages::clear( 'error' );
}

/**
 * Removes (unsets) a stored error
 *
 * @since 1.3.4
 * @uses EDD\Utils\Messages::remove()
 * @param int $error_id ID of the error being set.
 * @return void
 */
function edd_unset_error( $error_id ) {
	Messages::remove( (string) $error_id, 'error' );
}

/**
 * Wrapper function for wp_die().
 *
 * This function exists for backwards compatibility. In unit tests, a custom handler
 * is registered via the test suite to make wp_die() calls testable.
 *
 * When called without parameters and with default status, it performs a clean exit.
 * When called with a message or non-default status, it uses wp_die() for proper error handling.
 *
 * @since 1.6
 * @param string $message Optional. Error message. Default empty.
 * @param string $title   Optional. Error title. Default empty.
 * @param int    $status  Optional. HTTP status code. Default 400.
 * @return void
 */
function edd_die( $message = '', $title = '', $status = 400 ) {
	// In unit tests, always use wp_die() so it can be caught by test handlers.
	if ( edd_is_doing_unit_tests() ) {
		wp_die( $message, $title, array( 'response' => $status ) );
	}

	// If called with default parameters (clean exit), just exit without output.
	// This is common in AJAX handlers after JSON output and after redirects.
	if ( empty( $message ) && empty( $title ) && 400 === $status ) {
		exit;
	}

	// Otherwise, use wp_die() for proper error handling (including HTTP status codes).
	wp_die( $message, $title, array( 'response' => $status ) );
}
