<?php
/**
 * Tests for the Stripe frontend script localization.
 *
 * @package     EDD\Tests\Stripe
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

namespace EDD\Tests\Stripe;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Gateways\Stripe\API;

/**
 * Asserts that Stripe.js is given the same API version the server pins.
 *
 * Recurring reads `edd_stripe_vars.apiVersion` too, so the key name is shared across plugins.
 *
 * @group edd_stripe
 */
class Scripts extends EDD_UnitTestCase {

	/**
	 * Settings global as it stood before the test ran.
	 *
	 * @var array
	 */
	private $original_options;

	public function set_up(): void {
		parent::set_up();

		global $edd_options;

		$this->original_options = $edd_options;

		add_filter( 'edd_is_gateway_active', array( $this, 'activate_stripe' ), 10, 2 );
		$this->remove_stripe_script();
	}

	public function tear_down(): void {
		global $edd_options;

		$this->remove_stripe_script();
		remove_filter( 'edd_is_gateway_active', array( $this, 'activate_stripe' ), 10 );

		$edd_options = $this->original_options;

		parent::tear_down();
	}

	public function test_payment_elements_localizes_server_api_version() {
		edd_update_option( 'stripe_elements_mode', 'payment-elements' );

		$this->assertSame( 'payment-elements', edds_get_elements_mode() );
		$this->assertSame( API::get_api_version(), $this->get_localized_api_version() );
	}

	public function test_card_elements_localizes_server_api_version() {
		edd_update_option( 'stripe_elements_mode', 'card-elements' );

		$this->assertSame( 'card-elements', edds_get_elements_mode() );
		$this->assertSame( API::get_api_version(), $this->get_localized_api_version() );
	}

	/**
	 * Reports the Stripe gateway as active.
	 *
	 * @param bool   $is_active Whether the gateway is active.
	 * @param string $gateway   The gateway ID.
	 * @return bool
	 */
	public function activate_stripe( $is_active, $gateway ) {
		return 'stripe' === $gateway ? true : $is_active;
	}

	/**
	 * Enqueues the Stripe scripts and reads `apiVersion` back from the printed `edd_stripe_vars`.
	 *
	 * @return string|null
	 */
	private function get_localized_api_version() {
		edd_stripe_js( true );

		$localized = wp_scripts()->get_data( 'edd-stripe-js', 'data' );
		$this->assertIsString( $localized, 'edd_stripe_vars was not localized.' );
		$this->assertSame( 1, preg_match( '/var edd_stripe_vars = (\{.*\});/s', $localized, $matches ) );

		$stripe_vars = json_decode( $matches[1], true );

		return $stripe_vars['apiVersion'] ?? null;
	}

	/**
	 * Removes the Stripe script, which `edd_stripe_js()` skips once it is enqueued.
	 */
	private function remove_stripe_script() {
		wp_dequeue_script( 'edd-stripe-js' );
		wp_deregister_script( 'edd-stripe-js' );
	}
}
