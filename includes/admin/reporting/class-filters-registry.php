<?php
/**
 * Reports API Filters Registry
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
 * Implements a singleton registry for registering reports filters.
 *
 * @since 3.0
 *
 * @see \EDD\Utils\Registry
 * @see \EDD\Utils\Static_Registry
 *
 * @method array get_filter( string $filter_id )
 * @method void  remove_filter( string $filter_id )
 * @method array get_filters()
 */
class Filters_Registry extends Registry implements Utils\Static_Registry {

	/**
	 * Reports item error label.
	 *
	 * @since 3.0
	 * @var   string
	 */
	public $item_error_label = 'reports filter';

	/**
	 * The one true Filters_Registry instance.
	 *
	 * @since 3.0
	 * @var   \EDD\Admin\Reports\Filters_Registry
	 */
	private static $instance;

	/**
	 * Retrieves the one true Filters_Registry instance.
	 *
	 * @since 3.0
	 *
	 * @return \EDD\Admin\Reports\Filters_Registry Registry instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Filters_Registry();
		}

		return self::$instance;
	}

	/**
	 * Handles magic method calls for filter manipulation.
	 *
	 * @since 3.0
	 *
	 * @throws \EDD_Exception in get_filter() if the item does not exist.
	 *
	 * @param string $name      Method name.
	 * @param array  $arguments Method arguments (if any)
	 * @return mixed Results of the method call (if any).
	 */
	public function __call( $name, $arguments ) {
		switch( $name ) {
			case 'get_filter':
				return parent::get_item( $name );
				break;

			case 'remove_filter':
				parent::remove_item( $name );
				break;

			case 'get_filters':
				return parent::get_items();
				break;

		}
	}

	/**
	 * Adds a new reports filter to the master registry.
	 *
	 * @since 3.0
	 *
	 * @throws \EDD_Exception if any attributes are empty.
	 *
	 * @param string $filter_id   Reports filter ID.
	 * @param array  $attributes {
	 *     Attributes of the reports filter.
	 * }
	 * @return bool True if the filter was successfully registered, otherwise false.
	 */
	public function add_filter( $filter_id, $attributes ) {
		return parent::add_item( $filter_id, $attributes );
	}

}
