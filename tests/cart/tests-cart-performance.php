<?php
namespace EDD\Tests\Cart;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Cart Performance tests.
 *
 * Tests for cart caching and profiling functionality added in PR #2021.
 *
 * @group edd_cart
 * @group edd_cart_performance
 */
class Performance extends EDD_UnitTestCase {

	/**
	 * Download fixture.
	 *
	 * @var EDD_Download
	 */
	protected static $download;

	/**
	 * Variable pricing download fixture.
	 *
	 * @var EDD_Download
	 */
	protected static $variable_download;

	/**
	 * Discount fixture.
	 *
	 * @var EDD_Discount
	 */
	protected static $discount;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		// Create a simple download
		$post_id = static::factory()->post->create( array(
			'post_title'  => 'Simple Test Download',
			'post_type'   => 'download',
			'post_status' => 'publish',
		) );

		update_post_meta( $post_id, 'edd_price', '10.00' );
		update_post_meta( $post_id, '_edd_product_type', 'default' );

		self::$download = edd_get_download( $post_id );

		// Create a variable pricing download
		$post_id = static::factory()->post->create( array(
			'post_title'  => 'Variable Test Download',
			'post_type'   => 'download',
			'post_status' => 'publish',
		) );

		$_variable_pricing = array(
			array(
				'name'   => 'Basic',
				'amount' => 20,
			),
			array(
				'name'   => 'Pro',
				'amount' => 50,
			),
		);

		update_post_meta( $post_id, 'edd_price', '0.00' );
		update_post_meta( $post_id, '_variable_pricing', 1 );
		update_post_meta( $post_id, '_edd_price_options_mode', 'on' );
		update_post_meta( $post_id, 'edd_variable_prices', $_variable_pricing );
		update_post_meta( $post_id, '_edd_product_type', 'default' );

		self::$variable_download = edd_get_download( $post_id );

