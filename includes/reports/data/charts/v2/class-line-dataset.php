<?php
namespace EDD\Reports\Data\Charts\v2;

/**
 * Represents a manifestation of a ChartJS v2 line chart dataset in PHP form.
 *
 * @since 3.0.0
 */
class Line_Dataset extends Dataset {

	/**
	 * Represents the list of fields for a given dataset.
	 *
	 * @since 3.0.0
	 * @var   array
	 */
	protected $fields = array(
		'borderDash', 'borderDashOffset', 'borderCapStyle', 'borderJoinStyle',
		'cubicInterpolationMode', 'fill', 'lineTension', 'pointBackgroundColor',
		'pointBorderColor', 'pointBorderWidth', 'pointRadius', 'pointStyle',
		'pointHitRadius', 'pointHoverBackgroundColor', 'pointHoverBorderColor',
		'pointHoverBorderWidth', 'pointHoverRadius', 'showLine', 'spanGaps',
		'steppedLine',
	);


}
