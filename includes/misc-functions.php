<?php
/**
 * Misc Functions
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Return the base admin-area URL.
 *
 * Use this to avoid typing all of it out a million times.
 *
 * @since 3.0
 *
 * @return string
 */
function edd_get_admin_base_url() {

	// Default args
	$args = array(
		'post_type' => 'download'
	);

	// Default URL
	$admin_url = admin_url( 'edit.php' );

	// Get the base admin URL
	$url = add_query_arg( $args, $admin_url );

	// Filter & return
	return apply_filters( 'edd_get_admin_base_url', $url, $args, $admin_url );
}

/**
 * Get the admin URL, maybe with arguments added
 *
 * @since 3.0
 *
 * @param array $args
 * @return string
 */
function edd_get_admin_url( $args = array() ) {
	return add_query_arg( $args, edd_get_admin_base_url() );
}

/**
 * Is Test Mode
 *
 * @since 1.0
 * @return bool $ret True if test mode is enabled, false otherwise
 */
function edd_is_test_mode() {
	$ret = edd_get_option( 'test_mode', false );

	// Override any setting with the constant.
	if ( edd_is_test_mode_forced() ) {
		$ret = true;
	}

	// At the end of the day, the filter still has the final say.
	return (bool) apply_filters( 'edd_is_test_mode', $ret );
}

/**
 * Is Debug Mode
 *
 * @since 2.8.7
 * @return bool $ret True if debug mode is enabled, false otherwise
 */
function edd_is_debug_mode() {
	$ret = edd_get_option( 'debug_mode', false );
	if( defined( 'EDD_DEBUG_MODE' ) && EDD_DEBUG_MODE ) {
		$ret = true;
	}
	return (bool) apply_filters( 'edd_is_debug_mode', $ret );
}

/**
 * Check the network site URL for signs of being a development environment.
 *
 * @since 3.0
 *
 * @return bool $is_dev_environment True if development environment; otherwise false.
 */
function edd_is_dev_environment() {
	return apply_filters( 'edd_is_dev_environment', in_array( wp_get_environment_type(), array( 'local', 'development' ), true ) );
}

/**
 * Checks if Guest checkout is enabled
 *
 * @since 1.0
 * @return bool $ret True if guest checkout is enabled, false otherwise
 */
function edd_no_guest_checkout() {
	$ret = edd_get_option( 'logged_in_only', false );
	return (bool) apply_filters( 'edd_no_guest_checkout', $ret );
}

/**
 * Checks if users can only purchase downloads when logged in
 *
 * @since 1.0
 * @return bool $ret Whether or not the logged_in_only setting is set
 */
function edd_logged_in_only() {
	$ret = edd_get_option( 'logged_in_only', false );
	return (bool) apply_filters( 'edd_logged_in_only', $ret );
}

/**
 * Redirect to checkout immediately after adding items to the cart?
 *
 * @since 1.4.2
 * @return bool $ret True is redirect is enabled, false otherwise
 */
function edd_straight_to_checkout() {
	$ret = edd_get_option( 'redirect_on_add', false );
	return (bool) apply_filters( 'edd_straight_to_checkout', $ret );
}

/**
 * Disable Redownload
 *
 * @since 1.0.8.2
 * @return bool True if redownloading of files is disabled, false otherwise
 */
function edd_no_redownload() {
	$ret = edd_get_option( 'disable_redownload', false );
	return (bool) apply_filters( 'edd_no_redownload', $ret );
}

/**
 * Verify credit card numbers live?
 *
 * @since 1.4
 * @return bool $ret True is verify credit cards is live
 */
function edd_is_cc_verify_enabled() {
	$ret = true;

	/*
	 * Enable if use a single gateway other than PayPal or Manual. We have to assume it accepts credit cards
	 * Enable if using more than one gateway if they aren't both PayPal and manual, again assuming credit card usage
	 */

	$gateways = edd_get_enabled_payment_gateways();

	if ( count( $gateways ) == 1 && ! isset( $gateways['paypal'] ) && ! isset( $gateways['manual'] ) ) {
		$ret = true;
	} else if ( count( $gateways ) == 1 ) {
		$ret = false;
	} else if ( count( $gateways ) == 2 && isset( $gateways['paypal'] ) && isset( $gateways['manual'] ) ) {
		$ret = false;
	}

	return (bool) apply_filters( 'edd_verify_credit_cards', $ret );
}

/**
 * Is Odd
 *
 * Checks whether an integer is odd.
 *
 * @since 1.0
 * @param int     $int The integer to check
 * @return bool Is the integer odd?
 */
function edd_is_odd( $int ) {
	return (bool) ( $int & 1 );
}

/**
 * Get File Extension
 *
 * Returns the file extension of a filename.
 *
 * @since 1.0
 *
 * @param unknown $str File name
 *
 * @return mixed File extension
 */
function edd_get_file_extension( $str ) {
	$parts = explode( '.', $str );
	$file_extension = end( $parts );

	if ( false !== strpos( $file_extension, '?' ) ) {
		$file_extension = substr( $file_extension, 0, strpos( $file_extension, '?' ) );
	}

	return $file_extension;
}

/**
 * Checks if the string (filename) provided is an image URL
 *
 * @since 1.0
 * @param string  $filename Filename
 * @return bool Whether or not the filename is an image
 */
function edd_string_is_image_url( $filename ) {
	$ext    = edd_get_file_extension( $filename );
	$images = array( 'jpg', 'jpeg', 'png', 'gif', 'webp' );

	return (bool) apply_filters( 'edd_string_is_image', in_array( $ext, $images, true ), $filename );
}

/**
 * Get User IP
 *
 * Returns the IP address of the current visitor
 *
 * @since 1.0.8.2
 * @return string $ip User's IP address
 */
function edd_get_ip() {

	$ip = false;

	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		// Check ip from share internet.
		$ip = filter_var( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ), FILTER_VALIDATE_IP );
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {

		// To check ip is pass from proxy.
		// Can include more than 1 ip, first is the public one.

		// WPCS: sanitization ok.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$ips = explode( ',', wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		if ( is_array( $ips ) ) {
			$ip = filter_var( $ips[0], FILTER_VALIDATE_IP );
		}
	} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = filter_var( wp_unslash( $_SERVER['REMOTE_ADDR'] ), FILTER_VALIDATE_IP );
	}

	$ip = false !== $ip ? $ip : '127.0.0.1';

	// Fix potential CSV returned from $_SERVER variables.
	$ip_array = explode( ',', $ip );
	$ip_array = array_map( 'trim', $ip_array );

	return apply_filters( 'edd_get_ip', $ip_array[0] );
}


/**
 * Get user host
 *
 * Returns the webhost this site is using if possible
 *
 * @since 2.0
 * @return mixed string $host if detected, false otherwise
 */
function edd_get_host() {
	$host = false;

	if( defined( 'WPE_APIKEY' ) ) {
		$host = 'WP Engine';
	} elseif( defined( 'PAGELYBIN' ) ) {
		$host = 'Pagely';
	} elseif( DB_HOST == 'localhost:/tmp/mysql5.sock' ) {
		$host = 'ICDSoft';
	} elseif( DB_HOST == 'mysqlv5' ) {
		$host = 'NetworkSolutions';
	} elseif( strpos( DB_HOST, 'ipagemysql.com' ) !== false ) {
		$host = 'iPage';
	} elseif( strpos( DB_HOST, 'ipowermysql.com' ) !== false ) {
		$host = 'IPower';
	} elseif( strpos( DB_HOST, '.gridserver.com' ) !== false ) {
		$host = 'MediaTemple Grid';
	} elseif( strpos( DB_HOST, '.pair.com' ) !== false ) {
		$host = 'pair Networks';
	} elseif( strpos( DB_HOST, '.stabletransit.com' ) !== false ) {
		$host = 'Rackspace Cloud';
	} elseif( strpos( DB_HOST, '.sysfix.eu' ) !== false ) {
		$host = 'SysFix.eu Power Hosting';
	} elseif( isset( $_SERVER['SERVER_NAME'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ), 'Flywheel' ) !== false ) {
		$host = 'Flywheel';
	} else {

		// Adding a general fallback for data gathering.
		if ( isset( $_SERVER['SERVER_NAME'] ) ) {
			$server_name = sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) );
		}

		$host = 'DBH: ' . DB_HOST . ', SRV: ' . $server_name;
	}

	return $host;
}


/**
 * Check site host
 *
 * @since 2.0
 * @param $host The host to check
 * @return bool true if host matches, false if not
 */
function edd_is_host( $host = false ) {

	$return = false;

	if( $host ) {
		$host = str_replace( ' ', '', strtolower( $host ) );

		switch( $host ) {
			case 'wpengine':
				if( defined( 'WPE_APIKEY' ) )
					$return = true;
				break;
			case 'pagely':
				if( defined( 'PAGELYBIN' ) )
					$return = true;
				break;
			case 'icdsoft':
				if( DB_HOST == 'localhost:/tmp/mysql5.sock' )
					$return = true;
				break;
			case 'networksolutions':
				if( DB_HOST == 'mysqlv5' )
					$return = true;
				break;
			case 'ipage':
				if( strpos( DB_HOST, 'ipagemysql.com' ) !== false )
					$return = true;
				break;
			case 'ipower':
				if( strpos( DB_HOST, 'ipowermysql.com' ) !== false )
					$return = true;
				break;
			case 'mediatemplegrid':
				if( strpos( DB_HOST, '.gridserver.com' ) !== false )
					$return = true;
				break;
			case 'pairnetworks':
				if( strpos( DB_HOST, '.pair.com' ) !== false )
					$return = true;
				break;
			case 'rackspacecloud':
				if( strpos( DB_HOST, '.stabletransit.com' ) !== false )
					$return = true;
				break;
			case 'sysfix.eu':
			case 'sysfix.eupowerhosting':
				if( strpos( DB_HOST, '.sysfix.eu' ) !== false )
					$return = true;
				break;
			case 'flywheel':
				if ( isset( $_SERVER['SERVER_NAME'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ), 'Flywheel' ) !== false )
					$return = true;
				break;
			default:
				$return = false;
		}
	}

	return $return;
}

/**
 * Month Num To Name
 *
 * Takes a month number and returns the name three letter name of it.
 *
 * @since 1.0
 *
 * @param integer $n The number of the month.
 * @param bool    $return_long_name Optional. Return full name of month if true. Default false.
 * @return string Short month name
 */
function edd_month_num_to_name( $n, $return_long_name = false ) {
	$timestamp   = mktime( 0, 0, 0, $n, 1, 2005 );
	$date_format = $return_long_name ? 'F' : 'M';

	return date_i18n( $date_format, $timestamp );
}

/**
 * Get PHP Arg Separator Output
 *
 * @since 1.0.8.3
 * @return string Arg separator output
 */
function edd_get_php_arg_separator_output() {
	return ini_get( 'arg_separator.output' );
}

/**
 * Get the current page URL
 *
 * @since 1.3
 * @param  bool   $nocache  If we should bust cache on the returned URL
 * @return string $page_url Current page URL
 */
function edd_get_current_page_url( $nocache = false ) {

	global $wp;

	if( get_option( 'permalink_structure' ) ) {

		$base = trailingslashit( home_url( $wp->request ) );

	} else {

		$base = add_query_arg( $wp->query_string, '', trailingslashit( home_url( $wp->request ) ) );
		$base = remove_query_arg( array( 'post_type', 'name' ), $base );

	}

	$scheme = is_ssl() ? 'https' : 'http';
	$uri    = set_url_scheme( $base, $scheme );

	if ( is_front_page() ) {
		$uri = home_url( '/' );
	} elseif ( edd_is_checkout() ) {
		$uri = edd_get_checkout_uri();
	}

	$uri = apply_filters( 'edd_get_current_page_url', $uri );

	if ( $nocache ) {
		$uri = edd_add_cache_busting( $uri );
	}

	return $uri;
}

