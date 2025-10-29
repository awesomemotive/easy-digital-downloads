<?php
/**
 * Controls Trait for the Elementor Checkout Widget.
 *
 * @package     EDD\Elementor\Widgets\Config\Traits
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Elementor\Widgets\Config\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Controls trait for creating controls.
 */
trait Controls {

	/**
	 * Create responsive slider control configuration.
	 *
	 * @since 3.6.0
	 * @param string       $label Label for the control.
	 * @param array|string $selectors Selector(s).
	 * @param string       $property CSS property.
	 * @param array        $args Additional arguments.
	 * @return array
	 */
	protected static function create_slider_control( string $label, $selectors, string $property, array $args = array() ): array {
		$defaults = array(
			'label'      => $label,
			'type'       => 'slider',
			'responsive' => true,
			'size_units' => self::get_size_units(),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 100,
				),
				'%'  => array(
					'min' => 0,
					'max' => 100,
				),
				'em' => array(
					'min' => 0,
					'max' => 10,
				),
			),
			'selectors'  => self::build_selectors( $selectors, $property . ': {{SIZE}}{{UNIT}};' ),
		);

		return wp_parse_args( $args, $defaults );
	}

	/**
	 * Create dimensions control configuration.
	 *
	 * @since 3.6.0
	 * @param string       $label Label for the control.
	 * @param array|string $selectors Selector(s).
	 * @param string       $property CSS property (padding, margin, etc.).
	 * @param array        $args Additional arguments.
	 * @return array
	 */
	protected static function create_dimensions_control( string $label, $selectors, string $property, array $args = array() ): array {
		$defaults = array(
			'label'      => $label,
			'type'       => 'dimensions',
			'size_units' => self::get_spacing_units(),
			'selectors'  => self::build_selectors(
				$selectors,
				$property . ': {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
			),
		);

		return wp_parse_args( $args, $defaults );
	}

	/**
	 * Create color control configuration.
	 *
	 * @since 3.6.0
	 * @param string       $label Label for the control.
	 * @param array|string $selectors Selector(s).
	 * @param string       $property CSS property.
	 * @param array        $args Additional arguments.
	 * @return array
	 */
	protected static function create_color_control( string $label, $selectors, string $property = 'color', array $args = array() ): array {
		$defaults = array(
			'label'     => $label,
			'type'      => 'color',
			'selectors' => self::build_selectors( $selectors, $property . ': {{VALUE}};' ),
		);

		return wp_parse_args( $args, $defaults );
	}

	/**
	 * Build selector array with multiple selectors.
	 *
	 * @since 3.6.0
	 * @param array|string $selectors Selector(s).
	 * @param string       $property CSS property.
	 * @return array
	 */
	protected static function build_selectors( $selectors, string $property ): array {
		if ( is_string( $selectors ) ) {
			$selectors = array( $selectors );
		}

		$result = array();
		foreach ( $selectors as $selector ) {
			$result[ $selector ] = $property;
		}

		return $result;
	}
}
