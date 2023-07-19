<?php
/**
 * Sanitizes settings.
 *
 * @since 3.1.4
 * @package EDD
 */

namespace EDD\Admin\Settings;

defined( 'ABSPATH' ) || exit;

use EDD\EventManagement\SubscriberInterface;

class Sanitize implements SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return array();
		}

		return array(
			'edd_settings_gateways-accounting_sanitize' => 'sanitize_sequential_order_numbers',
		);
	}

	/**
	 * Sanitizes the sequential order number starting number.
	 *
	 * @since 3.1.4
	 * @param array $input The input.
	 * @return array
	 */
	public function sanitize_sequential_order_numbers( $input ) {

		// If no starting number was entered return the input.
		if ( empty( $input['sequential_start'] ) ) {
			return $input;
		}

		// If the next order number isn't set, return the input.
		$next_order_number = get_option( 'edd_next_order_number', false );
		if ( ! $next_order_number ) {
			return $input;
		}

		// If the next order number is the same as the original, return the input.
		$start          = (int) $input['sequential_start'];
		$original_start = (int) edd_get_option( 'sequential_start', $start );
		if ( $start === $original_start ) {
			return $input;
		}

		$can_update = true;
		// If the next order number is less than the original, we need to make sure the most recent order number is not less than the new starting number.
		if ( $start < (int) $next_order_number ) {
			$most_recent_order_number = $this->get_most_recent_order_number();
			if ( ! empty( $most_recent_order_number ) && $start < (int) $most_recent_order_number ) {
				$can_update = false;
			}
		}

		// If order number cannot be updated, return the original value and set a flag.
		if ( ! $can_update ) {
			$input['sequential_start'] = edd_get_option( 'sequential_start' );
			edd_update_option( 'sequential_start_update_failed', true );
		} else {
			// Update the next order number.
			update_option( 'edd_next_order_number', $start );
		}

		return $input;
	}

	/**
	 * Gets the most recent order number.
	 *
	 * @since 3.1.4
	 * @return int|bool
	 */
	private function get_most_recent_order_number() {
		$orders = edd_get_orders(
			array(
				'number'  => 1,
				'orderby' => 'ID',
				'order'   => 'DESC',
				'fields'  => 'order_number',
			)
		);

		if ( empty( $orders ) ) {
			return false;
		}
		$last_order_number = reset( $orders );
		$order_number      = new \EDD\Orders\Number();

		return $order_number->unformat( $last_order_number );
	}
}
