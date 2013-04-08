<?php

class Test_Easy_Digital_Downloads_Post_Type extends WP_UnitTestCase {
	protected $_post = null;

	protected $_variable_pricing = null;

	protected $_download_files = null;

	public function setUp() {
		parent::setUp();
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
			'edd_download_files' => array(
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
			'edd_product_notes' => 'Purchase Notes'
		);
		foreach( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		$this->_post = get_post( $post_id );
	}

	public function testGetDownload() {
		$out = edd_get_download($this->_post->ID);

		$this->assertObjectHasAttribute('ID', $out);
		$this->assertObjectHasAttribute('post_title', $out);
		$this->assertObjectHasAttribute('post_type', $out);

		$this->assertEquals($out->post_type, $this->_post->post_type);
	}

	public function testDownloadPrice() {
		$this->assertEquals(0.00, edd_get_download_price($this->_post->ID));
	}

	public function testDownloadVariablePrices() {
		$out = edd_get_variable_prices($this->_post->ID);
		$this->assertNotEmpty($out);
		foreach ($out as $var) {
			$this->assertArrayHasKey('name', $var);
			$this->assertArrayHasKey('amount', $var);

			if ($var['name'] == 'Simple') {
				$this->assertEquals(20, $var['amount']);
			}

			if ($var['name'] == 'Advanced') {
				$this->assertEquals(100, $var['amount']);
			}
		}
	}

	public function testDownloadHasVariablePrices() {
		$this->assertTrue(edd_has_variable_prices($this->_post->ID));
	}

	public function testVariablePriceOptionName() {
		$this->assertEquals('Simple', edd_get_price_option_name($this->_post->ID, 0));
	}
}