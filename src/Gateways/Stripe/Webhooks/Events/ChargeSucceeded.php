<?php
/**
 * Handle the Stripe Succeeded Refunded event.
 *
 * @package     EDD
 * @subpackage  Gateways\Stripe\Webhooks\Events
 * @since       3.3.0
 */

namespace EDD\Gateways\Stripe\Webhooks\Events;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class ChargeSucceeded
 *
 * @since 3.3.0
 */
class ChargeSucceeded extends Event {
	use Traits\Order;

	/**
	 * The event object.
	 *
	 * @since 3.3.0
	 *
	 * @var EDD\Vendor\Stripe\Charge
	 */
	protected $object;

	/**
	 * Process the event.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public function process() {
		$order = $this->get_order();
		if ( ! $order instanceof \EDD\Orders\Order ) {
			return;
		}

		$customer = edd_get_customer( $order->customer_id );
		$address  = array(
			'order_id'    => $order->id,
			'name'        => $customer->name,
			'address'     => $this->object->billing_details->address->line1,
			'address2'    => $this->object->billing_details->address->line2,
			'region'      => $this->object->billing_details->address->state,
			'city'        => $this->object->billing_details->address->city,
			'postal_code' => $this->object->billing_details->address->postal_code,
			'country'     => $this->object->billing_details->address->country,
		);

		edd_add_order_address( $address );

		if ( ! empty( $this->object->payment_method_details ) ) {
			\EDD\Gateways\Stripe\Checkout\Order::add_payment_method( $order->id, $this->object->payment_method_details );
		}

		// If the order is pending and has no parent, mark it as complete. This catches slower payment methods like bank transfers.
		if ( $this->should_mark_complete( $order ) ) {
			\EDD\Gateways\Stripe\Checkout\Complete::mark_complete_from_charge( $order, $this->object );
		}
	}

	/**
	 * Determine if the order should be marked as complete from the charge.
	 *
	 * @since 3.3.5
	 * @param \EDD\Orders\Order $order The order object.
	 * @return bool
	 */
	private function should_mark_complete( $order ) {
		if ( 'pending' !== $order->status ) {
			return false;
		}

		if ( ! empty( $order->parent ) ) {
			return false;
		}

		return in_array( edd_get_order_meta( $order->id, 'stripe_payment_method_type', true ), array( 'us_bank_account', 'sofort' ), true );
	}
}
