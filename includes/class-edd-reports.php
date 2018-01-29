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

}
