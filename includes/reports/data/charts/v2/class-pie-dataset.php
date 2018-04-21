<?php
namespace EDD\Reports\Data\Charts\v2;

/**
 * Represents a manifestation of a ChartJS v2 pie chart dataset in PHP form.
 *
 * @since 3.0
 */
class Pie_Dataset extends Dataset {

	/**
	 * Represents the list of fields for a given dataset.
	 *
	 * @since 3.0
	 * @var   array
	 */
	protected $fields = array(
		'hoverBackgroundColor', 'hoverBorderColor',
		'hoverBorderWidth'
	);

}
