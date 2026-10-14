<?php
/**
 * Discount Claims
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
 * Tracks the discount uses each order holds.
 *
 * An order holds a use from the moment it is built until it fails, is abandoned, canceled, deleted, trashed
 * while pending, or sits pending past the release window. Each held use is one order meta row, written after
 * its increment and removed before its decrement.
 *
 * @since 3.7.2
 */
class Claims implements SubscriberInterface {

	/**
	 * Order meta key prefix; the discount ID is appended.
	 *
	 * @since 3.7.2
	 * @var string
	 */
	const META_PREFIX = '_edd_discount_claim_';

	/**
	 * Order meta key marking that an order completed before claims were tracked no longer holds its uses as legacy ones.
	 *
	 * @since 3.7.2
	 * @var string
	 */
	const LEGACY_RELEASED_KEY = '_edd_discount_legacy_released';

	/**
	 * Option holding the time claims started being tracked.
	 *
	 * @since 3.7.2
	 * @var string
	 */
	const STARTED_OPTION = 'edd_discount_claims_started';

	/**
	 * Order statuses that give back the uses an order holds.
	 *
	 * @since 3.7.2
	 * @var string[]
	 */
	const RELEASE_STATUSES = array( 'failed', 'abandoned', 'cancelled' );

	/**
	 * Order statuses in which an order completed before claims were tracked had its uses counted.
	 *
	 * @since 3.7.2
	 * @var string[]
	 */
	const COUNTED_STATUSES = array( 'complete', 'publish', 'completed', 'partially_refunded', 'revoked', 'refunded', 'on_hold' );

	/**
	 * How long, in seconds, an unpaid PayPal Commerce order holds its discount uses before they may be given back.
	 *
	 * @since 3.7.2
	 * @var int
	 */
	const STALE_AGE = HOUR_IN_SECONDS;

	/**
	 * Discount IDs held by each order, read once per request and cleared on every write.
	 *
	 * @since 3.7.2
	 * @var array
	 */
	private static $held = array();

	/**
	 * Gets the subscribed events.
	 *
	 * @since 3.7.2
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return array(
			'init'                        => 'record_start',
			'edd_transition_order_status' => array( 'maybe_release', 10, 3 ),
		);
	}

	/**
	 * Records when claims started being tracked, before any order can complete under them.
	 *
	 * @since 3.7.2
	 * @return void
	 */
	public function record_start() {
		self::get_started();
	}

	/**
	 * Releases an order's uses when it moves to a status that gives them back.
	 *
	 * A move between counted statuses turns uses counted before claims were tracked into claims, since the
	 * legacy judgment only holds for the status the order was counted in.
	 *
	 * @since 3.7.2
	 *
	 * @param string $old_status The previous status.
	 * @param string $new_status The new status.
	 * @param int    $order_id   The order ID.
	 * @return void
	 */
	public function maybe_release( $old_status, $new_status, $order_id ) {
		if ( in_array( $old_status, self::COUNTED_STATUSES, true ) && in_array( $new_status, self::COUNTED_STATUSES, true ) ) {
			self::adopt_legacy( $order_id, $old_status );
			return;
		}

		$releases = in_array( $new_status, self::RELEASE_STATUSES, true )
			|| ( 'trash' === $new_status && 'pending' === $old_status );

		if ( $releases ) {
			self::release( $order_id, '', $old_status );
		}
	}

	/**
	 * Claims a use of each discount on an order that it does not already hold.
	 *
	 * Uses held for discounts no longer on the order are given back.
	 *
	 * @since 3.7.2
	 *
	 * @param \EDD\Orders\Order $order       The order.
	 * @param string            $from_status The status the order is leaving, when claiming on a status change.
	 * @return bool Whether the order now holds a use of every discount on it.
	 */
	public static function claim( $order, $from_status = '' ): bool {
		if ( ! self::can_hold( $order ) ) {
			return false;
		}

		return self::claim_unheld( $order, $from_status );
	}

