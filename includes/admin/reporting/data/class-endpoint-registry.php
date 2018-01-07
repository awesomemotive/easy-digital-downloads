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
	 * Registry item error label.
	 *
	 * @since 3.0
	 * @var   string
	 */
	public $item_error_label = 'reports endpoint';

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
	 * @throws \EDD\Utils\Exception If the endpoint doesn't exist for get_endpoint().
	 *
	 * @param string $name      Method name.
	 * @param array  $arguments Method arguments (if any)
	 * @return mixed Results of the method call (if any).
	 */
	public function __call( $name, $arguments ) {

		$endpoint_id = isset( $arguments[0] ) ? $arguments[0] : '';

		switch( $name ) {
			case 'get_endpoint':
				parent::get_item( $endpoint_id );
				break;

			case 'unregister_endpoint':
				parent::remove_item( $endpoint_id );
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
	 * @throws \EDD\Utils\Exception If the `$attributes` array is empty.
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

	/**
	 * Builds an endpoint object from a registry entry.
	 *
	 * @since 3.0
	 *
	 * @param string $endpoint_id Endpoint ID.
	 * @param string $type        View type to use when building the object.
	 * @return Endpoint|\WP_Error Endpoint object on success, otherwise a WP_Error object.
	 */
	public function build_endpoint( $endpoint_id, $type ) {

		try {

			$endpoint = $this->get_endpoint( $endpoint_id );

		} catch( \EDD_Exception $exception ) {

			edd_debug_log_exception( $exception );
		}

		if ( ! empty( $endpoint ) ) {
			$endpoint = new Endpoint( $endpoint, $type );
		} else {
			return new \WP_Error( 'invalid_endpoint', $endpoint_id );
		}

		if ( $endpoint->has_errors() ) {
			return $endpoint->get_errors();
		} else {
			return $endpoint;
		}
	}
}
