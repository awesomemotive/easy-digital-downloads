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

/**
 * Core class that initializes the Reports API.
 *
 * @since 3.0
 */
final class Reports {

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
	 * Sets up the Reports API.
	 *
	 * @since 3.0
	 */
	public function __construct() {
		$this->includes();

		$reports = Reports\Data\Reports_Registry::instance();

		/**
		 * Fires when the Reports API is initialized.
		 *
		 * Use this hook to register new reports and endpoints.
		 *
		 * Example:
		 *
		 *     add_action( 'edd_reports_init', function( $reports ) {
		 *
		 *         $reports->add_report( 'test', array(
		 *             'label'     => 'Test',
		 *             'priority'  => 11,
		 *             'endpoints' => array(
		 *
		 *                 // Endpoints supporting multiple view groups can be reused:
		 *                 'tiles'  => array( 'test_endpoint', ... ),
		 *                 'tables' => array( 'test_endpoint' ),
		 *             ),
		 *         ) );
		 *
		 *         $reports->register_endpoint( 'test_endpoint', array(
		 *             'label' => 'Test Endpoint',
		 *             'views' => array(
		 *
		 *                 // Possible to register a single endpoint for multiple view groups.
		 *                 'tile' => array(
		 *                     'data_callback' => '__return_true',
		 *
		 *                     // Display args are view group specific.
		 *                     'display_args'  => array(
		 *                         'context'          => 'secondary',
		 *                         'comparison_label' => 'Filtered by ...',
		 *                     ),
		 *                 ),
		 *                 'table' => array( ... ),
		 *             ),
		 *         ) );
		 *
		 *     } );
		 *
		 * Reports and endpoints can also be registered using standalone functions:
		 *
		 *     add_action( 'edd_reports_init', function() {
		 *
		 *         edd_reports_add_report( 'test', array( ... ) );
		 *
		 *         edd_reports_register_endpoint( 'test_endpoint', array( ... ) );
		 *
		 *     } );
		 *
		 * @since 3.0
		 *
		 * @param Reports\Data\Reports_Registry $reports Reports registry instance,
		 *                                               passed by reference.
		 */
		do_action_ref_array( 'edd_reports_init', array( &$reports ) );
	}

}