	/**
	 * Claims the uses a sale needs before it moves to complete.
	 *
	 * Unlike `claim()`, an order still in a status that gives uses back can claim, since it is about to complete.
	 *
	 * @since 3.7.2
	 *
	 * @param \EDD\Orders\Order $order       The order.
	 * @param string            $from_status The status the order is leaving.
	 * @return bool Whether the order can complete holding a use of every discount on it.
	 */
	public static function claim_for_completion( $order, $from_status ): bool {
		if ( empty( $order ) || 'sale' !== $order->type ) {
			return true;
		}

		return self::claim_unheld( $order, $from_status );
	}

	/**
	 * Claims a use of each discount on a paid order that it could not claim, even past the discount's limit.
	 *
	 * @since 3.7.2
	 *
	 * @param \EDD\Orders\Order $order       The order.
	 * @param string            $from_status The status the order is leaving, when claiming on a status change.
	 * @return \EDD_Discount[] The discounts counted past their limit.
	 */
	public static function claim_past_limit( $order, $from_status = '' ): array {
		if ( ! self::can_hold( $order ) ) {
			return array();
		}

		$past_limit = array();
		foreach ( self::get_unclaimed( $order, $from_status ) as $discount ) {
			if ( false === $discount->increase_usage_past_limit() ) {
				continue;
			}

			self::add_marker( $order->id, $discount->id );
			$past_limit[] = $discount;
		}

		self::forget( $order->id );

		return $past_limit;
	}

	/**
	 * Gives back every use an order holds.
	 *
	 * @since 3.7.2
	 *
	 * @param int    $order_id       The order ID.
	 * @param string $only_if_status Only release while the order has this status.
	 * @param string $from_status    The status the order is leaving, when releasing on a status change.
	 * @return int The number of uses given back.
	 */
	public static function release( $order_id, $only_if_status = '', $from_status = '' ): int {
		$order_id = absint( $order_id );
		$released = 0;

		foreach ( self::get_held( $order_id ) as $discount_id ) {
			$released += self::release_one( $order_id, $discount_id, $only_if_status );
		}

		$order = edd_get_order( $order_id );
		if ( $order && ! $only_if_status ) {
			$status = $from_status ? $from_status : $order->status;
			if ( 'trash' === $status ) {
				$status = edd_get_order_meta( $order->id, '_pre_trash_status', true );
			}
			$released += self::release_legacy( $order, $status );
		}

		self::forget( $order_id );

		return $released;
	}

