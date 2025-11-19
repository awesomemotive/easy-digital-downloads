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
 * Registers the CAPTCHA script.
 *
 * @since 2.0
 * @return void
 */
function register_script() {
	$provider = \EDD\Captcha\Providers\Provider::get_active_provider();
	if ( ! $provider ) {
		return;
	}

	$site_key = $provider->get_key();
	if ( ! $site_key ) {
		return;
	}

	// 1. Register the provider's API script (e.g., Google, Cloudflare).
	$provider_handle = $provider->get_script_handle();
	wp_register_script(
		$provider_handle,
		esc_url_raw( $provider->get_script_url() ),
		array(),
		$provider->get_script_version(),
		true
	);

	// 2. Register the provider's handler script (depends on API script).
	$handler_handle = 'edd-captcha-' . $provider->get_id();
	wp_register_script(
		$handler_handle,
		esc_url_raw( $provider->get_handler_script_url() ),
		array( $provider_handle ),
		edd_admin_get_script_version(),
		true
	);

	// 3. Register the core CAPTCHA script (depends on handler).
	wp_register_script(
		'edd-captcha',
		EDD_PLUGIN_URL . 'assets/js/captcha/captcha.js',
		array( $handler_handle ),
		EDD_VERSION,
		true
	);

	// Localize the script with provider info.
	wp_localize_script(
		'edd-captcha',
		'EDDreCAPTCHA',
		get_localize_args( $provider )
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
	wp_enqueue_script( 'edd-captcha' );
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
 * Gets the array of localized parameters for the CAPTCHA.
 *
 * @since 2.0
 * @param \EDD\Captcha\Providers\Provider|null $provider The active provider.
 * @return array
 */
function get_localize_args( $provider = null ) {
	if ( ! $provider ) {
		$provider = \EDD\Captcha\Providers\Provider::get_active_provider();
	}

	$args = array(
		'ajaxurl'         => edd_get_ajax_url(),
		'sitekey'         => $provider ? $provider->get_key() : '',
		'provider'        => $provider ? $provider->get_id() : '',
		'context'         => edd_is_checkout() ? 'checkout' : 'form',
		'action'          => 'edd_form_submit',
		'error'           => __( 'Error', 'easy-digital-downloads' ),
		'error_message'   => __( 'There was an error validating the form. Please contact support.', 'easy-digital-downloads' ),
		'checkoutFailure' => __( 'Unable to verify purchase session. Please try again.', 'easy-digital-downloads' ),
	);

	return $args;
}

/**
 * Gets the CAPTCHA site key if a provider is configured.
 *
 * Backwards compatible function that returns the active provider's site key.
 *
 * @since 2.0
 * @return false|string
 */
function get_site_key() {
	$provider = \EDD\Captcha\Providers\Provider::get_active_provider();

	return $provider ? $provider->get_key() : false;
}
