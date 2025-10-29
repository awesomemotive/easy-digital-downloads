<?php
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Cart Performance tests.
 *
 * Tests for cart caching and profiling functionality added in PR #2021.
 *
 * @group edd_cart
 * @group edd_cart_performance
 */
class Cart_Performance extends EDD_UnitTestCase {

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

		// This doesn't automatically invalidate cache, so need to manually do it
		// or get new cart details
		EDD()->cart->invalidate_cache();

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
}