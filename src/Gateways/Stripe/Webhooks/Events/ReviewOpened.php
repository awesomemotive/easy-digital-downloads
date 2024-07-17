<?php
/**
 * Handle the Stripe Review Opened event.
 *
 * @package     EDD
 * @subpackage  Gateways\Stripe\Webhooks\Events
 * @since       3.3.0
 */

namespace EDD\Gateways\Stripe\Webhooks\Events;
use EDD_Exception;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class ReviewOpened
 *
 * @since 3.3.0
 */
class ReviewOpened extends Event {

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
	 * @throws EDD_Exception When the PaymentIntent cannot be retrieved.
	 *
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

			try {
				$payment_intent = edds_api_request( 'PaymentIntent', 'retrieve', $payment_intent );
				$charge         = $payment_intent->charges->data[0]->id;
			} catch ( EDD_Exception $exception ) {
				throw $exception;
			}
		}

		$order_id = edd_get_order_id_from_transaction_id( $charge );
		$order    = edd_get_order( $order_id );

		if ( $order instanceof \EDD\Orders\Order ) {
			edd_add_note(
				array(
					'object_id'   => $order_id,
					'object_type' => 'order',
					'content'     => sprintf(
						/* translators: %s Stripe Radar review opening reason. */
						__( 'Stripe Radar review opened with a reason of %s.', 'easy-digital-downloads' ),
						$this->object->reason
					),
				)
			);

			do_action( 'edd_stripe_review_opened', $this->object, $order->id );
		}
	}
}
