<?php
/**
 * Order Discount Validation
 *
 * @package     EDD\Discounts
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.2
 */

namespace EDD\Discounts;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Validates the discounts stored on an order.
 *
 * @since 3.7.2
 */
class OrderValidation {

	/**
	 * Gets the codes of the discounts on an order that can no longer be applied to it.
	 *
	 * Checks the discount itself rather than the cart, so it holds for an order built in an earlier request.
	 *
	 * @since 3.7.2
	 *
	 * @param \EDD\Orders\Order $order The order.
	 * @return string[]
	 */
	public static function get_unavailable_codes( $order ): array {
		$codes = array();
		foreach ( $order->get_discounts() as $adjustment ) {
			// Adjustments without a discount ID, such as Software Licensing's renewal discount, have no uses to check.
			if ( empty( $adjustment->type_id ) ) {
				continue;
			}

			$discount = edd_get_discount( $adjustment->type_id );

			if ( empty( $discount ) || ! $discount->is_available_to_order( $order->id, false ) || $discount->is_used( $order->email, false, $order->id ) ) {
				$codes[] = $adjustment->description;
			}
		}

		return $codes;
	}

	/**
	 * Redeems every discount on an order, claiming any use the order doesn't hold.
	 *
	 * @since 3.7.2
	 *
	 * @param \EDD\Orders\Order $order The order.
	 * @return string[] The codes that could not be redeemed; empty when all were.
	 */
	public static function redeem_discounts( $order ): array {
		self::release_stale_uses( $order );

		$codes = self::get_unavailable_codes( $order );
		if ( $codes || Claims::claim( $order ) ) {
			return $codes;
		}

		return wp_list_pluck( Claims::get_unclaimed( $order ), 'code' );
	}

	/**
	 * Refuses a newly built order whose discounts can't all be redeemed.
	 *
	 * Rechecks the per-customer limit, which the claim made while building the order does not.
	 *
	 * @since 3.7.2
	 *
	 * @param int $order_id The order ID.
	 * @return string The message to show the buyer, or an empty string when the order can proceed.
	 */
	public static function refuse_unclaimed( $order_id ): string {
		$order = edd_get_order( $order_id );
		if ( empty( $order ) ) {
			return '';
		}

		$codes = self::redeem_discounts( $order );

		return $codes ? self::refuse( $order_id, $codes ) : '';
	}

	/**
	 * Fails an order whose discounts can't be redeemed, removes them from the cart, and records the buyer's error.
	 *
	 * @since 3.7.2
	 *
	 * @param int      $order_id The order ID.
	 * @param string[] $codes    The codes that could not be redeemed.
	 * @return string The message to show the buyer.
	 */
	public static function refuse( $order_id, array $codes ): string {
		edd_update_order_status( $order_id, 'failed' );

		foreach ( $codes as $code ) {
			edd_unset_cart_discount( $code );
		}

		$message = sprintf(
			/* translators: %s: Comma-separated discount codes */
			_n(
				'The discount %s is no longer available and has been removed from your cart. Please review your total and try again.',
				'The discounts %s are no longer available and have been removed from your cart. Please review your total and try again.',
				count( $codes ),
				'easy-digital-downloads'
			),
			implode( ', ', $codes )
		);

		edd_set_error( 'edd-discount-error', $message );

		return $message;
	}

	/**
	 * Whether the failed transaction page may fail an order and give back its uses.
	 *
	 * An order holding a use keeps it, and stays pending, unless its gateway rechecks discounts before taking payment.
	 * A PayPal Commerce order counts only once it was created at PayPal; one built elsewhere, such as a Recurring
	 * subscription's parent order, never reaches the capture that rechecks it.
	 *
	 * @since 3.7.2
	 *
	 * @param \EDD\Orders\Order $order The order.
	 * @return bool
	 */
	public static function releases_on_failed_page( $order ): bool {
		$rechecks_before_payment = 'paypal_commerce' === $order->gateway && ! empty( edd_get_order_meta( $order->id, 'paypal_order_id', true ) );
		$releases                = $rechecks_before_payment || ! Claims::holds_any( $order->id );

		/**
		 * Filters whether the failed transaction page may fail an order and give back its uses.
		 *
		 * Only return true for a gateway that refuses an unclaimed discount before taking payment.
		 *
		 * @since 3.7.2
		 *
		 * @param bool              $releases Whether the order is failed and its uses given back.
		 * @param \EDD\Orders\Order $order    The order.
		 */
		return (bool) apply_filters( 'edd/discounts/failed_page_releases', $releases, $order );
	}

	/**
	 * Moves a pending order to processing while its payment is taken, so its uses stay claimed.
	 *
	 * @since 3.7.2
	 *
	 * @param int $order_id The order ID.
	 * @return bool Whether this request moved the order; false when it was no longer pending.
	 * @throws \Throwable An error from a status transition listener, after the order is put back to pending.
	 */
	public static function start_capture( $order_id ): bool {
		return self::move_status( $order_id, 'pending', 'processing' );
	}

