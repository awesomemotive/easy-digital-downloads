<?php
/**
 * Reports API - Report object
 *
 * @package     EDD
 * @subpackage  Reports
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports\Data;

use EDD\Reports;
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
	 * @var   Endpoint[]
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
	 * Represents the display callback used to output the report.
	 *
	 * @since 3.0
	 * @var   callable
	 */
	private $display_callback = '\EDD\Reports\default_display_report';

	/**
	 * Represents filters the report has opted into.
	 *
	 * @since 3.0
	 * @var   array
	 */
	private $filters = array();

	/**
	 * Represents the group to display the report under.
	 *
	 * @since 3.0
	 * @var string
	 */
	private $group;

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
		} else {
			$this->errors->add( 'missing_endpoints', 'No endpoints are defined for the report.', $args );
		}

		if ( ! empty( $args['capability'] ) ) {
			$this->set_capability( $args['capability'] );
		} else {
			$this->errors->add( 'missing_capability', 'No capability is defined for the report.', $args );
		}

		if ( ! empty( $args['display_callback'] ) ) {
			$this->set_display_callback( $args['display_callback'] );
		}

		if ( ! empty( $args['filters'] ) ) {
			$this->set_filters( $args['filters'] );
		}

		if ( ! empty( $args['group'] ) ) {
			$this->set_group( $args['group'] );
		}
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
		/** @var \EDD\Reports\Data\Endpoint_Registry|\WP_Error $registry */
		$registry = EDD()->utils->get_registry( 'reports:endpoints' );

		if ( is_wp_error( $registry ) ) {
			throw new Utils\Exception( $registry->get_error_message() );
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
		$views = Reports\get_endpoint_views();

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
	 * @see \EDD\Reports\Data\Report::$valid_endpoints
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

		// Valid.
		} else {
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
	 * @return Endpoint[] List of validated endpoints by view view group.
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
	 * @param string $view_group Optional. View group for the type of endpoints
	 *                           to check the existence of. Default empty.
	 * @return bool True if there is at least one valid endpoint, otherwise false.
	 */
	public function has_endpoints( $view_group = '' ) {
		if ( ! empty( $view_group ) ) {
			$has_endpoints = ! empty( $this->endpoints[ $view_group ] );
		} else {
			$has_endpoints = ! empty( $this->endpoints );
		}

		return $has_endpoints;
	}

	/**
	 * Retrieves a given endpoint by view group.
	 *
	 * @since 3.0
	 *
	 * @param string $endpoint_id Endpoint ID.
	 * @param string $view_group  Endpoint view group.
	 * @return Endpoint|\WP_Error Endpoint object if it exists, otherwise a WP_Error object.
	 */
	public function get_endpoint( $endpoint_id, $view_group ) {
		$endpoints = $this->get_endpoints( $view_group );

		if ( isset( $endpoints[ $endpoint_id ] ) ) {
			$endpoint = $endpoints[ $endpoint_id ];

		} else {
			$message = sprintf( 'The \'%1$s\' endpoint does not exist for the \'%2$s\' view group in the \'%3$s\' report.',
				$endpoint_id,
				$view_group,
				$this->get_id()
			);

			$endpoint = new \WP_Error( 'invalid_report_endpoint', $message );
		}

		return $endpoint;
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

	/**
	 * Displays the endpoint based on the view (type).
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function display() {
		$callback = $this->get_display_callback();

		if ( is_callable( $callback ) ) {
			call_user_func( $callback, $this );
		}
	}

	/**
	 * Retrieves the current report's display callback.
	 *
	 * @since 3.0
	 *
	 * @return callable Display callback.
	 */
	public function get_display_callback() {
		return $this->display_callback;
	}

	/**
	 * Sets the display callback used to render the report.
	 *
	 * @since 3.0
	 *
	 * @param callable $callback Display callback.
	 */
	private function set_display_callback( $callback ) {
		if ( is_callable( $callback ) ) {
			$this->display_callback = $callback;

		} else {
			$this->flag_invalid_report_arg_type( 'display_callback', 'callable' );
		}
	}

	/**
	 * Retrieves the list of filters registered for use with this report.
	 *
	 * @since 3.0
	 *
	 * @return array List of support filters.
	 */
	public function get_filters() {
		return $this->filters;
	}

	/**
	 * Sets the endpoint filters supported by the current report's endpoints.
	 *
	 * @since 3.0
	 *
	 * @param array $filters Filters to set for this report.
	 */
	private function set_filters( $filters ) {

		foreach ( $filters as $filter ) {
			if ( Reports\validate_filter( $filter ) ) {
				$this->filters[] = $filter;

			} else {
				$message = sprintf( 'The \'%1$s\' filter for the \'%2$s\' report is invalid.',
					$filter,
					$this->get_id()
				);

				$this->errors->add( 'invalid_report_filter', $message, $this );
			}
		}

		$this->filters = array_unique( $this->filters );
	}

	/**
	 * Retrieves the display group for the current report.
	 *
	 * @since 3.0
	 *
	 * @return string Display group. Default 'reports'.
	 */
	public function get_group() {
		return $this->group;
	}

	/**
	 * Sets the display group for the current report.
	 *
	 * @since 3.0
	 *
	 * @param string $group Report display group.
	 */
	private function set_group( $group ) {
		$this->group = sanitize_key( $group );
	}

	/**
	 * Displays an entire group of an endpoints view.
	 *
	 * @since 3.0
	 *
	 * @param string $view_group Endpoints view group.
	 * @return void
	 */
	public function display_endpoint_group( $view_group ) {
		$groups = $this->parse_view_groups();

		if ( array_key_exists( $view_group, $groups ) ) {
			$callback = Reports\get_endpoint_group_callback( $groups[ $view_group ] );

			if ( is_callable( $callback ) ) {
				call_user_func( $callback, $this );
			}
		}
	}

	/**
	 * Flags an error for an invalid report argument type.
	 *
	 * @since 3.0
	 *
	 * @param string $argument Argument name.
	 */
	protected function flag_invalid_report_arg_type( $argument, $expected_type ) {
		$message = sprintf( 'The \'%1$s\' argument must be of type %2$s for the \'%3$s\' report.',
			$argument,
			$expected_type,
			$this->get_id()
		);

		$this->errors->add( 'invalid_report_arg_type', $message, array(
			'report_id' => $this->get_id(),
		) );
	}
}
