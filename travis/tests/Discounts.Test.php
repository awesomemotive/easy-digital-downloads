<?php
/**
 * Test Discounts
 */
class Test_Easy_Digital_Downloads_Discounts extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function testAdditionOfDiscount() {
		$post = array(
			'name' => 'Test Discount',
			'type' => 'percentage',
			'amount' => '20',
			'code' => '20OFF',
			'product_condition' => 'all'
		);

		$this->assertTrue(edd_store_discount($post));
	}
}