	/**
	 * Gives back one use of a full discount held by an unpaid order left pending past the stale age.
	 *
	 * Nothing is given back unless reaching the cap is all that refuses the discount.
	 *
	 * @since 3.7.2
	 *
	 * @param \EDD_Discount $discount The discount.
	 * @return bool Whether a use was given back.
	 */
	public static function release_stale( $discount ): bool {
		if ( ! self::is_refused_only_by_cap( $discount ) ) {
			return false;
		}

		$candidates = self::get_releasable_orders( gmdate( 'Y-m-d H:i:s', time() - self::STALE_AGE ), $discount->id, 5 );

		// A candidate that completed or was released since the lookup gives nothing back, so try the next.
		foreach ( $candidates as $candidate ) {
			if ( self::release_one( $candidate, $discount->id, 'pending' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Gets unpaid pending orders, oldest first, whose discount uses may be given back.
	 *
	 * Only PayPal Commerce orders created at PayPal qualify, because their capture claims the use again or
	 * refuses the payment. Other gateways can still complete a pending order, and an order with a recorded
	 * transaction has been paid, so both keep their uses.
	 *
	 * @since 3.7.2
	 *
	 * @param string $before      Only orders created before this GMT date.
	 * @param int    $discount_id Only orders holding a use of this discount; 0 for any discount.
	 * @param int    $limit       The most orders to return.
	 * @return int[] The order IDs.
	 */
	public static function get_releasable_orders( $before, $discount_id = 0, $limit = 100 ): array {
		global $wpdb;

		$claim_key = $wpdb->esc_like( self::META_PREFIX ) . ( $discount_id ? absint( $discount_id ) : '%' );

		// The sweep releases every match, so only a single discount's lookup needs the oldest first.
		$order_by = $discount_id ? 'ORDER BY o.date_created ASC' : '';

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $order_by is one of two literals.
		// nosemgrep: awesomemotive.wp-sql-injection-audit
		$order_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT o.id, o.date_created FROM {$wpdb->edd_orders} o
				INNER JOIN {$wpdb->edd_ordermeta} m ON m.edd_order_id = o.id AND m.meta_key LIKE %s
				WHERE o.status = 'pending' AND o.type = 'sale' AND o.gateway = 'paypal_commerce'
				AND o.date_created < %s AND ( o.date_completed IS NULL OR o.date_completed = '0000-00-00 00:00:00' )
				AND EXISTS ( SELECT 1 FROM {$wpdb->edd_ordermeta} p WHERE p.edd_order_id = o.id AND p.meta_key = 'paypal_order_id' )
				AND NOT EXISTS ( SELECT 1 FROM {$wpdb->edd_order_transactions} t WHERE t.object_type = 'order' AND t.object_id = o.id )
				{$order_by}
				LIMIT %d",
				$claim_key,
				$before,
				absint( $limit )
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return array_map( 'absint', $order_ids );
	}

	/**
	 * Whether an order holds a use of a discount.
	 *
	 * @since 3.7.2
	 *
	 * @param int $order_id    The order ID.
	 * @param int $discount_id The discount ID.
	 * @return bool
	 */
	public static function holds( $order_id, $discount_id ): bool {
		if ( empty( $order_id ) || empty( $discount_id ) ) {
			return false;
		}

		return in_array( absint( $discount_id ), self::get_held( $order_id ), true );
	}

	/**
	 * Whether an order holds a use of any discount.
	 *
	 * @since 3.7.2
	 *
	 * @param int $order_id The order ID.
	 * @return bool
	 */
	public static function holds_any( $order_id ): bool {
		return ! empty( self::get_held( $order_id ) );
	}

	/**
	 * Gets the discounts on an order that it does not hold a use of.
	 *
	 * @since 3.7.2
	 *
	 * @param \EDD\Orders\Order $order       The order.
	 * @param string            $from_status The status the order is leaving, when checking on a status change.
	 * @return \EDD_Discount[]
	 */
	public static function get_unclaimed( $order, $from_status = '' ): array {
		$held      = self::get_held_by( $order, $from_status );
		$unclaimed = array();

		foreach ( array_diff( self::get_discount_ids( $order ), $held ) as $discount_id ) {
			$discount = edd_get_discount( $discount_id );
			if ( $discount ) {
				$unclaimed[] = $discount;
			}
		}

		return $unclaimed;
	}

	/**
	 * Gets the orders, among those given, that hold a use of a discount.
	 *
	 * @since 3.7.2
	 *
	 * @param int   $discount_id The discount ID.
	 * @param int[] $order_ids   The orders to look in.
	 * @return int[]
	 */
	public static function get_holders( $discount_id, $order_ids ): array {
		global $wpdb;

		$order_ids = array_filter( array_map( 'absint', (array) $order_ids ) );
		if ( empty( $order_ids ) ) {
			return array();
		}

		$placeholders = implode( ', ', array_fill( 0, count( $order_ids ), '%d' ) );

		// nosemgrep: awesomemotive.wp-sql-injection-audit
		$holders = $wpdb->get_col(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT DISTINCT edd_order_id FROM {$wpdb->edd_ordermeta} WHERE meta_key = %s AND edd_order_id IN ( {$placeholders} )",
				array_merge( array( self::META_PREFIX . absint( $discount_id ) ), $order_ids )
			)
		);

		return array_map( 'absint', $holders );
	}

	/**
	 * Gets the order the current session would resume at checkout.
	 *
	 * @since 3.7.2
	 * @return int The order ID, or 0.
	 */
	public static function get_resumable_order_id(): int {
		if ( empty( EDD()->session ) ) {
			return 0;
		}

		$order_id = absint( EDD()->session->get( 'edd_resume_payment' ) );
		if ( empty( $order_id ) ) {
			return 0;
		}

		$order = edd_get_order( $order_id );
		if ( empty( $order ) || ! $order->is_recoverable() ) {
			return 0;
		}

		return $order_id;
	}

	/**
	 * Whether reaching its cap is all that refuses a discount.
	 *
	 * A code over its cap stays refused after one release, and a code set inactive on purpose stays off.
	 *
	 * @since 3.7.2
	 *
	 * @param \EDD_Discount $discount The discount.
	 * @return bool
	 */
	private static function is_refused_only_by_cap( $discount ): bool {
		if ( empty( $discount ) || empty( $discount->max_uses ) || (int) $discount->get_uses() !== (int) $discount->max_uses ) {
			return false;
		}

		$is_on = 'active' === $discount->status
			|| ( 'inactive' === $discount->status && ! ManualDeactivation::is_manual( $discount->id ) );

		return $is_on && ! $discount->is_expired( false ) && $discount->is_started( false );
	}

	/**
	 * Gives back one discount use an order holds.
	 *
	 * @since 3.7.2
	 *
	 * @param int    $order_id       The order ID.
	 * @param int    $discount_id    The discount ID.
	 * @param string $only_if_status Only release while the order has this status.
	 * @return int The number of uses given back.
	 */
	private static function release_one( $order_id, $discount_id, $only_if_status = '' ): int {
		global $wpdb;

		$meta_key = self::META_PREFIX . absint( $discount_id );

		if ( $only_if_status ) {
			// nosemgrep: awesomemotive.wp-sql-injection-audit
			$removed = $wpdb->query(
				$wpdb->prepare(
					"DELETE m FROM {$wpdb->edd_ordermeta} m INNER JOIN {$wpdb->edd_orders} o ON o.id = m.edd_order_id WHERE m.edd_order_id = %d AND m.meta_key = %s AND o.status = %s",
					$order_id,
					$meta_key,
					$only_if_status
				)
			);
		} else {
			// nosemgrep: awesomemotive.wp-sql-injection-audit
			$removed = $wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->edd_ordermeta} WHERE edd_order_id = %d AND meta_key = %s",
					$order_id,
					$meta_key
				)
			);
		}

		$removed = absint( $removed );
		if ( $removed ) {
			self::decrease( $discount_id, $removed );
		}

		self::forget( $order_id );

		return $removed;
	}

