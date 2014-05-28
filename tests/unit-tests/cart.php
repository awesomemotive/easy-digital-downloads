<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_cart
 */
class Test_Cart extends EDD_UnitTestCase {
	protected $_rewrite = null;

	protected $_post = null;

	public function setUp() {
		parent::setUp();

		global $wp_rewrite;
		$GLOBALS['wp_rewrite']->init();
		flush_rewrite_rules();

		edd_add_rewrite_endpoints($wp_rewrite);

		$this->_rewrite = $wp_rewrite;

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
	}

	public function test_endpoints() {
		$this->assertEquals('edd-add', $this->_rewrite->endpoints[0][1]);
		$this->assertEquals('edd-remove', $this->_rewrite->endpoints[1][1]);
	}

	public function test_add_to_cart() {
		$options = array(
			'price_id' => 0,
			'name' => 'Simple',
			'amount' => 20,
			'quantity' => 1
		);
		$this->assertEquals( 0, edd_add_to_cart( $this->_post->ID, $options ) );
	}

	public function test_get_cart_contents() {
		$expected = array(
			'0' => array(
				'id' => $this->_post->ID - 1,
				'options' => array(
					'price_id' => 0,
					'name' => 'Simple',
					'amount' => 20
				),
				'quantity' => 1
			)
		);

		$this->assertEquals($expected, edd_get_cart_contents());
	}

	public function test_cart_quantity() {
		$this->assertEquals(1, edd_get_cart_quantity());
	}

	public function test_item_in_cart() {
		$this->assertFalse(edd_item_in_cart($this->_post->ID));
	}

	public function test_cart_item_price() {
		$this->assertEquals( '&#036;0.00' , edd_cart_item_price( 0 ) );
	}

	public function test_remove_from_cart() {
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

		global $edd_options;

		// Test for no saved cart
		$this->assertFalse( edd_is_cart_saved() );

		// Create a saved cart then test again
		$cart = array(
			'0' => array(
				'id' => $this->_post->ID,
				'options' => array(
					'price_id' => 0,
					'name' => 'Simple',
					'amount' => 20,
					'quantity' => 1
				),
				'quantity' => 1
			)
		);
		update_user_meta( get_current_user_id(), 'edd_saved_cart', $cart );
		$edd_options['enable_cart_saving'] = '1';
		update_option( 'edd_settings', $edd_options );

		$this->assertTrue( edd_is_cart_saved() );
	}

	public function test_restore_cart() {

		// Create a saved cart
		$saved_cart = array(
			'0' => array(
				'id' => $this->_post->ID,
				'options' => array(
					'price_id' => 0,
					'name' => 'Simple',
					'amount' => 20,
					'quantity' => 1
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
					'price_id' => 1,
					'name' => 'Advanced',
					'amount' => 30,
					'quantity' => 1
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
}
