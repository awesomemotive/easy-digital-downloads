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

		$reports = EDD()->utils->get_registry( 'reports' );

		/**
		 * Fires when the reports registry is initialized.
		 *
		 * Use this hook to register new reports.
		 *
		 * @since 3.0
		 *
		 * @param \EDD\Admin\Reports\Reports_Registry $reports Reports registry instance, passed by reference.
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

		// Registries.
		require_once $reports_dir . '/class-registry.php';
		require_once $reports_dir . '/class-reports-registry.php';
		require_once $reports_dir . '/class-filters-registry.php';
	}

	/**
	 * Handles registering hook callbacks for a variety of reports API purposes.
	 *
	 * @since 3.0
	 */
	private function hooks() {
		add_action( 'edd_reports_init', array( $this, 'register_core_reports' ) );
	}

	/**
	 * Registers core reports for the Reports API.
	 *
	 * @since 3.0
	 *
	 * @param \EDD\Admin\Reports\Reports_Registry $reports Reports registry.
	 */
	public function register_core_reports( $reports ) {

		// Test code: The 'core' report doesn't exist, so exception(s) should bubble up and be caught.
		try {

			$reports->add_report( 'products', array(
				'label'     => __( 'Products', 'easy-digital-downloads' ),
				'priority'  => 10,
				'endpoints' => array(
					'tiles' => array( 'something' ),
				),
			) );

			$reports->add_report( 'earnings', array(
				'label'    => __( 'Earnings', 'easy-digital-downloads' ),
				'priority' => 5,
			) );

		} catch( \EDD_Exception $exception ) {

			edd_debug_log_exception( $exception );

		}

	}

}