	/**
	 * Gives back the uses an order completed before claims were tracked was counted for.
	 *
	 * @since 3.7.2
	 *
	 * @param \EDD\Orders\Order $order  The order.
	 * @param string            $status The status the order is being released from.
	 * @return int The number of uses given back.
	 */
	private static function release_legacy( $order, $status ): int {
		$legacy = self::get_legacy_held( $order, $status );
		if ( empty( $legacy ) ) {
			return 0;
		}

		// Recorded first, so releasing the order again gives nothing back.
		if ( ! add_metadata( 'edd_order', $order->id, self::LEGACY_RELEASED_KEY, time(), true ) ) {
			return 0;
		}

		foreach ( $legacy as $discount_id ) {
			self::decrease( $discount_id, 1 );
		}

		return count( $legacy );
	}

	/**
	 * Records the uses an order completed before claims were tracked was counted for as claims, without counting them again.
	 *
	 * @since 3.7.2
	 *
	 * @param int    $order_id The order ID.
	 * @param string $status   The status the order is leaving.
	 * @return void
	 */
	private static function adopt_legacy( $order_id, $status ) {
		$order = edd_get_order( $order_id );
		if ( empty( $order ) ) {
			return;
		}

		$legacy = self::get_legacy_held( $order, $status );
		if ( empty( $legacy ) ) {
			return;
		}

		// Recorded first, so a concurrent move can't adopt the uses twice.
		if ( ! add_metadata( 'edd_order', $order->id, self::LEGACY_RELEASED_KEY, time(), true ) ) {
			return;
		}

		foreach ( $legacy as $discount_id ) {
			add_metadata( 'edd_order', $order->id, self::META_PREFIX . absint( $discount_id ), time() );
		}

		self::forget( $order->id );
	}

