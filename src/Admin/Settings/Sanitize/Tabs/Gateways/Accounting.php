<?php
/**
 * Sanitizes the accounting section.
 *
 * @since 3.3.3
 * @package EDD\Admin\Settings\Sanitize\Tabs\Gateways
 */

namespace EDD\Admin\Settings\Sanitize\Tabs\Gateways;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Admin\Settings\Sanitize\Tabs\Section;
use EDD\Settings\Setting;

/**
 * Sanitizes the site terms section.
 *
 * @since 3.3.3
 */
class Accounting extends Section {
	/**
	 * Handle the changes to the accounting section.
	 *
	 * @since 3.3.3
	 * @param array $input The array of settings for the settings tab.
	 * @return array
	 */
	protected static function additional_processing( $input ) {
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
		$original_start = (int) Setting::get( 'sequential_start', $start );
		if ( $start === $original_start ) {
			return $input;
		}

		$can_update = true;
		// If the next order number is less than the original, we need to make sure the most recent order number is not less than the new starting number.
		if ( $start < (int) $next_order_number ) {
			$most_recent_order_number = self::get_most_recent_order_number();
			if ( ! empty( $most_recent_order_number ) && $start < (int) $most_recent_order_number ) {
				$can_update = false;
			}
		}

		// If order number cannot be updated, return the original value and set a flag.
		if ( ! $can_update ) {
			$input['sequential_start'] = Setting::get( 'sequential_start' );
			Setting::update( 'sequential_start_update_failed', true );
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
	 * @since 3.3.3 Moved to a section specific sanitization class.
	 * @return int|bool
	 */
	private static function get_most_recent_order_number() {
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
