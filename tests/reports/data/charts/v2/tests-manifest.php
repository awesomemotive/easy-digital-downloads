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

	/**
	 * @covers ::__construct()
	 */
	public function test_Manifest_should_implement_Error_Logger_Interface() {
		$this->assertInstanceOf( 'EDD\\Utils\\Error_Logger_Interface', $this->mock_Manifest );
	}

	/**
	 * @covers ::get_type()
	 * @covers ::set_type()
	 */
	public function test_get_type_should_return_the_chart_type() {
		$this->assertSame( 'pie', $this->mock_Manifest->get_type() );
	}

	/**
	 * @covers ::get_endpoint()
	 * @covers ::set_endpoint()
	 */
	public function test_get_endpoint_should_return_a_Chart_Endpoint() {
		$this->assertInstanceOf( 'EDD\\Reports\\Data\\Chart_Endpoint', $this->mock_Manifest->get_endpoint() );
	}

	/**
	 * @covers ::get_options()
	 */
	public function test_get_options_should_retrieve_the_options_minus_the_labels_and_datasets() {
		$expected = array(
			'cutoutPercentage' => 50,
		);

		$this->assertEqualSetsWithIndex( $expected, $this->mock_Manifest->get_options() );
	}

	/**
	 * Mocks a Manifest fixture.
	 *
	 * @return \EDD\Reports\Data\Charts\v2\Manifest
	 */
	protected function get_Manifest_mock( $dataset_id, $endpoint_args = array() ) {
		$defaults = array(
			'id'    => 'test_endpoint',
			'label' => __( 'Foo Dataset', 'edd-example-report' ),
			'views' => array(
				'chart' => array(
					'data_callback' => function() use ( $dataset_id ) {
						return array(
							$dataset_id => array( 40, 20, 30, 10 ),
						);
					},
					'type'    => 'pie',
					'options' => array(
						'cutoutPercentage' => 50,
						'datasets'         => array(
							$dataset_id => array(
								'label'           => __( 'Sales' ),
								'backgroundColor' => array(
									'rgb(234,16,109)',
									'rgb(98,133,193)',
									'rgb(151,99,143)',
									'rgb(244,10,43)',
								),
							),
						),
						'labels' => array( 'First', 'Second', 'Third', 'Fourth' ),
					),
				),
			)
		);

		$endpoint_args = array_merge( $defaults, $endpoint_args );

		return $this->getMockForAbstractClass(
			'EDD\\Reports\\Data\\Charts\\v2\\Manifest',
			array( new Chart_Endpoint( $endpoint_args ) )
		);
	}

}
