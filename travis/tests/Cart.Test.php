<?php
/**
 * Test Cart
 */

class Test_Easy_Digital_Downloads_Cart extends WP_UnitTestCase {
	protected $_rewrite = null;

	protected $_post = null;

	public function setUp() {
		parent::setUp();

		global $wp_rewrite;
		$GLOBALS['wp_rewrite']->init();
		flush_rewrite_rules();

		edd_add_rewrite_endpoints($wp_rewrite);

		$this->_rewrite = $wp_rewrite;

		$wp_factory = new WP_UnitTest_Factory;
		$post_id = $wp_factory->post->create( array( 'title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'draft' ) );

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

	public function testEndpoints() {
		$this->assertEquals('edd-add', $this->_rewrite->endpoints[0][1]);
		$this->assertEquals('edd-remove', $this->_rewrite->endpoints[1][1]);
	}

	public function testAddToCart() {
		$this->assertEquals(0, edd_add_to_cart($this->_post->ID));
	}

	public function testGetCartContents() {
		$expected = array(
			'0' => array(
				'id' => $this->_post->ID - 1,
				'options' => array(
					'price_id' => 0
				)
			)
		);

		$this->assertEquals($expected, edd_get_cart_contents());
	}

	public function testCartQuantity() {
		$this->assertEquals(1, edd_get_cart_quantity());
	}

	public function testRemoveFromCart() {
		$expected = array();
		$this->assertEquals($expected, edd_remove_from_cart(0) );
	}
}