<?php
/**
 * Reports API - Data Endpoints Registry
 *
 * @package     EDD
 * @subpackage  Reports/Data
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports\Data;

use EDD\Utils;
use EDD\Reports;
use EDD\Reports\Exceptions as Reports_Exceptions;

/**
 * Implements a singleton registry for registering reports data endpoints.
 *
 * @since 3.0
 *
 * @see \EDD\Reports\Registry
 * @see \EDD\Utils\Static_Registry
 *
 * @method array get_endpoint( string $endpoint_id )
 * @method bool  endpoint_exists( string $endpoing_id )
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

		$endpoint_id_or_sort = isset( $arguments[0] )
			? $arguments[0]
			: '';

		switch( $name ) {
			case 'get_endpoint':
				return parent::get_item( $endpoint_id_or_sort );

			case 'endpoint_exists':
				return parent::offsetExists( $endpoint_id_or_sort );

			case 'unregister_endpoint':
				parent::remove_item( $endpoint_id_or_sort );
				break;

			case 'get_endpoints':
				return $this->get_items_sorted( $endpoint_id_or_sort );
		}
	}

	/**
	 * Registers a new data endpoint to the master registry.
	 *
	 * @since 3.0
	 *
	 * @throws \EDD_Exception if the endpoint could not be validated.
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

		$defaults = array(
			'label'    => '',
			'priority' => 10,
			'views'    => array(),
		);

		$attributes = array_merge( $defaults, $attributes );

		$attributes['id']    = $endpoint_id;
		$attributes['views'] = Reports\parse_endpoint_views( $attributes['views'] );

		// Bail if this endpoint ID is already registered.
		if ( $this->offsetExists( $endpoint_id ) ) {
			$message = sprintf( 'The \'%1$s\' endpoint already exists and cannot be registered.', $endpoint_id );

			throw new Utils\Exception( $message );
		}

		try {
			$valid = $this->validate_endpoint( $endpoint_id, $attributes );
		} catch ( \EDD_Exception $exception ) {
			throw $exception;
		}

		if ( false === $valid ) {
			return false;
		} else {
			try {
				$return_value = parent::add_item( $endpoint_id, $attributes );
			} catch ( \EDD_Exception $exception ) {
				throw $exception;
			}
			return $return_value;
		}
	}

	/**
	 * Validates the endpoint attributes.
	 *
	 * @since 3.0
	 *
	 * @throws \EDD_Exception if the `$label` or `$views` attributes are empty.
	 * @throws \EDD_Exception if any of the `$views` sub-attributes are empty, except `$filters`.
	 *
	 * @param string $endpoint_id Reports data endpoint ID.
	 * @param array  $attributes  Endpoint attributes. See register_endpoint() for full accepted attributes.
	 * @return bool True if the endpoint is considered 'valid', otherwise false.
	 */
	public function validate_endpoint( $endpoint_id, $attributes ) {
		$is_valid = true;

		try {

			$this->validate_attributes( $attributes, $endpoint_id );

			try {
				$this->validate_views( $attributes['views'], $endpoint_id );

			} catch( \EDD_Exception $exception ) {
				edd_debug_log_exception( $exception );

				$is_valid = false;

				throw $exception;
			}

		} catch( \EDD_Exception $exception ) {
			edd_debug_log_exception( $exception );

			$is_valid = false;

			throw $exception;
		}

		return $is_valid;
	}

	/**
	 * Builds an endpoint object from a registry entry.
	 *
	 * @since 3.0
	 *
	 * @param string|Endpoint $endpoint  Endpoint ID or object.
	 * @param string          $view_type View type to use when building the object.
	 * @param string          $report    Optional. Report ID. Default null.
	 * @return Endpoint|\WP_Error Endpoint object on success, otherwise a WP_Error object.
	 */
	public function build_endpoint( $endpoint, $view_type, $report = null ) {

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

		if ( ! empty( $_endpoint ) ) {

			if ( Reports\validate_endpoint_view( $view_type ) ) {
				$_endpoint['report'] = $report;

				$handler = Reports\get_endpoint_handler( $view_type );

				if ( ! empty( $handler ) && class_exists( $handler ) ) {
					$_endpoint = new $handler( $_endpoint );

				} else {
					$_endpoint = new \WP_Error(
						'invalid_handler',
						sprintf( 'The handler for the \'%1$s\' view is invalid.', $view_type ),
						$handler
					);
				}

			} else {
				$_endpoint = new \WP_Error(
					'invalid_view',
					sprintf( 'The \'%1$s\' view is invalid.', $view_type )
				);
			}
		}

		return $_endpoint;
	}

	/**
	 * Validates view properties for an incoming endpoint.
	 *
	 * @since 3.0
	 *
	 * @throws \EDD_Exception if the view attributes is empty or it's not a valid view.
	 *
	 * @param array  $views       List of attributes to check.
	 * @param string $endpoint_id Endpoint ID.
	 * @return void
	 */
	public function validate_views( $views, $endpoint_id ) {
		$valid_views = Reports\get_endpoint_views();

		$this->validate_attributes( $views, $endpoint_id );

		foreach ( $views as $view => $attributes ) {
			if ( array_key_exists( $view, $valid_views ) ) {
				if ( ! empty( $valid_views[ $view ]['allow_empty'] ) ) {
					$skip = $valid_views[ $view ]['allow_empty'];
				} else {
					$skip = array();
				}

				// View atts have already been parsed at this point, just validate them.
				$this->validate_view_attributes( $attributes, $view, $skip );
			} else {
				throw Reports_Exceptions\Invalid_View::from( $view, __METHOD__, $endpoint_id );
			}
		}
	}

	/**
	 * Validates a list of endpoint view attributes.
	 *
	 * @since 3.0
	 *
	 * @throws \EDD_Exception if a required view attribute is empty.
	 *
	 * @param array  $attributes List of view attributes to check for emptiness.
	 * @param string $view       View slug.
	 * @param array  $skip       Optional. List of view attributes to skip validating.
	 *                           Default empty array.
	 * @return void
	 */
	public function validate_view_attributes( $attributes, $view, $skip = array() ) {
		foreach ( $attributes as $attribute => $value ) {
			if ( in_array( $attribute, $skip, true ) ) {
				continue;
			}

			if ( empty( $value ) ) {
				throw Reports_Exceptions\Invalid_View_Parameter::from( $attribute, __METHOD__, $view );
			}
		}
	}
}
