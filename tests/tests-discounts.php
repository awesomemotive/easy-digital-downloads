<?php


/**
 * @group edd_discounts
 */
class Tests_Discounts extends WP_UnitTestCase {
	protected $_post = null;
	protected $_post_id = null;
	protected $_download = null;
	protected $_flat_post_id = null;
	protected $_negative_post_id = null;

	public function setUp() {

		parent::setUp();

		$this->_post_id = EDD_Helper_Discount::create_simple_percent_discount();
		$this->_download = EDD_Helper_Download::create_simple_download();

		$this->_negative_post_id = EDD_Helper_Discount::create_simple_percent_discount();
		update_post_meta( $this->_negative_post_id, '_edd_discount_name', 'Double Double' );
		update_post_meta( $this->_negative_post_id, '_edd_discount_amount', '-100' );
		update_post_meta( $this->_negative_post_id, '_edd_discount_code', 'DOUBLE' );

		$this->_flat_post_id = EDD_Helper_Discount::create_simple_flat_discount();
	}

	public function tearDown() {

		parent::tearDown();

		EDD_Helper_Discount::delete_discount( $this->_post_id );
		EDD_Helper_Discount::delete_discount( $this->_negative_post_id );
		EDD_Helper_Discount::delete_discount( $this->_flat_post_id );

	}

	public function test_discount_created() {

		$this->assertInternalType( 'int', $this->_post_id );

	}

	public function test_addition_of_negative_discount() {

		$this->assertInternalType( 'int', $this->_negative_post_id );
	}

	public function test_addition_of_flat_discount() {

		$this->assertInternalType( 'int', $this->_flat_post_id );
	}

	public function test_updating_discount_code() {
		$post = array(
			'name'              => '20 Percent Off',
			'type'              => 'percent',
			'amount'            => '20',
			'code'              => '20OFF',
			'product_condition' => 'all',
			'start'             => '12/12/2050 00:00:00',
			'expiration'        => '12/31/2050 00:00:00',
			'max'               => 10,
			'uses'              => 54,
			'min_price'         => 128,
			'status'            => 'active'
		);

		$updated_post_id = edd_store_discount( $post, $this->_post_id );
		$this->assertInternalType( 'int', $updated_post_id );
	}

	public function test_discount_status_update() {
		$this->assertTrue( edd_update_discount_status( $this->_post_id, 'active' ) );
	}

	public function test_discount_status_update_fail() {
		$this->assertFalse( edd_update_discount_status( -1 ) );
	}

	public function test_discounts_exists() {
		edd_update_discount_status( $this->_post_id, 'active' );
		$this->assertTrue( edd_has_active_discounts() );
	}

	public function test_is_discount_active() {
		$this->assertTrue( edd_is_discount_active( $this->_post_id, true ) );
		$this->assertTrue( edd_is_discount_active( $this->_post_id, false ) );

		$post = array(
			'name'              => '20 Percent Off',
			'type'              => 'percent',
			'amount'            => '20',
			'code'              => '20OFF',
			'product_condition' => 'all',
			'start'             => '12/12/1998 00:00:00',
			'expiration'        => '12/31/1998 00:00:00',
			'max'               => 10,
			'uses'              => 54,
			'min_price'         => 128,
			'status'            => 'active'
		);

		$expired_post_id = edd_store_discount( $post );

		$this->assertFalse( edd_is_discount_active( $expired_post_id, false ) );

		$this->assertEquals( get_post_meta( $expired_post_id, '_edd_discount_status', true ), 'active' );

		// Update DB
		$this->assertFalse( edd_is_discount_active( $expired_post_id, true ) );
		$this->assertEquals( get_post_meta( $expired_post_id, '_edd_discount_status', true ), 'expired' );
		$this->assertEquals( get_post_status( $expired_post_id ), 'inactive' );
	}

	public function test_discount_exists() {
		$this->assertTrue( edd_discount_exists( $this->_post_id ) );
	}

	public function test_get_discount() {
		$discount = edd_get_discount(  $this->_post_id );
		$this->assertObjectHasAttribute( 'ID', $discount );
		$this->assertObjectHasAttribute( 'post_title', $discount );
		$this->assertObjectHasAttribute( 'post_status', $discount );
		$this->assertObjectHasAttribute( 'post_type', $discount );
	}

	public function test_get_discount_code() {
		$this->assertSame( '20OFF', edd_get_discount_code( $this->_post_id ) );
	}

