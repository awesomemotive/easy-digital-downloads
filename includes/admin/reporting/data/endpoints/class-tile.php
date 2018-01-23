<?php
/**
 * Reports API - Tile Endpoint Handler
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Admin\Reports\Data\Endpoints;

use EDD\Admin\Reports\Data;

/**
 * Handler for building a tile endpoint in the Reports API.
 *
 * @since 3.0
 */
class Tile extends Data\Endpoint {

	/**
	 * Endpoint view (type).
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $view = 'tile';

	/**
	 * Constructs the tile endpoint object.
	 *
	 * @since 3.0
	 *
	 * @param array $args Arguments for instantiating the endpoint as retrieved from the endpoint registry.
	 */
	public function __construct( $args ) {
		parent::__construct( $args );

		if ( ! empty( $args['views'][ $this->get_view() ] ) ) {
			$this->set_display_args( $args['views'][ $this->get_view() ] );
		}
	}

}
