<?php
namespace EDD\Tests\Discounts;

use EDD\Tests\Helpers;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests for Discounts API.
 *
 * @covers \EDD_Discount
 * @group edd_discounts
 *
 * @coversDefaultClass \EDD_Discount
 */
class Discounts extends EDD_UnitTestCase {

	/**
	 * Download test fixture.
	 *
	 * @var \WP_Post
	 * @static
	 */
	protected static $download;

	/**
	 * Discount ID.
	 *
	 * @var int
	 * @static
	 */
	protected static $discount_id;

	/**
	 * Discount object test fixture.
	 *
	 * @var \EDD_Discount
	 * @static
	 */
	protected static $discount;

	/**
	 * Flat discount test fixture.
	 *
	 * @var int
	 * @static
	 */
	protected static $flatdiscount_id;

	/**
	 * Negative discount test fixture.
	 *
	 * @var int
	 * @static
	 */
	protected static $negativediscount_id;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$download = Helpers\EDD_Helper_Download::create_simple_download();

		self::$discount_id         = Helpers\EDD_Helper_Discount::create_simple_percent_discount();
		self::$negativediscount_id = Helpers\EDD_Helper_Discount::create_simple_negative_percent_discount();
		self::$flatdiscount_id     = Helpers\EDD_Helper_Discount::create_simple_flat_discount();

