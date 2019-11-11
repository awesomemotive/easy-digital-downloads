<?php
/**
 * Misc Functions
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Is Test Mode
 *
 * @since 1.0
 * @return bool $ret True if test mode is enabled, false otherwise
 */
function edd_is_test_mode() {
	$ret = edd_get_option( 'test_mode', false );
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
	return end( $parts );
}

/**
 * Checks if the string (filename) provided is an image URL
 *
 * @since 1.0
 * @param string  $str Filename
 * @return bool Whether or not the filename is an image
 */
function edd_string_is_image_url( $str ) {
	$ext = edd_get_file_extension( $str );

	switch ( strtolower( $ext ) ) {
		case 'jpg';
			$return = true;
			break;
		case 'png';
			$return = true;
			break;
		case 'gif';
			$return = true;
			break;
		default:
			$return = false;
			break;
	}

	return (bool) apply_filters( 'edd_string_is_image', $return, $str );
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
 * Get Currencies
 *
 * @since 1.0
 * @return array $currencies A list of the available currencies
 */
function edd_get_currencies() {
	$currencies = array(
		'USD'  => __( 'US Dollars (&#36;)', 'easy-digital-downloads' ),
		'EUR'  => __( 'Euros (&euro;)', 'easy-digital-downloads' ),
		'GBP'  => __( 'Pound Sterling (&pound;)', 'easy-digital-downloads' ),
		'AUD'  => __( 'Australian Dollars (&#36;)', 'easy-digital-downloads' ),
		'BRL'  => __( 'Brazilian Real (R&#36;)', 'easy-digital-downloads' ),
		'CAD'  => __( 'Canadian Dollars (&#36;)', 'easy-digital-downloads' ),
		'CZK'  => __( 'Czech Koruna', 'easy-digital-downloads' ),
		'DKK'  => __( 'Danish Krone', 'easy-digital-downloads' ),
		'HKD'  => __( 'Hong Kong Dollar (&#36;)', 'easy-digital-downloads' ),
		'HUF'  => __( 'Hungarian Forint', 'easy-digital-downloads' ),
		'ILS'  => __( 'Israeli Shekel (&#8362;)', 'easy-digital-downloads' ),
		'JPY'  => __( 'Japanese Yen (&yen;)', 'easy-digital-downloads' ),
		'MYR'  => __( 'Malaysian Ringgits', 'easy-digital-downloads' ),
		'MXN'  => __( 'Mexican Peso (&#36;)', 'easy-digital-downloads' ),
		'NZD'  => __( 'New Zealand Dollar (&#36;)', 'easy-digital-downloads' ),
		'NOK'  => __( 'Norwegian Krone', 'easy-digital-downloads' ),
		'PHP'  => __( 'Philippine Pesos', 'easy-digital-downloads' ),
		'PLN'  => __( 'Polish Zloty', 'easy-digital-downloads' ),
		'SGD'  => __( 'Singapore Dollar (&#36;)', 'easy-digital-downloads' ),
		'SEK'  => __( 'Swedish Krona', 'easy-digital-downloads' ),
		'CHF'  => __( 'Swiss Franc', 'easy-digital-downloads' ),
		'TWD'  => __( 'Taiwan New Dollars', 'easy-digital-downloads' ),
		'THB'  => __( 'Thai Baht (&#3647;)', 'easy-digital-downloads' ),
		'INR'  => __( 'Indian Rupee (&#8377;)', 'easy-digital-downloads' ),
		'TRY'  => __( 'Turkish Lira (&#8378;)', 'easy-digital-downloads' ),
		'RIAL' => __( 'Iranian Rial (&#65020;)', 'easy-digital-downloads' ),
		'RUB'  => __( 'Russian Rubles', 'easy-digital-downloads' ),
		'AOA'  => __( 'Angolan Kwanza', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_currencies', $currencies );
}

/**
 * Get the store's set currency
 *
 * @since 1.5.2
 * @return string The currency code
 */
function edd_get_currency() {
	$currency = edd_get_option( 'currency', 'USD' );
	return apply_filters( 'edd_currency', $currency );
}

/**
 * Given a currency determine the symbol to use. If no currency given, site default is used.
 * If no symbol is determine, the currency string is returned.
 *
 * @since  2.2
 * @param  string $currency The currency string
 * @return string           The symbol to use for the currency
 */
function edd_currency_symbol( $currency = '' ) {
	if ( empty( $currency ) ) {
		$currency = edd_get_currency();
	}

	switch ( $currency ) :
		case "GBP" :
			$symbol = '&pound;';
			break;
		case "BRL" :
			$symbol = 'R&#36;';
			break;
		case "EUR" :
			$symbol = '&euro;';
			break;
		case "USD" :
		case "AUD" :
		case "NZD" :
		case "CAD" :
		case "HKD" :
		case "MXN" :
		case "SGD" :
			$symbol = '&#36;';
			break;
		case "JPY" :
			$symbol = '&yen;';
			break;
		case "AOA" :
			$symbol = 'Kz';
			break;
		default :
			$symbol = $currency;
			break;
	endswitch;

	return apply_filters( 'edd_currency_symbol', $symbol, $currency );
}

/**
 * Get the name of a currency
 *
 * @since 2.2
 * @param  string $code The currency code
 * @return string The currency's name
 */
function edd_get_currency_name( $code = 'USD' ) {
	$currencies = edd_get_currencies();
	$name       = isset( $currencies[ $code ] ) ? $currencies[ $code ] : $code;
	return apply_filters( 'edd_currency_name', $name );
}

/**
 * Month Num To Name
 *
 * Takes a month number and returns the name three letter name of it.
 *
 * @since 1.0
 *
 * @param integer $n
 * @return string Short month name
 */
function edd_month_num_to_name( $n ) {
	$timestamp = mktime( 0, 0, 0, $n, 1, 2005 );

	return date_i18n( "M", $timestamp );
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

	$show_errors = current_user_can( 'manage_options' );

	// Allow plugin to filter the output error trigger
	if ( WP_DEBUG && apply_filters( 'edd_deprecated_function_trigger_error', $show_errors ) ) {
		if ( ! is_null( $replacement ) ) {
			trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since Easy Digital Downloads version %2$s! Use %3$s instead.', 'easy-digital-downloads' ), $function, $version, $replacement ) );
			trigger_error(  print_r( $backtrace, 1 ) ); // Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
			// Alternatively we could dump this to a file.
		} else {
			trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since Easy Digital Downloads version %2$s with no alternative available.', 'easy-digital-downloads' ), $function, $version ) );
			trigger_error( print_r( $backtrace, 1 ) );// Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
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

	$show_errors = current_user_can( 'manage_options' );

	// Allow plugin to filter the output error trigger
	if ( WP_DEBUG && apply_filters( 'edd_deprecated_argument_trigger_error', $show_errors ) ) {
		if ( ! is_null( $replacement ) ) {
			trigger_error( sprintf( __( 'The %1$s argument of %2$s is <strong>deprecated</strong> since Easy Digital Downloads version %3$s! Please use %4$s instead.', 'easy-digital-downloads' ), $argument, $function, $version, $replacement ) );
			trigger_error(  print_r( $backtrace, 1 ) ); // Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
			// Alternatively we could dump this to a file.
		} else {
			trigger_error( sprintf( __( 'The %1$s argument of %2$s is <strong>deprecated</strong> since Easy Digital Downloads version %3$s with no alternative available.', 'easy-digital-downloads' ), $argument, $function, $version ) );
			trigger_error( print_r( $backtrace, 1 ) );// Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
			// Alternatively we could dump this to a file.
		}
	}
}


/**
 * Checks whether function is disabled.
 *
 * @since 1.3.5
 *
 * @param string  $function Name of the function.
 * @return bool Whether or not function is disabled.
 */
function edd_is_func_disabled( $function ) {
	$disabled = explode( ',',  ini_get( 'disable_functions' ) );

	return in_array( $function, $disabled );
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
 * Retrieve the URL of the symlink directory
 *
 * @since 1.5
 * @return string $url URL of the symlink directory
 */
function edd_get_symlink_url() {
	$wp_upload_dir = wp_upload_dir();
	wp_mkdir_p( $wp_upload_dir['basedir'] . '/edd/symlinks' );
	$url = $wp_upload_dir['baseurl'] . '/edd/symlinks';

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
	wp_mkdir_p( $wp_upload_dir['basedir'] . '/edd/symlinks' );
	$path = $wp_upload_dir['basedir'] . '/edd/symlinks';

	return apply_filters( 'edd_get_symlink_dir', $path );
}

/**
 * Retrieve the absolute path to the file upload directory without the trailing slash
 *
 * @since  1.8
 * @return string $path Absolute path to the EDD upload directory
 */
function edd_get_upload_dir() {
	$wp_upload_dir = wp_upload_dir();
	wp_mkdir_p( $wp_upload_dir['basedir'] . '/edd' );
	$path = $wp_upload_dir['basedir'] . '/edd';

	return apply_filters( 'edd_get_upload_dir', $path );
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
		if ( $file == '.' || $file == '..' )
			continue;

		$transient = get_transient( md5( $file ) );
		if ( $transient === false )
			@unlink( $path . '/' . $file );
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
 * Retrieve timezone
 *
 * @since 1.6
 * @return string $timezone The timezone ID
 */
function edd_get_timezone_id() {

	// if site timezone string exists, return it
	if ( $timezone = get_option( 'timezone_string' ) )
		return $timezone;

	// get UTC offset, if it isn't set return UTC
	if ( ! ( $utc_offset = 3600 * get_option( 'gmt_offset', 0 ) ) )
		return 'UTC';

	// attempt to guess the timezone string from the UTC offset
	$timezone = timezone_name_from_abbr( '', $utc_offset );

	// last try, guess timezone string manually
	if ( $timezone === false ) {

		$is_dst = date( 'I' );

		foreach ( timezone_abbreviations_list() as $abbr ) {
			foreach ( $abbr as $city ) {
				if ( $city['dst'] == $is_dst &&  $city['offset'] == $utc_offset )
					return $city['timezone_id'];
			}
		}
	}

	// fallback
	return 'UTC';
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

	$upload['subdir'] = '/edd' . $upload['subdir'];
	$upload['path']   = $upload['basedir'] . $upload['subdir'];
	$upload['url']    = $upload['baseurl'] . $upload['subdir'];
	return $upload;
}

/**
 * Check if the upgrade routine has been run for a specific action
 *
 * @since  2.3
 * @param  string $upgrade_action The upgrade action to check completion for
 * @return bool                   If the action has been added to the copmleted actions array
 */
function edd_has_upgrade_completed( $upgrade_action = '' ) {

	if ( empty( $upgrade_action ) ) {
		return false;
	}

	$completed_upgrades = edd_get_completed_upgrades();

	return in_array( $upgrade_action, $completed_upgrades );

}

/**
 * Get's the array of completed upgrade actions
 *
 * @since  2.3
 * @return array The array of completed upgrades
 */
function edd_get_completed_upgrades() {

	$completed_upgrades = get_option( 'edd_completed_upgrades' );

	if ( false === $completed_upgrades ) {
		$completed_upgrades = array();
	}

	return $completed_upgrades;

}


if ( ! function_exists( 'cal_days_in_month' ) ) {
	// Fallback in case the calendar extension is not loaded in PHP
	// Only supports Gregorian calendar
	function cal_days_in_month( $calendar, $month, $year ) {
		return date( 't', mktime( 0, 0, 0, $month, 1, $year ) );
	}
}


if ( ! function_exists( 'hash_equals' ) ) :
/**
 * Compare two strings in constant time.
 *
 * This function was added in PHP 5.6.
 * It can leak the length of a string.
 *
 * @since 2.2.1
 *
 * @param string $a Expected string.
 * @param string $b Actual string.
 * @return bool Whether strings are equal.
 */
function hash_equals( $a, $b ) {
	$a_length = strlen( $a );
	if ( $a_length !== strlen( $b ) ) {
		return false;
	}
	$result = 0;

	// Do not attempt to "optimize" this.
	for ( $i = 0; $i < $a_length; $i++ ) {
		$result |= ord( $a[ $i ] ) ^ ord( $b[ $i ] );
	}

	return $result === 0;
}
endif;

if ( ! function_exists( 'getallheaders' ) ) :

	/**
	 * Retrieve all headers
	 *
	 * Ensure getallheaders function exists in the case we're using nginx
	 *
	 * @since  2.4
	 * @return array
	 */
	function getallheaders() {
		$headers = array();
		foreach ( $_SERVER as $name => $value ) {
			if ( substr( $name, 0, 5 ) == 'HTTP_' ) {
				$headers[ str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value;
			}
		}
		return $headers;
	}

endif;

/**
 * Determines the receipt visibility status
 *
 * @return bool Whether the receipt is visible or not.
 */
function edd_can_view_receipt( $payment_key = '' ) {

	$return = false;

	if ( empty( $payment_key ) ) {
		return $return;
	}

	global $edd_receipt_args;

	$edd_receipt_args['id'] = edd_get_purchase_id_by_key( $payment_key );

	$user_id = (int) edd_get_payment_user_id( $edd_receipt_args['id'] );

	$payment_meta = edd_get_payment_meta( $edd_receipt_args['id'] );

	if ( is_user_logged_in() ) {
		if ( $user_id === (int) get_current_user_id() ) {
			$return = true;
		} elseif ( wp_get_current_user()->user_email === edd_get_payment_user_email( $edd_receipt_args['id'] ) ) {
			$return = true;
		} elseif ( current_user_can( 'view_shop_sensitive_data' ) ) {
			$return = true;
		}
	}

	$session = edd_get_purchase_session();
	if ( ! empty( $session ) && ! is_user_logged_in() ) {
		if ( $session['purchase_key'] === $payment_meta['key'] ) {
			$return = true;
		}
	}

	return (bool) apply_filters( 'edd_can_view_receipt', $return, $payment_key );
}

/**
 * Given a Payment ID, generate a link to IP address provider (ipinfo.io)
 *
 * @since  2.8.15
 * @param  int      $payment_id The Payment ID
 * @return string   A link to the IP details provider
 */
function edd_payment_get_ip_address_url( $payment_id ) {

	$payment = new EDD_Payment( $payment_id );

	$base_url = 'https://ipinfo.io/';
	$provider_url = '<a href="' . esc_url( $base_url ) . esc_attr( $payment->ip ) . '" target="_blank">' . esc_html( $payment->ip ) . '</a>';

	return apply_filters( 'edd_payment_get_ip_address_url', $provider_url, $payment->ip, $payment_id );

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

	// Bail if not doing WordPress cron (>4.8.0)
	if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
		return true;

	// Bail if not doing WordPress cron (<4.8.0)
	} elseif ( defined( 'DOING_CRON' ) && ( true === DOING_CRON ) ) {
		return true;
	}

	// Default to false
	return false;
}

/**
 * Check to see if we should be displaying promotional content
 *
 * In various parts of the plugin, we may choose to promote something like a sale for a limited time only. This
 * function should be used to set the conditions under which the promotions will display.
 *
 * @since 2.9.20
 *
 * @return bool
 */
function edd_is_promo_active() {

	// Set the date/time range based on UTC.
	$start = strtotime( '2019-11-29 06:00:00' );
	$end   = strtotime( '2019-12-07 05:59:59' );
	$now   = time();

	// Only display sidebar if the page is loaded within the date range.
	if ( ( $now > $start ) && ( $now < $end ) ) {
		return true;
	}

	return false;
}

/**
 * Polyfills for is_countable and is_iterable
 *
 * This helps with plugin compatibility going forward. Many extensions have issues with more modern PHP versions,
 * however unless teh customer is running WP 4.9.6 or PHP 7.3, we cannot use these functions.
 *
 */
if ( ! function_exists( 'is_countable' ) ) {
	/**
	 * Polyfill for is_countable() function added in PHP 7.3 or WP 4.9.6.
	 *
	 * Verify that the content of a variable is an array or an object
	 * implementing the Countable interface.
	 *
	 * @since 2.9.17
	 *
	 * @param mixed $var The value to check.
	 *
	 * @return bool True if `$var` is countable, false otherwise.
	 */
	function is_countable( $var ) {
		return ( is_array( $var )
		         || $var instanceof Countable
		         || $var instanceof SimpleXMLElement
		         || $var instanceof ResourceBundle
		);
	}
}

if ( ! function_exists( 'is_iterable' ) ) {
	/**
	 * Polyfill for is_iterable() function added in PHP 7.1  or WP 4.9.6.
	 *
	 * Verify that the content of a variable is an array or an object
	 * implementing the Traversable interface.
	 *
	 * @since 2.9.17
	 *
	 * @param mixed $var The value to check.
	 *
	 * @return bool True if `$var` is iterable, false otherwise.
	 */
	function is_iterable( $var ) {
		return ( is_array( $var ) || $var instanceof Traversable );
	}
}
