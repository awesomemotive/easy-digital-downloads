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
class Init_Tests extends EDD_UnitTestCase {

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
			// Functions.
			array( $reports_dir . 'reports-functions.php' ),

			// Exceptions.
			array( $reports_dir . 'exceptions/class-invalid-parameter.php' ),
			array( $reports_dir . 'exceptions/class-invalid-view.php' ),
			array( $reports_dir . 'exceptions/class-invalid-view-parameter.php' ),

			// Dependencies.
			array( $reports_dir . 'class-registry.php' ),
			array( $reports_dir . 'data/class-base-object.php' ),

			// Reports.
			array( $reports_dir . 'data/class-report-registry.php' ),
			array( $reports_dir . 'data/class-report.php' ),

			// Endpoints.
			array( $reports_dir . 'data/class-endpoint.php' ),
			array( $reports_dir . 'data/class-tile-endpoint.php' ),
			array( $reports_dir . 'data/class-table-endpoint.php' ),
			array( $reports_dir . 'data/class-chart-endpoint.php' ),
			array( $reports_dir . 'data/class-endpoint-registry.php' ),

			// Chart Dependencies.
			array( $reports_dir . 'data/charts/v2/class-manifest.php' ),
			array( $reports_dir . 'data/charts/v2/class-dataset.php' ),
			array( $reports_dir . 'data/charts/v2/class-bar-dataset.php' ),
			array( $reports_dir . 'data/charts/v2/class-line-dataset.php' ),
			array( $reports_dir . 'data/charts/v2/class-pie-dataset.php' ),
		);
	}
}
