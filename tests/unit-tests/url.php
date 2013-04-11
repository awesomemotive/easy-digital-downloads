<?php
/**
 * Test URLs
 */

class Test_EDD_URL extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test_ajax_url() {
		$_SERVER['SERVER_PORT'] = 80;
		$this->assertEquals( edd_get_ajax_url(), get_site_url( null, '/wp-admin/admin-ajax.php', 'http' ) );
	}
}