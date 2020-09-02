<?php
/**
 * Login / Register Functions
 *
 * @package     EDD
 * @subpackage  Functions/Login
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * While loading the template, see if an error was set for a filed login attempt and set the proper
 * HTTP status code if there was a failed login attempt.
 *
 * @since 2.9.24
 *
 * @return void
 */
function edd_login_error_check() {
	$errors = edd_get_errors();
	if ( ! empty( $errors ) ) {
		if ( array_key_exists( 'edd_invalid_login', $errors ) ) {
			status_header( 401 );
		}
	}
}
add_action( 'template_redirect', 'edd_login_error_check', 10 );

/**
 * Login Form
 *
 * @since 1.0
 * @global $post
 * @param string $redirect Redirect page URL
 * @return string Login form
*/
function edd_login_form( $redirect = '' ) {
	global $edd_login_redirect;

	if ( empty( $redirect ) ) {
		$redirect = edd_get_current_page_url();
	}

	$edd_login_redirect = $redirect;

	ob_start();

	edd_get_template_part( 'shortcode', 'login' );

	return apply_filters( 'edd_login_form', ob_get_clean() );
}

/**
 * Registration Form
 *
 * @since 2.0
 * @global $post
 * @param string $redirect Redirect page URL
 * @return string Register form
*/
function edd_register_form( $redirect = '' ) {
	global $edd_register_redirect;

	if ( empty( $redirect ) ) {
		$redirect = edd_get_current_page_url();
	}

	$edd_register_redirect = $redirect;

	ob_start();

	edd_get_template_part( 'shortcode', 'register' );

	return apply_filters( 'edd_register_form', ob_get_clean() );
}

/**
 * Process Login Form
 *
 * @since 1.0
 * @since 2.9.24 No longer does validation which would prevent bruteforce detection plugins to be able to integrate.
 *
 * @param array $data Data sent from the login form
 * @return void
*/
function edd_process_login_form( $data ) {

	if ( ! empty( $data['edd_login_nonce'] ) && wp_verify_nonce( $data['edd_login_nonce'], 'edd-login-nonce' ) ) {
		$login      = isset( $data['edd_user_login'] ) ? $data['edd_user_login'] : '';
		$pass       = isset( $data['edd_user_pass'] ) ? $data['edd_user_pass'] : '';
		$rememberme = isset( $data['rememberme'] );

		$user = edd_log_user_in( 0, $login, $pass, $rememberme );

		// Wipe these variables so they aren't anywhere in the submitted format any longer.
		$login = null;
		$pass  = null;
		$data['edd_user_login'] = null;
		$data['edd_user_pass']  = null;

		// Check for errors and redirect if none present.
		$errors = edd_get_errors();
		if ( ! $errors ) {
			$redirect = apply_filters( 'edd_login_redirect', $data['edd_redirect'], 0 );
			wp_redirect( $redirect );
			edd_die();
		}
	}
}
add_action( 'edd_user_login', 'edd_process_login_form' );


/**
 * Log User In
 *
 * @since 1.0
 * @since 2.9.24 Uses the wp_signon function instead of all the additional checks which can bypass hooks in core.
 *
 * @param int $user_id User ID
 * @param string $user_login Username
 * @param string $user_pass Password
 * @param boolean $remember Remember me
 * @return void
*/
function edd_log_user_in( $user_id, $user_login, $user_pass, $remember = false ) {

	$credentials = array(
		'user_login'    => $user_login,
		'user_password' => $user_pass,
		'remember'      => $remember,
	);

	$user = wp_signon( $credentials );

	if ( ! $user instanceof WP_User ) {
		edd_set_error(
			'edd_invalid_login',
			sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__( 'Invalid username or password. %1$sReset Password%2$s', 'easy-digital-downloads' ),
				'<a href="' . esc_url( wp_lostpassword_url( edd_get_checkout_uri() ) ) . '">',
				'</a>'
			)
		);
	} else {
		// Since wp_signon doesn't set the current user, we need to do this.
		wp_set_current_user( $user->ID );

		do_action( 'edd_log_user_in', $user_id, $user_login, $user_pass );
	}

	return $user;

}


/**
 * Process Register Form
 *
 * @since 2.0
 * @param array $data Data sent from the register form
 * @return void
*/
function edd_process_register_form( $data ) {

	if ( is_user_logged_in() ) {
		return;
	}

	if ( empty( $_POST['edd_register_submit'] ) ) {
		return;
	}

	do_action( 'edd_pre_process_register_form' );

	if ( empty( $data['edd_user_login'] ) ) {
		edd_set_error( 'empty_username', __( 'Invalid username', 'easy-digital-downloads' ) );
	}

	if ( username_exists( $data['edd_user_login'] ) ) {
		edd_set_error( 'username_unavailable', __( 'Username already taken', 'easy-digital-downloads' ) );
	}

	if ( ! validate_username( $data['edd_user_login'] ) ) {
		edd_set_error( 'username_invalid', __( 'Invalid username', 'easy-digital-downloads' ) );
	}

	if ( email_exists( $data['edd_user_email'] ) ) {
		edd_set_error( 'email_unavailable', __( 'Email address already taken', 'easy-digital-downloads' ) );
	}

	if ( empty( $data['edd_user_email'] ) || ! is_email( $data['edd_user_email'] ) ) {
		edd_set_error( 'email_invalid', __( 'Invalid email', 'easy-digital-downloads' ) );
	}

	if ( ! empty( $data['edd_payment_email'] ) && $data['edd_payment_email'] != $data['edd_user_email'] && ! is_email( $data['edd_payment_email'] ) ) {
		edd_set_error( 'payment_email_invalid', __( 'Invalid payment email', 'easy-digital-downloads' ) );
	}

	if ( empty( $_POST['edd_user_pass'] ) ) {
		edd_set_error( 'empty_password', __( 'Please enter a password', 'easy-digital-downloads' ) );
	}

	if ( ( ! empty( $_POST['edd_user_pass'] ) && empty( $_POST['edd_user_pass2'] ) ) || ( $_POST['edd_user_pass'] !== $_POST['edd_user_pass2'] ) ) {
		edd_set_error( 'password_mismatch', __( 'Passwords do not match', 'easy-digital-downloads' ) );
	}

	do_action( 'edd_process_register_form' );

	// Check for errors and redirect if none present.
	$errors = edd_get_errors();

	if ( empty( $errors ) ) {

		$redirect = apply_filters( 'edd_register_redirect', $data['edd_redirect'] );

		edd_register_and_login_new_user(
			array(
				'user_login'      => $data['edd_user_login'],
				'user_pass'       => $data['edd_user_pass'],
				'user_email'      => $data['edd_user_email'],
				'user_registered' => date( 'Y-m-d H:i:s' ),
				'role'            => get_option( 'default_role' ),
			)
		);

		wp_redirect( $redirect );
		edd_die();
	}
}
add_action( 'edd_user_register', 'edd_process_register_form' );
