<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_discounts
 */
class Tests_Discounts extends EDD_UnitTestCase {
	protected $_post = null;

	public function setUp() {
		parent::setUp();

		$meta = array(
			'name' => '20 Percent Off',
			'type' => 'percent',
			'amount' => '20',
			'code' => '20OFF',
			'product_condition' => 'all',
			'max' => 10,
			'uses' => 54,
			'min_price' => 128
		);

		edd_store_discount( $meta );

		$this->_post->ID = edd_get_discount_id_by_code( '20OFF' );
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_addition_of_discount() {
		$post = array(
			'name' => 'Test Discount',
			'type' => 'percent',
			'amount' => '20',
			'code' => '20OFF',
			'product_condition' => 'all',
			'start' => '12/12/2050 00:00:00',
			'expiration' => '12/31/2050 00:00:00'
		);

		$this->assertTrue( edd_store_discount( $post ) );
	}
}
