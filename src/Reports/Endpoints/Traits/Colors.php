<?php
/**
 * Colors Trait.
 *
 * @package   EDD\Reports\Endpoints\Traits
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.5.1
 */

namespace EDD\Reports\Endpoints\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Trait Colors
 *
 * @since 3.5.1
 */
trait Colors {

	/**
	 * The earnings color for line charts.
	 *
	 * @since 3.5.1
	 * @var string
	 */
	protected $earnings = 'rgba(24,126,244,0.75 )';

	/**
	 * The sales color for line charts.
	 *
	 * @since 3.5.1
	 * @var string
	 */
	protected $sales = 'rgba(252,108,18,0.75)';

	/**
	 * The base colors for the charts.
	 *
	 * @var array
	 */
	protected $colors = array(
		'green'     => '1,125,92',
		'blue'      => '1,96,135',
		'yellow'    => '240,195,60',
		'indigo'    => '75,0,130',
		'red'       => '214,54,56',
		'orange'    => '255,165,0',
		'pink'      => '255,192,203',
		'brown'     => '165,42,42',
		'cyan'      => '0,139,139',
		'lime'      => '50,205,50',
		'navy'      => '0,0,128',
		'olive'     => '128,128,0',
		'teal'      => '0,128,128',
		'maroon'    => '128,0,0',
		'violet'    => '238,130,238',
		'salmon'    => '250,128,114',
		'gold'      => '255,215,0',
		'turquoise' => '64,224,208',
		'coral'     => '255,127,80',
		'purple'    => '123,31,162',
		'khaki'     => '240,230,140',
		'plum'      => '221,160,221',
		'slate'     => '112,128,144',
		'crimson'   => '220,20,60',
	);

	/**
	 * The background color for the chart.
	 *
	 * @since 3.5.1
	 * @var string
	 */
	protected $background_color = 'rgba( 24, 126, 244, 0.05 )';

	/**
	 * Get the colors for a chart.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	private function get_colors() {
		$colors = array();
		foreach ( $this->colors as $color ) {
			$colors[] = "rgb({$color})";
		}

		return $colors;
	}
}
