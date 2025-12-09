<?php
/**
 * Handles checkout scripts.
 *
 * @package     EDD
 * @subpackage  Assets
 * @since       3.3.0
 */

namespace EDD\Assets;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codingStandardsIgnoreLine

/**
 * Checkout scripts.
 */
class Checkout {

	/**
	 * Register the checkout scripts.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public static function register() {
		$js_dir     = edd_get_assets_url( 'js/frontend' );
		$vendor_dir = edd_get_assets_url( 'vendor/js' );
		$version    = edd_admin_get_script_version();

		// Register vendor scripts from assets/vendor/js.
		wp_register_script( 'creditCardValidator', $vendor_dir . 'jquery.creditcardvalidator.min.js', array( 'jquery' ), $version, edd_scripts_in_footer() );
		wp_register_script( 'jQuery.payment', $vendor_dir . 'jquery.payment.min.js', array( 'jquery' ), $version, edd_scripts_in_footer() );

		// Register compiled scripts from assets/build/js.
		wp_register_script( 'edd-checkout-global', $js_dir . 'checkout.js', array( 'jquery' ), $version, edd_scripts_in_footer() );
		wp_register_script( 'edd-ajax', $js_dir . 'edd-ajax.js', array( 'jquery' ), $version, edd_scripts_in_footer() );
	}

	/**
	 * Enqueue scripts for the checkout page.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public static function enqueue() {
		// Enqueue credit-card validator.
		if ( edd_is_cc_verify_enabled() ) {
			wp_enqueue_script( 'creditCardValidator' );
		}

		// Enqueue global checkout.
		wp_enqueue_script( 'edd-checkout-global' );

		$checkout_address = edd_get_option( 'checkout_address_fields', array() );
		if ( ! empty( $checkout_address['phone'] ) ) {
			\EDD\HTML\Phone::enqueue();
		}
	}
}
