<?php
namespace EDD\Reports\Data\Charts\v2;

use EDD\Reports\Data\Chart_Endpoint;

if ( ! class_exists( 'EDD\\Reports\\Init' ) ) {
	require_once( EDD_PLUGIN_DIR . 'includes/reports/class-init.php' );
}

new \EDD\Reports\Init();

/**
 * Tests for the Line_Dataset class
 *
 * @group edd_reports
 * @group edd_reports_charts
 *
 * @coversDefaultClass \EDD\Reports\Data\Charts\v2\Line_Dataset
 */
class Line_Dataset_Tests extends \EDD_UnitTestCase {

	/**
	 * @covers ::$fields
	 */
	public function test_default_fields() {
		$expected = array(
			'borderDash', 'borderDashOffset', 'borderCapStyle', 'borderJoinStyle',
			'cubicInterpolationMode', 'fill', 'lineTension', 'pointBackgroundColor',
			'pointBorderColor', 'pointBorderWidth', 'pointRadius', 'pointStyle',
			'pointHitRadius', 'pointHoverBackgroundColor', 'pointHoverBorderColor',
			'pointHoverBorderWidth', 'pointHoverRadius', 'showLine', 'spanGaps',
			'steppedLine',
		);

		if ( version_compare( PHP_VERSION, '5.5', '<' ) ) {
			$class = 'EDD\\Reports\\Data\\Charts\\v2\\Line_Dataset';
		} else {
			$class = Line_Dataset::class;
		}

		$line_dataset = $this->getMockBuilder( $class )
			->setMethods( null )
			->disableOriginalConstructor()
			->getMock();

		$this->assertEqualSets( $expected, $line_dataset->get_fields() );
	}

}
