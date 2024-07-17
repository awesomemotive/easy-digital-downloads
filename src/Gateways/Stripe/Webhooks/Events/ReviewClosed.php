<?php
/**
 * Handle the Stripe Review Closed event.
 *
 * @package     EDD
 * @subpackage  Gateways\Stripe\Webhooks\Events
 * @since       3.3.0
 */

namespace EDD\Gateways\Stripe\Webhooks\Events;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class ReviewClosed
 *
 * @since 3.3.0
 */
class ReviewClosed extends Event {

	/**
	 * The event object.
	 *
	 * @since 3.3.0
	 *
	 * @var EDD\Vendor\Stripe\Radar\Review
	 */
	protected $object;

	/**
	 * Process the event.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public function process() {
		$charge = $this->object->charge;

		// Get the charge from the PaymentIntent.
		if ( ! $charge ) {
			$payment_intent = $this->object->payment_intent;

			if ( ! $payment_intent ) {
				return;
			}

			$payment_intent = edds_api_request( 'PaymentIntent', 'retrieve', $payment_intent );
			$charge         = $payment_intent->charges->data[0]->id;
		}

		$order_id = edd_get_order_id_from_transaction_id( $charge );
		$order    = edd_get_order( $order_id );

		if ( $order instanceof \EDD\Orders\Order ) {
			edd_add_note(
				array(
					'object_id'   => $order_id,
					'object_type' => 'order',
					'content'     => sprintf(
						/* translators: %s Stripe Radar review closing reason. */
						__( 'Stripe Radar review closed with a reason of %s.', 'easy-digital-downloads' ),
						$this->object->reason
					),
				)
			);

			do_action( 'edd_stripe_review_closed', $this->object, $order->id );
		}
	}
}
