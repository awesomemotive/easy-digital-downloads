<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_checkout
 */
class Tests_Checkout extends EDD_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test_can_checkout() {
		$this->assertTrue( edd_can_checkout() );
	}
}