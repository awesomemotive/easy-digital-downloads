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
class Endpoint {

	/**
	 * Endpoint ID.
	 *
	 * @since 3.0
	 * @var   string
	 */
	private $endpoint_id;

	/**
	 * Endpoint label.
	 *
	 * @since 3.0
	 * @var   string
	 */
	private $label;

	/**
	 * Endpoint view (type).
	 *
	 * @since 3.0
	 * @var   string
	 */
	private $view;

	/**
	 * Represents filters available to the endpoint.
	 *
	 * @since 3.0
	 */
	public $filters = array();

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
	 * Holds errors related to instantiating the endpoint object.
	 *
	 * @since 3.0
	 * @var   \WP_Error
	 */
	private $errors;

	/**
	 * Constructs the endpoint object.
	 *
	 * @since 3.0
	 *
	 * @see set_display_props()
	 *
	 * @param string $view_type Endpoint view type. Determines which view attribute to
	 *                          retrieve from the corresponding endpoint registry entry.
	 * @param array  $endpoint  Endpoint record from the registry.
	 */
	public function __construct( $view_type, $endpoint ) {
		$this->errors = new \WP_Error();

		$this->set_view( $view_type );
		$this->set_props( $endpoint );
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
	 * Sets props for the Endpoint object.
	 *
	 * @since 3.0
	 *
	 * @param array $endpoint Endpoint record.
	 */
	public function set_props( $endpoint ) {
		if ( ! empty( $endpoint['id'] ) ) {

			$this->set_id( $endpoint['id'] );

		} else {

			$this->errors->add( 'missing_endpoint_id', 'The endpoint_id is missing.' );

		}

		if ( ! empty( $endpoint['label'] ) ) {

			$this->set_label( $endpoint['label'] );

		} else {

			$this->errors->add( 'missing_endpoint_label', 'The endpoint label is missing.' );

		}

		$this->set_display_props( $endpoint );
	}

	/**
	 * Retrieves the endpoint ID.
	 *
	 * @since 3.0
	 *
	 * @return string Endpoint ID.
	 */
	public function get_id() {
		return $this->endpoint_id;
	}

	/**
	 * Sets the endpoint ID.
	 *
	 * @since 3.0
	 *
	 * @param string $endpoint_id Endpoint ID
	 * @return void
	 */
	private function set_id( $endpoint_id ) {
		$this->endpoint_id = $endpoint_id;
	}

	/**
	 * Retrieves the global label for the current endpoint.
	 *
	 * @since 3.0
	 *
	 * @return string Endpoint string.
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Sets the endpoint label.
	 *
	 * @since 3.0
	 *
	 * @param string $label Endpoint label.
	 * @return void
	 */
	private function set_label( $label ) {
		$this->label = $label;
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
	 * Determines whether the endpoint has generated errors during instantiation.
	 *
	 * @since 3.0
	 *
	 * @return bool True if errors have been logged, otherwise false.
	 */
	public function has_errors() {
		$errors = $this->errors->get_error_codes();

		return empty( $errors ) ? false : true;
	}

	/**
	 * Retrieves any logged errors for the endpoint.
	 *
	 * @since 3.0
	 *
	 * @return \WP_Error WP_Error object for the endpoint.
	 */
	public function get_errors() {
		return $this->errors;
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
