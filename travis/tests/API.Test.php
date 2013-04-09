<?php
/**
 * Test API
 */

class Test_Easy_Digital_Downloads_API extends WP_UnitTestCase {
	protected $_rewrite = null;

	public function setUp() {
		parent::setUp();

		global $wp_rewrite;
		$GLOBALS['wp_rewrite']->init();
		flush_rewrite_rules();

		EDD()->api->add_endpoint($wp_rewrite);

		$this->_rewrite = $wp_rewrite;
	}

	public function testEndpoints() {
		$this->assertEquals('edd-api', $this->_rewrite->endpoints[0][1]);
	}
}