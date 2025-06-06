<?php
namespace EDD\Tests\Data\Charts\V2;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Reports\Data\Chart_Endpoint;
use EDD\Reports;

use EDD\Reports\Init as ReportsInit;
new ReportsInit();

/**
 * Tests for the Manifest class.
 *
 * @group edd_reports
 * @group edd_reports_data
 * @group edd_reports_charts
 *
 * @coversDefaultClass \EDD\Reports\Data\Charts\v2\Manifest
 */
class Manifest_Tests extends EDD_UnitTestCase {

	/**
	 * @var \EDD\Reports\Data\Charts\v2\Manifest
	 */
	protected $mock_Manifest;

	/**
	 * Set up before each test.
	 */
	public function setup(): void {
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
	 * @covers ::get_datasets()
	 */
	public function test_get_datasets_should_retrieve_an_array_with_Dataset_values() {
		$this->assertStringContainsStringOnlyType( 'EDD\\Reports\\Data\\Charts\\v2\\Dataset', $this->mock_Manifest->get_datasets() );
	}

	/**
	 * @covers ::get_datasets()
	 */
	public function test_get_datasets_should_be_keyed_with_strings() {
		$keys = array_keys( $this->mock_Manifest->get_datasets() );

		$this->assertStringContainsStringOnlyType( 'string', $keys );
	}

	/**
	 * @covers ::has_datasets()
	 */
	public function test_has_datasets_should_return_false_if_no_datasets() {
		$manifest = $this->get_Manifest_mock( 'test', array(
			'id'    => 'test_endpoint',
			'label' => __( 'Foo Dataset', 'easy-digital-downloads' ),
			'views' => array(
				'chart' => array(
					'data_callback' => function() {
						return array(
							'test' => array( 40, 20, 30, 10 ),
						);
					},
					'type'    => 'pie',
					'options' => array(
						'cutoutPercentage' => 50,
						'labels'           => array( 'First', 'Second', 'Third', 'Fourth' ),
					),
				),
			)
		) );

		$this->assertFalse( $manifest->has_datasets() );
	}

	/**
	 * @covers ::has_datasets()
	 */
	public function test_has_datasets_should_return_true_if_there_are_datasets() {
		$this->assertTrue( $this->mock_Manifest->has_datasets() );
	}

	/**
	 * @covers ::get_labels()
	 * @covers ::set_labels()
	 */
	public function test_get_label_should_retrieve_labels_if_set() {
		$expected = array( 'First', 'Second', 'Third', 'Fourth' );

		$this->assertEqualSets( $expected, $this->mock_Manifest->get_labels() );
	}

	/**
	 * @covers ::get_labels()
	 * @covers ::set_labels()
	 */
	public function test_get_labels_should_retrieve_an_empty_array_if_not_set() {
		$manifest = $this->get_Manifest_mock( 'test', array(
			'id'    => 'test_endpoint',
			'label' => __( 'Foo Dataset', 'easy-digital-downloads' ),
			'views' => array(
				'chart' => array(
					'data_callback' => function() {
						return array(
							'test' => array( 40, 20, 30, 10 ),
						);
					},
					'type'    => 'pie',
					'options' => array(
						'cutoutPercentage' => 50,
					),
				),
			)
		) );

		$this->assertEqualSets( array(), $manifest->get_labels() );
	}

	/**
	 * @covers ::has_labels()
	 */
	public function test_has_labels_should_return_true_if_labels() {
		$this->assertTrue( $this->mock_Manifest->has_labels() );
	}

	/**
	 * @covers ::has_labels()
	 */
	public function test_has_labels_should_return_false_if_no_labels() {
		$manifest = $this->get_Manifest_mock( 'test', array(
			'id'    => 'test_endpoint',
			'label' => __( 'Foo Dataset', 'easy-digital-downloads' ),
			'views' => array(
				'chart' => array(
					'data_callback' => function() {
						return array(
							'test' => array( 40, 20, 30, 10 ),
						);
					},
					'type'    => 'pie',
					'options' => array(
						'cutoutPercentage' => 50,
					),
				),
			)
		) );

		$this->assertFalse( $manifest->has_labels() );
	}

	/**
	 * @covers ::add_dataset()
	 */
	public function test_add_dataset_should_return_false_on_failure() {
		$manifest = $this->get_Manifest_mock( 'test', array(
			'id'    => 'test_endpoint',
			'label' => __( 'Foo Dataset', 'easy-digital-downloads' ),
			'views' => array(
				'chart' => array(
					'data_callback' => function() {
						return array(
							'test' => array( 40, 20, 30, 10 ),
						);
					},
					'type' => 'fake',
				),
			),
		) );

		$this->assertFalse( $manifest->add_dataset( 'test', array() ) );
	}

	/**
	 * @covers ::get_dataset_handler()
	 */
	public function test_get_dataset_handler_should_return_an_empty_handler_string_for_an_invalid_type() {
		$manifest = $this->get_Manifest_mock( 'test', array(
			'id'    => 'test_endpoint',
			'label' => __( 'Foo Dataset', 'easy-digital-downloads' ),
			'views' => array(
				'chart' => array(
					'data_callback' => function() {
						return array(
							'test' => array( 40, 20, 30, 10 ),
						);
					},
					'type' => 'fake',
				),
			),
		) );

		$this->assertSame( '', $manifest->get_dataset_handler() );
	}

	/**
	 * @covers ::get_dataset_handler()
	 */
	public function test_get_dataset_handler_should_return_a_handler_string_for_a_valid_type() {
		$this->assertSame( 'EDD\\Reports\\Data\\Charts\\v2\\Pie_Dataset', $this->mock_Manifest->get_dataset_handler() );
	}

	/**
	 * @covers ::get_chart_data()
	 */
	public function test_get_chart_data_with_pie_chart_type_should_contain_a_labels_key() {
		$this->assertArrayHasKey( 'labels', $this->mock_Manifest->get_chart_data() );
	}

	/**
	 * @covers ::get_chart_data()
	 */
	public function test_get_chart_data_with_a_mismatched_dataset_will_skip_including_it() {
		$manifest = $this->get_Manifest_mock( 'foo', array(
			'id'    => 'test_endpoint',
			'label' => __( 'Foo Dataset', 'easy-digital-downloads' ),
			'views' => array(
				'chart' => array(
					'data_callback' => function() {
						return array(
							'test' => array( 40, 20, 30, 10 ),
						);
					},
					'type'    => 'pie',
					'options' => array(
						'cutoutPercentage' => 50,
						'datasets'         => array(
							'bar' => array(
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
		) );

		$this->assertArrayNotHasKey( 'datasets', $manifest->get_chart_data() );
	}

	/**
	 * @covers ::get_chart_options()
	 */
	public function test_get_chart_options_default_pie_options_should_match() {
		$expected = array(
			'datasets'   => array(
				'test' => array(
					'label' => 'Sales',
					'backgroundColor' => array(
						'rgb(234,16,109)',
						'rgb(98,133,193)',
						'rgb(151,99,143)',
						'rgb(244,10,43)',
					),
				),
			),
			'labels'     => array(
				0 => 'First',
				1 => 'Second',
				2 => 'Third',
				3 => 'Fourth',
			),
			'animation' => array(
				'duration'   => 0,
			),
			'responsive' => true,
			'legend'     => array(
				'position' => 'left',
			),
			'cutoutPercentage' => 50,
		);

		$this->assertEqualSetsWithIndex( $expected, $this->mock_Manifest->get_chart_options() );
	}

	/**
	 * @covers ::get_chart_options()
	 */
	public function test_get_chart_options_default_non_pie_options_should_match_and_include_the_endpoint_label() {
		$manifest = $this->get_Manifest_mock( 'foo', array(
			'id'    => 'test_endpoint',
			'label' => __( 'Foo Dataset', 'easy-digital-downloads' ),
			'views' => array(
				'chart' => array(
					'data_callback' => function() {
						return array(
							'test' => array( 40, 20, 30, 10 ),
						);
					},
					'type'    => 'line',
					'options' => array(
						'datasets' => array(
							'foo' => array(
								'label'           => __( 'Sales', 'easy-digital-downloads' ),
								'backgroundColor' => array(
									'rgb(234,16,109)',
									'rgb(98,133,193)',
									'rgb(151,99,143)',
									'rgb(244,10,43)',
								),
							),
						),
						'labels'   => array( 'First', 'Second', 'Third', 'Fourth' ),
					),
				),
			)
		) );

		$day_by_day   = Reports\get_dates_filter_day_by_day();
		$hour_by_hour = Reports\get_dates_filter_hour_by_hour();

		$time_unit   = 'month';
		$time_format = 'MMM YYYY';

		if ( $hour_by_hour ) {
			$time_unit   = 'hour';
			$time_format = 'hA';
		} elseif ( $day_by_day ) {
			$time_unit   = 'day';
			$time_format = 'MMM D';
		}

		$expected = array(
			'animation' => array(
				'duration'   => 0,
			),
			'responsive' => true,
			'stacked'    => false,
			'title'      => array(
				'display' => false,
				'text'    => 'Foo Dataset'
			),
			'scales'     => array(
				'xAxes' => array(
					array(
						'type'     => 'time',
						'offset'   => true,
						'display'  => true,
						'gridLines' => array(
							'display' => false,
						),
						'ticks'    => array(
							'source'  => 'auto',
						),
						'position' => 'bottom',
						'time'     => array(
							'unit'          => $time_unit,
							'tooltipFormat' => $time_format,
						),
					),
				),
				'yAxes' => array(
					array(
						'type'     => 'linear',
						'display'  => true,
						'position' => 'left',
						'gridLines' => array(
							'drawBorder' => false,
						),
						'ticks'    => array(
							'formattingType' => 'format',
							'beginAtZero'    => true,
							'suggestedMin'   => 0,
							'maxTicksLimit'  => 5,
							'padding'        => 10,
						),
						'fill' => false,
					),
				),
			),
			'hover' => array(
				'intersect' => false,
				'mode'      => 'index',
			),
			'labels'   => array( 'First', 'Second', 'Third', 'Fourth' ),
			'datasets' => array(
				'foo' => array(
					'label' => 'Sales',
					'backgroundColor' => array(
						'rgb(234,16,109)',
						'rgb(98,133,193)',
						'rgb(151,99,143)',
						'rgb(244,10,43)',
					),
				),
			),
		);

		$this->assertEqualSetsWithIndex( $expected, $manifest->get_chart_options() );
	}

	/**
	 * @covers ::is_pie_manifest()
	 */
	public function test_is_pie_manifest_should_return_true_if_type_is_pie() {
		$this->assertTrue( $this->mock_Manifest->is_pie_manifest() );
	}

	/**
	 * @covers ::is_pie_manifest()
	 */
	public function test_is_pie_manifest_should_return_true_if_type_is_doughnut() {
		$manifest = $this->get_Manifest_mock( 'foo', array(
			'id'    => 'test_endpoint',
			'label' => __( 'Foo Dataset', 'easy-digital-downloads' ),
			'views' => array(
				'chart' => array(
					'data_callback' => function() {
						return array(
							'test' => array( 40, 20, 30, 10 ),
						);
					},
					'type'    => 'doughnut',
					'options' => array(
						'cutoutPercentage' => 50,
						'datasets'         => array(
							'bar' => array(
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
		) );

		$this->assertTrue( $manifest->is_pie_manifest() );
	}

	/**
	 * @covers ::is_pie_manifest()
	 */
	public function test_is_pie_manifest_should_return_false_if_type_is_not_pie_or_doughnut() {
		$manifest = $this->get_Manifest_mock( 'foo', array(
			'id'    => 'test_endpoint',
			'label' => __( 'Foo Dataset', 'easy-digital-downloads' ),
			'views' => array(
				'chart' => array(
					'data_callback' => function() {
						return array(
							'test' => array( 40, 20, 30, 10 ),
						);
					},
					'type'    => 'line',
					'options' => array(
						'cutoutPercentage' => 50,
						'datasets'         => array(
							'bar' => array(
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
		) );

		$this->assertFalse( $manifest->is_pie_manifest() );
	}

	/**
	 * Mocks a Manifest fixture.
	 *
	 * @return \EDD\Reports\Data\Charts\v2\Manifest
	 */
	protected function get_Manifest_mock( $dataset_id, $endpoint_args = array() ) {
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
			'EDD\\Reports\\Data\\Charts\\v2\\Manifest',
			array( new Chart_Endpoint( $endpoint_args ) )
		);
	}

}
