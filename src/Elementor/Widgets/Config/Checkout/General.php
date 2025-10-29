<?php
/**
 * General Controls Configuration for Checkout Widget.
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
 * General Controls Configuration for Checkout Widget.
 *
 * @since 3.6.0
 */
class General extends Base {

	/**
	 * Get general controls configuration.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	public static function get_controls(): array {
		return array(
			'section_general' => array(
				'label'    => __( 'General', 'easy-digital-downloads' ),
				'tab'      => 'content',
				'controls' => array(
					'preview_as_guest'        => array(
						'label'        => __( 'Preview as Guest', 'easy-digital-downloads' ),
						'type'         => 'switcher',
						'label_on'     => __( 'Yes', 'easy-digital-downloads' ),
						'label_off'    => __( 'No', 'easy-digital-downloads' ),
						'return_value' => 'yes',
						'default'      => 'yes',
						'description'  => __( 'Show the checkout form as it would appear to a guest user.', 'easy-digital-downloads' ),
					),
					'preview_cart_item'       => array(
						'label'       => __( 'Cart Item', 'easy-digital-downloads' ),
						'type'        => 'select',
						'options'     => self::get_cart_item_options(),
						'default'     => '0',
						'description' => __( 'Select a product to preview the checkout form with a specific item in the cart.', 'easy-digital-downloads' ),
					),
					'login_details_alignment' => array(
						'label'       => __( 'Account Alignment', 'easy-digital-downloads' ),
						'type'        => 'choose',
						'options'     => self::get_alignment_options(),
						'default'     => 'left',
						'responsive'  => true,
						'selectors'   => array(
							'.edd-blocks__logged-in' => 'text-align: {{VALUE}};',
						),
						'description' => __( 'Align the account information text for logged in users.', 'easy-digital-downloads' ),
					),
					'layout'                  => array(
						'label'              => __( 'Layout', 'easy-digital-downloads' ),
						'type'               => 'select',
						'options'            => self::get_layout_options(),
						'default'            => '',
						'frontend_available' => true,
					),
				),
			),
		);
	}

	/**
	 * Get layout options for checkout.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	protected static function get_layout_options(): array {
		return array(
			''                  => __( 'Single Column', 'easy-digital-downloads' ),
			'half'              => __( '50/50 Split', 'easy-digital-downloads' ),
			'two-thirds'        => __( '70/30 Split', 'easy-digital-downloads' ),
			'four-fifths'       => __( '80/20 Split', 'easy-digital-downloads' ),
			'half-bottom'       => __( 'Bottom 50/50 Split', 'easy-digital-downloads' ),
			'two-thirds-bottom' => __( 'Bottom 70/30 Split', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Get cart item options for preview.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	protected static function get_cart_item_options(): array {
		$downloads = new \WP_Query(
			array(
				'post_type'      => 'download',
				'posts_per_page' => 30,
				'no_found_rows'  => true,
				'post_status'    => 'publish',
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		// Use a string key for the default option so the list is ordered correctly.
		$options = array( '0' => __( 'Select a product', 'easy-digital-downloads' ) );

		// Add downloads to the array.
		foreach ( $downloads->posts as $download ) {
			$options[ $download->ID ] = $download->post_title;
		}

		return $options;
	}
}
