<?php
/**
 * Checkout Functions
 *
 * @package     EDD
 * @subpackage  Checkout
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Determines if we're currently on the Checkout page
 *
 * @since 1.1.2
 * @return bool True if on the Checkout page, false otherwise
 */
function edd_is_checkout() {
	global $wp_query;

	$is_object_set    = isset( $wp_query->queried_object );
	$is_object_id_set = isset( $wp_query->queried_object_id );
	$is_checkout      = is_page( edd_get_option( 'purchase_page' ) );

	if( ! $is_object_set ) {
		unset( $wp_query->queried_object );
	} else if ( is_singular() ) {
		$content = $wp_query->queried_object->post_content;
	}

	if( ! $is_object_id_set ) {
		unset( $wp_query->queried_object_id );
	}

	// If we know this isn't the primary checkout page, check other methods.
	if ( ! $is_checkout && isset( $content ) && has_shortcode( $content, 'download_checkout' ) ) {
		$is_checkout = true;
	}

	return apply_filters( 'edd_is_checkout', $is_checkout );
}

/**
 * Determines if a user can checkout or not
 *
 * @since 1.3.3
 * @return bool Can user checkout?
 */
function edd_can_checkout() {
	$can_checkout = true; // Always true for now

	return (bool) apply_filters( 'edd_can_checkout', $can_checkout );
}

/**
 * Retrieve the Success page URI
 *
 * @since       1.6
 * @return      string
*/
function edd_get_success_page_uri( $query_string = null ) {
	$page_id = edd_get_option( 'success_page', 0 );
	$page_id = absint( $page_id );

	$success_page = get_permalink( $page_id );

	if ( $query_string ) {
		$success_page .= $query_string;
	}

	return apply_filters( 'edd_get_success_page_uri', $success_page );
}

/**
 * Determines if we're currently on the Success page.
 *
 * @since 1.9.9
 * @return bool True if on the Success page, false otherwise.
 */
function edd_is_success_page() {
	$is_success_page = edd_get_option( 'success_page', false );
	$is_success_page = isset( $is_success_page ) ? is_page( $is_success_page ) : false;

	return apply_filters( 'edd_is_success_page', $is_success_page );
}

/**
 * Send To Success Page
 *
 * Sends the user to the succes page.
 *
 * @param string $query_string
 * @since       1.0
 * @return      void
*/
function edd_send_to_success_page( $query_string = null ) {
	$redirect = edd_get_success_page_uri();

	if ( $query_string )
		$redirect .= $query_string;

	$gateway = isset( $_REQUEST['edd-gateway'] ) ? $_REQUEST['edd-gateway'] : '';

	wp_redirect( apply_filters('edd_success_page_redirect', $redirect, $gateway, $query_string) );
	edd_die();
}

/**
 * Get the URL of the Checkout page
 *
 * @since 1.0.8
 * @param array $args Extra query args to add to the URI
 * @return mixed Full URL to the checkout page, if present | null if it doesn't exist
 */
function edd_get_checkout_uri( $args = array() ) {
	$uri = false;

	if ( edd_is_checkout() ) {
		global $post;
		$uri = $post instanceof WP_Post ? get_permalink( $post->ID ) : NULL;
	}

	// If we are not on a checkout page, determine the URI from the default.
	if ( empty( $uri ) ) {
		$uri = edd_get_option( 'purchase_page', false );
		$uri = isset( $uri ) ? get_permalink( $uri ) : NULL;
	}

	if ( ! empty( $args ) ) {
		// Check for backward compatibility
		if ( is_string( $args ) )
			$args = str_replace( '?', '', $args );

		$args = wp_parse_args( $args );

		$uri = add_query_arg( $args, $uri );
	}

	$scheme = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https' : 'admin';

	$ajax_url = admin_url( 'admin-ajax.php', $scheme );

	if ( ( ! preg_match( '/^https/', $uri ) && preg_match( '/^https/', $ajax_url ) && edd_is_ajax_enabled() ) || edd_is_ssl_enforced() ) {
		$uri = preg_replace( '/^http:/', 'https:', $uri );
	}

	if ( edd_get_option( 'no_cache_checkout', false ) ) {
		$uri = edd_add_cache_busting( $uri );
	}

	return apply_filters( 'edd_get_checkout_uri', $uri );
}

/**
 * Send back to checkout.
 *
 * Used to redirect a user back to the purchase
 * page if there are errors present.
 *
 * @param array $args
 * @since  1.0
 * @return Void
 */
function edd_send_back_to_checkout( $args = array() ) {
	$redirect = edd_get_checkout_uri();

	if ( ! empty( $args ) ) {
		// Check for backward compatibility
		if ( is_string( $args ) )
			$args = str_replace( '?', '', $args );

		$args = wp_parse_args( $args );

		$redirect = add_query_arg( $args, $redirect );
	}

	wp_redirect( apply_filters( 'edd_send_back_to_checkout', $redirect, $args ) );
	edd_die();
}

/**
 * Get the URL of the Transaction Failed page
 *
 * @since 1.3.4
 * @param bool $extras Extras to append to the URL
 * @return mixed|void Full URL to the Transaction Failed page, if present, home page if it doesn't exist
 */
function edd_get_failed_transaction_uri( $extras = false ) {
	$uri = edd_get_option( 'failure_page', '' );
	$uri = ! empty( $uri ) ? trailingslashit( get_permalink( $uri ) ) : home_url();

	if ( $extras )
		$uri .= $extras;

	return apply_filters( 'edd_get_failed_transaction_uri', $uri );
}

/**
 * Determines if we're currently on the Failed Transaction page.
 *
 * @since 2.1
 * @return bool True if on the Failed Transaction page, false otherwise.
 */
