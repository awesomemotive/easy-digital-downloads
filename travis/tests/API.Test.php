<?php
/**
 * Test API
 */

class Test_Easy_Digital_Downloads_API extends WP_UnitTestCase {
	protected $_rewrite = null;

	protected $query = null;

	protected $_post = null;

	public function setUp() {
		parent::setUp();

		global $wp_rewrite, $wp_query;
		$GLOBALS['wp_rewrite']->init();
		flush_rewrite_rules();

		EDD()->api->add_endpoint($wp_rewrite);

		$this->_rewrite = $wp_rewrite;
		$this->_query = $wp_query;

		/** Create some downloads/sales for the API Tests */
		$wp_factory = new WP_UnitTest_Factory;
		$post_id = $wp_factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );

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
		$this->assertEquals('edd-api', $this->_rewrite->endpoints[0][1]);
	}

	public function testGetProducts() {
		$out = EDD()->api->get_products();
		$this->assertArrayHasKey('id', $out['products'][0]['info']);
		$this->assertArrayHasKey('slug', $out['products'][0]['info']);
		$this->assertArrayHasKey('title', $out['products'][0]['info']);
		$this->assertArrayHasKey('create_date', $out['products'][0]['info']);
		$this->assertArrayHasKey('modified_date', $out['products'][0]['info']);
		$this->assertArrayHasKey('status', $out['products'][0]['info']);
		$this->assertArrayHasKey('link', $out['products'][0]['info']);
		$this->assertArrayHasKey('content', $out['products'][0]['info']);
		$this->assertArrayHasKey('thumbnail', $out['products'][0]['info']);

		$this->assertEquals($this->_post->ID, $out['products'][0]['info']['id']);
		$this->assertEquals('test-download', $out['products'][0]['info']['slug']);
		$this->assertEquals('Test Download', $out['products'][0]['info']['title']);
		$this->assertEquals('publish', $out['products'][0]['info']['status']);
		$this->assertEquals('http://example.org/downloads/test-download/', $out['products'][0]['info']['link']);
		$this->assertEquals('Post content 1', $out['products'][0]['info']['content']);
		$this->assertEquals('', $out['products'][0]['info']['thumbnail']);
		//print_r(EDD()->api->get_products());
	}
}