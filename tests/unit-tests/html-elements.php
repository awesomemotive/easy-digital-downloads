<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_html
 */
class Test_HTML_Elements extends EDD_UnitTestCase {
	protected $_post_id = null;

	public function setUp() {
		parent::setUp();

		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );
		$this->_post_id = $post_id;
	}

	public function test_product_dropdown() {
		$expected = '<select name="edd_products" id="edd_products" class="edd-select edd_products"><option value="-1">None</option><option value="'. $this->_post_id .'">Test Download</option></select>';
		$this->assertEquals( $expected, EDD()->html->product_dropdown() );
	}


}
