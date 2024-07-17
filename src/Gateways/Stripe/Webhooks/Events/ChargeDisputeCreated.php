<?php
/**
 * Handle the Stripe dispute creation event.
 *
 * @package     EDD
 * @subpackage  Gateways\Stripe\Webhooks\Events
 * @since       3.3.0
 */

namespace EDD\Gateways\Stripe\Webhooks\Events;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class ChargeDisputeCreated
 *
 * @since 3.3.0
 */
class ChargeDisputeCreated extends Event {

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
		$order_id = edd_get_order_id_from_transaction_id( $this->object->charge );
		if ( $order_id ) {
			edd_record_order_dispute( $order_id, $this->object->charge, $this->object->reason );
			do_action( 'edd_stripe_dispute_created', $this->object, $order_id );
		}
	}
}