	/**
	 * Claims the uses an order does not hold yet, without checking that its status can hold them.
	 *
	 * @since 3.7.2
	 *
	 * @param \EDD\Orders\Order $order       The order.
	 * @param string            $from_status The status the order is leaving, when claiming on a status change.
	 * @return bool Whether the order now holds a use of every discount on it.
	 */
	private static function claim_unheld( $order, $from_status ): bool {
		$discount_ids = self::get_discount_ids( $order );
		$held         = self::get_held_by( $order, $from_status );

		foreach ( array_diff( self::get_held( $order->id ), $discount_ids ) as $discount_id ) {
			self::release_one( $order->id, $discount_id );
		}

		$holds_all = true;
		foreach ( array_diff( $discount_ids, $held ) as $discount_id ) {
			$discount = edd_get_discount( $discount_id );
			if ( empty( $discount ) ) {
				continue;
			}

			$claimed = false !== $discount->increase_usage();
			if ( ! $claimed && self::release_stale( $discount ) ) {
				$claimed = false !== $discount->increase_usage();
			}

			if ( ! $claimed ) {
				$holds_all = false;
				continue;
			}

			self::add_marker( $order->id, $discount_id );
		}

		self::forget( $order->id );

		return $holds_all;
	}

	/**
	 * Whether an order is one that can hold discount uses.
	 *
	 * @since 3.7.2
	 *
	 * @param \EDD\Orders\Order|false $order The order.
	 * @return bool
	 */
	private static function can_hold( $order ): bool {
		return ! empty( $order ) && 'sale' === $order->type && ! in_array( $order->status, self::RELEASE_STATUSES, true );
	}

	/**
	 * Records a claimed use, undoing the claim if another request recorded it first.
	 *
	 * @since 3.7.2
	 *
	 * @param int $order_id    The order ID.
	 * @param int $discount_id The discount ID.
	 * @return void
	 */
	private static function add_marker( $order_id, $discount_id ) {
		global $wpdb;

		$meta_key = self::META_PREFIX . absint( $discount_id );

		$wpdb->insert(
			$wpdb->edd_ordermeta,
			array(
				'edd_order_id' => $order_id,
				'meta_key'     => $meta_key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'   => time(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			),
			array( '%d', '%s', '%d' )
		);
		$meta_id = (int) $wpdb->insert_id;

		// nosemgrep: awesomemotive.wp-sql-injection-audit
		$first_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT MIN(meta_id) FROM {$wpdb->edd_ordermeta} WHERE edd_order_id = %d AND meta_key = %s",
				$order_id,
				$meta_key
			)
		);

		if ( $first_id === $meta_id ) {
			return;
		}

