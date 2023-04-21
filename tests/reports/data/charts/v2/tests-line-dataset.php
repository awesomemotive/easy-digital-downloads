<?php
namespace EDD\Tests\Data\Charts\V2;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Reports\Init as ReportsInit;
new ReportsInit();

/**
 * Tests for the Line_Dataset class
 *
 * @group edd_reports
 * @group edd_reports_charts
 *
 * @coversDefaultClass \EDD\Reports\Data\Charts\v2\Line_Dataset
 */
class Line_Dataset_Tests extends EDD_UnitTestCase {

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

		$line_dataset = $this->getMockBuilder( 'EDD\\Reports\\Data\\Charts\\v2\\Line_Dataset' )
			->setMethods( null )
			->disableOriginalConstructor()
			->getMock();

		$this->assertEqualSets( $expected, $line_dataset->get_fields() );
	}

}