function edd_is_failed_transaction_page() {
	$ret = edd_get_option( 'failure_page', false );
	$ret = isset( $ret ) ? is_page( $ret ) : false;

	return apply_filters( 'edd_is_failure_page', $ret );
}

/**
 * Mark payments as Failed when returning to the Failed Transaction page
 *
 * @since       1.9.9
 * @return      void
*/
function edd_listen_for_failed_payments() {

	$failed_page = edd_get_option( 'failure_page', 0 );

	if( ! empty( $failed_page ) && is_page( $failed_page ) && ! empty( $_GET['payment-id'] ) ) {

		$payment_id = absint( $_GET['payment-id'] );
		$payment    = get_post( $payment_id );
		$status     = edd_get_payment_status( $payment );

		if( $status && 'pending' === strtolower( $status ) ) {

			edd_update_payment_status( $payment_id, 'failed' );

		}

	}

}
add_action( 'template_redirect', 'edd_listen_for_failed_payments' );

/**
 * Check if a field is required
 *
 * @param string $field
 * @since       1.7
 * @return      bool
*/
function edd_field_is_required( $field = '' ) {
	$required_fields = edd_purchase_form_required_fields();
	return array_key_exists( $field, $required_fields );
}

/**
 * Retrieve an array of banned_emails
 *
 * @since       2.0
 * @return      array
 */
function edd_get_banned_emails() {
	$emails = array_map( 'trim', edd_get_option( 'banned_emails', array() ) );

	return apply_filters( 'edd_get_banned_emails', $emails );
}

/**
 * Determines if an email is banned
 *
 * @since       2.0
 * @param string $email Email to check if is banned.
 * @return bool
 */
function edd_is_email_banned( $email = '' ) {

	$email = trim( $email );
	if( empty( $email ) ) {
		return false;
	}

	$email         = strtolower( $email );
	$banned_emails = edd_get_banned_emails();

	if( ! is_array( $banned_emails ) || empty( $banned_emails ) ) {
		return false;
	}

	$return = false;
	foreach( $banned_emails as $banned_email ) {

		$banned_email = strtolower( $banned_email );

		if( is_email( $banned_email ) ) {

			// Complete email address
			$return = ( $banned_email == $email ? true : false );

		} elseif ( strpos( $banned_email, '.' ) === 0 ) {

			// TLD block
			$return = ( substr( $email, ( strlen( $banned_email ) * -1 ) ) == $banned_email ) ? true : false;

		} else {

			// Domain block
			$return = ( stristr( $email, $banned_email ) ? true : false );

		}

		if( true === $return ) {
			break;
		}
	}

	return apply_filters( 'edd_is_email_banned', $return, $email );
}

/**
 * Determines if secure checkout pages are enforced
 *
 * @since       2.0
 * @return      bool True if enforce SSL is enabled, false otherwise
 */
function edd_is_ssl_enforced() {
	$ssl_enforced = edd_get_option( 'enforce_ssl', false );
	return (bool) apply_filters( 'edd_is_ssl_enforced', $ssl_enforced );
}

/**
 * Handle redirections for SSL enforced checkouts
 *
 * @since 2.0
 * @return void
 */
function edd_enforced_ssl_redirect_handler() {

	if ( ! edd_is_ssl_enforced() || ! edd_is_checkout() || is_admin() || is_ssl() ) {
		return;
	}

	if( edd_is_checkout() && false !== strpos( edd_get_current_page_url(), 'https://' ) ) {
		return;
	}

	$uri = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	wp_safe_redirect( $uri );
	exit;
}
add_action( 'template_redirect', 'edd_enforced_ssl_redirect_handler' );

/**
 * Handle rewriting asset URLs for SSL enforced checkouts
 *
 * @since 2.0
 * @return void
 */
function edd_enforced_ssl_asset_handler() {
	if ( ! edd_is_ssl_enforced() || ! edd_is_checkout() || is_admin() ) {
		return;
	}

	$filters = array(
		'post_thumbnail_html',
		'wp_get_attachment_url',
		'wp_get_attachment_image_attributes',
		'wp_get_attachment_url',
		'option_stylesheet_url',
		'option_template_url',
		'script_loader_src',
		'style_loader_src',
		'template_directory_uri',
		'stylesheet_directory_uri',
		'site_url'
	);

	$filters = apply_filters( 'edd_enforced_ssl_asset_filters', $filters );

	foreach ( $filters as $filter ) {
		add_filter( $filter, 'edd_enforced_ssl_asset_filter', 1 );
	}
}
add_action( 'template_redirect', 'edd_enforced_ssl_asset_handler' );

/**
 * Filter filters and convert http to https
 *
 * @since 2.0
 * @param mixed $content
 * @return mixed
 */
function edd_enforced_ssl_asset_filter( $content ) {

	if ( is_array( $content ) ) {

		$content = array_map( 'edd_enforced_ssl_asset_filter', $content );

	} else {

		// Detect if URL ends in a common domain suffix. We want to only affect assets
		$extension = untrailingslashit( edd_get_file_extension( $content ) );
		$suffixes  = array(
			'br',
			'ca',
			'cn',
			'com',
			'de',
			'dev',
			'edu',
			'fr',
			'in',
			'info',
			'jp',
			'local',
			'mobi',
			'name',
			'net',
			'nz',
			'org',
			'ru',
		);

		if( ! in_array( $extension, $suffixes ) ) {

			$content = str_replace( 'http:', 'https:', $content );

		}

	}

	return $content;
}

/**
 * Given a number and algorithem, determine if we have a valid credit card format
 *
 * @since  2.4
 * @param  integer $number The Credit Card Number to validate
 * @return bool            If the card number provided matches a specific format of a valid card
 */
