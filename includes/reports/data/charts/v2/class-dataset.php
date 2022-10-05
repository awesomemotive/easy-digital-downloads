<?php
/**
 * Reports API - Dataset class
 *
 * @package     EDD
 * @subpackage  Reports\Data\Charts
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports\Data\Charts\v2;

use EDD\Reports\Data\Chart_Endpoint;
use EDD\Utils\Error_Logger_Interface as Error_Logger;

/**
 * Represents the manifestation of a ChartJS v2 dataset in PHP form.
 *
 * @since 3.0
 *
 * @see Error_Logger_Interface
 */
abstract class Dataset implements Error_Logger {

	/**
	 * The ID associated with the dataset.
	 *
	 * Primarily used for locating associated data via the endpoint's data callback.
	 *
	 * @since 3.0
	 * @var   string
	 */
	private $dataset_id;

	/**
	 * Represents the list of fields for a given dataset.
	 *
	 * Should be defined by all sub-classes.
	 *
	 * @since 3.0
	 * @var   array
	 */
	protected $fields = array();

	/**
	 * Holds errors related to instantiating the object.
	 *
	 * @since 3.0
	 * @var   \WP_Error
	 */
	protected $errors;

	/**
	 * Raw dataset options and data.
	 *
	 * @since 3.0
	 * @var   array
	 */
	private $options = array();

	/**
	 * Represents the chart endpoint the dataset is associated with.
	 *
	 * @since 3.0
	 * @var   Chart_Endpoint
	 */
	private $endpoint;

	/**
	 * Represents the list of global fields for all datasets.
	 *
	 * @since 3.0
	 * @var   array
	 */
	protected $global_fields = array(
		'label', 'xAxisID', 'yAxisID', 'data',
		'backgroundColor', 'borderColor', 'borderWidth',
	);

	/**
	 * Sets up the dataset for population.
	 *
	 * @since 3.0
	 *
	 * @param string         $dataset_id Dataset ID.
	 * @param Chart_Endpoint $endpoint Chart endpoint object.
	 * @param array $options Dataset options.
	 */
	public function __construct( $dataset_id, $endpoint, $options ) {
		$this->setup_error_logger();

		$this->set_id( $dataset_id );
		$this->set_endpoint( $endpoint );
		$this->validate( $options );
	}

	/**
	 * Retrieves the dataset ID.
	 *
	 * @since 3.0
	 *
	 * @return string Dataset ID.
	 */
	public function get_id() {
		return $this->dataset_id;
	}

	/**
	 * Sets the dataset ID.
	 *
	 * @since 3.0
	 *
	 * @param string $dataset_id Dataset ID
	 */
	private function set_id( $dataset_id ) {
		$this->dataset_id = sanitize_key( $dataset_id );
	}

	/**
	 * Sets the chart endpoint object.
	 *
	 * @since 3.0
	 *
	 * @param EDD\Reports\Data\Chart_Endpoint $endpoint Chart_Endpoint object.
	 */
	private function set_endpoint( $endpoint ) {
		$this->endpoint = $endpoint;
	}

	/**
	 * Retrieves the raw dataset options.
	 *
	 * @since 3.0
	 *
	 * @return array Dataset options (raw).
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Retrieves the chart endpoint object for this dataset.
	 *
	 * @since 3.0
	 *
	 * @return Chart_Endpoint Chart endpoint.
	 */
	public function get_endpoint() {
		return $this->endpoint;
	}

	/**
	 * Retrieves the list of local fields.
	 *
	 * @since 3.0
	 *
	 * @return array List of local fields.
	 */
	public function get_fields() {
		return $this->fields;
	}

	/**
	 * Retrieves the list of global fields.
	 *
	 * @since 3.0
	 *
	 * @return array List of global fields.
	 */
	public function get_global_fields() {
		return $this->global_fields;
	}

