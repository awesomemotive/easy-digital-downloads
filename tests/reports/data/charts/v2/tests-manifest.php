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
	 * @covers ::get_datasets()
	 */
	public function test_get_datasets_should_retrieve_an_array_with_Dataset_values() {
		$this->assertContainsOnlyType( 'EDD\\Reports\\Data\\Charts\\v2\\Dataset', $this->mock_Manifest->get_datasets() );
	}

	/**
	 * @covers ::get_datasets()
	 */
	public function test_get_datasets_should_be_keyed_with_strings() {
		$keys = array_keys( $this->mock_Manifest->get_datasets() );

		$this->assertContainsOnlyType( 'string', $keys );
	}

	/**
	 * @covers ::has_datasets()
	 */
	public function test_has_datasets_should_return_false_if_no_datasets() {
		$manifest = $this->get_Manifest_mock( 'test', array(
			'id'    => 'test_endpoint',
			'label' => __( 'Foo Dataset', 'edd-example-report' ),
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
			'label' => __( 'Foo Dataset', 'edd-example-report' ),
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
			'label' => __( 'Foo Dataset', 'edd-example-report' ),
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
			'label' => __( 'Foo Dataset', 'edd-example-report' ),
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
