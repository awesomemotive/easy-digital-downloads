<?php

/**
 * Test HTML Elements
 */
class Test_HTML_Elements extends WP_UnitTestCase {
	protected $_post_id = null;

	public function setUp() {
		parent::setUp();

		$wp_factory = new WP_UnitTest_Factory;
		$post_id = $wp_factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );
		$this->_post_id = $post_id;
	}

	public function test_product_dropdown() {
		$expected = '<select name="edd_products" id="edd_products"><option value="'. $this->_post_id .'">Test Download</option></select>';
		$this->assertEquals( $expected, EDD()->html->product_dropdown() );
	}
}