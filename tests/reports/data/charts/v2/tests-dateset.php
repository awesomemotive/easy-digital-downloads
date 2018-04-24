<?php
namespace EDD\Reports\Data\Charts\v2;

use EDD\Reports\Data\Chart_Endpoint;

if ( ! class_exists( 'EDD\\Reports\\Init' ) ) {
	require_once( EDD_PLUGIN_DIR . 'includes/reports/class-init.php' );
}

new \EDD\Reports\Init();

/**
 * Tests for Dataset class.
 *
 * @group edd_reports
 * @group edd_reports_data
 *
 * @coversDefaultClass \EDD\Reports\Data\Charts\v2\Dataset
 */
class Dataset_Tests extends \EDD_UnitTestCase {

	/**
	 * @var \EDD\Reports\Data\Charts\v2\Dataset
	 */
	protected $mock_Dataset;

	/**
	 * Set up before each test.
	 */
	public function setUp() {
		parent::setUp();

		$this->mock_Dataset = $this->get_Dataset_mock( 'test' );
	}

	/**
	 * @covers ::get_id()
	 * @covers ::set_id()
	 */
	public function test_get_id_should_return_the_dataset_id_supplied_during_instantiation() {
		$this->assertSame( 'test', $this->mock_Dataset->get_id() );
	}

	/**
	 * @covers ::get_endpoint()
	 *
	 * @group edd_reports_endpoints
	 */
	public function test_get_endpoint_should_retrieve_an_Endpoint_object() {
		$this->assertInstanceOf( 'EDD\\Reports\\Data\\Endpoint', $this->mock_Dataset->get_endpoint() );
	}

	/**
	 * @covers ::get_fields()
	 */
	public function test_get_fields_should_return_the_dataset_fields() {
		$this->assertEqualSets( array(), $this->mock_Dataset->get_fields() );
	}

	/**
	 * @covers ::get_global_fields()
	 */
	public function test_get_global_fields_should_return_the_global_fields() {
		$expected = array(
			'label', 'xAxisID', 'yAxisID', 'data',
			'backgroundColor', 'borderColor', 'borderWidth',
		);

		$this->assertEqualSets( $expected, $this->mock_Dataset->get_global_fields() );
	}

	/**
	 * @covers ::get_all_fields()
	 */
	public function test_get_all_fields_should_return_fields_and_global_fields_combined() {
		// Abstract fields are empty, so just expect the global fields.
		$expected = array(
			'label', 'xAxisID', 'yAxisID', 'data',
			'backgroundColor', 'borderColor', 'borderWidth',
		);

		$this->assertEqualSets( $expected, $this->mock_Dataset->get_all_fields() );
	}

	/**
	 * Mocks a Dataset fixture.
	 *
	 * @return \EDD\Reports\Data\Charts\v2\Dataset
	 */
	protected function get_Dataset_mock( $dataset_id, $args = array() ) {
		$defaults = array(
			'label' => __( 'Foo Dataset', 'edd-example-report' ),
			'views' => array(
				'chart' => array(
					'data_callback' => function() {
						return array(
							'sales' => array( 40, 20, 30, 10 ),
						);
					},
					'type'    => 'pie',
					'options' => array(
						'cutoutPercentage' => 50,
						'datasets'         => array(
							'sales' => array(
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

		$args = array_merge( $defaults, $args );

		return $this->getMockForAbstractClass(
			'EDD\\Reports\\Data\\Charts\\v2\\Dataset',
			array( $dataset_id, new Chart_Endpoint( $args ), $args['views']['chart']['options'] )
		);
	}
}
