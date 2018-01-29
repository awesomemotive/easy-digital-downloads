<?php
/**
 * Reports API
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Admin;
use EDD\Admin\Reports\Data\Endpoint;
use EDD\Admin\Reports\Data\Report;
use EDD\Admin\Reports\Data\Tile_Endpoint;

/**
 * Core class that implements the Reports API.
 *
 * @since 3.0
 */
final class Reports {

	/**
	 * Sets up the Reports API.
	 *
	 * @since 3.0
	 */
	public function __construct() {
		$this->includes();
		$this->hooks();

		$reports = \EDD\Admin\Reports\Data\Reports_Registry::instance();

		/**
		 * Fires when the reports registry is initialized.
		 *
		 * Use this hook to register new reports.
		 *
		 * @since 3.0
		 *
		 * @param Reports\Data\Reports_Registry $reports Reports registry instance,
		 *                                               passed by reference.
		 */
		do_action_ref_array( 'edd_reports_init', array( &$reports ) );
	}

	/**
	 * Handles including or requiring files central to the reports API.
	 *
	 * @since 3.0
	 */
	private function includes() {
		$reports_dir = EDD_PLUGIN_DIR . 'includes/admin/reporting/';

		// Exceptions.
		require_once $reports_dir . 'exceptions/class-invalid-parameter.php';
		require_once $reports_dir . 'exceptions/class-invalid-view.php';
		require_once $reports_dir . 'exceptions/class-invalid-view-parameter.php';

		// Dependencies.
		require_once $reports_dir . '/class-registry.php';
		require_once $reports_dir . '/data/class-base-object.php';

		// Reports.
		require_once $reports_dir . '/data/class-reports-registry.php';
		require_once $reports_dir . '/data/class-report.php';

		// Endpoints.
		require_once $reports_dir . '/data/class-endpoint.php';
		require_once $reports_dir . '/data/class-tile-endpoint.php';
		require_once $reports_dir . '/data/class-endpoint-registry.php';
	}

	/**
	 * Handles registering hook callbacks for a variety of reports API purposes.
	 *
	 * @since 3.0
	 */
	private function hooks() {
//		add_action( 'edd_reports_init', array( $this, 'register_core_reports' ) );
	}

	/**
	 * Registers core reports for the Reports API.
	 *
	 * @since 3.0
	 *
	 * @param \EDD\Admin\Reports\Data\Reports_Registry $reports Reports registry.
	 */
	public function register_core_reports( $reports ) {

		// Test code.
		try {

			$reports->register_endpoint( 'something', array(
				'label' => 'Something',
				'views' => array(
					'tile' => array(
						'data_callback' => function() {
							return 'Some Data';
						}
					)
				)
			) );

			$reports->add_report( 'products', array(
				'label'     => __( 'Products', 'easy-digital-downloads' ),
				'priority'  => 10,
				'endpoints' => array(
					'tiles' => array( 'something' ),
				),
			) );

			$endpoint = new Tile_Endpoint( array(
				'id'    => 'on_the_fly',
				'label' => 'On the Fly',
				'views' => edd_reports_parse_endpoint_views( array(
					'tile' => array(
						'data_callback'    => function() {
							return 'Hello, World! (data)';
						},
						'display_args' => array(
							'context' => 'secondary',
						),
					)
				) ),
			) );

			$reports->add_report( 'test_report', array(
				'label' => 'Foo Report',
				'endpoints' => array(
					'tiles' => array( 'something', $endpoint ),
				),
			) );

			try {
				$built_report = new Report( array(
					'id'    => 'on_the_fly',
					'label' => 'On the Fly',
					'endpoints' => array(
						'tiles' => array( 'something', 'else', $endpoint )
					)
				) );
			} catch ( \EDD_Exception $exception ) {

				edd_debug_log_exception( $exception );

				$built_report = 'fail';
			}

			$endpoints = $built_report->get_endpoints( 'tiles' );

//			var_dump( $endpoints );

			$registered_report = \edd_reports_get_report( 'test_report' );

//			var_dump( $registered_report );

		} catch( \EDD_Exception $exception ) {

			edd_debug_log_exception( $exception );

		}

	}

}
