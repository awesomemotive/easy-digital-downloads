<?php
/**
 * Login Functions
 *
 * @package     EDD
 * @subpackage  Functions/Login
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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
			// First check to see if we're processing a login from a file download that required a login.
			$download_require_login_redirect = EDD()->session->get( 'edd_require_login_to_download_redirect' );
			if ( ! empty( $download_require_login_redirect ) ) {
				$redirect_for_download = edd_get_file_download_login_redirect( $download_require_login_redirect );
				wp_safe_redirect( esc_url( $redirect_for_download ) );
			}

			$default_redirect_url = $data['edd_redirect'];
			if ( has_filter( 'edd_login_redirect' ) ) {
				$user_id = $user instanceof WP_User ? $user->ID : false;
				/**
				 * Filters the URL to which users are redirected to after logging in.
				 *
				 * @since 1.0
				 * @param string $default_redirect_url The URL to which to redirect after logging in.
				 * @param int|false                    User ID. false if no ID is available.
				 */
				wp_redirect( esc_url_raw( apply_filters( 'edd_login_redirect', $default_redirect_url, $user_id ) ) );
			} else {
				wp_safe_redirect( esc_url_raw( $default_redirect_url ) );
			}
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

	/**
	 * Fires before a user is logged in.
	 *
	 * This can be useful for performing actions prior to the user being logged in. Since EDD sparingly logs users in,
	 * it's important to note that this action is not used in most normal WordPress operations, but primarily during checkout,
	 * and with the Auto Register functions.
	 *
	 * @since 3.2.8
	 *
	 * @param int    $user_id    The user ID.
	 * @param string $user_login The user login.
	 */
	do_action( 'edd_pre_log_user_in', $user_id, $user_login );

	$user = wp_signon( $credentials );

	if ( ! $user instanceof WP_User ) {
		edd_set_error(
			'edd_invalid_login',
			sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__( 'Invalid username or password. %1$sReset Password%2$s', 'easy-digital-downloads' ),
				'<a href="' . esc_url( edd_get_lostpassword_url() ) . '">',
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

add_filter( 'login_url', 'edd_update_login_url', 10, 3 );
/**
 * If a login page has been set in the EDD settings,
 * update the WordPress login URL.
 *
 * @param string $url
 * @return string
 */
function edd_update_login_url( $url, $redirect_to, $force_reauth ) {

	// Don't change the login URL if the request is an admin request.
	if ( ! edd_doing_ajax() && is_admin() ) {
		return $url;
	}

	/**
	 * If the $wp_rewrite global hasn't been initialized, don't do anything.
	 * This is added defensively for situations where `wp_login_url` may be called too early.
	 */
	global $wp_rewrite;
	if ( ! $wp_rewrite ) {
		return $url;
	}

	// Get the login page URL and return the default if it's not set.
	$login_url = edd_get_login_page_uri();
	if ( ! $login_url ) {
		return $url;
	}

	if ( ! empty( $redirect ) ) {
		$login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $login_url );
	}
	if ( $force_reauth ) {
		$login_url = add_query_arg( 'reauth', '1', $login_url );
	}

	return $login_url;
}

/**
 * Helper function to get the EDD login page URI.
 *
 * @return false|string
 */
function edd_get_login_page_uri() {
	$login_page = edd_get_option( 'login_page', false );
	if ( ! function_exists( 'has_block' ) || ( $login_page && ! has_block( 'edd/login', absint( $login_page ) ) ) ) {
		return false;
	}

	return $login_page ? get_permalink( $login_page ) : false;
}

/**
 * Generate a redirect URL that is used when file downloads require the user to be logged in.
 *
 * By default uses the homepage, appends a nonce, and an action, and returns a Nonce'd URL
 *
 * @since 3.1
 *
 * @param array $redirect_data The data stored for this specific redirect URL.
 *
 * @return string The URL to use in the redirect process of logging in to download the file.
 */
function edd_get_file_download_login_redirect( $redirect_data ) {
	$login_redirect_page_id = edd_get_option( 'login_redirect_page', false );
	$redirect_base          = ! empty( $login_redirect_page_id ) ? get_permalink( $login_redirect_page_id ) : home_url();

	$token = \EDD\Utils\Tokenizer::tokenize( $redirect_data );

	$redirect_for_download = add_query_arg(
		array(
			'edd_action' => 'process_file_download_after_login',
			'_token'     => $token,
		),
		apply_filters( 'edd_get_file_download_login_redirect_base', $redirect_base )
	);

	return $redirect_for_download;
}
