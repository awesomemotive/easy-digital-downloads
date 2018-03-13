<?php
namespace EDD\Reports\Data\Charts\v2;

/**
 * Represents the manifestation of a ChartJS dataset in PHP form.
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
	 * Retrieves the list of fields for the current dataset.
	 *
	 * Includes the global fields.
	 *
	 * @since 3.0.0
	 *
	 * @return array List of fields available to the dataset.
	 */
	public function get_fields() {
		return array_merge( $this->get_global_fields(), $this->fields );
	}

}
