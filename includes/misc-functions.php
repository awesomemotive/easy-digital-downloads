<?php
/**
 * Misc Functions
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Is Test Mode
 *
 * @since 1.0
 * @global $edd_options
 * @return bool $ret True if return mode is enabled, false otherwise
 */
function edd_is_test_mode() {
	global $edd_options;

	if ( ! isset( $edd_options['test_mode'] ) || is_null( $edd_options['test_mode'] ) )
		$ret = false;
	else
		$ret = true;

	return (bool) apply_filters( 'edd_is_test_mode', $ret );
}

/**
 * Checks if Guest checkout is enabled
 *
 * @since 1.0
 * @global $edd_options
 * @return bool $ret True if guest checkout is enabled, false otherwise
 */
function edd_no_guest_checkout() {
	global $edd_options;

	if ( isset( $edd_options['logged_in_only'] ) )
		$ret = true;
	else
		$ret = false;

	return (bool) apply_filters( 'edd_no_guest_checkout', $ret );
}

/**
 * Checks if users can only purchase downloads when logged in
 *
 * @since 1.0
 * @global $edd_options
 * @return bool $ret Wheter or not the logged_in_only setting is set
 */
function edd_logged_in_only() {
	global $edd_options;

	if ( isset( $edd_options['logged_in_only'] ) )
		$ret = true;
	else
		$ret = false;

	return (bool) apply_filters( 'edd_logged_in_only', $ret );
}

/**
 * Redirect to checkout immediately after adding items to the cart?
 *
 * @since 1.4.2
 * @return bool $ret True is redirect is enabled, false otherwise
 */
function edd_straight_to_checkout() {
	global $edd_options;
	$ret = isset( $edd_options['redirect_on_add'] );
	return (bool) apply_filters( 'edd_straight_to_checkout', $ret );
}

/**
 * Disable Redownload
 *
 * @access public
 * @since 1.0.8.2
 * @global $edd_options
 * @return bool True if redownloading of files is disabled, false otherwise
 */
function edd_no_redownload() {
	global $edd_options;

	if ( isset( $edd_options['disable_redownload'] ) )
		return true;

	return (bool) apply_filters( 'edd_no_redownload', false );
}

/**
 * Verify credit card numbers live?
 *
 * @since 1.4
 * @global $edd_options
 * @return bool $ret True is verify credit cards is live
 */
function edd_is_cc_verify_enabled() {
	global $edd_options;

	$ret = true;

	/*
	 * Enable if use a single gateway other than PayPal or Manual. We have to assume it accepts cerdit cards
	 * Enable if using more than one gateway if they aren't both PayPal and manual, again assuming credit card usage
	 */

	$gateways = edd_get_enabled_payment_gateways();

	if ( count( $gateways ) == 1 && ! isset( $gateways['paypal'] ) && ! isset( $gateways['manual'] ) )
		$ret = true;
	else if ( count( $gateways ) == 1 )
		$ret = false;
	else if ( count( $gateways ) == 2 && isset( $gateways['paypal'] ) && isset( $gateways['manual'] ) )
		$ret = false;

	if ( isset( $edd_options['edd_is_cc_verify_enabled'] ) )
		$ret = false; // Global override

	return (bool) apply_filters( 'edd_verify_credit_cards', $ret );
}

/**
 * Is Odd
 *
 * Checks wether an integer is odd.
 *
 * @since 1.0
 * @param int $int The integer to check
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
 * @param string $string Filename
 * @return string $parts File extension
 */
function edd_get_file_extension( $str ) {
   $parts = explode( '.', $str );
   return end( $parts );
}

/**
 * Checks if the string (filename) provided is an image URL
 *
 * @since 1.0
 * @param string $str Filename
 * @return bool Whether or not the filename is an image
 */
function edd_string_is_image_url( $str ) {
	$ext = edd_get_file_extension( $str );

	switch( strtolower( $ext ) ) {
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
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		//check ip from share internet
	  $ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		//to check ip is pass from proxy
	  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
	  $ip = $_SERVER['REMOTE_ADDR'];
	}
	return apply_filters( 'edd_get_ip', $ip );
}

/**
 * Get Currencies
 *
 * @since 1.0
 * @return array $currencies A list of the available currencies
 */
