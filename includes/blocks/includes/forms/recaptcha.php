<?php
/**
 * reCAPTCHA handling for blocks.
 *
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 * @since 2.0
 */

namespace EDD\Blocks\Recaptcha;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\register_script' );
/**
 * Registers the reCAPTCHA script.
 *
 * @since 2.0
 * @return void
 */
function register_script() {
	$site_key = get_site_key();
	if ( ! $site_key ) {
		return;
	}
	$url = add_query_arg(
		array(
			'render' => $site_key,
		),
		'https://www.google.com/recaptcha/api.js'
	);
	wp_register_script( 'google-recaptcha', esc_url_raw( $url ), array(), '3', true );
	wp_register_script( 'edd-recaptcha', EDD_BLOCKS_URL . 'assets/js/recaptcha.js', array( 'google-recaptcha' ), EDD_VERSION, true );
	wp_localize_script(
		'edd-recaptcha',
		'EDDreCAPTCHA',
		get_localize_args()
	);
}

/**
 * Initialize reCAPTCHA scripts and fields.
 *
 * @since 2.0
 * @return void
 */
function initialize() {
	if ( ! get_site_key() ) {
		return;
	}
	enqueue();
	do_inputs();
}
add_action( 'edd_register_form_fields_after', __NAMESPACE__ . '\initialize' );
add_action( 'edd_lost_password_fields_after', __NAMESPACE__ . '\initialize' );

/**
 * Removes the reCAPTCHA initialization from the [edd_register] shortcode.
 * This hook is not used in the block.
 *
 * @since 3.2.4
 * @return void
 */
add_action(
	'edd_register_form_fields_top',
	function () {
		remove_action( 'edd_register_form_fields_after', __NAMESPACE__ . '\initialize' );
	}
);

/**
 * Renders the hidden inputs needed for reCAPTCHA validation.
 *
 * @since 2.0
 * @return void
 */
function do_inputs() {
	?>
	<input style="position: fixed; bottom: 0px; left: -10000px; width: 1px; height: 1px; overflow: hidden;" type="text" name="edd-blocks-recaptcha" id="edd-blocks-recaptcha" required value=""/>
	<?php
}

/**
 * Enqueues the script.
 *
 * @since 2.0
 * @return void
 */
function enqueue() {
	wp_enqueue_script( 'edd-recaptcha' );
}

/**
 * Checks for the reCAPTCHA validation.
 *
 * @since 2.0
 * @return void
 */
function validate() {
	_edd_deprecated_function( __FUNCTION__, '3.5.3', 'EDD\Captcha\Validate::validate' );
	$validate = new \EDD\Captcha\Validate();
	$validate->validate();
}

/**
 * Evaluates the reCAPTCHA response.
 *
 * @since 2.0
 * @deprecated 3.5.3 This function is no longer used.
 * @param array|\WP_Error $response The response from the reCAPTCHA API.
 * @return bool
 */
function validate_recaptcha( $response ) {
	if ( is_wp_error( $response ) ) {
		return set_error( 'invalid_recaptcha_bad' );
	}

	$verify = json_decode( wp_remote_retrieve_body( $response ) );
	if ( true !== $verify->success ) {
		return set_error( 'invalid_recaptcha_failed' );
	}

	if ( isset( $verify->score ) && (float) $verify->score < 0.5 ) {
		return set_error( 'invalid_recaptcha_low_score' );
	}

	return true;
}

/**
 * Gets the array of localized parameters for the recaptcha.
 *
 * @since 2.0
 * @return array
 */
function get_localize_args() {
	return array(
		'ajaxurl'         => edd_get_ajax_url(),
		'sitekey'         => get_site_key(),
		'context'         => edd_is_checkout() ? 'checkout' : 'form',
		'error'           => __( 'Error', 'easy-digital-downloads' ),
		'error_message'   => __( 'There was an error validating the form. Please contact support.', 'easy-digital-downloads' ),
		'checkoutFailure' => __( 'Unable to verify purchase session. Please try again.', 'easy-digital-downloads' ),
	);
}

/**
 * Gets the reCAPTCHA site key if both the site key and secret key are set.
 *
 * @since 2.0
 * @return false|string
 */
function get_site_key() {
	$site_key   = edd_get_option( 'recaptcha_site_key', false );
	$secret_key = edd_get_option( 'recaptcha_secret_key', false );

	return ! empty( $site_key ) && ! empty( $secret_key ) ? $site_key : false;
}
