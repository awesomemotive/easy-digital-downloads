<?php
/**
 * Returns the one true instance of EDD_Stripe
 *
 * @since 2.8.1
 *
 * @return void|\EDD_Stripe EDD_Stripe instance or void if Easy Digital
 *                          Downloads is not active.
 */
function edd_stripe_core_bootstrap() {

	// Stripe is already active, do nothing.
	if ( class_exists( 'EDD_Stripe' ) ) {
		return;
	}

	if ( ! defined( 'EDDS_PLUGIN_DIR' ) ) {
		define( 'EDDS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	}

	if ( ! defined( 'EDDSTRIPE_PLUGIN_URL' ) ) {
		define( 'EDDSTRIPE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	}

	if ( ! defined( 'EDD_STRIPE_PLUGIN_FILE' ) ) {
		define( 'EDD_STRIPE_PLUGIN_FILE', __FILE__ );
	}

	if ( ! defined( 'EDD_STRIPE_VERSION' ) ) {
		define( 'EDD_STRIPE_VERSION', '2.9.6' );
	}

	if ( ! defined( 'EDD_STRIPE_API_VERSION' ) ) {
		define( 'EDD_STRIPE_API_VERSION', '2020-03-02' );
	}

	if ( ! defined( 'EDD_STRIPE_PARTNER_ID' ) ) {
		define( 'EDD_STRIPE_PARTNER_ID', 'pp_partner_DKh7NDe3Y5G8XG' );
	}

	include_once __DIR__ . '/includes/class-edd-stripe.php';

	// Initial instantiation.
	EDD_Stripe::instance();
}
add_action( 'plugins_loaded', 'edd_stripe_core_bootstrap' );
