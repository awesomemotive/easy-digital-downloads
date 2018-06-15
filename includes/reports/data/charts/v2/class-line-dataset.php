<?php
/**
 * Reports API - LINE Dataset Class
 *
 * @package     EDD
 * @subpackage  Reports\Data\Charts
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports\Data\Charts\v2;

/**
 * Represents a manifestation of a ChartJS v2 line chart dataset in PHP form.
 *
 * @since 3.0
 *
 * @see Dataset
 * @see Manifest
 */
class Line_Dataset extends Dataset {

	/**
	 * Represents the list of fields for a given dataset.
	 *
	 * @since 3.0
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
