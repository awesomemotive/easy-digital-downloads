<?php
/**
 * Button Style Controls Configuration for Checkout Widget.
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
 * Button Style Controls Configuration for Checkout Widget.
 *
 * @since 3.6.0
 */
class Buttons extends Base {

	/**
	 * Get button style controls configuration.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	public static function get_controls(): array {
		return array(
			'section_button_style' => array(
				'label'    => __( 'Purchase Button', 'easy-digital-downloads' ),
				'controls' => array(
					// Layout Controls.
					'button_width'                  => array(
						'label'      => __( 'Width', 'easy-digital-downloads' ),
						'type'       => 'slider',
						'size_units' => array( 'px', '%' ),
						'range'      => array(
							'px' => array(
								'min' => 0,
								'max' => 400,
							),
							'%'  => array(
								'min' => 0,
								'max' => 100,
							),
						),
						'selectors'  => array(
							'form #edd-purchase-button' => 'width: {{SIZE}}{{UNIT}};',
						),
					),
					'button_alignment'              => array(
						'label'                => __( 'Alignment', 'easy-digital-downloads' ),
						'type'                 => 'choose',
						'options'              => self::get_button_alignment_options(),
						'default'              => 'left',
						'selectors_dictionary' => array(
							'left'   => 'margin-right:auto;',
							'center' => 'margin-left:auto; margin-right:auto;',
							'right'  => 'margin-left:auto;',
						),
						'selectors'            => array(
							'form #edd-purchase-button' => 'display:block;{{VALUE}}',
						),
					),

					// Typography (GROUP).
					'button_typography'             => self::create_typography_group(
						__( 'Typography', 'easy-digital-downloads' ),
						'form #edd-purchase-button'
					),

					// Normal State Controls.
					'button_background_color'       => self::create_color_control(
						__( 'Background Color', 'easy-digital-downloads' ),
						'form #edd-purchase-button',
						'background-color'
					),
					'button_text_color'             => self::create_color_control(
						__( 'Text Color', 'easy-digital-downloads' ),
						'form #edd-purchase-button'
					),
					'button_text_shadow'            => self::create_text_shadow_group(
						__( 'Text Shadow', 'easy-digital-downloads' ),
						'form #edd-purchase-button'
					),
					'button_box_shadow'             => self::create_box_shadow_group(
						__( 'Box Shadow', 'easy-digital-downloads' ),
						'form #edd-purchase-button'
					),

					// Hover State Controls.
					'button_background_hover_color' => self::create_color_control(
						__( 'Hover Background Color', 'easy-digital-downloads' ),
						'form #edd-purchase-button:hover',
						'background-color'
					),
					'button_hover_text_color'       => self::create_color_control(
						__( 'Hover Text Color', 'easy-digital-downloads' ),
						'form #edd-purchase-button:hover'
					),
					'button_text_shadow_hover'      => self::create_text_shadow_group(
						__( 'Hover Text Shadow', 'easy-digital-downloads' ),
						'form #edd-purchase-button:hover'
					),
					'button_box_shadow_hover'       => self::create_box_shadow_group(
						__( 'Hover Box Shadow', 'easy-digital-downloads' ),
						'form #edd-purchase-button:hover'
					),

					// Border Controls (with separator).
					'button_border'                 => self::create_border_group(
						__( 'Border', 'easy-digital-downloads' ),
						'form #edd-purchase-button',
						array( 'separator' => 'before' )
					),
					'button_border_radius'          => self::create_dimensions_control(
						__( 'Border Radius', 'easy-digital-downloads' ),
						'form #edd-purchase-button',
						'border-radius',
						array( 'size_units' => array( 'px', '%' ) )
					),
					'button_padding'                => self::create_dimensions_control(
						__( 'Padding', 'easy-digital-downloads' ),
						'form #edd-purchase-button',
						'padding'
					),
					'button_margin'                 => self::create_dimensions_control(
						__( 'Margin', 'easy-digital-downloads' ),
						'form #edd-purchase-button',
						'margin'
					),
				),
			),
		);
	}

	/**
	 * Get button alignment options.
	 *
	 * @since 3.6.0
	 * @return array Button alignment options.
	 */
	protected static function get_button_alignment_options(): array {
		return array(
			'left'   => array(
				'title' => __( 'Left', 'easy-digital-downloads' ),
				'icon'  => 'eicon-text-align-left',
			),
			'center' => array(
				'title' => __( 'Center', 'easy-digital-downloads' ),
				'icon'  => 'eicon-text-align-center',
			),
			'right'  => array(
				'title' => __( 'Right', 'easy-digital-downloads' ),
				'icon'  => 'eicon-text-align-right',
			),
		);
	}
}
