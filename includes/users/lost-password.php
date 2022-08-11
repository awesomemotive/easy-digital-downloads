<?php
/**
 * Lost Password Functions
 *
 * @package     EDD
 * @subpackage  Functions/Login
 * @copyright   Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

add_filter( 'wp_login_errors', 'edd_login_register_error_message', 10, 2 );
/**
 * Changes the WordPress login confirmation message when using EDD's reset password link.
 *
 * @since 2.10
 * @param object \WP_Error $errors
 * @param string $redirect
 * @return void
 */
function edd_login_register_error_message( $errors, $redirect ) {
	$redirect_url = EDD()->session->get( 'edd_forgot_password_redirect' );
	if ( empty( $redirect_url ) ) {
		return $errors;
	}
	$message = sprintf(
		/* translators: %s: Link to the referring page. */
		__( 'Follow the instructions in the confirmation email you just received, then <a href="%s">return to what you were doing</a>.', 'easy-digital-downloads' ),
		esc_url( $redirect_url )
	);
	$errors->remove( 'confirm' );
	$errors->add(
		'confirm',
		apply_filters(
			'edd_login_register_error_message',
			$message,
			$redirect_url
		),
		'message'
	);
	EDD()->session->set( 'edd_forgot_password_redirect', null );

	return $errors;
}

/**
 * Gets the lost password URL, customized for EDD. Using this allows the password
 * reset form to redirect to the login screen with the EDD custom confirmation message.
 *
 * @since 2.10
 * @return string
 */
function edd_get_lostpassword_url() {

	return add_query_arg(
		array(
			'edd_forgot_password' => 'confirm',
		),
		wp_lostpassword_url()
	);
}

add_action( 'lostpassword_form', 'edd_set_lostpassword_session' );
/**
 * Sets a session value for the lost password redirect URI.
 *
 * @since 3.0.2
 * @return void
 */
function edd_set_lostpassword_session() {
	if ( ! empty( $_GET['edd_forgot_password'] ) && 'confirm' === $_GET['edd_forgot_password'] ) {
		$url = wp_validate_redirect(
			wp_get_referer(),
			edd_get_checkout_uri()
		);
		EDD()->session->set( 'edd_forgot_password_redirect', $url );
	}
}
