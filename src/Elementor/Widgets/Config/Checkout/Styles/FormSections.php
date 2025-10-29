<?php
/**
 * Form Sections Style Controls Configuration for Checkout Widget.
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
 * Form Sections Style Controls Configuration for Checkout Widget.
 *
 * @since 3.6.0
 */
class FormSections extends Base {

	/**
	 * Get form sections style controls configuration.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	public static function get_controls(): array {
		return array(
			'section_form_sections_style' => array(
				'label'    => __( 'Form Sections', 'easy-digital-downloads' ),
				'controls' => array(
					// Form section controls.
					'form_section'          => array(
						'type'  => 'heading',
						'label' => __( 'Section', 'easy-digital-downloads' ),
					),
					'form_background_color' => self::create_color_control(
						__( 'Background Color', 'easy-digital-downloads' ),
						'form fieldset',
						'background-color'
					),
					'form_border'           => self::create_border_group(
						__( 'Border', 'easy-digital-downloads' ),
						'form fieldset'
					),
					'form_border_radius'    => self::create_dimensions_control(
						__( 'Border Radius', 'easy-digital-downloads' ),
						'form fieldset',
						'border-radius',
						array( 'size_units' => array( 'px', '%' ) )
					),
					'form_box_shadow'       => self::create_box_shadow_group(
						__( 'Box Shadow', 'easy-digital-downloads' ),
						'form fieldset'
					),
					'form_padding'          => self::create_dimensions_control(
						__( 'Padding', 'easy-digital-downloads' ),
						'form fieldset',
						'padding'
					),
					'form_margin'           => self::create_dimensions_control(
						__( 'Margin', 'easy-digital-downloads' ),
						'form fieldset',
						'margin'
					),

					// Form title controls.
					'form_title'            => array(
						'type'      => 'heading',
						'label'     => __( 'Title', 'easy-digital-downloads' ),
						'separator' => 'before',
					),
					'title_typography'      => self::create_typography_group(
						__( 'Typography', 'easy-digital-downloads' ),
						'form fieldset legend'
					),
					'title_text_color'      => self::create_color_control(
						__( 'Text Color', 'easy-digital-downloads' ),
						'form fieldset legend'
					),
					'title_text_shadow'     => self::create_text_shadow_group(
						__( 'Text Shadow', 'easy-digital-downloads' ),
						'form fieldset legend'
					),
				),
			),
		);
	}
}
