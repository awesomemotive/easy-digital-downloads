<?php
/**
 * Test API
 */

class Test_Easy_Digital_Downloads_API extends WP_UnitTestCase {
	protected $_rewrite = null;

	protected $query = null;

	public function setUp() {
		parent::setUp();

		global $wp_rewrite, $wp_query;
		$GLOBALS['wp_rewrite']->init();
		flush_rewrite_rules();

		EDD()->api->add_endpoint($wp_rewrite);

		$this->_rewrite = $wp_rewrite;
		$this->_query = $wp_query;
	}

	public function testEndpoints() {
		$this->assertEquals('edd-api', $this->_rewrite->endpoints[0][1]);
	}
}