/**
 * Adds the 'nocache' parameter to the provided URL
 *
 * @since  2.4.4
 * @param  string $url The URL being requested
 * @return string      The URL with cache busting added or not
 */
function edd_add_cache_busting( $url = '' ) {

	$no_cache_checkout = edd_get_option( 'no_cache_checkout', false );

	if ( edd_is_caching_plugin_active() || ( edd_is_checkout() && $no_cache_checkout ) ) {
		$url = add_query_arg( 'nocache', 'true', $url );
	}

	return $url;
}

/**
 * Marks a function as deprecated and informs when it has been used.
 *
 * There is a hook edd_deprecated_function_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * This function is to be used in every function that is deprecated.
 *
 * @uses do_action() Calls 'edd_deprecated_function_run' and passes the function name, what to use instead,
 *   and the version the function was deprecated in.
 * @uses apply_filters() Calls 'edd_deprecated_function_trigger_error' and expects boolean value of true to do
 *   trigger or false to not trigger error.
 *
 * @param string  $function    The function that was called
 * @param string  $version     The version of Easy Digital Downloads that deprecated the function
 * @param string  $replacement Optional. The function that should have been called
 * @param array   $backtrace   Optional. Contains stack backtrace of deprecated function
 */
function _edd_deprecated_function( $function, $version, $replacement = null, $backtrace = null ) {
	do_action( 'edd_deprecated_function_run', $function, $replacement, $version );

	if ( _edd_maybe_trigger_deprecation() ) {
		if ( ! is_null( $replacement ) ) {
			trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since Easy Digital Downloads version %2$s! Use %3$s instead.', 'easy-digital-downloads' ), $function, $version, $replacement ) );

			if ( ! empty( $backtrace ) ) {
				trigger_error(  print_r( $backtrace, 1 ) ); // Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
			}

			// Alternatively we could dump this to a file.
		} else {
			trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since Easy Digital Downloads version %2$s with no alternative available.', 'easy-digital-downloads' ), $function, $version ) );

			if ( ! empty( $backtrace ) ) {
				trigger_error( print_r( $backtrace, 1 ) );// Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
			}

			// Alternatively we could dump this to a file.
		}
	}
}

/**
 * Marks an argument in a function deprecated and informs when it's been used
 *
 * There is a hook edd_deprecated_argument_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * This function is to be used in every function that has an argument being deprecated.
 *
 * @uses do_action() Calls 'edd_deprecated_argument_run' and passes the argument, function name, what to use instead,
 *   and the version the function was deprecated in.
 * @uses apply_filters() Calls 'edd_deprecated_argument_trigger_error' and expects boolean value of true to do
 *   trigger or false to not trigger error.
 *
 * @param string  $argument    The arguemnt that is being deprecated
 * @param string  $function    The function that was called
 * @param string  $version     The version of WordPress that deprecated the function
 * @param string  $replacement Optional. The function that should have been called
 * @param array   $backtrace   Optional. Contains stack backtrace of deprecated function
 */
function _edd_deprected_argument( $argument, $function, $version, $replacement = null, $backtrace = null ) {
	do_action( 'edd_deprecated_argument_run', $argument, $function, $replacement, $version );

	if ( _edd_maybe_trigger_deprecation() ) {
		if ( ! is_null( $replacement ) ) {
			trigger_error( sprintf( __( 'The %1$s argument of %2$s is <strong>deprecated</strong> since Easy Digital Downloads version %3$s! Please use %4$s instead.', 'easy-digital-downloads' ), $argument, $function, $version, $replacement ) );

			if ( ! empty( $backtrace ) ) {
				trigger_error(  print_r( $backtrace, 1 ) ); // Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
			}

			// Alternatively we could dump this to a file.
		} else {
			trigger_error( sprintf( __( 'The %1$s argument of %2$s is <strong>deprecated</strong> since Easy Digital Downloads version %3$s with no alternative available.', 'easy-digital-downloads' ), $argument, $function, $version ) );

			if ( ! empty( $backtrace ) ) {
				trigger_error( print_r( $backtrace, 1 ) );// Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
			}
			// Alternatively we could dump this to a file.
		}
	}
}

/**
 * Marks an argument in a function deprecated and informs when it's been used
 *
 * There is a hook edd_deprecated_argument_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * This function is to be used in every function that has an argument being deprecated.
 *
 * @uses do_action() Calls 'edd_deprecated_argument_run' and passes the argument, function name, what to use instead,
 *   and the version the function was deprecated in.
 * @uses apply_filters() Calls 'edd_deprecated_argument_trigger_error' and expects boolean value of true to do
 *   trigger or false to not trigger error.
 *
 * @param string $file        The file that was included.
 * @param string $version     The version of EDD that deprecated the file.
 * @param string $replacement Optional. The file that should have been included based on EDD_PLUGIN_DIR.
 *                            Default null.
 * @param string $message     Optional. A message regarding the change. Default empty.
 */
function _edd_deprecated_file( $file, $version, $replacement = null, $message = '' ) {
	/**
	 * Fires immediately before a deprecated file notice is output.
	 *
	 * @since 3.0
	 *
	 * @param string $file        The file that was included.
	 * @param string $replacement The file that should have been included based on EDD_PLUGIN_DIR.
	 * @param string $version     The version of EDD that deprecated the file.
	 */
	do_action( 'edd_deprecated_file_run', $file, $replacement, $version );

	/**
	 * Filters whether to trigger the error output for deprecated EDD files.
	 *
	 * @since 3.0
	 *
	 * @param bool $show_errors Whether to trigger errors for deprecated files.
	 */
	if ( _edd_maybe_trigger_deprecation() ) {
		$message = empty( $message ) ? '' : ' ' . $message;

		if ( ! is_null( $replacement ) ) {
			/* translators: 1: PHP file name, 2: EDD version number, 3: alternative file name */
			trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since Easy Digital Downloads version %2$s! Use %3$s instead.', 'easy-digital-downloads' ), $file, $version, $replacement ) . $message );
		} else {
			/* translators: 1: PHP file name, 2: EDD version number */
			trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since Easy Digital Downloads version %2$s with no alternative available.', 'easy-digital-downloads' ), $file, $version ) . $message );
		}
	}
}

function _edd_generic_deprecated( $function, $version, $message ) {
	/**
	 * Fires immediately before a generic deprecated notice is output.
	 *
	 * @since 3.0
	 *
	 * @param string function  The function that the deprecation is happening in.
	 * @param string $version  The version of EDD that deprecated the code..
	 * @param string $message  The message to supply for the deprecation.
	 */
	do_action( 'edd_generic_deprecated', $function, $version, $message );

	/**
	 * Filters whether to trigger the error output for the deprecation.
	 *
	 * @since 3.0
	 *
	 * @param bool $show_errors Whether to trigger errors for deprecated calls..
	 */
	if ( _edd_maybe_trigger_deprecation() ) {
		$message = empty( $message ) ? '' : ' ' . $message;

		/* translators: 1: PHP file name, 2: EDD version number */
		trigger_error( sprintf( __( 'Code within %1$s is <strong>deprecated</strong> since Easy Digital Downloads version %2$s. See message for further details.', 'easy-digital-downloads' ), $function, $version ) . $message );
	}
}

/**
 * Determines if we are in an environment which should allow triggering deprecation notices.
 *
 * @since 3.2.0
 *
 * @return bool True if we should trigger deprecation notices, false otherwise.
 */
function _edd_maybe_trigger_deprecation() {
	$env                  = wp_get_environment_type();
	$trigger_environments = array( 'development', 'local' );
	$should_trigger       = in_array( $env, $trigger_environments, true );

	/**
	 * If we are not in an environment that should trigger, but WP_DEBUG is set to true, we'll
	 * check if the user has the permission to manage options. If they do, we'll trigger the
	 * deprecation notice only for those users.
	 */
	if ( ! $should_trigger && ( defined( 'WP_DEBUG' ) && WP_DEBUG ) && current_user_can( 'manage_options' ) ) {
		$should_trigger = true;
	}

	/**
	 * Filters whether to trigger deprecation notices.
	 *
	 * @since 3.2.0
	 *
	 * @param bool $should_trigger Whether to trigger deprecation notices.
	 */
	return apply_filters( 'edd_should_trigger_deprecation_notices', $should_trigger );
}

/**
 * Fires functions attached to a deprecated EDD filter hook.
 *
 * When a filter hook is deprecated, the apply_filters() call is replaced with
 * edd_apply_filters_deprecated(), which triggers a deprecation notice and then fires
 * the original filter hook.
 *
 * @param string $tag         The name of the filter hook.
 * @param array  $args        Array of additional function arguments to be passed to apply_filters().
 * @param string $version     The version of WordPress that deprecated the hook.
 * @param string $replacement Optional. The hook that should have been used. Default false.
 * @param string $message     Optional. A message regarding the change. Default null.
 * @return
 */
function edd_apply_filters_deprecated( $tag, $args, $version, $replacement = false, $message = null ) {
	if ( ! has_filter( $tag ) ) {
		return $args[0];
	}

	_edd_deprecated_hook( $tag, $version, $replacement, $message );

	return apply_filters_ref_array( $tag, $args );
}

/**
 * Fires functions attached to a deprecated EDD action hook.
 *
 * When an action hook is deprecated, the do_action() call is replaced with
 * edd_do_action_deprecated(), which triggers a deprecation notice and then fires
 * the original hook.
 *
 * @param string $tag         The name of the action hook.
 * @param array  $args        Array of additional function arguments to be passed to do_action().
 * @param string $version     The version of WordPress that deprecated the hook.
 * @param string $replacement Optional. The hook that should have been used.
 * @param string $message     Optional. A message regarding the change.
 */
function edd_do_action_deprecated( $tag, $args, $version, $replacement = false, $message = null ) {
	if ( ! has_action( $tag ) ) {
		return;
	}

	_edd_deprecated_hook( $tag, $version, $replacement, $message );

	do_action_ref_array( $tag, $args );
}

/**
 * Marks a deprecated EDD action or filter hook as deprecated and throws a notice.
 *
 * Use the {@see 'edd_deprecated_hook_run'} action to get the backtrace describing where
 * the deprecated hook was called.
 *
 * Default behavior is to trigger a user error if `WP_DEBUG` is true.
 *
 * This function is called by the edd_do_action_deprecated() and edd_apply_filters_deprecated()
 * functions, and so generally does not need to be called directly.
 *
 * @since 3.0
 *
 * @param string $hook        The hook that was used.
 * @param string $version     The version of WordPress that deprecated the hook.
 * @param string $replacement Optional. The hook that should have been used.
 * @param string $message     Optional. A message regarding the change.
 */
function _edd_deprecated_hook( $hook, $version, $replacement = null, $message = null ) {
	/**
	 * Fires when a deprecated EDD hook is called.
	 *
	 * @since 3.0
	 *
	 * @param string $hook        The hook that was called.
	 * @param string $replacement The hook that should be used as a replacement.
	 * @param string $version     The version of WordPress that deprecated the argument used.
	 * @param string $message     A message regarding the change.
	 */
	do_action( 'edd_deprecated_hook_run', $hook, $replacement, $version, $message );

	$show_errors = current_user_can( 'manage_options' );

	/**
	 * Filters whether to trigger deprecated EDD hook errors.
	 *
	 * @since 3.0
	 *
	 * @param bool $trigger Whether to trigger deprecated hook errors. Requires
	 *                      `WP_DEBUG` to be defined true.
	 */
	if ( WP_DEBUG && apply_filters( 'edd_deprecated_hook_trigger_error', $show_errors ) ) {
		$message = empty( $message ) ? '' : ' ' . $message;

		if ( ! is_null( $replacement ) ) {
			/* translators: 1: PHP file name, 2: EDD version number, 3: alternative hook name */
			trigger_error( sprintf( __( 'The %1$s hook is <strong>deprecated</strong> since Easy Digital Downloads version %2$s! Use the %3$s hook instead.', 'easy-digital-downloads' ), $hook, $version, $replacement ) . $message );
		} else {
			/* translators: 1: PHP file name, 2: EDD version number */
			trigger_error( sprintf( __( 'The %1$s hook is <strong>deprecated</strong> since Easy Digital Downloads version %2$s with no alternative available.', 'easy-digital-downloads' ), $hook, $version ) . $message );
		}
	}
}

