<?php

/**
 * Tests for Discounts API.
 *
 * @covers EDD_Discount
 * @group edd_discounts
 */
class Tests_Discounts extends EDD_UnitTestCase {

	/**
	 * Download test fixture.
	 *
	 * @access protected
	 * @var    EDD_Download
	 */
	protected $_download;

	/**
	 * Discount ID.
	 *
	 * @access protected
	 * @var    int
	 */
	protected $_discount_id;

	/**
	 * Discount object test fixture.
	 *
	 * @access protected
	 * @var    EDD_Discount
	 */
	protected $_discount;

	/**
	 * Flat discount test fixture.
	 *
	 * @access protected
	 * @var    int
	 */
	protected $_flatdiscount_id;

	/**
	 * Negative discount test fixture.
	 *
	 * @access protected
	 * @var    int
	 */
	protected $_negativediscount_id;

	/**
	 * Set up fixtures.
	 *
	 * @access public
	 */
	public function setUp() {
		parent::setUp();

		$this->_download = EDD_Helper_Download::create_simple_download();

		$this->_discount_id         = EDD_Helper_Discount::create_simple_percent_discount();
		$this->_negativediscount_id = EDD_Helper_Discount::create_simple_percent_discount();
		$this->_flatdiscount_id     = EDD_Helper_Discount::create_simple_flat_discount();

		$this->_discount = new EDD_Discount( $this->_discount_id );

		// Create legacy data records for backwards compatibility
		EDD()->discount_meta->add_meta( $this->_discount_id, 'legacy_id', $this->_discount_id );
		EDD()->discount_meta->add_meta( $this->_negativediscount_id, 'legacy_id', $this->_negativediscount_id );

		// Update meta via old method. If these work properly, it helps show backwards compatibility is working
		update_post_meta( $this->_negativediscount_id, '_edd_discount_name', 'Double Double' );
		update_post_meta( $this->_negativediscount_id, '_edd_discount_amount', '-100' );
		update_post_meta( $this->_negativediscount_id, '_edd_discount_code', 'DOUBLE' );
	}

	/**
	 * Run after each test to empty the cart and reset the test store.
	 *
	 * @access public
	 */
	public function tearDown() {
		self::_delete_all_data();
		edd_empty_cart();

		parent::tearDown();
	}

	/**
	 * @covers EDD_Discount::setup_discount()
	 */
	public function test_discount_instantiated() {
		$this->assertGreaterThan( 0, $this->_discount->id );
		$this->assertInstanceOf( EDD_Discount::class, $this->_discount );
	}

	/**
	 * @covers EDD_Discount::setup_discount()
	 */
	public function test_id_is_0_when_no_id_is_passed() {
		$d = new EDD_Discount();

		$this->assertTrue( 0 === $d->id );
	}

	/**
	 * @covers EDD_Discount::setup_discount()
	 */
	public function test_discount_id_matches_id() {
		$this->assertSame( $this->_discount->id, $this->_discount_id );
	}

	/**
	 * @covers EDD_Discount::setup_discount()
	 */
	public function test_discount_id_matches_capital_ID() {
		$this->assertSame( $this->_discount->ID, $this->_discount_id );
	}

	/**
	 * @covers EDD_Discount::setup_discount()
	 */
	public function test_get_discount_name() {
		$this->assertEquals( '20 Percent Off', $this->_discount->name );
	}

	/**
	 * @covers EDD_Discount::setup_discount()
	 */
	public function test_get_discount_name_by_property() {
		$this->assertEquals( '20OFF', $this->_discount->code );
	}

	/**
	 * @covers EDD_Discount::get_code()
	 */
	public function test_get_discount_name_by_method() {
		$this->assertEquals( '20OFF', $this->_discount->get_code() );
	}

	/**
	 * @covers EDD_Discount::get_status()
	 */
	public function test_get_discount_status_by_property() {
		$this->assertEquals( 'active', $this->_discount->status );
	}

