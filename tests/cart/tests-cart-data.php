<?php
namespace EDD\Tests\Cart;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Cart\Data;

/**
 * Cart Data Tests
 *
 * @group edd_cart
 * @group edd_cart_data
 */
class CartData extends EDD_UnitTestCase {

	/**
	 * Download fixture.
	 *
	 * @var \EDD_Download
	 */
	protected static $download;

	/**
	 * Download with variable pricing fixture.
	 *
	 * @var \EDD_Download
	 */
	protected static $download_variable;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		// Create a simple download.
		$post_id = self::factory()->post->create( array(
			'post_title'  => 'Test Download',
			'post_type'   => 'download',
			'post_status' => 'publish',
		) );

		update_post_meta( $post_id, 'edd_price', '20.00' );
		update_post_meta( $post_id, '_edd_product_type', 'default' );

		self::$download = edd_get_download( $post_id );

		// Create a variable priced download.
		$variable_post_id = self::factory()->post->create( array(
			'post_title'  => 'Variable Download',
			'post_type'   => 'download',
			'post_status' => 'publish',
		) );

		$prices = array(
			array(
				'name'   => 'Basic',
				'amount' => 10,
			),
			array(
				'name'   => 'Premium',
				'amount' => 25,
			),
		);

		update_post_meta( $variable_post_id, 'edd_variable_prices', $prices );
		update_post_meta( $variable_post_id, '_variable_pricing', 1 );

