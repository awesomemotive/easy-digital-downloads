<?php
namespace EDD\Reports\Data\Charts\v2;

use EDD\Reports\Data\Chart_Endpoint;

if ( ! class_exists( '\EDD\Reports\Init' ) ) {
	require_once( EDD_PLUGIN_DIR . 'includes/reports/class-init.php' );
}

new \EDD\Reports\Init();

/**
 * Tests for the Bar_Dataset class
 *
 * @group edd_reports
 * @group edd_reports_charts
 *
 * @coversDefaultClass \EDD\Reports\Data\Charts\v2\Bar_Dataset
 */
class Bar_Dataset_Tests extends \EDD_UnitTestCase {

	/**
	 * @covers ::$fields
	 */
	public function test_default_fields() {
		$expected = array(
			'borderSkipped', 'hoverBackgroundColor',
			'hoverBorderColor', 'hoverBorderWidth'
		);

		$bar_dataset = $this->getMockBuilder( Bar_Dataset::class )
			->setMethods( null )
			->disableOriginalConstructor()
			->getMock();

		$this->assertEqualSets( $expected, $bar_dataset->get_fields() );
	}

}
