<?php
/**
 * Cart Style Controls Configuration for Checkout Widget.
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
 * Cart Style Controls Configuration for Checkout Widget.
 *
 * @since 3.6.0
 */
class Cart extends Base {

	/**
	 * Get cart style controls configuration.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	public static function get_controls(): array {
		return array(
			'cart_section_style' => array(
				'label'    => __( 'Cart', 'easy-digital-downloads' ),
				'controls' => array_filter(
					array(
						...self::get_main_cart_controls(),
						...self::get_cart_header_controls(),
						...self::get_cart_items_controls(),
						...self::get_cart_quantity_controls(),
						...self::get_cart_footer_controls(),
					)
				),
			),
		);
	}

	/**
	 * Get main cart controls.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	private static function get_main_cart_controls(): array {
		return array(
			'cart_background_color' => self::create_color_control(
				__( 'Background Color', 'easy-digital-downloads' ),
				'form #edd_checkout_cart',
				'background-color'
			),
			'cart_border'           => self::create_border_group(
				__( 'Border', 'easy-digital-downloads' ),
				'form #edd_checkout_cart'
			),
			'cart_border_radius'    => self::create_dimensions_control(
				__( 'Border Radius', 'easy-digital-downloads' ),
				'form #edd_checkout_cart',
				'border-radius',
				array( 'size_units' => array( 'px', '%' ) )
			),
			'cart_box_shadow'       => self::create_box_shadow_group(
				__( 'Box Shadow', 'easy-digital-downloads' ),
				'form #edd_checkout_cart'
			),
			'cart_padding'          => self::create_dimensions_control(
				__( 'Padding', 'easy-digital-downloads' ),
				'form #edd_checkout_cart',
				'padding'
			),
			'cart_margin'           => self::create_dimensions_control(
				__( 'Margin', 'easy-digital-downloads' ),
				'form #edd_checkout_cart',
				'margin'
			),
		);
	}

	/**
	 * Get cart header controls.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	private static function get_cart_header_controls(): array {
		return array(
			'cart_header_heading'          => array(
				'label'     => __( 'Cart Header', 'easy-digital-downloads' ),
				'type'      => 'heading',
				'separator' => 'before',
			),
			'cart_header_alignment'        => array(
				'label'     => __( 'Alignment', 'easy-digital-downloads' ),
				'type'      => 'choose',
				'options'   => self::get_cart_header_alignment_options(),
				'selectors' => array(
					'form #edd_checkout_cart .edd_cart_header_row > div' => 'flex: 0 0 calc((100% - 1rem) / 2); justify-content: {{VALUE}};',
				),
			),
			'cart_header_typography'       => self::create_typography_group(
				__( 'Typography', 'easy-digital-downloads' ),
				'form #edd_checkout_cart .edd_cart_header_row > div'
			),
			'cart_header_color'            => self::create_color_control(
				__( 'Text Color', 'easy-digital-downloads' ),
				'form #edd_checkout_cart .edd_cart_header_row > div'
			),
			'cart_header_background_color' => self::create_color_control(
				__( 'Background Color', 'easy-digital-downloads' ),
				'form #edd_checkout_cart .edd_cart_header_row',
				'background-color'
			),
			'cart_header_border'           => self::create_border_group(
				__( 'Border', 'easy-digital-downloads' ),
				'form #edd_checkout_cart .edd_cart_header_row'
			),
			'cart_header_border_radius'    => self::create_dimensions_control(
				__( 'Border Radius', 'easy-digital-downloads' ),
				'form #edd_checkout_cart .edd_cart_header_row',
				'border-radius',
				array( 'size_units' => array( 'px', '%' ) )
			),
			'cart_header_padding'          => self::create_dimensions_control(
				__( 'Padding', 'easy-digital-downloads' ),
				'form #edd_checkout_cart .edd_cart_header_row',
				'padding'
			),
			'cart_header_margin'           => self::create_dimensions_control(
				__( 'Margin', 'easy-digital-downloads' ),
				'form #edd_checkout_cart .edd_cart_header_row',
				'margin'
			),
		);
	}

	/**
	 * Get cart items controls.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	private static function get_cart_items_controls(): array {
		return array(
			'cart_items_heading'       => array(
				'label'     => __( 'Cart Items', 'easy-digital-downloads' ),
				'type'      => 'heading',
				'separator' => 'before',
			),
			'cart_items_alignment'     => array(
				'label'     => __( 'Alignment', 'easy-digital-downloads' ),
				'type'      => 'choose',
				'options'   => self::get_cart_items_alignment_options(),
				'default'   => 'center',
				'selectors' => array(
					'form #edd_checkout_cart .edd-blocks-cart__items .edd_cart_item, form #edd_checkout_cart .edd-blocks-cart__items .edd_cart_item .edd_checkout_cart_item_title' => 'align-items: {{VALUE}};',
				),
			),
			'cart_items_typography'    => self::create_typography_group(
				__( 'Typography', 'easy-digital-downloads' ),
				'form #edd_checkout_cart .edd-blocks-cart__items .edd_cart_item'
			),
			'cart_items_border'        => self::create_border_group(
				__( 'Border', 'easy-digital-downloads' ),
				'form #edd_checkout_cart .edd-blocks-cart__items .edd_cart_item'
			),
			'cart_items_border_radius' => self::create_dimensions_control(
				__( 'Border Radius', 'easy-digital-downloads' ),
				'form #edd_checkout_cart .edd-blocks-cart__items .edd_cart_item',
				'border-radius',
				array( 'size_units' => array( 'px', '%' ) )
			),
			'cart_items_padding'       => self::create_dimensions_control(
				__( 'Padding', 'easy-digital-downloads' ),
				'form #edd_checkout_cart .edd-blocks-cart__items .edd_cart_item',
				'padding'
			),
			'cart_items_margin'        => self::create_dimensions_control(
				__( 'Margin', 'easy-digital-downloads' ),
				'form #edd_checkout_cart .edd-blocks-cart__items .edd_cart_item',
				'margin'
			),
		);
	}

	/**
	 * Get cart quantity controls.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	private static function get_cart_quantity_controls(): array {
		if ( ! edd_item_quantities_enabled() ) {
			return array();
		}

		return array(
			'quantity_heading'          => array(
				'label'     => __( 'Cart Quantity', 'easy-digital-downloads' ),
				'type'      => 'heading',
				'separator' => 'before',
			),
			'quantity_typography'       => self::create_typography_group(
				__( 'Typography', 'easy-digital-downloads' ),
				'#edd_checkout_cart .edd-item-quantity'
			),
			'quantity_background_color' => self::create_color_control(
				__( 'Background Color', 'easy-digital-downloads' ),
				'#edd_checkout_cart .edd-item-quantity',
				'background-color'
			),
			'quantity_text_color'       => self::create_color_control(
				__( 'Text Color', 'easy-digital-downloads' ),
				'#edd_checkout_cart .edd-item-quantity',
				'color'
			),
			'quantity_border'           => self::create_border_group(
				__( 'Border', 'easy-digital-downloads' ),
				'#edd_checkout_cart .edd-item-quantity'
			),
			'quantity_border_radius'    => self::create_dimensions_control(
				__( 'Border Radius', 'easy-digital-downloads' ),
				'#edd_checkout_cart .edd-item-quantity',
				'border-radius',
				array( 'size_units' => array( 'px', '%' ) )
			),
			'quantity_box_shadow'       => self::create_box_shadow_group(
				__( 'Box Shadow', 'easy-digital-downloads' ),
				'#edd_checkout_cart .edd-item-quantity'
			),
			'quantity_padding'          => self::create_dimensions_control(
				__( 'Padding', 'easy-digital-downloads' ),
				'#edd_checkout_cart .edd-item-quantity',
				'padding'
			),
		);
	}

	/**
	 * Get cart footer controls.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	private static function get_cart_footer_controls(): array {
		return array(
			'cart_footer_rows_heading'   => array(
				'label'     => __( 'Footer Rows', 'easy-digital-downloads' ),
				'type'      => 'heading',
				'separator' => 'before',
			),
			'cart_footer_rows_alignment' => array(
				'label'     => __( 'Alignment', 'easy-digital-downloads' ),
				'type'      => 'choose',
				'options'   => self::get_cart_footer_alignment_options(),
				'default'   => 'right',
				'selectors' => array(
					'form #edd_checkout_cart .edd_cart_footer_row:not(.edd_cart_apply_discount_row) > div' => 'display: flex; justify-content: {{VALUE}};',
				),
			),
			'cart_footer_rows_padding'   => self::create_dimensions_control(
				__( 'Padding', 'easy-digital-downloads' ),
				'form #edd_checkout_cart .edd_cart_footer_row',
				'padding'
			),
			'cart_footer_rows_margin'    => self::create_dimensions_control(
				__( 'Margin', 'easy-digital-downloads' ),
				'form #edd_checkout_cart .edd_cart_footer_row',
				'margin'
			),
		);
	}

	/**
	 * Get cart header alignment options.
	 *
	 * @since 3.6.0
	 * @return array Cart header alignment options.
	 */
	private static function get_cart_header_alignment_options(): array {
		return array(
			''       => array(
				'title' => __( 'Default', 'easy-digital-downloads' ),
				'icon'  => 'eicon-text-align-left',
			),
			'center' => array(
				'title' => __( 'Center', 'easy-digital-downloads' ),
				'icon'  => 'eicon-text-align-center',
			),
		);
	}

