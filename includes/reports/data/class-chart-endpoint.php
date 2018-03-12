<?php
/**
 * Reports API - Chart Endpoint Handler
 *
 * @package     EDD
 * @subpackage  Reports
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports\Data;

/**
 * Handler for building a chart endpoint in the Reports API.
 *
 * @since 3.0
 */
class Chart_Endpoint extends Endpoint {

	/**
	 * Endpoint view (type).
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $view = 'chart';

	/**
	 * Sets up the chart endpoint.
	 *
	 * @since 3.0
	 *
	 * @param array $args Chart endpoint attributes.
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

	/**
	 * Retrieves the value of a graph option if set.
	 *
	 * @since 3.0.0
	 *
	 * @param string $key Option key to retrieve a value for.
	 * @return mixed Value of the option key if set, otherwise an empty string.
	 */
	public function get( $key ) {
		if ( isset( $this->options[ $key ] ) ) {
			$value = $this->options[ $key ];
		} else {
			$value = '';
		}

		return $value;
	}

}
