<?php
namespace EDD\Tests\Data\Charts\V2;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Reports\Data\Chart_Endpoint;

use EDD\Reports\Init as ReportsInit;
new ReportsInit();

/**
 * Tests for the Dataset class.
 *
 * @group edd_reports
 * @group edd_reports_data
 * @group edd_reports_charts
 *
 * @coversDefaultClass \EDD\Reports\Data\Charts\v2\Dataset
 */
class Dataset_Tests extends EDD_UnitTestCase {

	/**
	 * @var \EDD\Reports\Data\Charts\v2\Dataset
	 */
	protected $mock_Dataset;

	/**
	 * Set up before each test.
	 */
	public function setup(): void {
		parent::setUp();

		$this->mock_Dataset = $this->get_Dataset_mock( 'test' );
	}

	/**
	 * @covers ::__construct()
	 */
	public function test_Dataset_should_implement_Error_Logger_Interface() {
		$this->assertInstanceOf( 'EDD\\Utils\\Error_Logger_Interface', $this->mock_Dataset );
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
	 * @covers ::get_data()
	 */
	public function test_get_data_should_retrieve_data_from_the_attached_endpoint_based_on_the_dataset_id() {
		$expected = array( 40, 20, 30, 10 );

		$this->assertEqualSets( $expected, $this->mock_Dataset->get_data() );
	}

	/**
	 * @covers ::validate()
	 */
	public function test_validate_should_have_errors_if_no_data() {
		$dataset = $this->get_Dataset_mock( 'test', array(
			'views' => array(
				'chart' => array(
					'data_callback' => function() {
						return;
					},
					'options' => array(),
				),
			),
		) );

		$this->assertTrue( $dataset->has_errors() );
	}

	/**
	 * @covers ::validate()
	 */
	public function test_validate_should_log_missing_chart_data_error_if_no_data() {
		$dataset = $this->get_Dataset_mock( 'test', array(
			'views' => array(
				'chart' => array(
					'data_callback' => function() {
						return;
					},
					'options' => array(),
				),
			),
		) );

		$this->assertTrue( in_array( 'missing_chart_data', $dataset->get_errors()->get_error_codes() ) );
	}

	/**
	 * @covers ::validate()
	 */
	public function test_validate_should_add_data_key_to_options_if_successful() {
		$this->assertArrayHasKey( 'data', $this->mock_Dataset->get_options() );
	}

	/**
	 * @covers ::parse_data_for_output()
	 */
	public function test_parse_data_for_output_should_return_single_axis_keyed_data_for_single_level_non_pie_data() {
		$expected = array();
		$data     = array();

		for ( $day = 1; $day < 5; $day++ ) {
			$timezone = new \DateTimeZone( edd_get_timezone_id() );
			$date     = new \DateTime( strtotime( 'Y-m-' . $day )  );
			$data[]   = $date->getTimestamp();

			$date_on_chart = new \DateTime( '@' . $date->getTimestamp() );
			$expected[]    = array(
				'x' => $date_on_chart->setTimeZone( $timezone )->format( 'Y-m-d H:i:s' ),
			);
		}

		$this->assertEqualSetsWithIndex( $expected, $this->mock_Dataset->parse_data_for_output( $data ) );
	}

	/**
	 * @covers ::parse_data_for_output()
	 */
	public function test_parse_data_for_output_should_return_double_axis_keyed_data_for_double_level_non_pie_data() {
		$expected = array();
		$data     = array();

		for ( $day = 1; $day < 5; $day++ ) {
			$timezone = new \DateTimeZone( edd_get_timezone_id() );
			$date     = new \DateTime( strtotime( 'Y-m-' . $day )  );
			$data[]   = array( $date->getTimestamp(), $day * 5 );

			$date_on_chart = new \DateTime( '@' . $date->getTimestamp() );
			$expected[]    = array(
				'x' => $date_on_chart->setTimeZone( $timezone )->format( 'Y-m-d H:i:s' ),
				'y' => $day * 5,
			);
		}

		$this->assertEqualSetsWithIndex( $expected, $this->mock_Dataset->parse_data_for_output( $data ) );
	}

	/**
	 * Mocks a Dataset fixture.
	 *
	 * @return \EDD\Reports\Data\Charts\v2\Dataset
	 */
	protected function get_Dataset_mock( $dataset_id, $endpoint_args = array() ) {
		$defaults = array(
			'id'    => 'test_endpoint',
			'label' => __( 'Foo Dataset', 'easy-digital-downloads' ),
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
								'label'           => __( 'Sales', 'easy-digital-downloads' ),
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
			'EDD\\Reports\\Data\\Charts\\v2\\Dataset',
			array( $dataset_id, new Chart_Endpoint( $endpoint_args ), $endpoint_args['views']['chart']['options'] )
		);
	}
}
