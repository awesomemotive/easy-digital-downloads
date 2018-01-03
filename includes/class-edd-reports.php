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
 * The reports API is intentionally initialized outside of the admin-only constraint
 * to provide greater accessibility to core and extensions. As such, the potential
 * footprint for report tab and tile registrations is intentionally kept minimal.
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

		$tabs = EDD()->utils->get_registry( 'reports:tabs' );

		/**
		 * Fires when the reports tab registry is initialized.
		 *
		 * Use this hook to register new reports tabs.
		 *
		 * @since 3.0
		 *
		 * @param \EDD\Admin\Reports\Tabs_Registry $tabs Report tabs registry instance, passed by reference.
		 */
		do_action_ref_array( 'edd_reports_tabs_init', array( &$tabs ) );
	}

	/**
	 * Handles including or requiring files central to the reports API.
	 *
	 * @since 3.0
	 */
	private function includes() {
		$reports_dir = EDD_PLUGIN_DIR . 'includes/admin/reporting/';

		// Registries.
		require_once $reports_dir . '/registries/class-tabs-registry.php';
		require_once $reports_dir . '/registries/class-tiles-registry.php';
		require_once $reports_dir . '/registries/class-data-points-registry.php';
	}

	/**
	 * Handles registering hook callbacks for a variety of reports API purposes.
	 *
	 * @since 3.0
	 */
	private function hooks() {
		add_action( 'edd_reports_tabs_init', array( $this, 'register_core_tabs' ) );
	}

	/**
	 * Registers core tabs for the Reports API.
	 *
	 * @since 3.0
	 *
	 * @param \EDD\Admin\Reports\Tabs_Registry $tabs Reports tabs registry.
	 */
	public function register_core_tabs( $tabs ) {

		// Test code: The 'core' tab doesn't exist, so exception(s) should bubble up and be caught.
		try {

			$tabs->add_tab( 'core_test', array(
				'label' => __( 'Test', 'easy-digital-downloads' )
			) );

		} catch( \EDD_Exception $exception ) {

			edd_debug_log_exception( $exception );

		}

	}

}
