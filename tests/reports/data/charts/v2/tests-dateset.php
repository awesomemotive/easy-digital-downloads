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