	/**
	 * Moves a failed order back to pending for another payment attempt.
	 *
	 * @since 3.7.2
	 *
	 * @param int $order_id The order ID.
	 * @return bool Whether this request moved the order; false when it was no longer failed.
	 * @throws \Throwable An error from a status transition listener, after the order is put back to failed.
	 */
	public static function reopen_failed( $order_id ): bool {
		return self::move_status( $order_id, 'failed', 'pending' );
	}

	/**
	 * Moves an order still processing to a status, when its payment was not taken.
	 *
	 * @since 3.7.2
	 *
	 * @param int    $order_id The order ID.
	 * @param string $status   The status to move it to.
	 * @return void
	 * @throws \Throwable An error from a status transition listener, after the order is put back to processing.
	 */
	public static function end_capture( $order_id, $status ) {
		self::move_status( $order_id, 'processing', $status );
	}

	/**
	 * Takes payment for a pending order while it is held in processing.
	 *
	 * Record the payment after this returns, so an error while recording leaves a paid order processing.
	 *
	 * @since 3.7.2
	 *
	 * @param int      $order_id The order ID.
	 * @param callable $pay      Takes the payment and returns PayPal's response.
	 * @return mixed The result of `$pay`, or a REST error when the order is no longer pending.
	 * @throws \Throwable An unexpected error from `$pay`, after the order is put back to pending.
	 */
	public static function hold_capture( $order_id, callable $pay ) {
		if ( ! self::start_capture( $order_id ) ) {
			return new \WP_Error( 'invalid_order', __( 'This order can no longer be processed.', 'easy-digital-downloads' ), array( 'status' => 409 ) );
		}

		try {
			return $pay();
		} catch ( \Throwable $e ) {
			self::end_capture( $order_id, 'pending' );
			throw $e;
		}
	}

	/**
	 * Gets the REST error returned when an order is refused for its discounts.
	 *
	 * @since 3.7.2
	 *
	 * @param string $message The message to show the buyer.
	 * @return \WP_Error
	 */
	public static function get_refusal_error( $message ): \WP_Error {
		return new \WP_Error(
			'invalid_discount',
			$message,
			array(
				'status'  => 409,
				'refresh' => true,
			)
		);
	}

	/**
	 * Gives back a stale use of each full discount an order needs but doesn't hold, when the customer may still use it.
	 *
	 * @since 3.7.2
	 *
	 * @param \EDD\Orders\Order $order The order.
	 * @return void
	 */
	private static function release_stale_uses( $order ) {
		foreach ( $order->get_discounts() as $adjustment ) {
			if ( empty( $adjustment->type_id ) || Claims::holds( $order->id, $adjustment->type_id ) ) {
				continue;
			}

			$discount = edd_get_discount( $adjustment->type_id );
			if ( $discount && $discount->is_maxed_out( false ) && ! $discount->is_used( $order->email, false, $order->id ) ) {
				Claims::release_stale( $discount );
			}
		}
	}

	/**
	 * Moves an order from one status to another, only while it still has the first.
	 *
	 * Puts the order back when a listener on the transition throws, so it is not stranded.
	 *
	 * @since 3.7.2
	 *
	 * @param int    $order_id The order ID.
	 * @param string $from     The status the order must have.
	 * @param string $to       The status to move it to.
	 * @return bool Whether this request moved the order.
	 * @throws \Throwable An error from a transition listener, after the order is put back.
	 */
	private static function move_status( $order_id, $from, $to ): bool {
		if ( ! self::write_status( $order_id, $from, $to ) ) {
			return false;
		}

		// The conditional write bypasses the order query, so fire the transition it would have.
		try {
			do_action( 'edd_transition_order_status', $from, $to, $order_id );
		} catch ( \Throwable $e ) {
			self::write_status( $order_id, $to, $from );
			throw $e;
		}

		return true;
	}

	/**
	 * Writes an order's status only while it still has the expected one, and its items' statuses with it.
	 *
	 * @since 3.7.2
	 *
	 * @param int    $order_id The order ID.
	 * @param string $from     The status the order must have.
	 * @param string $to       The status to write.
	 * @return bool Whether the row changed.
	 */
	private static function write_status( $order_id, $from, $to ): bool {
		global $wpdb;

		// nosemgrep: awesomemotive.wp-sql-injection-audit
		$moved = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->edd_orders} SET status = %s, date_modified = %s WHERE id = %d AND status = %s",
				$to,
				gmdate( 'Y-m-d H:i:s' ),
				$order_id,
				$from
			)
		);
		( new \EDD\Database\Queries\Order() )->refresh_item_cache( $order_id );

		if ( 1 !== $moved ) {
			return false;
		}

		$items = edd_get_order_items(
			array(
				'order_id' => $order_id,
				// Berlin's no-limit value.
				'number'   => 0,
			)
		);
		foreach ( $items as $item ) {
			edd_update_order_item( $item->id, array( 'status' => $to ) );
		}

		return true;
	}
}
