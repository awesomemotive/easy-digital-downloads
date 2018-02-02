<?php
/**
 * Reports API - Graph Endpoint Handler
 *
 * @package     EDD
 * @subpackage  Reports
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports\Data;

/**
 * Handler for building a graph endpoint in the Reports API.
 *
 * @since 3.0
 */
final class Graph_Endpoint extends Endpoint {

	/**
	 * Endpoint view (type).
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $view = 'graph';

	/**
	 * Sets up the graph endpoint.
	 *
	 * @since 3.0
	 *
	 * @param array $args Graph endpoint attributes.
	 */
	public function __construct( array $args ) {
		parent::__construct( $args );
	}

}
