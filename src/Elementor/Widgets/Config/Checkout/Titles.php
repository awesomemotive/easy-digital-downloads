<?php
/**
 * Titles Controls Configuration for Checkout Widget.
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
 * Titles Controls Configuration for Checkout Widget.
 *
 * @since 3.6.0
 */
class Titles extends Base {

	/**
	 * Get titles controls configuration.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	public static function get_controls(): array {
		return array(
			'section_titles' => array(
				'label'    => __( 'Section Titles', 'easy-digital-downloads' ),
				'tab'      => 'content', // Override default 'style' tab.
				'controls' => array(
					'show_personal_info_title'    => array(
						'label'                => __( 'Personal Info', 'easy-digital-downloads' ),
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
							'#edd_checkout_user_info legend' => 'display: {{VALUE}};',
						),
					),
					'show_billing_details_title'  => array(
						'label'                => __( 'Billing Details', 'easy-digital-downloads' ),
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
							'#edd_cc_address legend' => 'display: {{VALUE}};',
						),
					),
					'show_payment_method_title'   => array(
						'label'                => __( 'Payment Method', 'easy-digital-downloads' ),
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
							'#edd_payment_mode_select legend' => 'display: {{VALUE}};',
						),
					),
					'show_cc_details_title'       => array(
						'label'                => __( 'Card Info', 'easy-digital-downloads' ),
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
							'#edd_cc_fields legend' => 'display: {{VALUE}};',
						),
					),
				),
			),
		);
	}
}
