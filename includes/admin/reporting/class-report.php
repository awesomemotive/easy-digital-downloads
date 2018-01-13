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
	private $valid_endpoints = array();

	/**
	 * Represents invalid endpoints, unavailable for display.
	 *
	 * @since 3.0
	 * @var array
	 */
	private $invalid_endpoints = array();

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

		$signal_keys = $this->parse_signal_keys();

		// Strip any invalid views based on signal key.
		foreach ( $report_endpoints as $signal => $endpoints ) {
			if ( ! array_key_exists( $signal, $signal_keys ) ) {
				throw new Utils\Exception( sprintf(
					'The \'%1$s\' signal key does not correspond to a known endpoint view type.',
					$signal
				) );

				unset( $report_endpoints[ $signal ] );
			}
		}

		// Loop through all passed endpoints using signal keys.
		foreach ( $report_endpoints as $signal => $endpoints ) {

			// Loop through all endpoints for each signal key and build endpoint objects.
			foreach ( $endpoints as $endpoint ) {

				if ( $endpoint instanceof \EDD\Admin\Reports\Data\Endpoint ) {

					if ( $endpoint->has_errors() ) {

						$this->invalid_endpoints[ $signal ][ $endpoint->get_id() ] = $endpoint->get_errors();

					} else {

						$this->valid_endpoints[ $signal ][ $endpoint->get_id() ] = $endpoint;

					}

				} elseif ( is_string( $endpoint ) ) {

					$endpoint = $registry->build_endpoint( $endpoint, $signal_keys[ $signal ] );

					if ( is_wp_error( $endpoint ) ) {

						$this->invalid_endpoints[ $signal ][ $endpoint->get_id() ] = $endpoint->get_errors();

					} else {

						$this->valid_endpoints[ $signal ][ $endpoint->get_id() ] = $endpoint;
					}

				} else {

					$this->invalid_endpoints[ $signal ][] = $endpoint;

				}
			}
		}

	}

	/**
	 * Parses the views whitelist to retrieve corresponding signal keys.
	 *
	 * @since 3.0
	 *
	 * @return array List of signal key and view slug pairs.
	 */
	public function parse_signal_keys() {
		$views = edd_reports_get_endpoint_views();

		$signal_keys = array();

		foreach ( $views as $view_type => $atts ) {
			if ( isset( $atts['signal_key'] ) ) {
				$signal_key = $atts['signal_key'];

				$signal_keys[ $signal_key ] = $view_type;
			}
		}

		return $signal_keys;
	}

	/**
	 * Validates an endpoint for rendering.
	 *
	 * @since 3.0
	 *
	 * @see \EDD\Admin\Reports\Report::$valid_endpoints
	 *
	 * @param string                           $signal_key Signal key corresponding to the endpoint view.
	 * @param \EDD\Admin\Reports\Data\Endpoint $endpoint   Endpoint object.
	 */
	public function validate_endpoint( $signal_key, $endpoint ) {
		$this->endpoints[ $signal_key ][ $endpoint->get_id() ][] = $endpoint;
	}

	/**
	 * Invalidates an endpoint for rendering.
	 *
	 * @since 3.0
	 *
	 * @see \EDD\Admin\Reports\Report::$errors
	 *
	 * @param string                  $signal_key Signal key corresponding to the endpoint view.
	 * @param Data\Endpoint|\WP_Error $endpoint   Endpoint or WP_Error object.
	 */
	public function invalidate_endpoint( $signal_key, $endpoint ) {
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
	 * Determines whether the endpoint has generated errors during instantiation.
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
	 * Retrieves any logged errors for the endpoint.
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
