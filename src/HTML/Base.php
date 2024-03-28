<?php
/**
 * Base HTML Element
 *
 * @package EDD
 * @subpackage HTML
 * @since 3.2.8
 */

namespace EDD\HTML;

defined( 'ABSPATH' ) || exit;

/**
 * Class Base
 *
 * @since 3.2.8
 * @package EDD\HTML
 */
abstract class Base {

	/**
	 * @var array
	 */
	protected $args;

	/**
	 * Abstract constructor.
	 *
	 * @since 3.2.8
	 * @param array $args Arguments for the element.
	 */
	public function __construct( array $args = array() ) {
		$this->args = $this->parse_args( $args );
	}

	/**
	 * Gets the HTML for the element.
	 *
	 * @since 3.2.8
	 * @return string Element HTML.
	 */
	abstract public function get();

	/**
	 * Gets the default arguments for the element.
	 *
	 * @since 3.2.8
	 * @return array
	 */
	abstract protected function defaults();

	/**
	 * Echoes the HTML for the element.
	 *
	 * @since 3.2.8
	 * @return void
	 */
	public function output() {
		echo $this->get();
	}

	/**
	 * Get data elements
	 *
	 * @since 3.2.8
	 * @return string
	 */
	protected function get_data_elements() {
		if ( empty( $this->args['data'] ) ) {
			return '';
		}

		$data_elements = array();
		foreach ( $this->args['data'] as $key => $value ) {
			$data_elements[] = 'data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}
		if ( empty( $this->args['data']['placeholder'] ) && ! empty( $this->args['placeholder'] ) ) {
			$data_elements[] = 'data-placeholder="' . esc_attr( $this->args['placeholder'] ) . '"';
		}

		return implode( ' ', $data_elements );
	}

	/**
	 * Gets the CSS class string for the element.
	 *
	 * @since 3.2.8
	 * @param array $classes The classes to add to the element.
	 * @return string
	 */
	final protected function get_css_class_string( array $classes = array() ): string {
		$classes = array_merge( $this->get_base_classes(), $classes, $this->get_css_classes_from_args() );

		return $this->array_to_css_string( $classes );
	}

	/**
	 * Gets a CSS class string from any array.
	 *
	 * @since 3.2.8
	 * @param array $classes The array of classes.
	 * @return string
	 */
	final protected function array_to_css_string( $classes ) {
		return implode( ' ', array_map( 'sanitize_html_class', array_unique( array_filter( $classes ) ) ) );
	}

	/**
	 * Gets the CSS classes from the arguments.
	 *
	 * @since 3.2.8
	 * @return array
	 */
	private function get_css_classes_from_args() {
		if ( empty( $this->args['class'] ) ) {
			return array();
		}
		$custom_classes = $this->args['class'];
		if ( ! is_array( $custom_classes ) ) {
			$custom_classes = explode( ' ', $custom_classes );
		}

		return array_map( 'sanitize_html_class', array_filter( $custom_classes ) );
	}

	/**
	 * Gets the base classes for an element which cannot be overwritten.
	 *
	 * @since 3.2.8
	 * @return array
	 */
	protected function get_base_classes(): array {
		return array();
	}

	/**
	 * Parses the arguments for the element.
	 *
	 * @since 3.2.8
	 * @param array $args The arguments for the element.
	 * @return array
	 */
	private function parse_args( array $args ) {
		return wp_parse_args( $args, $this->defaults() );
	}
}