/**
 * EDD Let To Num
 *
 * Does Size Conversions
 *
 * @since 1.4
 * @usedby edd_settings()
 * @author Chris Christoff
 *
 * @param unknown $v
 * @return int
 */
function edd_let_to_num( $v ) {
	$l   = substr( $v, -1 );
	$ret = substr( $v, 0, -1 );

	switch ( strtoupper( $l ) ) {
		case 'P': // fall-through
		case 'T': // fall-through
		case 'G': // fall-through
		case 'M': // fall-through
		case 'K': // fall-through
			$ret *= 1024;
			break;
		default:
			break;
	}

	return (int) $ret;
}

/**
 * Return the name of base uploads directory.
 *
 * @since 3.0
 *
 * @return string
 */
function edd_get_uploads_base_dir() {
	return 'edd'; // No filter, for now
}

/**
 * Retrieve the URL of the symlink directory
 *
 * @since 1.5
 * @return string $url URL of the symlink directory
 */
function edd_get_symlink_url() {

	// Make sure the symlink directory exists
	edd_get_symlink_dir();

	// Get the URL
	$wp_upload_dir = wp_upload_dir();
	$edd_dir       = edd_get_uploads_base_dir();
	$path          = '/' . $edd_dir . '/symlinks';
	$url           = $wp_upload_dir['baseurl'] . $path;

	// Filter & return
	return apply_filters( 'edd_get_symlink_url', $url );
}

/**
 * Retrieve the absolute path to the symlink directory
 *
 * @since  1.5
 * @return string $path Absolute path to the symlink directory
 */
function edd_get_symlink_dir() {
	$wp_upload_dir = wp_upload_dir();
	$edd_dir       = edd_get_uploads_base_dir();
	$path          = $wp_upload_dir['basedir'] . '/' . $edd_dir . '/symlinks';
	$retval        = apply_filters( 'edd_get_symlink_dir', $path );

	// Make sure the directory exists
	wp_mkdir_p( $retval );

	// Return, possibly filtered
	return $retval;
}

/**
 * Retrieve the absolute path to the file upload directory without the trailing slash
 *
 * @since  1.8
 * @return string $path Absolute path to the EDD upload directory
 */
function edd_get_upload_dir() {
	$wp_upload_dir = wp_upload_dir();
	$edd_dir       = edd_get_uploads_base_dir();
	$path          = $wp_upload_dir['basedir'] . '/' . $edd_dir;
	$retval        =  apply_filters( 'edd_get_upload_dir', $path );

	// Make sure the directory exists
	wp_mkdir_p( $retval );

	// Return, possibly filtered
	return $retval;
}

/**
 * Retrieve the URL to the file upload directory without the trailing slash
 *
 * @since  3.0
 * @return string $purl URL to the EDD upload directory
 */
function edd_get_upload_url() {

	// Make sure the symlink directory exists
	edd_get_upload_dir();

	// Get the URL
	$wp_upload_dir = wp_upload_dir();
	$edd_dir       = edd_get_uploads_base_dir();
	$url           = $wp_upload_dir['baseurl'] . '/' . $edd_dir;

	return apply_filters( 'edd_get_upload_url', $url );
}

/**
 * Determine if the uploads directory is protected, and not publicly accessible.
 *
 * @since 3.0
 *
 * @return bool True if URL returns 200, False if anything else
 */
function edd_is_uploads_url_protected() {
	$transient_key = 'edd_is_uploads_url_protected';
	$protected     = get_transient( $transient_key );

	// No transient
	if ( false === $protected ) {

		// Get the upload path
		$upload_path = edd_get_upload_dir();

		// The upload path is writeable
		if ( wp_is_writable( $upload_path ) ) {

			// Get the file path
			$file_name = wp_unique_filename( $upload_path, 'edd-temp.zip' );
			$file_path = trailingslashit( $upload_path ) . $file_name;

			// Save a temporary file - we will try to access it
			if ( ! file_exists( $file_path ) ) {
				@file_put_contents( $file_path, 'Just testing!' );
			}

			// Setup vars for request
			$upload_url = edd_get_upload_url() . '/' . $file_name;
			$url        = esc_url_raw( $upload_url );
			$args       = array(
				'sslverify'   => false,
				'timeout'     => 2,
				'redirection' => 0
			);

			// Send the request
			$response   = wp_remote_get( $url, $args );
			$code       = wp_remote_retrieve_response_code( $response );
			$protected  = (int) ( 200 !== (int) $code );

			// Delete the temporary file
			if ( file_exists( $file_path ) ) {
				@unlink( $file_path );
			}
		}

		// Set the transient
		set_transient( $transient_key, $protected, 12 * HOUR_IN_SECONDS );
	}

	/**
	 * Filter whether the uploads directory is public or not.
	 *
	 * @since 3.0
	 *
	 * @param string $protected Response code from remote get request
	 */
	return (bool) apply_filters( 'edd_is_uploads_url_protected', $protected );
}

/**
 * Delete symbolic links after they have been used
 *
 * This function is only intended to be used by WordPress cron.
 *
 * @since  1.5
 * @return void
 */
function edd_cleanup_file_symlinks() {

	// Bail if not in WordPress cron
	if ( ! edd_doing_cron() ) {
		return;
	}

	$path = edd_get_symlink_dir();
	$dir = opendir( $path );

	while ( ( $file = readdir( $dir ) ) !== false ) {
		if ( $file == '.' || $file == '..' ) {
			continue;
		}

		$transient = get_transient( md5( $file ) );
		if ( $transient === false ) {
			@unlink( $path . '/' . $file );
		}
	}
}
add_action( 'edd_cleanup_file_symlinks', 'edd_cleanup_file_symlinks' );

/**
 * Checks if SKUs are enabled
 *
 * @since 1.6
 * @author Daniel J Griffiths
 * @return bool $ret True if SKUs are enabled, false otherwise
 */
function edd_use_skus() {
	$ret = edd_get_option( 'enable_skus', false );
	return (bool) apply_filters( 'edd_use_skus', $ret );
}

/**
 * Given an object or array of objects, convert them to arrays
 *
 * @since    1.7
 * @internal Updated in 2.6
 * @param    object|array $object An object or an array of objects
 * @return   array                An array or array of arrays, converted from the provided object(s)
 */
function edd_object_to_array( $object = array() ) {

	if ( empty( $object ) || ( ! is_object( $object ) && ! is_array( $object ) ) ) {
		return $object;
	}

	if ( is_array( $object ) ) {
		$return = array();
		foreach ( $object as $item ) {
			if ( $object instanceof EDD_Payment ) {
				$return[] = $object->array_convert();
			} else {
				$return[] = edd_object_to_array( $item );
			}

		}
	} else {
		if ( $object instanceof EDD_Payment ) {
			$return = $object->array_convert();
		} else {
			$return = get_object_vars( $object );

			// Now look at the items that came back and convert any nested objects to arrays
			foreach ( $return as $key => $value ) {
				$value = ( is_array( $value ) || is_object( $value ) ) ? edd_object_to_array( $value ) : $value;
				$return[ $key ] = $value;
			}
		}
	}

	return $return;

}

/**
 * Set Upload Directory
 *
 * Sets the upload dir to edd. This function is called from
 * edd_change_downloads_upload_dir()
 *
 * @since 1.0
 * @return array Upload directory information
 */
function edd_set_upload_dir( $upload ) {

	// Override the year / month being based on the post publication date, if year/month organization is enabled
	if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
		// Generate the yearly and monthly dirs
		$time = current_time( 'mysql' );
		$y = substr( $time, 0, 4 );
		$m = substr( $time, 5, 2 );
		$upload['subdir'] = "/$y/$m";
	}

	$edd_dir          = edd_get_uploads_base_dir();
	$upload['subdir'] = '/' . $edd_dir . $upload['subdir'];
	$upload['path']   = $upload['basedir'] . $upload['subdir'];
	$upload['url']    = $upload['baseurl'] . $upload['subdir'];
	return $upload;
}

/**
 * Determines the receipt visibility status
 *
 * @param  string $payment_key The payment key.
 * @return bool                Whether the receipt is visible or not.
 */
/**
 * Determines the receipt visibility status
 *
 * @param string  $order_or_key The order object or payment key. Using the payment key will eventually be deprecated.
 * @return bool   whether the receipt is visible or not.
 */
function edd_can_view_receipt( $order_or_key = '' ) {

	$user_can_view = false;

	if ( empty( $order_or_key ) ) {
		return $user_can_view;
	}

	// Fetch order.
	if ( $order_or_key instanceof EDD\Orders\Order ) {
		$order = $order_or_key;
		$key   = $order->payment_key;
	} else {
		$key   = $order_or_key;
		$order = edd_get_order_by( 'payment_key', $key );
	}

	global $edd_receipt_args;

	if ( empty( $order->id ) ) {
		return $user_can_view;
	}

	$edd_receipt_args['id'] = $order->id;

	// Some capabilities can always view the receipt, skip the filter.
	if ( current_user_can( 'edit_shop_payments' ) ) {
		return true;
	}

	if ( is_user_logged_in() ) {
		if ( (int) get_current_user_id() === (int) $order->user_id ) {
			$user_can_view = true;
		} elseif ( wp_get_current_user()->user_email === $order->email ) {
			$user_can_view = true;
		} elseif ( current_user_can( 'view_shop_sensitive_data' ) ) {
			$user_can_view = true;
		}
	} else {
		$session = edd_get_purchase_session();
		if ( ! empty( $session ) ) {
			if ( $session['purchase_key'] === $order->payment_key ) {
				$user_can_view = true;
			}
		}
	}

	return (bool) apply_filters( 'edd_can_view_receipt', $user_can_view, $key, $order );
}

/**
 * Given an order ID, generate a link to IP address provider (ipinfo.io)
 *
 * @since 2.8.15
 * @since 3.0 Updated to use EDD\Orders\Order.
 *
 * @param int $order_id Order ID.
 * @return string A link to the IP details provider
 */
function edd_payment_get_ip_address_url( $order_id ) {
	$order = edd_get_order( $order_id );

	$base_url = 'https://ipinfo.io/';
	$provider_url = '<a href="' . esc_url( $base_url ) . esc_attr( $order->ip ) . '" target="_blank">' . esc_attr( $order->ip ) . '</a>';

	return apply_filters( 'edd_payment_get_ip_address_url', $provider_url, $order->ip, $order->id );
}

/**
 * Abstraction for WordPress cron checking, to avoid code duplication.
 *
 * In future versions of EDD, this function will be changed to only refer to
 * EDD specific cron related jobs. You probably won't want to use it until then.
 *
 * @since 2.8.16
 *
 * @return boolean
 */
function edd_doing_cron() {

	// Bail if doing WordPress cron.
	if ( wp_doing_cron() ) {
		return true;
	}

	// Default to false
	return false;
}

/**
 * Abstraction for WordPress AJAX checking, to avoid code duplication.
 *
 * In future versions of EDD, this function will be changed to only refer to
 * EDD specific AJAX related requests. You probably won't want to use it until then.
 *
 * @since 3.0
 *
 * @return boolean
 */
function edd_doing_ajax() {

	// Bail if doing WordPress AJAX.
	if ( wp_doing_ajax() ) {
		return true;
	}

	// Default to false
	return false;
}

/**
 * Abstraction for WordPress autosave checking, to avoid code duplication.
 *
 * In future versions of EDD, this function will be changed to only refer to
 * EDD specific autosave related requests. You probably won't want to use it until then.
 *
 * @since 3.0
 *
 * @return boolean
 */
