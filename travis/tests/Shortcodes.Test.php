<?php
/**
 * Test Shortcodes
 */

class Test_Easy_Digital_Downloads_Shortcodes extends WP_UnitTestCase {
	protected $_post;

	public function setUp() {
		parent::setUp();

		$post_id = $wp_factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'draft' ) );

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

	public function testShortcodesAreRegistered() {
		global $shortcode_tags;
		$this->assertArrayHasKey('purchase_link', $shortcode_tags);
		$this->assertArrayHasKey('download_history', $shortcode_tags);
		$this->assertArrayHasKey('purchase_history', $shortcode_tags);
		$this->assertArrayHasKey('download_checkout', $shortcode_tags);
		$this->assertArrayHasKey('download_cart', $shortcode_tags);
		$this->assertArrayHasKey('edd_login', $shortcode_tags);
		$this->assertArrayHasKey('download_discounts', $shortcode_tags);
		$this->assertArrayHasKey('purchase_collection', $shortcode_tags);
		$this->assertArrayHasKey('downloads', $shortcode_tags);
		$this->assertArrayHasKey('edd_price', $shortcode_tags);
		$this->assertArrayHasKey('edd_receipt', $shortcode_tags);
		$this->assertArrayHasKey('edd_profile_editor', $shortcode_tags);
	}
}