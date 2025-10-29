<?php
/**
 * Cart Controls Configuration for Checkout Widget.
 *
 * @package     EDD\Elementor\Widgets\Config\Checkout
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Elementor\Widgets\Config\Checkout;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Elementor\Widgets\Config\Base;

/**
 * Cart Controls Configuration for Checkout Widget.
 *
 * @since 3.6.0
 */
class Cart extends Base {

	/**
	 * Get cart controls configuration.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	public static function get_controls(): array {
		return array(
			'section_cart' => array(
				'label'    => __( 'Cart', 'easy-digital-downloads' ),
				'tab'      => 'content', // Override default 'style' tab.
				'controls' => array(
					'show_cart_header'   => array(
						'label'                => __( 'Cart Header', 'easy-digital-downloads' ),
						'type'                 => 'switcher',
						'label_on'             => __( 'Show', 'easy-digital-downloads' ),
						'label_off'            => __( 'Hide', 'easy-digital-downloads' ),
						'return_value'         => 'yes',
						'default'              => 'yes',
						'selectors_dictionary' => array(
							'yes' => 'flex',
							''    => 'none',
						),
						'selectors'            => array(
							'#edd_checkout_cart_form .edd_cart_header_row' => 'display: {{VALUE}};',
						),
					),
					'show_cart_image'    => array(
						'label'                => __( 'Cart Thumbnail', 'easy-digital-downloads' ),
						'type'                 => 'switcher',
						'label_on'             => __( 'Show', 'easy-digital-downloads' ),
						'label_off'            => __( 'Hide', 'easy-digital-downloads' ),
						'return_value'         => 'yes',
						'default'              => 'yes',
						'selectors_dictionary' => array(
							'yes' => 'block',
							''    => 'none',
						),
						'selectors'            => array(
							'.edd_cart_item_image' => 'display: {{VALUE}};',
						),
					),
					'thumbnail_width'    => array(
						'label'      => __( 'Size', 'easy-digital-downloads' ),
						'type'       => 'slider',
						'size_units' => array( 'px' ),
						'condition'  => array(
							'show_cart_image' => 'yes',
						),
						'default'    => array( 'size' => 25 ),
						'range'      => array(
							'px' => array(
								'min'  => 10,
								'max'  => 100,
								'step' => 1,
							),
						),
					),
					'show_cart_quantity' => self::get_cart_quantity_control(),
					'show_discount_form' => array(
						'label'              => __( 'Discount Form', 'easy-digital-downloads' ),
						'type'               => 'switcher',
						'label_on'           => __( 'Show', 'easy-digital-downloads' ),
						'label_off'          => __( 'Hide', 'easy-digital-downloads' ),
						'return_value'       => 'yes',
						'default'            => 'yes',
						'frontend_available' => true,
					),
				),
			),
		);
	}

	/**
	 * Get cart quantity control configuration.
	 *
	 * @since 3.6.0
	 * @return array|null
	 */
	private static function get_cart_quantity_control(): ?array {
		if ( ! edd_item_quantities_enabled() ) {
			return null;
		}

		return array(
			'label'                => __( 'Cart Quantity', 'easy-digital-downloads' ),
			'type'                 => 'switcher',
			'label_on'             => __( 'Show', 'easy-digital-downloads' ),
			'label_off'            => __( 'Hide', 'easy-digital-downloads' ),
			'return_value'         => 'yes',
			'default'              => 'yes',
			'selectors_dictionary' => array(
				'yes' => 'block',
				''    => 'none',
			),
			'selectors'            => array(
				'.edd_cart_actions' => 'display: {{VALUE}};',
			),
		);
	}
}
