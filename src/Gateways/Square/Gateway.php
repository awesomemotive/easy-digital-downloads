<?php
/**
 * Square Payment Gateway
 *
 * Main gateway class for Square payment processing
 *
 * @package     EDD\Gateways\Square
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square;


// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Gateways\Square\Checkout\Form;
use EDD\Gateways\Square\Checkout\Process;
use EDD\Gateways\Square\Helpers\Mode;
use EDD\Gateways\Square\Helpers\Currency;
use EDD\Gateways\Square\Admin\Orders\Refunds;

/**
 * Square Gateway Class
 *
 * @since 3.4.0
 */
class Gateway extends \EDD\Gateways\Gateway {

	/**
	 * Gateway ID
	 *
	 * @since 3.4.0
	 * @var string
	 */
	protected $id = 'square';

	/**
	 * Supported features
	 *
	 * @since 3.4.0
	 * @var array
	 */
	protected $supports = array();

	/**
	 * The connection object
	 *
	 * @since 3.4.0
	 * @var Connection
	 */
	protected $connection;

	/**
	 * Payment icons
	 *
	 * @since 3.4.0
	 * @var array
	 */
	protected $icons = array(
		'visa'       => 'Visa',
		'mastercard' => 'Mastercard',
		'amex'       => 'American Express',
		'discover'   => 'Discover',
	);

	/**
	 * Constructor
	 *
	 * @since 3.4.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Get the admin label
	 *
	 * @since 3.4.0
	 * @return string
	 */
	public function get_admin_label(): string {
		return __( 'Square', 'easy-digital-downloads' );
	}

	/**
	 * Get the checkout label
	 *
	 * @since 3.4.0
	 * @return string
	 */
	public function get_checkout_label(): string {
		return __( 'Credit Card', 'easy-digital-downloads' );
	}

