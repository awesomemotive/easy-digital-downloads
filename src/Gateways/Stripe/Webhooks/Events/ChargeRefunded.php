<?php
/**
 * Handle the Stripe Charge Refunded event.
 *
 * @package     EDD
 * @subpackage  Gateways\Stripe\Webhooks\Events
 * @since       3.3.0
 */

namespace EDD\Gateways\Stripe\Webhooks\Events;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class ChargeRefunded
 *
 * @since 3.3.0
 */
class ChargeRefunded extends Event {
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
		// This is an PaymentIntent that hasn't been captured, not a true refund.
		if ( ! $this->object->captured ) {
			return;
		}

		$order = $this->get_order();
		if ( ! $order instanceof \EDD\Orders\Order ) {
			return;
		}

		// If this was completely refunded, set the status to refunded.
		if ( $this->object->refunded ) {
			$refund_id = $this->get_refund_id( $order );

			if ( $refund_id && ! is_wp_error( $refund_id ) ) {
				edd_add_order_transaction(
					array(
						'object_type'    => 'order',
						'object_id'      => $refund_id,
						'transaction_id' => $this->object->id,
						'gateway'        => 'stripe',
						'total'          => $order->total,
						'status'         => 'complete',
						'currency'       => $order->currency,
					)
				);
			} else {
				edd_update_order_status( $order->id, 'refunded' );
			}
			/* translators: The charge ID from Stripe that is being refunded. */
			$note = sprintf( __( 'Charge %s has been fully refunded in Stripe.', 'easy-digital-downloads' ), $this->object->id );
		} else {
			edd_update_order_status( $order->id, 'partially_refunded' );
			/* translators: The charge ID from Stripe that is being partially refunded. */
			$note = sprintf( __( 'Charge %s partially refunded in Stripe.', 'easy-digital-downloads' ), $this->object->id );
		}
		edd_add_note(
			array(
				'object_id'   => $order->id,
				'object_type' => 'order',
				'content'     => $note,
			)
		);
	}
}
