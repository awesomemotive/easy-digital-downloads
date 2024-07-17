<?php
/**
 * Handles localization for EDD scripts.
 *
 * @package     EDD
 * @subpackage  Assets
 * @since       3.3.0
 */

namespace EDD\Assets;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Localization class.
 */
class Localization {

	/**
	 * Sets up script localization for the checkout page.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public static function checkout() {
		if ( ! edd_is_checkout() ) {
			return;
		}

		wp_localize_script( 'edd-checkout-global', 'edd_global_vars', self::get_checkout_variables() );
	}

	/**
	 * Sets up ajax script localization.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public static function ajax() {
		if ( edd_is_ajax_disabled() ) {
			return;
		}

		global $post;
		// Get position in cart of current download.
		$position = isset( $post->ID )
			? edd_get_item_position_in_cart( $post->ID )
			: -1;

		wp_localize_script(
			'edd-ajax',
			'edd_scripts',
			apply_filters(
				'edd_ajax_script_vars',
				array(
					'ajaxurl'                 => esc_url_raw( edd_get_ajax_url() ),
					'position_in_cart'        => $position,
					'has_purchase_links'      => self::has_purchase_links() ? '1' : '0',
					'already_in_cart_message' => __('You have already added this item to your cart','easy-digital-downloads' ), // Item already in the cart message
					'empty_cart_message'      => __('Your cart is empty','easy-digital-downloads' ), // Item already in the cart message
					'loading'                 => __('Loading','easy-digital-downloads' ) , // General loading message
					'select_option'           => __('Please select an option','easy-digital-downloads' ) , // Variable pricing error with multi-purchase option enabled
					'is_checkout'             => edd_is_checkout() ? '1' : '0',
					'default_gateway'         => edd_get_default_gateway(),
					'redirect_to_checkout'    => ( edd_straight_to_checkout() || edd_is_checkout() ) ? '1' : '0',
					'checkout_page'           => esc_url_raw( edd_get_checkout_uri() ),
					'permalinks'              => get_option( 'permalink_structure' ) ? '1' : '0',
					'quantities_enabled'      => edd_item_quantities_enabled(),
					'taxes_enabled'           => edd_use_taxes() ? '1' : '0', // Adding here for widget, but leaving in checkout vars for backcompat
					'current_page'            => get_the_ID(),
				)
			)
		);
	}

	/**
	 * Gets the localization variables for the checkout page.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	private static function get_checkout_variables() {
		$currency = new \EDD\Currency\Currency( edd_get_currency() );

		return apply_filters(
			'edd_global_checkout_script_vars',
			array(
				'ajaxurl'               => esc_url_raw( edd_get_ajax_url() ),
				'checkout_nonce'        => wp_create_nonce( 'edd_checkout_nonce' ),
				'checkout_error_anchor' => '#edd_purchase_submit',
				'currency_sign'         => $currency->symbol,
				'currency_pos'          => $currency->position,
				'decimal_separator'     => $currency->decimal_separator,
				'thousands_separator'   => $currency->thousands_separator,
				'no_gateway'            => __( 'Please select a payment method', 'easy-digital-downloads' ),
				'no_discount'           => __( 'Please enter a discount code', 'easy-digital-downloads' ), // Blank discount code message.
				'enter_discount'        => __( 'Enter discount', 'easy-digital-downloads' ),
				'discount_applied'      => __( 'Discount Applied', 'easy-digital-downloads' ), // Discount verified message.
				'no_email'              => __( 'Please enter an email address before applying a discount code', 'easy-digital-downloads' ),
				'no_username'           => __( 'Please enter a username before applying a discount code', 'easy-digital-downloads' ),
				'purchase_loading'      => __( 'Please Wait...', 'easy-digital-downloads' ),
				'complete_purchase'     => edd_get_checkout_button_purchase_label(),
				'taxes_enabled'         => edd_use_taxes() ? '1' : '0',
				'edd_version'           => edd_admin_get_script_version(),
				'current_page'          => get_the_ID(),
				'showStoreErrors'       => current_user_can( 'manage_shop_settings' ) ? 'true' : 'false',
			)
		);
	}

	/**
	 * Check if the current page has purchase links.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	private static function has_purchase_links() {
		if ( is_post_type_archive( 'download' ) ) {
			return true;
		}

		$post_id = get_the_ID();
		if ( has_block( 'edd/downloads', $post_id ) || has_block( 'edd/buy-button', $post_id ) ) {
			return true;
		}

		global $post;
		if ( ! empty( $post->post_content ) &&
			(
				has_shortcode( $post->post_content, 'purchase_link' ) ||
				has_shortcode( $post->post_content, 'downloads' )
				)
			) {
			return true;
		}

		return false;
	}
}

