<?php

/**
 * @group edd_cart
 */
class Test_Cart extends WP_UnitTestCase {
	protected $_rewrite = null;

	protected $_post = null;
	protected $_discount = null;

	public function setUp() {
		parent::setUp();

		$this->_user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->_user_id );

		global $wp_rewrite;
		$GLOBALS['wp_rewrite']->init();
		flush_rewrite_rules( false );

		edd_add_rewrite_endpoints($wp_rewrite);

		$this->_rewrite = $wp_rewrite;

		global $current_user;

		$current_user = new WP_User(1);
		$current_user->set_role('administrator');

		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );

		$_variable_pricing = array(
			array(
				'name' => 'Simple',
				'amount' => 20
			),
			array(
				'name' => 'Advanced',
				'amount' => 100
			)
		);

		$_download_files = array(
			array(
				'name' => 'File 1',
				'file' => 'http://localhost/file1.jpg',
				'condition' => 0
			),
			array(
				'name' => 'File 2',
				'file' => 'http://localhost/file2.jpg',
				'condition' => 'all'
			)
		);

		$meta = array(
			'edd_price' => '0.00',
			'_variable_pricing' => 1,
			'_edd_price_options_mode' => 'on',
			'edd_variable_prices' => array_values( $_variable_pricing ),
			'edd_download_files' => array_values( $_download_files ),
			'_edd_download_limit' => 20,
			'_edd_hide_purchase_link' => 1,
			'edd_product_notes' => 'Purchase Notes',
			'_edd_product_type' => 'default',
			'_edd_download_earnings' => 129.43,
			'_edd_download_sales' => 59,
			'_edd_download_limit_override_1' => 1
		);
		foreach( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		$this->_post = get_post( $post_id );

		$discount = array(
			'code' => '20OFF',
			'uses' => 54,
			'max' => 10,
			'name' => '20 Percent Off',
			'type' => 'percent',
			'amount' => '20',
			'start' => '12/12/2010 00:00:00',
			'expiration' => '12/31/2050 00:00:00',
			'min_price' => 128,
			'status' => 'active',
			'product_condition' => 'all'
		);

		$this->_discount = edd_store_discount( $discount );
	}

	public function test_endpoints() {
		$this->assertEquals('edd-add', $this->_rewrite->endpoints[0][1]);
		$this->assertEquals('edd-remove', $this->_rewrite->endpoints[1][1]);
	}

	public function test_add_to_cart() {
		$options = array(
			'price_id' => 0
		);
		$this->assertEquals( 0, edd_add_to_cart( $this->_post->ID, $options ) );
	}

	public function test_add_to_cart_multiple_price_ids() {

		edd_empty_cart();

		$options = array(
			'price_id' => array( 0, 1 )
		);

		edd_add_to_cart( $this->_post->ID, $options );
		$this->assertEquals( 2, count( edd_get_cart_contents() ) );

		edd_empty_cart();

		$options = array(
			'price_id' => '0,1'
		);
		edd_add_to_cart( $this->_post->ID, $options );
		$this->assertEquals( 2, count( edd_get_cart_contents() ) );

	}

	public function test_get_cart_contents() {

		edd_empty_cart();

		$options = array(
			'price_id' => 0
		);
		edd_add_to_cart( $this->_post->ID, $options );

		$expected = array(
			'0' => array(
				'id' => $this->_post->ID,
				'options' => array(
					'price_id' => 0
				),
				'quantity' => 1
			)
		);

		$this->assertEquals($expected, edd_get_cart_contents());
	}

	public function test_get_cart_content_details() {

		edd_empty_cart();

		$options = array(
			'price_id' => 0
		);
		edd_add_to_cart( $this->_post->ID, $options );

		$expected = array(
			'0' => array(
				'name' => 'Test Download',
				'id' => $this->_post->ID,
				'item_number' => array(
					'options' => array(
						'price_id' => '0'
					),
					'id' => $this->_post->ID,
					'quantity' => 1,
				),
				'item_price' => '20.0',
				'quantity' => 1,
				'discount' => '0.0',
				'subtotal' => '20.0',
				'tax' => 0,
				'fees' => array(),
				'price' => '20.0'
			)
		);

		$this->assertEquals( $expected, edd_get_cart_content_details() );

		// Now set a discount and test again
		edd_set_cart_discount( '20OFF' );

		$expected = array(
			'0' => array(
				'name' => 'Test Download',
				'id' => $this->_post->ID,
				'item_number' => array(
					'options' => array(
						'price_id' => '0'
					),
					'id' => $this->_post->ID,
					'quantity' => 1,
				),
				'item_price' => '20.0',
				'quantity' => 1,
				'discount' => '4.0',
				'subtotal' => '20.0',
				'tax' => 0,
				'fees' => array(),
				'price' => '16.0'
			)
		);

		$this->assertEquals( $expected, edd_get_cart_content_details() );

		// Now turn on taxes and do it again
		add_filter( 'edd_use_taxes', '__return_true' );
		add_filter( 'edd_tax_rate', function() {
			return 0.20;
		} );

		$expected = array(
			'0' => array(
				'name' => 'Test Download',
				'id' => $this->_post->ID,
				'item_number' => array(
					'options' => array(
						'price_id' => '0'
					),
					'id' => $this->_post->ID,
					'quantity' => 1,
				),
				'item_price' => '20.0',
				'quantity' => 1,
				'discount' => '4.0',
				'subtotal' => '20.0',
				'tax' => '3.2',
				'fees' => array(),
				'price' => '19.2'
			)
		);

		$this->assertEquals( $expected, edd_get_cart_content_details() );

		// Now remove the discount code and test with taxes again
		edd_unset_cart_discount( '20OFF' );

		$expected = array(
			'0' => array(
				'name' => 'Test Download',
				'id' => $this->_post->ID,
				'item_number' => array(
					'options' => array(
						'price_id' => '0'
					),
					'id' => $this->_post->ID,
					'quantity' => 1,
				),
				'item_price' => '20.0',
				'quantity' => 1,
				'discount' => '0.0',
				'subtotal' => '20.0',
				'tax' => '4.0',
				'fees' => array(),
				'price' => '24.0'
			)
		);

		$this->assertEquals( $expected, edd_get_cart_content_details() );

	}

	public function test_get_cart_item_discounted_amount() {

		// Call without any arguements
		$expected = edd_get_cart_item_discount_amount();
		$this->assertEquals( 0.00, $expected );

		// Call with an array but missing 'id'
		$expected = edd_get_cart_item_discount_amount( array( 'foo' => 'bar' ) );
		$this->assertEquals( 0.00, $expected );

		// Now setup a cart and make sure it works
		edd_empty_cart();

		$options = array(
			'price_id' => 0
		);

		edd_add_to_cart( $this->_post->ID, $options );

		// Now set a discount and test again
		edd_set_cart_discount( '20OFF' );

		// Test it without a quantity
		$cart_item_args = array( 'id' => $this->_post->ID );
		$this->assertEquals( 0.00, edd_get_cart_item_discount_amount( $cart_item_args ) );

		// Test it without an options array on an item with variable pricing to make sure we get 0
		$cart_item_args = array( 'id' => $this->_post->ID, 'quantity' => 1 );
		$this->assertEquals( 0.00, edd_get_cart_item_discount_amount( $cart_item_args ) );

		// Now test it with an options array properly set
		$cart_item_args['options'] = $options;
		$this->assertEquals( 4, edd_get_cart_item_discount_amount( $cart_item_args ) );

	}

	public function test_cart_quantity() {
		$this->assertEquals(1, edd_get_cart_quantity());
	}

	public function test_get_cart_item_quantity() {

		$this->markTestIncomplete( 'This fails due to some weird session issue. Works fine on sites, just not in tests. #2294' );

		edd_empty_cart();

		$options = array(
			'price_id' => 0
		);
		edd_add_to_cart( $this->_post->ID, $options );

		$this->assertEquals( 1, edd_get_cart_item_quantity( $this->_post->ID, $options ) );

		// Add the item to the cart again
		edd_add_to_cart( $this->_post->ID, $options );

		$this->assertEquals( 2, edd_get_cart_item_quantity( $this->_post->ID, $options ) );

		// Now add a different price option to the cart
		$options = array(
			'price_id' => 1
		);
		edd_add_to_cart( $this->_post->ID, $options );

		$this->assertEquals( 1, edd_get_cart_item_quantity( $this->_post->ID, $options ) );

	}

	public function test_set_cart_item_quantity() {

		$this->markTestIncomplete( 'This fails due to some weird session issue. Works fine on sites, just not in tests. #2294' );

		$options = array(
			'price_id' => 0
		);

		edd_set_cart_item_quantity( $this->_post->ID, 3, $options );

		$this->assertEquals( 3, edd_get_cart_item_quantity( $this->_post->ID, $options ) );

	}

	public function test_item_in_cart() {
		$this->assertFalse(edd_item_in_cart($this->_post->ID));
	}

	public function test_cart_item_price() {
		$this->assertEquals( '&#36;0.00' , edd_cart_item_price( 0 ) );
	}

	public function test_get_cart_item_price() {
		$this->assertEquals( false , edd_get_cart_item_price( 0 ) );
	}

	public function test_remove_from_cart() {

		edd_empty_cart();

		edd_add_to_cart( $this->_post->ID );

		$expected = array();
		$this->assertEquals( $expected, edd_remove_from_cart( 0 ) );
	}

	public function test_set_purchase_session() {
		$this->assertNull( edd_set_purchase_session() );
	}

	public function test_get_purchase_session() {
		$this->assertEmpty( edd_get_purchase_session() );
	}

	public function test_cart_saving_disabled() {
		$this->assertTrue( edd_is_cart_saving_disabled() );
	}

	public function test_is_cart_saved() {


		// Test for no saved cart
		$this->assertFalse( edd_is_cart_saved() );

		// Create a saved cart then test again
		$cart = array(
			'0' => array(
				'id' => $this->_post->ID,
				'options' => array(
					'price_id' => 0
				),
				'quantity' => 1
			)
		);
		update_user_meta( get_current_user_id(), 'edd_saved_cart', $cart );

		edd_update_option( 'enable_cart_saving', '1' );

		$this->assertTrue( edd_is_cart_saved() );
	}

	public function test_restore_cart() {

		// Create a saved cart
		$saved_cart = array(
			'0' => array(
				'id' => $this->_post->ID,
				'options' => array(
					'price_id' => 0
				),
				'quantity' => 1
			)
		);
		update_user_meta( get_current_user_id(), 'edd_saved_cart', $saved_cart );

		// Set the current cart contents (different from saved)
		$cart = array(
			'0' => array(
				'id' => $this->_post->ID,
				'options' => array(
					'price_id' => 1
				),
				'quantity' => 1
			)
		);
		EDD()->session->set( 'edd_cart', $cart );

		edd_restore_cart();

		$this->assertEquals( edd_get_cart_contents(), $saved_cart );
	}

	public function test_generate_cart_token() {
		$this->assertInternalType( 'int', edd_generate_cart_token() );
	}

	public function test_edd_get_cart_item_name() {

		edd_empty_cart();

		edd_add_to_cart( $this->_post->ID );

		$items = edd_get_cart_content_details();

		$this->assertEquals( 'Test Download - Simple', edd_get_cart_item_name( $items[0] ) );

	}
}
