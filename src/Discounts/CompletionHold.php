<?php
/**
 * Completion Hold
 *
 * @package     EDD\Discounts
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.2
 */

namespace EDD\Discounts;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;

/**
 * Puts a paid order on hold instead of completing it when one of its discounts has no use left.
 *
 * The store decides: completing the held order from the admin counts the use past the discount's limit.
 *
 * @since 3.7.2
 */
class CompletionHold implements SubscriberInterface {

	/**
	 * Order meta key marking an order held because a discount had no use left.
	 *
	 * @since 3.7.2
	 * @var string
	 */
	const META_KEY = '_edd_discount_completion_held';

	/**
	 * Gets the subscribed events.
	 *
	 * @since 3.7.2
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return array(
			'edd_should_update_payment_status' => array( 'maybe_hold', 10, 4 ),
		);
	}

	/**
	 * Holds an order moving to complete when it can't claim a use of every discount on it.
	 *
	 * Both `edd_update_order_status()` and `EDD_Payment::update_status()` apply this filter, so it covers every way an
	 * order is completed; `edd_should_update_order_status` runs only in the first.
	 *
	 * @since 3.7.2
	 *
	 * @param bool        $do_change  Whether the status should change.
	 * @param int         $order_id   The order ID.
	 * @param string      $new_status The new status.
	 * @param string|bool $old_status The previous status.
	 * @return bool
	 */
	public function maybe_hold( $do_change, $order_id, $new_status, $old_status ) {
		if ( ! $do_change || 'complete' !== $new_status || $this->is_admin_decision() ) {
			return $do_change;
		}

		$order = edd_get_order( $order_id );
		if ( empty( $order ) || Claims::claim_for_completion( $order, (string) $old_status ) ) {
			return $do_change;
		}

		if ( 'on_hold' !== $order->status ) {
			$this->hold( $order, (string) $old_status );
		}

		return false;
	}

	/**
	 * Whether an order is on hold because a discount had no use left.
	 *
	 * @since 3.7.2
	 *
	 * @param int $order_id The order ID.
	 * @return bool
	 */
	public static function holds( $order_id ): bool {
		$order = edd_get_order( $order_id );

		return ! empty( $order ) && 'on_hold' === $order->status && ! empty( edd_get_order_meta( $order->id, self::META_KEY, true ) );
	}

	/**
	 * Moves an order to on hold and tells the store why.
	 *
	 * @since 3.7.2
	 *
	 * @param \EDD\Orders\Order $order       The order.
	 * @param string            $from_status The status the order is leaving.
	 * @return void
	 */
	private function hold( $order, $from_status ) {
		$codes = wp_list_pluck( Claims::get_unclaimed( $order, $from_status ), 'code' );

		edd_update_order_meta( $order->id, self::META_KEY, time() );
		edd_update_order_status( $order->id, 'on_hold' );
		edd_add_note(
			array(
				'object_id'   => $order->id,
				'object_type' => 'order',
				'content'     => sprintf(
					/* translators: %s: Comma-separated discount codes */
					_n(
						'Discount %s had no uses left, so this paid order is on hold. Complete it to count the use past the discount\'s maximum uses, or refund it.',
						'Discounts %s had no uses left, so this paid order is on hold. Complete it to count the uses past the discounts\' maximum uses, or refund it.',
						count( $codes ),
						'easy-digital-downloads'
					),
					implode( ', ', $codes )
				),
			)
		);

		/**
		 * Fires when a paid order is put on hold because a discount on it had no use left.
		 *
		 * @since 3.7.2
		 *
		 * @param \EDD\Orders\Order $order The held order.
		 * @param string[]          $codes The discount codes that had no use left.
		 */
		do_action( 'edd/discounts/completion_held', edd_get_order( $order->id ), $codes );
	}

	/**
	 * Whether a store admin is completing the order on purpose, rather than a gateway or webhook.
	 *
	 * @since 3.7.2
	 * @return bool
	 */
	private function is_admin_decision(): bool {
		return is_admin() && ! wp_doing_ajax() && current_user_can( 'edit_shop_payments' );
	}
}