	/**
	 * Initialize the gateway
	 *
	 * @since 3.4.0
	 * @return void
	 */
	public function init() {
		add_filter( 'edd_enabled_payment_gateways_before_sort', array( $this, 'maybe_disable_gateway' ), 10, 1 );
		add_filter( 'edd_is_gateway_setup_square', array( $this, 'is_available' ) );

		if ( ! $this->is_store_country_supported() ) {
			return;
		}

		if ( is_admin() ) {
			new Refunds();
			add_action( 'admin_init', array( $this, 'capture_oauth_tokens' ) );
		}

		add_action( 'edd_square_cc_form', array( $this, 'credit_card_form' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

		// Hook into the Square checkout form submission.
		add_action( 'wp_ajax_edd_square_process_checkout_form', array( $this, 'validate_checkout_form' ) );
		add_action( 'wp_ajax_nopriv_edd_square_process_checkout_form', array( $this, 'validate_checkout_form' ) );
		add_action( 'edd_gateway_square', array( $this, 'process_checkout_form' ), 10, 1 );
	}

	/**
	 * Capture the OAuth tokens
	 *
	 * @since 3.4.0
	 * @return void
	 */
	public function capture_oauth_tokens() {
		$this->get_connection()->handle_oauth_redirect();
	}

	/**
	 * Validate the checkout form
	 *
	 * @since 3.4.0
	 * @return void
	 */
	public function validate_checkout_form() {
		Form::validate();
	}

	/**
	 * Process the checkout form
	 *
	 * @since 3.4.0
	 * @return void
	 */
	public function process_checkout_form( $purchase_data ) {
		Process::process( $purchase_data );
	}

	/**
	 * Load gateway specific scripts
	 *
	 * @since 3.4.0
	 *
	 * @param bool $force_load_scripts Whether to force load the scripts.
	 *
	 * @return void
	 */
	public function load_scripts( $force_load_scripts = false ) {

		if ( wp_script_is( 'edd-square-checkout', 'enqueued' ) ) {
			return;
		}

		if ( false === edd_is_gateway_active( $this->id ) ) {
			return;
		}

		// Determine the correct Square SDK URL based on environment.
		$square_sdk_url = Mode::is_sandbox()
			? 'https://sandbox.web.squarecdn.com/v1/square.js'
			: 'https://web.squarecdn.com/v1/square.js';

		// Enqueue Square Web Payments SDK.
		wp_register_script(
			'edd-square-web-payments-sdk',
			$square_sdk_url,
			array(),
		);

		$is_checkout = edd_is_checkout() && 0 < edd_get_cart_total();

		if ( $is_checkout || $force_load_scripts ) {
			wp_enqueue_script( 'edd-square-web-payments-sdk' );
		}

		if ( $is_checkout || $force_load_scripts ) {
			$client_id   = edd_get_option( 'square_' . Mode::get() . '_client_id' );
			$location_id = edd_get_option( 'square_' . Mode::get() . '_location_id' );

			if ( empty( $client_id ) ) {
				edd_debug_log( 'EDD Square: client_id is empty. Skipping script localization.' );
				return;
			}

			// Enqueue our custom Square checkout script.
			wp_register_script(
				'edd-square-checkout',
				EDD_PLUGIN_URL . 'assets/js/square-checkout.js',
				array( 'edd-square-web-payments-sdk', 'jquery', 'edd-ajax', 'wp-dom-ready' ),
				EDD_VERSION,
				true
			);

			// Enqueue Square checkout styles.
			wp_register_style(
				'edd-square-checkout',
				EDD_PLUGIN_URL . 'assets/css/square-checkout.min.css',
				array(),
				EDD_VERSION
			);

			wp_enqueue_script( 'edd-square-checkout' );
			wp_enqueue_style( 'edd-square-checkout' );

			// Localize script with necessary data.
			wp_localize_script(
				'edd-square-checkout',
				'eddSquare',
				apply_filters(
					'edd_square_js_vars',
					array(
						'client_id'        => $client_id,
						'location_id'      => $location_id,
						'environment'      => Mode::get(),
						'currency'         => edd_get_currency(),
						'country'          => edd_get_option( 'base_country', 'US' ),
						'nonce'            => wp_create_nonce( 'edd_square_nonce' ),
						'ajax_url'         => admin_url( 'admin-ajax.php' ),
						'is_test_mode'     => edd_is_test_mode() ? 'true' : 'false',
						'debug'            => defined( 'WP_DEBUG' ) && WP_DEBUG, // Pass WP_DEBUG status.
						'generic_error'    => __( 'An error occurred while processing your payment. Please try again.', 'easy-digital-downloads' ),
						'processing_text'  => __( 'Processing...', 'easy-digital-downloads' ),
						'success_page_uri' => edd_get_success_page_uri(),
						'i18n'             => array(
							'card_information' => __( 'Card Information', 'easy-digital-downloads' ),
							'processing'       => __( 'Processing...', 'easy-digital-downloads' ),
							'payment_failed'   => __( 'Payment failed. Please try again.', 'easy-digital-downloads' ),
							'invalid_card'     => __( 'Please enter valid card information.', 'easy-digital-downloads' ),
							'network_error'    => __( 'Network error. Please check your connection and try again.', 'easy-digital-downloads' ),
						),
					)
				)
			);
		}
	}

	/**
	 * Output the credit card form
	 *
	 * @since 3.4.0
	 * @param bool $echo_form Whether to echo or return the form.
	 *
	 * @return string The credit card form HTML.
	 */
	public function credit_card_form( $echo_form = true ) {
		ob_start();

		// If cart contains recurring items, we need to throw an error for now.
		if ( function_exists( 'edd_recurring' ) && edd_recurring()->cart_contains_recurring() ) {
			edd_set_error(
				'square_checkout_form_unavailable',
				__( 'Square does not support subscription payments at this time. Please remove any recurring items from your cart and try again.', 'easy-digital-downloads' )
			);
			return;
		}

		// Reorder the Address Fields.
		remove_action( 'edd_after_cc_fields', 'edd_default_cc_address_fields' );
		add_action( 'edd_before_cc_fields', 'edd_default_cc_address_fields' );

		// Delegate to the new Form class for rendering.
		if ( $this->is_available() && class_exists( 'EDD\Gateways\Square\Checkout\Form' ) ) {
			Form::render();
		} else {
			if ( current_user_can( 'manage_shop_settings' ) ) {
				edd_set_error(
					'square_checkout_form_unavailable',
					__( 'The Square payment gateway is not properly configured. Please review your settings and try again.', 'easy-digital-downloads' )
				);
			} else {
				edd_set_error(
					'square_checkout_form_unavailable',
					__( 'Checkout is currently unavailable. Please contact support.', 'easy-digital-downloads' )
				);
			}
		}
		$form = ob_get_clean();

		if ( false !== $echo_form ) {
			echo $form;
		}

		return $form;
	}

	/**
	 * Check if gateway is available
	 *
	 * @since 3.4.0
	 * @return bool True if available, false otherwise.
	 */
	public function is_available() {
		$currency_supported = Currency::is_currency_supported();
		$country_supported  = self::is_store_country_supported();

		return $this->get_connection()->is_connected() && $currency_supported && $country_supported;
	}

	/**
	 * Maybe remove the gateway from the list of enabled gateways.
	 *
	 * @since 3.4.0
	 *
	 * @param array $gateways The list of enabled gateways.
	 *
	 * @return array The list of enabled gateways.
	 */
	public function maybe_disable_gateway( $gateways ) {
		$square_enabled = $this->is_available();
		if ( ! $square_enabled ) {
			unset( $gateways[ $this->id ] );
		}

		return $gateways;
	}

	/**
	 * Check if the store country is supported, so we don't present connection options.
	 *
	 * @since 3.4.0
	 * @return bool True if supported, false otherwise.
	 */
	public static function is_store_country_supported() {
		$supported_countries = array(
			'US',
			'CA',
			'AU',
			'JP',
			'GB',
			'IE',
			'FR',
			'ES',
		);

		$store_country = edd_get_option( 'base_country', 'US' );

		return in_array( $store_country, $supported_countries, true );
	}


	/**
	 * Get the connection object
	 *
	 * @since 3.4.0
	 * @return Connection
	 */
	private function get_connection() {
		if ( empty( $this->connection ) ) {
			$this->connection = new Connection();
		}

		return $this->connection;
	}
}
