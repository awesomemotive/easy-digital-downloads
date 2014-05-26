<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_discounts
 */
class Tests_Discounts extends EDD_UnitTestCase {
	protected $_post = null;
	protected $_post_id = null;
	protected $_flat_post_id = null;
	protected $_negative_post_id = null;

	public function setUp() {
		parent::setUp();
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
			'expiration' => '12/31/2050 00:00:00',
			'max' => 10,
			'uses' => 54,
			'min_price' => 128
		);

		$this->_post_id = edd_store_discount( $post );
		$this->assertInternalType( 'int', $this->_post_id );

	}

	public function test_addition_of_negative_discount() {
		$post = array(
			'name' => 'Double Double',
			'type' => 'percent',
			'amount' => '-100',
			'code' => 'DOUBLE',
			'product_condition' => 'all',
			'max' => 10,
			'uses' => 54,
			'min_price' => 0
		);

		$this->_negative_post_id = edd_store_discount( $post );
		$this->assertInternalType( 'int', $this->_negative_post_id );
	}

	public function test_addition_of_flat_discount() {
		$post = array(
			'name' => 'Flat Rate',
			'type' => 'flat',
			'amount' => 1,
			'code' => 'FLAT',
			'product_condition' => 'all',
			'max' => 100,
			'uses' => 0,
			'min_price' => 0
		);

		$this->_flat_post_id = edd_store_discount( $post );
		$this->assertInternalType( 'int', $this->_flat_post_id );
	}

	public function test_updating_discount_code() {
		$post = array(
			'name' => 'Test Discount Updated',
			'type' => 'percent',
			'amount' => '20',
			'code' => '20OFF',
			'product_condition' => 'all',
			'start' => '12/12/2050 00:00:00',
			'expiration' => '12/31/2050 00:00:00',
			'max' => 10,
			'uses' => 54,
			'min_price' => 128
		);

		$updated_post_id = edd_store_discount( $post, $this->_post_id );
		$this->assertInternalType( 'int', $updated_post_id );
	}

	public function test_discount_status_update() {
		$this->assertTrue( edd_update_discount_status( $this->_post_id ) );
	}

	public function test_discount_status_update_fail() {
		$this->assertFalse( edd_update_discount_status( -1 ) );
	}

	public function test_discounts_exists() {
		edd_update_discount_status( $this->_post_id );
		$this->assertTrue( edd_has_active_discounts() );
	}

	public function test_discount_exists() {
		$this->assertTrue( edd_discount_exists( $this->_post_id ) );
	}

	public function test_discount_retrieved_from_database() {
		$this->assertObjectHasAttribute( 'ID', edd_get_discount(  $this->_post_id ) );
		$this->assertObjectHasAttribute( 'post_title', edd_get_discount(  $this->_post_id ) );
		$this->assertObjectHasAttribute( 'post_status', edd_get_discount(  $this->_post_id ) );
		$this->assertObjectHasAttribute( 'post_type', edd_get_discount(  $this->_post_id ) );
	}

	public function test_get_discount_code() {
		$this->assertSame( '20OFF', edd_get_discount_code( $this->_post_id ) );
	}

	public function test_discount_start_date() {
		$this->assertSame( '', edd_get_discount_start_date( $this->_post_id ) );
	}

	public function test_discount_expiration_date() {
		$this->assertSame( '', edd_get_discount_expiration( $this->_post_id ) );
	}

	public function test_discount_max_uses() {
		$this->assertSame( 10, edd_get_discount_max_uses( $this->_post_id ) );
	}

	public function test_discount_uses() {
		$this->assertSame( 54, edd_get_discount_uses( $this->_post_id ) );
	}

	public function testDiscountMinPrice() {
		$this->assertSame(128.0, edd_get_discount_min_price($this->_post_id));
	}

	public function test_discount_amount() {
		$this->assertSame( 20.0, edd_get_discount_amount( $this->_post_id ) );
	}

	public function test_discount_amount_negative() {
		$this->assertSame( -100, edd_get_discount_amount( $this->_negative_post_id ) );
	}

	public function test_discount_type() {
		$this->assertSame( 'percent', edd_get_discount_type( $this->_post_id ) );
	}

	public function test_discount_product_condition() {
		$this->assertSame( 'all', edd_get_discount_product_condition( $this->_post_id ) );
	}

	public function test_discount_is_not_global() {
		$this->assertFalse( edd_is_discount_not_global( $this->_post_id ) );
	}

	public function test_discount_is_single_use() {
		$this->assertFalse( edd_discount_is_single_use( $this->_post_id ) );
	}

	public function test_discount_is_started() {
		$this->assertTrue( edd_is_discount_started( $this->_post_id ) );
	}

	public function test_discount_is_expired() {
		$this->assertFalse( edd_is_discount_expired( $this->_post_id ) );
	}

	public function test_discount_is_maxed_out() {
		$this->assertTrue( edd_is_discount_maxed_out( $this->_post_id ) );
	}

	public function test_discount_is_min_met() {
		$this->assertFalse( edd_discount_is_min_met( $this->_post_id ) );
	}

	public function test_discount_is_used() {
		$this->assertFalse( edd_is_discount_used( '20OFF' ) );
		$this->markTestIncomplete('test');
	}

	public function test_discount_is_valid_when_purchasing() {
		$this->assertFalse( edd_is_discount_valid( '20OFF' ) );
	}

	public function test_discount_id_by_code() {
		$this->markTestIncomplete('Fix this per #2302');
		//$this->assertSame( $this->_post_id, edd_get_discount_id_by_code( '20OFF' ) );
	}


	public function test_get_discounted_amount() {
		$this->assertEquals( 432.0, edd_get_discounted_amount( '20OFF', '540' ) );
	}

	public function test_get_discounted_amount_negative() {
		$this->assertEqual( 150.0, edd_get_discounted_amount( 'DOUBLE', '75' ) );
	}

	public function test_get_discounted_amount_flat() {
		$this->assertEqual( 9.0, edd_get_discounted_amount( 'FLAT', '1' ) );
	}

	public function test_increase_discount_usage() {
		$uses = edd_increase_discount_usage( '20OFF' );
		$this->assertSame( 55, $uses );
	}

	public function test_formatted_discount_amount() {
		$this->assertSame( '20%', edd_format_discount_rate( 'percent', get_post_meta( $this->_post_id, '_edd_discount_amount', true ) ) );
	}

	public function test_edd_get_discount_by() {
		$this->assertObjectHasAttribute( 'ID', edd_get_discount_by( 'id', $this->_post_id ) );
		$this->assertSame( $this->_post_id, edd_get_discount_by( 'id', $this->_post_id )->ID );
		$this->assertObjectHasAttribute( 'post_title', edd_get_discount_by( 'code', '20OFF' ) );
		$this->assertSame( $this->_post_id, edd_get_discount_by( 'code', '20OFF' )->ID );
		$this->assertObjectHasAttribute( 'post_name', edd_get_discount_by( 'name', '20 Percent Off' ) );
		$this->assertSame( $this->_post_id, edd_get_discount_by( 'name', '20 Percent Off' )->ID );
	}

	public function test_formatted_discount_amount_negative() {
		$this->assertSame( '-100%', edd_format_discount_rate( 'percent', get_post_meta( $this->_negative_post_id, '_edd_discount_amount', true ) ) );
	}

	public function test_formatted_discount_amount_flat() {
		$this->assertSame( '$1.00', edd_format_discount_rate( 'flat', get_post_meta( $this->_flat_post_id, '_edd_discount_amount', true ) ) );
	}

	public function test_discount_excluded_products() {
		$this->assertInternalType( 'array', edd_get_discount_excluded_products( $this->_post_id ) );
	}

	public function test_discount_product_reqs() {
		$this->assertInternalType( 'array', edd_get_discount_product_reqs( $this->_post_id ) );
	}

	public function test_deletion_of_discount() {
		edd_remove_discount( $this->_post_id );
		$this->assertFalse( wp_cache_get( $this->_post_id, 'posts' ) );

		edd_remove_discount( $this->_negative_post_id );
		$this->assertFalse( wp_cache_get( $this->_negative_post_id, 'posts' ) );
	}
}