function edd_doing_autosave() {

	// Bail if doing WordPress autosave.
	if ( defined( 'DOING_AUTOSAVE' ) && ( true === DOING_AUTOSAVE ) ) {
		return true;
	}

	// Default to false
	return false;
}

/**
 * Abstraction for WordPress Script-Debug checking to avoid code duplication.
 *
 * @since 3.0
 *
 * @return boolean
 */
function edd_doing_script_debug() {
	return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
}

/**
 * Get the bot name. Usually "Store Bot" unless filtered.
 *
 * @since 3.0
 *
 * @return string
 */
function edd_get_bot_name() {
	$retval = esc_html__( 'Store Bot', 'easy-digital-downloads' );

	return (string) apply_filters( 'edd_get_bot_name', $retval );
}

/**
 * Perform a safe, local redirect somewhere inside the current site.
 *
 * On some setups, passing the value of wp_get_referer() may result in an empty
 * value for $location, which results in an error on redirection. If $location
 * is empty, we can safely redirect back to the root. This might change
 * in a future version, possibly to the site root.
 *
 * @since 3.0
 *
 * @param string $location The URL to redirect the user to.
 * @param int    $status   Optional. The numeric code to give in the redirect
 *                         headers. Default: 302.
 */
function edd_redirect( $location = '', $status = 302 ) {

	// Prevent redirects in unit tests.
	if ( edd_is_doing_unit_tests() ) {
		return;
	}

	// Prevent errors from empty $location.
	if ( empty( $location ) ) {
		$location = is_admin()
			? admin_url()
			: home_url();
	}

	// Setup the safe redirect.
	wp_safe_redirect( esc_url_raw( $location ), $status );

	// Exit so the redirect takes place immediately.
	edd_die();
}

/**
 * Checks whether function is disabled.
 *
 * @since 1.3.5
 * @since 3.0.0 String type-checking the `in_array()` call
 *
 * @param string  $function Name of the function.
 * @return bool Whether or not function is disabled.
 */
function edd_is_func_disabled( $function ) {
	$disabled = explode( ',',  @ini_get( 'disable_functions' ) );

	return in_array( $function, $disabled, true );
}

/**
 * Ignore the time limit set by the server (likely from php.ini.)
 *
 * This is usually only necessary during upgrades and exports. If you need to
 * use this function directly, please be careful in doing so.
 *
 * The $time_limit parameter is filterable, but infinite values are not allowed
 * so any erroneous processes are able to terminate normally.
 *
 * @since 3.0.0
 *
 * @param boolean $ignore_user_abort Whether to call ignore_user_about( true )
 * @param int     $time_limit        How long to set the time limit to. Cannot be 0. Default 6 hours.
 */
function edd_set_time_limit( $ignore_user_abort = true, $time_limit = 21600 ) {

	// Default time limit is 6 hours
	$default = HOUR_IN_SECONDS * 6;

	// Only abort if true and if function is enabled
	if ( ( true === $ignore_user_abort ) && ! edd_is_func_disabled( 'ignore_user_abort' ) ) {
		@ignore_user_abort( true );
	}

	/**
	 * Filter the time limit to set for this request.
	 *
	 * Infinite (0) values are not allowed so any erroneous processes are able
	 * to terminate normally.
	 *
	 * @since 3.0
	 *
	 * @param int $time_limit The time limit in nano-seconds. Default 6 hours.
	 *
	 * @returns int $time_limit The filtered time limit value. Default 6 hours.
	 */
	$time_limit = (int) apply_filters( 'edd_set_time_limit', $time_limit );

	// Disallow infinite values
	if ( empty( $time_limit ) ) {
		$time_limit = $default;
	}

	// Set time limit to non-infinite value if function is enabled
	if ( ! edd_is_func_disabled( 'set_time_limit' ) ) {
		@set_time_limit( $time_limit );
	}

	// Attempt to raise the memory limit. See: edd_set_batch_memory_limit()
	wp_raise_memory_limit( 'edd_batch' );
}

/**
 * Set the memory limit for batch processing to 256M
 *
 * @since 3.0
 *
 * @param string $memory_limit 128M by default
 *
 * @return string 256M
 */
function edd_set_batch_memory_limit( $memory_limit = '128M' ) {
	$memory_limit = '256M';

	return $memory_limit;
}
add_filter( 'edd_batch_memory_limit', 'edd_set_batch_memory_limit' );

/**
 * Output the admin area filter bar
 *
 * @since 3.0
 *
 * @param string $context
 */
function edd_admin_filter_bar( $context = '', $item = null ) {

	?><div class="wp-filter" id="edd-filters"><?php

		/**
		 * Fires before filtered items, usually unused
		 *
		 * @since 3.0
		 *
		 * @param string $context
		 */
		do_action( "edd_before_admin_filter_bar_{$context}", $item );

		?><div class="filter-items"><?php

			/**
			 * Output filter bar items, used primarily for selects/inputs/buttons
			 *
			 * @since 3.0
			 *
			 * @param string $context
			 */
			do_action( "edd_admin_filter_bar_{$context}", $item );

		?></div><?php

		/**
		 * Fires after filtered items, usually used by search boxes
		 *
		 * @since 3.0
		 *
		 * @param string $context
		 */
		do_action( "edd_after_admin_filter_bar_{$context}", $item );

	?></div><?php
}

/**
 * Negate an amount.
 *
 * @since 3.0
 *
 * @param float $value Amount to negate.
 * @return float Negated amount.
 */
function edd_negate_amount( $value = 0.00 ) {
	return abs( floatval( $value ) ) * -1;
}

/**
 * Negate an integer
 *
 * @since 3.0
 *
 * @param int $value
 * @return int
 */
function edd_negate_int( $value = 0 ) {
	return intval( $value ) * -1;
}

/**
 * Get the label for a status
 *
 * @since 3.0
 *
 * @param string $status
 *
 * @return string Label for the status
 */
function edd_get_status_label( $status = '' ) {

	// Set a default.
	$status_label = str_replace( '_', ' ', $status );
	$status_label = ucwords( $status_label );

	// If this is a payment label, fetch from `edd_get_payment_status_label()`.
	if ( array_key_exists( $status, edd_get_payment_statuses() ) ) {
		$status_label = edd_get_payment_status_label( $status );
	} else {
		// Otherwise, fetch from generic array. This covers all other non-payment statuses.
		$labels = array(
			// Discounts
			'active'             => __( 'Active', 'easy-digital-downloads' ),
			'inactive'           => __( 'Inactive', 'easy-digital-downloads' ),
			'expired'            => __( 'Expired', 'easy-digital-downloads' ),

			// Common
			'pending'            => __( 'Pending', 'easy-digital-downloads' ),
			'verified'           => __( 'Verified', 'easy-digital-downloads' ),
			'spam'               => __( 'Spam', 'easy-digital-downloads' ),
			'deleted'            => __( 'Deleted', 'easy-digital-downloads' ),
			'cancelled'          => __( 'Cancelled', 'easy-digital-downloads' ),
		);

		// Return the label if set, or uppercase the first letter if not
		if ( isset( $labels[ $status ] ) ) {
			$status_label = $labels[ $status ];
		}
	}

	/**
	 * Filters the label for the provided status.
	 *
	 * @since 3.0
	 *
	 * @param string $status_label Status label.
	 * @param string $status       Provided status key.
	 */
	return apply_filters( 'edd_get_status_label', $status_label, $status );
}

/**
 * Format an array of count objects, using the $groupby key.
 *
 * @since 3.0
 *
 * @param EDD\Database\Query $counts
 * @param string             $groupby
 * @return array
 */
function edd_format_counts( $counts = array(), $groupby = '' ) {

	// Default array
	$c = array(
		'total' => 0
	);

	// Loop through counts and shape return value
	if ( ! empty( $counts->items ) ) {
		// Loop through statuses
		foreach ( $counts->items as $count ) {
			$c[ $count[ $groupby ] ] = absint( $count['count'] );

			// We don't want to include trashed or archived items in the counts.
			if ( ! isset( $count['status'] ) || ! in_array( $count['status'], array( 'trash', 'archived' ), true ) ) {
				$c['total'] += $count['count'];
			}
		}
	}

	// Return array of counts
	return $c;
}

/**
 * Get all payment icon dimensions.
 *
 * This is used because as of EDD 3.0, payment icons are SVGs with specific
 * (and sometimes different) widths and heights.
 *
 * @since 3.0
 *
 * @return array $sizes Sizes array (width and height) of the icon requested.
 */
function edd_get_payment_icon_dimensions( $icon = '' ) {

	// Bail if icon is empty
	if ( empty( $icon ) ) {
		return false;
	}

	// Filter the SVG dimensions
	$sizes = apply_filters( 'edd_get_payment_icon_dimensions', array(
		'mastercard' => array(
			'width'  => 50,
			'height' => 32
		),
		'americanexpress' => array(
			'width'  => 32,
			'height' => 32
		),
		'visa' => array(
			'width'  => 50,
			'height' => 32
		),
		'discover' => array(
			'width'  => 50,
			'height' => 32
		),
		'paypal' => array(
			'width'  => 50,
			'height' => 32
		),
		'amazon' => array(
			'width'  => 50,
			'height' => 32
		),
	) );

	return isset( $sizes[ $icon ] )
		? $sizes[ $icon ]
		: false;
}

/**
 * Return a payment icon
 *
 * @since 3.0
 *
 * @return string SVG markup.
 */
function edd_get_payment_icon( $args = array() ) {

	// Bail if no arguments
	if ( empty( $args ) ) {
		return __( 'Please define default parameters in the form of an array.', 'easy-digital-downloads' );
	}

	// Bail if no icon
	if ( false === array_key_exists( 'icon', $args ) ) {
		return __( 'Please define an SVG icon filename.', 'easy-digital-downloads' );
	}

	// Parse args.
	$args = wp_parse_args( $args, array(
		'icon'     => '',
		'title'    => '',
		'desc'     => '',
		'fallback' => false,
		'width'    => '',
		'height'   => '',
		'classes'  => array()
	) );

	$args['classes'][] = 'icon-' . esc_attr( $args['icon'] );

	// Set aria hidden.
	$aria_hidden = ' aria-hidden="true"';

	// Set ARIA.
	$aria_labelledby = '';

	// Setup the unique ID
	$unique_id = uniqid();

	if ( $args['title'] ) {
		$aria_hidden     = '';
		$aria_labelledby = ' aria-labelledby="title-' . $unique_id . '"';

		if ( $args['desc'] ) {
			$aria_labelledby = ' aria-labelledby="title-' . $unique_id . ' desc-' . $unique_id . '"';
		}
	}

	// Set width and height.
	$width  = ! empty( $args['width']  ) ? ' width="'  . esc_attr( $args['width']  ) . '"' : '';
	$height = ! empty( $args['height'] ) ? ' height="' . esc_attr( $args['height'] ) . '"' : '';

	// Begin SVG markup.
	$svg = '<svg' . $width . $height . ' class="'. implode( ' ', array_filter( $args['classes'] ) ) .'"' . $aria_hidden . $aria_labelledby . ' role="img">';

	// Display the title.
	if ( $args['title'] ) {
		$svg .= '<title id="title-' . $unique_id . '">' . esc_html( $args['title'] ) . '</title>';

		// Display the desc only if the title is already set.
		if ( $args['desc'] ) {
			$svg .= '<desc id="desc-' . $unique_id . '">' . esc_html( $args['desc'] ) . '</desc>';
		}
	}

	/*
	 * Display the icon.
	 *
	 * The whitespace around `<use>` is intentional - it is a workaround to a
	 * keyboard navigation bug in Safari 10.
	 *
	 * See https://core.trac.wordpress.org/ticket/38387.
	 */
	$svg .= ' <use href="#icon-' . esc_attr( $args['icon'] ) . '" xlink:href="#icon-' . esc_attr( $args['icon'] ) . '"></use> ';

	// Add some markup to use as a fallback for browsers that do not support SVGs.
	if ( $args['fallback'] ) {
		$svg .= '<span class="svg-fallback icon-' . esc_attr( $args['icon'] ) . '"></span>';
	}

	$svg .= '</svg>';

	// Return the SVG
	return $svg;
}