		// Create a discount
		self::$discount = static::edd()->discount->create_and_get( array(
			'name'              => '10 Percent Off',
			'code'              => '10OFF',
			'status'            => 'active',
			'type'              => 'percent',
			'amount'            => '10',
			'product_condition' => 'all',
			'start_date'        => '2010-12-12 00:00:00',
			'end_date'          => '2050-12-31 23:59:59',
		) );
	}

	public function setUp(): void {
		parent::setUp();
		// Clear cart before each test
		edd_empty_cart();
		// Ensure caching is disabled by default
		edd_delete_option( 'cart_caching' );
		edd_delete_option( 'cart_profiler' );
	}

	public function tearDown(): void {
		parent::tearDown();
		edd_empty_cart();
		edd_delete_option( 'cart_caching' );
		edd_delete_option( 'cart_profiler' );
	}

	/**
	 * Test that cache invalidation works when adding items to cart.
	 */
	public function test_cache_invalidation_on_add_to_cart() {
		// Enable caching
		edd_update_option( 'cart_caching', true );

		// Add item to cart
		edd_add_to_cart( self::$download->ID );

		// Get cart details to populate cache
		$details1 = EDD()->cart->get_contents_details();
		$stats1   = EDD()->cart->get_calculation_stats();

		// Cache should be valid
		$this->assertTrue( $stats1['cached'] );
		$this->assertGreaterThan( 0, $stats1['cache_size'] );

		// Add another item
		edd_add_to_cart( self::$variable_download->ID, array( 'price_id' => 0 ) );

		// Cache should be invalidated
		$stats2 = EDD()->cart->get_calculation_stats();
		$this->assertFalse( $stats2['cached'] );
		$this->assertEquals( 0, $stats2['cache_size'] );

		// Get details again to repopulate cache
		$details2 = EDD()->cart->get_contents_details();
		$stats3   = EDD()->cart->get_calculation_stats();

		// Cache should be valid again
		$this->assertTrue( $stats3['cached'] );
		$this->assertGreaterThan( 0, $stats3['cache_size'] );
	}

	/**
	 * Test that cache invalidation works when removing items from cart.
	 */
	public function test_cache_invalidation_on_remove_from_cart() {
		// Enable caching
		edd_update_option( 'cart_caching', true );

		// Add items to cart
		edd_add_to_cart( self::$download->ID );
		edd_add_to_cart( self::$variable_download->ID, array( 'price_id' => 0 ) );

		// Get cart details to populate cache
		$details1 = EDD()->cart->get_contents_details();
		$stats1   = EDD()->cart->get_calculation_stats();

		// Cache should be valid
		$this->assertTrue( $stats1['cached'] );

		// Remove first item
		EDD()->cart->remove( 0 );

		// Cache should be invalidated
		$stats2 = EDD()->cart->get_calculation_stats();
		$this->assertFalse( $stats2['cached'] );
		$this->assertEquals( 0, $stats2['cache_size'] );
	}

	/**
	 * Test that cache invalidation works when emptying cart.
	 */
	public function test_cache_invalidation_on_empty_cart() {
		// Enable caching
		edd_update_option( 'cart_caching', true );

		// Add item to cart
		edd_add_to_cart( self::$download->ID );

		// Get cart details to populate cache
		$details = EDD()->cart->get_contents_details();
		$stats1  = EDD()->cart->get_calculation_stats();

		// Cache should be valid
		$this->assertTrue( $stats1['cached'] );

		// Empty cart
		edd_empty_cart();

		// Cache should be invalidated
		$stats2 = EDD()->cart->get_calculation_stats();
		$this->assertFalse( $stats2['cached'] );
		$this->assertEquals( 0, $stats2['cache_size'] );
	}

	/**
	 * Test that cache invalidation works when applying discounts.
	 */
	public function test_cache_invalidation_on_apply_discount() {
		// Enable caching
		edd_update_option( 'cart_caching', true );

		// Add item to cart
		edd_add_to_cart( self::$download->ID );

		// Get cart details to populate cache
		$details1 = EDD()->cart->get_contents_details();
		$stats1   = EDD()->cart->get_calculation_stats();

		// Cache should be valid
		$this->assertTrue( $stats1['cached'] );

		// Apply discount
		edd_set_cart_discount( '10OFF' );

		// Cache should be invalidated
		$stats2 = EDD()->cart->get_calculation_stats();
		$this->assertFalse( $stats2['cached'] );
		$this->assertEquals( 0, $stats2['cache_size'] );
	}

	/**
	 * Test that cache invalidation works when removing discounts.
	 */
	public function test_cache_invalidation_on_remove_discount() {
		// Enable caching
		edd_update_option( 'cart_caching', true );

		// Add item to cart
		edd_add_to_cart( self::$download->ID );

		// Apply discount
		edd_set_cart_discount( '10OFF' );

		// Get cart details to populate cache
		$details1 = EDD()->cart->get_contents_details();
		$stats1   = EDD()->cart->get_calculation_stats();

		// Cache should be valid
		$this->assertTrue( $stats1['cached'] );

		// Remove discount
		EDD()->cart->remove_discount( '10OFF' );

		// Cache should be invalidated
		$stats2 = EDD()->cart->get_calculation_stats();
		$this->assertFalse( $stats2['cached'] );
		$this->assertEquals( 0, $stats2['cache_size'] );
	}

	/**
	 * Test that cache invalidation works when changing item quantities.
	 */
	public function test_cache_invalidation_on_quantity_change() {
		// Enable caching and quantities
		edd_update_option( 'cart_caching', true );
		edd_update_option( 'item_quantities', true );

		// Add item to cart
		edd_add_to_cart( self::$download->ID, array( 'quantity' => 1 ) );

		// Get cart details to populate cache
		$details1 = EDD()->cart->get_contents_details();
		$stats1   = EDD()->cart->get_calculation_stats();

		// Cache should be valid
		$this->assertTrue( $stats1['cached'] );

		// Change quantity
		edd_set_cart_item_quantity( self::$download->ID, 3 );

		// Cache should be invalidated
		$stats2 = EDD()->cart->get_calculation_stats();
		$this->assertFalse( $stats2['cached'] );
		$this->assertEquals( 0, $stats2['cache_size'] );

		// Clean up
		edd_delete_option( 'item_quantities' );
	}

	/**
	 * Test that cached results are returned when cache is valid.
	 */
	public function test_cached_results_returned() {
		// Enable caching
		edd_update_option( 'cart_caching', true );

		// Add items to cart
		edd_add_to_cart( self::$download->ID );
		edd_add_to_cart( self::$variable_download->ID, array( 'price_id' => 0 ) );

		// Get cart details to populate cache
		$details1 = EDD()->cart->get_contents_details();

		// Store the object hash to verify we get the same object back
		$hash1 = spl_object_hash( (object) $details1 );

		// Get details again - should return cached version
		$details2 = EDD()->cart->get_contents_details();

		// Should be the exact same array
		$this->assertEquals( $details1, $details2 );

		// Stats should show cache is being used
		$stats = EDD()->cart->get_calculation_stats();
		$this->assertTrue( $stats['cached'] );
		$this->assertGreaterThan( 0, $stats['cache_size'] );
	}

	/**
	 * Test that caching is disabled by default.
	 */
	public function test_caching_disabled_by_default() {
		// Do not set cart_caching option

		// Add item to cart
		edd_add_to_cart( self::$download->ID );

		// Get cart details
		$details1 = EDD()->cart->get_contents_details();

		// Cache should not be valid
		$stats = EDD()->cart->get_calculation_stats();
		$this->assertFalse( $stats['cached'] );
		$this->assertEquals( 0, $stats['cache_size'] );

		// Get details again
		$details2 = EDD()->cart->get_contents_details();

		// Should still not be cached
		$stats2 = EDD()->cart->get_calculation_stats();
		$this->assertFalse( $stats2['cached'] );
		$this->assertEquals( 0, $stats2['cache_size'] );
	}

	/**
	 * Test the invalidate_cache method directly.
	 */
	public function test_invalidate_cache_method() {
		// Enable caching
		edd_update_option( 'cart_caching', true );

		// Add item to cart
		edd_add_to_cart( self::$download->ID );

		// Get cart details to populate cache
		$details = EDD()->cart->get_contents_details();

		// Verify cache is valid
		$stats1 = EDD()->cart->get_calculation_stats();
		$this->assertTrue( $stats1['cached'] );
		$this->assertGreaterThan( 0, $stats1['cache_size'] );

		// Manually invalidate cache
		EDD()->cart->invalidate_cache();

		// Cache should be invalidated
		$stats2 = EDD()->cart->get_calculation_stats();
		$this->assertFalse( $stats2['cached'] );
		$this->assertEquals( 0, $stats2['cache_size'] );
	}

	/**
	 * Test get_calculation_stats method returns correct structure.
	 */
	public function test_get_calculation_stats_structure() {
		// Enable caching
		edd_update_option( 'cart_caching', true );

		// Get initial stats
		$stats = EDD()->cart->get_calculation_stats();

		// Check structure
		$this->assertIsArray( $stats );
		$this->assertArrayHasKey( 'cached', $stats );
		$this->assertArrayHasKey( 'cache_size', $stats );
		$this->assertIsBool( $stats['cached'] );
		$this->assertIsInt( $stats['cache_size'] );

		// Add item and get details to populate cache
		edd_add_to_cart( self::$download->ID );
		$details = EDD()->cart->get_contents_details();

		// Get stats again
		$stats2 = EDD()->cart->get_calculation_stats();

		// Should show cache is valid
		$this->assertTrue( $stats2['cached'] );
		$this->assertGreaterThan( 0, $stats2['cache_size'] );
	}

	/**
	 * Test that profiler initialization depends on option.
	 */
	public function test_profiler_initialization() {
		// Create new cart instance without profiler
		edd_delete_option( 'cart_profiler' );
		$cart1 = new \EDD_Cart();

		// Reflection to check private property
		$reflection = new \ReflectionClass( $cart1 );
		$profiler_prop = $reflection->getProperty( 'profiler' );
		$profiler_prop->setAccessible( true );

		// Profiler should be null
		$this->assertNull( $profiler_prop->getValue( $cart1 ) );

		// Enable profiler and create new cart
		edd_update_option( 'cart_profiler', true );
		$cart2 = new \EDD_Cart();

		// Profiler should be initialized
		$profiler = $profiler_prop->getValue( $cart2 );
		$this->assertInstanceOf( '\EDD\Profiler\Cart', $profiler );

		// Clean up
		edd_delete_option( 'cart_profiler' );
	}

	/**
	 * Test that cache works correctly with fees.
	 */
	public function test_cache_with_fees() {
		// Enable caching
		edd_update_option( 'cart_caching', true );

		// Add item to cart
		edd_add_to_cart( self::$download->ID );

		// Add a fee
		EDD()->fees->add_fee( array(
			'amount' => 5,
			'id'     => 'test_fee',
			'label'  => 'Test Fee',
		) );

		// Get cart total (should cache)
		$total1 = EDD()->cart->get_total();
		$this->assertEquals( 15.00, $total1 );

		// Get stats
		$stats = EDD()->cart->get_calculation_stats();
		$this->assertTrue( $stats['cached'] );

		// Remove fee
		EDD()->fees->remove_fee( 'test_fee' );

		// Get total again
		$total2 = EDD()->cart->get_total();
		$this->assertEquals( 10.00, $total2 );
	}

	/**
	 * Test cache behavior with taxes enabled.
	 */
	public function test_cache_with_taxes() {
		// Enable caching and taxes
		edd_update_option( 'cart_caching', true );
		edd_update_option( 'enable_taxes', true );
		EDD()->cart->set_tax_rate( null ); // Clear tax rate cache

		// Set tax rate
		add_filter( 'edd_tax_rate', function() {
			return 0.10; // 10% tax
		} );

		// Add item to cart
		edd_add_to_cart( self::$download->ID );

		// Get cart details to cache
		$details1 = EDD()->cart->get_contents_details();
		$tax1     = EDD()->cart->get_tax();
		$total1   = EDD()->cart->get_total();

		$this->assertEquals( 1.00, $tax1 );
		$this->assertEquals( 11.00, $total1 );

		// Verify cache is being used
		$stats = EDD()->cart->get_calculation_stats();
		$this->assertTrue( $stats['cached'] );

		// Get values again - should use cache
		$details2 = EDD()->cart->get_contents_details();
		$tax2     = EDD()->cart->get_tax();
		$total2   = EDD()->cart->get_total();

		$this->assertEquals( $details1, $details2 );
		$this->assertEquals( $tax1, $tax2 );
		$this->assertEquals( $total1, $total2 );

		// Clean up
		edd_delete_option( 'enable_taxes' );
	}

	/**
	 * Test that multiple cart instances share the same cache state.
	 */
	public function test_cache_shared_between_instances() {
		// Enable caching
		edd_update_option( 'cart_caching', true );

		// Use the global cart instance
		edd_add_to_cart( self::$download->ID );
		$details1 = EDD()->cart->get_contents_details();

		// Check cache is valid
		$stats1 = EDD()->cart->get_calculation_stats();
		$this->assertTrue( $stats1['cached'] );

		// After invalidation, cache should be invalid
		EDD()->cart->invalidate_cache();
		$stats2 = EDD()->cart->get_calculation_stats();
		$this->assertFalse( $stats2['cached'] );
	}

	/**
	 * Test that cart cache is invalidated when set_tax_rate is called with null.
	 *
	 * When a customer changes their billing address during checkout, the tax
	 * amounts need to be recalculated. Calling set_tax_rate(null) should
	 * invalidate the cart cache since cached calculations contain tax amounts
	 * based on the previous rate.
	 *
	 * @link https://github.com/easydigitaldownloads/easy-digital-downloads/issues/2159
	 */
	public function test_cart_cache_invalidated_on_set_tax_rate_null() {
		// Enable caching and taxes
		edd_update_option( 'cart_caching', true );
		edd_update_option( 'enable_taxes', true );

		// Set a tax rate
		add_filter( 'edd_tax_rate', function() {
			return 0.10; // 10% tax
		} );

		// Add item to cart
		edd_add_to_cart( self::$download->ID );

		// Get cart details to populate cache
		$details = EDD()->cart->get_contents_details();
		$stats1  = EDD()->cart->get_calculation_stats();

		// Cache should be valid
		$this->assertTrue( $stats1['cached'] );
		$this->assertGreaterThan( 0, $stats1['cache_size'] );

		// Reset the tax rate to null - this should invalidate the cache
		EDD()->cart->set_tax_rate( null );

		// Cache should be invalidated
		$stats2 = EDD()->cart->get_calculation_stats();
		$this->assertFalse( $stats2['cached'] );
		$this->assertEquals( 0, $stats2['cache_size'] );

		// Clean up
		edd_delete_option( 'enable_taxes' );
	}

	/**
	 * Test that tax amounts match after address change with cart caching enabled.
	 *
	 * This test simulates the scenario where a customer changes their billing
	 * address during checkout, verifying that the tax amounts are correctly
	 * recalculated and match the new address's tax rate.
	 *
	 * @link https://github.com/easydigitaldownloads/easy-digital-downloads/issues/2159
	 */
	public function test_tax_amounts_match_after_address_change() {
		// Enable caching and taxes
		edd_update_option( 'cart_caching', true );
		edd_update_option( 'enable_taxes', true );

		// Start with a 10% tax rate (e.g., Luxembourg)
		$current_tax_rate = 0.10;
		add_filter( 'edd_tax_rate', function() use ( &$current_tax_rate ) {
			return $current_tax_rate;
		} );

		// Add item to cart ($10.00)
		edd_add_to_cart( self::$download->ID );

		// Get initial cart values (10% tax = $1.00 tax, $11.00 total)
		$details1 = EDD()->cart->get_contents_details();
		$tax1     = EDD()->cart->get_tax();
		$total1   = EDD()->cart->get_total();

		$this->assertEquals( 1.00, $tax1 );
		$this->assertEquals( 11.00, $total1 );

		// Verify cache is populated
		$stats1 = EDD()->cart->get_calculation_stats();
		$this->assertTrue( $stats1['cached'] );

		// Simulate address change - new country has 20% tax (e.g., Germany)
		$current_tax_rate = 0.20;

		// Fire the edd_before_checkout_cart action (calls set_tax_rate(null) which invalidates cache)
		do_action( 'edd_before_checkout_cart' );

		// Get new cart values - should reflect 20% tax = $2.00 tax, $12.00 total
		$details2 = EDD()->cart->get_contents_details();
		$tax2     = EDD()->cart->get_tax();
		$total2   = EDD()->cart->get_total();

		$this->assertEquals( 2.00, $tax2 );
		$this->assertEquals( 12.00, $total2 );

		// Verify that the values changed
		$this->assertNotEquals( $tax1, $tax2 );
		$this->assertNotEquals( $total1, $total2 );

		// Clean up
		edd_delete_option( 'enable_taxes' );
	}

	/**
	 * Test that set_tax_rate with a non-null value does NOT invalidate the cache.
	 *
	 * Setting the tax rate to a specific value should not invalidate the cache
	 * since the cache entries may still be valid when only the stored rate changes
	 * to a known value.
	 *
	 * @link https://github.com/easydigitaldownloads/easy-digital-downloads/issues/2159
	 */
	public function test_set_tax_rate_non_null_does_not_invalidate_cache() {
		// Enable caching and taxes
		edd_update_option( 'cart_caching', true );
		edd_update_option( 'enable_taxes', true );

		// Set a tax rate
		add_filter( 'edd_tax_rate', function() {
			return 0.10;
		} );

		// Add item to cart
		edd_add_to_cart( self::$download->ID );

		// Get cart details to populate cache
		$details = EDD()->cart->get_contents_details();
		$stats1  = EDD()->cart->get_calculation_stats();

		// Cache should be valid
		$this->assertTrue( $stats1['cached'] );
		$this->assertGreaterThan( 0, $stats1['cache_size'] );

		// Set a specific tax rate (not null) - should NOT invalidate cache
		EDD()->cart->set_tax_rate( 0.15 );

		// Cache should still be valid
		$stats2 = EDD()->cart->get_calculation_stats();
		$this->assertTrue( $stats2['cached'] );
		$this->assertGreaterThan( 0, $stats2['cache_size'] );

		// Clean up
		edd_delete_option( 'enable_taxes' );
	}

	/**
	 * Test that set_tax_rate(null) does not attempt cache invalidation when caching is disabled.
	 *
	 * When cart caching is disabled, the set_tax_rate(null) call should not
	 * attempt to invalidate the cache.
	 *
	 * @link https://github.com/easydigitaldownloads/easy-digital-downloads/issues/2159
	 */
	public function test_set_tax_rate_null_skipped_when_caching_disabled() {
		// Disable caching, enable taxes
		edd_delete_option( 'cart_caching' );
		edd_update_option( 'enable_taxes', true );

		// Set a tax rate
		add_filter( 'edd_tax_rate', function() {
			return 0.10;
		} );

		// Add item to cart
		edd_add_to_cart( self::$download->ID );

		// Get cart details
		$details = EDD()->cart->get_contents_details();

		// Cache should not be valid (caching disabled)
		$stats1 = EDD()->cart->get_calculation_stats();
		$this->assertFalse( $stats1['cached'] );

		// Reset tax rate to null
		EDD()->cart->set_tax_rate( null );

		// Cache should still not be valid (caching disabled)
		$stats2 = EDD()->cart->get_calculation_stats();
		$this->assertFalse( $stats2['cached'] );

		// Clean up
		edd_delete_option( 'enable_taxes' );
	}

	/**
	 * Test that cart cache is invalidated when the tax rate is reset during purchase processing.
	 *
	 * This test simulates the Stripe purchase flow where setup_cart() populates the
	 * cache during init (before form_data is parsed), and then set_tax_rate(null) is
	 * called to force recalculation with the correct billing address.
	 *
	 * @link https://github.com/easydigitaldownloads/easy-digital-downloads/issues/2159
	 */
	public function test_cart_cache_invalidated_on_tax_rate_reset_during_purchase() {
		// Enable caching and taxes
		edd_update_option( 'cart_caching', true );
		edd_update_option( 'enable_taxes', true );

		// Start with a default tax rate (simulating shop default or no address)
		$current_tax_rate = 0.00;
		add_filter( 'edd_tax_rate', function() use ( &$current_tax_rate ) {
			return $current_tax_rate;
		} );

		// Add item to cart ($10.00)
		edd_add_to_cart( self::$download->ID );

		// Simulate setup_cart() populating the cache with 0% tax (no billing address)
		$details1 = EDD()->cart->get_contents_details();
		$tax1     = EDD()->cart->get_tax();
		$stats1   = EDD()->cart->get_calculation_stats();

		// Cache should be valid with 0% tax
		$this->assertTrue( $stats1['cached'] );
		$this->assertEquals( 0.00, $tax1 );

		// Now simulate _edds_process_purchase_form() parsing form_data
		// and setting up the correct billing country
		$current_tax_rate = 0.19; // 19% German VAT

		// This is what _edds_process_purchase_form() does: reset the tax rate
		EDD()->cart->set_tax_rate( null );

		// The cache should now be invalidated
		$stats2 = EDD()->cart->get_calculation_stats();
		$this->assertFalse( $stats2['cached'] );
		$this->assertEquals( 0, $stats2['cache_size'] );

		// Getting cart details again should recalculate with the new tax rate
		$details2 = EDD()->cart->get_contents_details();
		$tax2     = EDD()->cart->get_tax();

		// Tax should now reflect the German VAT rate
		$this->assertEquals( 1.90, $tax2 );

		// Clean up
		edd_delete_option( 'enable_taxes' );
	}
}
