<?php
/**
 * Slideout Cart Data Utility
 *
 * Transforms cart data for the slideout cart.
 *
 * @package     EDD\SlideoutCart\Utilities
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.2
 */

namespace EDD\Cart;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Cart Data class
 *
 * Provides cart data transformation utilities.
 *
 * @since 3.6.2
 */
class Data {

	/**
	 * Get formatted cart data.
	 *
	 * @since 3.6.2
	 * @return array Cart data formatted for slideout cart.
	 */
	public static function get_cart_data() {
		$cart_contents = EDD()->cart->get_contents_details();
		$items         = array();

		if ( ! empty( $cart_contents ) ) {
			foreach ( $cart_contents as $key => $item ) {
				$items[] = self::format_cart_item( $key, $item );
			}
		}

		// Apply the currency filter to the raw amount.
		$display_amount = edd_prices_include_tax() ? edd_get_cart_total() : edd_get_cart_subtotal();

		$cart_data = array(
			'items'      => $items,
			'subtotal'   => edd_currency_filter( edd_format_amount( $display_amount ) ),
			'has_items'  => ! empty( $items ),
			'item_count' => edd_get_cart_quantity(),
		);

		/**
		 * Filter cart preview data.
		 *
		 * @since 3.6.2
		 * @param array $cart_data Cart data.
		 */
		return apply_filters( 'edd_cart_preview_data', $cart_data );
	}

	/**
	 * Format single cart item.
	 *
	 * @since 3.6.2
	 * @param int   $key  Cart item key.
	 * @param array $item Cart item data.
	 * @return array Formatted cart item.
	 */
	public static function format_cart_item( $key, $item ) {
		if ( empty( $item['id'] ) ) {
			return array();
		}

		$download_id = (int) $item['id'];
		$download    = edd_get_download( $download_id );
		if ( ! $download ) {
			return array();
		}

		$options    = isset( $item['item_number']['options'] ) ? $item['item_number']['options'] : array();
		$price_id   = isset( $options['price_id'] ) ? (int) $options['price_id'] : null;
		$quantity   = isset( $item['quantity'] ) ? absint( $item['quantity'] ) : 1;
		$item_price = edd_get_cart_item_price( $download_id, $options );

		$formatted_item = array(
			'key'                => $key,
			'id'                 => $download_id,
			'name'               => edd_get_download_name( $download_id, $price_id ),
			'price'              => edd_currency_filter( edd_format_amount( $item_price ) ),
			'price_raw'          => $item_price,
			'quantity'           => $quantity,
			'thumbnail'          => self::get_thumbnail_url( $download_id ),
			'options'            => $options,
			'quantities_enabled' => edd_item_quantities_enabled() && ! edd_download_quantities_disabled( $download_id ),
		);

		if ( ! empty( $item['fees'] ) ) {
			foreach ( $item['fees'] as $fee ) {
				$formatted_item['fees'][] = array(
					'label'      => $fee['label'],
					'amount'     => edd_currency_filter( edd_format_amount( $fee['amount'] ) ),
					'amount_raw' => $fee['amount'],
				);
			}
		}

		/**
		 * Filter individual cart item data for slideout cart.
		 *
		 * @since 3.6.2
		 * @param array $formatted_item Formatted item data.
		 * @param int   $download_id    Download ID.
		 * @param int   $key            Cart item key.
		 * @param array $item           Original cart item data.
		 */
		return apply_filters( 'edd_cart_item_data', $formatted_item, $download_id, $key, $item );
	}

	/**
	 * Get thumbnail URL for download.
	 *
	 * @since 3.6.2
	 * @param int $download_id Download ID.
	 * @return string|null Thumbnail URL or null.
	 */
	private static function get_thumbnail_url( $download_id ) {
		if ( ! has_post_thumbnail( $download_id ) ) {
			return null;
		}

		$thumbnail_id = get_post_thumbnail_id( $download_id );
		$thumbnail    = wp_get_attachment_image_src( $thumbnail_id, 'thumbnail' );

		if ( ! $thumbnail ) {
			return null;
		}

		return $thumbnail[0];
	}
}
