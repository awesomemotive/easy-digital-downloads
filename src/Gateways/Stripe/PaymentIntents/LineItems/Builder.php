<?php
/**
 * Line Items Builder
 *
 * Orchestrates building line items for Stripe Payment Intents.
 *
 * @package EDD\Gateways\Stripe\PaymentIntents\LineItems
 * @since 3.6.1
 */

namespace EDD\Gateways\Stripe\PaymentIntents\LineItems;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Builder class.
 *
 * @since 3.6.1
 */
class Builder {

	/**
	 * The purchase data.
	 *
	 * @since 3.6.1
	 * @var array
	 */
	private $purchase_data;

	/**
	 * The payment method type.
	 *
	 * @since 3.6.1
	 * @var string
	 */
	private $payment_method;

	/**
	 * Constructor.
	 *
	 * @since 3.6.1
	 * @param array  $purchase_data The purchase data.
	 * @param string $payment_method The payment method type.
	 */
	public function __construct( $purchase_data, $payment_method ) {
		$this->purchase_data  = $purchase_data;
		$this->payment_method = $payment_method;
	}

	/**
	 * Check if line items are required for the payment method.
	 *
	 * @since 3.6.1
	 * @return bool True if line items are required.
	 */
	public function is_enabled() {
		if ( in_array( $this->payment_method, array( 'klarna', 'paypal' ), true ) ) {
			return true;
		}

		return (bool) edd_get_option( 'stripe_line_items_enabled', false );
	}

	/**
	 * Build line items array.
	 *
	 * @since 3.6.1
	 * @return array Line items data.
	 */
	public function build() {
		if ( ! $this->is_enabled() ) {
			return array();
		}

		$line_items = array();

		// Add cart items.
		foreach ( $this->get_cart_items() as $cart_item ) {
			$item         = new Item( $cart_item, $this->purchase_data );
			$line_items[] = $item->to_array();
		}

		// Add cart-level fees as line items (item-specific fees are already in item subtotals).
		$fees = $this->get_fees();
		foreach ( $fees as $id => $fee ) {
			$fee_line_item = $this->build_fee_line_item( $id, $fee );
			// Only add if not empty (negative fees return empty array).
			if ( ! empty( $fee_line_item ) ) {
				$line_items[] = $fee_line_item;
			}
		}

		// Validate total.
		$this->validate_line_items_total( $line_items );

		/**
		 * Filters the line items.
		 *
		 * @since 3.6.1
		 *
		 * @param array  $line_items     Line items data.
		 * @param string $payment_method Payment method type.
		 * @param array  $purchase_data  Purchase data.
		 */
		return apply_filters( 'edds_line_items', $line_items, $this->payment_method, $this->purchase_data );
	}

	/**
	 * Get cart items.
	 *
	 * @since 3.6.1
	 * @return array Cart items.
	 */
	private function get_cart_items() {
		if ( ! empty( $this->purchase_data['cart_details'] ) ) {
			return $this->purchase_data['cart_details'];
		}

		return array();
	}

	/**
	 * Get cart-level fees (not item-specific fees).
	 *
	 * Item-specific fees are already included in cart item subtotals.
	 * Only cart-level fees (where download_id is empty) should be added as separate line items.
	 *
	 * @since 3.6.1
	 * @return array Cart-level fees only.
	 */
	private function get_fees() {
		$all_fees = EDD()->fees->get_fees( 'all' );

		if ( empty( $all_fees ) ) {
			return array();
		}

		$cart_level_fees = array();

		// Only include fees that are NOT tied to a specific item.
		// Item-specific fees are already in the item's subtotal.
		foreach ( $all_fees as $fee ) {
			if ( empty( $fee['download_id'] ) ) {
				$cart_level_fees[] = $fee;
			}
		}

		return $cart_level_fees;
	}

	/**
	 * Build a line item from a cart-level fee.
	 *
	 * Cart-level fees may have tax applied to them. We need to calculate and include that tax.
	 * Negative fees (discounts) should not be added as line items.
	 *
	 * @since 3.6.1
	 * @param string $id  The fee ID.
	 * @param array  $fee The fee data.
	 * @return array Line item data (empty array for negative fees).
	 */
	private function build_fee_line_item( $id, $fee ) {
		$fee_amount = $fee['amount'];

		// Handle negative fees (discounts) - don't add as line items.
		// These should be handled via discount_amount at the payment level.
		if ( $fee_amount < 0 ) {
			return array();
		}

		$line_item = array(
			'product_code' => Formatter::sanitize_product_code( 'fee_' . sanitize_key( $id ) ),
			'product_name' => Formatter::sanitize_product_name( $fee['label'] ),
			'unit_cost'    => Formatter::format_amount( $fee_amount ),
			'quantity'     => 1,
		);

		// Calculate tax on the fee if applicable (fees without no_tax flag).
		if ( edd_use_taxes() && empty( $fee['no_tax'] ) ) {
			$fee_tax = edd_calculate_tax( $fee_amount );
			if ( $fee_tax > 0 ) {
				$line_item['tax'] = array(
					'total_tax_amount' => Formatter::format_amount( $fee_tax ),
				);
			}
		}

		/**
		 * Filters fee line item data.
		 *
		 * @since 3.6.1
		 *
		 * @param array $line_item Line item data.
		 * @param array $fee       Fee data.
		 */
		return apply_filters( 'edds_fee_line_item', $line_item, $fee );
	}

	/**
	 * Validate that line items total matches the purchase total.
	 *
	 * @since 3.6.1
	 * @param array $line_items Line items.
	 * @return void
	 * @throws \Exception If totals don't match.
	 */
	private function validate_line_items_total( $line_items ) {
		$expected_total = Formatter::format_amount( $this->purchase_data['price'] );

		$line_items_total = 0;
		foreach ( $line_items as $item ) {
			// Calculate item subtotal (unit_cost * quantity).
			$item_total = $item['unit_cost'] * $item['quantity'];

			// Subtract discount if present.
			if ( isset( $item['discount_amount'] ) ) {
				$item_total -= $item['discount_amount'];
			}

			// Add tax if present (line items include tax, total must include tax).
			if ( isset( $item['tax']['total_tax_amount'] ) ) {
				$item_total += $item['tax']['total_tax_amount'];
			}

			$line_items_total += $item_total;
		}

		// Allow for small rounding differences (1 cent).
		$difference = abs( $expected_total - $line_items_total );
		if ( $difference > 1 ) {
			edd_debug_log(
				sprintf(
					'Stripe line items total mismatch. Expected: %d, Got: %d, Difference: %d',
					$expected_total,
					$line_items_total,
					$difference
				),
				true
			);

			/**
			 * Fires when line items total doesn't match expected total.
			 *
			 * @since 3.6.1
			 *
			 * @param int   $expected_total    Expected total in cents.
			 * @param int   $line_items_total  Line items total in cents.
			 * @param array $line_items        Line items data.
			 * @param array $purchase_data     Purchase data.
			 */
			do_action( 'edds_line_items_total_mismatch', $expected_total, $line_items_total, $line_items, $this->purchase_data );
		}
	}
}