	/**
	 * @covers EDD_Discount::get_status()
	 */
	public function test_get_discount_status_by_method() {
		$this->assertEquals( 'active', $this->_discount->get_status() );
	}

	/**
	 * @covers EDD_Discount::get_expiration()
	 */
	public function test_get_discount_expiration_by_property() {
		$this->assertEquals( '2050-12-31 23:59:59', $this->_discount->expiration );
	}

	/**
	 * @covers EDD_Discount::get_expiration()
	 */
	public function test_get_discount_expiration_by_method() {
		$d = new EDD_Discount( $this->_discount_id );
		$this->assertEquals( '2050-12-31 23:59:59', $this->_discount->get_expiration() );
	}

	/**
	 * @covers EDD_Discount::get_type()
	 */
	public function test_get_discount_type_by_property() {
		$this->assertEquals( 'percent', $this->_discount->type );
	}

	/**
	 * @covers EDD_Discount::get_type()
	 */
	public function test_get_discount_type_by_method() {
		$this->assertEquals( 'percent', $this->_discount->get_type() );
	}

	/**
	 * @covers EDD_Discount::get_type()
	 */
	public function test_get_discount_type_of_flat_discount() {
		$d = new EDD_Discount( $this->_flatdiscount_id );
		$this->assertEquals( 'flat', $d->get_type() );
	}

	/**
	 * @covers EDD_Discount::get_amount()
	 */
	public function test_get_discount_amount_by_property() {
		$this->assertEquals( '20', $this->_discount->amount );
	}

	/**
	 * @covers EDD_Discount::get_amount()
	 */
	public function test_get_discount_amount_by_method() {
		$this->assertEquals( '20', $this->_discount->get_amount() );
	}

	/**
	 * @covers EDD_Discount::get_product_reqs()
	 */
	public function test_get_discount_product_requirements_by_method() {
		$this->assertSame( array(), $this->_discount->product_reqs );
	}

	/**
	 * @covers EDD_Discount::get_product_reqs()
	 */
	public function test_get_discount_product_requirements_by_property() {
		$this->assertSame( array(), $this->_discount->get_product_reqs() );
	}

	/**
	 * @covers EDD_Discount::get_excluded_products()
	 */
	public function test_get_discount_excluded_products_by_method() {
		$this->assertSame( array(), $this->_discount->excluded_products );
	}

	/**
	 * @covers EDD_Discount::get_excluded_products()
	 */
	public function test_get_discount_excluded_products_by_property() {
		$this->assertSame( array(), $this->_discount->get_excluded_products() );
	}


	/*
	 * Migration tests
	 *
	 * These tests help ensure that the migration() method of EDD_Discount works properly.
	 */
	public function test_creating_legacy_discount() {
		$old_id = EDD_Helper_Discount::create_legacy_discount();
		$this->assertInternalType( 'int', $old_id );

		$this->assertTrue( $old_id > 0 );
	}

	/**
	 * @covers EDD_Discount::setup_discount()
	 */
	public function test_instantiating_non_migrated_legacy_discount() {
		$old_id = EDD_Helper_Discount::create_legacy_discount();
		$d      = new EDD_Discount( $old_id );

		$this->assertTrue( empty( $d->id ) );
	}

	/**
	 * @covers EDD_Discount::migrate()
	 */
	public function test_migrating_legacy_discount() {
		$old_id   = EDD_Helper_Discount::create_legacy_discount();
		$d        = new EDD_Discount();

		$migrated = $d->migrate( $old_id );

		$this->assertInternalType( 'int', $migrated );
	}

	/**
	 * @covers EDD_Discount::migrate()
	 */
	public function test_code_of_migrated_discount() {
		$old_id = EDD_Helper_Discount::create_legacy_discount();
		$d      = new EDD_Discount();

		$migrated_id = $d->migrate( $old_id );

		$d2          = new EDD_Discount( $migrated_id );
		$this->assertSame( 'OLD', $d2->code );
	}

