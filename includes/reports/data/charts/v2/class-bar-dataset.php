<?php
/**
 * Reports API - Bar Dataset class
 *
 * @package     EDD
 * @subpackage  Reports\Data\Charts
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
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
		'borderSkipped', 'hoverBackgroundColor',
		'hoverBorderColor', 'hoverBorderWidth'
	);

}
