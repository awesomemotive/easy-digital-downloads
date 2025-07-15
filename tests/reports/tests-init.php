<?php
namespace EDD\Tests\Reports;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Reports\Init as ReportsInit;
new ReportsInit();

/**
 * Tests for the Reports Init class.
 *
 * @group edd_reports
 * @group edd_bootstrap
 *
 * @coversDefaultClass \EDD\Reports\Init
 */
class Init extends EDD_UnitTestCase {

	/**
	 * @dataProvider _test_bootstrap_dp
	 * @covers ::bootstrap()
	 *
	 * @group edd_includes
	 */
	public function test_bootstrap( $path_to_file ) {
		$this->assertFileExists( $path_to_file );
	}

	/**
	 * Data provider for test_bootstrap method.
	 */
	public function _test_bootstrap_dp() {
		$reports_dir = EDD_PLUGIN_DIR . 'includes/reports/';

		return array(
			array( $reports_dir . 'reports-functions.php' ),
		);
	}

	public function test_reports_class_exists() {
		$this->assertTrue( class_exists( '\EDD\Reports\Init' ) );
	}

	public function test_reports_functions_class_exists() {
		$this->assertTrue( class_exists( '\EDD\Reports\Data\Charts\v2\Dataset' ) );
	}
}