	/**
	 * @covers EDD_Discount::migrate()
	 */
	public function test_status_of_migrated_discount() {
		$old_id      = EDD_Helper_Discount::create_legacy_discount();
		$d           = new EDD_Discount();
		$migrated_id = $d->migrate( $old_id );
		$d2          = new EDD_Discount( $migrated_id );
		$this->assertEquals( 'active', $d2->status );
	}

	/**
	 * @covers EDD_Discount::migrate()
	 */
	public function test_uses_of_migrated_discount() {
		$old_id      = EDD_Helper_Discount::create_legacy_discount();
		$d           = new EDD_Discount();
		$migrated_id = $d->migrate( $old_id );
		$d2          = new EDD_Discount( $migrated_id );
		$this->assertSame( 10, $d2->uses );
	}

	/**
	 * @covers EDD_Discount::migrate()
	 */
	public function test_max_uses_of_migrated_discount() {
		$old_id      = EDD_Helper_Discount::create_legacy_discount();
		$d           = new EDD_Discount();
		$migrated_id = $d->migrate( $old_id );
		$d2          = new EDD_Discount( $migrated_id );
		$this->assertSame( 20, $d2->max_uses );
	}

	/**
	 * @covers EDD_Discount::get_amount()
	 */
	public function test_amount_of_migrated_discount() {
		$old_id      = EDD_Helper_Discount::create_legacy_discount();
		$d           = new EDD_Discount();
		$migrated_id = $d->migrate( $old_id );
		$d2          = new EDD_Discount( $migrated_id );
		$this->assertEquals( '20', $d2->amount );
	}

	/**
	 * @covers EDD_Discount::get_start_date()
	 */
	public function test_start_date_of_migrated_discount() {
		$old_id      = EDD_Helper_Discount::create_legacy_discount();
		$d           = new EDD_Discount();
		$migrated_id = $d->migrate( $old_id );
		$d2          = new EDD_Discount( $migrated_id );
		$this->assertEquals( '2000-01-01 00:00:00', $d2->start_date );
	}

	/**
	 * @covers EDD_Discount::get_expiration()
	 */
	public function test_end_date_of_migrated_discount() {
		$old_id      = EDD_Helper_Discount::create_legacy_discount();
		$d           = new EDD_Discount();
		$migrated_id = $d->migrate( $old_id );
		$d2          = new EDD_Discount( $migrated_id );
		$this->assertEquals( '2050-12-31 23:59:59', $d2->end_date );
	}

	/**
	 * @covers EDD_Discount::get_type()
	 */
	public function test_type_of_migrated_discount() {
		$old_id      = EDD_Helper_Discount::create_legacy_discount();
		$d           = new EDD_Discount();
		$migrated_id = $d->migrate( $old_id );
		$d2          = new EDD_Discount( $migrated_id );
		$this->assertEquals( 'percent', $d2->type );
	}

	/**
	 * @covers EDD_Discount::get_min_price()
	 */
	public function test_min_cart_price_of_migrated_discount() {
		$old_id      = EDD_Helper_Discount::create_legacy_discount();
		$d           = new EDD_Discount();
		$migrated_id = $d->migrate( $old_id );
		$d2          = new EDD_Discount( $migrated_id );
		$this->assertEquals( '10.50', $d2->min_cart_price );
	}

	/**
	 * @covers EDD_Discount::get_product_reqs()
	 */
	public function test_product_reqs_of_migrated_discount() {
		$old_id      = EDD_Helper_Discount::create_legacy_discount();
		$d           = new EDD_Discount();
		$migrated_id = $d->migrate( $old_id );
		$d2          = new EDD_Discount( $migrated_id );
		$this->assertTrue( in_array( 57, $d2->product_reqs ) );
	}

	/**
	 * @covers EDD_Discount::get_product_condition()
	 */
	public function test_product_condition_of_migrated_discount() {
		$old_id      = EDD_Helper_Discount::create_legacy_discount();
		$d           = new EDD_Discount();
		$migrated_id = $d->migrate( $old_id );
		$d2          = new EDD_Discount( $migrated_id );
		$this->assertEquals( 'all', $d2->product_condition );
	}

