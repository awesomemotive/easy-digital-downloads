<?php
/**
 * Line Item
 *
 * Represents a single line item for Stripe.
 *
 * @package EDD\Gateways\Stripe\PaymentIntents\LineItems
 * @since 3.6.1
 */

namespace EDD\Gateways\Stripe\PaymentIntents\LineItems;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Item class.
 *
 * @since 3.6.1
 */
class Item {

	/**
	 * The cart item data.
	 *
	 * @since 3.6.1
	 * @var array
	 */
	private $cart_item;

	/**
	 * The purchase data.
	 *
	 * @since 3.6.1
	 * @var array
	 */
	private $purchase_data;

	/**
	 * Constructor.
	 *
	 * @since 3.6.1
	 * @param array $cart_item     The cart item data.
	 * @param array $purchase_data The purchase data.
	 */
	public function __construct( $cart_item, $purchase_data ) {
		$this->cart_item     = $cart_item;
		$this->purchase_data = $purchase_data;
	}

	/**
	 * Convert the item to a Stripe line item array.
	 *
	 * @since 3.6.1
	 * @return array The line item data.
	 */
	public function to_array() {
		$line_item = array(
			'product_code' => $this->get_product_code(),
			'product_name' => $this->get_product_name(),
			'unit_cost'    => $this->get_unit_cost(),
			'quantity'     => $this->get_quantity(),
		);

		// Add optional fields.
		$discount_amount = $this->get_discount_amount();
		if ( ! is_null( $discount_amount ) && $discount_amount > 0 ) {
			$line_item['discount_amount'] = $discount_amount;
		}

		$tax = $this->get_tax();
		if ( ! is_null( $tax ) ) {
			$line_item['tax'] = $tax;
		}

		// Always add unit_of_measure (required for L3 qualification).
		$line_item['unit_of_measure'] = $this->get_unit_of_measure();

		// Add payment method options.
		$payment_method_options = $this->get_payment_method_options();
		if ( ! empty( $payment_method_options ) ) {
			$line_item['payment_method_options'] = $payment_method_options;
		}

		/**
		 * Filters the line item data.
		 *
		 * @since 3.6.1
		 *
		 * @param array $line_item     Line item data.
		 * @param array $cart_item     Cart item data.
		 * @param array $purchase_data Purchase data.
		 */
		return apply_filters( 'edds_line_item_data', $line_item, $this->cart_item, $this->purchase_data );
	}

	/**
	 * Get the product code (SKU if available, otherwise download ID).
	 *
	 * @since 3.6.1
	 * @return string The product code.
	 */
	private function get_product_code() {
		$download_id = $this->cart_item['id'];

		// Try to get SKU first.
		if ( edd_use_skus() ) {
			$sku = edd_get_download_sku( $download_id );
			if ( ! empty( $sku ) && '-' !== $sku ) {
				// Add price ID if variable pricing.
				if ( isset( $this->cart_item['item_number']['options']['price_id'] ) ) {
					$price_id = $this->cart_item['item_number']['options']['price_id'];
					$sku     .= '_' . $price_id;
				}

				return Formatter::sanitize_product_code( $sku );
			}
		}

		// Fallback to download ID.
		$code = (string) $download_id;
		if ( isset( $this->cart_item['item_number']['options']['price_id'] ) ) {
			$price_id = $this->cart_item['item_number']['options']['price_id'];
			$code    .= '_' . $price_id;
		}

		return Formatter::sanitize_product_code( $code );
	}

	/**
	 * Get the product name.
	 *
	 * @since 3.6.1
	 * @return string The product name.
	 */
	private function get_product_name() {
		$name = isset( $this->cart_item['name'] ) ? $this->cart_item['name'] : '';

		// If name is not set in cart item, get it from the download.
		if ( empty( $name ) ) {
			$download_id = $this->cart_item['id'];
			$price_id    = isset( $this->cart_item['item_number']['options']['price_id'] ) ?
				$this->cart_item['item_number']['options']['price_id'] : null;

			$name = edd_get_download_name( $download_id, $price_id );
		}

		return Formatter::sanitize_product_name( $name );
	}

	/**
	 * Get the unit cost in cents.
	 *
	 * Uses subtotal which includes item-specific fees (like Simple Shipping).
	 * Subtotal is the pre-tax amount, so when Stripe adds tax separately, the total matches.
	 *
	 * @since 3.6.1
	 * @return int The unit cost.
	 */
	private function get_unit_cost() {
		// Use subtotal (includes item-specific fees, excludes tax).
		// This matches the PayPal Commerce pattern.
		$subtotal = isset( $this->cart_item['subtotal'] ) ? $this->cart_item['subtotal'] : 0;
		$quantity = $this->get_quantity();

		// Calculate unit cost from subtotal.
		$unit_price = $quantity > 0 ? ( $subtotal / $quantity ) : $subtotal;

		// Fallback to item_price if subtotal is not set.
		if ( empty( $unit_price ) && isset( $this->cart_item['item_price'] ) ) {
			$unit_price = $this->cart_item['item_price'];
		}

		return Formatter::format_amount( $unit_price );
	}

	/**
	 * Get the quantity.
	 *
	 * @since 3.6.1
	 * @return int The quantity.
	 */
	private function get_quantity() {
		return isset( $this->cart_item['quantity'] ) ? (int) $this->cart_item['quantity'] : 1;
	}

	/**
	 * Get the discount amount in cents (item-level discount).
	 *
	 * @since 3.6.1
	 * @return int|null The discount amount or null if none.
	 */
	private function get_discount_amount() {
		if ( ! isset( $this->cart_item['discount'] ) || 0 === (float) $this->cart_item['discount'] ) {
			return null;
		}

		return Formatter::format_amount( $this->cart_item['discount'] );
	}

	/**
	 * Get the tax data (item-level tax).
	 *
	 * Includes both the item's base tax and any tax from item-specific fees.
	 * EDD calculates fee tax separately and stores it in the fee data as 'tax'.
	 *
	 * @since 3.6.1
	 * @return array|null The tax data or null if none.
	 */
	private function get_tax() {
		$total_tax = 0;

		// Start with the cart item's tax (tax on base item).
		if ( ! empty( $this->cart_item['tax'] ) ) {
			$total_tax = (float) $this->cart_item['tax'];
		}

		// Add tax from item-specific fees.
		if ( ! empty( $this->cart_item['fees'] ) ) {
			foreach ( $this->cart_item['fees'] as $fee ) {
				if ( ! empty( $fee['tax'] ) ) {
					$total_tax += (float) $fee['tax'];
				}
			}
		}

		if ( empty( $total_tax ) ) {
			return null;
		}

		return array(
			'total_tax_amount' => Formatter::format_amount( $total_tax ),
		);
	}

	/**
	 * Get the unit of measure. This is required for L3 qualification.
	 *
	 * @since 3.6.1
	 * @return string|null The unit of measure.
	 */
	private function get_unit_of_measure() {
		/**
		 * Filters the unit of measure for a line item.
		 *
		 * @since 3.6.1
		 *
		 * @param string      $unit_of_measure Default unit of measure.
		 * @param array       $cart_item       Cart item data.
		 * @param array       $purchase_data   Purchase data.
		 */
		return apply_filters( 'edds_line_item_unit_of_measure', 'each', $this->cart_item, $this->purchase_data );
	}

	/**
	 * Get payment method options.
	 *
	 * @since 3.6.1
	 * @return array Payment method options.
	 */
	private function get_payment_method_options() {
		return PaymentMethodOptions::build( $this->cart_item, $this->cart_item['id'] );
	}
}
