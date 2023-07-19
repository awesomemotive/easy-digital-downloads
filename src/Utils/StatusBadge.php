<?php
/**
 * Status Badge utility.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.1.4
 */
namespace EDD\Utils;

defined( 'ABSPATH' ) || exit;

class StatusBadge {

	/**
	 * The array of parameters for the badge.
	 *
	 * @var array
	 */
	private $args = array();

	/**
	 * Constructor.
	 *
	 * @param array $args Array of parameters for the badge.
	 */
	public function __construct( $args ) {
		$this->args = wp_parse_args(
			$args,
			array(
				'status'   => 'default',
				'label'    => '',
				'icon'     => '',
				'color'    => '',
				'dashicon' => true,
				'class'    => '',
			)
		);
	}

	/**
	 * Gets the badge.
	 *
	 * @since 3.1.4
	 * @param string $icon Optional. Icon markup to use. Default is empty.
	 * @return string
	 */
	public function get( $icon = false ) {
		if ( empty( $this->args['label'] ) ) {
			return '';
		}

		return sprintf(
			'<span class="%s"><span class="edd-status-badge__text">%s</span>%s</span>',
			$this->get_class_string( $this->get_classes() ),
			esc_html( $this->args['label'] ),
			! empty( $icon ) ? $icon : $this->get_icon()
		);
	}

	/**
	 * Gets the icon HTML markup.
	 *
	 * @since 3.1.4
	 * @return string
	 */
	public function get_icon() {
		if ( empty( $this->args['icon'] ) ) {
			return '';
		}

		$classes = array(
			'edd-status-badge__icon',
		);
		if ( ! empty( $this->args['dashicon'] ) ) {
			$classes[] = 'dashicons';
			$classes[] = "dashicons-{$this->args['icon']}";
		} else {
			$classes[] = $this->args['icon'];
		}

		return sprintf(
			'<span class="edd-status-badge__icon"><span class="%s"></span></span>',
			esc_attr( $this->get_class_string( $classes ) )
		);
	}

	/**
	 * Gets the classes for the badge.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	private function get_classes() {
		return array(
			'edd-status-badge',
			"edd-status-badge--{$this->args['status']}",
			$this->args['class'],
			$this->get_color_class(),
		);
	}

	/**
	 * Gets a class string from an array of classes.
	 *
	 * @since 3.1.4
	 * @param array $classes
	 * @return string
	 */
	private function get_class_string( array $classes ) {
		return implode( ' ', array_filter( array_map( 'sanitize_html_class', $classes ) ) );
	}

	/**
	 * Gets the color class.
	 *
	 * @since 3.1.4
	 * @return string
	 */
	private function get_color_class() {
		if ( ! empty( $this->args['color'] ) && false === strpos( $this->args['color'], '#' ) ) {
			return "edd-status-badge--{$this->args['color']}";
		}

		return '';
	}
}
