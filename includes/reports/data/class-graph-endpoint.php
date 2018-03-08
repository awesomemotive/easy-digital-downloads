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

if ( ! class_exists( 'EDD\Reports\Data\Chart_Endpoint' ) ) {
	require_once EDD_PLUGIN_DIR . 'includes/reports/data/class-chart-endpoint.php';
}

/**
 * Handler for building a graph endpoint in the Reports API.
 *
 * @since 3.0
 */
final class Graph_Endpoint extends Chart_Endpoint {

	/**
	 * Endpoint view (type).
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $view = 'graph';

	/**
	 * Represents the options used by the graphing library.
	 *
	 * @since 3.0
	 * @var   array
	 */
	private $options = array();

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
