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
	$errors    = edd_get_errors();
	$successes = EDD()->session->get( 'edd_success_errors' );
	if ( $errors || $successes ) {

		echo edd_build_errors_html( $errors );
		echo edd_build_successes_html( $successes );

		edd_clear_errors();
	}
}
add_action( 'edd_purchase_form_before_submit', 'edd_print_errors' );
add_action( 'edd_ajax_checkout_errors', 'edd_print_errors' );
add_action( 'edd_print_errors', 'edd_print_errors' );

/**
 * Formats error messages and returns an HTML string.
 *
 * @param array $errors Array of error messages.
 *
 * @since 2.11
 * @return string
 */
function edd_build_errors_html( $errors ) {
	$error_html = '';

	$classes = apply_filters(
		'edd_error_class',
		array(
			'edd_errors',
			'edd-alert',
			'edd-alert-error',
		)
	);

	if ( ! empty( $errors ) && is_array( $errors ) ) {
		$error_html .= '<div class="' . implode( ' ', $classes ) . '">';
		foreach ( $errors as $error_id => $error ) {
			$error_html .= '<p class="edd_error" id="edd_error_' . $error_id . '"><strong>' . __( 'Error', 'easy-digital-downloads' ) . '</strong>: ' . $error . '</p>';

		}
		$error_html .= '</div>';
	}

	return $error_html;
}

/**
 * Builds the HTML output for the sucess messages.
 *
 * @since 3.1
 * @param array $successes Array of success messages.
 * @return string
 */
function edd_build_successes_html( $successes ) {
	if ( empty( $successes ) || ! is_array( $successes ) ) {
		return '';
	}

	$html = '<div class="edd_success edd-alert edd-alert-success">';
	foreach ( $successes as $id => $message ) {
		$html .= '<p id="' . $id . '">';
		$html .= '<strong>' . esc_html__( 'Success', 'easy-digital-downloads' ) . '</strong>: ';
		$html .= $message;
		$html .= '</p>';
	}
	$html .= '</div>';

	return $html;
}

/**
 * Get Errors
 *
 * Retrieves all error messages stored during the checkout process.
 * If errors exist, they are returned.
 *
 * @since 1.0
 * @uses EDD\Sessions\Handler::get()
 * @return mixed array if errors are present, false if none found
 */
function edd_get_errors() {
	$errors = EDD()->session->get( 'edd_errors' );
	$errors = apply_filters( 'edd_errors', $errors );
	return $errors;
}

/**
 * Set Error
 *
 * Stores an error in a session var.
 *
 * @since 1.0
 * @uses EDD\Sessions\Handler::get()
 * @param int    $error_id ID of the error being set.
 * @param string $error_message Message to store with the error.
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
 * Stores an array of success messages in a session variable.
 *
 * @since 3.1
 * @uses EDD\Sessions\Handler::set()
 * @param string $error_id      ID of the error being set.
 * @param string $error_message Message to store with the error.
 * @return void
 */
function edd_set_success( $error_id, $error_message ) {
	$successes = EDD()->session->get( 'edd_success_errors' );
	if ( ! $successes ) {
		$successes = array();
	}
	$successes[ $error_id ] = $error_message;

	EDD()->session->set( 'edd_success_errors', $successes );
}

/**
 * Clears all stored errors.
 *
 * @since 1.0
 * @uses EDD\Sessions\Handler::set()
 * @return void
 */
function edd_clear_errors() {
	EDD()->session->set( 'edd_errors', null );
	EDD()->session->set( 'edd_success_errors', null );
}

/**
 * Removes (unsets) a stored error
 *
 * @since 1.3.4
 * @uses EDD\Sessions\Handler::set()
 * @param int $error_id ID of the error being set.
 */
function edd_unset_error( $error_id ) {
	$errors = edd_get_errors();

	if ( $errors && isset( $errors[ $error_id ] ) ) {
		unset( $errors[ $error_id ] );
		EDD()->session->set( 'edd_errors', $errors );
	}
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
