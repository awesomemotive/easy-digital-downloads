<?php
/**
 * Handles the Cart cron events.
 *
 * @package EDD
 * @subpackage Cron/Components
 * @since 3.3.0
 */

namespace EDD\Cron\Components;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Cart Class for Cron Events.
 *
 * @since 3.3.0
 */
class Cart extends Component {

	/**
	 * The component ID.
	 *
	 * @var string
	 */
	protected static $id = 'cart';

	/**
	 * Register the event to run.
	 *
	 * @since 3.3.0
	 */
	public static function get_subscribed_events(): array {
		return array(
			'edd_weekly_scheduled_events' => 'delete_saved_carts',
		);
	}

	/**
	 * Delete saved carts from the database.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function delete_saved_carts() {
		global $wpdb;

		$carts = $wpdb->get_results(
			"
			SELECT user_id, meta_key, FROM_UNIXTIME(meta_value, '%Y-%m-%d') AS date
			FROM {$wpdb->usermeta}
			WHERE meta_key = 'edd_cart_token'
			",
			ARRAY_A
		);

		if ( empty( $carts ) ) {
			return;
		}

		foreach ( $carts as $cart ) {
			$user_id    = $cart['user_id'];
			$meta_value = $cart['date'];

			if ( strtotime( $meta_value ) > strtotime( '-1 week' ) ) {
				continue;
			}

			$wpdb->delete(
				$wpdb->usermeta,
				array(
					'user_id'  => $user_id,
					'meta_key' => 'edd_cart_token',
				)
			);

			$wpdb->delete(
				$wpdb->usermeta,
				array(
					'user_id'  => $user_id,
					'meta_key' => 'edd_saved_cart',
				)
			);
		}
	}
}
