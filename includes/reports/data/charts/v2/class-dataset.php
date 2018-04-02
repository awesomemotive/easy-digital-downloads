<?php
namespace EDD\Reports\Data\Charts\v2;

use EDD\Utils\Error_Logger_Interface as Error_Logger;

/**
 * Represents the manifestation of a ChartJS v2 dataset in PHP form.
 *
 * @since 3.0.0
 */
abstract class Dataset implements Error_Logger {

	/**
	 * Represents the list of fields for a given dataset.
	 *
	 * Should be defined by all sub-classes.
	 *
	 * @since 3.0.0
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
	 * Represents the list of global fields for all datasets.
	 *
	 * @since 3.0.0
	 * @var   array
	 */
	protected $global_fields = [
		'label', 'xAxisID', 'yAxisID', 'data',
		'backgroundColor', 'borderColor', 'borderWidth',
	];

	/**
	 * Sets up the dataset for population.
	 *
	 * @since 3.0
	 */
	public function __construct() {
		$this->setup_error_logger();
	}

	/**
	 * Retrieves the list of global fields.
	 *
	 * @since 3.0.0
	 *
	 * @return array List of global fields.
	 */
	public function get_global_fields() {
		return $this->global_fields;
	}

	/**
	 * Retrieves the list of local fields.
	 *
	 * @since 3.0.0
	 *
	 * @return array List of local fields.
	 */
	public function get_fields() {
		return $this->fields;
	}

	/**
	 * Retrieves the list of fields for the current dataset.
	 *
	 * Includes the global fields.
	 *
	 * @since 3.0.0
	 *
	 * @return array List of fields available to the dataset.
	 */
	public function get_all_fields() {
		$fields = array_merge( $this->get_global_fields(), $this->get_fields() );

		/**
		 * Filters the fields available to a ChartJS graph.
		 *
		 * @since 3.0.0
		 *
		 * @param array   $fields ChartJS fields (global and local).
		 * @param Dataset $this   Dataset instance.
		 */
		return apply_filters( 'edd_reports_chart_fields', $fields, $this );
	}

	/**
	 * Determines whether the dataset has generated errors during instantiation.
	 *
	 * @since 3.0
	 *
	 * @return bool True if errors have been logged, otherwise false.
	 */
	public function has_errors() {
		if ( version_compare( $GLOBALS['wp_version'], '5.0', '>=' ) ) {
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
