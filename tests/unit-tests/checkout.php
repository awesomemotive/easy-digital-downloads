<?php

/**
 * Test Checkout
 */
class Tests_Checkout extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test_can_checkout() {
		$this->assertTrue( edd_can_checkout() );
	}
}