	public function test_discount_start_date() {
		$this->assertSame( '12/12/2010 00:00:00', edd_get_discount_start_date( $this->_post_id ) );
	}

	public function test_discount_expiration_date() {
		$this->assertSame( '12/31/2050 23:59:59', edd_get_discount_expiration( $this->_post_id ) );
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
		$this->assertSame( -100.0, edd_get_discount_amount( $this->_negative_post_id ) );
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
	}

	public function test_discount_is_valid_when_purchasing() {
		$this->assertFalse( edd_is_discount_valid( '20OFF' ) );
	}

	public function test_discount_id_by_code() {
		$this->assertSame( $this->_post_id, edd_get_discount_id_by_code( '20OFF' ) );
	}


	public function test_get_discounted_amount() {
		$this->assertEquals( '432', edd_get_discounted_amount( '20OFF', '540' ) );
		$this->assertEquals( '150', edd_get_discounted_amount( 'DOUBLE', '75' ) );
		$this->assertEquals( '10', edd_get_discounted_amount( '10FLAT', '20' ) );

		// Test that an invalid Code returns the base price
		$this->assertEquals( '10', edd_get_discounted_amount( 'FAKEDISCOUNT', '10' ) );
	}

	public function test_increase_discount_usage() {
		$id   = edd_get_discount_id_by_code( '20OFF' );
		$uses = edd_get_discount_uses( $id );

		$increased = edd_increase_discount_usage( '20OFF' );
		$this->assertSame( $increased, $uses + 1 );

		// Test missing codes
		$this->assertFalse( edd_increase_discount_usage( 'INVALIDDISCOUNTCODE' ) );
	}

	public function test_discount_inactive_at_max() {
		$current_usage = edd_get_discount_uses( $this->_post_id );
		$max_uses      = edd_get_discount_max_uses( $this->_post_id );

		update_post_meta( $this->_post_id, '_edd_discount_uses', $max_uses - 1 );

		$this->assertEquals( get_post_meta( $this->_post_id, '_edd_discount_status', true ), 'active' );

		$code = edd_get_discount_code( $this->_post_id );
		edd_increase_discount_usage( $code );

		$this->assertEquals( get_post_meta( $this->_post_id, '_edd_discount_status', true ), 'inactive' );
		$this->assertEquals( get_post_status( $this->_post_id ), 'inactive' );

		edd_decrease_discount_usage( $code );
		$this->assertEquals( get_post_meta( $this->_post_id, '_edd_discount_status', true ), 'active' );
		$this->assertEquals( get_post_status( $this->_post_id ), 'active' );
	}

	public function test_decrease_discount_usage() {
		$id   = edd_get_discount_id_by_code( '20OFF' );
		$uses = edd_get_discount_uses( $id );

		$decreased = edd_decrease_discount_usage( '20OFF' );
		$this->assertSame( $decreased, $uses - 1 );

		// Test missing codes
		$this->assertFalse( edd_decrease_discount_usage( 'INVALIDDISCOUNTCODE' ) );
	}

	public function test_formatted_discount_amount() {
		$rate = get_post_meta( $this->_post_id, '_edd_discount_amount', true );
		$this->assertSame( '20%', edd_format_discount_rate( 'percent', $rate ) );
	}

	public function test_edd_get_discount_by() {
		$discount = edd_get_discount_by( 'id', $this->_post_id );
		$this->assertObjectHasAttribute( 'ID', $discount );
		$this->assertSame( $this->_post_id, $discount->ID );
		$this->assertObjectHasAttribute( 'post_title', edd_get_discount_by( 'code', '20OFF' ) );
		$this->assertSame( $this->_post_id, edd_get_discount_by( 'code', '20OFF' )->ID );
		$this->assertObjectHasAttribute( 'post_name', edd_get_discount_by( 'name', '20 Percent Off' ) );
		$this->assertSame( $this->_post_id, edd_get_discount_by( 'name', '20 Percent Off' )->ID );
	}

	public function test_formatted_discount_amount_negative() {
		$amount = edd_get_discount_amount( $this->_negative_post_id );
		$this->assertSame( '-100%', edd_format_discount_rate( 'percent', $amount ) );
	}

