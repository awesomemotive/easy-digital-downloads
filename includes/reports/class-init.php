<?php
/**
 * Reports API
 *
 * @package     EDD
 * @subpackage  Reports
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports;

/**
 * Core class that initializes the Reports API.
 *
 * @since 3.0
 */
final class Init {

	/**
	 * Handles including or requiring files central to the reports API.
	 *
	 * @since 3.0
	 */
	public static function bootstrap() {
		$reports_dir = EDD_PLUGIN_DIR . 'includes/reports/';

		// Functions.
		require_once $reports_dir . 'reports-functions.php';

		// Exceptions.
		require_once $reports_dir . 'exceptions/class-invalid-parameter.php';
		require_once $reports_dir . 'exceptions/class-invalid-view.php';
		require_once $reports_dir . 'exceptions/class-invalid-view-parameter.php';

		// Dependencies.
		require_once $reports_dir . 'class-registry.php';
		require_once $reports_dir . 'data/class-base-object.php';

		// Reports.
		require_once $reports_dir . 'data/class-report-registry.php';
		require_once $reports_dir . 'data/class-report.php';

		// Endpoints.
		require_once $reports_dir . 'data/class-endpoint.php';
		require_once $reports_dir . 'data/class-tile-endpoint.php';
		require_once $reports_dir . 'data/class-table-endpoint.php';
		require_once $reports_dir . 'data/class-chart-endpoint.php';
		require_once $reports_dir . 'data/class-endpoint-registry.php';

		// Chart Dependencies.
		require_once $reports_dir . 'data/charts/v2/class-manifest.php';
		require_once $reports_dir . 'data/charts/v2/class-dataset.php';
		require_once $reports_dir . 'data/charts/v2/class-bar-dataset.php';
		require_once $reports_dir . 'data/charts/v2/class-line-dataset.php';
		require_once $reports_dir . 'data/charts/v2/class-pie-dataset.php';
	}

	/**
	 * Sets up the Reports API.
	 *
	 * @since 3.0
	 */
	public function __construct() {
		self::bootstrap();

		$reports = Data\Report_Registry::instance();

		/**
		 * Fires when the Reports API is initialized.
		 *
		 * Use this hook to register new reports and endpoints.
		 *
		 * Example:
		 *
		 *     add_action( 'edd_reports_init', function( $reports ) {
		 *
		 *         try {
		 *             $reports->add_report( 'test', array(
		 *                 'label'     => 'Test',
		 *                 'priority'  => 11,
		 *                 'endpoints' => array(
		 *
		 *                     // Endpoints supporting multiple view groups can be reused:
		 *                     'tiles'  => array( 'test_endpoint', ... ),
		 *                     'tables' => array( 'test_endpoint' ),
		 *                 ),
		 *             ) );
		 *
		 *             $reports->register_endpoint( 'test_endpoint', array(
		 *                 'label' => 'Test Endpoint',
		 *                 'views' => array(
		 *
		 *                     // Possible to register a single endpoint for multiple view groups.
		 *                     'tile' => array(
		 *                         'data_callback' => '__return_true',
		 *                         'display_args'  => array(
		 *                             'context'          => 'secondary',
		 *                             'comparison_label' => 'Filtered by ...',
		 *                         ),
		 *                     ),
		 *                     'table' => array( ... ),
		 *                 ),
		 *             ) );
		 *         } catch ( \EDD_Exception $exception ) {
		 *
		 *             edd_debug_log_exception( $exception );
		 *
		 *         }
		 *
		 *     } );
		 *
		 * Reports and endpoints can also be registered using standalone functions:
		 *
		 *     add_action( 'edd_reports_init', function() {
		 *
		 *         \EDD\Reports\add_report( 'test', array( ... ) );
		 *
		 *         \EDD\Reports\register_endpoint( 'test_endpoint', array( ... ) );
		 *
		 *     } );
		 *
		 * @since 3.0
		 *
		 * @param Data\Report_Registry $reports Report registry instance,
		 *                                       passed by reference.
		 */
		do_action_ref_array( 'edd_reports_init', array( &$reports ) );
	}

}