		// nosemgrep: awesomemotive.wp-sql-injection-audit
		$removed = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->edd_ordermeta} WHERE meta_id = %d", $meta_id ) );
		if ( $removed ) {
			self::decrease( $discount_id, 1 );
		}
	}

	/**
	 * Gives back uses of a discount, if it still exists.
	 *
	 * @since 3.7.2
	 *
	 * @param int $discount_id The discount ID.
	 * @param int $uses        The number of uses.
	 * @return void
	 */
	private static function decrease( $discount_id, $uses ) {
		$discount = edd_get_discount( $discount_id );
		if ( empty( $discount ) ) {
			return;
		}

		for ( $i = 0; $i < $uses; $i++ ) {
			$discount->decrease_usage();
		}
	}

	/**
	 * Gets the IDs of the discounts an order holds a use of, including uses counted before claims were tracked.
	 *
	 * @since 3.7.2
	 *
	 * @param \EDD\Orders\Order $order  The order.
	 * @param string            $status The status to judge uses counted before claims were tracked by; defaults to its current one.
	 * @return int[]
	 */
	private static function get_held_by( $order, $status = '' ): array {
		if ( 'trash' === $status ) {
			$status = edd_get_order_meta( $order->id, '_pre_trash_status', true );
		}

		return array_values( array_unique( array_merge( self::get_held( $order->id ), self::get_legacy_held( $order, $status ) ) ) );
	}

	/**
	 * Gets the discounts an order completed before claims were tracked was counted for, if not yet given back.
	 *
	 * @since 3.7.2
	 *
	 * @param \EDD\Orders\Order $order  The order.
	 * @param string            $status The status to judge the order by; defaults to its current one.
	 * @return int[]
	 */
	private static function get_legacy_held( $order, $status = '' ): array {
		$status = $status ? $status : $order->status;
		if ( ! in_array( $status, self::COUNTED_STATUSES, true ) ) {
			return array();
		}

		$counted_at = empty( $order->date_completed ) || '0000-00-00 00:00:00' === $order->date_completed ? '' : $order->date_completed;
		if ( ! $counted_at ) {
			// Orders imported as complete can lack a completed date; any other order without one never completed.
			if ( ! in_array( $status, array( 'complete', 'publish', 'completed' ), true ) ) {
				return array();
			}
			$counted_at = $order->date_created;
		}

		if ( strtotime( $counted_at ) >= self::get_started() || self::get_held( $order->id ) ) {
			return array();
		}

		if ( edd_get_order_meta( $order->id, self::LEGACY_RELEASED_KEY, true ) ) {
			return array();
		}

		return self::get_discount_ids( $order );
	}

	/**
	 * Gets the time claims started being tracked on this site.
	 *
	 * @since 3.7.2
	 * @return int
	 */
	private static function get_started(): int {
		$started = (int) get_option( self::STARTED_OPTION );
		if ( ! $started ) {
			$started = time();
			add_option( self::STARTED_OPTION, $started );
		}

		return $started;
	}

	/**
	 * Gets the IDs of the discounts an order holds a claim marker for.
	 *
	 * Read from the table so a cached copy of the meta can't report a use already given back.
	 *
	 * @since 3.7.2
	 *
	 * @param int $order_id The order ID.
	 * @return int[]
	 */
	private static function get_held( $order_id ): array {
		global $wpdb;

		$order_id = absint( $order_id );
		if ( isset( self::$held[ $order_id ] ) ) {
			return self::$held[ $order_id ];
		}

		// nosemgrep: awesomemotive.wp-sql-injection-audit
		$keys = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT meta_key FROM {$wpdb->edd_ordermeta} WHERE edd_order_id = %d AND meta_key LIKE %s",
				$order_id,
				$wpdb->esc_like( self::META_PREFIX ) . '%'
			)
		);

		self::$held[ $order_id ] = array_map(
			function ( $key ) {
				return absint( substr( $key, strlen( self::META_PREFIX ) ) );
			},
			$keys
		);

		return self::$held[ $order_id ];
	}

	/**
	 * Clears what is known about an order's claims after a write.
	 *
	 * @since 3.7.2
	 *
	 * @param int $order_id The order ID.
	 * @return void
	 */
	private static function forget( $order_id ) {
		unset( self::$held[ absint( $order_id ) ] );
		wp_cache_delete( absint( $order_id ), 'edd_order_meta' );
	}

	/**
	 * Gets the IDs of the discounts on an order.
	 *
	 * @since 3.7.2
	 *
	 * @param \EDD\Orders\Order $order The order.
	 * @return int[]
	 */
	private static function get_discount_ids( $order ): array {
		$discount_ids = array();
		foreach ( $order->get_discounts() as $adjustment ) {
			$discount_ids[] = absint( $adjustment->type_id );
		}

		return array_values( array_unique( array_filter( $discount_ids ) ) );
	}
}