	/**
	 * @covers EDD_Discount::get_excluded_products()
	 */
	public function test_excluded_products_of_migrated_discount() {
		$old_id      = EDD_Helper_Discount::create_legacy_discount();
		$d           = new EDD_Discount();
		$migrated_id = $d->migrate( $old_id );
		$d2          = new EDD_Discount( $migrated_id );
		$this->assertTrue( in_array( 75, $d2->excluded_products ) );
	}

	/**
	 * @covers EDD_Discount::get_scope()
	 */
	public function test_is_not_global_of_migrated_discount() {
		$old_id      = EDD_Helper_Discount::create_legacy_discount();
		$d           = new EDD_Discount();
		$migrated_id = $d->migrate( $old_id );
		$d2          = new EDD_Discount( $migrated_id );
		$this->assertTrue( $d2->is_not_global );
	}

	/**
	 * @covers EDD_Discount::setup_discount()
	 */
	public function test_applies_globally_of_migrated_discount() {
		$old_id      = EDD_Helper_Discount::create_legacy_discount();
		$d           = new EDD_Discount();
		$migrated_id = $d->migrate( $old_id );
		$d2          = new EDD_Discount( $migrated_id );
		$this->assertTrue( empty( $d2->applies_globally ) );
	}

	/**
	 * @covers EDD_Discount::is_single_use()
	 */
	public function test_is_single_use_of_migrated_discount() {
		$old_id        = EDD_Helper_Discount::create_legacy_discount();
		$d             = new EDD_Discount();
		$migrated_id   = $d->migrate( $old_id );
		$d2            = new EDD_Discount( $migrated_id );
		$is_single_use = $d2->is_single_use();
		$this->assertTrue( ! empty( $is_single_use ) );
	}

	/**
	 * @covers EDD_Discount::get_once_per_customer()
	 */
	public function test_once_per_customer_of_migrated_discount() {
		$old_id      = EDD_Helper_Discount::create_legacy_discount();
		$d           = new EDD_Discount();
		$migrated_id = $d->migrate( $old_id );
		$d2          = new EDD_Discount( $migrated_id );
		$this->assertTrue( ! empty( $d2->once_per_customer ) );
	}

	/**
	 * @covers EDD_Discount::is_started()
	 */
	public function test_is_started_of_migrated_discount() {
		$old_id      = EDD_Helper_Discount::create_legacy_discount();
		$d           = new EDD_Discount();
		$migrated_id = $d->migrate( $old_id );
		$d2          = new EDD_Discount( $migrated_id );
		$this->assertTrue( $d2->is_started() );
	}

	/**
	 * @covers EDD_Discount::is_expired()
	 */
	public function test_is_expired_of_migrated_discount() {
		$old_id      = EDD_Helper_Discount::create_legacy_discount();
		$d           = new EDD_Discount();
		$migrated_id = $d->migrate( $old_id );
		$d2          = new EDD_Discount( $migrated_id );
		$this->assertFalse( $d2->is_expired() );
	}

	/**
	 * @covers EDD_Discount::is_maxed_out()
	 */
	public function test_is_maxed_out_of_migrated_discount() {
		$old_id      = EDD_Helper_Discount::create_legacy_discount();
		$d           = new EDD_Discount();
		$migrated_id = $d->migrate( $old_id );
		$d2          = new EDD_Discount( $migrated_id );
		$this->assertFalse( $d2->is_maxed_out( false ) );
	}

	/**
	 * @covers EDD_Discount::is_active()
	 */
	public function test_is_active_of_migrated_discount() {
		$old_id      = EDD_Helper_Discount::create_legacy_discount();
		$d           = new EDD_Discount();
		$migrated_id = $d->migrate( $old_id );
		$d2          = new EDD_Discount( $migrated_id );
		$this->assertTrue( $d2->is_active( false, false ) );
	}


