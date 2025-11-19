<?php
/**
 * Amount Details
 *
 * Coordinates line items with payment-level tax and discount data.
 *
 * @package EDD\Gateways\Stripe\PaymentIntents
 * @since 3.6.1
 */

namespace EDD\Gateways\Stripe\PaymentIntents;

use EDD\Gateways\Stripe\PaymentIntents\LineItems\Builder as LineItemsBuilder;
use EDD\Gateways\Stripe\PaymentIntents\LineItems\Formatter;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * AmountDetails class.
 *
 * @since 3.6.1
 */
class AmountDetails {

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
	 * Line items builder.
	 *
	 * @since 3.6.1
	 * @var LineItemsBuilder
	 */
	private $line_items_builder;

	/**
	 * Constructor.
	 *
	 * @since 3.6.1
	 * @param array  $purchase_data The purchase data.
	 * @param string $payment_method The payment method type.
	 */
	public function __construct( $purchase_data, $payment_method ) {
		$this->purchase_data      = $purchase_data;
		$this->payment_method     = $payment_method;
		$this->line_items_builder = new LineItemsBuilder( $purchase_data, $payment_method );
	}

	/**
	 * Build the amount_details parameter for Stripe.
	 *
	 * @since 3.6.1
	 * @return array Amount details data.
	 */
	public function build() {
		// Get line items.
		$line_items = $this->line_items_builder->build();

		if ( empty( $line_items ) ) {
			return array();
		}

		$amount_details = array(
			'line_items' => $line_items,
		);

		// Add payment-level discount (order-level discounts only, item discounts are in line items).
		$discount = $this->get_total_discount();
		if ( ! is_null( $discount ) && $discount > 0 ) {
			$amount_details['discount_amount'] = $discount;
		}

		// Add shipping details if available.
		$shipping = $this->get_shipping();
		if ( ! empty( $shipping ) ) {
			$amount_details['shipping'] = $shipping;
		}

		/**
		 * Filters the amount details.
		 *
		 * @since 3.6.1
		 *
		 * @param array  $amount_details Amount details data.
		 * @param string $payment_method Payment method type.
		 * @param array  $purchase_data  Purchase data.
		 */
		return apply_filters( 'edds_amount_details', $amount_details, $this->payment_method, $this->purchase_data );
	}

	/**
	 * Check if line items are enabled.
	 *
	 * @since 3.6.1
	 * @return bool True if line items should be sent.
	 */
	public function is_enabled() {
		return $this->line_items_builder->is_enabled();
	}

	/**
	 * Get total discount for the payment.
	 *
	 * Note: This is order-level discount (negative fees) only.
	 *
	 * @since 3.6.1
	 * @return int|null Discount amount in cents or null if none.
	 */
	private function get_total_discount() {
		// Check if there's a discount in purchase data.
		if ( empty( $this->purchase_data['fees'] ) ) {
			return null;
		}

		$discount = 0;
		foreach ( $this->purchase_data['fees'] as $fee ) {
			if ( ! empty( $fee['download_id'] ) ) {
				continue;
			}
			if ( $fee['amount'] >= 0 ) {
				continue;
			}
			$discount += $fee['amount'];
		}

		return Formatter::format_amount( $discount );
	}

	/**
	 * Get shipping details for the payment.
	 *
	 * Note: This does NOT include shipping amounts because shipping fees are already
	 * accounted for in line items:
	 * - Item-specific fees (like Simple Shipping) are included in cart item subtotals,
	 *   which are used to calculate line item unit_cost.
	 * - Cart-level fees are added as separate line items by the Builder.
	 *
	 * Including shipping.amount would double-count the fees.
	 *
	 * @since 3.6.1
	 * @return array Shipping details or empty array if none.
	 */
	private function get_shipping() {
		/**
		 * Filters the shipping details for Stripe line items.
		 *
		 * Allows extensions like Simple Shipping to provide shipping data for L3 qualification.
		 * Expected format:
		 * array(
		 *   'from_postal_code' => '94110', // Optional, max 10 chars
		 *   'to_postal_code'   => '94117', // Optional, max 10 chars
		 * )
		 *
		 * Note: Do not include 'amount' here. Shipping fees should be added via EDD's fees
		 * system, where they will be automatically included in line items (either as part of
		 * item subtotals for item-specific fees, or as separate line items for cart-level fees).
		 *
		 * @since 3.6.1
		 *
		 * @param array  $shipping       Shipping details.
		 * @param string $payment_method Payment method type.
		 * @param array  $purchase_data  Purchase data.
		 */
		$shipping = apply_filters( 'edds_line_items_shipping', array(), $this->payment_method, $this->purchase_data );

		if ( empty( $shipping ) ) {
			return array();
		}

		// Validate and sanitize shipping data (postal codes only).
		$validated = array();

		if ( ! empty( $shipping['from_postal_code'] ) ) {
			$validated['from_postal_code'] = substr( sanitize_text_field( $shipping['from_postal_code'] ), 0, 10 );
		}

		if ( ! empty( $shipping['to_postal_code'] ) ) {
			$validated['to_postal_code'] = substr( sanitize_text_field( $shipping['to_postal_code'] ), 0, 10 );
		}

		return $validated;
	}
}
