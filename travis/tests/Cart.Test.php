<?php
/**
 * Test Cart
 */

class Test_Easy_Digital_Downloads_Cart extends WP_UnitTestCase {
	protected $_rewrite = null;

	public function setUp() {
		parent::setUp();

		global $wp_rewrite;
		$GLOBALS['wp_rewrite']->init();
		flush_rewrite_rules();

		edd_add_rewrite_endpoints($wp_rewrite);

		$this->_rewrite = $wp_rewrite;
	}

	public function testEndpoints() {
		$this->assertEquals('edd-add', $this->_rewrite->endpoints[0][1]);
		$this->assertEquals('edd-remove', $this->_rewrite->endpoints[1][1]);
	}
}