function edd_validate_card_number_format( $number = 0 ) {

	$number = trim( $number );
	if ( empty( $number ) ) {
		return false;
	}

	if ( ! is_numeric( $number ) ) {
		return false;
	}

	$is_valid_format = false;

	// First check if it passes with the passed method, Luhn by default
	$is_valid_format = edd_validate_card_number_format_luhn( $number );

	// Run additional checks before we start the regexing and looping by type
	$is_valid_format = apply_filters( 'edd_valiate_card_format_pre_type', $is_valid_format, $number );

	if ( true === $is_valid_format ) {
		// We've passed our method check, onto card specific checks
		$card_type       = edd_detect_cc_type( $number );
		$is_valid_format = ! empty( $card_type ) ? true : false;
	}

	return apply_filters( 'edd_cc_is_valid_format', $is_valid_format, $number );
}

/**
 * Validate credit card number based on the luhn algorithm
 *
 * @since  2.4
 * @param string $number
 * @return bool
 */
function edd_validate_card_number_format_luhn( $number ) {

	// Strip any non-digits (useful for credit card numbers with spaces and hyphens)
	$number = preg_replace( '/\D/', '', $number );

	// Set the string length and parity
	$length = strlen( $number );
	$parity = $length % 2;

	// Loop through each digit and do the math
	$total = 0;
	for ( $i = 0; $i < $length; $i++ ) {
		$digit = $number[ $i ];

		// Multiply alternate digits by two
		if ( $i % 2 == $parity ) {
			$digit *= 2;

			// If the sum is two digits, add them together (in effect)
			if ( $digit > 9 ) {
				$digit -= 9;
			}
		}

		// Total up the digits
		$total += $digit;
	}

	// If the total mod 10 equals 0, the number is valid
	return ( $total % 10 == 0 ) ? true : false;

}

/**
 * Detect credit card type based on the number and return an
 * array of data to validate the credit card number
 *
 * @since  2.4
 * @param string  $number
 * @return string|bool
 */
function edd_detect_cc_type( $number ) {

	$return = false;

	$card_types = array(
		array(
			'name'         => 'amex',
			'pattern'      => '/^3[4|7]/',
			'valid_length' => array( 15 ),
		),
		array(
			'name'         => 'diners_club_carte_blanche',
			'pattern'      => '/^30[0-5]/',
			'valid_length' => array( 14 ),
		),
		array(
			'name'         => 'diners_club_international',
			'pattern'      => '/^36/',
			'valid_length' => array( 14 ),
		),
		array(
			'name'         => 'jcb',
			'pattern'      => '/^35(2[89]|[3-8][0-9])/',
			'valid_length' => array( 16 ),
		),
		array(
			'name'         => 'laser',
			'pattern'      => '/^(6304|670[69]|6771)/',
			'valid_length' => array( 16, 17, 18, 19 ),
		),
		array(
			'name'         => 'visa_electron',
			'pattern'      => '/^(4026|417500|4508|4844|491(3|7))/',
			'valid_length' => array( 16 ),
		),
		array(
			'name'         => 'visa',
			'pattern'      => '/^4/',
			'valid_length' => array( 16 ),
		),
		array(
			'name'         => 'mastercard',
			'pattern'      => '/^5[1-5]/',
			'valid_length' => array( 16 ),
		),
		array(
			'name'         => 'maestro',
			'pattern'      => '/^(5018|5020|5038|6304|6759|676[1-3])/',
			'valid_length' => array( 12, 13, 14, 15, 16, 17, 18, 19 ),
		),
		array(
			'name'         => 'discover',
			'pattern'      => '/^(6011|622(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[0-1][0-9]|92[0-5]|64[4-9])|65)/',
			'valid_length' => array( 16 ),
		),
	);

	$card_types = apply_filters( 'edd_cc_card_types', $card_types );

	if ( ! is_array( $card_types ) ) {
		return false;
	}

	foreach ( $card_types as $card_type ){

		if ( preg_match( $card_type['pattern'], $number ) ) {

			$number_length = strlen( $number );
			if ( in_array( $number_length, $card_type['valid_length'] ) ) {
				$return = $card_type['name'];
				break;
			}

		}

	}

	return apply_filters( 'edd_cc_found_card_type', $return, $number, $card_types );
}

/**
 * Validate credit card expiration date
 *
 * @since  2.4
 * @param string  $exp_month
 * @param string  $exp_year
 * @return bool
 */
function edd_purchase_form_validate_cc_exp_date( $exp_month, $exp_year ) {

	$month_name = date( 'M', mktime( 0, 0, 0, $exp_month, 10 ) );
	$expiration = strtotime( date( 't', strtotime( $month_name . ' ' . $exp_year ) ) . ' ' . $month_name . ' ' . $exp_year . ' 11:59:59PM' );

	return $expiration >= time();

}

/**
 * Adds a SVG that holds payment icons.
 * 
 * @since 2.9.x
 */
