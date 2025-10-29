<?php
/**
 * Groups Trait for the Elementor Checkout Widget.
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
 * Groups trait for creating group controls.
 */
trait Groups {

	/**
	 * Create typography group control configuration.
	 *
	 * @since 3.6.0
	 * @param string $label Label for the control.
	 * @param string $selector Selector.
	 * @param array  $args Additional arguments.
	 * @return array
	 */
	protected static function create_typography_group( string $label, string $selector, array $args = array() ): array {
		$defaults = array(
			'type'     => 'typography',
			'label'    => $label,
			'selector' => $selector,
		);

		return wp_parse_args( $args, $defaults );
	}

	/**
	 * Create border group control configuration.
	 *
	 * @since 3.6.0
	 * @param string $label Label for the control.
	 * @param string $selector Selector.
	 * @param array  $args Additional arguments.
	 * @return array
	 */
	protected static function create_border_group( string $label, string $selector, array $args = array() ): array {
		$defaults = array(
			'type'     => 'border',
			'label'    => $label,
			'selector' => $selector,
		);

		return wp_parse_args( $args, $defaults );
	}

	/**
	 * Create box shadow group control configuration.
	 *
	 * @since 3.6.0
	 * @param string $label Label for the control.
	 * @param string $selector Selector.
	 * @param array  $args Additional arguments.
	 * @return array
	 */
	protected static function create_box_shadow_group( string $label, string $selector, array $args = array() ): array {
		$defaults = array(
			'type'     => 'box_shadow',
			'label'    => $label,
			'selector' => $selector,
		);

		return wp_parse_args( $args, $defaults );
	}

	/**
	 * Create text shadow group control configuration.
	 *
	 * @since 3.6.0
	 * @param string $label Label for the control.
	 * @param string $selector Selector.
	 * @param array  $args Additional arguments.
	 * @return array
	 */
	protected static function create_text_shadow_group( string $label, string $selector, array $args = array() ): array {
		$defaults = array(
			'type'     => 'text_shadow',
			'label'    => $label,
			'selector' => $selector,
		);

		return wp_parse_args( $args, $defaults );
	}
}
