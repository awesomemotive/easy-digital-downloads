<?php

namespace EDD\CLI\Migration;

defined( 'ABSPATH' ) || exit;

/**
 * CLI command to query for customers with missing email addresses after a migration.
 *
 * @since 3.2.2
 */
class CustomerEmails {

	/**
	 * CLI command to query for customers with missing email addresses after a migration.
	 *
	 * @return void
	 */
	public function migrate_missing() {
		global $wpdb;
		$sql_base     =
			"SELECT *
			FROM {$wpdb->edd_customers}
			WHERE email != ''
			AND email NOT IN (
				SELECT email
				FROM {$wpdb->edd_customer_email_addresses}
			)";
		$sql          = $sql_base . ' LIMIT 1';
		$check_result = $wpdb->get_results( $sql );
		$check_total  = count( $check_result );
		$has_results  = ! empty( $check_total );

		if ( ! $has_results ) {
			\WP_CLI::line( __( 'No customers with missing emails were found.', 'easy-digital-downloads' ) );
			return;
		}

		$total    = count( $wpdb->get_results( $sql_base ) );
		$progress = new \cli\progress\Bar( 'Adding Missing Customer Emails', $total );
		$progress->tick();

		$count = 0;
		while ( $has_results ) {
			$progress->tick();

			// Query & count.
			$sql     = "{$sql_base} LIMIT 50";
			$results = $wpdb->get_results( $sql );

			if ( ! empty( $results ) ) {
				foreach ( $results as $result ) {
					$customer_has_primary = edd_count_customer_email_addresses(
						array(
							'customer_id' => $result->id,
							'type'        => 'primary',
						)
					);
					edd_add_customer_email_address(
						array(
							'customer_id'  => $result->id,
							'email'        => $result->email,
							'date_created' => $result->date_created,
							'type'         => $customer_has_primary ? 'secondary' : 'primary',
						)
					);

					// Tick the spinner...
					$progress->tick();
					++$count;
				}
			} else {
				$has_results = false;
			}
		}

		$progress->finish();
		\WP_CLI::line( __( 'Missing Customer Emails Added:', 'easy-digital-downloads' ) . $count );
	}
}
