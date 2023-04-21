<?php
/**
 * Gets the store stats to send to our telemetry server.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.1.1
 */
namespace EDD\Telemetry;

use EDD\Admin\Pass_Manager;

class Stats {

	/**
	 * The number of products on the site.
	 *
	 * @since 3.1.1
	 * @var int
	 */
	private $product_count;

	public function get() {
		return array(
			'activated'            => $this->convert_timestamp( edd_get_activation_date() ),
			'pro_activated'        => $this->convert_timestamp( get_option( 'edd_pro_activation_date' ) ),
			'first_order'          => $this->get_first_order_date(),
			'onboarding_started'   => get_option( 'edd_onboarding_started' ),
			'onboarding_completed' => get_option( 'edd_onboarding_completed' ),
			'products'             => $this->get_product_count(),
			'pass_id'              => $this->get_pass_id(),
		);
	}

	/**
	 * Gets the date of the first completed order.
	 *
	 * @since 3.1.1
	 * @return string
	 */
	private function get_first_order_date() {
		$orders = edd_get_orders(
			array(
				'mode'       => 'live',
				'status__in' => edd_get_complete_order_statuses(),
				'number'     => 1,
				'fields'     => 'date_completed',
				'orderby'    => 'id',
				'order'      => 'ASC',
			),
		);

		return ! empty( $orders ) ? reset( $orders ) : '';
	}

	/**
	 * Converts a timestamp value to a date string for consistent dates.
	 *
	 * @since 3.1.1
	 * @param string $timestamp
	 * @return string
	 */
	private function convert_timestamp( $timestamp = '' ) {
		return $timestamp ? gmdate( 'Y-m-d H:i:s', $timestamp ) : '';
	}

	/**
	 * Gets the site pass ID.
	 *
	 * @since 3.1.1
	 * @return int|string
	 */
	private function get_pass_id() {
		$pass_manager = new Pass_Manager();

		return $pass_manager->highest_pass_id;
	}

	/**
	 * Gets the number of published products on the website.
	 *
	 * @since 3.1.1
	 * @return int
	 */
	private function get_product_count() {
		if ( $this->product_count ) {
			return $this->product_count;
		}
		$query               = new \WP_Query(
			array(
				'post_type' => 'download',
				'status'    => 'publish',
				'nopaging'  => true,
			)
		);
		$this->product_count = $query->found_posts;

		return $this->product_count;
	}

	/**
	 * Gets the average total earnings per product.
	 *
	 * @since 3.1.1
	 * @return float
	 */
	private function get_average_per_product() {
		global $wpdb;

		$results = $wpdb->get_results(
			"SELECT SUM(total) as earnings
			 FROM {$wpdb->edd_orders}
			 WHERE type = 'sale'
			 AND status IN ('" . implode( "', '", edd_get_gross_order_statuses() ) . "')
			 LIMIT 0, 99999;"
		);
		if ( empty( $results ) ) {
			return 0;
		}
		$results  = reset( $results );
		$products = $this->get_product_count();

		return $results->earnings / $products;
	}
}
