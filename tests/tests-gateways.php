<?php


/**
 * @group edd_gateways
 */
class Test_Gateways extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_payment_gateways() {
		$out = edd_get_payment_gateways();
		$this->assertArrayHasKey( 'paypal', $out );
		$this->assertArrayHasKey( 'manual', $out );

		$this->assertEquals( 'PayPal Standard', $out['paypal']['admin_label'] );
		$this->assertEquals( 'PayPal', $out['paypal']['checkout_label'] );

		$this->assertEquals( 'Test Payment', $out['manual']['admin_label'] );
		$this->assertEquals( 'Test Payment', $out['manual']['checkout_label'] );
	}

	public function test_enabled_gateways() {
		$this->assertEmpty( edd_get_enabled_payment_gateways() );

		global $edd_options;
		$edd_options['gateways']['paypal'] = '1';
		$edd_options['gateways']['manual'] = '1';

		// Verify PayPal comes back as default/first when none is set
		$this->assertTrue( empty( $edd_options['default_gateway'] ) );

		$enabled_gateway_list = edd_get_enabled_payment_gateways( true );
		$first_gateway_id     = current( array_keys( $enabled_gateway_list ) );
		$this->assertEquals( 'paypal', $first_gateway_id );

		// Test when default is set to paypal
		$edd_options['default_gateway'] = 'paypal';
		$enabled_gateway_list = edd_get_enabled_payment_gateways( true );
		$first_gateway_id     = current( array_keys( $enabled_gateway_list ) );
		$this->assertEquals( 'paypal', $first_gateway_id );

		// Test default is set to manual and we ask for it sorted
		$edd_options['default_gateway'] = 'manual';
		$enabled_gateway_list = edd_get_enabled_payment_gateways( true );
		$first_gateway_id     = current( array_keys( $enabled_gateway_list ) );
		$this->assertEquals( 'manual', $first_gateway_id );

		// Test the call does not return it sorted when manual is default
		$enabled_gateway_list = edd_get_enabled_payment_gateways();
		$first_gateway_id     = current( array_keys( $enabled_gateway_list ) );
		$this->assertEquals( 'paypal', $first_gateway_id );

		// Reset these so the rest of the tests don't fail
		unset( $edd_options['default_gateway'], $edd_options['gateways']['paypal'], $edd_options['gateways']['manual'] );
	}

	public function test_is_gateway_active() {
		$this->assertFalse( edd_is_gateway_active( 'paypal' ) );
	}

	public function test_default_gateway() {

		global $edd_options;

		$this->assertFalse( edd_get_default_gateway() );

		$edd_options['gateways'] = array();
		$edd_options['gateways']['paypal'] = '1';
		$edd_options['gateways']['manual'] = '1';

		$this->assertEquals( 'paypal', edd_get_default_gateway() );

		$edd_options['default_gateway'] = 'paypal';
		$edd_options['gateways'] = array();
		$edd_options['gateways']['manual'] = '1';
		$edd_options['gateways']['stripe'] = '1';

		$this->assertEquals( 'manual', edd_get_default_gateway() );
	}

	public function test_get_gateway_admin_label() {
		global $edd_options;

		$edd_options['gateways'] = array();
		$edd_options['gateways']['paypal'] = '1';
		$edd_options['gateways']['manual'] = '1';

		$this->assertEquals( 'PayPal Standard', edd_get_gateway_admin_label( 'paypal' ) );
		$this->assertEquals( 'Test Payment', edd_get_gateway_admin_label( 'manual' ) );
	}

	public function test_get_gateway_checkout_label() {
		global $edd_options;

		$edd_options['gateways'] = array();
		$edd_options['gateways']['paypal'] = '1';
		$edd_options['gateways']['manual'] = '1';

		$this->assertEquals( 'PayPal', edd_get_gateway_checkout_label( 'paypal' ) );
		$this->assertEquals( 'Free Purchase', edd_get_gateway_checkout_label( 'manual' ) );
	}

	public function test_show_gateways() {
		edd_empty_cart();
		$this->assertFalse( edd_show_gateways() );
	}

	public function test_chosen_gateway() {
		$this->assertEquals( 'manual', edd_get_chosen_gateway() );
	}

	public function test_no_gateway_error() {

		global $edd_options;

		$download = EDD_Helper_Download::create_simple_download();
		edd_add_to_cart( $download->ID );

		$edd_options['gateways'] = array();

		edd_no_gateway_error();

		$errors = edd_get_errors();

		$this->assertArrayHasKey( 'no_gateways', $errors );
		$this->assertEquals( 'You must enable a payment gateway to use Easy Digital Downloads', $errors['no_gateways'] );
	}
}
