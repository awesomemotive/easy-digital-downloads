<?php
/**
 * Webhooks.
 *
 * @package EDD_Stripe
 * @since   2.7.0
 */

/**
 * Listen for Stripe Webhooks.
 *
 * @since 1.5
 */
function edds_stripe_event_listener() {
	if ( ! isset( $_GET['edd-listener'] ) || 'stripe' !== $_GET['edd-listener'] ) {
		return;
	}

	try {
		// Retrieve the request's body and parse it as JSON.
		$body  = @file_get_contents( 'php://input' );
		$event = json_decode( $body );

		if ( isset( $event->id ) ) {
			$event = edds_api_request( 'Event', 'retrieve', $event->id );
		} else {
			throw new \Exception( esc_html__( 'Unable to find Event', 'easy-digital-downloads' ) );
		}

		// Handle events.
		//
		switch ( $event->type ) {

			// Charge succeeded. Update EDD Payment address.
			case 'charge.succeeded':
				$charge   = $event->data->object;
				$order_id = edd_get_order_id_from_transaction_id( $charge->id );
				$order    = edd_get_order( $order_id );

				if ( $order instanceof \EDD\Orders\Order ) {
					$customer = edd_get_customer( $order->customer_id );
					$address  = array(
						'order_id'    => $order_id,
						'name'        => $customer->name,
						'address'     => $charge->billing_details->address->line1,
						'address2'    => $charge->billing_details->address->line2,
						'region'      => $charge->billing_details->address->state,
						'city'        => $charge->billing_details->address->city,
						'postal_code' => $charge->billing_details->address->postal_code,
						'country'     => $charge->billing_details->address->country,
					);

					edd_add_order_address( $address );
				}

				break;

			// Charge refunded. Ensure EDD Payment status is correct.
			case 'charge.refunded':
				$charge = $event->data->object;
				// This is an uncaptured PaymentIntent, not a true refund.
				if ( ! $charge->captured ) {
					return;
				}

				$order_id = edd_get_order_id_from_transaction_id( $charge->id );
				$order    = edd_get_order( $order_id );

				if ( ! $order instanceof \EDD\Orders\Order ) {
					return;
				}

				// If this was completely refunded, set the status to refunded.
				if ( $charge->refunded ) {
					$refund_id = edd_refund_order( $order->id );
					if ( $refund_id ) {
						edd_add_order_transaction(
							array(
								'object_type'    => 'order',
								'object_id'      => $refund_id,
								'transaction_id' => $charge->id,
								'gateway'        => 'stripe',
								'total'          => $order->total,
								'status'         => 'complete',
								'currency'       => $order->currency,
							)
						);
					} else {
						edd_update_order_status( $order->id, 'refunded' );
					}
					// Translators: The charge ID from Stripe that is being refunded.
					$note = sprintf( __( 'Charge %s has been fully refunded in Stripe.', 'easy-digital-downloads' ), $charge->id );
				} else {
					edd_update_order_status( $order->id, 'partially_refunded' );
					// Translators: The charge ID from Stripe that is being partially refunded.
					$note = sprintf( __( 'Charge %s partially refunded in Stripe.', 'easy-digital-downloads' ), $charge->id );
				}
				edd_add_note(
					array(
						'object_id'   => $order_id,
						'object_type' => 'order',
						'content'     => $note,
					)
				);

				break;

			// Review started.
			case 'review.opened':
				$is_live = ! edd_is_test_mode();
				$review  = $event->data->object;

				// Make sure the modes match.
				if ( $is_live !== $review->livemode ) {
					return;
				}

				$charge = $review->charge;

				// Get the charge from the PaymentIntent.
				if ( ! $charge ) {
					$payment_intent = $review->payment_intent;

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
								/* translators: %s Stripe Radar review opening reason. */
								__( 'Stripe Radar review opened with a reason of %s.', 'easy-digital-downloads' ),
								$review->reason
							),
						)
					);

					do_action( 'edd_stripe_review_opened', $review, $order->id );
				}

				break;

			// Review closed.
			case 'review.closed':
				$is_live = ! edd_is_test_mode();
				$review  = $event->data->object;

				// Make sure the modes match
				if ( $is_live !== $review->livemode ) {
					return;
				}

				$charge = $review->charge;

				// Get the charge from the PaymentIntent.
				if ( ! $charge ) {
					$payment_intent = $review->payment_intent;

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
								$review->reason
							),
						)
					);

					do_action( 'edd_stripe_review_closed', $review, $order->id );
				}

				break;

			case 'charge.dispute.created':
				$is_live = ! edd_is_test_mode();
				$dispute = $event->data->object;

				// Make sure the modes match
				if ( $is_live !== $dispute->livemode ) {
					return;
				}

				$order_id = edd_get_order_id_from_transaction_id( $dispute->charge );
				if ( $order_id ) {
					edd_record_order_dispute( $order_id, $dispute->charge, $dispute->reason );
					do_action( 'edd_stripe_dispute_created', $dispute, $order_id );
				}
				break;
		}

		do_action( 'edds_stripe_event_' . $event->type, $event );

		// Nothing failed, mark complete.
		status_header( 200 );
		die( esc_html( 'EDD Stripe: ' . $event->type ) );

		// Fail, allow a retry.
	} catch ( \Exception $e ) {
		status_header( 500 );
		die( '-2' );
	}
}
add_action( 'init', 'edds_stripe_event_listener' );