/**
 * Output payment gateway icons.
 *
 * @since 3.0
 */
function edd_print_payment_icons( $icons = array() ) {

	// Bail if no icons being requested
	if ( empty( $icons ) ) {
		return;
	} ?>

	<svg style="position: absolute; width: 0; height: 0; overflow: hidden;" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
		<defs>
		<?php

		// Mastercard
		if ( in_array( 'mastercard', $icons, true ) ) : ?>
			<symbol id="icon-<?php echo 'mastercard'; ?>" viewBox="0 0 50 32">
				<rect width="50" height="32"/>
				<path d="m13.827 29.327v-1.804c3e-3 -0.029 4e-3 -0.059 4e-3 -0.088 0-0.576-0.473-1.05-1.049-1.05-0.02 0-0.041 1e-3 -0.061 2e-3 -0.404-0.026-0.792 0.17-1.01 0.511-0.199-0.33-0.564-0.527-0.95-0.511-0.342-0.02-0.671 0.14-0.866 0.421v-0.352h-0.592v2.877h0.583v-1.653c-3e-3 -0.025-4e-3 -0.049-4e-3 -0.073 0-0.38 0.312-0.692 0.692-0.692 0.013 0 0.026 0 0.04 1e-3 0.415 0 0.649 0.271 0.649 0.758v1.656h0.583v-1.65c-2e-3 -0.023-3e-3 -0.047-3e-3 -0.07 0-0.381 0.313-0.695 0.694-0.695 0.012 0 0.025 1e-3 0.037 1e-3 0.427 0 0.655 0.271 0.655 0.758v1.656l0.598-3e-3zm9.368-2.871h-1.046v-0.872h-0.586v0.872h-0.601v0.523h0.601v1.362c0 0.668 0.234 1.064 0.974 1.064 0.276 1e-3 0.547-0.076 0.782-0.222l-0.181-0.511c-0.167 0.1-0.358 0.156-0.553 0.162-0.301 0-0.439-0.192-0.439-0.481v-1.38h1.046l3e-3 -0.517zm5.34-0.072c-0.316-6e-3 -0.613 0.154-0.782 0.421v-0.349h-0.571v2.877h0.577v-1.623c0-0.475 0.229-0.782 0.637-0.782 0.134-2e-3 0.267 0.023 0.391 0.072l0.193-0.544c-0.143-0.051-0.294-0.077-0.445-0.078v6e-3zm-8.072 0.301c-0.354-0.211-0.761-0.315-1.173-0.301-0.727 0-1.172 0.343-1.172 0.902 0 0.469 0.324 0.752 0.968 0.842l0.3 0.042c0.343 0.048 0.529 0.168 0.529 0.331 0 0.222-0.252 0.366-0.679 0.366-0.344 0.012-0.681-0.094-0.956-0.3l-0.301 0.451c0.367 0.249 0.802 0.38 1.245 0.372 0.83 0 1.29-0.384 1.29-0.932 0-0.547-0.352-0.754-0.974-0.844l-0.301-0.042c-0.271-0.036-0.511-0.121-0.511-0.301s0.228-0.355 0.571-0.355c0.317 4e-3 0.627 0.089 0.902 0.247l0.262-0.478zm8.718 1.202c-1e-3 0.024-2e-3 0.048-2e-3 0.071 0 0.787 0.648 1.434 1.434 1.434 0.024 0 0.048 0 0.071-2e-3 0.376 0.02 0.745-0.103 1.034-0.342l-0.3-0.451c-0.216 0.164-0.48 0.255-0.752 0.258-0.5-0.048-0.886-0.473-0.886-0.975 0-0.503 0.386-0.928 0.886-0.976 0.272 3e-3 0.536 0.094 0.752 0.259l0.3-0.451c-0.289-0.24-0.658-0.362-1.034-0.343-0.023-1e-3 -0.047-2e-3 -0.071-2e-3 -0.786 0-1.434 0.648-1.434 1.434 0 0.024 1e-3 0.048 2e-3 0.071v0.015zm-4.047-1.503c-0.841 0-1.422 0.601-1.422 1.503-1e-3 0.03-2e-3 0.059-2e-3 0.088 0 0.777 0.639 1.416 1.416 1.416 0.017 0 0.034 0 0.051-1e-3 0.428 0.015 0.848-0.128 1.178-0.402l-0.301-0.427c-0.237 0.19-0.531 0.296-0.835 0.3-0.435 0.016-0.814-0.305-0.869-0.736h2.149v-0.241c0-0.902-0.547-1.503-1.355-1.503l-0.01 3e-3zm0 0.535h0.025c0.4 0 0.73 0.327 0.733 0.728h-1.542c0.022-0.416 0.378-0.741 0.794-0.728h-0.01zm-7.789 0.971v-1.434h-0.577v0.349c-0.227-0.279-0.573-0.436-0.932-0.421-0.829 0-1.511 0.682-1.511 1.511s0.682 1.51 1.511 1.51c0.359 0.015 0.705-0.141 0.932-0.42v0.348h0.577v-1.443zm-2.33 0c-2e-3 -0.024-3e-3 -0.048-3e-3 -0.072 0-0.495 0.407-0.902 0.902-0.902h3e-3c0.5 0.048 0.886 0.473 0.886 0.976 0 0.502-0.386 0.927-0.886 0.975h-3e-3c-0.495 0-0.902-0.407-0.902-0.902 0-0.024 1e-3 -0.048 3e-3 -0.072v-3e-3zm22.214-1.503c-0.316-6e-3 -0.613 0.154-0.781 0.421v-0.352h-0.572v2.877h0.578v-1.623c0-0.475 0.228-0.782 0.637-0.782 0.134-2e-3 0.266 0.023 0.391 0.072l0.192-0.541c-0.143-0.051-0.293-0.077-0.445-0.078v6e-3zm4.636 2.531c0.039 0 0.078 7e-3 0.114 0.021 0.035 0.015 0.066 0.035 0.093 0.061s0.048 0.056 0.064 0.09c0.03 0.071 0.03 0.151 0 0.222-0.016 0.034-0.037 0.065-0.064 0.09-0.027 0.026-0.058 0.047-0.093 0.061-0.036 0.015-0.075 0.024-0.114 0.024-0.116-1e-3 -0.222-0.069-0.271-0.175-0.03-0.071-0.03-0.151 0-0.222 0.016-0.034 0.037-0.064 0.064-0.09s0.058-0.046 0.093-0.061c0.036-0.017 0.074-0.027 0.114-0.03v9e-3zm0 0.509c0.03 0 0.06-6e-3 0.087-0.019 0.026-0.011 0.05-0.027 0.069-0.048 0.078-0.084 0.078-0.216 0-0.3-0.019-0.021-0.043-0.037-0.069-0.048-0.027-0.012-0.057-0.019-0.087-0.018-0.03 0-0.06 6e-3 -0.087 0.018-0.027 0.011-0.052 0.027-0.072 0.048-0.078 0.084-0.078 0.216 0 0.3 0.02 0.021 0.045 0.037 0.072 0.048 0.028 0.01 0.057 0.014 0.087 0.013v6e-3zm0.018-0.358c0.028-2e-3 0.056 7e-3 0.078 0.024 0.019 0.015 0.029 0.039 0.027 0.063 1e-3 0.02-6e-3 0.04-0.021 0.054-0.017 0.016-0.039 0.025-0.063 0.027l0.087 0.099h-0.069l-0.081-0.099h-0.027v0.099h-0.057v-0.264l0.126-3e-3zm-0.066 0.051v0.072h0.066c0.012 4e-3 0.024 4e-3 0.036 0 4e-3 -8e-3 4e-3 -0.019 0-0.027 4e-3 -9e-3 4e-3 -0.019 0-0.027-0.012-3e-3 -0.024-3e-3 -0.036 0l-0.066-0.018zm-6.804-1.224v-1.44h-0.577v0.349c-0.226-0.279-0.572-0.436-0.932-0.421-0.828 0-1.51 0.682-1.51 1.511s0.682 1.51 1.51 1.51c0.36 0.015 0.706-0.141 0.932-0.42v0.348h0.577v-1.437zm-2.329 0c-2e-3 -0.024-3e-3 -0.048-3e-3 -0.072 0-0.495 0.407-0.902 0.902-0.902h2e-3c0.5 0.048 0.887 0.473 0.887 0.976 0 0.502-0.387 0.927-0.887 0.975h-2e-3c-0.495 0-0.902-0.407-0.902-0.902 0-0.024 1e-3 -0.048 3e-3 -0.072v-3e-3zm8.138 0v-2.6h-0.577v1.503c-0.227-0.279-0.573-0.436-0.932-0.421-0.829 0-1.511 0.682-1.511 1.511s0.682 1.51 1.511 1.51c0.359 0.015 0.705-0.141 0.932-0.42v0.348h0.577v-1.431zm-2.33 0c-2e-3 -0.024-3e-3 -0.048-3e-3 -0.072 0-0.495 0.407-0.902 0.902-0.902h3e-3c0.476 0.073 0.831 0.487 0.831 0.969 0 0.486-0.362 0.902-0.843 0.97h-3e-3c-0.495 0-0.902-0.407-0.902-0.902 0-0.023 1e-3 -0.046 3e-3 -0.069l0.012 6e-3z" fill="#fff" fill-rule="nonzero"/>
				<rect x="20.264" y="4.552" width="9.47" height="17.019" fill="#ff5f00"/>
				<path d="m20.865 13.063c-2e-3 -3.319 1.524-6.46 4.134-8.508-1.906-1.499-4.262-2.314-6.687-2.314-5.938 0-10.823 4.886-10.823 10.823 0 5.938 4.885 10.823 10.823 10.823 2.425 0 4.781-0.815 6.687-2.313-2.611-2.05-4.137-5.192-4.134-8.511z" fill="#eb001b" fill-rule="nonzero"/>
				<path d="m41.486 19.77v-0.349h0.142v-0.072h-0.358v0.072h0.141v0.349h0.075zm0.695 0v-0.421h-0.109l-0.126 0.301-0.126-0.301h-0.108v0.421h0.075v-0.319l0.117 0.274h0.081l0.118-0.274v0.319h0.078z" fill="#f79e1b" fill-rule="nonzero"/>
				<path d="m42.511 13.063c0 5.937-4.885 10.823-10.823 10.823-2.425 0-4.782-0.816-6.689-2.315 2.609-2.051 4.136-5.191 4.136-8.51 0-3.318-1.527-6.459-4.136-8.509 1.907-1.5 4.264-2.315 6.689-2.315 5.938 0 10.823 4.886 10.823 10.823v3e-3z" fill="#f79e1b" fill-rule="nonzero"/>
			</symbol>
		<?php endif;

		// American Express
		if ( in_array( 'americanexpress', $icons, true ) ) : ?>
			<symbol id="icon-<?php echo 'americanexpress'; ?>" viewBox="0 0 32 32">
				<path d="m32 17.318v-17.318h-32v32h32v-9.336c-0.071 0 0-5.346 0-5.346" fill="#006fcf"/>
				<path d="m28.08 15.537h2.423v-5.631h-2.637v0.784l-0.499-0.784h-2.28v0.998l-0.428-0.998h-3.706-0.499c-0.142 0-0.285 0.072-0.427 0.072-0.143 0-0.214 0.071-0.357 0.142-0.142 0.072-0.213 0.072-0.356 0.143v-0.143-0.214h-12.045l-0.356 0.927-0.356-0.927h-2.851v0.998l-0.428-0.998h-2.28l-0.998 2.424v3.207h1.639l0.285-0.784h0.57l0.286 0.784h12.543v-0.713l0.499 0.713h3.492v-0.143-0.285c0.071 0.071 0.214 0.071 0.285 0.143 0.071 0.071 0.214 0.071 0.285 0.142 0.143 0.071 0.285 0.071 0.428 0.071h2.566l0.285-0.783h0.57l0.285 0.783h3.492v-0.712l0.57 0.784zm3.92 7.127v-5.274h-19.599l-0.499 0.712-0.499-0.712h-5.701v5.63h5.701l0.499-0.713 0.499 0.713h3.563v-1.212h-0.142c0.499 0 0.926-0.071 1.283-0.213v1.496h2.565v-0.712l0.499 0.712h10.619c0.428-0.142 0.856-0.213 1.212-0.427z" fill="#fff"/>
				<path d="m30.788 21.31h-1.924v0.784h1.853c0.784 0 1.283-0.499 1.283-1.212s-0.428-1.069-1.14-1.069h-0.856c-0.213 0-0.356-0.143-0.356-0.356 0-0.214 0.143-0.357 0.356-0.357h1.64l0.356-0.784h-1.924c-0.784 0-1.283 0.499-1.283 1.141 0 0.712 0.427 1.069 1.14 1.069h0.855c0.214 0 0.357 0.142 0.357 0.356 0.071 0.285-0.072 0.428-0.357 0.428zm-3.492 0h-1.924v0.784h1.853c0.784 0 1.283-0.499 1.283-1.212s-0.428-1.069-1.141-1.069h-0.855c-0.214 0-0.356-0.143-0.356-0.356 0-0.214 0.142-0.357 0.356-0.357h1.639l0.357-0.784h-1.924c-0.784 0-1.283 0.499-1.283 1.141 0 0.712 0.427 1.069 1.14 1.069h0.855c0.214 0 0.357 0.142 0.357 0.356 0.071 0.285-0.143 0.428-0.357 0.428zm-2.494-2.281v-0.784h-2.994v3.777h2.994v-0.784h-2.138v-0.784h2.067v-0.784h-2.067v-0.712h2.138v0.071zm-4.847 0c0.357 0 0.499 0.214 0.499 0.428 0 0.213-0.142 0.427-0.499 0.427h-1.069v-0.926l1.069 0.071zm-1.069 1.639h0.428l1.14 1.354h1.069l-1.282-1.425c0.641-0.143 0.997-0.57 0.997-1.14 0-0.713-0.499-1.212-1.283-1.212h-1.995v3.777h0.855l0.071-1.354zm-2.28-1.14c0 0.285-0.143 0.499-0.499 0.499h-1.14v-0.998h1.069c0.356 0 0.57 0.214 0.57 0.499zm-2.495-1.283v3.777h0.856v-1.283h1.14c0.784 0 1.354-0.498 1.354-1.282 0-0.713-0.499-1.283-1.283-1.283l-2.067 0.071zm-1.282 3.777h1.069l-1.497-1.924 1.497-1.853h-1.069l-0.927 1.212-0.926-1.212h-1.07l1.497 1.853-1.497 1.853h1.07l0.926-1.212 0.927 1.283zm-3.208-2.993v-0.784h-2.993v3.777h2.993v-0.784h-2.138v-0.784h2.067v-0.784h-2.067v-0.712h2.138v0.071zm17.319-6.699l1.497 2.28h1.069v-3.777h-0.856v2.494l-0.213-0.356-1.355-2.138h-1.14v3.777h0.855v-2.565l0.143 0.285zm-3.706-0.072l0.285-0.784 0.285 0.784 0.356 0.856h-1.282l0.356-0.856zm1.497 2.352h0.926l-1.639-3.777h-1.14l-1.64 3.777h0.927l0.356-0.784h1.853l0.357 0.784zm-3.992 0l0.357-0.784h-0.214c-0.641 0-0.998-0.427-0.998-1.069v-0.071c0-0.641 0.357-1.069 0.998-1.069h0.926v-0.784h-0.997c-1.141 0-1.782 0.784-1.782 1.853v0.071c0 1.141 0.641 1.853 1.71 1.853zm-3.207 0h0.856v-1.71-1.996h-0.856v3.706zm-1.853-2.993c0.357 0 0.499 0.214 0.499 0.428 0 0.213-0.142 0.427-0.499 0.427h-1.069v-0.926l1.069 0.071zm-1.069 1.639h0.428l1.14 1.354h1.069l-1.283-1.425c0.642-0.143 0.998-0.57 0.998-1.14 0-0.713-0.499-1.212-1.283-1.212h-1.995v3.777h0.855l0.071-1.354zm-1.568-1.639v-0.784h-2.993v3.777h2.993v-0.784h-2.138v-0.784h2.067v-0.784h-2.067v-0.712h2.138v0.071zm-6.485 2.993h0.784l1.069-3.064v3.064h0.855v-3.777h-1.425l-0.856 2.566-0.855-2.566h-1.425v3.777h0.855v-3.064l0.998 3.064zm-4.633-2.352l0.285-0.784 0.285 0.784 0.357 0.856h-1.283l0.356-0.856zm1.497 2.352h0.926l-1.639-3.777h-1.069l-1.639 3.777h0.927l0.356-0.784h1.853l0.285 0.784z" fill="#006fcf"/>
			</symbol>
		<?php endif;

		// Visa
		if ( in_array( 'visa', $icons, true ) ) : ?>
			<symbol id="icon-<?php echo 'visa'; ?>" viewBox="0 0 50 32">
				<rect y="4.608" width="50" height="22.794" fill="#fff"/>
				<rect y="27.402" width="50" height="4.608" fill="#f7b600"/>
				<rect width="50" height="4.608" fill="#1a1f71"/>
				<path d="m24.803 9.686l-2.71 12.666h-3.277l2.71-12.666h3.277zm13.786 8.179l1.725-4.757 0.992 4.757h-2.717zm3.658 4.487h3.03l-2.648-12.666h-2.795c-0.63 0-1.161 0.365-1.396 0.928l-4.917 11.738h3.442l0.683-1.892h4.204l0.397 1.892zm-8.555-4.135c0.014-3.343-4.621-3.528-4.59-5.022 0.01-0.454 0.443-0.937 1.389-1.061 0.47-0.06 1.764-0.109 3.232 0.567l0.574-2.687c-0.788-0.285-1.803-0.56-3.065-0.56-3.239 0-5.518 1.721-5.537 4.187-0.02 1.823 1.628 2.84 2.868 3.447 1.278 0.621 1.706 1.02 1.7 1.574-9e-3 0.85-1.019 1.226-1.96 1.24-1.649 0.026-2.604-0.445-3.365-0.8l-0.595 2.777c0.767 0.351 2.18 0.656 3.643 0.672 3.444 0 5.696-1.701 5.706-4.334m-13.572-8.531l-5.309 12.666h-3.464l-2.613-10.109c-0.158-0.621-0.296-0.85-0.778-1.112-0.788-0.429-2.089-0.829-3.233-1.078l0.078-0.367h5.576c0.71 0 1.349 0.472 1.512 1.29l1.38 7.33 3.409-8.62h3.442z" fill="#1a1f71" fill-rule="nonzero"/>
			</symbol>
		<?php endif;

		// Discover
		if ( in_array( 'discover', $icons, true ) ) : ?>
			<symbol id="icon-<?php echo 'discover'; ?>" viewBox="0 0 50 32">
				<rect width="50" height="32" fill="#fff"/>
				<path d="m49.673 17.776s-13.573 9.578-38.433 13.864h38.433v-13.864z" fill="#f58025" fill-rule="nonzero"/>
				<path d="m49.668 0.363v31.274h-49.307c0-0.355-2e-3 -30.917-2e-3 -31.273 0.361 0 48.947-1e-3 49.309-1e-3m0.181-0.363l-49.849 5e-3v31.995h50.029l-3e-3 -32h-0.177z" fill="#231f20" fill-rule="nonzero"/>
				<path d="m6.024 15.23c-0.443 0.401-1.02 0.576-1.932 0.576h-0.379v-4.788h0.379c0.912 0 1.466 0.164 1.932 0.586 0.489 0.435 0.782 1.109 0.782 1.802 0 0.695-0.293 1.39-0.782 1.824m-1.649-5.438h-2.073v7.239h2.062c1.096 0 1.888-0.258 2.583-0.836 0.826-0.683 1.314-1.713 1.314-2.778 0-2.137-1.596-3.625-3.886-3.625" fill="#231f20" fill-rule="nonzero"/>
				<rect x="8.911" y="9.792" width="1.412" height="7.24" fill="#231f20"/>
				<path d="m13.776 12.57c-0.848-0.314-1.096-0.52-1.096-0.912 0-0.456 0.443-0.802 1.052-0.802 0.423 0 0.77 0.174 1.138 0.586l0.739-0.967c-0.607-0.531-1.333-0.803-2.127-0.803-1.281 0-2.258 0.89-2.258 2.074 0 0.998 0.455 1.509 1.781 1.986 0.553 0.195 0.834 0.325 0.976 0.412 0.282 0.184 0.424 0.445 0.424 0.749 0 0.587-0.467 1.021-1.097 1.021-0.673 0-1.216-0.337-1.541-0.965l-0.912 0.878c0.65 0.955 1.432 1.379 2.506 1.379 1.468 0 2.497-0.976 2.497-2.378 0-1.15-0.476-1.67-2.082-2.258" fill="#231f20" fill-rule="nonzero"/>
				<path d="m16.304 13.417c0 2.127 1.67 3.777 3.821 3.777 0.608 0 1.128-0.119 1.77-0.421v-1.663c-0.564 0.565-1.064 0.793-1.705 0.793-1.422 0-2.431-1.031-2.431-2.497 0-1.39 1.041-2.486 2.366-2.486 0.673 0 1.183 0.24 1.77 0.814v-1.661c-0.62-0.315-1.129-0.445-1.737-0.445-2.139 0-3.854 1.684-3.854 3.789" fill="#231f20" fill-rule="nonzero"/>
				<path d="m33.092 14.655l-1.931-4.863h-1.542l3.072 7.425h0.76l3.127-7.425h-1.531l-1.955 4.863z" fill="#231f20" fill-rule="nonzero"/>
				<path d="m37.217 17.031h4.004v-1.225h-2.593v-1.955h2.498v-1.226h-2.498v-1.607h2.593v-1.226h-4.004v7.239z" fill="#231f20" fill-rule="nonzero"/>
				<path d="m43.98 13.125h-0.413v-2.193h0.435c0.88 0 1.358 0.369 1.358 1.073 0 0.728-0.478 1.12-1.38 1.12m2.833-1.196c0-1.355-0.934-2.137-2.562-2.137h-2.094v7.239h1.41v-2.908h0.184l1.955 2.908h1.736l-2.279-3.05c1.064-0.216 1.65-0.944 1.65-2.052" fill="#231f20" fill-rule="nonzero"/>
				<path d="m30.042 13.416c0 2.126-1.722 3.849-3.849 3.849-2.126 0-3.849-1.723-3.849-3.849s1.723-3.849 3.849-3.849c2.127 0 3.849 1.723 3.849 3.849" fill="#f58025" fill-rule="nonzero"/>
				<path d="m47.299 10.022h-0.026v-0.166h0.027c0.075 0 0.115 0.027 0.115 0.082 0 0.056-0.04 0.084-0.116 0.084m0.267-0.087c0-0.126-0.087-0.196-0.24-0.196h-0.205v0.637h0.152v-0.247l0.178 0.247h0.185l-0.209-0.263c0.09-0.024 0.139-0.089 0.139-0.178" fill="#231f20" fill-rule="nonzero"/>
				<path d="m47.354 10.512c-0.243 0-0.442-0.203-0.442-0.455 0-0.253 0.196-0.456 0.442-0.456 0.242 0 0.439 0.207 0.439 0.456 0 0.25-0.197 0.455-0.439 0.455m2e-3 -1.01c-0.309 0-0.554 0.246-0.554 0.554s0.248 0.555 0.554 0.555c0.301 0 0.548-0.25 0.548-0.555 0-0.304-0.247-0.554-0.548-0.554" fill="#231f20" fill-rule="nonzero"/>
			</symbol>
		<?php endif;

		// Paypal
		if ( in_array( 'paypal', $icons, true ) ) : ?>
			<symbol id="icon-<?php echo 'paypal'; ?>" viewBox="0 0 50 32">
				<rect width="50" height="32" fill="#fff"/>
				<path d="m49.639 0.363v31.274h-49.278c0-0.355-2e-3 -30.917-2e-3 -31.273 0.36 0 48.918-1e-3 49.28-1e-3m0.18-0.363l-49.819 5e-3v31.995h50l-3e-3 -32h-0.178z" fill="#ebebeb" fill-rule="nonzero"/>
				<path d="m29.585 23.525c-0.106 0.697-0.638 0.697-1.152 0.697h-0.293l0.205-1.302c0.013-0.079 0.08-0.137 0.16-0.137h0.134c0.351 0 0.681 0 0.852 0.2 0.102 0.12 0.133 0.297 0.094 0.542m-0.224-1.82h-1.941c-0.132 0-0.245 0.097-0.266 0.228l-0.785 4.982c-0.015 0.098 0.061 0.187 0.16 0.187h0.996c0.093 0 0.172-0.068 0.186-0.16l0.223-1.412c0.021-0.131 0.134-0.228 0.266-0.228h0.614c1.279 0 2.017-0.619 2.21-1.847 0.086-0.536 3e-3 -0.958-0.248-1.254-0.276-0.324-0.765-0.496-1.415-0.496" fill="#0f9bdf" fill-rule="nonzero"/>
				<path d="m15.742 23.525c-0.106 0.697-0.638 0.697-1.153 0.697h-0.293l0.205-1.302c0.013-0.079 0.081-0.137 0.16-0.137h0.135c0.35 0 0.681 0 0.851 0.2 0.102 0.12 0.133 0.297 0.095 0.542m-0.224-1.82h-1.941c-0.133 0-0.246 0.097-0.267 0.228l-0.784 4.982c-0.016 0.098 0.06 0.187 0.159 0.187h0.927c0.133 0 0.246-0.097 0.267-0.228l0.211-1.344c0.021-0.131 0.134-0.228 0.267-0.228h0.614c1.278 0 2.016-0.619 2.209-1.847 0.087-0.536 4e-3 -0.958-0.248-1.254-0.276-0.324-0.765-0.496-1.414-0.496" fill="#012f87" fill-rule="nonzero"/>
				<path d="m20.024 25.313c-0.09 0.531-0.511 0.888-1.049 0.888-0.27 0-0.486-0.087-0.624-0.251-0.138-0.163-0.19-0.395-0.146-0.653 0.084-0.527 0.512-0.895 1.042-0.895 0.264 0 0.478 0.087 0.619 0.253 0.143 0.167 0.199 0.401 0.158 0.658m1.295-1.811h-0.929c-0.08 0-0.148 0.058-0.16 0.137l-0.041 0.26-0.065-0.094c-0.201-0.293-0.65-0.39-1.098-0.39-1.027 0-1.904 0.778-2.074 1.871-0.089 0.545 0.037 1.066 0.346 1.429 0.283 0.334 0.688 0.473 1.17 0.473 0.828 0 1.287-0.532 1.287-0.532l-0.042 0.258c-0.015 0.099 0.061 0.188 0.16 0.188h0.837c0.133 0 0.246-0.097 0.266-0.228l0.503-3.185c0.015-0.098-0.06-0.187-0.16-0.187" fill="#012f87" fill-rule="nonzero"/>
				<path d="m33.867 25.313c-0.089 0.531-0.511 0.888-1.049 0.888-0.269 0-0.485-0.087-0.624-0.251-0.137-0.163-0.189-0.395-0.145-0.653 0.083-0.527 0.512-0.895 1.041-0.895 0.264 0 0.479 0.087 0.62 0.253 0.142 0.167 0.198 0.401 0.157 0.658m1.296-1.811h-0.93c-0.079 0-0.147 0.058-0.16 0.137l-0.04 0.26-0.065-0.094c-0.202-0.293-0.65-0.39-1.098-0.39-1.027 0-1.904 0.778-2.075 1.871-0.089 0.545 0.037 1.066 0.346 1.429 0.284 0.334 0.689 0.473 1.171 0.473 0.827 0 1.286-0.532 1.286-0.532l-0.041 0.258c-0.016 0.099 0.06 0.188 0.16 0.188h0.837c0.132 0 0.245-0.097 0.266-0.228l0.503-3.185c0.015-0.098-0.061-0.187-0.16-0.187" fill="#0f9bdf" fill-rule="nonzero"/>
				<path d="m26.269 23.502h-0.934c-0.089 0-0.173 0.045-0.223 0.119l-1.289 1.899-0.546-1.825c-0.034-0.114-0.139-0.193-0.258-0.193h-0.918c-0.111 0-0.189 0.109-0.154 0.214l1.029 3.023-0.967 1.366c-0.076 0.107 0 0.256 0.132 0.256h0.933c0.088 0 0.171-0.044 0.221-0.117l3.107-4.488c0.075-0.107-2e-3 -0.254-0.133-0.254" fill="#012f87" fill-rule="nonzero"/>
				<path d="m36.258 21.842l-0.796 5.073c-0.016 0.098 0.06 0.187 0.159 0.187h0.802c0.132 0 0.245-0.097 0.266-0.228l0.785-4.982c0.016-0.098-0.06-0.187-0.159-0.187h-0.897c-0.08 0-0.147 0.058-0.16 0.137" fill="#0f9bdf" fill-rule="nonzero"/>
				<path d="m31.052 7.735c0.196-1.254-2e-3 -2.107-0.679-2.88-0.746-0.851-2.094-1.215-3.818-1.215h-5.006c-0.352 0-0.652 0.256-0.707 0.605l-2.084 13.228c-0.041 0.261 0.16 0.497 0.424 0.497h3.09l-0.213 1.354c-0.036 0.228 0.14 0.435 0.371 0.435h2.605c0.308 0 0.571-0.225 0.619-0.53l0.025-0.132 0.491-3.114 0.031-0.172c0.049-0.305 0.311-0.53 0.619-0.53h0.39c2.523 0 4.499-1.026 5.076-3.993 0.241-1.24 0.117-2.275-0.521-3.003-0.193-0.22-0.433-0.402-0.713-0.55" fill="#0f9bdf" fill-rule="nonzero"/>
				<path d="m31.052 7.735c0.196-1.254-2e-3 -2.107-0.679-2.88-0.746-0.851-2.094-1.215-3.818-1.215h-5.006c-0.352 0-0.652 0.256-0.707 0.605l-2.084 13.228c-0.041 0.261 0.16 0.497 0.424 0.497h3.09l0.777-4.926-0.024 0.154c0.054-0.348 0.352-0.605 0.704-0.605h1.469c2.884 0 5.143-1.173 5.803-4.565 0.019-0.1 0.036-0.198 0.051-0.293" fill="#072269" fill-rule="nonzero"/>
				<path d="m23.882 7.751c0.033-0.209 0.168-0.381 0.349-0.468 0.082-0.039 0.174-0.061 0.27-0.061h3.924c0.464 0 0.898 0.031 1.294 0.094 0.113 0.019 0.223 0.04 0.33 0.063 0.108 0.024 0.211 0.051 0.312 0.08 0.05 0.015 0.1 0.03 0.148 0.046 0.195 0.065 0.376 0.141 0.543 0.23 0.196-1.254-2e-3 -2.107-0.679-2.88-0.746-0.851-2.094-1.216-3.818-1.216h-5.005c-0.353 0-0.653 0.257-0.708 0.606l-2.084 13.228c-0.041 0.261 0.16 0.497 0.424 0.497h3.09l0.777-4.926 0.833-5.293z" fill="#012f87" fill-rule="nonzero"/>
			</symbol>
		<?php endif;

		// Amazon
		if ( in_array( 'amazon', $icons, true ) ) : ?>
			<symbol id="icon-<?php echo 'amazon'; ?>" viewBox="0 0 50 32">
				<rect width="50" height="32" fill="#eaeded"/>
				<path d="m17.467 7.121c-0.071 0.01-0.148 0.02-0.22 0.031-0.714 0.086-1.358 0.352-1.94 0.771-0.123 0.087-0.235 0.179-0.363 0.276-0.01-0.026-0.02-0.052-0.02-0.072-0.021-0.138-0.041-0.281-0.066-0.419-0.036-0.235-0.154-0.337-0.389-0.337h-0.587c-0.352 0-0.419 0.067-0.419 0.419v10.688c0 0.052 0 0.103 5e-3 0.154 0.011 0.153 0.103 0.25 0.251 0.255 0.357 5e-3 0.72 5e-3 1.077 0 0.148 0 0.24-0.102 0.256-0.255 5e-3 -0.051 5e-3 -0.102 5e-3 -0.154v-3.697c0.056 0.046 0.087 0.072 0.112 0.097 0.914 0.761 1.966 1.011 3.115 0.787 1.042-0.205 1.767-0.843 2.237-1.783 0.357-0.709 0.505-1.465 0.526-2.252 0.025-0.873-0.061-1.731-0.414-2.543-0.434-1.001-1.154-1.66-2.242-1.884-0.163-0.036-0.332-0.051-0.5-0.077-0.143-5e-3 -0.281-5e-3 -0.424-5e-3zm-2.421 2.14c0-0.077 0.021-0.123 0.087-0.169 0.7-0.485 1.471-0.74 2.329-0.674 0.761 0.056 1.384 0.429 1.711 1.323 0.199 0.546 0.25 1.113 0.25 1.685 0 0.531-0.041 1.052-0.204 1.563-0.348 1.087-1.144 1.501-2.176 1.455-0.715-0.031-1.338-0.306-1.91-0.71-0.061-0.046-0.087-0.087-0.087-0.168 6e-3 -0.72 0-1.435 0-2.155 0-0.715 6e-3 -1.43 0-2.15zm10.505-2.14c-0.051 5e-3 -0.102 0.015-0.148 0.02-0.5 0.026-0.991 0.087-1.476 0.21-0.311 0.082-0.613 0.194-0.914 0.296-0.184 0.061-0.276 0.194-0.271 0.393 5e-3 0.169-5e-3 0.337 0 0.506 5e-3 0.245 0.108 0.311 0.348 0.25 0.398-0.102 0.796-0.214 1.2-0.291 0.628-0.117 1.261-0.169 1.899-0.072 0.332 0.052 0.644 0.149 0.858 0.429 0.189 0.246 0.261 0.537 0.271 0.838 0.015 0.424 0.01 0.848 0.015 1.272 0 0.02-5e-3 0.045-0.01 0.071-0.025-5e-3 -0.046 0-0.066-5e-3 -0.536-0.128-1.078-0.22-1.634-0.25-0.577-0.031-1.149 5e-3 -1.701 0.199-0.659 0.23-1.19 0.628-1.501 1.271-0.24 0.501-0.276 1.032-0.2 1.568 0.103 0.715 0.46 1.267 1.093 1.619 0.608 0.337 1.267 0.378 1.936 0.276 0.771-0.118 1.455-0.444 2.058-0.94 0.02-0.02 0.046-0.036 0.082-0.056 0.03 0.194 0.056 0.378 0.091 0.562 0.031 0.158 0.128 0.26 0.276 0.265 0.276 5e-3 0.557 5e-3 0.833 0 0.137-5e-3 0.229-0.097 0.245-0.24 5e-3 -0.046 5e-3 -0.097 5e-3 -0.143v-5.413c0-0.219-0.01-0.439-0.046-0.659-0.097-0.658-0.378-1.2-0.97-1.552-0.343-0.204-0.72-0.307-1.114-0.363-0.184-0.025-0.367-0.041-0.551-0.066-0.199 5e-3 -0.404 5e-3 -0.608 5e-3zm1.787 6.521c0 0.067-0.02 0.113-0.076 0.154-0.572 0.413-1.2 0.689-1.91 0.76-0.291 0.031-0.582 0.021-0.858-0.091-0.322-0.128-0.531-0.353-0.633-0.68-0.102-0.326-0.102-0.664-5e-3 -0.99 0.127-0.424 0.429-0.664 0.837-0.797 0.414-0.133 0.843-0.153 1.267-0.112 0.429 0.035 0.847 0.117 1.276 0.173 0.082 0.01 0.108 0.051 0.108 0.133-6e-3 0.245 0 0.485 0 0.73-6e-3 0.24-0.011 0.48-6e-3 0.72zm6.527 0.317c-0.659-1.823-1.318-3.651-1.976-5.474-0.103-0.292-0.215-0.578-0.322-0.864-0.056-0.148-0.164-0.245-0.327-0.245-0.388-5e-3 -0.776-0.01-1.169-5e-3 -0.128 0-0.189 0.102-0.164 0.23 0.026 0.107 0.056 0.209 0.097 0.312 1.001 2.476 2.007 4.958 3.018 7.43 0.087 0.209 0.108 0.388 0.01 0.603-0.168 0.372-0.301 0.766-0.474 1.138-0.154 0.332-0.409 0.582-0.777 0.68-0.26 0.071-0.521 0.081-0.786 0.056-0.128-0.011-0.255-0.041-0.383-0.051-0.174-0.011-0.261 0.066-0.266 0.245-5e-3 0.168-5e-3 0.337 0 0.505 5e-3 0.281 0.102 0.409 0.378 0.455 0.286 0.051 0.577 0.097 0.863 0.102 0.874 0.02 1.568-0.332 2.017-1.093 0.179-0.301 0.343-0.618 0.47-0.94 1.211-3.053 2.406-6.112 3.606-9.171 0.035-0.092 0.066-0.184 0.081-0.281 0.021-0.143-0.046-0.225-0.189-0.225-0.337-5e-3 -0.679 0-1.016 0-0.189 0-0.322 0.082-0.393 0.266-0.026 0.071-0.056 0.138-0.082 0.209-0.592 1.701-1.185 3.401-1.777 5.107-0.128 0.368-0.26 0.74-0.393 1.134-0.021-0.057-0.031-0.087-0.046-0.123z"/>
				<path d="m7.647 19.594c0.131-0.238 0.295-0.278 0.551-0.142 0.59 0.318 1.169 0.647 1.771 0.948 2.311 1.159 4.725 2.022 7.235 2.629 1.186 0.284 2.379 0.511 3.588 0.67 1.789 0.239 3.589 0.341 5.395 0.296 0.988-0.023 1.976-0.102 2.958-0.216 3.203-0.38 6.298-1.181 9.273-2.43 0.165-0.068 0.335-0.114 0.517-0.068 0.38 0.102 0.511 0.511 0.233 0.789-0.159 0.159-0.358 0.289-0.546 0.42-1.743 1.198-3.645 2.067-5.655 2.72-1.397 0.449-2.817 0.784-4.265 0.999-0.999 0.148-2.01 0.25-3.021 0.273-0.045 0-0.096 0.011-0.142 0.017h-1.198c-0.045-6e-3 -0.096-0.017-0.142-0.017-0.204-0.011-0.408-0.017-0.607-0.023-0.96-0.04-1.914-0.147-2.862-0.301-1.556-0.255-3.078-0.647-4.566-1.187-3.072-1.113-5.826-2.759-8.267-4.94-0.102-0.091-0.171-0.216-0.25-0.323v-0.114zm34.706-0.176c-0.057-0.284-0.272-0.392-0.517-0.471-0.386-0.131-0.789-0.188-1.192-0.222-0.744-0.062-1.488-0.028-2.226 0.108-0.812 0.153-1.584 0.415-2.271 0.886-0.08 0.057-0.159 0.119-0.21 0.199-0.04 0.062-0.052 0.159-0.029 0.227 0.023 0.085 0.119 0.108 0.205 0.102 0.039 0 0.085 0 0.124-6e-3 0.443-0.045 0.881-0.096 1.324-0.142 0.647-0.062 1.3-0.102 1.947-0.051 0.273 0.017 0.551 0.08 0.818 0.154 0.29 0.079 0.42 0.295 0.431 0.59 0.023 0.454-0.079 0.892-0.198 1.323-0.233 0.875-0.568 1.721-0.897 2.561-0.023 0.057-0.046 0.114-0.057 0.171-0.029 0.164 0.068 0.272 0.233 0.232 0.096-0.022 0.204-0.073 0.272-0.142 0.25-0.244 0.506-0.488 0.721-0.761 0.727-0.931 1.153-2.004 1.403-3.157 0.045-0.204 0.079-0.414 0.119-0.619v-0.982z" fill="#f90"/>
				<rect y="30.292" width="50" height="1.684" fill="#f90"/>
			</symbol>
		<?php endif;

		// Lock
		if ( in_array( 'lock', $icons, true ) ) : ?>
			<symbol id="icon-<?php echo 'lock'; ?>" viewBox="0 0 16 16">
				<path d="M5.091,7.273L10.909,7.273L10.909,5.091C10.909,3.489 9.602,2.182 8,2.182C6.398,2.182 5.091,3.489 5.091,5.091L5.091,7.273ZM14.545,8.364L14.545,14.909C14.545,15.511 14.057,16 13.455,16L2.545,16C1.943,16 1.455,15.511 1.455,14.909L1.455,8.364C1.455,7.761 1.943,7.273 2.545,7.273L2.909,7.273L2.909,5.091C2.909,2.295 5.204,0 8,0C10.796,0 13.091,2.295 13.091,5.091L13.091,7.273L13.455,7.273C14.057,7.273 14.545,7.761 14.545,8.364Z" />
			</symbol>
		<?php endif; ?>

		</defs>
	</svg>

	<?php
}

