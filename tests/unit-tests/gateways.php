<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_gateways
 */
class Test_Gateways extends EDD_UnitTestCase {
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


}
