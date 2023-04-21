<?php
/**
 * Downgrades
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.11
 */

/**
 * Checks if the current site has downgraded, and if so, performs any necessary actions.
 *
 * @since 2.11
 * @return bool Whether or not a downgrade was performed.
 */
function edd_do_downgrade() {
	$did_downgrade   = false;
	$edd_version     = preg_replace( '/[^0-9.].*/', '', get_option( 'edd_version' ) );
	$downgraded_from = get_option( 'edd_version_downgraded_from' );

	/**
	 * Check for downgrade from 3.0 to 2.11.
	 */
	if ( version_compare( EDD_VERSION, '3.0-beta1', '<' ) ) {
		if (
			version_compare( $edd_version, '3.0-beta1', '>=' ) ||
			( $downgraded_from && version_compare( $downgraded_from, '3.0-beta1', '>=' ) )
		) {
			/*
			 * This site probably just downgraded from EDD 3.0. Let's perform a downgrade.
			 */
			$did_downgrade = edd_maybe_downgrade_from_v3();
		}
	}

	if ( $did_downgrade ) {
		update_option( 'edd_version', preg_replace( '/[^0-9.].*/', '', EDD_VERSION ) );
		delete_option( 'edd_version_downgraded_from' );
	}

	return $did_downgrade;
}

/**
 * Performs a database downgrade from EDD 3.0 to 2.11 if one is needed.
 * The main operation here is changing the customer meta column from `edd_customer_id` (v3.0 version)
 * back to `customer_id` for v2.x.
 *
 * @since 2.11
 * @return bool Whether the downgrade was performed.
 */
function edd_maybe_downgrade_from_v3() {
	global $wpdb;
	$customer_meta_table = EDD()->customer_meta->table_name;

	// If there is no column called `edd_customer_id`, then we don't need to downgrade.
	$columns = $wpdb->query( "SHOW COLUMNS FROM {$customer_meta_table} LIKE 'edd_customer_id'");
	if ( empty( $columns ) ) {
		return false;
	}

	$wpdb->query( "ALTER TABLE {$customer_meta_table} CHANGE `edd_customer_id` `customer_id` bigint(20) unsigned NOT NULL default '0'" );
	$wpdb->query( "ALTER TABLE {$customer_meta_table} DROP INDEX edd_customer_id" );
	$wpdb->query( "ALTER TABLE {$customer_meta_table} ADD INDEX customer_id (customer_id)" );

	// These two calls re-add the table version numbers for us.
	EDD()->customer_meta->create_table();
	EDD()->customers->create_table();

	edd_debug_log( 'Completed downgrade from EDD 3.0.', true );

	return true;
}
