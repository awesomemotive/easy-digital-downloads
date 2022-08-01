<?php
/**
 * Reports API - Tile Endpoint Handler
 *
 * @package     EDD
 * @subpackage  Reports
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports\Data;

/**
 * Handler for building a tile endpoint in the Reports API.
 *
 * @since 3.0
 */
final class Tile_Endpoint extends Endpoint {

	/**
	 * Endpoint view (type).
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $view = 'tile';

	/**
	 * Display logic for the current tile endpoint.
	 *
	 * Tiles are rendered via meta boxes, so this method is deliberately empty.
	 *
	 * @since 3.0
	 */
	public function display() {
		$classnames = array(
			'edd-reports-tile',
		);

		echo '<div id="' . esc_attr( $this->get_id() ) . '" class="' . esc_attr( implode( ' ', $classnames ) ) . '">';
			parent::display();
		echo '</div>';
	}

}
