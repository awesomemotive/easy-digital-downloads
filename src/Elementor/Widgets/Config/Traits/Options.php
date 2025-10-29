<?php
/**
 * Common Options Trait.
 *
 * @package     EDD\Elementor\Widgets\Config
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Elementor\Widgets\Config\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Common options trait for sharing helper methods.
 *
 * @since 3.6.0
 */
trait Options {

	/**
	 * Get common alignment options.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	protected static function get_alignment_options(): array {
		return array(
			'left'   => array(
				'title' => esc_html__( 'Left', 'easy-digital-downloads' ),
				'icon'  => 'eicon-text-align-left',
			),
			'center' => array(
				'title' => esc_html__( 'Center', 'easy-digital-downloads' ),
				'icon'  => 'eicon-text-align-center',
			),
			'right'  => array(
				'title' => esc_html__( 'Right', 'easy-digital-downloads' ),
				'icon'  => 'eicon-text-align-right',
			),
		);
	}

	/**
	 * Get common justify content options.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	protected static function get_justify_options(): array {
		return array(
			'flex-start'    => array(
				'title' => esc_html__( 'Start', 'easy-digital-downloads' ),
				'icon'  => 'eicon-flex eicon-justify-start-h',
			),
			'center'        => array(
				'title' => esc_html__( 'Center', 'easy-digital-downloads' ),
				'icon'  => 'eicon-flex eicon-justify-center-h',
			),
			'flex-end'      => array(
				'title' => esc_html__( 'End', 'easy-digital-downloads' ),
				'icon'  => 'eicon-flex eicon-justify-end-h',
			),
			'space-between' => array(
				'title' => esc_html__( 'Space Between', 'easy-digital-downloads' ),
				'icon'  => 'eicon-flex eicon-justify-space-between-h',
			),
			'space-around'  => array(
				'title' => esc_html__( 'Space Around', 'easy-digital-downloads' ),
				'icon'  => 'eicon-flex eicon-justify-space-around-h',
			),
			'space-evenly'  => array(
				'title' => esc_html__( 'Space Evenly', 'easy-digital-downloads' ),
				'icon'  => 'eicon-flex eicon-justify-space-evenly-h',
			),
		);
	}

	/**
	 * Get common size units.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	protected static function get_size_units(): array {
		return array( 'px', 'em', 'rem', '%', 'vh', 'vw' );
	}

	/**
	 * Get common spacing size units.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	protected static function get_spacing_units(): array {
		return array( 'px', 'em', 'rem', '%' );
	}

	/**
	 * Get common border types.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	protected static function get_border_types(): array {
		return array( 'none', 'solid', 'double', 'dotted', 'dashed', 'groove' );
	}
}
