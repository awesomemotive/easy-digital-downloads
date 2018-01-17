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
namespace EDD\Admin\Reports\Data;

use EDD\Utils;

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
	 * @param array $report Report record from the registry.
	 */
	public function __construct( $report ) {
		$this->errors = new \WP_Error();

		$this->set_props( $report );

		if ( ! empty( $report['endpoints'] ) ) {
			try {

				$this->build_endpoints( $report['endpoints'] );

			} catch ( \EDD_Exception $exception ) {

				edd_debug_log_exception( $exception );

			}
		} else{

			$this->errors->add( 'missing_endpoints', 'No endpoints were defined for the report.', $report );
		}

	}

	/**
	 * Sets a variety of properties needed to render the report.
	 *
	 * @since 3.0
	 *
	 * @param array $report Report record from the registry.
	 */
	protected function set_props( $report ) {
		if ( ! empty( $report['id'] ) ) {

			$this->set_id( $report['id'] );

		} else {

			$this->errors->add( 'missing_report_id', 'The report_id is missing.' );

		}

		if ( ! empty( $report['label'] ) ) {

			$this->set_label( $report['label'] );

		} else {

			$this->errors->add( 'missing_report_label', 'The report label is missing.' );

		}
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
	 * Sets the report ID.
	 *
	 * @since 3.0
	 *
	 * @param string $report_id Report ID.
	 */
	private function set_id( $report_id ) {
		$this->report_id = $report_id;
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

	/**
	 * Sets the global report label.
	 *
	 * @since 3.0
	 *
	 * @param string $label Report label.
	 */
	private function set_label( $label ) {
		$this->label = $label;
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

				$endpoint = $registry->build_endpoint( $endpoint, $view_groups[ $group ] );

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
	 *
	 * @return \WP_Error Error object for the report.
	 */
	public function get_errors() {
		return $this->errors;
	}

}
