<?php
namespace EDD\Reports\Data\Charts\v2;

use EDD\Reports\Data\Chart_Endpoint;

if ( ! class_exists( 'EDD\\Reports\\Init' ) ) {
	require_once( EDD_PLUGIN_DIR . 'includes/reports/class-init.php' );
}

new \EDD\Reports\Init();

/**
 * Tests for the Manifest class.
 *
 * @group edd_reports
 * @group edd_reports_data
 * @group edd_reports_charts
 *
 * @coversDefaultClass \EDD\Reports\Data\Charts\v2\Manifest
 */
class Manfiest_Tests extends \EDD_UnitTestCase {

	/**
	 * @var \EDD\Reports\Data\Charts\v2\Manifest
	 */
	protected $mock_Manifest;

	/**
	 * Set up before each test.
	 */
	public function setUp() {
		parent::setUp();

		$this->mock_Manifest = $this->get_Manifest_mock( 'test' );
	}

}
