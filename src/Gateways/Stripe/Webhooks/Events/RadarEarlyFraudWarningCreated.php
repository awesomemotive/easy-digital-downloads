<?php
/**
 * Handle the Stripe Radar Early Fraud Warning Created event.
 *
 * @package     EDD
 * @subpackage  Gateways\Stripe\Webhooks\Events
 * @since       3.3.0
 */

namespace EDD\Gateways\Stripe\Webhooks\Events;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class RadarEarlyFraudWarningCreated
 *
 * @since 3.3.0
 */
class RadarEarlyFraudWarningCreated extends Event {

	/**
	 * The event object.
	 *
	 * @since 3.3.0
	 *
	 * @var EDD\Vendor\Stripe\Radar\EarlyFraudWarning
	 */
	protected $object;

	/**
	 * Process the event.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public function process() {
		$order_id = edd_get_order_id_from_transaction_id( $this->object->charge );
		$order    = edd_get_order( $order_id );

		if ( $order instanceof \EDD\Orders\Order ) {
			// Add an order note.
			edd_add_note(
				array(
					'object_id'   => $order_id,
					'object_type' => 'order',
					'content'     => sprintf(
						/* translators: %s Stripe Radar early fraud warning reason. */
						__( 'Stripe Radar early fraud warning created with a reason of %s.', 'easy-digital-downloads' ),
						$this->object->fraud_type
					),
				)
			);

			do_action( 'edd_stripe_early_fraud_warning', $order, $this->object );
		}
	}

	/**
	 * Check if the requirements for this event are met.
	 *
	 * The early fraud warning event requires that the store be using the Payment Elements integration. The legacy
	 * Card Elements integration can hit a race condition where the early fraud warning event is triggered before EDD has captured
	 * and created the EDD transaction.
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	public function requirements_met() {
		return 'payment-elements' === edds_get_elements_mode();
	}
}
