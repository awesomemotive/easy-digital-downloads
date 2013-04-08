<?php

class Test_Easy_Digital_Downloads_Discounts extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function testAdditionOfDiscount() {
		$discount = edd_store_discount( array(
				'code' => 'EDD_TEST_DISCOUNT',
				'name' => 'Test Discount'
			)
		);

		$this->assertTrue( $discount );
	}
}