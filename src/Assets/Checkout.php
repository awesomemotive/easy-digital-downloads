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
		$scripts   = array(
			'creditCardValidator'  => 'vendor/jquery.creditcardvalidator.min.js',
			'jQuery.payment'       => 'vendor/jquery.payment.min.js',
			'edd-checkout-global'  => 'edd-checkout-global.js',
			'edd-ajax'             => 'edd-ajax.js',
		);
		foreach ( $scripts as $handle => $file  ) {
			wp_register_script(
				$handle,
				EDD_PLUGIN_URL . 'assets/js/' . $file,
				array( 'jquery' ),
				edd_admin_get_script_version(),
				edd_scripts_in_footer()
			);
		}
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
	}
}
