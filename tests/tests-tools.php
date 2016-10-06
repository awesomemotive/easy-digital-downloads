<?php


/**
 * @group edd_tools
 */
class Tests_Tools extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		require_once EDD_PLUGIN_DIR . 'includes/admin/tools.php';
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_system_info() {
		$system_info = edd_tools_sysinfo_get();
		$this->assertContains( 'Site URL:                 ' . site_url()                       , $system_info );
		$this->assertContains( 'Home URL:                 ' . home_url()                       , $system_info );
		$this->assertContains( 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ), $system_info );

		$this->assertContains( 'Host:', $system_info );
		$this->assertContains( 'Version:                  ' . get_bloginfo( 'version' ), $system_info );
		$this->assertContains( 'Language', $system_info );
		$this->assertContains( 'Permalink Structure', $system_info );
		$this->assertContains( 'Active Theme', $system_info );
		$this->assertContains( 'Show On Front', $system_info );

		$this->assertContains( 'Remote Post', $system_info );
		$this->assertContains( 'Table Prefix', $system_info );
		$this->assertContains( 'WP_DEBUG', $system_info );
		$this->assertContains( 'Memory Limit', $system_info );
		$this->assertContains( 'Registered Post Stati', $system_info );
		$this->assertContains( 'Upgraded From', $system_info );
		$this->assertContains( 'Test Mode', $system_info );
		$this->assertContains( 'AJAX', $system_info );
		$this->assertContains( 'Guest Checkout', $system_info );
		$this->assertContains( 'Symlinks', $system_info );
		$this->assertContains( 'Download Method', $system_info );
		$this->assertContains( 'Currency Code', $system_info );
		$this->assertContains( 'Currency Position', $system_info );
		$this->assertContains( 'Decimal Separator', $system_info );
		$this->assertContains( 'Thousands Separator', $system_info );
		$this->assertContains( 'Upgrades Completed', $system_info );
		$this->assertContains( 'Checkout', $system_info );
		$this->assertContains( 'Checkout Page', $system_info );
		$this->assertContains( 'Success Page', $system_info );
		$this->assertContains( 'Failure Page', $system_info );
		$this->assertContains( 'Downloads Slug', $system_info );
		$this->assertContains( 'Taxes', $system_info );
		$this->assertContains( 'Tax Rate', $system_info );
		$this->assertContains( 'Display On Checkout', $system_info );
		$this->assertContains( 'Prices Include Tax', $system_info );
		$this->assertContains( 'PHP Version', $system_info );
		$this->assertContains( 'MySQL Version', $system_info );
		$this->assertContains( 'Webserver Info', $system_info );
		$this->assertContains( 'Safe Mode', $system_info );
		$this->assertContains( 'Memory Limit', $system_info );
		$this->assertContains( 'Upload Max Size', $system_info );
		$this->assertContains( 'Post Max Size', $system_info );
		$this->assertContains( 'Upload Max Filesize', $system_info );
		$this->assertContains( 'Time Limit', $system_info );
		$this->assertContains( 'Max Input Vars', $system_info );
		$this->assertContains( 'Display Errors', $system_info );
		$this->assertContains( 'PHP Arg Separator', $system_info );
		$this->assertContains( 'cURL', $system_info );
		$this->assertContains( 'fsockopen', $system_info );
		$this->assertContains( 'SOAP Client', $system_info );
		$this->assertContains( 'Suhosin', $system_info );
		$this->assertContains( 'EDD Use Sessions', $system_info );
		$this->assertContains( 'Session', $system_info );
	}
}