	/**
	 * Get cart items alignment options.
	 *
	 * @since 3.6.0
	 * @return array Cart items alignment options.
	 */
	private static function get_cart_items_alignment_options(): array {
		return array(
			'flex-start' => array(
				'title' => __( 'Top', 'easy-digital-downloads' ),
				'icon'  => 'eicon-align-start-v',
			),
			'center'     => array(
				'title' => __( 'Center', 'easy-digital-downloads' ),
				'icon'  => 'eicon-align-center-v',
			),
			'flex-end'   => array(
				'title' => __( 'Bottom', 'easy-digital-downloads' ),
				'icon'  => 'eicon-align-end-h',
			),
		);
	}

	/**
	 * Get cart footer alignment options.
	 *
	 * @since 3.6.0
	 * @return array Cart footer alignment options.
	 */
	private static function get_cart_footer_alignment_options(): array {
		return array(
			'left'          => array(
				'title' => __( 'Left', 'easy-digital-downloads' ),
				'icon'  => 'eicon-justify-start-h',
			),
			'space-between' => array(
				'title' => __( 'Space Between', 'easy-digital-downloads' ),
				'icon'  => 'eicon-justify-space-between-h',
			),
			'right'         => array(
				'title' => __( 'Right', 'easy-digital-downloads' ),
				'icon'  => 'eicon-justify-end-h',
			),
		);
	}
}
