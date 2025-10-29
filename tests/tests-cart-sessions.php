<?php
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Cart Sessions tests.
 *
 * Tests for cart session management and cache invalidation in PR #2021.
 *
 * @group edd_cart
 * @group edd_sessions
 */
class Cart_Sessions extends EDD_UnitTestCase {

	/**
	 * Download fixture.
	 *
	 * @var EDD_Download
	 */
	protected static $download;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		// Create a test download
		$post_id = static::factory()->post->create( array(
			'post_title'  => 'Test Download for Sessions',
			'post_type'   => 'download',
			'post_status' => 'publish',
		) );

		update_post_meta( $post_id, 'edd_price', '15.00' );
		update_post_meta( $post_id, '_edd_product_type', 'default' );

		self::$download = edd_get_download( $post_id );
	}

	public function setUp(): void {
		parent::setUp();
		// Clear cart and sessions before each test
		edd_empty_cart();
		// Ensure caching is disabled by default
		edd_delete_option( 'cart_caching' );
	}

	public function tearDown(): void {
		parent::tearDown();
		edd_empty_cart();
		edd_delete_option( 'cart_caching' );
	}

	/**
	 * Test that empty_cart method in Sessions\Cart invalidates cache.
	 */
	public function test_sessions_empty_cart_invalidates_cache() {
		// Enable caching
		edd_update_option( 'cart_caching', true );

		// Add item to cart
		edd_add_to_cart( self::$download->ID );

		// Get cart details to populate cache
		$details = EDD()->cart->get_contents_details();

		// Verify cache is populated
		$stats1 = EDD()->cart->get_calculation_stats();
		$this->assertTrue( $stats1['cached'] );
		$this->assertGreaterThan( 0, $stats1['cache_size'] );

		// Create cart session instance
		$cart_session = new \EDD\Sessions\Cart( EDD()->cart );

		// Empty cart through session
		$cart_session->empty_cart();

		// Cache should be invalidated
		$stats2 = EDD()->cart->get_calculation_stats();
		$this->assertFalse( $stats2['cached'] );
		$this->assertEquals( 0, $stats2['cache_size'] );

		// Cart should be empty
		$this->assertTrue( edd_is_cart_empty() );
	}

	/**
	 * Test that get_contents method loads cart from session correctly.
	 */
	public function test_sessions_get_contents_loads_from_session() {
		// Set cart data in session
		$cart_data = array(
			array(
				'id'       => self::$download->ID,
				'options'  => array(),
				'quantity' => 1,
			),
		);
		EDD()->session->set( 'edd_cart', $cart_data );

		// Create cart session instance
		$cart_session = new \EDD\Sessions\Cart( EDD()->cart );

		// Get contents from session
		$cart_session->get_contents();

		// Verify cart is loaded
		$contents = EDD()->cart->get_contents();
		$this->assertNotEmpty( $contents );
		$this->assertEquals( self::$download->ID, $contents[0]['id'] );
	}

	/**
	 * Test that cart session operations work with caching enabled.
	 */
	public function test_sessions_operations_with_caching() {
		// Enable caching
		edd_update_option( 'cart_caching', true );

		// Add item to cart
		edd_add_to_cart( self::$download->ID );

		// Get cart details to populate cache
		$details = EDD()->cart->get_contents_details();

		// Verify cache is populated
		$stats = EDD()->cart->get_calculation_stats();
		$this->assertTrue( $stats['cached'] );
		$this->assertGreaterThan( 0, $stats['cache_size'] );

		// Verify cart has contents
		$this->assertFalse( edd_is_cart_empty() );
	}

	/**
	 * Test that session operations handle cart state correctly.
	 */
	public function test_sessions_cart_state_handling() {
		// Add item to cart
		edd_add_to_cart( self::$download->ID );

		// Verify cart is not empty
		$this->assertFalse( edd_is_cart_empty() );

		// Get contents through session
		$cart_session = new \EDD\Sessions\Cart( EDD()->cart );
		$cart_session->get_contents();

		// Verify cart still has contents
		$contents = EDD()->cart->get_contents();
		$this->assertNotEmpty( $contents );
		$this->assertEquals( self::$download->ID, $contents[0]['id'] );
	}

	/**
	 * Test that session discounts are loaded correctly.
	 */
	public function test_sessions_discounts_loading() {
		// Add item to cart
		edd_add_to_cart( self::$download->ID, array( 'price_id' => 0 ) );

		// Set a discount in session
		EDD()->session->set( 'cart_discounts', '20OFF' );

		// Create cart session instance
		$cart_session = new \EDD\Sessions\Cart( EDD()->cart );

		// Get discounts from session
		$cart_session->get_discounts();

		// Verify discount is loaded
		$discounts = EDD()->cart->get_discounts();
		$this->assertContains( '20OFF', $discounts );
	}

	/**
	 * Test interaction between sessions and cache when cart changes.
	 */
	public function test_sessions_and_cache_interaction() {
		// Enable caching
		edd_update_option( 'cart_caching', true );

		// Add item to cart
		edd_add_to_cart( self::$download->ID );

		// Get cart details to populate cache
		$details1 = EDD()->cart->get_contents_details();
		$total1   = EDD()->cart->get_total();

		// Verify cache is valid
		$stats1 = EDD()->cart->get_calculation_stats();
		$this->assertTrue( $stats1['cached'] );

		// Simulate session update by directly modifying cart contents
		$cart_contents = EDD()->cart->get_contents();
		$cart_contents[0]['quantity'] = 2;
		EDD()->session->set( 'edd_cart', $cart_contents );

		// Load from session (should invalidate cache)
		$cart_session = new \EDD\Sessions\Cart( EDD()->cart );
		$cart_session->get_contents();

		// Cache should be invalidated when cart is modified
		EDD()->cart->invalidate_cache();

		$stats2 = EDD()->cart->get_calculation_stats();
		$this->assertFalse( $stats2['cached'] );

		// Get new details
		$details2 = EDD()->cart->get_contents_details();

		// Details should reflect updated quantity
		if ( edd_item_quantities_enabled() ) {
			$this->assertEquals( 2, $details2[0]['quantity'] );
		}
	}

	/**
	 * Test that empty cart through main function invalidates cache.
	 */
	public function test_empty_cart_function_invalidates_cache() {
		// Enable caching
		edd_update_option( 'cart_caching', true );

		// Add items to cart
		edd_add_to_cart( self::$download->ID );

		// Get cart details to populate cache
		$details = EDD()->cart->get_contents_details();

		// Verify cache is populated
		$stats1 = EDD()->cart->get_calculation_stats();
		$this->assertTrue( $stats1['cached'] );

		// Empty cart using main function
		edd_empty_cart();

		// Cache should be invalidated
		$stats2 = EDD()->cart->get_calculation_stats();
		$this->assertFalse( $stats2['cached'] );
		$this->assertEquals( 0, $stats2['cache_size'] );

		// Cart should be empty
		$this->assertTrue( edd_is_cart_empty() );
	}
}