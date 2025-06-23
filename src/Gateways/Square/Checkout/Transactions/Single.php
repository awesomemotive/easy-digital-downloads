<?php
/**
 * Single transaction class for the Square integration.
 *
 * @package     EDD\Gateways\Square\Checkout\Transactions
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Checkout\Transactions;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Gateways\Square\Helpers\Order;
use EDD\Gateways\Square\Helpers\Payment;
use EDD\Vendor\Square\Models\Order as SquareOrder;
use EDD\Vendor\Square\Models\Payment as SquarePayment;

/**
 * Single transaction class for the Square integration.
 *
 * @since 3.4.0
 */
class Single extends Transaction {

	/**
	 * Process the transaction.
	 *
	 * @since 3.4.0
	 * @return array
	 * @throws \Exception If there is an error processing the transaction.
	 */
	public function process() {
		$result = array();

		// Create an order.
		$this->order_request = Order::build_order_request( $this->purchase_data, $this->args );
		$this->order         = Order::create_order( $this->order_request );

		if ( ! $this->order instanceof SquareOrder ) {
			// We shouldn't get here, as the create_order() function should throw an exception, but just in case.
			edd_debug_log( 'EDD Square: Order is not an instance of Order.' );
			throw new \Exception(
				sprintf(
					/* translators: %s is a reference code for the error, to help with customer support */
					__( 'There was an error processing your order. Please try again. Reference: %s', 'easy-digital-downloads' ),
					'SQ1004'
				)
			);
		}

		// Create a payment.
		$this->payment_request = Payment::build_payment_request( $this->purchase_data, $this->args, $this->order );
		$this->payment         = Payment::create_payment( $this->payment_request, $this->order );

		// Now that a payment attempt has been made, update the local order objects.
		$this->order = Order::get_order( $this->order->getId() );

		// In this case we should cancel an order.
		if ( ! $this->payment instanceof SquarePayment ) {
			// We shouldn't get here, as the create_payment() function should throw an exception, but just in case.
			edd_debug_log( 'EDD Square: Payment is not an instance of Payment.' );
			throw new \Exception(
				sprintf(
					/* translators: %s is a reference code for the error, to help with customer support */
					__( 'There was an error processing your payment. Please try again. Reference: %s', 'easy-digital-downloads' ),
					'SQ1006'
				)
			);
		}

		$result['order']   = $this->order;
		$result['payment'] = $this->payment;

		return $result;
	}
}
