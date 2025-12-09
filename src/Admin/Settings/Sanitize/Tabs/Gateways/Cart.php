<?php
/**
 * Sanitizes the Cart section.
 *
 * @since 3.6.2
 * @package EDD\Admin\Settings\Sanitize\Tabs\Gateways
 */

namespace EDD\Admin\Settings\Sanitize\Tabs\Gateways;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Admin\Settings\Sanitize\Tabs\Section;
use EDD\Settings\Setting;

/**
 * Sanitizes the Cart section.
 *
 * @since 3.6.2
 */
class Cart extends Section {

	/**
	 * Sanitize the empty cart behavior.
	 *
	 * @since 3.5.0
	 * @param array $value The value to sanitize.
	 * @return array The sanitized value.
	 */
	protected static function sanitize_empty_cart_behavior( $value ) {
		$provider = edd_get_namespace( 'Admin\\Settings\\EmptyCartBehavior' );

		return $provider::validate_empty_cart_behavior( $value );
	}

	/**
	 * Additional processing for the cart section.
	 *
	 * @since 3.6.2
	 * @param array $input The input array.
	 * @return array The processed input.
	 */
	protected static function additional_processing( $input ) {
		if ( ! empty( $input['enable_cart_preview'] ) ) {
			$input['enable_cart_preview'] = self::check_rest_api( $input['enable_cart_preview'] );
		}

		/**
		 * Filter the cart recommendations enabled status.
		 *
		 * @since 3.6.2
		 * @param bool $enabled Whether the cart recommendations are enabled.
		 * @param array $input The input array.
		 * @return bool The filtered enabled status.
		 */
		$cart_recommendations = apply_filters( 'edd_cart_recommendations_enabled', false, $input );
		if ( $cart_recommendations ) {
			$input['cart_recommendations'] = true;
		}

		if ( isset( $input['delete_recommendations'] ) ) {
			unset( $input['delete_recommendations'] );
		}

		if ( isset( $input['delete_recommendations_nonce'] ) ) {
			unset( $input['delete_recommendations_nonce'] );
		}

		return $input;
	}

	/**
	 * Sanitize the empty cart message.
	 *
	 * @since 3.5.0
	 * @param string $value The value to sanitize.
	 * @return string The sanitized value.
	 */
	public static function sanitize_empty_cart_message( $value ) {
		$provider = edd_get_namespace( 'Admin\\Settings\\EmptyCartBehavior' );

		return $provider::validate_empty_cart_message( $value );
	}

	/**
	 * Sanitize the empty cart redirect page.
	 *
	 * @since 3.5.0
	 * @param string $value The value to sanitize.
	 * @return string The sanitized value.
	 */
	public static function sanitize_empty_cart_redirect_page( $value ) {
		$provider = edd_get_namespace( 'Admin\\Settings\\EmptyCartBehavior' );

		return $provider::validate_empty_cart_redirect_page( $value );
	}

	/**
	 * Sanitize the empty cart redirect URL.
	 *
	 * @since 3.5.0
	 * @param string $value The value to sanitize.
	 * @return string The sanitized value.
	 */
	public static function sanitize_empty_cart_redirect_url( $value ) {
		$provider = edd_get_namespace( 'Admin\\Settings\\EmptyCartBehavior' );

		return $provider::validate_empty_cart_redirect_url( $value );
	}

	/**
	 * Make a quick REST call to the cart token endpoint to ensure REST is enabled.
	 *
	 * @since 3.6.2
	 * @param bool $value The value to sanitize.
	 * @return bool The sanitized value.
	 */
	protected static function check_rest_api( $value ) {
		if ( ! $value ) {
			return false;
		}

		$checker = new \EDD\Utils\RESTChecker( 'edd/v3/cart/token', true );
		if ( ! $checker->is_enabled() ) {
			update_option( 'edd_enable_cart_preview_rest_error', true );

			return false;
		}

		return true;
	}

	/**
	 * Sanitize the empty cart preview message.
	 *
	 * @since 3.6.2
	 * @param string $value The value to sanitize.
	 * @return string The sanitized value.
	 */
	protected static function sanitize_empty_cart_preview( $value ) {
		// Remove any shortcodes from the value.
		$value = strip_shortcodes( $value );

		return wp_kses_post( $value );
	}
}
