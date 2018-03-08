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

		$options = empty( $args['options'] ) ? array() : $args['options'];

		// TODO handle validation, or just use the defaults?
		$this->set_options( $options );
	}

	/**
	 * Retrieves the graphing library options set for the current endpoint.
	 *
	 * @since 3.0
	 *
	 * @return array Options set for the current graph endpoint.
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Sets options for displaying the graph.
	 *
	 * @since 3.0
	 *
	 * @param array $args Options for displaying the graph via the graphing library.
	 */
	private function set_options( $args ) {
		$options = array(
			'y_mode'             => null,
			'x_mode'             => null,
			'y_decimals'         => 0,
			'x_decimals'         => 0,
			'y_position'         => 'right',
			'time_format'        => '%d/%b',
			'ticksize_unit'      => 'day',
			'ticksize_num'       => 1,
			'multiple_y_axes'    => false,
			'bgcolor'            => '#f9f9f9',
			'bordercolor'        => '#ccc',
			'color'              => '#bbb',
			'borderwidth'        => 2,
			'bars'               => false,
			'lines'              => true,
			'points'             => true,
			'additional_options' => '',
		);

		$this->options = wp_parse_args( $args, $options );
	}

}
