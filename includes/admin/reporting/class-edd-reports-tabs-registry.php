<?php
/**
 * Reports API Tabs Registry
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

/**
 * Implements a singleton registry for registering reports tabs.
 *
 * @since 3.0
 *
 * @see \EDD_Registry
 */
class EDD_Reports_Tabs_Registry extends \EDD_Registry {

	/**
	 * The one true EDD_Reports_Tabs_Registry instance.
	 *
	 * @since 3.0
	 * @var   \EDD_Reports_Tabs_Registry
	 */
	private static $instance;

	/**
	 * Retrieves the one true Reports Tabs Registry instance.
	 *
	 * @since 3.0
	 *
	 * @return \EDD_Reports_Tabs_Registry Reports tabs registry instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new EDD_Reports_Tabs_Registry;
		}

		return self::$instance;
	}

	/**
	 * Adds a new reports tab to the master registry.
	 *
	 * @since 3.0
	 *
	 * @param string $tab_id     Reports tab ID.
	 * @param array  $attributes
	 * @return bool
	 */
	public function add_tab( $tab_id, $attributes ) {
		$result = false;

		try {

			$result = parent::add_item( $tab_id, $attributes );

		} catch( EDD_Exception $exception ) {

			$exception->log();

		}

		return $result;
	}

	/**
	 * Removes a reports tab by ID from the master registry.
	 *
	 * @since 3.0
	 *
	 * @param string $tab_id Reports tab ID.
	 */
	public function remove_tab( $tab_id ) {
		parent::remove_item( $tab_id );
	}

	/**
	 * Retrieves a specific reports tab by ID from the master registry.
	 *
	 * @since 3.0
	 *
	 * @param string $tab_id Name of the reports tab to retrieve.
	 * @return array The tab's attributes if it exists, otherwise an empty array.
	 */
	public function get_tab( $tab_id ) {
		$tab = array();

		try {

			$tab = parent::get_item( $tab_id );

		} catch( EDD_Exception $exception ) {

			$exception->log();

		}

		return $tab;
	}

	/**
	 * Retrieves all of the registered reports tab records.
	 *
	 * @since 3.0
	 *
	 * @return array All registered reports tabs.
	 */
	public function get_tabs() {
		return parent::get_items();
	}

	/**
	 * Initializes the registry.
	 *
	 * @since 3.0
	 */
	public function init() {}
	
}
