<?php
/**
 * Reports API - Bar Dataset class
 *
 * @package     EDD\Reports\Data\Charts\v2
 * @copyright   Copyright (c) 2018, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

namespace EDD\Reports\Data\Charts\v2;

/**
 * Represents a manifestation of a ChartJS v2 bar chart dataset in PHP form.
 *
 * @since 3.0
 *
 * @see Dataset
 * @see Manifest
 */
class Bar_Dataset extends Dataset {

	/**
	 * Represents the list of fields for a given dataset.
	 *
	 * @since 3.0
	 * @var   array
	 */
	protected $fields = array(
		'borderSkipped',
		'hoverBackgroundColor',
		'hoverBorderColor',
		'hoverBorderWidth',
	);

	/**
	 * Parses the dataset data for output via JS.
	 *
	 * Overrides parent to handle both time-based and static bar charts.
	 *
	 * @since 3.6.5
	 *
	 * @param array $data Dataset data.
	 * @return array Processed data.
	 */
	public function parse_data_for_output( $data ) {
		// Check if this is a static bar chart (simple numeric array) or time-based.
		// If the first element is numeric (not an array), it's a static chart.
		if ( ! empty( $data ) && ! is_array( reset( $data ) ) ) {
			// Static bar chart - return data as-is.
			return $data;
		}

		// Time-based bar chart - use parent's time-series parsing.
		return parent::parse_data_for_output( $data );
	}
}
