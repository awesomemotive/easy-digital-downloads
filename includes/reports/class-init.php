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

		// Avoid multiple initializations
		if ( did_action( 'edd_reports_init' ) ) {
			return;
		}

		self::bootstrap();

		$reports = Data\Report_Registry::instance();

		$reports = $this->legacy_reports( $reports );

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

	/**
	 * Maybe add legacy reports if any exist
	 *
	 * @since 3.0
	 *
	 * @param array $reports
	 *
	 * @return array
	 */
	private function legacy_reports( $reports = array() ) {

		// Bail if no legacy reports
		if ( ! has_filter( 'edd_report_views' ) ) {
			return $reports;
		}

		/**
		 * Filters legacy 'Reports' tab views.
		 *
		 * @since 1.4
		 * @deprecated 3.0 Use {@see 'edd_reports_get_tabs'}
		 * @see 'edd_reports_get_tabs'
		 *
		 * @param array $views 'Reports' tab views.
		 */
		$legacy_views = edd_apply_filters_deprecated( 'edd_report_views', array( array() ), '3.0', 'edd_reports_get_tabs' );

		// Bail if no legacy views
		if ( empty( $legacy_views ) ) {
			return $reports;
		}

		// Loop through views and try to convert them
		foreach ( $legacy_views as $report_id => $label ) {

			// Legacy "_tab_" action
			if ( has_action( "edd_reports_tab_{$report_id}" ) ) {
				$hook = "edd_reports_tab_{$report_id}";

			// Legacy "_view_" action
			} elseif ( has_action( "edd_reports_view_{$report_id}" ) ) {
				$hook = "edd_reports_view_{$report_id}";

			// Skip
			} else {
				continue;
			}

			// Create a callback function
			$callback = function() use ( $hook ) {
				/**
				 * Legacy: Fires inside the content area of the currently active Reports tab.
				 *
				 * The dynamic portion of the hook name, `$report_id` refers to the slug of
				 * the current reports tab.
				 *
				 * @since 1.0
				 * @deprecated 3.0 Use the new Reports API to register new tabs.
				 * @see \EDD\Reports\add_report()
				 *
				 * @param \EDD\Reports\Data\Report|\WP_Error $report The current report object,
				 *                                                   or WP_Error if invalid.
				 */
				edd_do_action_deprecated( $hook, array(), '3.0', '\EDD\Reports\add_report' );
			};

			// Add report
			$reports->add_report( $report_id, array(
				'label'            => $label,
				'group'            => 'core',
				'icon'             => 'info',
				'priority'         => 10,
				'display_callback' => $callback
			) );
		}

		// Return reports array
		return $reports;
	}
}
