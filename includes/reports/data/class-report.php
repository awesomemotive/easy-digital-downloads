<?php
/**
 * Reports API - Report object
 *
 * @package     EDD
 * @subpackage  Reports
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Admin\Reports\Data;

use EDD\Utils;

/**
 * Represents an encapsulated report for the Reports API.
 *
 * @since 3.0
 */
final class Report extends Base_Object {

	/**
	 * Represents valid endpoints available for display.
	 *
	 * @since 3.0
	 * @var array
	 */
	private $endpoints = array();

	/**
	 * Represents the raw endpoints passed to the Report constructor.
	 *
	 * @since 3.0
	 * @var   array
	 */
	private $raw_endpoints = array();

	/**
	 * Represents the capability needed to view the rendered report.
	 *
	 * @since 3.0
	 * @var   string
	 */
	private $capability;

	/**
	 * Constructs the report object.
	 *
	 * @since 3.0
	 *
	 * @param array $args Arguments for building the report (usually taking
	 *                    the form of a report record from the registry).
	 */
	public function __construct( $args ) {
		parent::__construct( $args );

		if ( ! empty( $args['endpoints'] ) ) {

			$this->raw_endpoints = $args['endpoints'];

		} else{

			$this->errors->add( 'missing_endpoints', 'No endpoints are defined for the report.', $args );
		}

		if ( ! empty( $args['capability'] ) ) {

			$this->set_capability( $args['capability'] );

		} else {

			$this->errors->add( 'missing_capability', 'No capability is defined for the report.', $args );

		}

		$this->atts = $args;
	}

	/**
	 * Triggers building the report's endpoints if defined and the current user
	 * has the ability to view them.
	 *
	 * This is abstracted away from instantiation to allow for building Report objects
	 * without always registering meta boxes and other endpoint dependencies for display.
	 *
	 * @since 3.0
	 */
	public function build_endpoints() {
		if ( ! empty( $this->raw_endpoints ) && current_user_can( $this->get_capability() ) ) {
			try {

				$this->parse_endpoints( $this->raw_endpoints );

			} catch ( \EDD_Exception $exception ) {

				edd_debug_log_exception( $exception );

			}

		} else {

			$this->errors->add( 'missing_endpoints', 'No endpoints are defined for the report.' );

		}
	}

	/**
	 * Parses Endpoint objects for each endpoint in the report.
	 *
	 * @since 3.0
	 *
	 * @throws \EDD_Exception
	 *
	 * @param array $endpoints Endpoints, keyed by view type.
	 */
	public function parse_endpoints( $report_endpoints ) {
		/** @var \EDD\Admin\Reports\Data\Endpoint_Registry|\WP_Error $registry */
		$registry = EDD()->utils->get_registry( 'reports:endpoints' );

		if ( is_wp_error( $registry ) ) {

			throw new Utils\Exception( $registry->get_error_message() );

			return;
		}

		$view_groups = $this->parse_view_groups();

		// Loop through all passed endpoints using view groups.
		foreach ( $report_endpoints as $group => $endpoints ) {

			// Skip any invalid views based on view group.
			if ( ! array_key_exists( $group, $view_groups ) ) {
				throw new Utils\Exception( sprintf(
					'The \'%1$s\' view group does not correspond to a known endpoint view type.',
					$group
				) );

				continue;
			}

			// Loop through all endpoints for each view group and build endpoint objects.
			foreach ( $endpoints as $endpoint ) {

				$endpoint = $registry->build_endpoint( $endpoint, $view_groups[ $group ], $this->get_id() );

				$this->validate_endpoint( $group, $endpoint );

			}
		}

	}

	/**
	 * Parses the views whitelist to retrieve corresponding view groups.
	 *
	 * @since 3.0
	 *
	 * @return array List of view group and view slug pairs.
	 */
	public function parse_view_groups() {
		$views = edd_reports_get_endpoint_views();

		$view_groups = array();

		foreach ( $views as $view_type => $atts ) {

			if ( ! empty( $atts['group'] ) ) {
				$view_group = $atts['group'];

				$view_groups[ $view_group ] = $view_type;
			}

		}

		return $view_groups;
	}

	/**
	 * Validates an endpoint for rendering.
	 *
	 * @since 3.0
	 *
	 * @see \EDD\Admin\Reports\Data\Report::$valid_endpoints
	 *
	 * @param string                  $view_group View group corresponding to the endpoint view.
	 * @param Data\Endpoint|\WP_Error $endpoint   Endpoint object.
	 */
	public function validate_endpoint( $view_group, $endpoint ) {
		if ( is_wp_error( $endpoint ) ) {

			$this->errors->add(
				$endpoint->get_error_code(),
				$endpoint->get_error_message(),
				$endpoint->get_error_data()
			);

		} elseif ( ! is_wp_error( $endpoint ) && $endpoint->has_errors() ) {

			$message = sprintf( 'The \'%1$s\' endpoint is invalid.', $endpoint->get_id() );

			$this->errors->add( 'invalid_endpoint', $message, $endpoint->get_errors() );

		} else {

			// Valid.
			$this->endpoints[ $view_group ][ $endpoint->get_id() ] = $endpoint;

		}
	}

	/**
	 * Retrieves a list of validated endpoints for the current report.
	 *
	 * @since 3.0
	 *
	 * @param string $view_group Optional. View group for the type of endpoints to retrieve.
	 *                           Default empty (all valid endpoints).
	 * @return array List of validated endpoints by view view group.
	 */
	public function get_endpoints( $view_group = '' ) {
		if ( ! empty( $view_group ) && ! empty( $this->endpoints[ $view_group ] ) ) {

			return $this->endpoints[ $view_group ];

		} else {

			return $this->endpoints;

		}
	}

	/**
	 * Determines whether the report has any valid endpoints.
	 *
	 * @since 3.0
	 *
	 * @param string $view_group View group for the type of endpoints to check the existence of.
	 * @return bool True if there is at least one valid endpoint, otherwise false.
	 */
	public function has_endpoints( $view_group ) {
		$groups = $this->parse_view_groups();

		if ( array_key_exists( $view_group, $groups ) ) {
			$endpoints = $this->get_endpoints( $view_group );
		} else {
			$endpoints = array();
		}

		return ! empty( $endpoints );
	}

	/**
	 * Retrieves the capability needed to view the rendered report.
	 *
	 * @since 3.0
	 *
	 * @return string Report capability.
	 */
	public function get_capability() {
		return $this->capability;
	}

	/**
	 * Sets the capability needed for the current user to view the report.
	 *
	 * @since 3.0
	 *
	 * @param string $capability Capability.
	 */
	private function set_capability( $capability ) {
		$this->capability = sanitize_key( $capability );
	}

}
