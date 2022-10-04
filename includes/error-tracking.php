<?php
/**
 * Error Tracking
 *
 * @package     EDD
 * @subpackage  Functions/Errors
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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
 * @param array $errors
 *
 * @since 2.11
 * @return string
 */
function edd_build_errors_html( $errors ) {
	$error_html = '';

	$classes = apply_filters( 'edd_error_class', array(
		'edd_errors', 'edd-alert', 'edd-alert-error'
	) );

	if ( ! empty( $errors ) && is_array( $errors ) ) {
		$error_html .= '<div class="' . implode( ' ', $classes ) . '">';
		// Loop error codes and display errors
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
 * @param array $successes
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
 * @uses EDD_Session::get()
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
 * Stores an array of success messages in a session variable.
 *
 * @since 3.1
 * @uses EDD_Session::set()
 * @param string $error_id
 * @param string $error_message
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
 * @uses EDD_Session::set()
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
 * @uses EDD_Session::set()
 * @param int $error_id ID of the error being set
 * @return string
 */
function edd_unset_error( $error_id ) {
	$errors = edd_get_errors();

	if ( $errors && isset( $errors[ $error_id ] ) ) {
		unset( $errors[ $error_id ] );
		EDD()->session->set( 'edd_errors', $errors );
	}
}

/**
 * Register die handler for edd_die()
 *
 * @author Sunny Ratilal
 * @since 1.6
 *
 * @return void
 */
function _edd_die_handler() {
	die();
}

/**
 * Wrapper function for wp_die().
 *
 * This function adds filters for wp_die() which kills execution of the script
 * using wp_die(). This allows us to then to work with functions using edd_die()
 * in the unit tests.
 *
 * @author Sunny Ratilal
 * @since 1.6
 * @return void
 */
function edd_die( $message = '', $title = '', $status = 400 ) {
	if ( ! defined( 'EDD_UNIT_TESTS' ) ) {
		add_filter( 'wp_die_ajax_handler', '_edd_die_handler', 10, 3 );
		add_filter( 'wp_die_handler'     , '_edd_die_handler', 10, 3 );
		add_filter( 'wp_die_json_handler', '_edd_die_handler', 10, 3 );
	}

	wp_die( $message, $title, array( 'response' => $status ) );
}
