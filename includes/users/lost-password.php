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

	$login_page_uri = edd_get_login_page_uri();

	if ( empty( $login_page_uri ) ) {
		return add_query_arg(
			array(
				'edd_forgot_password' => 'confirm',
			),
			wp_lostpassword_url()
		);
	}

	return add_query_arg( 'action', 'lostpassword', $login_page_uri );
}

/**
 * Gets the password reset link for a user.
 *
 * @param WP_User $user
 * @return false|string
 */
function edd_get_password_reset_link( $user ) {
	$key = get_password_reset_key( $user );
	if ( is_wp_error( $key ) ) {
		return false;
	}

	return add_query_arg(
		array(
			'action' => 'rp',
			'key'    => rawurlencode( $key ),
			'login'  => rawurlencode( $user->user_login ),
		),
		wp_login_url()
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

add_action( 'edd_user_lost_password', 'edd_handle_lost_password_request' );
/**
 * Handles the lost password request from the EDD lost password block.
 *
 * @since 3.1
 * @param array $data
 * @return void
 */
function edd_handle_lost_password_request( $data ) {

	// Verify the nonce.
	if ( empty( $data['edd_lost-password_nonce'] ) || ! wp_verify_nonce( $data['edd_lost-password_nonce'], 'edd-lost-password-nonce' ) ) {
		edd_set_error( 'edd_lost_password', __( 'Your request could not be completed.', 'easy-digital-downloads' ) );
		return;
	}

	if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
		$errors = retrieve_password();
		if ( ! is_wp_error( $errors ) ) {
			edd_set_success( 'checkemail', __( 'You did it! Check your email for instructions on resetting your password.', 'easy-digital-downloads' ) );
		} else {
			$error_code = $errors->get_error_code();
			$message    = $errors->get_error_message( $error_code );
			if ( $message ) {
				// WP_Error messages include "Error:" so we remove that here to prevent duplication.
				$message = explode( ':', $message );
				$output  = trim( $message[0] );
				if ( ! empty( $message[1] ) ) {
					unset( $message[0] );
					$output = trim( implode( ':', $message ) );
				}
				edd_set_error( $error_code, $output );
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
			'edd_action' => 'password_reset_requested',
			'key'        => $key,
			'login'      => rawurlencode( $user_login ),
		),
		esc_url_raw( $_POST['edd_redirect'] )
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

add_action( 'edd_password_reset_requested', 'edd_validate_password_reset_link' );
/**
 * Validates the email link and sends the user to the password reset form upon success.
 *
 * @since 3.1
 * @return void
 */
function edd_validate_password_reset_link() {
	list( $rp_path ) = explode( '?', wp_unslash( $_SERVER['REQUEST_URI'] ) );
	$rp_cookie       = 'wp-resetpass-' . COOKIEHASH;
	$redirect        = remove_query_arg( array( 'key', 'login', 'edd_action' ), wp_get_referer() );

	// Everything is good; move forward with the password reset.
	if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {
		$value = sprintf( '%s:%s', wp_unslash( $_GET['login'] ), wp_unslash( $_GET['key'] ) );
		setcookie( $rp_cookie, $value, 0, $rp_path, COOKIE_DOMAIN, is_ssl(), true );

		edd_redirect( add_query_arg( 'action', 'rp', $redirect ) );
	}

	$user = false;
	if ( isset( $_COOKIE[ $rp_cookie ] ) && 0 < strpos( $_COOKIE[ $rp_cookie ], ':' ) ) {
		list( $rp_login, $rp_key ) = explode( ':', wp_unslash( $_COOKIE[ $rp_cookie ] ), 2 );

		$user = check_password_reset_key( $rp_key, $rp_login );

		if ( isset( $_POST['pass1'] ) && ! hash_equals( $rp_key, $_POST['rp_key'] ) ) {
			$user = false;
		}
	}

	if ( ! $user || is_wp_error( $user ) ) {
		setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );

		if ( $user && $user->get_error_code() === 'expired_key' ) {
			edd_set_error( 'expiredkey', __( 'Your password reset link has expired. Please request a new link below.', 'easy-digital-downloads' ) );
		} else {
			edd_set_error( 'invalidkey', __( 'Your password reset link appears to be invalid. Please request a new link below.', 'easy-digital-downloads' ) );
		}
	}

	// Redirect back to the lost password form instead of the password reset.
	edd_redirect( add_query_arg( 'action', 'lostpassword', $redirect ) );
}

add_action( 'edd_user_reset_password', 'edd_validate_password_reset' );
/**
 * Validates the password reset and redirects to the login form on success.
 *
 * @since 3.1
 * @param array $data
 * @return void
 */
function edd_validate_password_reset( $data ) {

	// We don't need or use AJAX requests for this, so die if one is received.
	if ( edd_doing_ajax() ) {
		wp_die( __( 'Invalid password reset request.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 400 ) );
	}

	// Verify the nonce.
	if ( ! isset( $data['edd_resetpassword_nonce'] ) || ! wp_verify_nonce( $data['edd_resetpassword_nonce'], 'edd-reset-password-nonce' ) ) {
		edd_set_error( 'password_reset_failed', __( 'Invalid password reset request.', 'easy-digital-downloads' ) );
	}

	if ( empty( $data['rp_key'] ) ) {
		edd_set_error( 'password_reset_failed', __( 'Invalid password reset request.', 'easy-digital-downloads' ) );
	}

	$user = check_password_reset_key( $data['rp_key'], $data['user_login'] );

	if ( ! $user || is_wp_error( $user ) ) {
		edd_set_error( 'password_reset_failed', __( 'Invalid password reset request.', 'easy-digital-downloads' ) );
	}

	// Check if password is one or all empty spaces.
	if ( ! empty( $data['pass1'] ) ) {
		$_POST['pass1'] = trim( $data['pass1'] );
	}

	if ( empty( $data['pass1'] ) ) {
		edd_set_error( 'empty_password', __( 'The password cannot be a space or all spaces.', 'easy-digital-downloads' ) );
	}

	// Check if password fields do not match.
	if ( ! empty( $data['pass1'] ) && trim( $data['pass2'] ) !== $data['pass1'] ) {
		edd_set_error( 'password_reset_mismatch', __( 'The passwords do not match.', 'easy-digital-downloads' ) );
	}

	$user = get_user_by( 'login', $data['user_login'] );
	if ( false === $user ) {
		edd_set_error( 'password_reset_unsuccessful', __( 'Your password could not be reset.', 'easy-digital-downloads' ) );
	}

	$redirect = remove_query_arg( 'action', $data['edd_redirect'] );

	// If no errors were registered then reset the password.
	$errors = edd_get_errors();
	if ( empty( $errors ) ) {
		reset_password( $user, $data['pass1'] );
		edd_set_success( 'password_reset_successful', __( 'Your password was successfully reset.', 'easy-digital-downloads' ) );
		// todo: check if this is correct
		setcookie( 'wp-resetpass-' . COOKIEHASH, ' ', time() - YEAR_IN_SECONDS, wp_make_link_relative( wp_get_referer() ), COOKIE_DOMAIN, is_ssl(), true );
		edd_redirect( $redirect );
	}

	edd_redirect( add_query_arg( 'action', 'password_reset_unsuccessful', $redirect ) );
}
