<?php
/**
 * Slideout Cart Assets
 *
 * Handles asset enqueuing and load conditions.
 *
 * @package     EDD\SlideoutCart
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.2
 */

namespace EDD\Cart\Preview;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Cart\Preview\Utility;
use EDD\REST\Routes\Cart;

/**
 * Assets class
 *
 * Manages asset loading for the slideout cart feature.
 *
 * @since 3.6.2
 */
class Assets {

	/**
	 * Check if assets should load on current page.
	 *
	 * @since 3.6.2
	 * @return bool
	 */
	public function should_load() {
		// Must be enabled.
		if ( ! Utility::is_enabled() ) {
			return false;
		}

		// Must be frontend and not on the checkout page.
		if ( is_admin() || edd_is_checkout() ) {
			return false;
		}

		// If there is anything in the cart, load the assets.
		if ( edd_get_cart_quantity() > 0 ) {
			return true;
		}

		// Check for add to cart buttons in content.
		if ( $this->has_add_to_cart_button() ) {
			return true;
		}

		// Check for mini cart block.
		if ( $this->has_mini_cart_block() ) {
			return true;
		}

		/**
		 * Filter whether slideout cart should load on current page.
		 *
		 * @since 3.6.2
		 * @param bool $should_load Whether to load slideout cart.
		 */
		return apply_filters( 'edd_cart_preview_should_load', false );
	}

	/**
	 * Check if page has add to cart button.
	 *
	 * @since 3.6.2
	 * @return bool
	 */
	private function has_add_to_cart_button() {
		$post = get_queried_object();
		if ( ! $post || ! isset( $post->post_content ) ) {
			return false;
		}

		// Check for EDD blocks.
		if ( has_block( 'edd/buy-button' ) || has_block( 'edd/downloads' ) ) {
			return true;
		}

		// Check for EDD shortcodes.
		if ( false !== strpos( $post->post_content, '[downloads' ) ||
			false !== strpos( $post->post_content, '[purchase_link' ) ||
			false !== strpos( $post->post_content, '[edd_price' ) ||
			false !== strpos( $post->post_content, '[edd_downloads' ) ) {
			return true;
		}

		// Check if this is a download post type.
		if ( isset( $post->post_type ) && 'download' === $post->post_type ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if page has mini cart block.
	 *
	 * @since 3.6.2
	 * @return bool
	 */
	private function has_mini_cart_block() {
		return has_block( 'edd/cart' );
	}

	/**
	 * Enqueue assets.
	 *
	 * @since 3.6.2
	 * @return void
	 */
	public function enqueue() {
		if ( ! $this->should_load() ) {
			return;
		}

		// Enqueue cart preview script.
		wp_enqueue_script(
			'edd-cart-preview',
			EDD_PLUGIN_URL . 'assets/build/js/frontend/cart-preview.js',
			array( 'jquery' ),
			edd_admin_get_script_version(),
			true
		);

		wp_localize_script( 'edd-cart-preview', 'eddCartPreviewConfig', $this->get_config() );

		/**
		 * Fires after cart preview assets are enqueued.
		 *
		 * @since 3.6.2
		 */
		do_action( 'edd_cart_preview_assets_enqueued' );
	}

	/**
	 * Get cart preview configuration.
	 *
	 * @since 3.6.2
	 * @return array
	 */
	private function get_config(): array {
		$timestamp     = time();
		$rest_endpoint = Cart::NAMESPACE . '/' . Cart::$version . '/' . Cart::BASE;

		// Configuration for JavaScript.
		$config = array(
			'apiBase'           => rest_url( $rest_endpoint ),
			'timestamp'         => $timestamp,
			'token'             => \EDD\REST\Security::generate_token( $timestamp ),
			'nonce'             => wp_create_nonce( 'wp_rest' ),
			'checkoutUrl'       => edd_get_checkout_uri(),
			'quantitiesEnabled' => edd_item_quantities_enabled(),
			'currency'          => edd_get_currency(),
			'currencySymbol'    => html_entity_decode( edd_currency_filter(), ENT_QUOTES, 'UTF-8' ),
			'autoOpenOnAdd'     => ! edd_get_option( 'redirect_on_add', false ),
			'i18n'              => array(
				'failedCartContents'   => __( 'Failed to load cart contents. Please try again.', 'easy-digital-downloads' ),
				'failedRemoveItem'     => __( 'Failed to remove item. Please try again.', 'easy-digital-downloads' ),
				'failedUpdateQuantity' => __( 'Failed to update quantity. Please try again.', 'easy-digital-downloads' ),
			),
			'buttonColors'      => \EDD\Utils\Colors::get_button_colors(),
			'debug'             => edd_doing_script_debug(),
			'showButton'        => edd_get_option( 'cart_preview_button', false ),
			'buttonSize'        => edd_get_option( 'cart_preview_button_size', 'large' ),
			'buttonPosition'    => edd_get_option( 'cart_preview_button_position', '' ),
		);

		/**
		 * Filter cart preview configuration.
		 *
		 * @since 3.6.2
		 * @param array $config Configuration array.
		 */
		return apply_filters( 'edd_cart_preview_config', $config );
	}
}
