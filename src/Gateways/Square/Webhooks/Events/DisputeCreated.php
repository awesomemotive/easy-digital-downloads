<?php
/**
 * Handle the Square dispute creation event.
 *
 * @package     EDD\Gateways\Square\Webhooks\Events
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Webhooks\Events;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class DisputeCreated
 *
 * @since 3.4.0
 */
class DisputeCreated extends Event {

	/**
	 * The event object.
	 *
	 * @since 3.4.0
	 *
	 * @var array
	 */
	protected $object;

	/**
	 * Process the event.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public function process() {
		$order_id = edd_get_order_id_from_transaction_id( $this->object['dispute']['disputed_payment']['payment_id'] );
		if ( $order_id ) {
			edd_record_order_dispute( $order_id, $this->object['dispute']['id'], $this->object['dispute']['reason'] );
			do_action( 'edd_square_dispute_created', $this->object, $order_id );
		}
	}
}