/**
 * Gets the supported card image for a given gateway.
 *
 * @since 3.1
 * @param string $gateway
 * @param string $label
 * @return string
 */
function edd_get_payment_image( $gateway, $label ) {
	if ( edd_string_is_image_url( $gateway ) ) {
		return '<img class="payment-icon" src="' . esc_url( $gateway ) . '" alt="' . esc_attr( $label ) . '"/>';
	}

	$type = '';
	$card = strtolower( str_replace( ' ', '', $label ) );

	if ( has_filter( 'edd_accepted_payment_' . $card . '_image' ) ) {
		$image = apply_filters( 'edd_accepted_payment_' . $card . '_image', '' );
	} elseif ( has_filter( 'edd_accepted_payment_' . $gateway . '_image' ) ) {
		$image = apply_filters( 'edd_accepted_payment_' . $gateway . '_image', '' );
	} else {
		// Set the type to SVG.
		$type = 'svg';

		// Get SVG dimensions.
		$dimensions = edd_get_payment_icon_dimensions( $gateway );

		// Get SVG markup.
		$image = edd_get_payment_icon(
			array(
				'icon'    => $gateway,
				'width'   => $dimensions['width'],
				'height'  => $dimensions['height'],
				'title'   => $label,
				'classes' => array( 'payment-icon' ),
			)
		);
	}

	if ( edd_is_ssl_enforced() || is_ssl() ) {
		$image = edd_enforced_ssl_asset_filter( $image );
	}

	if ( 'svg' === $type ) {
		return $image;
	}

	return '<img class="payment-icon" src="' . esc_url( $image ) . '" alt="' . esc_attr( $label ) . '"/>';
}

