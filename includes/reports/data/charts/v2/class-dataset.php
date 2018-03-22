<?php
namespace EDD\Reports\Data\Charts\v2;

/**
 * Represents the manifestation of a ChartJS v2 dataset in PHP form.
 *
 * @since 3.0.0
 */
abstract class Dataset {

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

}
