<?php
/**
 * Class for generating order numbers for refunds.
 *
 * @since 3.3.0
 * @package EDD\Orders\Refunds\Number
 */

namespace EDD\Orders\Refunds;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class to build a number for a refund.
 *
 * @since 3.3.0
 */
class Number {

	/**
	 * Generates a refund number.
	 *
	 * @since 3.3.0
	 * @param int $order_id The order ID a refund number is being generated for.
	 * @return string
	 */
	public static function generate( $order_id ) {
		return $order_id . self::suffix() . self::get_next_refund_number( $order_id );
	}

	/**
	 * Gets the refund number suffix.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private static function suffix() {
		/**
		 * Filter the suffix applied to order numbers for refunds.
		 *
		 * @since 3.0
		 *
		 * @param string Suffix.
		 */
		return apply_filters( 'edd_order_refund_suffix', '-R-' );
	}

	/**
	 * Gets the next refund number for a given order.
	 *
	 * @since 3.3.0
	 * @param int $order_id The order ID a refund number is being generated for.
	 * @return int
	 */
	private static function get_next_refund_number( $order_id ) {
		global $wpdb;

		$existing_refunds_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(id) FROM {$wpdb->edd_orders} WHERE parent = %d AND type = 'refund'",
				$order_id
			)
		);

		return intval( $existing_refunds_count ) + 1;
	}
}