/**
 * Gets the date that this EDD install was activated (for new installs).
 * For existing installs, this option is added whenever the function is first used.
 *
 * @since 2.11.4
 * @since 3.1 Checks for the table before checking if orders exist.
 *
 * @return int The timestamp when EDD was marked as activated.
 */
function edd_get_activation_date() {
	$activation_date = get_option( 'edd_activation_date', '' );
	if ( ! $activation_date ) {
		$activation_date = time();

		$orders_table = new EDD\Database\Tables\Orders();

		if ( $orders_table->exists() ) {
			// Gets the first order placed in the store (any status).
			$orders = edd_get_orders(
				array(
					'number'  => 1,
					'orderby' => 'id',
					'order'   => 'ASC',
					'fields'  => 'date_created',
				)
			);
			if ( $orders ) {
				$first_order_date = reset( $orders );
				if ( ! empty( $first_order_date ) ) {
					$activation_date = strtotime( $first_order_date );
				}
			}
		}

		update_option( 'edd_activation_date', $activation_date );
	}

	return $activation_date;
}

/**
 * Given a URL, run it through query arg additions.
 *
 * @since 3.1
 *
 * @param string $base_url    The base URL for the generation.
 * @param array  $query_args  The arguments to add to the $base_url.
 * @param bool   $run_esc_url If true, esc_url will be run
 *
 * @return string.
 */
