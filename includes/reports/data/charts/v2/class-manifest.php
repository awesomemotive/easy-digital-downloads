<?php
namespace EDD\Reports\Data\Charts\v2;

use EDD\Reports\Data\Chart_Endpoint;
use EDD\Utils\Error_Logger_Interface as Error_Logger;

/**
 * Represents a manifestation of a ChartJS v2 object's attributes in PHP form.
 *
 * Primarily used to simplify translating server-side arguments into client-side ones.
 *
 * @since 3.0.0
 *
 * @see Hydrator
 */
class Manifest implements Error_Logger {

	/**
	 * Represents the chart type to be manifested.
	 *
	 * @since 3.0
	 * @var   string
	 */
	private $type;

	/**
	 * Represents the unfiltered chart options for the manifest.
	 *
	 * @since 3.0
	 * @var   array
	 */
	private $options = array();

	/**
	 * Datasets associated with the current graph.
	 *
	 * @since 3.0
	 * @var   array
	 */
	private $datasets = array();

	/**
	 * Represents the current Chart_Endpoint instance.
	 *
	 * @since 3.0
	 * @var   Chart_Endpoint
	 */
	private $endpoint;

	/**
	 * Holds errors related to instantiating the manifest.
	 *
	 * @since 3.0
	 * @var   \WP_Error
	 */
	protected $errors;

	/**
	 * Sets up the manifest.
	 *
	 * @since 3.0
	 *
	 * @param string         $type    Type of chart manifest.
	 * @param Chart_Endpoint $endpoint Chart endpoint.
	 * @param array          $options Array of options to populate the manifest with.
	 */
	public function __construct( $type, $endpoint, $options ) {
		$this->setup_error_logger();

		$this->set_type( $type );
		$this->set_endpoint( $endpoint );
		$this->set_options( $options );
	}

	/**
	 * Retrieves the chart type.
	 *
	 * @since 3.0
	 *
	 * @return string Chart type.
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Sets the chart type for the manifest.
	 *
	 * @since 3.0
	 *
	 * @param string $type Chart type to be manifested.
	 */
	private function set_type( $type ) {
		$this->type = sanitize_key( $type );
	}

	/**
	 * Retrieves the chart endpoint object for this manifest.
	 *
	 * @since 3.0
	 *
	 * @return Chart_Endpoint Chart endpoint.
	 */
	public function get_endpoint() {
		return $this->endpoint;
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
	 * Stores the unfiltered chart options for later access.
	 *
	 * @since 3.0
	 *
	 * @param array $options Chart options and datasets.
	 */
	private function set_options( $options ) {
		$this->options = $options;

		$datasets = $this->endpoint->get_data();

		if ( ! empty( $datasets ) && is_array( $datasets ) ) {
			foreach ( $datasets as $axis => $data ) {
				$this->add_dataset( $axis, $data );
			}
		} else {

			$message = sprintf( 'The %s endpoint has no data.', $this->get_endpoint()->get_id() );

			$this->errors->add( 'missing_chart_data', $message, $this->get_endpoint() );

		}
	}

	/**
	 * Retrieves the manifest datasets.
	 *
	 * @since 3.0
	 *
	 * @return array Datasets for this chart if any are defined, otherwise an empty array.
	 */
	public function get_datasets() {
		return $this->datasets;
	}

	/**
	 * Determines whether the current chart manifest contains any datasets.
	 *
	 * @since 3.0
	 *
	 * @return bool True if there are datasets, otherwise false.
	 */
	public function has_datasets() {
		return ! empty( $this->get_datasets() );
	}

	/**
	 * Adds a dataset.
	 *
	 * @since 3.0
	 *
	 * @param string $axis Axis to associate the dataset with.
	 * @param array  $data Dataset data
	 * @return bool True if the dataset was added, otherwise false.
	 */
	public function add_dataset( $axis, $data ) {
		$handler = $this->get_type_handler();

		if ( ! empty( $handler ) && class_exists( $handler ) ) {
			$dataset = new $handler( $data );

			if ( ! $dataset->has_errors() ) {
				$this->datasets[ $axis ] = $dataset;

				return true;
			}

		}

		return false;
	}

	/**
	 * Retrieves the type handler for the current dataset type.
	 *
	 * @since 3.0
	 *
	 * @return string Dataset type handler.
	 */
	public function get_type_handler() {
		$handler = '';

		switch( $this->get_type() ) {

			case 'bar':
				$handler = 'EDD\Reports\Data\Charts\v2\Bar_Dataset';
				break;

			case 'line':
			default:
				$handler = 'EDD\Reports\Data\Charts\v2\Line_Dataset';
				break;


		}
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