	/*
	 * Legacy tests
	 *
	 * All tests below are from before EDD 3.0 when discounts were stored as wp_posts.
	 * EDD 3.0 stores them in a custom table.
	 * The below tests are left here to help ensure the backwards compatibility layers work properly
	 */
	public function test_discount_created() {
		$this->assertInternalType( 'int', $this->_discount_id );
	}

	public function test_addition_of_negative_discount() {
		$this->assertInternalType( 'int', $this->_negativediscount_id );
	}

	public function test_addition_of_flat_discount() {
		$this->assertInternalType( 'int', $this->_flatdiscount_id );
	}

	/**
	 * @covers edd_store_discount()
	 */
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

		$updated_id = edd_store_discount( $post, $this->_discount_id );
		$this->assertInternalType( 'int', $updated_id );
	}

	/**
	 * @covers edd_update_discount_status()
	 */
	public function test_discount_status_update() {
		$this->assertTrue( edd_update_discount_status( $this->_discount_id, 'active' ) );
	}

	/**
	 * @covers edd_update_discount_status()
	 */
	public function test_discount_status_update_fail() {
		$this->assertFalse( edd_update_discount_status( - 1 ) );
	}

	/**
	 * @covers edd_has_active_discounts()
	 */
	public function test_discounts_exists() {
		edd_update_discount_status( $this->_discount_id, 'active' );
		$this->assertTrue( edd_has_active_discounts() );
	}

	/**
	 * @covers edd_update_discount_status()
	 * @covers edd_is_discount_active()
	 * @covers edd_store_discount()
	 */
	public function test_is_discount_active() {
		edd_update_discount_status( $this->_discount_id, 'active' );

		$this->assertTrue( edd_is_discount_active( $this->_discount_id, true ) );
		$this->assertTrue( edd_is_discount_active( $this->_discount_id, false ) );

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

		$expired_discount_id = edd_store_discount( $post );

		$this->assertFalse( edd_is_discount_active( $expired_discount_id, false ) );

		$this->assertEquals( 'expired', get_post_meta( $expired_discount_id, '_edd_discount_status', true ) );
	}

	/**
	 * @covers edd_discount_exists()
	 */
	public function test_discount_exists() {
		$this->assertTrue( edd_discount_exists( $this->_discount_id ) );
	}

	/**
	 * @covers edd_update_discount_status()
	 * @covers edd_get_discount()
	 */
	public function test_get_discount() {
		edd_update_discount_status( $this->_discount_id, 'active' );

		$discount = edd_get_discount( $this->_discount_id );

		$this->assertEquals( $this->_discount_id, $discount->ID );
		$this->assertEquals( '20 Percent Off', $discount->post_title );
		$this->assertEquals( 'active', $discount->post_status );
	}

	/**
	 * @covers edd_get_discount_code()
	 */
	public function test_get_discount_code() {
		$this->assertSame( '20OFF', edd_get_discount_code( $this->_discount_id ) );
	}

	/**
	 * @covers edd_get_discount_start_date()
	 */
	public function test_discount_start_date() {
		$this->assertSame( '2010-12-12 00:00:00', edd_get_discount_start_date( $this->_discount_id ) );
	}

	/**
	 * @covers edd_get_discount_expiration()
	 */
	public function test_discount_expiration_date() {
		$this->assertSame( '2050-12-31 23:59:59', edd_get_discount_expiration( $this->_discount_id ) );
	}

	/**
	 * @covers edd_get_discount_max_uses()
	 */
	public function test_discount_max_uses() {
		$this->assertSame( 10, edd_get_discount_max_uses( $this->_discount_id ) );
	}

	/**
	 * @covers edd_get_discount_uses()
	 */
	public function test_discount_uses() {
		$this->assertSame( 54, edd_get_discount_uses( $this->_discount_id ) );
	}

	/**
	 * @covers edd_get_discount_min_price()
	 */
	public function testDiscountMinPrice() {
		$this->assertSame( 128.0, edd_get_discount_min_price( $this->_discount_id ) );
	}

	/**
	 * @covers edd_get_discount_amount()
	 */
	public function test_discount_amount() {
		$this->assertSame( 20.0, edd_get_discount_amount( $this->_discount_id ) );
	}

	/**
	 * @covers edd_get_discount_amount()
	 */
	public function test_discount_amount_negative() {
		$this->assertSame( - 100.0, edd_get_discount_amount( $this->_negativediscount_id ) );
	}

	/**
	 * @covers edd_get_discount_type()
	 */
	public function test_discount_type() {
		$this->assertSame( 'percent', edd_get_discount_type( $this->_discount_id ) );
	}

	/**
	 * @covers edd_get_discount_product_condition()
	 */
	public function test_discount_product_condition() {
		$this->assertSame( 'all', edd_get_discount_product_condition( $this->_discount_id ) );
	}

	/**
	 * @covers edd_is_discount_not_global()
	 */
	public function test_discount_is_not_global() {
		$this->assertFalse( edd_is_discount_not_global( $this->_discount_id ) );
	}

	/**
	 * @covers edd_discount_is_single_use()
	 */
	public function test_discount_is_single_use() {
		$this->assertFalse( edd_discount_is_single_use( $this->_discount_id ) );
	}

	/**
	 * @covers edd_is_discount_started()
	 */
	public function test_discount_is_started() {
		$this->assertTrue( edd_is_discount_started( $this->_discount_id ) );
	}

	/**
	 * @covers edd_is_discount_expired()
	 */
	public function test_discount_is_expired() {
		$this->assertFalse( edd_is_discount_expired( $this->_discount_id ) );
	}

	/**
	 * @covers edd_is_discount_maxed_out()
	 */
	public function test_discount_is_maxed_out() {
		$this->assertTrue( edd_is_discount_maxed_out( $this->_discount_id ) );
	}

	/**
	 * @covers edd_discount_is_min_met()
	 */
	public function test_discount_is_min_met() {
		$this->assertFalse( edd_discount_is_min_met( $this->_discount_id ) );
	}

	/**
	 * @covers edd_is_discount_used()
	 */
	public function test_discount_is_used() {
		$this->assertFalse( edd_is_discount_used( '20OFF' ) );
	}

	/**
	 * @covers EDD_Discount::setup_discount()
	 * @covers EDD_Discount::get_is_single_use()
	 * @covers EDD_Discount::is_used()
	 */
	public function test_is_used_case_insensitive() {
		$payment_id         = EDD_Helper_Payment::create_simple_payment();
		$payment            = edd_get_payment( $payment_id );
		$payment->discounts = '20off';
		$payment->status    = 'publish';
		$payment->save();

		$discount                = new EDD_Discount( '20OFF', true );
		$discount->is_single_use = true;
		$this->assertTrue( $discount->is_used( 'admin@example.org', false ) );
		$discount->is_single_use = false;
		EDD_Helper_Payment::delete_payment( $payment_id );
	}

	/**
	 * @covers edd_is_discount_valid()
	 */
	public function test_discount_is_valid_when_purchasing() {
		$this->assertFalse( edd_is_discount_valid( '20OFF' ) );
	}

	/**
	 * @covers edd_get_discount_id_by_code()
	 *@covers edd_get_discount_id_by()
	 */
	public function test_discount_id_by_code() {
		$id       = edd_get_discount_id_by_code( '20OFF' );
		$discount = edd_get_discount_by( 'code', '20OFF' );

		$this->assertSame( $discount->id, $id );
	}


	/**
	 * @covers edd_get_discounted_amount()
	 */
	public function test_get_discounted_amount() {
		$this->assertEquals( '432', edd_get_discounted_amount( '20OFF', '540' ) );
		$this->assertEquals( '150', edd_get_discounted_amount( 'DOUBLE', '75' ) );
		$this->assertEquals( '10', edd_get_discounted_amount( '10FLAT', '20' ) );

		// Test that an invalid Code returns the base price
		$this->assertEquals( '10', edd_get_discounted_amount( 'FAKEDISCOUNT', '10' ) );
	}

	/**
	 * @covers edd_get_discount_id_by_code()
	 * @covers edd_get_discount_uses()
	 * @covers edd_increase_discount_usage()
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
	 * @covers edd_get_discount_code()
	 * @covers edd_increase_discount_usage()
	 */
	public function test_discount_inactive_at_max() {
		update_post_meta( $this->_discount_id, '_edd_discount_status', 'active' );

		$code = edd_get_discount_code( $this->_discount_id );

		update_post_meta( $this->_discount_id, '_edd_discount_max', 10 );
		update_post_meta( $this->_discount_id, '_edd_discount_uses', 9 );

		edd_increase_discount_usage( $code );

		$this->assertEquals( 'inactive', get_post_meta( $this->_discount_id, '_edd_discount_status', true ) );
	}

	/**
	 * @covers _edd_discount_update_meta_backcompat()
	 * @covers edd_get_discount_code()
	 * @covers edd_increase_discount_usage()
	 */
	public function test_discount_active_after_decreasing_at_max() {
		update_post_meta( $this->_discount_id, '_edd_discount_max', 10 );
		update_post_meta( $this->_discount_id, '_edd_discount_uses', 10 );
		update_post_meta( $this->_discount_id, '_edd_discount_status', 'inactive' );

		$code = edd_get_discount_code( $this->_discount_id );

		edd_decrease_discount_usage( $code );

		$this->assertEquals( 'active', get_post_meta( $this->_discount_id, '_edd_discount_status', true ) );
	}

	/**
	 * @covers edd_get_discount_id_by_code()
	 * @covers edd_get_discount_uses()
	 * @covers edd_decrease_discount_usage()
	 */
	public function test_decrease_discount_usage() {
		$id   = edd_get_discount_id_by_code( '20OFF' );
		$uses = edd_get_discount_uses( $id );

		$decreased = edd_decrease_discount_usage( '20OFF' );
		$this->assertSame( $decreased, (int) $uses - 1 );

		// Test missing codes
		$this->assertFalse( edd_decrease_discount_usage( 'INVALIDDISCOUNTCODE' ) );
	}

	/**
	 * @covers _edd_discount_post_meta_bc_filter()
	 * @covers edd_format_discount_rate()
	 */
	public function test_formatted_discount_amount() {
		$rate = get_post_meta( $this->_discount_id, '_edd_discount_amount', true );
		$this->assertSame( '20%', edd_format_discount_rate( 'percent', $rate ) );
	}

	/**
	 * @covers edd_get_discount_by()
	 */
	public function test_edd_get_discount_by() {
		$discount = edd_get_discount_by( 'id', $this->_discount_id );

		$this->assertEquals( $discount->ID, $this->_discount_id );
		$this->assertEquals( '20 Percent Off', edd_get_discount_by( 'code', '20OFF' )->post_title );
		$this->assertEquals( $discount->ID, edd_get_discount_by( 'code', '20OFF' )->ID );
		$this->assertEquals( $discount->ID, edd_get_discount_by( 'name', '20 Percent Off' )->ID );
	}

	/**
	 * @covers edd_get_discount_amount()
	 * @covers edd_format_discount_rate()
	 */
	public function test_formatted_discount_amount_negative() {
		$amount = edd_get_discount_amount( $this->_negativediscount_id );
		$this->assertSame( '-100%', edd_format_discount_rate( 'percent', $amount ) );
	}

	/**
	 * @covers edd_get_discount_amount()
	 * @covers edd_format_discount_rate()
	 */
	public function test_formatted_discount_amount_flat() {
		$amount = edd_get_discount_amount( $this->_flatdiscount_id );
		$this->assertSame( '&#36;10.00', edd_format_discount_rate( 'flat', $amount ) );
	}

	/**
	 * @covers edd_get_discount_excluded_products()
	 * @covers EDD_Discount::get_excluded_products()
	 */
	public function test_discount_excluded_products() {
		$this->assertInternalType( 'array', edd_get_discount_excluded_products( $this->_discount_id ) );
	}

	/**
	 * @covers edd_get_discount_product_reqs()
	 * @covers EDD_Discount::get_product_reqs()
	 */
	public function test_discount_product_reqs() {
		$this->assertInternalType( 'array', edd_get_discount_product_reqs( $this->_discount_id ) );
	}

	/**
	 * @covers edd_remove_discount()
	 * @covers edd_get_discount()
	 */
	public function test_deletion_of_discount() {
		edd_remove_discount( $this->_discount_id );
		$this->assertFalse( edd_get_discount( $this->_discount_id ) );

		edd_remove_discount( $this->_negativediscount_id );
		$this->assertFalse( edd_get_discount( $this->_negativediscount_id ) );
	}

	/**
	 * @covers edd_set_cart_discount()
	 * @covers edd_get_discount_code()
	 */
	public function test_set_discount() {
		EDD()->session->set( 'cart_discounts', null );

		edd_add_to_cart( $this->_download->ID );

		$this->assertEquals( '20.00', edd_get_cart_total() );

		edd_set_cart_discount( edd_get_discount_code( $this->_discount_id ) );
		$this->assertEquals( '16.00', edd_get_cart_total() );
	}

	/**
	 * @covers edd_set_cart_discount()
	 */
	public function test_set_multiple_discounts() {
		EDD()->session->set( 'cart_discounts', null );

		edd_update_option( 'allow_multiple_discounts', true );

		edd_add_to_cart( $this->_download->ID );

		$this->assertEquals( '20.00', edd_get_cart_total() );

		// Test a single discount code
		$discounts = edd_set_cart_discount( $this->_discount->code );

		$this->assertInternalType( 'array', $discounts );
		$this->assertTrue( 1 === count( $discounts ) );
		$this->assertEquals( '16.00', edd_get_cart_total() );

		// Test a single discount code again but with lower case
		$discounts = edd_set_cart_discount( strtolower( $this->_discount->code ) );

		$this->assertInternalType( 'array', $discounts );
		$this->assertTrue( 1 === count( $discounts ) );
		$this->assertEquals( '16.00', edd_get_cart_total() );

		// Test a new code
		$code_id = EDD_Helper_Discount::create_simple_percent_discount();
		update_post_meta( $code_id, '_edd_discount_code', 'SECONDcode' );

		$discounts = edd_set_cart_discount( 'SECONDCODE' );

		$this->assertInternalType( 'array', $discounts );
		$this->assertTrue( 2 === count( $discounts ) );
		$this->assertEquals( '12.00', edd_get_cart_total() );
	}

	/**
	 * @covers edd_store_discount()
	 * @covers edd_get_cart_discountable_subtotal()
	 */
	public function test_discountable_subtotal() {
		$download_1 = EDD_Helper_Download::create_simple_download();
		$download_2 = EDD_Helper_Download::create_simple_download();
		edd_add_to_cart( $download_1->ID );
		edd_add_to_cart( $download_2->ID );

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

		$this->assertEquals( '20', edd_get_cart_discountable_subtotal( $discount ) );

		$download_3 = EDD_Helper_Download::create_simple_download();
		edd_add_to_cart( $download_3->ID );

		$this->assertEquals( '40', edd_get_cart_discountable_subtotal( $discount ) );

		EDD_Helper_Download::delete_download( $download_1->ID );
		EDD_Helper_Download::delete_download( $download_2->ID );
		EDD_Helper_Download::delete_download( $download_3->ID );
		EDD_Helper_Discount::delete_discount( $discount );
	}

	/**
	 * @covers edd_discount_is_min_met()
	 * @covers edd_is_discount_valid()
	 */
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
	}

	/**
	 * @covers edd_get_discounts()
	 */
	public function test_edd_get_discounts() {
		$found_discounts = edd_get_discounts( array(
			'posts_per_page' => 3,
		) );

		$this->assertTrue( 3 === count( $found_discounts ) );
	}
}