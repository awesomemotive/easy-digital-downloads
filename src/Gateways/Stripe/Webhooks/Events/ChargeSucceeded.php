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
		$order_id = edd_get_order_id_from_transaction_id( $this->object->id );
		$order    = edd_get_order( $order_id );

		if ( $order instanceof \EDD\Orders\Order ) {
			$customer = edd_get_customer( $order->customer_id );
			$address  = array(
				'order_id'    => $order_id,
				'name'        => $customer->name,
				'address'     => $this->object->billing_details->address->line1,
				'address2'    => $this->object->billing_details->address->line2,
				'region'      => $this->object->billing_details->address->state,
				'city'        => $this->object->billing_details->address->city,
				'postal_code' => $this->object->billing_details->address->postal_code,
				'country'     => $this->object->billing_details->address->country,
			);

			edd_add_order_address( $address );
		}
	}
}