function edd_link_helper( $base_url = 'https://easydigitaldownloads.com/', $query_args = array(), $run_esc_url = true ) {
	$default_args = array(
		'utm_source'   => 'WordPress',
		'utm_medium'   => '',
		'utm_content'  => '',
		'utm_campaign' => edd_is_pro() ? 'edd-pro' : 'edd',
	);

	$args = wp_parse_args( $query_args, $default_args );

	if ( empty( $args['utm_medium'] ) ) {
		if ( is_admin() ) {
			$screen = get_current_screen();
			if ( $screen ) {
				$args['utm_medium'] = $screen->id;
			}
		} else {

			$template = '';

			if ( is_home() ) {
				$template = get_home_template();
			} elseif ( is_front_page() ) {
				$template = get_front_page_template();
			} elseif ( is_search() ) {
				$template = get_search_template();
			} elseif ( is_single() ) {
				$template = get_single_template();
			} elseif ( is_page() ) {
				$template = get_page_template();
			} elseif ( is_post_type_archive() ) {
				$template = get_post_type_archive_template();
			} elseif ( is_archive() ) {
				$template = get_archive_template();
			}

			$args['utm_medium'] = wp_basename( $template, 'php' );
		}
	}

	// Ensure we sanitize the medium and content.
	$args['utm_medium']  = str_replace( '_', '-', sanitize_title( $args['utm_medium'] ) );
	$args['utm_content'] = str_replace( '_', '-', sanitize_title( $args['utm_content'] ) );

	$url = add_query_arg( $args, trailingslashit( $base_url ) );

	return $run_esc_url ? esc_url( $url ) : $url;
}

/**
 * Whether core blocks are active.
 *
 * @since 3.1.0.2
 * @return bool
 */
function edd_has_core_blocks() {
	return defined( 'EDD_BLOCKS_DIR' );
}

/**
 * Gets the correct namespace for a class/function.
 *
 * @since 3.1.1
 * @param string $extension The rest of the namespace (optional).
 * @return string
 */
function edd_get_namespace( $extension = '' ) {
	$prefix = edd_is_pro() ? '\\EDD\\Pro\\' : '\\EDD\\';

	return $prefix . $extension;
}

/**
 * Whether EDD (Pro) is active.
 * This only checks if the pro code is available; it does not check the validity of a pass.
 *
 * @since 3.1.1
 * @return bool
 */
function edd_is_pro() {

	// This filter is not intended for public consumption. Forcing it to return true will likely have negative consequences.
	return (bool) apply_filters( 'edd_is_pro', EDD()->is_pro() );
}

/**
 * Whether EDD (Pro) is active, but without a valid license key.
 *
 * @since 3.1.1
 * @return bool
 */
function edd_is_inactive_pro() {
	if ( ! edd_is_pro() ) {
		return false;
	}

	$pass_manager = new EDD\Admin\Pass_Manager();

	return ! $pass_manager->isPro();
}

/**
 * Whether unit tests are running.
 *
 * @since 3.1.2
 * @return bool
 */
function edd_is_doing_unit_tests() {
	return (bool) ( ( defined( 'EDD_DOING_TESTS' ) && EDD_DOING_TESTS ) || function_exists( '_manually_load_plugin' ) );
}
