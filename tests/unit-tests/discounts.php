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

		$this->assertInternalType( 'int', edd_store_discount( $post ) );
	}

	public function test_discount_status_update() {
		$this->assertTrue( edd_update_discount_status( $this->_post->ID ) );
	}

	public function test_discounts_exists() {
		edd_update_discount_status( $this->_post->ID );
		$this->assertTrue( edd_has_active_discounts() );
	}

	public function test_discount_exists() {
		$this->assertTrue( edd_discount_exists( $this->_post->ID ) );
	}

	public function test_discount_retrieved_from_database() {
		$this->assertObjectHasAttribute( 'ID', edd_get_discount(  $this->_post->ID ) );
		$this->assertObjectHasAttribute( 'post_title', edd_get_discount(  $this->_post->ID ) );
		$this->assertObjectHasAttribute( 'post_status', edd_get_discount(  $this->_post->ID ) );
		$this->assertObjectHasAttribute( 'post_type', edd_get_discount(  $this->_post->ID ) );
	}

	public function test_get_discount_code() {
		$this->assertSame( '20OFF', edd_get_discount_code( $this->_post->ID ) );
	}

	public function test_discount_start_date() {
		$this->assertSame( '', edd_get_discount_start_date( $this->_post->ID ) );
	}

	public function test_discount_expiration_date() {
		$this->assertSame( '', edd_get_discount_expiration( $this->_post->ID ) );
	}

	public function test_discount_max_uses() {
		$this->assertSame( 10, edd_get_discount_max_uses( $this->_post->ID ) );
	}

	public function test_discount_uses() {
		$this->assertSame( 54, edd_get_discount_uses( $this->_post->ID ) );
	}

	public function testDiscountMinPrice() {
		$this->assertSame(128.0, edd_get_discount_min_price($this->_post->ID));
	}

	public function test_discount_amount() {
		$this->assertSame( 20.0, edd_get_discount_amount( $this->_post->ID ) );
	}

	public function test_discount_type() {
		$this->assertSame( 'percent', edd_get_discount_type( $this->_post->ID ) );
	}

	public function test_discount_product_condition() {
		$this->assertSame( 'all', edd_get_discount_product_condition( $this->_post->ID ) );
	}

	public function test_discount_is_not_global() {
		$this->assertFalse( edd_is_discount_not_global( $this->_post->ID ) );
	}

	public function test_discount_is_single_use() {
		$this->assertFalse( edd_discount_is_single_use( $this->_post->ID ) );
	}

	public function test_discount_is_started() {
		$this->assertTrue( edd_is_discount_started( $this->_post->ID ) );
	}

	public function test_discount_is_expired() {
		$this->assertFalse( edd_is_discount_expired( $this->_post->ID ) );
	}

	public function test_discount_is_maxed_out() {
		$this->assertTrue( edd_is_discount_maxed_out( $this->_post->ID ) );
	}

	public function test_discount_is_min_met() {
		$this->assertFalse( edd_discount_is_min_met( $this->_post->ID ) );
	}

	public function test_discount_is_used() {
		$this->assertFalse( edd_is_discount_used( $this->_post->ID ) );
	}

	public function test_discount_is_valid_when_purchasing() {
		$this->assertFalse( edd_is_discount_valid( $this->_post->ID ) );
	}

	public function test_discount_id_by_code() {
		$this->assertSame( $this->_post->ID, edd_get_discount_id_by_code( '20OFF' ) );
	}


	public function test_get_discounted_amount() {
		$this->assertEquals( 432.0, edd_get_discounted_amount( '20OFF', '540' ) );
	}

	public function test_increase_discount_usage() {
		$uses = edd_increase_discount_usage( '20OFF' );
		$this->assertSame( 55, $uses );
	}

	public function test_formatted_discount_amount() {
		$this->assertSame( '20%', edd_format_discount_rate( 'percent', get_post_meta( $this->_post->ID, '_edd_discount_amount', true ) ) );
	}

	public function test_edd_get_discount_by() {
		$this->assertSame( $this->_post, edd_get_discount_by( 'id', $this->_post->ID ) );
		$this->assertSame( $this->_post, edd_get_discount_by( 'code', '20OFF' ) );
		$this->assertSame( $this->_post, edd_get_discount_by( 'name', '20 Percent Off' ) );
	}

	public function test_deletion_of_discount() {
		edd_remove_discount( $this->_post->ID );
		$this->assertFalse( wp_cache_get( $this->_post->ID, 'posts' ) );
	}
}
