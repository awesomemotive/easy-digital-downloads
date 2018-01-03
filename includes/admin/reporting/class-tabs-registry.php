<?php
/**
 * Reports API - Tabs Registry
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Admin\Reports;

use EDD\Utils;

/**
 * Implements a singleton registry for registering reports tabs.
 *
 * @since 3.0
 *
 * @see \EDD\Utils\Registry
 * @see \EDD\Utils\Static_Registry
 *
 * @method array get_tab( string $tab_id )
 * @method void  remove_tab( string $tab_id )
 * @method array get_tabs()
 */
class Tabs_Registry extends Utils\Registry implements Utils\Static_Registry {

	/**
	 * Registry type.
	 *
	 * @since 3.0
	 * @var   string
	 */
	public $type = 'tab';

	/**
	 * The one true EDD_Reports_Tabs_Registry instance.
	 *
	 * @since 3.0
	 * @var   \EDD\Admin\Reports\Tabs_Registry
	 */
	private static $instance;

	/**
	 * Retrieves the one true Reports Tabs Registry instance.
	 *
	 * @since 3.0
	 *
	 * @return \EDD\Admin\Reports\Tabs_Registry Reports tabs registry instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Tabs_Registry();
		}

		return self::$instance;
	}

	/**
	 * Handles magic method calls for tab manipulation.
	 *
	 * @since 3.0
	 *
	 * @param string $name      Method name.
	 * @param array  $arguments Method arguments (if any)
	 * @return mixed Results of the method call (if any).
	 */
	public function __call( $name, $arguments ) {
		switch( $name ) {
			case 'get_tab':
				return parent::get_item( $arguments[0] );
				break;

			case 'remote_tab':
				parent::remove_item( $arguments[0] );
				break;

			case 'get_tabs':
				return parent::get_items();
				break;

		}
	}

	/**
	 * Adds a new reports tab to the master registry.
	 *
	 * @since 3.0
	 *
	 * @param string $tab_id     Reports tab ID.
	 * @param array  $attributes {
	 *     Attributes of the reports tab.
	 *
	 *     @type string $label    Tab label.
	 *     @type int    $priority Priority by which to register the tab.
	 *     @type array  $filters  Registered filters to expose for the tab.
	 *     @type string $graph    Class to instantiate for building the graph.
	 * }
	 * @return bool True if the tab was successfully registered, otherwise false.
	 */
	public function add_tab( $tab_id, $attributes ) {
		return parent::add_item( $tab_id, $attributes );
	}

}
