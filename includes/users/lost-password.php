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

add_action( 'edd_user_lost_password', 'edd_lost_password_block' );
/**
 * Handles the lost password request from the EDD lost password block.
 *
 * @since 3.1
 * @param array $data
 * @return void
 */
function edd_lost_password_block( $data ) {
	if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
		$errors = retrieve_password();
		if ( ! is_wp_error( $errors ) ) {
			edd_set_success( 'checkemail', __( 'You did it! Check your email for instructions on resetting your password.', 'easy-digital-downloads' ) );
		} else {
			foreach ( $errors->errors as $id => $message ) {
				$message = explode( ':', reset( $message ) );
				$message = ! empty( $message[1] ) ? trim( $message[1] ) : trim( $message[0] );
				edd_set_error( $id, $message );
			}
		}
	}
	edd_redirect( remove_query_arg( 'action', wp_get_referer() ) );
}

add_filter( 'retrieve_password_message', 'edd_retrieve_password_message', 10, 4 );
/**
 * Filters the email message sent when a password reset has been requested.
 *
 * @since 3.1
 * @param string  $message    The email message.
 * @param string  $key        The activation key.
 * @param string  $user_login The username for the user.
 * @param WP_User $user_data  WP_User object.
 * @return string
 */
function edd_retrieve_password_message( $message, $key, $user_login, $user_data ) {
	if ( empty( $_POST['edd_action'] ) || 'user_lost_password' !== $_POST['edd_action'] ) {
		return $message;
	}
	if ( empty( $_POST['edd_lost-password_nonce'] ) || ! wp_verify_nonce( $_POST['edd_lost-password_nonce'], 'edd-lost-password-nonce' ) ) {
		return $message;
	}
	if ( is_multisite() ) {
		$site_name = get_network()->site_name;
	} else {
		/*
		 * The blogname option is escaped with esc_html on the way into the database
		 * in sanitize_option. We want to reverse this for the plain text arena of emails.
		 */
		$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}
	$message = __( 'Someone has requested a password reset for the following account:', 'easy-digital-downloads' ) . "\r\n\r\n";
	/* translators: %s: Site name. */
	$message .= sprintf( __( 'Site Name: %s', 'easy-digital-downloads' ), $site_name ) . "\r\n\r\n";
	/* translators: %s: User login. */
	$message .= sprintf( __( 'Username: %s', 'easy-digital-downloads' ), $user_login ) . "\r\n\r\n";
	$message .= __( 'If this was a mistake, ignore this email and nothing will happen.', 'easy-digital-downloads' ) . "\r\n\r\n";
	$message .= __( 'To reset your password, visit the following address:', 'easy-digital-downloads' ) . "\r\n\r\n";
	$message .= add_query_arg(
		array(
			'action'  => 'resetpassword',
			'key'     => $key,
			'login'   => rawurlencode( $user_login ),
			'wp_lang' => get_user_locale( $user_data ),
		),
		$_POST['edd_redirect']
	);
	$message .= "\r\n\r\n";

	if ( ! is_user_logged_in() ) {
		$requester_ip = $_SERVER['REMOTE_ADDR'];
		if ( $requester_ip ) {
			$message .= sprintf(
				/* translators: %s: IP address of password reset requester. */
				__( 'This password reset request originated from the IP address %s.', 'easy-digital-downloads' ),
				$requester_ip
			) . "\r\n";
		}
	}

	return $message;
}
