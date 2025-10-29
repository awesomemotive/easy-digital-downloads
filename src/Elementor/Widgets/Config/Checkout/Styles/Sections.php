<?php
/**
 * Sections Style Controls Configuration for Checkout Widget.
 *
 * @package     EDD\Elementor\Widgets\Config\Checkout\Styles
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Elementor\Widgets\Config\Checkout\Styles;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Elementor\Widgets\Config\Base;

/**
 * Sections Style Controls Configuration for Checkout Widget.
 *
 * @since 3.6.0
 */
class Sections extends Base {

	/**
	 * Get sections style controls configuration.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	public static function get_controls(): array {
		$sections = self::get_section_configs();
		$controls = array();

		foreach ( $sections as $section_name => $config ) {
			$controls[ "{$section_name}_section_style" ] = array(
				'label'    => $config['title'],
				'controls' => self::get_section_controls( $section_name, $config['selector'] ),
			);
		}

		return $controls;
	}

	/**
	 * Get section configurations.
	 *
	 * @since 3.6.0
	 * @return array Section configurations.
	 */
	protected static function get_section_configs(): array {
		return array(
			'personal_information' => array(
				'title'    => __( 'Personal Information', 'easy-digital-downloads' ),
				'selector' => 'form#edd_purchase_form #edd_checkout_user_info',
			),
			'billing_details'      => array(
				'title'    => __( 'Billing Details', 'easy-digital-downloads' ),
				'selector' => 'form#edd_purchase_form #edd_cc_address',
			),
			'payment_method'       => array(
				'title'    => __( 'Payment Method', 'easy-digital-downloads' ),
				'selector' => 'form#edd_purchase_form #edd_payment_mode_select',
			),
			'cc_details'           => array(
				'title'    => __( 'Card Details', 'easy-digital-downloads' ),
				'selector' => 'form#edd_purchase_form #edd_cc_fields',
			),
		);
	}

	/**
	 * Get controls for a specific section.
	 *
	 * @since 3.6.0
	 * @param string $section_name     Section name.
	 * @param string $section_selector Section selector.
	 * @return array Section controls.
	 */
	protected static function get_section_controls( string $section_name, string $section_selector ): array {
		return array(
			// Section controls.
			"{$section_name}_section_heading"          => array(
				'label' => __( 'Section', 'easy-digital-downloads' ),
				'type'  => 'heading',
			),
			"{$section_name}_section_background_color" => self::create_color_control(
				__( 'Background Color', 'easy-digital-downloads' ),
				$section_selector,
				'background-color'
			),
			"{$section_name}_text_color"               => self::create_color_control(
				__( 'Text Color', 'easy-digital-downloads' ),
				$section_selector
			),
			"{$section_name}_border"                   => self::create_border_group(
				__( 'Border', 'easy-digital-downloads' ),
				$section_selector
			),
			"{$section_name}_border_radius"            => self::create_dimensions_control(
				__( 'Border Radius', 'easy-digital-downloads' ),
				$section_selector,
				'border-radius',
				array( 'size_units' => array( 'px', '%' ) )
			),
			"{$section_name}_box_shadow"               => self::create_box_shadow_group(
				__( 'Box Shadow', 'easy-digital-downloads' ),
				$section_selector
			),
			"{$section_name}_padding"                  => self::create_dimensions_control(
				__( 'Padding', 'easy-digital-downloads' ),
				$section_selector,
				'padding'
			),
			"{$section_name}_margin"                   => self::create_dimensions_control(
				__( 'Margin', 'easy-digital-downloads' ),
				$section_selector,
				'margin'
			),

			// Title controls.
			"{$section_name}_title_heading"            => array(
				'label' => __( 'Title', 'easy-digital-downloads' ),
				'type'  => 'heading',
			),
			"{$section_name}_title_typography"         => self::create_typography_group(
				__( 'Typography', 'easy-digital-downloads' ),
				"{$section_selector} legend"
			),
			"{$section_name}_title_color"              => self::create_color_control(
				__( 'Color', 'easy-digital-downloads' ),
				"{$section_selector} legend"
			),
			"{$section_name}_title_padding"            => self::create_dimensions_control(
				__( 'Padding', 'easy-digital-downloads' ),
				"{$section_selector} legend",
				'padding'
			),
			"{$section_name}_title_margin"             => self::create_dimensions_control(
				__( 'Margin', 'easy-digital-downloads' ),
				"{$section_selector} legend",
				'margin'
			),
		);
	}
}
