<?php
/**
 * Payment Method Options
 *
 * Builds payment method-specific data for line items (Klarna, PayPal, etc).
 *
 * @package EDD\Gateways\Stripe\PaymentIntents\LineItems
 * @since 3.6.1
 */

namespace EDD\Gateways\Stripe\PaymentIntents\LineItems;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Payment Method Options class.
 *
 * @since 3.6.1
 */
class PaymentMethodOptions {

	/**
	 * Build payment method options for a line item.
	 *
	 * @since 3.6.1
	 * @param array $cart_item    The cart item data.
	 * @param int   $download_id  The download ID.
	 * @return array Payment method options.
	 */
	public static function build( $cart_item, $download_id ) {
		$options = array();

		// Add card-specific options (for L3 qualification).
		$card_options = self::get_card_options( $download_id, $cart_item );
		if ( ! empty( $card_options ) ) {
			$options['card'] = $card_options;
		}

		// Add Klarna-specific options.
		$klarna_options = self::get_klarna_options( $download_id, $cart_item );
		if ( ! empty( $klarna_options ) ) {
			$options['klarna'] = $klarna_options;
		}

		// Add PayPal-specific options.
		$paypal_options = self::get_paypal_options( $cart_item, $download_id );
		if ( ! empty( $paypal_options ) ) {
			$options['paypal'] = $paypal_options;
		}

		/**
		 * Filters the payment method options for a line item.
		 *
		 * @since 3.6.1
		 *
		 * @param array $options    Payment method options.
		 * @param array $cart_item  Cart item data.
		 * @param int   $download_id Download ID.
		 */
		return apply_filters( 'edds_line_item_payment_method_options', $options, $cart_item, $download_id );
	}

	/**
	 * Get card-specific options.
	 *
	 * @since 3.6.1
	 * @param int   $download_id The download ID.
	 * @param array $cart_item   The cart item data.
	 * @return array Card options.
	 */
	private static function get_card_options( $download_id, $cart_item ) {
		$options = array();

		$commodity_code = self::get_commodity_code( $download_id, $cart_item );
		if ( ! empty( $commodity_code ) ) {
			$options['commodity_code'] = $commodity_code;
		}

		/**
		 * Filters card options for a line item.
		 *
		 * @since 3.6.1
		 *
		 * @param array $options     Card options.
		 * @param int   $download_id Download ID.
		 * @param array $cart_item   Cart item data.
		 */
		return apply_filters( 'edds_line_item_card_options', $options, $download_id, $cart_item );
	}

	/**
	 * Get Klarna-specific options.
	 *
	 * @since 3.6.1
	 * @param int   $download_id The download ID.
	 * @param array $cart_item   The cart item data.
	 * @return array Klarna options.
	 */
	private static function get_klarna_options( $download_id, $cart_item ) {
		$options = array();

		// Get product URL.
		$product_url = get_permalink( $download_id );
		if ( $product_url ) {
			$options['product_url'] = esc_url_raw( $product_url );
		}

		// Get product image.
		$image_id = get_post_thumbnail_id( $download_id );
		if ( $image_id ) {
			$image_url = wp_get_attachment_image_url( $image_id, 'medium' );
			if ( $image_url ) {
				$options['image_url'] = esc_url_raw( $image_url );
			}
		}

		/**
		 * Filters Klarna options for a line item.
		 *
		 * @since 3.6.1
		 *
		 * @param array $options     Klarna options.
		 * @param int   $download_id Download ID.
		 * @param array $cart_item   Cart item data.
		 */
		return apply_filters( 'edds_line_item_klarna_options', $options, $download_id, $cart_item );
	}

	/**
	 * Get PayPal-specific options.
	 *
	 * @since 3.6.1
	 * @param array $cart_item   The cart item data.
	 * @param int   $download_id The download ID.
	 * @return array PayPal options.
	 */
	private static function get_paypal_options( $cart_item, $download_id ) {
		$options = array();

		// Get product description.
		$download = edd_get_download( $download_id );
		if ( $download ) {
			// Use excerpt if available, otherwise use name.
			$description = $download->post_excerpt ? $download->post_excerpt : $download->post_title;
			$description = Formatter::sanitize_product_name( $description );
			if ( $description ) {
				$options['description'] = substr( $description, 0, 127 ); // PayPal has a 127 char limit.
			}
		}

		/**
		 * Filters the PayPal category for a line item.
		 *
		 * Can be 'digital_goods' or 'physical_goods'.
		 *
		 * @since 3.6.1
		 *
		 * @param string $category    PayPal category.
		 * @param int    $download_id Download ID.
		 * @param array  $cart_item   Cart item data.
		 */
		$category = apply_filters( 'edds_line_item_paypal_category', 'digital_goods', $download_id, $cart_item );

		$options['category'] = $category;

		/**
		 * Filters PayPal options for a line item.
		 *
		 * @since 3.6.1
		 *
		 * @param array $options     PayPal options.
		 * @param int   $download_id Download ID.
		 * @param array $cart_item   Cart item data.
		 */
		return apply_filters( 'edds_line_item_paypal_options', $options, $download_id, $cart_item );
	}

	/**
	 * Get the commodity code for a line item (for card L3 qualification).
	 *
	 * The commodity code should follow a standardized scheme such as:
	 * - UNSPSC (United Nations Standard Products and Services Code)
	 * - NAICS (North American Industry Classification System)
	 * - NAPCS (North American Product Classification System)
	 *
	 * @since 3.6.1
	 * @param int   $download_id The download ID.
	 * @param array $cart_item   The cart item data.
	 * @return string|null Commodity code.
	 */
	private static function get_commodity_code( $download_id, $cart_item ): ?string {
		/**
		 * Filters the commodity code for a line item (for card L3 qualification).
		 *
		 * Commodity codes help qualify U.S. domestic commercial card transactions
		 * for Level 3 interchange rates (additional 0.1-0.3% savings beyond L2).
		 * Only beneficial for merchants on IC+ pricing plans.
		 *
		 * Use standardized codes such as:
		 * - UNSPSC (United Nations Standard Products and Services Code)
		 * - NAICS (North American Industry Classification System)
		 * - NAPCS (North American Product Classification System)
		 *
		 * Common codes for digital products:
		 * - 81161500: Computer software licensing
		 * - 81112000: Educational software
		 * - 43232500: E-books
		 * - 81161801: Digital downloads
		 *
		 * @since 3.6.1
		 *
		 * @param string|null $commodity_code Commodity code (max 12 alphanumeric chars).
		 * @param int         $download_id    Download ID.
		 * @param array       $cart_item      Cart item data.
		 */
		$commodity_code = apply_filters( 'edds_line_item_commodity_code', null, $download_id, $cart_item );

		if ( empty( $commodity_code ) ) {
			return null;
		}

		// Sanitize: max 12 alphanumeric characters, no spaces.
		$commodity_code = preg_replace( '/[^a-zA-Z0-9]/', '', $commodity_code );
		$commodity_code = substr( $commodity_code, 0, 12 );

		return $commodity_code;
	}
}
