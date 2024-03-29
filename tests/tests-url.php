<?php
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * @group edd_url
 */
class Tests_URL extends EDD_UnitTestCase {
	public function test_ajax_url() {
		$_SERVER['SERVER_PORT'] = 80;
		$_SERVER['HTTPS'] = 'off';

		$this->assertEquals( edd_get_ajax_url(), get_site_url( null, '/wp-admin/admin-ajax.php', 'http' ) );
	}

	public function test_current_page_url() {
		$_SERVER['SERVER_PORT'] = 80;
		$_SERVER["SERVER_NAME"] = 'example.org';
		$this->assertEquals( 'http://example.org/', edd_get_current_page_url() );
	}
}
