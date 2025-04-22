<?php
/**
 * Register Functions
 *
 * @package     EDD\Users
 * @copyright   Copyright (c) 2018, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Registration Form
 *
 * @since 2.0
 * @global $post
 * @param string $redirect Redirect page URL.
 * @return string Register form.
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
 * Process Register Form
 *
 * @since 2.0
 * @param array $data Data sent from the register form.
 * @return void
 */
function edd_process_register_form( $data ) {

	if ( is_user_logged_in() ) {
		return;
	}

	if ( empty( $data['edd_register_submit'] ) ) {
		return;
	}

	do_action( 'edd_pre_process_register_form' );

	$login = isset( $data['edd_user_login'] ) ? $data['edd_user_login'] : '';
	if ( ! isset( $data['edd_user_login'] ) && isset( $data['edd_user_email'] ) ) {
		$login = $data['edd_user_email'];
	}

	if ( empty( $login ) ) {
		edd_set_error( 'empty_username', __( 'Invalid username', 'easy-digital-downloads' ) );
	}

	if ( username_exists( $login ) ) {
		edd_set_error( 'username_unavailable', __( 'Username already taken', 'easy-digital-downloads' ) );
	}

	if ( ! validate_username( $login ) ) {
		edd_set_error( 'username_invalid', __( 'Invalid username', 'easy-digital-downloads' ) );
	}

	$customers = edd_get_customers(
		array(
			'email'           => $data['edd_user_email'],
			'user_id__not_in' => array( null ),
		)
	);
	if ( ( ! empty( $data['edd_user_email'] ) && email_exists( $data['edd_user_email'] ) ) || ! empty( $customers ) ) {
		edd_set_error( 'email_unavailable', __( 'This email address is not available.', 'easy-digital-downloads' ) );
	}

	if ( empty( $data['edd_user_email'] ) || ! is_email( $data['edd_user_email'] ) ) {
		edd_set_error( 'email_invalid', __( 'Invalid email', 'easy-digital-downloads' ) );
	}

	if ( ! empty( $data['edd_payment_email'] ) && $data['edd_payment_email'] != $data['edd_user_email'] && ! is_email( $data['edd_payment_email'] ) ) {
		edd_set_error( 'payment_email_invalid', __( 'Invalid payment email', 'easy-digital-downloads' ) );
	}

	if ( isset( $data['edd_honeypot'] ) && ! empty( $data['edd_honeypot'] ) ) {
		edd_set_error( 'invalid_form_data', __( 'Registration form validation failed.', 'easy-digital-downloads' ) );
	}

	// Check if password is one or all empty spaces.
	if ( ! empty( $data['edd_user_pass'] ) ) {
		$data['edd_user_pass'] = trim( $data['edd_user_pass'] );
	}

	if ( empty( $data['edd_user_pass'] ) ) {
		edd_set_error( 'empty_password', __( 'The password cannot be a space or all spaces.', 'easy-digital-downloads' ) );
	}

	// Check if password fields do not match.
	if ( ! empty( $data['edd_user_pass'] ) && ( empty( $data['edd_user_pass2'] ) || trim( $data['edd_user_pass2'] ) !== $data['edd_user_pass'] ) ) {
		edd_set_error( 'password_mismatch', __( 'The passwords do not match.', 'easy-digital-downloads' ) );
	}

	do_action( 'edd_process_register_form' );

	// Check for errors and redirect if none present.
	$errors = edd_get_errors();

	if ( empty( $errors ) ) {

		edd_register_and_login_new_user(
			array(
				'user_login' => $login,
				'user_pass'  => $data['edd_user_pass'],
				'user_email' => $data['edd_user_email'],
			)
		);

		edd_set_success( 'account_registration_successful', __( 'Your account has been successfully created.', 'easy-digital-downloads' ) );

		if ( ! empty( $data['edd_redirect'] ) ) {
			$redirect = apply_filters( 'edd_register_redirect', $data['edd_redirect'] );
			edd_redirect( $redirect );
		}
	}
}
add_action( 'edd_user_register', 'edd_process_register_form' );

/**
 * Register And Login New User.
 *
 * @since 1.0.8.1
 *
 * @param array $user_data The data provided by the checkout page's registration form.
 * @return integer
 */
function edd_register_and_login_new_user( $user_data = array() ) {

	// Verify the array.
	if ( empty( $user_data ) ) {
		return -1;
	}

	// Bail if errors.
	if ( edd_get_errors() ) {
		return -1;
	}

	$defaults  = array(
		'user_login' => '',
		'user_pass'  => '',
		'user_email' => '',
		'first_name' => isset( $user_data['user_first'] ) ? $user_data['user_first'] : '',
		'last_name'  => isset( $user_data['user_last'] ) ? $user_data['user_last'] : '',
		'role'       => get_option( 'default_role' ),
	);
	$user_args = wp_parse_args( $user_data, $defaults );
	$user_args = apply_filters(
		'edd_insert_user_args',
		$user_args,
		$user_data
	);

	// Insert new user.
	$user_id = wp_insert_user( $user_args );

	// Validate inserted user.
	if ( is_wp_error( $user_id ) ) {
		return -1;
	}

	// Allow themes and plugins to filter the user data.
	$user_data = apply_filters( 'edd_insert_user_data', $user_data, $user_args );

	// Login new user.
	$user = edd_log_user_in( $user_id, $user_data['user_login'], $user_data['user_pass'] );

	// Allow themes and plugins to hook.
	do_action( 'edd_insert_user', $user_id, $user_data );

	// If we have errors after trying to use wp_signon, return -1.
	if ( edd_get_errors() ) {
		return -1;
	}

	// Return user id.
	return $user->ID;
}
