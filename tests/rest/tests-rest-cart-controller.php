<?php
namespace EDD\Tests\REST;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\REST\Controllers\Cart as REST_Cart_Controller;
use EDD\REST\Security;

/**
 * REST Cart Controller Tests
 *
 * @group edd_rest
 * @group edd_rest_cart
 * @group edd_rest_cart_controller
 */
class CartController extends EDD_UnitTestCase {

	/**
	 * Controller instance.
	 *
	 * @var Controller
	 */
	protected $controller;

	/**
	 * Security instance.
	 *
	 * @var Security
	 */
	protected $security;

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
		// Create a simple download
		$post_id = self::factory()->post->create( array(
			'post_title'  => 'Test Download',
			'post_type'   => 'download',
			'post_status' => 'publish',
		) );

		update_post_meta( $post_id, 'edd_price', '20.00' );
		update_post_meta( $post_id, '_edd_product_type', 'default' );

		self::$download = edd_get_download( $post_id );

		// Create a download with variable pricing
		$post_id_variable = self::factory()->post->create( array(
			'post_title'  => 'Variable Download',
			'post_type'   => 'download',
			'post_status' => 'publish',
		) );

		$_variable_pricing = array(
			array(
				'name'   => 'Simple',
				'amount' => 20,
			),
			array(
				'name'   => 'Advanced',
				'amount' => 100,
			),
		);

		update_post_meta( $post_id_variable, 'edd_price', '0.00' );
		update_post_meta( $post_id_variable, '_variable_pricing', 1 );
		update_post_meta( $post_id_variable, 'edd_variable_prices', array_values( $_variable_pricing ) );
		update_post_meta( $post_id_variable, '_edd_product_type', 'default' );

