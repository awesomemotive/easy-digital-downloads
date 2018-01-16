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
use EDD\Admin\Reports;
use EDD\Admin\Reports\Exceptions as Reports_Exceptions;

/**
 * Implements a singleton registry for registering reports data endpoints.
 *
 * @since 3.0
 *
 * @see \EDD\Admin\Reports\Registry
 * @see \EDD\Utils\Static_Registry
 *
 * @method array get_endpoint( string $endpoint_id )
 * @method void  unregister_endpoint( string $endpoint_id )
 * @method array get_endpoints( string $sort )
 */
class Endpoint_Registry extends Reports\Registry implements Utils\Static_Registry {

	/**
	 * Registry item error label.
	 *
	 * @since 3.0
	 * @var   string
	 */
	public static $item_error_label = 'reports endpoint';

	/**
	 * The one true Endpoint_Registry instance.
	 *
	 * @since 3.0
	 * @var   Endpoint_Registry
	 */
	private static $instance;

	/**
	 * Retrieves the one true Endpoint_Registry instance.
	 *
	 * @since 3.0
	 *
	 * @return Endpoint_Registry Registry instance.
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
	 * @throws \EDD_Exception If the endpoint doesn't exist for get_endpoint().
	 *
	 * @param string $name      Method name.
	 * @param array  $arguments Method arguments (if any)
	 * @return mixed Results of the method call (if any).
	 */
	public function __call( $name, $arguments ) {

		$endpoint_id_or_sort = isset( $arguments[0] ) ? $arguments[0] : '';

		switch( $name ) {
			case 'get_endpoint':
				return parent::get_item( $endpoint_id_or_sort );
				break;

			case 'unregister_endpoint':
				parent::remove_item( $endpoint_id_or_sort );
				break;

			case 'get_endpoints':
				return $this->get_items_sorted( $endpoint_id_or_sort );
				break;

		}
	}

	/**
	 * Registers a new data endpoint to the master registry.
	 *
	 * @since 3.0
	 *
	 * @throws \EDD_Exception if the `$label` or `$views` attributes are empty.
	 * @throws \EDD_Exception if any of the `$views` sub-attributes are empty, except `$filters`.
	 *
	 * @param string $endpoint_id Reports data endpoint ID.
	 * @param array  $attributes  {
	 *     Endpoint attributes. All arguments are required unless otherwise noted.
	 *
	 *     @type string $label    Endpoint label.
	 *     @type int    $priority Optional. Priority by which to retrieve the endpoint. Default 10.
	 *     @type array  $views {
	 *         Array of view handlers by type.
	 *
	 *         @type array $view_type {
	 *             View type slug, with array beneath it.
	 *
	 *             @type callable $data_callback    Callback used to retrieve data for the view.
	 *             @type callable $display_callback Callback used to render the view.
	 *             @type array    $display_args     Optional. Array of arguments to pass to the
	 *                                              display_callback (if any). Default empty array.
	 *         }
	 *     }
	 * }
	 * @return bool True if the endpoint was successfully registered, otherwise false.
	 */
	public function register_endpoint( $endpoint_id, $attributes ) {
		$error = false;

		$defaults = array(
			'label'    => '',
			'priority' => 10,
			'views'    => array(),
		);

		$attributes = array_merge( $defaults, $attributes );

		$attributes['id'] = $endpoint_id;

		try {

			$this->validate_attributes( $attributes, $endpoint_id );

			try {

				$this->validate_views( $attributes['views'], $endpoint_id );

			} catch( \EDD_Exception $exception ) {

				edd_debug_log_exception( $exception );

				throw $exception;

				$error = true;

			}

		} catch( \EDD_Exception $exception ) {

			edd_debug_log_exception( $exception );

			throw $exception;

			$error = true;
		}

		if ( true === $error ) {

			return false;

		} else {

			return parent::add_item( $endpoint_id, $attributes );

		}
	}

	/**
	 * Builds an endpoint object from a registry entry.
	 *
	 * @since 3.0
	 *
	 * @param string|Endpoint $endpoint  Endpoint ID or object.
	 * @param string          $view_type View type to use when building the object.
	 * @return Endpoint|\WP_Error Endpoint object on success, otherwise a WP_Error object.
	 */
	public function build_endpoint( $endpoint, $view_type ) {

		// If an endpoint object was passed, just return it.
		if ( $endpoint instanceof Endpoint ) {
			return $endpoint;
		}

		try {

			$_endpoint = $this->get_endpoint( $endpoint );

		} catch( \EDD_Exception $exception ) {

			edd_debug_log_exception( $exception );

			return new \WP_Error( 'invalid_endpoint', $exception->getMessage(), $endpoint );

		}

		// Build the Endpoint object.
		$_endpoint = new Endpoint( $view_type, $_endpoint );

		// If any errors were logged during instantiation, return the resulting WP_Error object.
		if ( $_endpoint->has_errors() ) {

			return $_endpoint->get_errors();

		}

		return $_endpoint;
	}

	/**
	 * Validates view properties for an incoming endpoint.
	 *
	 * @since 3.0
	 *
	 * @throws \EDD_Exception if a view's attributes is empty or it's not a valid view.
	 *
	 * @param array  $attributes List of attributes to check.
	 * @param string $item_id    Endpoint ID.
	 * @return void
	 */
	public function validate_views( $views, $endpoint_id ) {
		$valid_views = edd_reports_get_endpoint_views();

		$this->validate_attributes( $views, $endpoint_id, array( 'display_args' ) );

		foreach ( $views as $view => $attributes ) {
			if ( ! array_key_exists( $view, $valid_views ) ) {
				throw Reports_Exceptions\Invalid_View::from( $view, __METHOD__, $endpoint_id );
			}
		}
	}

}
