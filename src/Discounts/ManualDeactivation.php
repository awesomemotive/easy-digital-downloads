<?php
/**
 * Manual Discount Deactivation
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
 * Records which discounts were set inactive on purpose rather than by reaching their cap.
 *
 * Reaching the cap deactivates a code without a status transition. A transition to inactive at the cap is
 * ambiguous, so only the admin deactivation actions mark a code that is already full.
 *
 * @since 3.7.2
 */
class ManualDeactivation implements SubscriberInterface {

	/**
	 * Adjustment meta key marking a discount set inactive on purpose.
	 *
	 * @since 3.7.2
	 * @var string
	 */
	const META_KEY = 'deactivated_manually';

	/**
	 * Gets the subscribed events.
	 *
	 * @since 3.7.2
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return array(
			'edd_transition_adjustment_status' => array( 'record', 10, 3 ),
		);
	}

	/**
	 * Marks a discount set inactive on purpose, and clears the mark when it leaves inactive.
	 *
	 * A discount created inactive, or set inactive at its cap, is not marked: imports, migrations and older code
	 * deactivate a code that reached its cap that way.
	 *
	 * @since 3.7.2
	 *
	 * @param string $old_status    The previous status.
	 * @param string $new_status    The new status.
	 * @param int    $adjustment_id The adjustment ID.
	 * @return void
	 */
	public function record( $old_status, $new_status, $adjustment_id ) {
		if ( 'new' === $old_status ) {
			return;
		}

		$adjustment = edd_get_adjustment( absint( $adjustment_id ) );
		if ( empty( $adjustment ) || 'discount' !== $adjustment->type ) {
			return;
		}

		$is_at_cap = ! empty( $adjustment->max_uses ) && $adjustment->use_count >= $adjustment->max_uses;
		if ( 'inactive' === $new_status && ! $is_at_cap ) {
			self::mark( $adjustment->id );
		} elseif ( 'inactive' === $old_status ) {
			edd_delete_adjustment_meta( $adjustment->id, self::META_KEY );
		}
	}

	/**
	 * Marks a discount as set inactive on purpose.
	 *
	 * @since 3.7.2
	 *
	 * @param int $discount_id The discount ID.
	 * @return void
	 */
	public static function mark( $discount_id ) {
		edd_add_adjustment_meta( absint( $discount_id ), self::META_KEY, time(), true );
	}

	/**
	 * Whether a discount was set inactive on purpose.
	 *
	 * @since 3.7.2
	 *
	 * @param int $discount_id The discount ID.
	 * @return bool
	 */
	public static function is_manual( $discount_id ): bool {
		return ! empty( edd_get_adjustment_meta( absint( $discount_id ), self::META_KEY, true ) );
	}
}
