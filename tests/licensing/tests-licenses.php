<?php

namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Tests_Licenses extends EDD_UnitTestCase {

	/**
	 * The pass handler class.
	 *
	 * @var \EDD\Admin\PassHandler\Handler
	 */
	private static $pass_handler;

	public static function wpSetUpBeforeClass() {
		self::$pass_handler = new \EDD\Admin\PassHandler\Handler();
	}

	public function setUp(): void {
		parent::setUp();
	}

	public function tearDown(): void {
		parent::tearDown();
		delete_site_option( 'edd_pro_license_key' );
	}

	public function test_get_pro_license() {
		$license_key = 'daksjfg98q3kjhJ3K4Q2354';
		update_site_option( 'edd_pro_license_key', $license_key );

		$license = self::$pass_handler->get_pro_license();

		$this->assertEquals( $license_key, $license->key );
	}

	public function test_get_pro_license_data() {
		$license_key = 'daksjfg98q3kjhJ3K4Q2354';
		update_site_option( 'edd_pro_license_key', $license_key );
		self::$pass_handler->update_pro_license( $this->get_pass_license_data() );

		$license = self::$pass_handler->get_pro_license();

		$this->assertEquals( 7642331, $license->payment_id );
	}

	public function test_pro_license_inactive_pro_returns_false() {
		$license_key = 'daksjfg98q3kjhJ3K4Q2354';
		update_site_option( 'edd_pro_license_key', $license_key );
		self::$pass_handler->update_pro_license( $this->get_pass_license_data() );

		$this->assertFalse( edd_is_inactive_pro() );
	}

	public function test_get_stripe_license() {
		$product_name = 'Stripe Pro Payment Gateway';
		$shortname    = 'edd_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $product_name ) ) );
		$license_key  = 'bgvear89p7ty4qbrjkc4';

		edd_update_option( "{$shortname}_license_key", $license_key );
		$license = new \EDD\Licensing\License( $product_name );
		$license->save( $this->get_stripe_license_data() );
		$license = $license->get();

		$this->assertEquals( 167, $license->item_id );
		$this->assertEquals( $license_key, $license->key );
	}

	private function get_pass_license_data() {
		return (object) array(
			'success'          => true,
			'license'          => 'valid',
			'item_id'          => 1783595,
			'item_name'        => '',
			'license_limit'    => 1,
			'site_count'       => 2,
			'expires'          => 'lifetime',
			'activations_left' => 'unlimited',
			'payment_id'       => 7642331,
			'customer_name'    => 'John Doe',
			'customer_email'   => 'john@edd.local',
			'price_id'         => 0,
			'pass_id'          => 1464807,
		);
	}

	private function get_stripe_license_data() {
		return (object) array(
			'success'          => true,
			'license'          => 'valid',
			'item_id'          => 167,
			'item_name'        => 'Stripe Pro Payment Gateway',
			'license_limit'    => 1,
			'site_count'       => 1,
			'expires'          => 'lifetime',
			'activations_left' => 0,
			'payment_id'       => 7642331,
			'customer_name'    => 'John Doe',
			'customer_email'   => 'john@edd.local',
			'price_id'         => 1,
		);
	}
}
