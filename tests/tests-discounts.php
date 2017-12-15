<?php


/**
 * @group edd_discounts
 */
class Tests_Discounts extends EDD_UnitTestCase {
	public static $_post = null;
	public static $_post_id = null;
	public static $_download = null;
	public static $_flat_post_id = null;
	public static $_negative_post_id = null;

	public function setUp() {

		parent::setUp();

		self::$_post_id = EDD_Helper_Discount::create_simple_percent_discount();
		self::$_download = EDD_Helper_Download::create_simple_download();

		self::$_negative_post_id = EDD_Helper_Discount::create_simple_percent_discount();

		// Create legacy data records for backwards compatibility
		EDD()->discount_meta->add_meta( self::$_post_id, '_edd_discount_legacy_id', self::$_post_id );
		EDD()->discount_meta->add_meta( self::$_negative_post_id, '_edd_discount_legacy_id', self::$_negative_post_id );

		// Update meta via old dmethod. If these work properly, it helps show backwards compatibility is working
		update_post_meta( self::$_negative_post_id, '_edd_discount_name', 'Double Double' );
		update_post_meta( self::$_negative_post_id, '_edd_discount_amount', '-100' );
		update_post_meta( self::$_negative_post_id, '_edd_discount_code', 'DOUBLE' );

		self::$_flat_post_id = EDD_Helper_Discount::create_simple_flat_discount();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_discount_created() {

		$this->assertInternalType( 'int', self::$_post_id );

	}

	public function test_addition_of_negative_discount() {

		$this->assertInternalType( 'int', self::$_negative_post_id );
	}

	public function test_addition_of_flat_discount() {

		$this->assertInternalType( 'int', self::$_flat_post_id );
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

		$updated_post_id = edd_store_discount( $post, self::$_post_id );
		$this->assertInternalType( 'int', $updated_post_id );
	}

	public function test_discount_status_update() {
		$this->assertTrue( edd_update_discount_status( self::$_post_id, 'active' ) );
	}

	public function test_discount_status_update_fail() {
		$this->assertFalse( edd_update_discount_status( -1 ) );
	}

	public function test_discounts_exists() {
		edd_update_discount_status( self::$_post_id, 'active' );
		$this->assertTrue( edd_has_active_discounts() );
	}

	public function test_is_discount_active() {
		edd_update_discount_status( self::$_post_id, 'active' );
		$this->assertTrue( edd_is_discount_active( self::$_post_id, true ) );
		$this->assertTrue( edd_is_discount_active( self::$_post_id, false ) );

		$post = array(
			'name'              => '20 Percent Off',
			'type'              => 'percent',
			'amount'            => '20',
			'code'              => '20OFFEXPIRED',
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

		$this->assertEquals( 'expired', get_post_meta( $expired_post_id, '_edd_discount_status', true ) );

	}

	public function test_discount_exists() {
		$this->assertTrue( edd_discount_exists( self::$_post_id ) );
	}

	public function test_get_discount() {

		edd_update_discount_status( self::$_post_id, 'active' );		

		$discount = edd_get_discount(  self::$_post_id );
		$this->assertEquals( self::$_post_id, $discount->ID );
		$this->assertEquals( '20 Percent Off', $discount->post_title );
		$this->assertEquals( 'active', $discount->post_status );
	}

	public function test_get_discount_code() {
		$this->assertSame( '20OFF', edd_get_discount_code( self::$_post_id ) );
	}

	public function test_discount_start_date() {
		$this->assertSame( '2010-12-12 00:00:00', edd_get_discount_start_date( self::$_post_id ) );
	}

	public function test_discount_expiration_date() {
		$this->assertSame( '2050-12-31 23:59:59', edd_get_discount_expiration( self::$_post_id ) );
	}

	public function test_discount_max_uses() {
		$this->assertSame( 10, edd_get_discount_max_uses( self::$_post_id ) );
	}

	public function test_discount_uses() {
		$this->assertSame( 54, edd_get_discount_uses( self::$_post_id ) );
	}

	public function testDiscountMinPrice() {
		$this->assertSame(128.0, edd_get_discount_min_price(self::$_post_id));
	}

	public function test_discount_amount() {
		$this->assertSame( 20.0, edd_get_discount_amount( self::$_post_id ) );
	}

	public function test_discount_amount_negative() {
		$this->assertSame( -100.0, edd_get_discount_amount( self::$_negative_post_id ) );
	}

	public function test_discount_type() {
		$this->assertSame( 'percent', edd_get_discount_type( self::$_post_id ) );
	}

	public function test_discount_product_condition() {
		$this->assertSame( 'all', edd_get_discount_product_condition( self::$_post_id ) );
	}

	public function test_discount_is_not_global() {
		$this->assertFalse( edd_is_discount_not_global( self::$_post_id ) );
	}

	public function test_discount_is_single_use() {
		$this->assertFalse( edd_discount_is_single_use( self::$_post_id ) );
	}

	public function test_discount_is_started() {
		$this->assertTrue( edd_is_discount_started( self::$_post_id ) );
	}

	public function test_discount_is_expired() {
		$this->assertFalse( edd_is_discount_expired( self::$_post_id ) );
	}

	public function test_discount_is_maxed_out() {
		$this->assertTrue( edd_is_discount_maxed_out( self::$_post_id ) );
	}

	public function test_discount_is_min_met() {
		$this->assertFalse( edd_discount_is_min_met( self::$_post_id ) );
	}

	public function test_discount_is_used() {
		$this->assertFalse( edd_is_discount_used( '20OFF' ) );
	}

	public function test_is_used_case_insensitive() {
		$payment_id = EDD_Helper_Payment::create_simple_payment();
		$payment    = edd_get_payment( $payment_id );
		$payment->discounts = '20off';
		$payment->status = 'publish';
		$payment->save();

		$discount = new EDD_Discount( '20OFF', true );
		$discount->is_single_use = true;
		$this->assertTrue( $discount->is_used( 'admin@example.org', false ) );
		$discount->is_single_use = false;
		EDD_Helper_Payment::delete_payment( $payment_id );
	}

	public function test_discount_is_valid_when_purchasing() {
		$this->assertFalse( edd_is_discount_valid( '20OFF' ) );
	}

	public function test_discount_id_by_code() {

		$id = edd_get_discount_id_by_code( '20OFF' );
		$discount = edd_get_discount_by( 'code', '20OFF' );

		$this->assertSame( $discount->id, $id );
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
		$this->assertequals( $increased, (int) $uses + 1 );

		// Test missing codes
		$this->assertFalse( edd_increase_discount_usage( 'INVALIDDISCOUNTCODE' ) );
	}

	public function test_discount_inactive_at_max() {
		$current_usage = edd_get_discount_uses( self::$_post_id );
		$max_uses      = edd_get_discount_max_uses( self::$_post_id );

		update_post_meta( self::$_post_id, '_edd_discount_uses', $max_uses - 1 );

		$this->assertEquals( 'active', get_post_meta( self::$_post_id, '_edd_discount_status', true ) );

		$code = edd_get_discount_code( self::$_post_id );
		edd_increase_discount_usage( $code );

		$this->assertEquals( 'inactive', get_post_meta( self::$_post_id, '_edd_discount_status', true ) );

		edd_decrease_discount_usage( $code );
		$this->assertEquals( 'active', get_post_meta( self::$_post_id, '_edd_discount_status', true ) );
	}

	public function test_decrease_discount_usage() {
		$id   = edd_get_discount_id_by_code( '20OFF' );
		$uses = edd_get_discount_uses( $id );

		$decreased = edd_decrease_discount_usage( '20OFF' );
		$this->assertSame( $decreased, (int) $uses - 1 );

		// Test missing codes
		$this->assertFalse( edd_decrease_discount_usage( 'INVALIDDISCOUNTCODE' ) );
	}

	public function test_formatted_discount_amount() {
		$rate = get_post_meta( self::$_post_id, '_edd_discount_amount', true );
		$this->assertSame( '20%', edd_format_discount_rate( 'percent', $rate ) );
	}

	public function test_edd_get_discount_by() {
		$discount = edd_get_discount_by( 'id', self::$_post_id );
		$this->assertEquals( $discount->ID, self::$_post_id );
		$this->assertEquals( '20 Percent Off', edd_get_discount_by( 'code', '20OFF' )->post_title );
		$this->assertEquals( $discount->ID, edd_get_discount_by( 'code', '20OFF' )->ID );
		$this->assertEquals( $discount->ID, edd_get_discount_by( 'name', '20 Percent Off' )->ID );
	}

	public function test_formatted_discount_amount_negative() {
		$amount = edd_get_discount_amount( self::$_negative_post_id );
		$this->assertSame( '-100%', edd_format_discount_rate( 'percent', $amount ) );
	}

	public function test_formatted_discount_amount_flat() {
		$amount = edd_get_discount_amount( self::$_flat_post_id );
		$this->assertSame( '&#36;10.00', edd_format_discount_rate( 'flat', $amount ) );
	}

	public function test_discount_excluded_products() {
		$this->assertInternalType( 'array', edd_get_discount_excluded_products( self::$_post_id ) );
	}

	public function test_discount_product_reqs() {
		$this->assertInternalType( 'array', edd_get_discount_product_reqs( self::$_post_id ) );
	}

	public function test_deletion_of_discount() {
		edd_remove_discount( self::$_post_id );
		$this->assertFalse( wp_cache_get( self::$_post_id, 'posts' ) );

		edd_remove_discount( self::$_negative_post_id );
		$this->assertFalse( wp_cache_get( self::$_negative_post_id, 'posts' ) );
	}

	public function test_set_discount() {

		EDD()->session->set( 'cart_discounts', null );

		edd_add_to_cart( self::$_download->ID );

		$this->assertEquals( '20.00', edd_get_cart_total() );

		edd_set_cart_discount( edd_get_discount_code( self::$_post_id ) );
		$this->assertEquals( '16.00', edd_get_cart_total() );
	}

	public function test_set_multiple_discounts() {

		EDD()->session->set( 'cart_discounts', null );

		edd_update_option( 'allow_multiple_discounts', true );

		edd_add_to_cart( self::$_download->ID );

		$this->assertEquals( '20.00', edd_get_cart_total() );

		// Test a single discount code

		$code = edd_get_discount_code( self::$_post_id );

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

		$discount_obj = edd_get_discount( $discount );
		$this->assertFalse( edd_is_discount_valid( $discount_obj->code ) );

		EDD_Helper_Download::delete_download( $download_1->ID );
		EDD_Helper_Download::delete_download( $download_2->ID );
		EDD_Helper_Download::delete_download( $download_3->ID );
		EDD_Helper_Discount::delete_discount( $discount );
	}

	public function test_edd_get_discounts() {
		$found_discounts = edd_get_discounts( array( 'posts_per_page' => 3 ));
		$this->assertSame( 3, count( $found_discounts ) );
	}
}
