<?php
/**
 * Reports API - Endpoint View object
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Admin\Reports\Data;

/**
 * Represents a data endpoint for the Reports API.
 *
 * @since 3.0
 */
class Endpoint extends Base_Object {

	/**
	 * Endpoint view (type).
	 *
	 * @since 3.0
	 * @var   string
	 */
	private $view;

	/**
	 * Represents the callback used to retrieve data based on the set view type.
	 *
	 * @since 3.0
	 * @var   callable
	 */
	private $data_callback;

	/**
	 * Represents the display callback based on the set view type.
	 *
	 * @since 3.0
	 * @var   callable
	 */
	private $display_callback;

	/**
	 * Represents the display arguments (passed to the display callback) for the view (type).
	 *
	 * @since 3.0
	 * @var   array
	 */
	private $display_args = array();

	/**
	 * Constructs the endpoint object.
	 *
	 * Note: The Endpoint object is intended for use in conjunction with entries coming
	 * directly from the endpoint registry, which have already been validated. If the choice
	 * is made to build an Endpoint object "on-the-fly", care should be taken to ensure all
	 * expected values are passed to avoid errors that will prevent prop rendering.
	 *
	 * @since 3.0
	 *
	 * @param array $args Arguments for instantiating the endpoint as retrieved from the endpoint registry.
	 */
	public function __construct( $args ) {
		$this->errors = new \WP_Error();

		if ( empty( $args['view'] ) ) {
			$this->errors->add( 'missing_endpoint_view', 'The view argument must be defined when instantiating an Endpoint object.', $args );
		} else {
			$this->set_view( $args['view'] );
		}

		if ( empty( $args['atts'] ) ) {
			$this->errors->add( 'missing_endpoint_attributes', 'An array of attributes must be supplied when instantiating an Endpoint object.', $args );
		} else {
			$this->set_props( $args['atts'] );
			$this->set_display_props( $args['atts'] );
		}
	}

	/**
	 * Displays the endpoint based on the view (type).
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function display() {
		$callback = $this->get_display_callback();

		if ( is_callable( $callback ) ) {
			call_user_func_array( $callback, array(
				'data' => $this->get_data(),
				'args' => $this->get_display_args(),
			) );
		}
	}

	/**
	 * Retrieves the data for the endpoint view (type).
	 *
	 * @since 3.0
	 *
	 * @return mixed Endpoint data.
	 */
	public function get_data() {
		$data_callback = $this->get_data_callback();

		if ( is_callable( $data_callback ) ) {
			$data = call_user_func( $data_callback );
		} else {
			$data = '';
		}

		/**
		 * Filters data for the current endpoint.
		 *
		 * @since 3.0
		 *
		 * @param mixed|string $data Endpoint data.
		 * @param Endpoint     $this Endpoint object.
		 */
		return apply_filters( 'edd_reports_endpoint_data', $data, $this );
	}

	/**
	 * Retrieves the endpoint view (type).
	 *
	 * @since 3.0
	 *
	 * @return string Endpoint view.
	 */
	public function get_view() {
		return $this->view;
	}

	/**
	 * Sets the endpoint view (type).
	 *
	 * @since 3.0
	 *
	 * @param string $view_type Endpoint type.
	 */
	private function set_view( $view_type ) {
		$views = \edd_reports_get_endpoint_views();

		if ( array_key_exists( $view_type, $views ) ) {
			$this->view = $view_type;
		} else {
			$this->errors->add( 'invalid_view', 'Invalid endpoint view.', $view_type );
		}
	}

	/**
	 * Sets display-related properties for the Endpoint.
	 *
	 * @since 3.0
	 *
	 * @param array $endpoint Endpoint record from the registry.
	 */
	protected function set_display_props( $endpoint ) {

		$view_type = $this->get_view();

		if ( ! empty( $endpoint['views'][ $view_type ] ) ) {

			$view_atts = $endpoint['views'][ $view_type ];

			// display_args is optional.
			if ( ! empty( $view_atts['display_args'] ) ) {
				$this->set_display_args( $view_atts['display_args'] );
			}

			// display_callback
			if ( ! empty( $view_atts['display_callback'] ) ) {
				$this->set_display_callback( $view_atts['display_callback'] );
			} else {
				$this->flag_missing_view_arg( 'display_callback' );
			}

			// data_callback
			if ( ! empty( $view_atts['data_callback'] ) ) {
				$this->set_data_callback( $view_atts['data_callback'] );
			} else {
				$this->flag_missing_view_arg( 'data_callback' );
			}

		} else {

			$message = sprintf( 'The \'%1$s\' view type is not defined for the \'%2$s\' endpoint.',
				$view_type,
				$this->get_id()
			);

			$this->errors->add( 'view_not_defined', $message, array(
				'view_type'   => $view_type,
				'endpoint_id' => $this->get_id(),
			) );

		}
	}