function edd_get_currencies() {
	$currencies = array(
		'USD'  => __( 'US Dollars (&#36;)', 'edd' ),
		'EUR'  => __( 'Euros (&euro;)', 'edd' ),
		'GBP'  => __( 'Pounds Sterling (&pound;)', 'edd' ),
		'AUD'  => __( 'Australian Dollars (&#36;)', 'edd' ),
		'BRL'  => __( 'Brazilian Real (R&#36;)', 'edd' ),
		'CAD'  => __( 'Canadian Dollars (&#36;)', 'edd' ),
		'CZK'  => __( 'Czech Koruna', 'edd' ),
		'DKK'  => __( 'Danish Krone', 'edd' ),
		'HKD'  => __( 'Hong Kong Dollar (&#36;)', 'edd' ),
		'HUF'  => __( 'Hungarian Forint', 'edd' ),
		'ILS'  => __( 'Israeli Shekel (&#8362;)', 'edd' ),
		'JPY'  => __( 'Japanese Yen (&yen;)', 'edd' ),
		'RM'   => __( 'Malaysian Ringgits', 'edd' ),
		'MXN'  => __( 'Mexican Peso (&#36;)', 'edd' ),
		'NZD'  => __( 'New Zealand Dollar (&#36;)', 'edd' ),
		'NOK'  => __( 'Norwegian Krone', 'edd' ),
		'PHP'  => __( 'Philippine Pesos', 'edd' ),
		'PLN'  => __( 'Polish Zloty', 'edd' ),
		'SGD'  => __( 'Singapore Dollar (&#36;)', 'edd' ),
		'SEK'  => __( 'Swedish Krona', 'edd' ),
		'CHF'  => __( 'Swiss Franc', 'edd' ),
		'TWD'  => __( 'Taiwan New Dollars', 'edd' ),
		'THB'  => __( 'Thai Baht (&#3647;)', 'edd' ),
		'INR'  => __( 'Indian Rupee (&#8377;)', 'edd' ),
		'TRY'  => __( 'Turkish Lira (&#8378;)', 'edd' ),
		'RIAL' => __( 'Iranian Rial (&#65020;)', 'edd' )
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
	global $edd_options;
	$currency = isset( $edd_options['currency'] ) ? $edd_options['currency'] : 'USD';
	return apply_filters( 'edd_currency', $currency );
}


/**
 * Month Num To Name
 *
 * Takes a month number and returns the name three letter name of it.
 *
 * @since 1.0
 * @return string Short month name
 */
function edd_month_num_to_name( $n ) {
	$timestamp = mktime( 0, 0, 0, $n, 1, 2005 );

	return date_i18n( "M", $timestamp );
}

/**
 * Get PHP Arg Separator Ouput
 *
 * @since 1.0.8.3
 * @return string Arg separator output
*/
function edd_get_php_arg_separator_output() {
	return ini_get('arg_separator.output');
}

/**
 * Get the current page URL
 *
 * @since 1.3
 * @global $post
 * @return string $page_url Current page URL
 */
function edd_get_current_page_url() {
	global $post;

	if ( is_front_page() ) :
		$page_url = home_url();
	else :
		$page_url = 'http';

		if ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" )
			$page_url .= "s";

		$page_url .= "://";

		if ( $_SERVER["SERVER_PORT"] != "80" )
			$page_url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		else
			$page_url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	endif;

	return apply_filters( 'edd_get_current_page_url', esc_url( $page_url ) );
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
 * @param string $function The function that was called
 * @param string $version The version of WordPress that deprecated the function
 * @param string $replacement Optional. The function that should have been called
 * @param array $backtrace Optional. Contains stack backtrace of deprecated function
 */
function _edd_deprecated_function( $function, $version, $replacement = null, $backtrace = null ) {
	do_action( 'edd_deprecated_function_run', $function, $replacement, $version );

	$show_errors = current_user_can( 'manage_options' );

	// Allow plugin to filter the output error trigger
	if ( WP_DEBUG && apply_filters( 'edd_deprecated_function_trigger_error', $show_errors ) ) {
		if ( ! is_null( $replacement ) ) {
			trigger_error( sprintf( __('%1$s is <strong>deprecated</strong> since Easy Digital Downloads version %2$s! Use %3$s instead.', 'edd' ), $function, $version, $replacement ) );
			trigger_error(  print_r( $backtrace ) ); // Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
			// Alernatively we could dump this to a file.
		}
		else {
			trigger_error( sprintf( __('%1$s is <strong>deprecated</strong> since Easy Digital Downloads version %2$s with no alternative available.', 'edd'), $function, $version ) );
			trigger_error( print_r($backtrace) );// Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
			// Alernatively we could dump this to a file.
		}
	}
}


/**
 * Checks whether function is disabled.
 *
 * @since 1.3.5
 *
 * @param string $function Name of the function.
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
 * @return $ret
 */
function edd_let_to_num( $v ) {
	$l   = substr( $v, -1 );
	$ret = substr( $v, 0, -1 );

	switch ( strtoupper( $l ) ) {
		case 'P':
			$ret *= 1024;
		case 'T':
			$ret *= 1024;
		case 'G':
			$ret *= 1024;
		case 'M':
			$ret *= 1024;
		case 'K':
			$ret *= 1024;
			break;
	}

	return $ret;
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
 * Delete symbolic links afer they have been used
 *
 * @access public
 * @since  1.5
 * @return void
 */
function edd_cleanup_file_symlinks() {
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
 * @global $edd_options
 * @author Daniel J Griffiths
 * @return bool $ret True if SKUs are enabled, false otherwise
 */
function edd_use_skus() {
	global $edd_options;

	$ret = isset( $edd_options['enable_skus'] );

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

        $is_dst = date('I');

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
