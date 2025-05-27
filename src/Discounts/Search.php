<?php
/**
 * Discounts Search.
 *
 * @package EDD\Discounts
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.3.9
 */

namespace EDD\Discounts;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;

/**
 * Discounts Search
 *
 * @since 3.3.9
 */
class Search implements SubscriberInterface {

	/**
	 * Get the subscribed events.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return array(
			'wp_ajax_edd_discount_search'        => 'search',
			'wp_ajax_nopriv_edd_discount_search' => 'search',
		);
	}

	/**
	 * Search for discounts.
	 *
	 * @since 3.3.9
	 * @return void
	 */
	public function search(): void {
		if ( ! edd_doing_ajax() ) {
			return;
		}

		echo wp_json_encode( $this->get_discounts() );

		edd_die();
	}

	/**
	 * Get the discounts.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	private function get_discounts(): array {
		if ( ! current_user_can( 'manage_shop_discounts' ) ) {
			return array();
		}

		$args = array(
			'search'  => isset( $_GET['s'] ) ? sanitize_text_field( urldecode( $_GET['s'] ) ) : '',
			'orderby' => 'code',
			'order'   => 'ASC',
		);

		$discounts = edd_get_discounts( $args );

		return array_map(
			function ( $discount ) {
				return array(
					'id'   => $discount->id,
					'name' => $discount->name,
				);
			},
			$discounts
		);
	}
}