	/**
	 * Retrieves the list of fields for the current dataset.
	 *
	 * Includes the global fields.
	 *
	 * @since 3.0
	 *
	 * @return array List of fields available to the dataset.
	 */
	public function get_all_fields() {
		$fields = array_merge( $this->get_global_fields(), $this->get_fields() );

		/**
		 * Filters the fields available to a ChartJS graph.
		 *
		 * @since 3.0
		 *
		 * @param array   $fields ChartJS fields (global and local).
		 * @param Dataset $this   Dataset instance.
		 */
		return apply_filters( 'edd_reports_chart_fields', $fields, $this );
	}

	/**
	 * Attempts to retrieve data associated with the current dataset.
	 *
	 * @since 3.0
	 *
	 * @return mixed Data associated with the current dataset.
	 */
	public function get_data() {
		return $this->get_endpoint()->get_data_by_set( $this->get_id() );
	}

	/**
	 * Performs validation on incoming dataset options.
	 *
	 * @since 3.0
	 *
	 * @param array $options Dataset options.
	 */
	public function validate( $options ) {
		$fields = $this->get_all_fields();

		// Strip invalid options.
		foreach ( $options as $key => $value ) {
			if ( ! in_array( $key, $fields, true ) ) {
				unset( $options[ $key ] );
			}
		}

		$data      = $this->get_data();
		$processed = array();

		if ( ! empty( $data ) ) {

			$options['data'] = $this->parse_data_for_output( $data );

			$this->options = $options;

		} else {

			$message = sprintf( 'The data for the \'%1$s\' dataset for the \'%2$s\' endpoint in the \'%3$s\' report is missing or invalid.',
				$this->get_id(),
				$this->get_endpoint()->get_id(),
				$this->get_endpoint()->get_report_id()
			);

			$this->errors->add( 'missing_chart_data', $message, $data );
		}
	}

	/**
	 * Parses the dataset data for output via JS.
	 *
	 * @since 3.0
	 *
	 * @param array $data Dataset data.
	 * @return array Processed data.
	 */
	public function parse_data_for_output( $data ) {

		if ( $this instanceof Pie_Dataset ) {

			$processed = $data;

		} else {

			foreach ( $data as $key => $values ) {
				if ( is_array( $values ) && isset( $values[1] ) ) {
					$processed[ $key ] = array(
						'x' => $this->adjust_time_string( $values[0] ),
						'y' => $values[1],
					);
				} else {
					$processed[ $key ] = array(
						'x' => $this->adjust_time_string( $values ),
					);
				}
			}
		}

		return $processed;
	}

	/**
	 * Given a date as a string or numeric timestamp, adjust it for a specific timezone.
	 *
	 * This allows the points on the graph to line up with the ticks, which are already adjusted.
	 *
	 * @since 3.1
	 *
	 * @param string|int $time_string The time string to possibly adjust.
	 *
	 * @return string If a timestamp, it's adjusted for the timezone of the store.
	 */
	private function adjust_time_string( $time_string ) {
		if ( is_numeric( $time_string ) ) {
			$timezone      = new \DateTimeZone( edd_get_timezone_id() );
			$date_on_chart = new \DateTime( '@' . $time_string );

			$time_string = $date_on_chart->setTimeZone( $timezone )->format( 'Y-m-d H:i:s' );
		}

		return $time_string;
	}

	/**
	 * Determines whether the dataset has generated errors during instantiation.
	 *
	 * @since 3.0
	 *
	 * @return bool True if errors have been logged, otherwise false.
	 */
	public function has_errors() {
		if ( method_exists( $this->errors, 'has_errors' ) ) {
			return $this->errors->has_errors();
		} else {
			$errors = $this->errors->get_error_codes();

			return ! empty( $errors );
		}
	}

	/**
	 * Retrieves any logged errors for the dataset.
	 *
	 * @since 3.0
	 *
	 * @return \WP_Error WP_Error object for the current dataset.
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Sets up the WP_Error instance.
	 *
	 * @since 3.0
	 */
	public function setup_error_logger() {
		if ( ! isset( $this->errors ) ) {
			$this->errors = new \WP_Error();
		}
	}

}
