<?php
namespace EDD\Reports );

if ( ! class_exists( '\EDD\Reports\Init' ) ) {
	require_once( EDD_PLUGIN_DIR . 'includes/reports/class-init.php' ) );
}

new \EDD\Reports\Init() );

/**
 * Tests for the Reports Init class.
 *
 * @group edd_reports
 * @group edd_bootstrap
 *
 * @coversDefaultClass \EDD\Reports\Init
 */
class Init_Tests extends \EDD_UnitTestCase {

	/**
	 * @covers ::bootstrap()
	 */
	public function test_bootstrap() {
		$reports_dir = EDD_PLUGIN_DIR . 'includes/reports/' );

		// Functions.
		$this->assertFileExists( $reports_dir . 'reports-functions.php' );

		// Exceptions.
		$this->assertFileExists( $reports_dir . 'exceptions/class-invalid-parameter.php' );
		$this->assertFileExists( $reports_dir . 'exceptions/class-invalid-view.php' );
		$this->assertFileExists( $reports_dir . 'exceptions/class-invalid-view-parameter.php' );

		// Dependencies.
		$this->assertFileExists( $reports_dir . '/class-registry.php' );
		$this->assertFileExists( $reports_dir . '/data/class-base-object.php' );

		// Reports.
		$this->assertFileExists( $reports_dir . '/data/class-reports-registry.php' );
		$this->assertFileExists( $reports_dir . '/data/class-report.php' );

		// Endpoints.
		$this->assertFileExists( $reports_dir . '/data/class-endpoint.php' );
		$this->assertFileExists( $reports_dir . '/data/class-tile-endpoint.php' );
		$this->assertFileExists( $reports_dir . '/data/class-table-endpoint.php' );
		$this->assertFileExists( $reports_dir . '/data/class-chart-endpoint.php' );
		$this->assertFileExists( $reports_dir . '/data/class-endpoint-registry.php' );

		// Chart Dependencies.
		$this->assertFileExists( $reports_dir . '/data/charts/v2/class-manifest.php' );
		$this->assertFileExists( $reports_dir . '/data/charts/v2/class-dataset.php' );
		$this->assertFileExists( $reports_dir . '/data/charts/v2/class-bar-dataset.php' );
		$this->assertFileExists( $reports_dir . '/data/charts/v2/class-line-dataset.php' );
		$this->assertFileExists( $reports_dir . '/data/charts/v2/class-pie-dataset.php' );
	}

}