	/**
	 * Retrieves the display arguments for the view (type).
	 *
	 * @since 3.0
	 *
	 * @return array Display arguments.
	 */
	public function get_display_args() {
		/**
		 * Filters the display arguments for the current endpoint.
		 *
		 * @since 3.0
		 *
		 * @param array    $display_args Display arguments.
		 * @param Endpoint $this         Endpoint object.
		 */
		return apply_filters( 'edd_reports_endpoint_display_args', $this->display_args, $this );
	}

	/**
	 * Validates and sets the display_args prop.
	 *
	 * @since 3.0
	 *
	 * @param array|mixed $display_args Display arguments.
	 * @return void
	 */
	private function set_display_args( $display_args ) {
		if ( is_array( $display_args ) ) {

			$this->display_args = $display_args;

		} else {

			$this->flag_invalid_view_arg_type( 'display_args', 'array' );

		}
	}

	/**
	 * Retrieves the display callback for the endpoint view (type).
	 *
	 * @since 3.0
	 *
	 * @return callable Display callback.
	 */
	public function get_display_callback() {
		/**
		 * Filters the display callback for the current endpoint.
		 *
		 * @since 3.0
		 *
		 * @param callable $display_callback Display callback.
		 * @param Endpoint $this             Endpoint object.
		 */
		return apply_filters( 'edd_reports_endpoint_display_callback', $this->display_callback, $this );
	}

	/**
	 * Validates and sets the display_args prop.
	 *
	 * @since 3.0
	 *
	 * @param callable|mixed $display_callback Display callback.
	 * @return void
	 */
	private function set_display_callback( $display_callback ) {
		if ( is_callable( $display_callback ) ) {

			$this->display_callback = $display_callback;

		} else {

			$this->flag_invalid_view_arg_type( 'display_callback', 'callable' );

		}
	}

	/**
	 * Retrieves the data callback for the endpoint view (type).
	 *
	 * @since 3.0
	 *
	 * @return callable Data callback.
	 */
	public function get_data_callback() {
		/**
		 * Filters the data callback for the current endpoint.
		 *
		 * @since 3.0
		 *
		 * @param callable $data_callback Data callback.
		 * @param Endpoint $this          Endpoint object.
		 */
		return apply_filters( 'edd_reports_endpoint_data_callback', $this->data_callback, $this );
	}

	/**
	 * Validates and sets the display_args prop.
	 *
	 * @since 3.0
	 *
	 * @param callable|mixed $data_callback Data callback.
	 * @return void
	 */
	private function set_data_callback( $data_callback ) {
		if ( is_callable( $data_callback ) ) {

			$this->data_callback = $data_callback;

		} else {

			$this->flag_invalid_view_arg_type( 'data_callback', 'callable' );

		}
	}

	/**
	 * Flags an error for an invalid view argument type.
	 *
	 * @since 3.0
	 *
	 * @param string $argument Argument name.
	 * @return void
	 */
	private function flag_invalid_view_arg_type( $argument, $expected_type ) {
		$message = sprintf( 'The \'%1$s\' argument must be of type %2$s for the \'%3$s\' endpoint \'%4$s\' view.',
			$argument,
			$expected_type,
			$this->get_view(),
			$this->get_id()
		);

		$this->errors->add( 'invalid_view_arg_type', $message, array(
			'view_type'   => $this->get_view(),
			'endpoint_id' => $this->get_id(),
		) );
	}

	/**
	 * Flags an error for a missing required view argument.
	 *
	 * @since 3.0
	 *
	 * @param string $argument Argument name.
	 * @return void
	 */
	private function flag_missing_view_arg( $argument ) {
		$message = sprintf( 'The \'%1$s\' argument must be set for the \'%2$s\' endpoint \'%3$s\' view.',
			$argument,
			$this->get_id(),
			$this->get_view()
		);

		$this->errors->add( "missing_{$argument}", $message, array(
			'view_type'   => $this->get_view(),
			'endpoint_id' => $this->get_id(),
		) );
	}
}
