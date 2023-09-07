<?php
/**
 * Easy Digital Downloads WP-CLI Tools for Stripe
 *
 * This class provides an integration point with the WP-CLI plugin allowing
 * access to EDD from the command line.
 *
 * @package     EDD
 * @subpackage  Classes/CLI
 * @copyright   Copyright (c) 2015, Chris Klosowski
 * @license     http://opensource.org/license/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

WP_CLI::add_command( 'edd-stripe', 'EDD_Stripe_CLI' );

/**
 * Work with EDD through WP-CLI
 *
 * EDD_CLI Class
 *
 * Adds CLI support to EDD through WP-CL
 *
 * @since   1.0
 */
class EDD_Stripe_CLI extends EDD_CLI {
	/**
	 * Migrate the Stripe customer IDs from the usermeta table to the edd_customermeta table.
	 *
	 * ## OPTIONS
	 *
	 * --force=<boolean>: If the routine should be run even if the upgrade routine has been run already
	 *
	 * ## EXAMPLES
	 *
	 * wp edd-stripe migrate_customer_ids
	 * wp edd-stripe migrate_customer_ids --force
	 */
	public function migrate_customer_ids( $args, $assoc_args ) {
		global $wpdb;
		$force  = isset( $assoc_args['force'] ) ? true : false;

		$upgrade_completed = edd_has_upgrade_completed( 'stripe_customer_id_migration' );

		if ( ! $force && $upgrade_completed ) {
			WP_CLI::error( __( 'The Stripe customer ID migration has already been run. To do this anyway, use the --force argument.', 'easy-digital-downloads' ) );
		}

		$sql     = "SELECT user_id, meta_key, meta_value FROM $wpdb->usermeta WHERE meta_key IN ( '_edd_stripe_customer_id', '_edd_stripe_customer_id_test' )";
		$results = $wpdb->get_results( $sql );
		$total   = count( $results );

		if ( ! empty( $total ) ) {

			$progress = new \cli\progress\Bar( 'Processing user meta', $total );

			foreach ( $results as $result ) {
				$user_data = get_userdata( $result->user_id );
				$customer  = new EDD_Customer( $user_data->user_email );

				if ( ! $customer->id > 0 ) {
					$customer = new EDD_Customer( $result->user_id, true );

					if ( ! $customer->id > 0 ) {
						continue;
					}
				}

				$stripe_customer_id = $result->meta_value;

				// We should try and use a recurring ID if one exists for this user
				if ( class_exists( 'EDD_Recurring_Subscriber' ) ) {
					$subscriber         = new EDD_Recurring_Subscriber( $customer->id );
					$stripe_customer_id = $subscriber->get_recurring_customer_id( 'stripe' );
				}

				$customer->update_meta( $result->meta_key, $stripe_customer_id );

				$progress->tick();
			}

			$progress->finish();
			WP_CLI::line( __( 'Migration complete.', 'easy-digital-downloads' ) );
		} else {
			WP_CLI::line( __( 'No user records were found that needed to be migrated.', 'easy-digital-downloads' ) );
		}

		update_option( 'edds_stripe_version', preg_replace( '/[^0-9.].*/', '', EDD_STRIPE_VERSION ) );
		edd_set_upgrade_complete( 'stripe_customer_id_migration' );

	}
}