		self::$discount = edd_get_discount( self::$discount_id );
	}

	/**
	 * Run after each test to empty the cart and reset the test store.
	 *
	 * @access public
	 */
	public function tearDown(): void {
		edd_empty_cart();

		parent::tearDown();
	}

	/**
	 * @covers ::setup_discount()
	 */
	public function test_discount_instantiated() {
		$this->assertGreaterThan( 0, self::$discount->id );
	}

	/**
	 * @covers ::setup_discount()
	 */
	public function test_id_is_0_when_no_id_is_passed() {
		$d = new \EDD_Discount();

		$this->assertTrue( 0 === $d->id );
	}

	/**
	 * @covers ::setup_discount()
	 */
	public function test_discount_id_matches_id() {
		$this->assertEquals( self::$discount->id, self::$discount_id );
	}

	/**
	 * @covers ::setup_discount()
	 */
	public function test_discount_id_matches_capital_ID() {
		$this->assertEquals( self::$discount->ID, self::$discount_id );
	}

	/**
	 * @covers ::setup_discount()
	 */
	public function test_get_discount_name() {
		$this->assertEquals( '20 Percent Off', self::$discount->name );
	}

	/**
	 * @covers ::setup_discount()
	 */
	public function test_get_discount_name_by_property() {
		$this->assertEquals( '20OFF', self::$discount->code );
	}

	/**
	 * @covers ::get_code()
	 */
	public function test_get_discount_name_by_method() {
		$this->assertEquals( '20OFF', self::$discount->get_code() );
	}

	/**
	 * @covers ::get_status()
	 */
	public function test_get_discount_status_by_property() {
		$this->assertEquals( 'active', self::$discount->status );
	}

	/**
	 * @covers ::get_status()
	 */
	public function test_get_discount_status_by_method() {
		$this->assertEquals( 'active', self::$discount->get_status() );
	}

	/**
	 * @covers ::get_expiration()
	 */
	public function test_get_discount_expiration_by_property_backcompat() {
		$this->assertEquals( date( 'Y-m-d', time() ) . ' 23:59:59', self::$discount->expiration );
	}

	/**
	 * @covers ::get_expiration()
	 */
	public function test_get_discount_expiration_by_method_backcompat() {
		$this->assertEquals( date( 'Y-m-d', time() ) . ' 23:59:59', self::$discount->get_expiration() );
	}

	/**
	 * @covers ::end_date
	 */
	public function test_get_discount_end_date_by_property() {
		$this->assertEquals( date( 'Y-m-d', time() ) . ' 23:59:59', self::$discount->end_date );
	}

	/**
	 * @covers ::get_min_price()
	 */
	public function test_get_discount_min_price_by_property() {
		$this->assertEquals( 128, self::$discount->min_charge_amount );
	}

	/**
	 * @covers ::get_min_price()
	 */
	public function test_get_discount_min_price_by_method() {
		$this->assertEquals( 128, self::$discount->get_min_price() );
	}

	/**
	 * @covers ::get_is_single_use()
	 */
	public function test_get_discount_is_single_use_should_return_false() {
		$this->assertFalse( self::$discount->get_is_single_use() );
	}

	/**
	 * @covers ::get_once_per_customer()
	 */
	public function test_get_discount_is_once_per_customer_should_return_false() {
		$this->assertFalse( self::$discount->get_once_per_customer() );
	}

	/**
	 * @covers ::exists()
	 */
	public function test_discount_exists_should_return_true() {
		$this->assertTrue( self::$discount->exists() );
	}

	/**
	 * @covers ::get_type()
	 */
	public function test_get_discount_type_by_property() {
		$this->assertEquals( 'percent', self::$discount->type );
	}

	/**
	 * @covers ::get_type()
	 */
	public function test_get_discount_type_by_method() {
		$this->assertEquals( 'percent', self::$discount->get_type() );
	}

	/**
	 * @covers ::get_type()
	 */
	public function test_get_discount_type_of_flat_discount() {
		$d = new \EDD_Discount( self::$flatdiscount_id );
		$this->assertEquals( 'flat', $d->type );
	}

	/**
	 * @covers ::get_amount()
	 */
	public function test_get_discount_amount_by_property() {
		$this->assertEquals( '20', self::$discount->amount );
	}

	/**
	 * @covers ::get_amount()
	 */
	public function test_get_discount_amount_by_method() {
		$this->assertEquals( '20', self::$discount->get_amount() );
	}

	/**
	 * @covers ::get_product_reqs()
	 */
	public function test_get_discount_product_requirements_by_method() {
		$this->assertSame( array(), self::$discount->get_product_reqs() );
	}

	/**
	 * @covers ::get_product_reqs()
	 */
	public function test_get_discount_product_requirements_by_property() {
		$this->assertSame( array(), self::$discount->product_reqs );
	}

	/**
	 * @covers ::get_excluded_products()
	 */
	public function test_get_discount_excluded_products_by_method() {
		$this->assertSame( array(), self::$discount->get_excluded_products() );
	}

	/**
	 * @covers ::get_excluded_products()
	 */
	public function test_get_discount_excluded_products_by_property() {
		$this->assertSame( array(), self::$discount->excluded_products );
	}

	public function test_get_discount_categories_by_method() {
		$this->assertSame( array(), self::$discount->get_categories() );
	}

	public function test_get_discount_categories_by_property() {
		$this->assertSame( array(), self::$discount->categories );
	}

	public function test_get_discount_term_condition_by_method() {
		$this->assertSame( '', self::$discount->get_term_condition() );
	}

	public function test_get_discount_term_condition_by_property() {
		$this->assertSame( '', self::$discount->term_condition );
	}

	/**
	 * @covers ::save()
	 * @covers ::add()
	 */
	public function test_discount_save() {
		$discount = new \EDD_Discount();
		$discount->code = '30FLAT';
		$discount->name = '$30 Off';
		$discount->type = 'flat';
		$discount->amount = '30';

		$discount->save();

		$this->assertGreaterThan( 0, (int) $discount->id );
	}

	/**
	 * @covers ::add()
	 * @covers ::sanitize_columns()
	 * @covers ::convert_legacy_args()
	 */
	public function test_discount_add() {
		$args = array(
			'code'   => '30FLAT',
			'name'   => '$30 Off',
			'type'   => 'flat',
			'amount' => 30,
		);

		$discount = new \EDD_Discount();
		$discount->add( $args );

		$this->assertGreaterThan( 0, $discount->id );
	}

	/**
	 * @covers ::update()
	 * @covers ::sanitize_columns()
	 * @covers ::convert_legacy_args()
	 */
	public function test_discount_update_type() {
		$args = array(
			'type'   => 'flat',
			'amount' => 50,
		);

		self::$discount->update( $args );

		$this->assertEquals( 'flat', self::$discount->type );
	}

	/**
	 * @covers ::update()
	 * @covers ::sanitize_columns()
	 * @covers ::convert_legacy_args()
	 */
	public function test_discount_update_amount() {
		$args = array(
			'amount' => 50,
		);

		self::$discount->update( $args );

		$this->assertEquals( 50.0, self::$discount->amount );
	}

	/**
	 * @covers ::update_status()
	 * @covers ::get_status()
	 */
	public function test_discount_update_status_with_no_args() {
		self::$discount->update_status();

		$this->assertEquals( 'active', self::$discount->status );
	}

	/**
	 * @covers ::update_status()
	 * @covers ::get_status()
	 */
	public function test_discount_update_status_to_inactive() {
		self::$discount->update_status( 'inactive' );

		$this->assertEquals( 'inactive', self::$discount->status );
	}

	/**
	 * @covers ::update_status()
	 * @covers ::get_status()
	 */
	public function test_discount_update_status_to_archived() {
		self::$discount->update_status( 'archived' );

		$this->assertEquals( 'archived', self::$discount->status );
	}

	/**
	 * @covers ::is_product_requirements_met()
	 */
	public function test_discount_is_product_requirements_met() {
		$args = array(
			'product_reqs' => array( self::$download->ID ),
		);

		edd_update_discount( self::$discount_id, $args );

		edd_add_to_cart( self::$download->ID );

		$discount = edd_get_discount( self::$discount_id );

		$this->assertTrue( $discount->is_product_requirements_met() );
	}

	/**
	 * @covers ::is_product_requirements_met() with variable download
	 * @covers edd_validate_discount() with variable download
	 */
	public function test_discount_is_product_requirements_met_with_variable_download() {
		$variable_download_id = Helpers\EDD_Helper_Download::create_variable_download();
		$variable_download    = edd_get_download( $variable_download_id->ID );
		$price_id             = 0;

		$args = array(
			'product_reqs' => array( $variable_download->ID . '_' . $price_id ),
		);

		edd_update_discount( self::$discount_id, $args );

		edd_add_to_cart( $variable_download->ID, array( 'price_id' => $price_id ) );

		$discount = edd_get_discount( self::$discount_id );

		$this->assertTrue( $discount->is_product_requirements_met() );
		$this->assertTrue( edd_validate_discount( self::$discount_id, array( $variable_download->ID . '_' . $price_id ) ) );
	}

	public function test_discount_is_product_requirements_met_with_variable_download_all_variations() {
		$variable_download_id = Helpers\EDD_Helper_Download::create_variable_download();
		$variable_download    = edd_get_download( $variable_download_id->ID );
		$price_id             = 0;

		$args = array(
			'product_reqs' => array( $variable_download->ID ),
		);

		edd_update_discount( self::$discount_id, $args );

		edd_add_to_cart( $variable_download->ID, array( 'price_id' => $price_id ) );

		$discount = edd_get_discount( self::$discount_id );

		$this->assertTrue( $discount->is_product_requirements_met() );
		$this->assertTrue( edd_validate_discount( self::$discount_id, array( $variable_download->ID . '_' . $price_id ) ) );
	}


	/**
	 * @covers ::is_product_requirements_met() with variable download
	 * @covers edd_validate_discount() with variable download
	 */
	public function test_discount_is_product_requirements_met_with_variable_download_is_false() {
		$variable_download_id = Helpers\EDD_Helper_Download::create_variable_download();
		$variable_download    = edd_get_download( $variable_download_id->ID );
		$price_id             = 0;

		$args = array(
			'product_reqs' => array( $variable_download->ID . '_' . $price_id ),
		);

		edd_update_discount( self::$discount_id, $args );

		edd_add_to_cart( $variable_download->ID, array( 'price_id' => 1 ) );

		$discount = edd_get_discount( self::$discount_id );

		$this->assertFalse( $discount->is_product_requirements_met() );
		$this->assertFalse( edd_validate_discount( self::$discount_id, array( $variable_download->ID . '_1' ) ) );
	}

	/**
	 * @covers edd_validate_discount
	 */
	public function test_edd_validate_discount_product_requirements_any_all_in_array() {
		$products = array( self::$download->ID, 99999 );
		$args     = array(
			'product_reqs'      => $products,
			'product_condition' => 'any',
			'max_uses'          => 10000,
		);

		edd_update_discount( self::$discount_id, $args );
		$this->assertTrue( edd_validate_discount( self::$discount_id, $products ) );
	}

	/**
	 * @covers edd_validate_discount
	 */
	public function test_edd_validate_discount_product_requirements_any_none_in_array() {
		$products = array( self::$download->ID, 99999 );
		$args     = array(
			'product_reqs'      => $products,
			'product_condition' => 'any',
			'max_uses'          => 10000,
		);

		edd_update_discount( self::$discount_id, $args );
		$this->assertFalse( edd_validate_discount( self::$discount_id, array( 123 ) ) );
	}

	/**
	 * @covers edd_validate_discount
	 */
	public function test_edd_validate_discount_product_requirements_any_one_in_array() {
		$products = array( self::$download->ID, 99999 );
		$args     = array(
			'product_reqs'      => $products,
			'product_condition' => 'any',
			'max_uses'          => 10000,
		);

		edd_update_discount( self::$discount_id, $args );
		$this->assertTrue( edd_validate_discount( self::$discount_id, array( self::$download->ID ) ) );
	}

	/**
	 * @covers edd_validate_discount
	 */
	public function test_edd_validate_discount_product_requirements_all_all_in_array() {
		$products = array( self::$download->ID, 99999 );
		$args     = array(
			'product_reqs'      => $products,
			'product_condition' => 'all',
			'max_uses'          => 10000,
		);

		edd_update_discount( self::$discount_id, $args );
		$this->assertTrue( edd_validate_discount( self::$discount_id, $products ) );
	}

	/**
	 * @covers edd_validate_discount
	 */
	public function test_edd_validate_discount_product_requirements_all_none_in_array() {
		$products = array( self::$download->ID, 99999 );
		$args     = array(
			'product_reqs'      => $products,
			'product_condition' => 'all',
			'max_uses'          => 10000,
		);

		edd_update_discount( self::$discount_id, $args );
		$this->assertFalse( edd_validate_discount( 123 ) );
	}

	/**
	 * @covers edd_validate_discount
	 */
	public function test_edd_validate_discount_product_requirements_all_one_in_array() {
		$products = array( self::$download->ID, 99999 );
		$args     = array(
			'product_reqs'      => $products,
			'product_condition' => 'all',
			'max_uses'          => 10000,
		);

		edd_update_discount( self::$discount_id, $args );
		$this->assertFalse( edd_validate_discount( self::$discount_id, array( self::$download->ID ) ) );
	}

	/**
	 * @covers edd_validate_discount
	 */
	public function test_edd_validate_discount_excluded_products_all_in_array() {
		$products = array( self::$download->ID, 99999 );
		$args     = array(
			'product_reqs'      => array(),
			'max_uses'          => 10000,
			'excluded_products' => $products,
		);

		edd_update_discount( self::$discount_id, $args );
		$this->assertFalse( edd_validate_discount( self::$discount_id, $products ) );
	}

	/**
	 * @covers edd_validate_discount
	 */
	public function test_edd_validate_discount_excluded_products_none_in_array() {
		$products = array( self::$download->ID, 99999 );
		$args     = array(
			'product_reqs'      => array(),
			'max_uses'          => 10000,
			'excluded_products' => $products,
		);

		edd_update_discount( self::$discount_id, $args );
		$this->assertTrue( edd_validate_discount( self::$discount_id, array( 546 ) ) );
	}

	/**
	 * @covers edd_validate_discount
	 */
	public function test_edd_validate_discount_excluded_products_one_in_array() {
		$products = array( self::$download->ID, 99999 );
		$args     = array(
			'product_reqs'      => array(),
			'max_uses'          => 10000,
			'excluded_products' => $products,
		);

		edd_update_discount( self::$discount_id, $args );
		$this->assertFalse( edd_validate_discount( self::$discount_id, array( self::$download->ID ) ) );
	}

	/**
	 * @covers ::edit_url()
	 */
	public function test_discount_edit_url() {
		$this->assertStringContainsString( 'edit.php?post_type=download&#038;page=edd-discounts', self::$discount->edit_url() );
	}

	/**
	 * @covers ::update_meta()
	 */
	public function test_discount_update_meta() {
		edd_update_adjustment_meta( self::$discount->id, 'test_meta_key', 'test_meta_value' );

		$this->assertEquals( 'test_meta_value', edd_get_adjustment_meta( self::$discount->id, 'test_meta_key', true ) );
	}

	/**
	 * @covers ::delete_meta()
	 */
	public function test_discount_delete_meta_with_no_meta_key_should_be_false() {
		$this->assertFalse( edd_delete_adjustment_meta( self::$download->ID, '' ) );
	}

	/*
	 * Legacy tests
	 *
	 * All tests below are from before EDD 3.0 when discounts were stored as wp_posts.
	 * EDD 3.0 stores them in a custom table.
	 * The below tests are left here to help ensure the backwards compatibility layers work properly
	 */
	public function test_discount_created() {
		$this->assertIsInt( self::$discount_id );
	}

	public function test_addition_of_negative_discount() {
		$this->assertIsInt( self::$negativediscount_id );
	}

	public function test_addition_of_flat_discount() {
		$this->assertIsInt( self::$flatdiscount_id );
	}

	/**
	 * @covers \edd_store_discount()
	 */
	public function test_updating_discount_code() {
		$post = array(
			'name'              => '20 Percent Off',
			'type'              => 'percent',
			'amount'            => '20',
			'code'              => '20OFF',
			'product_condition' => 'all',
			'start'             => date( 'm/d/Y', time() ) . ' 00:00:00',
			'expiration'        => date( 'm/d/Y', time() ) . ' 23:59:59',
			'max'               => 10,
			'uses'              => 54,
			'min_price'         => 128,
			'status'            => 'active'
		);

		$updated_id = edd_store_discount( $post, self::$discount_id );
		$this->assertEquals( $updated_id, self::$discount_id );
	}

	/**
	 * @covers \edd_update_discount_status()
	 */
	public function test_discount_status_update_inactive() {
		$this->assertTrue( edd_update_discount_status( self::$discount_id, 'inactive' ) );
		$discount = edd_get_discount( self::$discount_id );
		$this->assertEquals( 'inactive', $discount->status );

		$this->assertTrue( edd_update_discount_status( self::$discount_id, 'active' ) );
		$discount = edd_get_discount( self::$discount_id );
		$this->assertEquals( 'active', $discount->status );
	}

	/**
	 * @covers \edd_update_discount_status()
	 */
	public function test_discount_status_update() {
		$this->assertTrue( edd_update_discount_status( self::$discount_id, 'active' ) );
	}

	/**
	 * @covers \edd_update_discount_status()
	 */
	public function test_discount_status_update_fail() {
		$this->assertFalse( edd_update_discount_status( -1 ) );
	}

	/**
	 * @covers \edd_update_discount_status()
	 * @covers \edd_is_discount_active()
	 * @covers \edd_store_discount()
	 */
	public function test_is_discount_active() {
		$this->setExpectedIncorrectUsage( 'get_post_meta()' );

		edd_update_discount_status( self::$discount_id, 'active' );

		$this->assertTrue( edd_is_discount_active( self::$discount_id, true  ) );
		$this->assertTrue( edd_is_discount_active( self::$discount_id, false ) );

		$post = array(
			'name'              => '20 Percent Off',
			'type'              => 'percent',
			'amount'            => '20',
			'code'              => '20OFFEXPIRED',
			'product_condition' => 'all',
			'start'             => date( 'm/d/Y', time() - DAY_IN_SECONDS*5 ) . ' 00:00:00',
			'expiration'        => date( 'm/d/Y', time() - DAY_IN_SECONDS*5 ) . ' 23:59:59',
			'max'               => 10,
			'uses'              => 54,
			'min_price'         => 128,
			'status'            => 'active'
		);

		$expired_discount_id = edd_store_discount( $post );

		$this->assertFalse( edd_is_discount_active( $expired_discount_id, true ) );

		$this->assertEquals( 'expired', get_post_meta( $expired_discount_id, '_edd_discount_status', true ) );
	}

	/**
	 * @covers \edd_discount_exists()
	 */
	public function test_discount_exists_helper() {
		$this->assertTrue( edd_discount_exists( self::$discount_id ) );
	}

	/**
	 * @covers \edd_update_discount_status()
	 * @covers \edd_get_discount()
	 */
	public function test_get_discount() {
		edd_update_discount_status( self::$discount_id, 'active' );

		$discount = edd_get_discount( self::$discount_id );

		$this->assertEquals( self::$discount_id, $discount->id );
		$this->assertEquals( '20 Percent Off', $discount->post_title );
		$this->assertEquals( 'active', $discount->post_status );
	}

	/**
	 * @covers \edd_get_discount_code()
	 */
	public function test_get_discount_code() {
		$this->assertSame( '20OFF', edd_get_discount_code( self::$discount_id ) );
	}

	/**
	 * @covers \edd_get_discount_start_date()
	 */
	public function test_discount_start_date() {
		$this->assertSame( date( 'Y-m-d', time() ) . ' 00:00:00', edd_get_discount_start_date( self::$discount_id ) );
	}

	/**
	 * @covers \edd_get_discount_expiration()
	 */
	public function test_discount_expiration_date() {
		$this->assertSame( date( 'Y-m-d', time() ) . ' 23:59:59', edd_get_discount_expiration( self::$discount_id ) );
	}

	/**
	 * @covers \edd_get_discount_min_price()
	 */
	public function test_discount_min_price() {
		$this->assertSame( '128.00', edd_get_discount_min_price( self::$discount_id ) );
	}

	/**
	 * @covers \edd_get_discount_amount()
	 */
	public function test_discount_amount() {
		$this->assertSame( 20.0, edd_get_discount_amount( self::$discount_id ) );
	}

	/**
	 * @covers \edd_get_discount_amount()
	 */
	public function test_discount_amount_negative() {
		$this->assertSame( -100.0, edd_get_discount_amount( self::$negativediscount_id ) );
	}

	/**
	 * @covers \edd_get_discount_type()
	 */
	public function test_discount_type() {
		$this->assertSame( 'percent', edd_get_discount_type( self::$discount_id ) );
	}

	/**
	 * @covers \edd_is_discount_not_global()
	 */
	public function test_discount_is_not_global() {
		$this->assertFalse( edd_is_discount_not_global( self::$discount_id ) );
	}

	/**
	 * @covers \edd_discount_is_single_use()
	 */
	public function test_discount_is_single_use() {
		$this->assertFalse( edd_discount_is_single_use( self::$discount_id ) );
	}

	/**
	 * @covers \edd_is_discount_started()
	 */
	public function test_discount_is_started() {
		$this->assertTrue( edd_is_discount_started( self::$discount_id ) );
	}

	/**
	 * @covers \edd_is_discount_expired()
	 */
	public function test_discount_is_expired() {
		$this->assertFalse( edd_is_discount_expired( self::$discount_id ) );
	}

	public function test_discount_is_expired_timezone_change() {
		update_option( 'gmt_offset', 25 );
		$this->assertFalse( edd_is_discount_expired( self::$discount_id ) );
		update_option( 'gmt_offset', 0 );
	}

	/**
	 * @covers \edd_discount_is_min_met()
	 */
	public function test_discount_is_min_met() {
		$this->assertFalse( edd_discount_is_min_met( self::$discount_id ) );
	}

	/**
	 * @covers \edd_is_discount_used()
	 * @covers ::is_used()
	 */
	public function test_discount_is_used() {
		$this->assertFalse( edd_is_discount_used( '20OFF' ) );
	}

	/**
	 * @covers ::setup_discount()
	 * @covers ::get_is_single_use()
	 * @covers ::is_used()
	 *
	 */
	public function test_is_used_case_insensitive() {
		$payment_id         = Helpers\EDD_Helper_Payment::create_simple_payment();
		$payment            = edd_get_payment( $payment_id );
		$payment->discounts = '20off';
		$payment->status    = 'publish';
		$payment->save();

		$discount                = new \EDD_Discount( '20OFF', true );
		$discount->is_single_use = true;
		$this->assertTrue( $discount->is_used( 'admin@example.org', false ) );
		$discount->is_single_use = false;

		Helpers\EDD_Helper_Payment::delete_payment( $payment_id );
	}

	/**
	 * @covers \edd_is_discount_valid()
	 * @covers ::is_valid()
	 */
	public function test_discount_is_valid_when_purchasing() {
		$this->assertFalse( edd_is_discount_valid( '20OFF' ) );
	}

	/**
	 * @covers \edd_get_discount_id_by_code()
	 *@covers \edd_get_discount_id_by()
	 */
	public function test_discount_id_by_code() {
		$id       = edd_get_discount_id_by_code( '20OFF' );
		$discount = edd_get_discount_by( 'code', '20OFF' );

		$this->assertSame( $discount->id, $id );
	}


	/**
	 * @covers \edd_get_discounted_amount()
	 * @covers ::get_discounted_amount()
	 */
	public function test_get_discounted_amount() {
		$this->assertEquals( '432', edd_get_discounted_amount( '20OFF',  '540' ) );
		$this->assertEquals( '150', edd_get_discounted_amount( 'DOUBLE', '75'  ) );
		$this->assertEquals( '10',  edd_get_discounted_amount( '10FLAT', '20'  ) );

		// Test that an invalid Code returns the base price
		$this->assertEquals( '10', edd_get_discounted_amount( 'FAKEDISCOUNT', '10' ) );
	}

	/**
	 * @covers \edd_get_discount_id_by_code()
	 * @covers \edd_get_discount_uses()
	 * @covers \edd_increase_discount_usage()
	 * @covers ::increase_usage()
	 */
	public function test_increase_discount_usage() {
		$id   = edd_get_discount_id_by_code( '20OFF' );
		$uses = edd_get_discount_uses( $id );

		$increased = edd_increase_discount_usage( '20OFF' );
		$this->assertequals( $increased, (int) $uses + 1 );

		// Test missing codes
		$this->assertFalse( edd_increase_discount_usage( 'INVALIDDISCOUNTCODE' ) );
	}

	/**
	 * @covers _edd_discount_update_meta_backcompat()
	 * @covers \edd_get_discount_code()
	 * @covers \edd_increase_discount_usage()
	 */
	public function test_discount_inactive_at_max() {
		$this->setExpectedIncorrectUsage( 'get_post_meta()' );
		$this->setExpectedIncorrectUsage( 'add_post_meta()/update_post_meta()' );

		update_post_meta( self::$discount_id, '_edd_discount_status', 'active' );

		$code = edd_get_discount_code( self::$discount_id );

		update_post_meta( self::$discount_id, '_edd_discount_max', 10 );
		update_post_meta( self::$discount_id, '_edd_discount_uses', 9 );

		edd_increase_discount_usage( $code );

		$this->assertEquals( 'inactive', get_post_meta( self::$discount_id, '_edd_discount_status', true ) );
	}

	/**
	 * @covers _edd_discount_update_meta_backcompat()
	 * @covers \edd_get_discount_code()
	 * @covers \edd_increase_discount_usage()
	 * @covers ::decrease_usage()
	 */
	public function test_discount_active_after_decreasing_at_max() {
		$this->setExpectedIncorrectUsage( 'get_post_meta()' );
		$this->setExpectedIncorrectUsage( 'add_post_meta()/update_post_meta()' );

		update_post_meta( self::$discount_id, '_edd_discount_max', 10 );
		update_post_meta( self::$discount_id, '_edd_discount_uses', 10 );
		update_post_meta( self::$discount_id, '_edd_discount_status', 'inactive' );

		$code = edd_get_discount_code( self::$discount_id );

		edd_decrease_discount_usage( $code );

		$this->assertEquals( 'active', get_post_meta( self::$discount_id, '_edd_discount_status', true ) );
	}

	/**
	 * @covers _edd_discount_post_meta_bc_filter()
	 * @covers \edd_format_discount_rate()
	 */
	public function test_formatted_discount_amount() {
		$this->setExpectedIncorrectUsage( 'get_post_meta()' );

		$rate = get_post_meta( self::$discount_id, '_edd_discount_amount', true );
		$this->assertSame( '20.00%', edd_format_discount_rate( 'percent', $rate ) );
	}

	/**
	 * @covers \edd_get_discount_by()
	 */
	public function test_edd_get_discount_by() {
		$discount = edd_get_discount_by( 'id', self::$discount_id );

		$this->assertEquals( $discount->id,    self::$discount_id );
		$this->assertEquals( '20 Percent Off', edd_get_discount_by( 'code', '20OFF'          )->post_title );
		$this->assertEquals( $discount->id,    edd_get_discount_by( 'code', '20OFF'          )->id         );
		$this->assertEquals( $discount->id,    edd_get_discount_by( 'name', '20 Percent Off' )->id         );
	}

	/**
	 * @covers \edd_get_discount_amount()
	 * @covers \edd_format_discount_rate()
	 */
	public function test_formatted_discount_amount_negative() {
		$amount = edd_get_discount_amount( self::$negativediscount_id );
		$this->assertSame( '-100.00%', edd_format_discount_rate( 'percent', $amount ) );
	}

	/**
	 * @covers \edd_get_discount_amount()
	 * @covers \edd_format_discount_rate()
	 */
	public function test_formatted_discount_amount_flat() {
		$amount = edd_get_discount_amount( self::$flatdiscount_id );

		$this->assertSame( '&#36;10.00', edd_format_discount_rate( 'flat', $amount ) );
	}

	/**
	 * @covers \edd_get_discount_excluded_products()
	 * @covers ::get_excluded_products()
	 */
	public function test_discount_excluded_products() {
		$this->assertIsArray( edd_get_discount_excluded_products( self::$discount_id ) );
	}

	/**
	 * @covers \edd_get_discount_product_reqs()
	 * @covers ::get_product_reqs()
	 */
	public function test_discount_product_reqs() {
		$this->assertIsArray( edd_get_discount_product_reqs( self::$discount_id ) );
	}

	/**
	 * @covers \edd_set_cart_discount()
	 * @covers \edd_get_discount_code()
	 */
	public function test_set_discount() {
		EDD()->session->set( 'cart_discounts', null );

		edd_add_to_cart( self::$download->ID );

		$this->assertEquals( '20.00', edd_get_cart_total() );

		edd_set_cart_discount( edd_get_discount_code( self::$discount_id ) );
		$this->assertEquals( '16.00', edd_get_cart_total() );
	}

	/**
	 * @covers \edd_set_cart_discount()
	 */
	public function test_set_multiple_discounts() {
		$this->setExpectedIncorrectUsage( 'add_post_meta()/update_post_meta()' );

		EDD()->session->set( 'cart_discounts', null );

		edd_update_option( 'allow_multiple_discounts', true );

		edd_add_to_cart( self::$download->ID );

		$this->assertEquals( '20.00', edd_get_cart_total() );

		// Test a single discount code
		$discounts = edd_set_cart_discount( self::$discount->code );

		$this->assertIsArray( $discounts );
		$this->assertTrue( 1 === count( $discounts ) );
		$this->assertEquals( '16.00', edd_get_cart_total() );

		// Test a single discount code again but with lower case
		$discounts = edd_set_cart_discount( strtolower( self::$discount->code ) );

		$this->assertIsArray( $discounts );
		$this->assertTrue( 1 === count( $discounts ) );
		$this->assertEquals( '16.00', edd_get_cart_total() );

		// Test a new code
		$code_id =  Helpers\EDD_Helper_Discount::create_simple_percent_discount();
		update_post_meta( $code_id, '_edd_discount_code', 'SECONDcode' );

		$discounts = edd_set_cart_discount( 'SECONDCODE' );

		$this->assertIsArray( $discounts );
		$this->assertTrue( 2 === count( $discounts ) );
		$this->assertEquals( '12.00', edd_get_cart_total() );
	}

	/**
	 * @covers \edd_store_discount()
	 * @covers \edd_get_cart_discountable_subtotal()
	 */
	public function test_discountable_subtotal() {
		$download_1 = Helpers\EDD_Helper_Download::create_simple_download();
		$download_2 = Helpers\EDD_Helper_Download::create_simple_download();
		edd_add_to_cart( $download_1->ID );
		edd_add_to_cart( $download_2->ID );

		$discount = Helpers\EDD_Helper_Discount::create_simple_flat_discount();
		$post = array(
			'name'              => 'Excludes',
			'amount'            => '1',
			'code'              => 'EXCLUDES',
			'product_condition' => 'all',
			'start'             => date( 'm/d/Y H:i:s', time() ),
			'expiration'        => date( 'm/d/Y H:i:s', time() + HOUR_IN_SECONDS ),
			'min_price'         => 23,
			'status'            => 'active',
			'excluded-products' => array( $download_2->ID ),
		);
		edd_store_discount( $post, $discount );

		$this->assertEquals( '20.00', edd_get_cart_discountable_subtotal( $discount ) );

		$download_3 = Helpers\EDD_Helper_Download::create_simple_download();
		edd_add_to_cart( $download_3->ID );

		$this->assertEquals( '40.00', edd_get_cart_discountable_subtotal( $discount ) );

		Helpers\EDD_Helper_Download::delete_download( $download_1->ID );
		Helpers\EDD_Helper_Download::delete_download( $download_2->ID );
		Helpers\EDD_Helper_Download::delete_download( $download_3->ID );
		Helpers\EDD_Helper_Discount::delete_discount( $discount );
	}

	/**
	 * @covers \edd_discount_is_min_met()
	 * @covers \edd_is_discount_valid()
	 */
	public function test_discount_min_excluded_products() {
		edd_empty_cart();
		$download_1 = Helpers\EDD_Helper_Download::create_simple_download();
		$download_2 = Helpers\EDD_Helper_Download::create_simple_download();
		$discount   = Helpers\EDD_Helper_Discount::create_simple_flat_discount();

		$post = array(
			'name'              => 'Excludes',
			'amount'            => '1',
			'code'              => 'EXCLUDES',
			'product_condition' => 'all',
			'start'             => date( 'm/d/Y H:i:s', time() ),
			'expiration'        => date( 'm/d/Y H:i:s', time() + HOUR_IN_SECONDS ),
			'min_price'         => 23,
			'status'            => 'active',
			'excluded-products' => array( $download_2->ID ),
		);

		edd_store_discount( $post, $discount );

		edd_add_to_cart( $download_1->ID );
		edd_add_to_cart( $download_2->ID );
		$this->assertFalse( edd_discount_is_min_met( $discount ) );

		$download_3 = Helpers\EDD_Helper_Download::create_simple_download();
		edd_add_to_cart( $download_3->ID );
		$this->assertTrue( edd_discount_is_min_met( $discount ) );

		edd_empty_cart();
		edd_add_to_cart( $download_2->ID );
		$discount_obj = edd_get_discount( $discount );
		$this->assertFalse( edd_is_discount_valid( $discount_obj->code ) );

		Helpers\EDD_Helper_Download::delete_download( $download_1->ID );
		Helpers\EDD_Helper_Download::delete_download( $download_2->ID );
		Helpers\EDD_Helper_Download::delete_download( $download_3->ID );
	}

	/**
	 * @covers \edd_get_discounts()
	 */
	public function test_edd_get_discounts() {
		$found_discounts = edd_get_discounts( array(
			'posts_per_page' => 3,
		) );

		$this->assertTrue( 3 === count( $found_discounts ) );
	}

	public function test_edd_validate_discount_product_requirements_all_all_variations_in_array() {
		$variable_download_id = Helpers\EDD_Helper_Download::create_variable_download();
		$variable_download    = edd_get_download( $variable_download_id->ID );
		$products = array( self::$download->ID, $variable_download->ID );
		$args     = array(
			'product_reqs'      => array( self::$download->ID ),
			'product_condition' => 'all',
			'max_uses'          => 10000,
		);
		edd_update_discount( self::$discount_id, $args );
		edd_add_to_cart( $variable_download->ID, array( 'price_id' => array( 0, 1 ) ) );

		$discount = edd_get_discount( self::$discount_id );
		$this->assertFalse( $discount->is_product_requirements_met( false ) );
	}

	/**
	 * Tests a discount for a single price product when a false price ID is added to the cart.
	 */
	public function test_discount_product_requirements_false_price_id() {
		$args     = array(
			'product_reqs'      => array( self::$download->ID ),
			'product_condition' => 'all',
			'max_uses'          => 10000,
			'scope'             => 'not_global',
		);
		edd_update_discount( self::$discount_id, $args );
		edd_add_to_cart( self::$download->ID, array( 'price_id' => false ) );

		$cart_contents = edd_get_cart_contents();
		$first_item    = reset( $cart_contents );
		$discount      = edd_get_discount( self::$discount_id );
		$this->assertTrue( $discount->is_product_requirements_met( false ) );
		$this->assertEquals( 4.0, edd_get_item_discount_amount( $first_item, $cart_contents, array( $discount ) ) );
	}

	/**
	 * Tests a discount for a single price product when a false price ID is added to the cart.
	 *
	 * Returns details.
	 */
	public function test_discount_product_requirements_false_price_id_returns_details() {
		$args     = array(
			'product_reqs'      => array( self::$download->ID ),
			'product_condition' => 'all',
			'max_uses'          => 10000,
			'scope'             => 'not_global',
		);
		edd_update_discount( self::$discount_id, $args );
		edd_add_to_cart( self::$download->ID, array( 'price_id' => false ) );

		$cart_contents = edd_get_cart_contents();
		$first_item    = reset( $cart_contents );
		$discount      = edd_get_discount( self::$discount_id );
		$this->assertTrue( $discount->is_product_requirements_met( false ) );

		$expected = array(
			'amount'    => 4.0,
			'discounts' => array(
				'20OFF' => 4.0,
			),
		);
		$this->assertEquals( $expected, edd_get_item_discount_breakdown( $first_item, $cart_contents, array( $discount ) ) );
	}

	/**
	 * Tests a discount for a single price product when an empty string price ID is added to the cart.
	 */
	public function test_discount_product_requirements_empty_string_price_id() {
		$args     = array(
			'product_reqs'      => array( self::$download->ID ),
			'product_condition' => 'all',
			'max_uses'          => 10000,
		);
		edd_update_discount( self::$discount_id, $args );
		edd_add_to_cart( self::$download->ID, array( 'price_id' => '' ) );

		$discount = edd_get_discount( self::$discount_id );
		$this->assertTrue( $discount->is_product_requirements_met( false ) );
	}

	public function test_store_discount_empty_start_end_is_empty() {
		$time = time();
		$data = array(
			'code'       => 'EXP' . (string) $time,
			'uses'       => 703,
			'max_uses'   => '',
			'amount'     => 15,
			'start'      => '',
			'expiration' => '',
			'type'       => 'percent',
			'min_price'  => '',
			'name'       => 'Expiration Testing',
		);

		$discount_id = edd_store_discount( $data );
		$discount    = edd_get_adjustment( $discount_id );

		$this->assertEmpty( $discount->start_date );
		$this->assertEmpty( $discount->end_date );
	}

	public function test_discount_with_expiration_keeps_expiration_after_update() {
		// Create a discount with an expiration date.
		$discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount();
		edd_update_adjustment(
			$discount_id,
			array(
				'status' => 'inactive',
			)
		);
		$discount = edd_get_discount( $discount_id );

		$this->assertNotEmpty( $discount->end_date );
		$this->assertEquals( 'inactive', $discount->status );
		$this->assertNotEmpty( $discount->start_date );
	}

	/**
	 * @covers EDD_Discount::is_valid() with archived status
	 */
	public function test_discount_is_valid_with_archived_status_returns_false() {
		$discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		edd_update_adjustment(
			$discount_id,
			array(
				'status' => 'archived',
			)
		);
		edd_add_to_cart( self::$download->ID );

		$discount = edd_get_discount( $discount_id );

		$this->assertFalse( $discount->is_valid() );
	}

	/**
	 * @covers EDD_Discount::is_valid() with not started date
	 */
	public function test_discount_is_valid_with_non_started_status_returns_false() {
		$discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		$discount    = edd_get_discount( $discount_id );

		$discount->__set( 'start_date', date( 'Y-m-d', time() + DAY_IN_SECONDS ) );

		edd_add_to_cart( self::$download->ID );

		$this->assertFalse( $discount->is_valid() );
	}

	/**
	 * @covers EDD_Discount::is_valid() with non-active status
	 */
	public function test_discount_is_valid_with_non_active_status_returns_false() {
		$discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		$discount    = edd_get_discount( $discount_id );

		$discount->__set( 'end_date', date( 'Y-m-d', time() - DAY_IN_SECONDS ) );

		edd_add_to_cart( self::$download->ID );

		$this->assertFalse( $discount->is_valid() );
	}

	/**
	 * @covers EDD_Discount::is_valid() with maxed out uses
	 */
	public function test_discount_is_valid_with_maxed_out_uses_returns_false() {
		$discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		edd_update_adjustment(
			$discount_id,
			array(
				'use_count' => 10,
				'max_uses'  => 10,
			)
		);
		edd_add_to_cart( self::$download->ID );

		$discount = edd_get_discount( $discount_id );

		$this->assertFalse( $discount->is_valid() );
	}

	/**
	 * @covers EDD_Discount::is_valid() with once per customer
	 */
	public function test_discount_is_valid_with_used_returns_false() {
		$discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		edd_update_adjustment(
			$discount_id,
			array(
				'once_per_customer' => true,
			)
		);
		$discount = edd_get_discount( $discount_id );

		$payment_id         = Helpers\EDD_Helper_Payment::create_simple_payment();
		$payment            = edd_get_payment( $payment_id );
		$payment->discounts = $discount->get_code();
		$payment->status    = 'publish';
		$payment->save();

		edd_add_to_cart( self::$download->ID );

		$this->assertFalse( $discount->is_valid( 'admin@example.org' ) );

		Helpers\EDD_Helper_Payment::delete_payment( $payment_id );
	}

	/**
	 * @covers EDD_Discount::is_valid() with invalid product requirements
	 */
	public function test_discount_is_valid_with_product_requirements_returns_false() {
		$download_1  = Helpers\EDD_Helper_Download::create_simple_download();
		$discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();

		edd_update_discount(
			$discount_id,
			array(
				'product_reqs'      => array( $download_1->ID ),
				'product_condition' => 'all',
			)
		);
		edd_add_to_cart( self::$download->ID );

		$discount = edd_get_discount( $discount_id );

		$this->assertFalse( $discount->is_valid() );
	}

	/**
	 * @covers EDD_Discount::is_valid() with excluded category
	 */
	public function test_discount_is_valid_with_excluded_category_returns_false() {
		$category_1  = wp_insert_term( 'Test Category', 'download_category' );
		$discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();

		edd_update_discount(
			$discount_id,
			array(
				'categories'     => array( $category_1['term_id'] ),
				'term_condition' => 'exclude',
			)
		);
		wp_set_object_terms( self::$download->ID, $category_1['term_id'], 'download_category' );
		edd_add_to_cart( self::$download->ID );

		$discount = edd_get_discount( $discount_id );

		$this->assertFalse( $discount->is_valid() );
	}

	/**
	 * @covers EDD_Discount::is_valid()
	 */
	public function test_discount_is_valid_simple_returns_true() {
		$discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		edd_add_to_cart( self::$download->ID );

		$discount = edd_get_discount( $discount_id );

		$this->assertTrue( $discount->is_valid() );
	}
}