function edd_payment_svg_icons() {

	$display = false;

	// Allow the SVG to be loaded on the "General" tab of the "Payment Gateways" admin page.
	if ( is_admin() && edd_is_admin_page( 'settings', 'gateways' ) && isset( $_GET['section'] ) && 'main' === $_GET['section'] ) {
		$display = true;
	}

	// Return if we can't display it, or we're not on EDD Checkout.
	if ( ! $display && ! edd_is_checkout() ) {
		return;
	}

	// Get the payment methods.
	$payment_methods = edd_get_option( 'accepted_cards', array() );

	// Return if there are no payment methods, or we cannot display it.
	if ( empty( $payment_methods ) && ! $display ) {
		return;
	}
	?>
	<svg style="position: absolute; width: 0; height: 0; overflow: hidden;" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
		<defs>
		<?php if ( array_key_exists( 'mastercard', $payment_methods ) || $display ) : ?>
			<symbol id="icon-mastercard" viewBox="0 0 50 32">
				<rect width="50" height="32"/>
				<path d="m13.827 29.327v-1.804c3e-3 -0.029 4e-3 -0.059 4e-3 -0.088 0-0.576-0.473-1.05-1.049-1.05-0.02 0-0.041 1e-3 -0.061 2e-3 -0.404-0.026-0.792 0.17-1.01 0.511-0.199-0.33-0.564-0.527-0.95-0.511-0.342-0.02-0.671 0.14-0.866 0.421v-0.352h-0.592v2.877h0.583v-1.653c-3e-3 -0.025-4e-3 -0.049-4e-3 -0.073 0-0.38 0.312-0.692 0.692-0.692 0.013 0 0.026 0 0.04 1e-3 0.415 0 0.649 0.271 0.649 0.758v1.656h0.583v-1.65c-2e-3 -0.023-3e-3 -0.047-3e-3 -0.07 0-0.381 0.313-0.695 0.694-0.695 0.012 0 0.025 1e-3 0.037 1e-3 0.427 0 0.655 0.271 0.655 0.758v1.656l0.598-3e-3zm9.368-2.871h-1.046v-0.872h-0.586v0.872h-0.601v0.523h0.601v1.362c0 0.668 0.234 1.064 0.974 1.064 0.276 1e-3 0.547-0.076 0.782-0.222l-0.181-0.511c-0.167 0.1-0.358 0.156-0.553 0.162-0.301 0-0.439-0.192-0.439-0.481v-1.38h1.046l3e-3 -0.517zm5.34-0.072c-0.316-6e-3 -0.613 0.154-0.782 0.421v-0.349h-0.571v2.877h0.577v-1.623c0-0.475 0.229-0.782 0.637-0.782 0.134-2e-3 0.267 0.023 0.391 0.072l0.193-0.544c-0.143-0.051-0.294-0.077-0.445-0.078v6e-3zm-8.072 0.301c-0.354-0.211-0.761-0.315-1.173-0.301-0.727 0-1.172 0.343-1.172 0.902 0 0.469 0.324 0.752 0.968 0.842l0.3 0.042c0.343 0.048 0.529 0.168 0.529 0.331 0 0.222-0.252 0.366-0.679 0.366-0.344 0.012-0.681-0.094-0.956-0.3l-0.301 0.451c0.367 0.249 0.802 0.38 1.245 0.372 0.83 0 1.29-0.384 1.29-0.932 0-0.547-0.352-0.754-0.974-0.844l-0.301-0.042c-0.271-0.036-0.511-0.121-0.511-0.301s0.228-0.355 0.571-0.355c0.317 4e-3 0.627 0.089 0.902 0.247l0.262-0.478zm8.718 1.202c-1e-3 0.024-2e-3 0.048-2e-3 0.071 0 0.787 0.648 1.434 1.434 1.434 0.024 0 0.048 0 0.071-2e-3 0.376 0.02 0.745-0.103 1.034-0.342l-0.3-0.451c-0.216 0.164-0.48 0.255-0.752 0.258-0.5-0.048-0.886-0.473-0.886-0.975 0-0.503 0.386-0.928 0.886-0.976 0.272 3e-3 0.536 0.094 0.752 0.259l0.3-0.451c-0.289-0.24-0.658-0.362-1.034-0.343-0.023-1e-3 -0.047-2e-3 -0.071-2e-3 -0.786 0-1.434 0.648-1.434 1.434 0 0.024 1e-3 0.048 2e-3 0.071v0.015zm-4.047-1.503c-0.841 0-1.422 0.601-1.422 1.503-1e-3 0.03-2e-3 0.059-2e-3 0.088 0 0.777 0.639 1.416 1.416 1.416 0.017 0 0.034 0 0.051-1e-3 0.428 0.015 0.848-0.128 1.178-0.402l-0.301-0.427c-0.237 0.19-0.531 0.296-0.835 0.3-0.435 0.016-0.814-0.305-0.869-0.736h2.149v-0.241c0-0.902-0.547-1.503-1.355-1.503l-0.01 3e-3zm0 0.535h0.025c0.4 0 0.73 0.327 0.733 0.728h-1.542c0.022-0.416 0.378-0.741 0.794-0.728h-0.01zm-7.789 0.971v-1.434h-0.577v0.349c-0.227-0.279-0.573-0.436-0.932-0.421-0.829 0-1.511 0.682-1.511 1.511s0.682 1.51 1.511 1.51c0.359 0.015 0.705-0.141 0.932-0.42v0.348h0.577v-1.443zm-2.33 0c-2e-3 -0.024-3e-3 -0.048-3e-3 -0.072 0-0.495 0.407-0.902 0.902-0.902h3e-3c0.5 0.048 0.886 0.473 0.886 0.976 0 0.502-0.386 0.927-0.886 0.975h-3e-3c-0.495 0-0.902-0.407-0.902-0.902 0-0.024 1e-3 -0.048 3e-3 -0.072v-3e-3zm22.214-1.503c-0.316-6e-3 -0.613 0.154-0.781 0.421v-0.352h-0.572v2.877h0.578v-1.623c0-0.475 0.228-0.782 0.637-0.782 0.134-2e-3 0.266 0.023 0.391 0.072l0.192-0.541c-0.143-0.051-0.293-0.077-0.445-0.078v6e-3zm4.636 2.531c0.039 0 0.078 7e-3 0.114 0.021 0.035 0.015 0.066 0.035 0.093 0.061s0.048 0.056 0.064 0.09c0.03 0.071 0.03 0.151 0 0.222-0.016 0.034-0.037 0.065-0.064 0.09-0.027 0.026-0.058 0.047-0.093 0.061-0.036 0.015-0.075 0.024-0.114 0.024-0.116-1e-3 -0.222-0.069-0.271-0.175-0.03-0.071-0.03-0.151 0-0.222 0.016-0.034 0.037-0.064 0.064-0.09s0.058-0.046 0.093-0.061c0.036-0.017 0.074-0.027 0.114-0.03v9e-3zm0 0.509c0.03 0 0.06-6e-3 0.087-0.019 0.026-0.011 0.05-0.027 0.069-0.048 0.078-0.084 0.078-0.216 0-0.3-0.019-0.021-0.043-0.037-0.069-0.048-0.027-0.012-0.057-0.019-0.087-0.018-0.03 0-0.06 6e-3 -0.087 0.018-0.027 0.011-0.052 0.027-0.072 0.048-0.078 0.084-0.078 0.216 0 0.3 0.02 0.021 0.045 0.037 0.072 0.048 0.028 0.01 0.057 0.014 0.087 0.013v6e-3zm0.018-0.358c0.028-2e-3 0.056 7e-3 0.078 0.024 0.019 0.015 0.029 0.039 0.027 0.063 1e-3 0.02-6e-3 0.04-0.021 0.054-0.017 0.016-0.039 0.025-0.063 0.027l0.087 0.099h-0.069l-0.081-0.099h-0.027v0.099h-0.057v-0.264l0.126-3e-3zm-0.066 0.051v0.072h0.066c0.012 4e-3 0.024 4e-3 0.036 0 4e-3 -8e-3 4e-3 -0.019 0-0.027 4e-3 -9e-3 4e-3 -0.019 0-0.027-0.012-3e-3 -0.024-3e-3 -0.036 0l-0.066-0.018zm-6.804-1.224v-1.44h-0.577v0.349c-0.226-0.279-0.572-0.436-0.932-0.421-0.828 0-1.51 0.682-1.51 1.511s0.682 1.51 1.51 1.51c0.36 0.015 0.706-0.141 0.932-0.42v0.348h0.577v-1.437zm-2.329 0c-2e-3 -0.024-3e-3 -0.048-3e-3 -0.072 0-0.495 0.407-0.902 0.902-0.902h2e-3c0.5 0.048 0.887 0.473 0.887 0.976 0 0.502-0.387 0.927-0.887 0.975h-2e-3c-0.495 0-0.902-0.407-0.902-0.902 0-0.024 1e-3 -0.048 3e-3 -0.072v-3e-3zm8.138 0v-2.6h-0.577v1.503c-0.227-0.279-0.573-0.436-0.932-0.421-0.829 0-1.511 0.682-1.511 1.511s0.682 1.51 1.511 1.51c0.359 0.015 0.705-0.141 0.932-0.42v0.348h0.577v-1.431zm-2.33 0c-2e-3 -0.024-3e-3 -0.048-3e-3 -0.072 0-0.495 0.407-0.902 0.902-0.902h3e-3c0.476 0.073 0.831 0.487 0.831 0.969 0 0.486-0.362 0.902-0.843 0.97h-3e-3c-0.495 0-0.902-0.407-0.902-0.902 0-0.023 1e-3 -0.046 3e-3 -0.069l0.012 6e-3z" fill="#fff" fill-rule="nonzero"/>
				<rect x="20.264" y="4.552" width="9.47" height="17.019" fill="#ff5f00"/>
				<path d="m20.865 13.063c-2e-3 -3.319 1.524-6.46 4.134-8.508-1.906-1.499-4.262-2.314-6.687-2.314-5.938 0-10.823 4.886-10.823 10.823 0 5.938 4.885 10.823 10.823 10.823 2.425 0 4.781-0.815 6.687-2.313-2.611-2.05-4.137-5.192-4.134-8.511z" fill="#eb001b" fill-rule="nonzero"/>
				<path d="m41.486 19.77v-0.349h0.142v-0.072h-0.358v0.072h0.141v0.349h0.075zm0.695 0v-0.421h-0.109l-0.126 0.301-0.126-0.301h-0.108v0.421h0.075v-0.319l0.117 0.274h0.081l0.118-0.274v0.319h0.078z" fill="#f79e1b" fill-rule="nonzero"/>
				<path d="m42.511 13.063c0 5.937-4.885 10.823-10.823 10.823-2.425 0-4.782-0.816-6.689-2.315 2.609-2.051 4.136-5.191 4.136-8.51 0-3.318-1.527-6.459-4.136-8.509 1.907-1.5 4.264-2.315 6.689-2.315 5.938 0 10.823 4.886 10.823 10.823v3e-3z" fill="#f79e1b" fill-rule="nonzero"/>
			</symbol>
		<?php endif; ?>
		<?php if ( array_key_exists( 'americanexpress', $payment_methods ) || $display ) : ?>
			<symbol id="icon-americanexpress" viewBox="0 0 32 32">
				<path d="m32 17.318v-17.318h-32v32h32v-9.336c-0.071 0 0-5.346 0-5.346" fill="#006fcf"/>
				<path d="m28.08 15.537h2.423v-5.631h-2.637v0.784l-0.499-0.784h-2.28v0.998l-0.428-0.998h-3.706-0.499c-0.142 0-0.285 0.072-0.427 0.072-0.143 0-0.214 0.071-0.357 0.142-0.142 0.072-0.213 0.072-0.356 0.143v-0.143-0.214h-12.045l-0.356 0.927-0.356-0.927h-2.851v0.998l-0.428-0.998h-2.28l-0.998 2.424v3.207h1.639l0.285-0.784h0.57l0.286 0.784h12.543v-0.713l0.499 0.713h3.492v-0.143-0.285c0.071 0.071 0.214 0.071 0.285 0.143 0.071 0.071 0.214 0.071 0.285 0.142 0.143 0.071 0.285 0.071 0.428 0.071h2.566l0.285-0.783h0.57l0.285 0.783h3.492v-0.712l0.57 0.784zm3.92 7.127v-5.274h-19.599l-0.499 0.712-0.499-0.712h-5.701v5.63h5.701l0.499-0.713 0.499 0.713h3.563v-1.212h-0.142c0.499 0 0.926-0.071 1.283-0.213v1.496h2.565v-0.712l0.499 0.712h10.619c0.428-0.142 0.856-0.213 1.212-0.427z" fill="#fff"/>
				<path d="m30.788 21.31h-1.924v0.784h1.853c0.784 0 1.283-0.499 1.283-1.212s-0.428-1.069-1.14-1.069h-0.856c-0.213 0-0.356-0.143-0.356-0.356 0-0.214 0.143-0.357 0.356-0.357h1.64l0.356-0.784h-1.924c-0.784 0-1.283 0.499-1.283 1.141 0 0.712 0.427 1.069 1.14 1.069h0.855c0.214 0 0.357 0.142 0.357 0.356 0.071 0.285-0.072 0.428-0.357 0.428zm-3.492 0h-1.924v0.784h1.853c0.784 0 1.283-0.499 1.283-1.212s-0.428-1.069-1.141-1.069h-0.855c-0.214 0-0.356-0.143-0.356-0.356 0-0.214 0.142-0.357 0.356-0.357h1.639l0.357-0.784h-1.924c-0.784 0-1.283 0.499-1.283 1.141 0 0.712 0.427 1.069 1.14 1.069h0.855c0.214 0 0.357 0.142 0.357 0.356 0.071 0.285-0.143 0.428-0.357 0.428zm-2.494-2.281v-0.784h-2.994v3.777h2.994v-0.784h-2.138v-0.784h2.067v-0.784h-2.067v-0.712h2.138v0.071zm-4.847 0c0.357 0 0.499 0.214 0.499 0.428 0 0.213-0.142 0.427-0.499 0.427h-1.069v-0.926l1.069 0.071zm-1.069 1.639h0.428l1.14 1.354h1.069l-1.282-1.425c0.641-0.143 0.997-0.57 0.997-1.14 0-0.713-0.499-1.212-1.283-1.212h-1.995v3.777h0.855l0.071-1.354zm-2.28-1.14c0 0.285-0.143 0.499-0.499 0.499h-1.14v-0.998h1.069c0.356 0 0.57 0.214 0.57 0.499zm-2.495-1.283v3.777h0.856v-1.283h1.14c0.784 0 1.354-0.498 1.354-1.282 0-0.713-0.499-1.283-1.283-1.283l-2.067 0.071zm-1.282 3.777h1.069l-1.497-1.924 1.497-1.853h-1.069l-0.927 1.212-0.926-1.212h-1.07l1.497 1.853-1.497 1.853h1.07l0.926-1.212 0.927 1.283zm-3.208-2.993v-0.784h-2.993v3.777h2.993v-0.784h-2.138v-0.784h2.067v-0.784h-2.067v-0.712h2.138v0.071zm17.319-6.699l1.497 2.28h1.069v-3.777h-0.856v2.494l-0.213-0.356-1.355-2.138h-1.14v3.777h0.855v-2.565l0.143 0.285zm-3.706-0.072l0.285-0.784 0.285 0.784 0.356 0.856h-1.282l0.356-0.856zm1.497 2.352h0.926l-1.639-3.777h-1.14l-1.64 3.777h0.927l0.356-0.784h1.853l0.357 0.784zm-3.992 0l0.357-0.784h-0.214c-0.641 0-0.998-0.427-0.998-1.069v-0.071c0-0.641 0.357-1.069 0.998-1.069h0.926v-0.784h-0.997c-1.141 0-1.782 0.784-1.782 1.853v0.071c0 1.141 0.641 1.853 1.71 1.853zm-3.207 0h0.856v-1.71-1.996h-0.856v3.706zm-1.853-2.993c0.357 0 0.499 0.214 0.499 0.428 0 0.213-0.142 0.427-0.499 0.427h-1.069v-0.926l1.069 0.071zm-1.069 1.639h0.428l1.14 1.354h1.069l-1.283-1.425c0.642-0.143 0.998-0.57 0.998-1.14 0-0.713-0.499-1.212-1.283-1.212h-1.995v3.777h0.855l0.071-1.354zm-1.568-1.639v-0.784h-2.993v3.777h2.993v-0.784h-2.138v-0.784h2.067v-0.784h-2.067v-0.712h2.138v0.071zm-6.485 2.993h0.784l1.069-3.064v3.064h0.855v-3.777h-1.425l-0.856 2.566-0.855-2.566h-1.425v3.777h0.855v-3.064l0.998 3.064zm-4.633-2.352l0.285-0.784 0.285 0.784 0.357 0.856h-1.283l0.356-0.856zm1.497 2.352h0.926l-1.639-3.777h-1.069l-1.639 3.777h0.927l0.356-0.784h1.853l0.285 0.784z" fill="#006fcf"/>
			</symbol>
		<?php endif; ?>
		<?php if ( array_key_exists( 'visa', $payment_methods ) || $display ) : ?>
			<symbol id="icon-visa" viewBox="0 0 50 32">
				<rect y="4.608" width="50" height="22.794" fill="#fff"/>
				<rect y="27.402" width="50" height="4.608" fill="#f7b600"/>
				<rect width="50" height="4.608" fill="#1a1f71"/>
				<path d="m24.803 9.686l-2.71 12.666h-3.277l2.71-12.666h3.277zm13.786 8.179l1.725-4.757 0.992 4.757h-2.717zm3.658 4.487h3.03l-2.648-12.666h-2.795c-0.63 0-1.161 0.365-1.396 0.928l-4.917 11.738h3.442l0.683-1.892h4.204l0.397 1.892zm-8.555-4.135c0.014-3.343-4.621-3.528-4.59-5.022 0.01-0.454 0.443-0.937 1.389-1.061 0.47-0.06 1.764-0.109 3.232 0.567l0.574-2.687c-0.788-0.285-1.803-0.56-3.065-0.56-3.239 0-5.518 1.721-5.537 4.187-0.02 1.823 1.628 2.84 2.868 3.447 1.278 0.621 1.706 1.02 1.7 1.574-9e-3 0.85-1.019 1.226-1.96 1.24-1.649 0.026-2.604-0.445-3.365-0.8l-0.595 2.777c0.767 0.351 2.18 0.656 3.643 0.672 3.444 0 5.696-1.701 5.706-4.334m-13.572-8.531l-5.309 12.666h-3.464l-2.613-10.109c-0.158-0.621-0.296-0.85-0.778-1.112-0.788-0.429-2.089-0.829-3.233-1.078l0.078-0.367h5.576c0.71 0 1.349 0.472 1.512 1.29l1.38 7.33 3.409-8.62h3.442z" fill="#1a1f71" fill-rule="nonzero"/>
			</symbol>
		<?php endif; ?>	
		<?php if ( array_key_exists( 'discover', $payment_methods ) || $display ) : ?>
			<symbol id="icon-discover" viewBox="0 0 50 32">
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
		<?php endif; ?>
		<?php if ( array_key_exists( 'paypal', $payment_methods ) || $display ) : ?>
			<symbol id="icon-paypal" viewBox="0 0 50 32">
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
		<?php endif; ?>	
		<?php if ( array_key_exists( 'amazon', $payment_methods ) || $display ) : ?>	
			<symbol id="icon-amazon" viewBox="0 0 50 32">
				<rect width="50" height="32" fill="#eaeded"/>
				<path d="m17.467 7.121c-0.071 0.01-0.148 0.02-0.22 0.031-0.714 0.086-1.358 0.352-1.94 0.771-0.123 0.087-0.235 0.179-0.363 0.276-0.01-0.026-0.02-0.052-0.02-0.072-0.021-0.138-0.041-0.281-0.066-0.419-0.036-0.235-0.154-0.337-0.389-0.337h-0.587c-0.352 0-0.419 0.067-0.419 0.419v10.688c0 0.052 0 0.103 5e-3 0.154 0.011 0.153 0.103 0.25 0.251 0.255 0.357 5e-3 0.72 5e-3 1.077 0 0.148 0 0.24-0.102 0.256-0.255 5e-3 -0.051 5e-3 -0.102 5e-3 -0.154v-3.697c0.056 0.046 0.087 0.072 0.112 0.097 0.914 0.761 1.966 1.011 3.115 0.787 1.042-0.205 1.767-0.843 2.237-1.783 0.357-0.709 0.505-1.465 0.526-2.252 0.025-0.873-0.061-1.731-0.414-2.543-0.434-1.001-1.154-1.66-2.242-1.884-0.163-0.036-0.332-0.051-0.5-0.077-0.143-5e-3 -0.281-5e-3 -0.424-5e-3zm-2.421 2.14c0-0.077 0.021-0.123 0.087-0.169 0.7-0.485 1.471-0.74 2.329-0.674 0.761 0.056 1.384 0.429 1.711 1.323 0.199 0.546 0.25 1.113 0.25 1.685 0 0.531-0.041 1.052-0.204 1.563-0.348 1.087-1.144 1.501-2.176 1.455-0.715-0.031-1.338-0.306-1.91-0.71-0.061-0.046-0.087-0.087-0.087-0.168 6e-3 -0.72 0-1.435 0-2.155 0-0.715 6e-3 -1.43 0-2.15zm10.505-2.14c-0.051 5e-3 -0.102 0.015-0.148 0.02-0.5 0.026-0.991 0.087-1.476 0.21-0.311 0.082-0.613 0.194-0.914 0.296-0.184 0.061-0.276 0.194-0.271 0.393 5e-3 0.169-5e-3 0.337 0 0.506 5e-3 0.245 0.108 0.311 0.348 0.25 0.398-0.102 0.796-0.214 1.2-0.291 0.628-0.117 1.261-0.169 1.899-0.072 0.332 0.052 0.644 0.149 0.858 0.429 0.189 0.246 0.261 0.537 0.271 0.838 0.015 0.424 0.01 0.848 0.015 1.272 0 0.02-5e-3 0.045-0.01 0.071-0.025-5e-3 -0.046 0-0.066-5e-3 -0.536-0.128-1.078-0.22-1.634-0.25-0.577-0.031-1.149 5e-3 -1.701 0.199-0.659 0.23-1.19 0.628-1.501 1.271-0.24 0.501-0.276 1.032-0.2 1.568 0.103 0.715 0.46 1.267 1.093 1.619 0.608 0.337 1.267 0.378 1.936 0.276 0.771-0.118 1.455-0.444 2.058-0.94 0.02-0.02 0.046-0.036 0.082-0.056 0.03 0.194 0.056 0.378 0.091 0.562 0.031 0.158 0.128 0.26 0.276 0.265 0.276 5e-3 0.557 5e-3 0.833 0 0.137-5e-3 0.229-0.097 0.245-0.24 5e-3 -0.046 5e-3 -0.097 5e-3 -0.143v-5.413c0-0.219-0.01-0.439-0.046-0.659-0.097-0.658-0.378-1.2-0.97-1.552-0.343-0.204-0.72-0.307-1.114-0.363-0.184-0.025-0.367-0.041-0.551-0.066-0.199 5e-3 -0.404 5e-3 -0.608 5e-3zm1.787 6.521c0 0.067-0.02 0.113-0.076 0.154-0.572 0.413-1.2 0.689-1.91 0.76-0.291 0.031-0.582 0.021-0.858-0.091-0.322-0.128-0.531-0.353-0.633-0.68-0.102-0.326-0.102-0.664-5e-3 -0.99 0.127-0.424 0.429-0.664 0.837-0.797 0.414-0.133 0.843-0.153 1.267-0.112 0.429 0.035 0.847 0.117 1.276 0.173 0.082 0.01 0.108 0.051 0.108 0.133-6e-3 0.245 0 0.485 0 0.73-6e-3 0.24-0.011 0.48-6e-3 0.72zm6.527 0.317c-0.659-1.823-1.318-3.651-1.976-5.474-0.103-0.292-0.215-0.578-0.322-0.864-0.056-0.148-0.164-0.245-0.327-0.245-0.388-5e-3 -0.776-0.01-1.169-5e-3 -0.128 0-0.189 0.102-0.164 0.23 0.026 0.107 0.056 0.209 0.097 0.312 1.001 2.476 2.007 4.958 3.018 7.43 0.087 0.209 0.108 0.388 0.01 0.603-0.168 0.372-0.301 0.766-0.474 1.138-0.154 0.332-0.409 0.582-0.777 0.68-0.26 0.071-0.521 0.081-0.786 0.056-0.128-0.011-0.255-0.041-0.383-0.051-0.174-0.011-0.261 0.066-0.266 0.245-5e-3 0.168-5e-3 0.337 0 0.505 5e-3 0.281 0.102 0.409 0.378 0.455 0.286 0.051 0.577 0.097 0.863 0.102 0.874 0.02 1.568-0.332 2.017-1.093 0.179-0.301 0.343-0.618 0.47-0.94 1.211-3.053 2.406-6.112 3.606-9.171 0.035-0.092 0.066-0.184 0.081-0.281 0.021-0.143-0.046-0.225-0.189-0.225-0.337-5e-3 -0.679 0-1.016 0-0.189 0-0.322 0.082-0.393 0.266-0.026 0.071-0.056 0.138-0.082 0.209-0.592 1.701-1.185 3.401-1.777 5.107-0.128 0.368-0.26 0.74-0.393 1.134-0.021-0.057-0.031-0.087-0.046-0.123z"/>
				<path d="m7.647 19.594c0.131-0.238 0.295-0.278 0.551-0.142 0.59 0.318 1.169 0.647 1.771 0.948 2.311 1.159 4.725 2.022 7.235 2.629 1.186 0.284 2.379 0.511 3.588 0.67 1.789 0.239 3.589 0.341 5.395 0.296 0.988-0.023 1.976-0.102 2.958-0.216 3.203-0.38 6.298-1.181 9.273-2.43 0.165-0.068 0.335-0.114 0.517-0.068 0.38 0.102 0.511 0.511 0.233 0.789-0.159 0.159-0.358 0.289-0.546 0.42-1.743 1.198-3.645 2.067-5.655 2.72-1.397 0.449-2.817 0.784-4.265 0.999-0.999 0.148-2.01 0.25-3.021 0.273-0.045 0-0.096 0.011-0.142 0.017h-1.198c-0.045-6e-3 -0.096-0.017-0.142-0.017-0.204-0.011-0.408-0.017-0.607-0.023-0.96-0.04-1.914-0.147-2.862-0.301-1.556-0.255-3.078-0.647-4.566-1.187-3.072-1.113-5.826-2.759-8.267-4.94-0.102-0.091-0.171-0.216-0.25-0.323v-0.114zm34.706-0.176c-0.057-0.284-0.272-0.392-0.517-0.471-0.386-0.131-0.789-0.188-1.192-0.222-0.744-0.062-1.488-0.028-2.226 0.108-0.812 0.153-1.584 0.415-2.271 0.886-0.08 0.057-0.159 0.119-0.21 0.199-0.04 0.062-0.052 0.159-0.029 0.227 0.023 0.085 0.119 0.108 0.205 0.102 0.039 0 0.085 0 0.124-6e-3 0.443-0.045 0.881-0.096 1.324-0.142 0.647-0.062 1.3-0.102 1.947-0.051 0.273 0.017 0.551 0.08 0.818 0.154 0.29 0.079 0.42 0.295 0.431 0.59 0.023 0.454-0.079 0.892-0.198 1.323-0.233 0.875-0.568 1.721-0.897 2.561-0.023 0.057-0.046 0.114-0.057 0.171-0.029 0.164 0.068 0.272 0.233 0.232 0.096-0.022 0.204-0.073 0.272-0.142 0.25-0.244 0.506-0.488 0.721-0.761 0.727-0.931 1.153-2.004 1.403-3.157 0.045-0.204 0.079-0.414 0.119-0.619v-0.982z" fill="#f90"/>
				<rect y="30.292" width="50" height="1.684" fill="#f90"/>
			</symbol>
		<?php endif; ?>
		</defs>
	</svg>
	<?php
}
add_action( 'wp_footer', 'edd_payment_svg_icons', 9999 );
add_action( 'admin_footer', 'edd_payment_svg_icons', 9999 );