		self::$download_variable = edd_get_download( $post_id_variable );
	}

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();

		// Clear cart before each test
		edd_empty_cart();

		$this->security   = new Security();
		$this->controller = new REST_Cart_Controller( $this->security );
	}

	/**
	 * Tear down after each test.
	 */
	public function tearDown(): void {
		edd_empty_cart();
		parent::tearDown();
	}

	/**
	 * Test that controller has security dependency.
	 *
	 * @covers \EDD\REST\Controllers\Cart::__construct
	 */
	public function test_controller_has_security_dependency() {
		$reflection = new \ReflectionClass( $this->controller );
		$property = $reflection->getProperty( 'security' );
		$property->setAccessible( true );

		$this->assertInstanceOf( 'EDD\REST\Security', $property->getValue( $this->controller ) );
	}

	/**
	 * Test add_item with valid download.
	 *
	 * @covers \EDD\REST\Controllers\Cart::add_item
	 */
	public function test_add_item_with_valid_download() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/add' );
		$request->set_param( 'download_id', self::$download->ID );

		$response = $this->controller->add_item( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
		$this->assertArrayHasKey( 'addedToCart', $data );
		$this->assertArrayHasKey( 'cart', $data );
		$this->assertArrayHasKey( 'item_position', $data );
		$this->assertArrayHasKey( 'token', $data );
		$this->assertArrayHasKey( 'timestamp', $data );

		// Verify item is in cart
		$cart_contents = edd_get_cart_contents();
		$this->assertCount( 1, $cart_contents );
	}

	/**
	 * Test add_item with variable pricing.
	 *
	 * @covers \EDD\REST\Controllers\Cart::add_item
	 */
	public function test_add_item_with_variable_pricing() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/add' );
		$request->set_param( 'download_id', self::$download_variable->ID );
		$request->set_param( 'price_id', 0 );

		$response = $this->controller->add_item( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );

		// Verify item is in cart with correct price_id
		$cart_contents = edd_get_cart_contents();
		$this->assertCount( 1, $cart_contents );
		$this->assertEquals( 0, $cart_contents[0]['options']['price_id'] );
	}

	/**
	 * Test add_item with quantity.
	 *
	 * @covers \EDD\REST\Controllers\Cart::add_item
	 */
	public function test_add_item_with_quantity() {
		// Enable quantities
		edd_update_option( 'item_quantities', true );

		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/add' );
		$request->set_param( 'download_id', self::$download->ID );
		$request->set_param( 'quantity', 3 );

		$response = $this->controller->add_item( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );

		// Verify quantity in cart
		$cart_contents = edd_get_cart_contents();
		$this->assertEquals( 3, $cart_contents[0]['quantity'] );

		// Clean up
		edd_update_option( 'item_quantities', false );
	}

	/**
	 * Test add_item with invalid download ID returns error.
	 *
	 * @covers \EDD\REST\Controllers\Cart::add_item
	 */
	public function test_add_item_with_invalid_download() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/add' );
		$request->set_param( 'download_id', 999999 );

		// Since validation happens at route level, we test the validator directly
		$result = $this->controller->validate_download_id( 999999, $request, 'download_id' );

		$this->assertWPError( $result );
		$this->assertEquals( 'invalid_download', $result->get_error_code() );
	}

	/**
	 * Test validate_download_id with valid download.
	 *
	 * @covers \EDD\REST\Controllers\Cart::validate_download_id
	 */
	public function test_validate_download_id_with_valid_download() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/add' );

		$result = $this->controller->validate_download_id( self::$download->ID, $request, 'download_id' );

		$this->assertTrue( $result );
	}

	/**
	 * Test validate_download_id with non-existent download.
	 *
	 * @covers \EDD\REST\Controllers\Cart::validate_download_id
	 */
	public function test_validate_download_id_with_nonexistent_download() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/add' );

		$result = $this->controller->validate_download_id( 999999, $request, 'download_id' );

		$this->assertWPError( $result );
		$this->assertEquals( 'invalid_download', $result->get_error_code() );
		$this->assertEquals( 400, $result->get_error_data()['status'] );
	}

	/**
	 * Test remove_item removes item from cart.
	 *
	 * @covers \EDD\REST\Controllers\Cart::remove_item
	 */
	public function test_remove_item() {
		// Add item to cart first
		$cart_key = edd_add_to_cart( self::$download->ID );

		$this->assertNotFalse( $cart_key );
		$this->assertCount( 1, edd_get_cart_contents() );

		// Remove item
		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/remove' );
		$request->set_param( 'cart_key', $cart_key );

		$response = $this->controller->remove_item( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
		$this->assertArrayHasKey( 'removedFromCart', $data );
		$this->assertArrayHasKey( 'cart', $data );
		$this->assertArrayHasKey( 'token', $data );
		$this->assertArrayHasKey( 'timestamp', $data );

		// Verify cart is empty
		$this->assertCount( 0, edd_get_cart_contents() );
	}

	/**
	 * Test remove_item with invalid cart key.
	 *
	 * @covers \EDD\REST\Controllers\Cart::remove_item
	 */
	public function test_remove_item_with_invalid_cart_key() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/remove' );
		$request->set_param( 'cart_key', 999 );

		$response = $this->controller->remove_item( $request );

		$this->assertWPError( $response );
		$this->assertEquals( 'item_not_found', $response->get_error_code() );
		$this->assertEquals( 404, $response->get_error_data()['status'] );
	}

	/**
	 * Test update_quantity updates item quantity.
	 *
	 * @covers \EDD\REST\Controllers\Cart::update_quantity
	 */
	public function test_update_quantity() {
		// Enable quantities
		edd_update_option( 'item_quantities', true );

		// Add item to cart
		$cart_key = edd_add_to_cart( self::$download->ID, array( 'quantity' => 1 ) );

		$this->assertNotFalse( $cart_key );

		// Update quantity
		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/update-quantity' );
		$request->set_param( 'cart_key', $cart_key );
		$request->set_param( 'quantity', 5 );

		$response = $this->controller->update_quantity( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
		$this->assertArrayHasKey( 'message', $data );
		$this->assertArrayHasKey( 'cart', $data );

		// Verify quantity updated
		$cart_contents = edd_get_cart_contents();
		$this->assertEquals( 5, $cart_contents[ $cart_key ]['quantity'] );

		// Clean up
		edd_update_option( 'item_quantities', false );
	}

	/**
	 * Test update_quantity when quantities disabled.
	 *
	 * @covers \EDD\REST\Controllers\Cart::update_quantity
	 */
	public function test_update_quantity_when_quantities_disabled() {
		// Ensure quantities are disabled
		edd_update_option( 'item_quantities', false );

		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/update-quantity' );
		$request->set_param( 'cart_key', 0 );
		$request->set_param( 'quantity', 5 );

		$response = $this->controller->update_quantity( $request );

		$this->assertWPError( $response );
		$this->assertEquals( 'quantities_disabled', $response->get_error_code() );
		$this->assertEquals( 400, $response->get_error_data()['status'] );
	}

	/**
	 * Test update_quantity with zero removes item.
	 *
	 * @covers \EDD\REST\Controllers\Cart::update_quantity
	 */
	public function test_update_quantity_zero_removes_item() {
		// Enable quantities
		edd_update_option( 'item_quantities', true );

		// Add item to cart
		$cart_key = edd_add_to_cart( self::$download->ID, array( 'quantity' => 2 ) );

		$this->assertNotFalse( $cart_key );
		$this->assertCount( 1, edd_get_cart_contents() );

		// Update quantity to 0
		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/update-quantity' );
		$request->set_param( 'cart_key', $cart_key );
		$request->set_param( 'quantity', 0 );

		$response = $this->controller->update_quantity( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );

		// Verify item removed
		$this->assertCount( 0, edd_get_cart_contents() );

		// Clean up
		edd_update_option( 'item_quantities', false );
	}

	/**
	 * Test update_quantity with invalid cart key.
	 *
	 * @covers \EDD\REST\Controllers\Cart::update_quantity
	 */
	public function test_update_quantity_with_invalid_cart_key() {
		// Enable quantities
		edd_update_option( 'item_quantities', true );

		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/update-quantity' );
		$request->set_param( 'cart_key', 999 );
		$request->set_param( 'quantity', 5 );

		$response = $this->controller->update_quantity( $request );

		$this->assertWPError( $response );
		$this->assertEquals( 'item_not_found', $response->get_error_code() );
		$this->assertEquals( 404, $response->get_error_data()['status'] );

		// Clean up
		edd_update_option( 'item_quantities', false );
	}

	/**
	 * Test validate_quantity with valid positive number.
	 *
	 * @covers \EDD\REST\Controllers\Cart::validate_quantity
	 */
	public function test_validate_quantity_with_positive_number() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/update-quantity' );

		$result = $this->controller->validate_quantity( 5, $request, 'quantity' );

		$this->assertTrue( $result );
	}

	/**
	 * Test validate_quantity with zero.
	 *
	 * @covers \EDD\REST\Controllers\Cart::validate_quantity
	 */
	public function test_validate_quantity_with_zero() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/update-quantity' );

		$result = $this->controller->validate_quantity( 0, $request, 'quantity' );

		$this->assertTrue( $result );
	}

	/**
	 * Test validate_quantity with negative number returns error.
	 *
	 * @covers \EDD\REST\Controllers\Cart::validate_quantity
	 */
	public function test_validate_quantity_with_negative_number() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/update-quantity' );

		$result = $this->controller->validate_quantity( -5, $request, 'quantity' );

		$this->assertWPError( $result );
		$this->assertEquals( 'invalid_quantity', $result->get_error_code() );
		$this->assertEquals( 400, $result->get_error_data()['status'] );
	}

	/**
	 * Test validate_quantity with non-numeric value returns error.
	 *
	 * @covers \EDD\REST\Controllers\Cart::validate_quantity
	 */
	public function test_validate_quantity_with_non_numeric() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/update-quantity' );

		$result = $this->controller->validate_quantity( 'abc', $request, 'quantity' );

		$this->assertWPError( $result );
		$this->assertEquals( 'invalid_quantity', $result->get_error_code() );
	}

	/**
	 * Test get_contents returns cart data.
	 *
	 * @covers \EDD\REST\Controllers\Cart::get_contents
	 */
	public function test_get_contents() {
		// Add items to cart
		edd_add_to_cart( self::$download->ID );

		$request = new \WP_REST_Request( 'GET', '/edd/v3/cart/contents' );

		$response = $this->controller->get_contents( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );
		$this->assertArrayHasKey( 'subtotal', $data );
		$this->assertArrayHasKey( 'token', $data );
		$this->assertArrayHasKey( 'timestamp', $data );

		$this->assertCount( 1, $data['items'] );
	}

	/**
	 * Test get_contents with empty cart.
	 *
	 * @covers \EDD\REST\Controllers\Cart::get_contents
	 */
	public function test_get_contents_with_empty_cart() {
		$request = new \WP_REST_Request( 'GET', '/edd/v3/cart/contents' );

		$response = $this->controller->get_contents( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );
		$this->assertCount( 0, $data['items'] );
	}

	/**
	 * Test get_token returns token and timestamp.
	 *
	 * @covers \EDD\REST\Controllers\Cart::get_token
	 */
	public function test_get_token() {
		$request = new \WP_REST_Request( 'GET', '/edd/v3/cart/token' );

		$response = $this->controller->get_token( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'token', $data );
		$this->assertIsString( $data['token'] );
		$this->assertNotEmpty( $data['token'] );
	}

	/**
	 * Test that action hook fires when item added.
	 *
	 * @covers \EDD\REST\Controllers\Cart::add_item
	 */
	public function test_add_item_fires_action() {
		$action_fired = false;

		add_action( 'edd_rest_cart_item_added', function() use ( &$action_fired ) {
			$action_fired = true;
		} );

		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/add' );
		$request->set_param( 'download_id', self::$download->ID );

		$this->controller->add_item( $request );

		$this->assertTrue( $action_fired );
	}

	/**
	 * Test that action hook fires when item removed.
	 *
	 * @covers \EDD\REST\Controllers\Cart::remove_item
	 */
	public function test_remove_item_fires_action() {
		$action_fired = false;

		$cart_key = edd_add_to_cart( self::$download->ID );

		add_action( 'edd_rest_cart_item_removed', function() use ( &$action_fired ) {
			$action_fired = true;
		} );

		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/remove' );
		$request->set_param( 'cart_key', $cart_key );

		$this->controller->remove_item( $request );

		$this->assertTrue( $action_fired );
	}

	/**
	 * Test that action hook fires when quantity updated.
	 *
	 * @covers \EDD\REST\Controllers\Cart::update_quantity
	 */
	public function test_update_quantity_fires_action() {
		$action_fired = false;

		// Enable quantities
		edd_update_option( 'item_quantities', true );

		$cart_key = edd_add_to_cart( self::$download->ID, array( 'quantity' => 1 ) );

		add_action( 'edd_rest_cart_quantity_updated', function() use ( &$action_fired ) {
			$action_fired = true;
		} );

		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/update-quantity' );
		$request->set_param( 'cart_key', $cart_key );
		$request->set_param( 'quantity', 3 );

		$this->controller->update_quantity( $request );

		$this->assertTrue( $action_fired );

		// Clean up
		edd_update_option( 'item_quantities', false );
	}

	/**
	 * Test add_item with custom options.
	 *
	 * @covers \EDD\REST\Controllers\Cart::add_item
	 * @covers \EDD\REST\Controllers\Cart::sanitize_options
	 */
	public function test_add_item_with_custom_options() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/add' );
		$request->set_param( 'download_id', self::$download->ID );
		$request->set_param( 'options', array(
			'custom_field' => 'custom_value',
			'number_field' => 42,
			'bool_field'   => true,
		) );

		$response = $this->controller->add_item( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );

		// Verify custom options are in cart
		$cart_contents = edd_get_cart_contents();
		$this->assertArrayHasKey( 'custom_field', $cart_contents[0]['options'] );
		$this->assertEquals( 'custom_value', $cart_contents[0]['options']['custom_field'] );
		$this->assertArrayHasKey( 'number_field', $cart_contents[0]['options'] );
		$this->assertEquals( 42, $cart_contents[0]['options']['number_field'] );
		$this->assertArrayHasKey( 'bool_field', $cart_contents[0]['options'] );
		$this->assertTrue( $cart_contents[0]['options']['bool_field'] );
	}

	/**
	 * Test add_item with options and price_id both set.
	 *
	 * @covers \EDD\REST\Controllers\Cart::add_item
	 */
	public function test_add_item_with_options_and_price_id() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/add' );
		$request->set_param( 'download_id', self::$download_variable->ID );
		$request->set_param( 'price_id', 0 );
		$request->set_param( 'options', array(
			'custom_field' => 'test_value',
		) );

		$response = $this->controller->add_item( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );

		// Verify both price_id and custom options are in cart
		$cart_contents = edd_get_cart_contents();
		$this->assertEquals( 0, $cart_contents[0]['options']['price_id'] );
		$this->assertEquals( 'test_value', $cart_contents[0]['options']['custom_field'] );
	}

	/**
	 * Test sanitize_options filters out protected keys.
	 *
	 * @covers \EDD\REST\Controllers\Cart::sanitize_options
	 */
	public function test_sanitize_options_filters_protected_keys() {
		$options = array(
			'custom_field' => 'allowed',
			'price_id'     => 999,
			'quantity'     => 999,
			'id'           => 999,
			'hash'         => 'hacked',
		);

		$sanitized = $this->controller->sanitize_options( $options );

		// Custom field should be allowed
		$this->assertArrayHasKey( 'custom_field', $sanitized );
		$this->assertEquals( 'allowed', $sanitized['custom_field'] );

		// Protected keys should be filtered out
		$this->assertArrayNotHasKey( 'price_id', $sanitized );
		$this->assertArrayNotHasKey( 'quantity', $sanitized );
		$this->assertArrayNotHasKey( 'id', $sanitized );
		$this->assertArrayNotHasKey( 'hash', $sanitized );
	}

	/**
	 * Test sanitize_options with different data types.
	 *
	 * @covers \EDD\REST\Controllers\Cart::sanitize_options
	 */
	public function test_sanitize_options_sanitizes_different_types() {
		$options = array(
			'string_field'  => 'test value',
			'int_field'     => 42,
			'bool_field'    => true,
			'string_int'    => '123',
		);

		$sanitized = $this->controller->sanitize_options( $options );

		// String should be sanitized
		$this->assertIsString( $sanitized['string_field'] );
		$this->assertEquals( 'test value', $sanitized['string_field'] );

		// Int should remain int
		$this->assertIsInt( $sanitized['int_field'] );
		$this->assertEquals( 42, $sanitized['int_field'] );

		// Bool should remain bool
		$this->assertIsBool( $sanitized['bool_field'] );
		$this->assertTrue( $sanitized['bool_field'] );

		// String that looks like int should be treated as string
		$this->assertIsString( $sanitized['string_int'] );
		$this->assertEquals( '123', $sanitized['string_int'] );
	}

	/**
	 * Test sanitize_options filters out non-scalar values.
	 *
	 * @covers \EDD\REST\Controllers\Cart::sanitize_options
	 */
	public function test_sanitize_options_filters_non_scalar_values() {
		$options = array(
			'string_field' => 'allowed',
			'array_field'  => array( 'not', 'allowed' ),
			'object_field' => (object) array( 'not' => 'allowed' ),
		);

		$sanitized = $this->controller->sanitize_options( $options );

		// String should be allowed
		$this->assertArrayHasKey( 'string_field', $sanitized );

		// Array and object should be filtered out
		$this->assertArrayNotHasKey( 'array_field', $sanitized );
		$this->assertArrayNotHasKey( 'object_field', $sanitized );
	}

	/**
	 * Test sanitize_options returns empty array for non-array input.
	 *
	 * @covers \EDD\REST\Controllers\Cart::sanitize_options
	 */
	public function test_sanitize_options_with_non_array_input() {
		$result = $this->controller->sanitize_options( 'not an array' );
		$this->assertIsArray( $result );
		$this->assertEmpty( $result );

		$result = $this->controller->sanitize_options( 123 );
		$this->assertIsArray( $result );
		$this->assertEmpty( $result );

		$result = $this->controller->sanitize_options( null );
		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}

	/**
	 * Test sanitize_options sanitizes keys.
	 *
	 * @covers \EDD\REST\Controllers\Cart::sanitize_options
	 */
	public function test_sanitize_options_sanitizes_keys() {
		$options = array(
			'valid_key'      => 'value1',
			'Invalid Key!'   => 'value2',
			'key-with-dash'  => 'value3',
		);

		$sanitized = $this->controller->sanitize_options( $options );

		// Valid key should remain
		$this->assertArrayHasKey( 'valid_key', $sanitized );

		// Invalid key should be sanitized
		$this->assertArrayHasKey( 'invalidkey', $sanitized );
		$this->assertEquals( 'value2', $sanitized['invalidkey'] );

		// Key with dash should remain (dashes are valid)
		$this->assertArrayHasKey( 'key-with-dash', $sanitized );
		$this->assertEquals( 'value3', $sanitized['key-with-dash'] );
	}

	/**
	 * Test that protected keys filter is applied.
	 *
	 * @covers \EDD\REST\Controllers\Cart::sanitize_options
	 */
	public function test_sanitize_options_protected_keys_filter() {
		$options = array(
			'custom_field'    => 'allowed',
			'special_field'   => 'normally allowed',
		);

		// Add filter to protect additional key
		add_filter( 'edd_rest_cart_protected_option_keys', function( $keys ) {
			$keys[] = 'special_field';
			return $keys;
		} );

		$sanitized = $this->controller->sanitize_options( $options );

		// Custom field should still be allowed
		$this->assertArrayHasKey( 'custom_field', $sanitized );

		// Special field should now be filtered out
		$this->assertArrayNotHasKey( 'special_field', $sanitized );

		// Clean up
		remove_all_filters( 'edd_rest_cart_protected_option_keys' );
	}

	/**
	 * Test add_item cannot override price_id via options parameter.
	 *
	 * @covers \EDD\REST\Controllers\Cart::add_item
	 */
	public function test_add_item_cannot_override_price_id_via_options() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/add' );
		$request->set_param( 'download_id', self::$download_variable->ID );
		$request->set_param( 'price_id', 0 );
		$request->set_param( 'options', array(
			'price_id' => 1, // Attempt to override
		) );

		$response = $this->controller->add_item( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );

		// Verify price_id from the direct parameter is used, not from options
		$cart_contents = edd_get_cart_contents();
		$this->assertEquals( 0, $cart_contents[0]['options']['price_id'] );
	}

	/**
	 * Test add_item cannot override quantity via options parameter.
	 *
	 * @covers \EDD\REST\Controllers\Cart::add_item
	 */
	public function test_add_item_cannot_override_quantity_via_options() {
		// Enable quantities
		edd_update_option( 'item_quantities', true );

		$request = new \WP_REST_Request( 'POST', '/edd/v3/cart/add' );
		$request->set_param( 'download_id', self::$download->ID );
		$request->set_param( 'quantity', 3 );
		$request->set_param( 'options', array(
			'quantity' => 10, // Attempt to override
		) );

		$response = $this->controller->add_item( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );

		// Verify quantity from the direct parameter is used, not from options
		$cart_contents = edd_get_cart_contents();
		$this->assertEquals( 3, $cart_contents[0]['quantity'] );

		// Clean up
		edd_update_option( 'item_quantities', false );
	}
}
