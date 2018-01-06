<?php
/**
 * Reports API - Endpoint View object
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Admin\Reports\Data;

/**
 * Represents a data endpoint for the Reports API.
 *
 * @since 3.0
 */
class Endpoint {

	/**
	 * Represents filters available to the endpoint.
	 *
	 * @since 3.0
	 */
	public $filters = array();

	/**
	 * Endpoint label.
	 *
	 * @since 3.0
	 * @var   string
	 */
	public $label;

	/**
	 * Endpoint type.
	 *
	 * @since 3.0
	 * @var   string
	 */
	public $type;

	/**
	 * Sets the endpoint type.
	 *
	 * @since 3.0
	 *
	 * @param string $type Endpoint type.
	 */
	public function set_type( $type ) {
		$types = edd_get_reports_endpoint_views();

		if ( in_array( $type, $types, true ) ) {
			$this->type = $type;
		}
	}

	/**
	 * Constructs the endpoint object.
	 *
	 * @since 3.0
	 *
	 * @param string $endpoint_id Endpoint ID.
	 * @param string $type        Endpoint view type. Determines which view attribute to
	 *                            retrieve from the corresponding endpoint registry entry.
	 */
	public function __construct( $endpoint_id, $type ) {
		$registry = EDD()->utils->get_registry( 'reports:endpoints' );

		try {

			$endpoint = $registry->get_endpoint( $endpoint_id );

		} catch( \EDD_Exception $exception ) {

			edd_debug_log_exception( $exception );

			return new \WP_Error( 'invalid_endpoint_id', 'Invalid endpoint ID', $exception->getTraceAsString() );
		}

		
		$this->set_type( $type );
	}

}
