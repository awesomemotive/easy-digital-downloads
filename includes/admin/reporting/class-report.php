<?php
/**
 * Reports API - Report object
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Admin\Reports;

use EDD\Utils;
use EDD\Utils\Exceptions;

/**
 * Represents an encapsulated report for the Reports API.
 *
 * @since 3.0
 */
final class Report {

	/**
	 * Report ID.
	 *
	 * Will only be set if built using a reports registry entry.
	 *
	 * @since 3.0
	 * @var   string
	 */
	private $report_id;

	/**
	 * Endpoint label.
	 *
	 * @since 3.0
	 * @var   string
	 */
	private $label;

	/**
	 * Represents filters available to the report.
	 *
	 * @since 3.0
	 */
	public $filters = array();

	/**
	 * Represents valid endpoints available for display.
	 *
	 * @since 3.0
	 * @var array
	 */
	private $endpoints = array();

	/**
	 * Holds errors related to instantiating the report object.
	 *
	 * @since 3.0
	 * @var   \WP_Error
	 */
	private $errors;

	/**
	 * Constructs the report object.
	 *
	 * @since 3.0
	 *
	 * @param array  $report Optional. Report record from the registry. Default empty array.
	 */
	public function __construct( $report = array() ) {
		$this->errors = new \WP_Error();

		$this->set_props( $report );

		if ( ! empty( $report['endpoints'] ) ) {
			try {

				$this->build_endpoints( $report['endpoints'] );

			} catch ( \EDD_Exception $exception ) {

				edd_debug_log_exception( $exception );

			}
		}

	}

	/**
	 * Sets a variety of properties needed to render the report.
	 *
	 * @since 3.0
	 *
	 * @param array $report Report record from the registry.
	 */
	public function set_props( $report ) {
		if ( ! empty( $report ) ) {

			if ( ! empty( $report['id'] ) ) {
				$this->report_id = $report['id'];
			} else {
				$this->errors->add( 'missing_report_id', 'The report_id is missing.' );
			}

			if ( ! empty( $report['label'] ) ) {
				$this->label = $report['label'];
			} else {
				$this->errors->add( 'missing_label', 'The report label is missing.' );
			}

			if ( ! empty( $report['filters'] ) ) {
				$this->filters = $report['filters'];
			}
		}
	}

	/**
	 * Builds Endpoint objects for each endpoint in the report.
	 *
	 * @since 3.0
	 *
	 * @throws \EDD_Exception
	 *
	 * @param array $endpoints Endpoints, keyed by view type.
	 */
	public function build_endpoints( $report_endpoints ) {
		/** @var \EDD\Admin\Reports\Data\Endpoint_Registry|\WP_Error $registry */
		$registry = EDD()->utils->get_registry( 'reports:endpoints' );

		if ( is_wp_error( $registry ) ) {

			throw new Utils\Exception( $registry->get_error_message() );

			return;
		}

		$view_groups = $this->parse_view_groups();

		// Loop through all passed endpoints using view groups.
		foreach ( $report_endpoints as $group => $endpoints ) {

			// Strip any invalid views based on view group.
			if ( ! array_key_exists( $group, $view_groups ) ) {
				throw new Utils\Exception( sprintf(
					'The \'%1$s\' view group does not correspond to a known endpoint view type.',
					$group
				) );

				continue;
			}

			// Loop through all endpoints for each view group and build endpoint objects.
			foreach ( $endpoints as $endpoint ) {

				$endpoint = $registry->build_endpoint( $endpoint, $view_groups[ $group ] );

				if ( is_wp_error( $endpoint ) || ( ! is_wp_error( $endpoint ) && $endpoint->has_errors() ) ) {

					$this->invalidate_endpoint( $group, $endpoint );

				} else {

					$this->validate_endpoint( $group, $endpoint );

				}

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
			if ( isset( $atts['view_group'] ) ) {
				$view_group = $atts['view_group'];

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
	 * @see \EDD\Admin\Reports\Report::$valid_endpoints
	 *
	 * @param string                           $view_group view group corresponding to the endpoint view.
	 * @param \EDD\Admin\Reports\Data\Endpoint $endpoint   Endpoint object.
	 */
	public function validate_endpoint( $view_group, $endpoint ) {
		$this->endpoints[ $view_group ][ $endpoint->get_id() ][] = $endpoint;
	}

	/**
	 * Invalidates an endpoint for rendering.
	 *
	 * @since 3.0
	 *
	 * @see \EDD\Admin\Reports\Report::$errors
	 *
	 * @param string                  $view_group view group corresponding to the endpoint view.
	 * @param Data\Endpoint|\WP_Error $endpoint   Endpoint or WP_Error object.
	 */
	public function invalidate_endpoint( $view_group, $endpoint ) {
		if ( is_wp_error( $endpoint ) ) {
			$this->errors->add(
				$endpoint->get_error_code(),
				$endpoint->get_error_message(),
				$endpoint->get_error_data()
			);
		} else {
			$message = sprintf( 'The \'%1$s\' endpoint is invalid.', $endpoint->get_id() );

			$this->errors->add( 'invalid_endpoint', $message, $endpoint->get_errors() );
		}
	}

	/**
	 * Retrieves the master list of validated endpoints for the current report.
	 *
	 * @since 3.0
	 *
	 * @return array List of validated endpoints by view signal key.
	 */
	public function get_valid_endpoints() {
		return $this->endpoints;
	}

	/**
	 * Determines whether the report has generated errors during instantiation.
	 *
	 * @since 3.0
	 *
	 * @return bool True if errors have been logged, otherwise false.
	 */
	public function has_errors() {
		$errors = $this->errors->get_error_codes();

		return empty( $errors ) ? false : true;
	}

	/**
	 * Retrieves any logged errors for the report.
	 *
	 * @since 3.0
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Retrieves the report ID.
	 *
	 * @since 3.0
	 *
	 * @return string Report ID.
	 */
	public function get_id() {
		return $this->report_id;
	}

	/**
	 * Retrieves the global label for the report.
	 *
	 * @since 3.0
	 *
	 * @return string Report label.
	 */
	public function get_label() {
		return $this->label;
	}

}