	public function test_formatted_discount_amount_flat() {
		$amount = edd_get_discount_amount( $this->_flat_post_id );
		$this->assertSame( '&#36;10.00', edd_format_discount_rate( 'flat', $amount ) );
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

	public function test_set_discount() {

		EDD()->session->set( 'cart_discounts', null );

		edd_add_to_cart( $this->_download->ID );

		$this->assertEquals( '20.00', edd_get_cart_total() );

		edd_set_cart_discount( edd_get_discount_code( $this->_post_id ) );
		$this->assertEquals( '16.00', edd_get_cart_total() );
	}

	public function test_set_multiple_discounts() {

		EDD()->session->set( 'cart_discounts', null );

		edd_update_option( 'allow_multiple_discounts', true );

		edd_add_to_cart( $this->_download->ID );

		$this->assertEquals( '20.00', edd_get_cart_total() );

		// Test a single discount code

		$code = edd_get_discount_code( $this->_post_id );

		$discounts = edd_set_cart_discount( $code );

		$this->assertInternalType( 'array', $discounts );
		$this->assertTrue( 1 == count( $discounts ) );
		$this->assertEquals( '16.00', edd_get_cart_total() );

		// Test a single discount code again but with lower case

		$code = strtolower( $code );

		$discounts = edd_set_cart_discount( $code );

		$this->assertInternalType( 'array', $discounts );
		$this->assertTrue( 1 == count( $discounts ) );
		$this->assertEquals( '16.00', edd_get_cart_total() );

		// Test a new code

		$code_id = EDD_Helper_Discount::create_simple_percent_discount();
		update_post_meta( $code_id, '_edd_discount_code', 'SECONDcode' );

		$discounts = edd_set_cart_discount( 'SECONDCODE' );

		$this->assertInternalType( 'array', $discounts );
		$this->assertTrue( 2 == count( $discounts ) );
		$this->assertEquals( '12.00', edd_get_cart_total() );

	}

	public function test_discountable_subtotal() {
		edd_empty_cart();
		$download_1 = EDD_Helper_Download::create_simple_download();
		$download_2 = EDD_Helper_Download::create_simple_download();
		$discount   = EDD_Helper_Discount::create_simple_flat_discount();

		$post = array(
			'name'              => 'Excludes',
			'amount'            => '1',
			'code'              => 'EXCLUDES',
			'product_condition' => 'all',
			'start'             => '12/12/2050 00:00:00',
			'expiration'        => '12/31/2050 00:00:00',
			'min_price'         => 23,
			'status'            => 'active',
			'excluded-products' => array( $download_2->ID ),
		);

		edd_store_discount( $post, $discount );

		edd_add_to_cart( $download_1->ID );
		edd_add_to_cart( $download_2->ID );
		$this->assertEquals( '20', edd_get_cart_discountable_subtotal( $discount ) );

		$download_3 = EDD_Helper_Download::create_simple_download();
		edd_add_to_cart( $download_3->ID );
		$this->assertEquals( '40', edd_get_cart_discountable_subtotal( $discount ) );

		EDD_Helper_Download::delete_download( $download_1->ID );
		EDD_Helper_Download::delete_download( $download_2->ID );
		EDD_Helper_Download::delete_download( $download_3->ID );
		EDD_Helper_Discount::delete_discount( $discount );
	}

	public function test_discount_min_excluded_products() {
		edd_empty_cart();
		$download_1 = EDD_Helper_Download::create_simple_download();
		$download_2 = EDD_Helper_Download::create_simple_download();
		$discount   = EDD_Helper_Discount::create_simple_flat_discount();

		$post = array(
			'name'              => 'Excludes',
			'amount'            => '1',
			'code'              => 'EXCLUDES',
			'product_condition' => 'all',
			'start'             => '12/12/2050 00:00:00',
			'expiration'        => '12/31/2050 00:00:00',
			'min_price'         => 23,
			'status'            => 'active',
			'excluded-products' => array( $download_2->ID ),
		);

		edd_store_discount( $post, $discount );

		edd_add_to_cart( $download_1->ID );
		edd_add_to_cart( $download_2->ID );
		$this->assertFalse( edd_discount_is_min_met( $discount ) );

		$download_3 = EDD_Helper_Download::create_simple_download();
		edd_add_to_cart( $download_3->ID );
		$this->assertTrue( edd_discount_is_min_met( $discount ) );

		EDD_Helper_Download::delete_download( $download_1->ID );
		EDD_Helper_Download::delete_download( $download_2->ID );
		EDD_Helper_Download::delete_download( $download_3->ID );
		EDD_Helper_Discount::delete_discount( $discount );
	}

}
