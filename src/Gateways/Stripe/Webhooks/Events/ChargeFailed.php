<?php
/**
 * Handle the Stripe Charge Failed event.
 *
 * @package     EDD\Gateways\Stripe\Webhooks\Events
 * @since       3.3.5
 */

namespace EDD\Gateways\Stripe\Webhooks\Events;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class ChargeFailed
 *
 * @since 3.3.5
 */
class ChargeFailed extends Event {
	use Traits\Order;

	/**
	 * Process the event.
	 *
	 * @since 3.3.5
	 * @return void
	 */
	public function process() {
		$order = $this->get_order();
		if ( ! $order instanceof \EDD\Orders\Order ) {
			return;
		}

		if ( ! empty( $this->object->payment_method_details ) ) {
			\EDD\Gateways\Stripe\Checkout\Order::add_payment_method( $order->id, $this->object->payment_method_details );
		}

		edd_update_order_status( $order->id, 'failed' );
	}
}
