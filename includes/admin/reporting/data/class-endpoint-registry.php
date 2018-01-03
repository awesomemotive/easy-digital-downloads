<?php
/**
 * Reports API - Data Endpoints Registry
 *
 * @package     EDD
 * @subpackage  Admin/Reports/Data
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Admin\Reports\Data;

use EDD\Utils;

/**
 * Implements a singleton registry for registering reports data endpoints.
 *
 * @since 3.0
 *
 * @see \EDD\Utils\Registry
 * @see \EDD\Utils\Static_Registry
 *
 * @method array get_endpoint( string $endpoint_id )
 * @method void  unregister_endpoint( string $endpoint_id )
 * @method array get_endpoints()
 */
class Endpoint_Registry extends Utils\Registry implements Utils\Static_Registry {

	/**
	 * Registry type.
	 *
	 * @since 3.0
	 * @var   string
	 */
	public $type = 'data endpoint';

	/**
	 * The one true Endpoint_Registry instance.
	 *
	 * @since 3.0
	 * @var   \EDD\Admin\Reports\Data\Endpoint_Registry
	 */
	private static $instance;

	/**
	 * Retrieves the one true Endpoint_Registry instance.
	 *
	 * @since 3.0
	 *
	 * @return \EDD\Admin\Reports\Data\Endpoint_Registry Registry instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Endpoint_Registry();
		}

		return self::$instance;
	}

	/**
	 * Handles magic method calls for endpoint manipulation.
	 *
	 * @since 3.0
	 *
	 * @param string $name      Method name.
	 * @param array  $arguments Method arguments (if any)
	 * @return mixed Results of the method call (if any).
	 */
	public function __call( $name, $arguments ) {
		switch( $name ) {
			case 'get_endpoint':
				return parent::get_item( $arguments[0] );
				break;

			case 'unregister_endpoint':
				parent::remove_item( $arguments[0] );
				break;

			case 'get_endpoints':
				return parent::get_items();
				break;

		}
	}

	/**
	 * Adds a new reports data endpoint to the master registry.
	 *
	 * @since 3.0
	 *
	 * @param string $endpoint_id Reports data endpoint ID.
	 * @param array  $attributes  {
	 *     Attributes of the reports data point.
	 *
	 *     @type string $model Model ID.
	 *     @type string $view  View ID.
	 * }
	 * @return bool True if the endpoint was successfully registered, otherwise false.
	 */
	public function register_endpoint( $endpoint_id, $attributes ) {
		return parent::add_item( $endpoint_id, $attributes );
	}

}
