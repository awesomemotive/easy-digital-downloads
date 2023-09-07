<?php

namespace EDD\CLI\Migration;

defined( 'ABSPATH' ) || exit;

class Discounts {

	/**
	 * CLI command to query for orders with missing discounts after a migration.
	 * This will add the `discount` value of the order object as a new order adjustment.
	 * It makes no calculations or currency conversions.
	 *
	 * This outputs a line for each discount added to the database.
	 *
	 * @return void
	 */
	public function migrate_missing() {
		global $wpdb;
		$sql_base = "
			SELECT *
			FROM {$wpdb->edd_orders} o
			WHERE o.type = 'sale'
			AND o.status IN( 'complete', 'edd_subscription', 'refunded', 'partially_refunded' )
			AND o.discount > 0
			AND NOT EXISTS(
			SELECT *
			FROM {$wpdb->edd_order_adjustments} oa
			WHERE oa.type = 'discount'
			AND oa.object_id = o.id
		)";

		// Query & count.
		$sql          = "{$sql_base} LIMIT 1";
		$check_result = $wpdb->get_results( $sql );
		$check_total  = count( $check_result );
		$has_results  = ! empty( $check_total );
		$number       = 50;
		$step         = 0;

		if ( ! $has_results ) {
			\WP_CLI::line( __( 'No orders with missing discounts were found.', 'easy-digital-downloads' ) );
			return;
		}

		$total    = count( $wpdb->get_results( $sql_base ) );
		$progress = new \cli\progress\Bar( 'Adding Missing Discounts', $total );
		$progress->tick();

		while ( $has_results ) {
			$sql     = $sql_base . " LIMIT {$number}";
			$results = $wpdb->get_results( $sql );
			if ( ! empty( $results ) ) {
				foreach ( $results as $result ) {
					$discount = edd_add_order_adjustment(
						array(
							'object_id'   => $result->id,
							'object_type' => 'order',
							'type'        => 'discount',
							'description' => __( 'Legacy Discount', 'easy-digital-downloads' ),
							'subtotal'    => $result->discount,
							'total'       => $result->discount,
							'rate'        => $result->rate,
						)
					);
				}

				$step++;
				$progress->tick();
			} else {
				$has_results = false;
				$progress->finish();
			}
		}

		\WP_CLI::line( __( 'Missing Discounts Added:', 'easy-digital-downloads' ) . $total );
	}
}
