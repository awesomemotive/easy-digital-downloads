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
	 * @param array $options Options for displaying the graph via the graphing library.
	 */
	protected function set_options( $options ) {
		$this->options = $options;
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

	/**
	 * Builds and outputs the graph JS to the page.
	 *
	 * @since 3.0.0
	 */
	public function build_graph() {}

}