		self::$download_variable = edd_get_download( $variable_post_id );
	}

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		edd_empty_cart();
	}

	/**
	 * Tear down after each test.
	 */
	public function tearDown(): void {
		parent::tearDown();
		edd_empty_cart();
	}

	/**
	 * Test get_cart_data returns empty data for empty cart.
	 *
	 * @covers \EDD\Cart\Data::get_cart_data
	 */
	public function test_get_cart_data_empty_cart() {
		$cart_data = Data::get_cart_data();

		$this->assertIsArray( $cart_data );
		$this->assertArrayHasKey( 'items', $cart_data );
		$this->assertArrayHasKey( 'subtotal', $cart_data );
		$this->assertArrayHasKey( 'has_items', $cart_data );
		$this->assertArrayHasKey( 'item_count', $cart_data );

		$this->assertEmpty( $cart_data['items'] );
		$this->assertFalse( $cart_data['has_items'] );
		$this->assertEquals( 0, $cart_data['item_count'] );
		$this->assertEquals( edd_currency_filter( edd_format_amount( 0.00 ) ), $cart_data['subtotal'] );
	}

	/**
	 * Test get_cart_data returns correct data with items.
	 *
	 * @covers \EDD\Cart\Data::get_cart_data
	 */
	public function test_get_cart_data_with_items() {
		edd_add_to_cart( self::$download->ID );

		$cart_data = Data::get_cart_data();

		$this->assertTrue( $cart_data['has_items'] );
		$this->assertEquals( 1, $cart_data['item_count'] );
		$this->assertCount( 1, $cart_data['items'] );
		$this->assertEquals( edd_currency_filter( edd_format_amount( 20.00 ) ), $cart_data['subtotal'] );
	}

	/**
	 * Test get_cart_data with multiple items.
	 *
	 * @covers \EDD\Cart\Data::get_cart_data
	 */
	public function test_get_cart_data_with_multiple_items() {
		edd_add_to_cart( self::$download->ID );
		edd_add_to_cart( self::$download_variable->ID, array( 'price_id' => 0 ) );

		$cart_data = Data::get_cart_data();

		$this->assertTrue( $cart_data['has_items'] );
		$this->assertEquals( 2, $cart_data['item_count'] );
		$this->assertCount( 2, $cart_data['items'] );
		$this->assertEquals( edd_currency_filter( edd_format_amount( 30.00 ) ), $cart_data['subtotal'] );
	}

	/**
	 * Test get_cart_data filter.
	 *
	 * @covers \EDD\Cart\Data::get_cart_data
	 */
	public function test_get_cart_data_filter() {
		add_filter( 'edd_cart_preview_data', function( $data ) {
			$data['custom_field'] = 'custom_value';
			return $data;
		} );

		$cart_data = Data::get_cart_data();

		$this->assertArrayHasKey( 'custom_field', $cart_data );
		$this->assertEquals( 'custom_value', $cart_data['custom_field'] );
	}

	/**
	 * Test format_cart_item returns empty array for invalid item.
	 *
	 * @covers \EDD\Cart\Data::format_cart_item
	 */
	public function test_format_cart_item_empty_item() {
		$item = array();

		$formatted = Data::format_cart_item( 0, $item );

		$this->assertIsArray( $formatted );
		$this->assertEmpty( $formatted );
	}

	/**
	 * Test format_cart_item returns empty array for non-existent download.
	 *
	 * @covers \EDD\Cart\Data::format_cart_item
	 */
	public function test_format_cart_item_invalid_download() {
		$item = array(
			'id' => 999999,
		);

		$formatted = Data::format_cart_item( 0, $item );

		$this->assertIsArray( $formatted );
		$this->assertEmpty( $formatted );
	}

	/**
	 * Test format_cart_item returns correct data for simple download.
	 *
	 * @covers \EDD\Cart\Data::format_cart_item
	 */
	public function test_format_cart_item_simple_download() {
		$item = array(
			'id'       => self::$download->ID,
			'quantity' => 1,
		);

		$formatted = Data::format_cart_item( 0, $item );

		$this->assertIsArray( $formatted );
		$this->assertArrayHasKey( 'key', $formatted );
		$this->assertArrayHasKey( 'id', $formatted );
		$this->assertArrayHasKey( 'name', $formatted );
		$this->assertArrayHasKey( 'price', $formatted );
		$this->assertArrayHasKey( 'price_raw', $formatted );
		$this->assertArrayHasKey( 'quantity', $formatted );
		$this->assertArrayHasKey( 'thumbnail', $formatted );
		$this->assertArrayHasKey( 'options', $formatted );
		$this->assertArrayHasKey( 'quantities_enabled', $formatted );

		$this->assertEquals( 0, $formatted['key'] );
		$this->assertEquals( self::$download->ID, $formatted['id'] );
		$this->assertEquals( 'Test Download', $formatted['name'] );
		$this->assertEquals( 1, $formatted['quantity'] );
		$this->assertEquals( 20.00, $formatted['price_raw'] );
	}

	/**
	 * Test format_cart_item with variable pricing.
	 *
	 * @covers \EDD\Cart\Data::format_cart_item
	 */
	public function test_format_cart_item_variable_pricing() {
		edd_add_to_cart( self::$download_variable->ID, array( 'price_id' => 1 ) );

		$cart_contents = EDD()->cart->get_contents_details();

		if ( ! empty( $cart_contents ) ) {
			foreach ( $cart_contents as $key => $item ) {
				$formatted = Data::format_cart_item( $key, $item );
				$this->assertEquals( self::$download_variable->ID, $formatted['id'] );
				$this->assertEquals( 'Variable Download — Premium', $formatted['name'] );
				$this->assertEquals( 25.00, $formatted['price_raw'] );
			}
		}
	}

	/**
	 * Test format_cart_item with quantity.
	 *
	 * @covers \EDD\Cart\Data::format_cart_item
	 */
	public function test_format_cart_item_with_quantity() {
		edd_update_option( 'item_quantities', true );

		$item = array(
			'id'       => self::$download->ID,
			'quantity' => 3,
			'options'  => array(),
		);

		$formatted = Data::format_cart_item( 0, $item );

		$this->assertEquals( 3, $formatted['quantity'] );
		$this->assertTrue( $formatted['quantities_enabled'] );
	}

	/**
	 * Test format_cart_item filter.
	 *
	 * @covers \EDD\Cart\Data::format_cart_item
	 */
	public function test_format_cart_item_filter() {
		add_filter( 'edd_cart_item_data', function( $formatted, $download_id, $key, $item ) {
			$formatted['custom_field'] = 'custom_value';
			return $formatted;
		}, 10, 4 );

		$item = array(
			'id'       => self::$download->ID,
			'quantity' => 1,
			'options'  => array(),
		);

		$formatted = Data::format_cart_item( 0, $item );

		$this->assertArrayHasKey( 'custom_field', $formatted );
		$this->assertEquals( 'custom_value', $formatted['custom_field'] );
	}

	/**
	 * Test format_cart_item handles missing quantity.
	 *
	 * @covers \EDD\Cart\Data::format_cart_item
	 */
	public function test_format_cart_item_missing_quantity() {
		$item = array(
			'id'      => self::$download->ID,
			'options' => array(),
		);

		$formatted = Data::format_cart_item( 0, $item );

		$this->assertEquals( 1, $formatted['quantity'] );
	}

	/**
	 * Test format_cart_item with thumbnail.
	 *
	 * @covers \EDD\Cart\Data::format_cart_item
	 */
	public function test_format_cart_item_with_thumbnail() {
		// Create a test image attachment.
		$attachment_id = self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/canola.jpg' );
		set_post_thumbnail( self::$download->ID, $attachment_id );

		$item = array(
			'id'       => self::$download->ID,
			'quantity' => 1,
			'options'  => array(),
		);

		$formatted = Data::format_cart_item( 0, $item );

		$this->assertNotNull( $formatted['thumbnail'] );
		$this->assertStringContainsString( 'canola', $formatted['thumbnail'] );
	}

	/**
	 * Test format_cart_item without thumbnail.
	 *
	 * @covers \EDD\Cart\Data::format_cart_item
	 */
	public function test_format_cart_item_without_thumbnail() {
		$item = array(
			'id'       => self::$download->ID,
			'quantity' => 1,
			'options'  => array(),
		);

		$formatted = Data::format_cart_item( 0, $item );

		$this->assertNull( $formatted['thumbnail'] );
	}
}
