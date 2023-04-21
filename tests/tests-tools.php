<?php
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * @group edd_tools
 */
class Tests_Tools extends EDD_UnitTestCase {

	public function setup(): void {
		parent::setUp();
		require_once EDD_PLUGIN_DIR . 'includes/admin/tools.php';
	}

	public function tearDown(): void {
		parent::tearDown();
	}

	public function test_system_info() {
		$system_info = edd_tools_sysinfo_get();
		$this->assertStringContainsString( 'Site URL:                 ' . site_url()                       , $system_info );
		$this->assertStringContainsString( 'Home URL:                 ' . home_url()                       , $system_info );
		$this->assertStringContainsString( 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ), $system_info );

		$this->assertStringContainsString( 'Host:', $system_info );
		$this->assertStringContainsString( 'Version:                  ' . get_bloginfo( 'version' ), $system_info );
		$this->assertStringContainsString( 'Language', $system_info );
		$this->assertStringContainsString( 'Permalink Structure', $system_info );
		$this->assertStringContainsString( 'Active Theme', $system_info );
		$this->assertStringContainsString( 'Show On Front', $system_info );

		$this->assertStringContainsString( 'Remote Post', $system_info );
		$this->assertStringContainsString( 'Table Prefix', $system_info );
		$this->assertStringContainsString( 'WP_DEBUG', $system_info );
		$this->assertStringContainsString( 'Memory Limit', $system_info );
		$this->assertStringContainsString( 'Registered Post Stati', $system_info );
		$this->assertStringContainsString( 'Upgraded From', $system_info );
		$this->assertStringContainsString( 'Test Mode', $system_info );
		$this->assertStringContainsString( 'AJAX', $system_info );
		$this->assertStringContainsString( 'Guest Checkout', $system_info );
		$this->assertStringContainsString( 'Symlinks', $system_info );
		$this->assertStringContainsString( 'Download Method', $system_info );
		$this->assertStringContainsString( 'Currency Code', $system_info );
		$this->assertStringContainsString( 'Currency Position', $system_info );
		$this->assertStringContainsString( 'Decimal Separator', $system_info );
		$this->assertStringContainsString( 'Thousands Separator', $system_info );
		$this->assertStringContainsString( 'Upgrades Completed', $system_info );
		$this->assertStringContainsString( 'Checkout', $system_info );
		$this->assertStringContainsString( 'Checkout Page', $system_info );
		$this->assertStringContainsString( 'Success Page', $system_info );
		$this->assertStringContainsString( 'Failure Page', $system_info );
		$this->assertStringContainsString( 'Downloads Slug', $system_info );
		$this->assertStringContainsString( 'Taxes', $system_info );
		$this->assertStringContainsString( 'Default Rate', $system_info );
		$this->assertStringContainsString( 'Display On Checkout', $system_info );
		$this->assertStringContainsString( 'Prices Include Tax', $system_info );
		$this->assertStringContainsString( 'PHP Version', $system_info );
		$this->assertStringContainsString( 'MySQL Version', $system_info );
		$this->assertStringContainsString( 'Webserver Info', $system_info );
		$this->assertStringContainsString( 'Memory Limit', $system_info );
		$this->assertStringContainsString( 'Upload Max Size', $system_info );
		$this->assertStringContainsString( 'Post Max Size', $system_info );
		$this->assertStringContainsString( 'Upload Max Filesize', $system_info );
		$this->assertStringContainsString( 'Time Limit', $system_info );
		$this->assertStringContainsString( 'Max Input Vars', $system_info );
		$this->assertStringContainsString( 'Display Errors', $system_info );
		$this->assertStringContainsString( 'PHP Arg Separator', $system_info );
		$this->assertStringContainsString( 'cURL', $system_info );
		$this->assertStringContainsString( 'fsockopen', $system_info );
		$this->assertStringContainsString( 'SOAP Client', $system_info );
		$this->assertStringContainsString( 'Suhosin', $system_info );
		$this->assertStringContainsString( 'EDD Use Sessions', $system_info );
		$this->assertStringContainsString( 'Session', $system_info );
	}
}
