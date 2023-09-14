<?php
/**
 * General upgrade helper functions.
 *
 * @package    easy-digital-downloads
 * @subpackage Upgrades
 * @copyright  Copyright (c) 2023, Easy Digital Downloads
 * @license    GPL2+
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get the current database version
 *
 * @since 3.0
 *
 * @return string
 */
function edd_get_db_version() {
	$db_version = get_option( 'edd_version' );
	$retval     = ! empty( $db_version )
		? edd_format_db_version( $db_version )
		: false;

	return $retval;
}

/**
 * Update the EDD version in the options table
 *
 * @since 3.0
 */
function edd_update_db_version() {
	if ( defined( 'EDD_VERSION' ) ) {
		update_option( 'edd_version', edd_format_db_version( EDD_VERSION ) );
	}
}

/**
 * Format the EDD version (going into or coming from the database.)
 *
 * @since 3.0
 *
 * @param string $version
 * @return string
 */
function edd_format_db_version( $version = '' ) {
	return preg_replace( '/[^0-9.].*/', '', $version );
}

/**
 * Check if the upgrade routine has been run for a specific action
 *
 * @since  2.3
 * @param  string $upgrade_action The upgrade action to check completion for
 * @return bool                   If the action has been added to the copmleted actions array
 */
function edd_has_upgrade_completed( $upgrade_action = '' ) {

	// Bail if no upgrade action to check
	if ( empty( $upgrade_action ) ) {
		return false;
	}

	// Get completed upgrades
	$completed_upgrades = edd_get_completed_upgrades();

	// Return true if in array, false if not
	return in_array( $upgrade_action, $completed_upgrades, true );
}

/**
 * Adds an upgrade action to the completed upgrades array
 *
 * @since  2.3
 * @param  string $upgrade_action The action to add to the copmleted upgrades array
 * @return bool                   If the function was successfully added
 */
function edd_set_upgrade_complete( $upgrade_action = '' ) {

	if ( empty( $upgrade_action ) ) {
		return false;
	}

	$completed_upgrades   = edd_get_completed_upgrades();
	$completed_upgrades[] = $upgrade_action;

	// Remove any blanks, and only show uniques
	$completed_upgrades = array_unique( array_values( $completed_upgrades ) );

	return update_option( 'edd_completed_upgrades', $completed_upgrades );
}

/**
 * Gets the array of completed upgrade actions
 *
 * @since  2.3
 * @return array The array of completed upgrades
 */
function edd_get_completed_upgrades() {

	// Get the completed upgrades for this site
	$completed_upgrades = get_option( 'edd_completed_upgrades', array() );

	// Return array of completed upgrades
	return (array) $completed_upgrades;
}

/**
 * Defines all EDD core upgrades.
 * When a new upgrade routine is added, it should be registered here.
 *
 * @since 3.1.4
 * @return array
 */
function edd_get_all_upgrades() {
	$upgrade_routines = array(
		'upgrade_payment_taxes',
		'upgrade_customer_payments_association',
		'upgrade_user_api_keys',
		'remove_refunded_sale_logs',
		'update_file_download_log_data',
		'migrate_order_actions_date', // Added in 3.2, migrates the order actions run date to the order table.
	);
	$edd_30_upgrades  = edd_get_v30_upgrades();

	return array_merge( $upgrade_routines, array_keys( $edd_30_upgrades ) );
}

/**
 * Returns an array of upgrades for 3.0
 *
 * Key is the name of the upgrade, which can be used in `edd_has_upgrade_completed()` completed functions.
 * The value is the name of the associated batch processor class for that upgrade.
 *
 * @since 3.0
 * @return array
 */
function edd_get_v30_upgrades() {
	return array(
		'migrate_tax_rates'                => array(
			'name'  => __( 'Tax Rates', 'easy-digital-downloads' ),
			'class' => 'EDD\\Admin\\Upgrades\\v3\\Tax_Rates',
		),
		'migrate_discounts'                => array(
			'name'  => __( 'Discounts', 'easy-digital-downloads' ),
			'class' => 'EDD\\Admin\\Upgrades\\v3\\Discounts',
		),
		'migrate_orders'                   => array(
			'name'  => __( 'Orders', 'easy-digital-downloads' ),
			'class' => 'EDD\\Admin\\Upgrades\\v3\\Orders',
		),
		'migrate_customer_addresses'       => array(
			'name'  => __( 'Customer Addresses', 'easy-digital-downloads' ),
			'class' => 'EDD\\Admin\\Upgrades\\v3\\Customer_Addresses',
		),
		'migrate_customer_email_addresses' => array(
			'name'  => __( 'Customer Email Addresses', 'easy-digital-downloads' ),
			'class' => 'EDD\\Admin\\Upgrades\\v3\\Customer_Email_Addresses',
		),
		'migrate_customer_notes'           => array(
			'name'  => __( 'Customer Notes', 'easy-digital-downloads' ),
			'class' => 'EDD\\Admin\\Upgrades\\v3\\Customer_Notes',
		),
		'migrate_logs'                     => array(
			'name'  => __( 'Logs', 'easy-digital-downloads' ),
			'class' => 'EDD\\Admin\\Upgrades\\v3\\Logs',
		),
		'migrate_order_notes'              => array(
			'name'  => __( 'Order Notes', 'easy-digital-downloads' ),
			'class' => 'EDD\\Admin\\Upgrades\\v3\\Order_Notes',
		),
		'v30_legacy_data_removed'          => array(
			'name'  => __( 'Remove Legacy Data', 'easy-digital-downloads' ),
			'class' => 'EDD\\Admin\\Upgrades\\v3\\Remove_Legacy_Data',
		),
	);
}

/**
 * Perform automatic database upgrades when necessary
 *
 * @since 2.6
 * @return void
 */
function edd_do_automatic_upgrades() {

	$edd_version = edd_get_db_version();
	if ( version_compare( $edd_version, EDD_VERSION, '>=' ) ) {
		return;
	}

	$set_stripe_transients = true;
	if ( ! empty( $edd_version ) ) {
		update_option( 'edd_version_upgraded_from', $edd_version, false );

		// Existing stores should set the upgraded version and the onboarding wizard as complete.
		if ( ! get_option( 'edd_onboarding_completed', false ) && ! get_option( 'edd_onboarding_started', false ) ) {
			update_option( 'edd_onboarding_completed', true, false );
		}

		// Stores upgrading from 3.2.0 or greater should not set the Stripe transients.
		if ( version_compare( $edd_version, '3.2.0', '>=' ) ) {
			$set_stripe_transients = false;
		}
	}

	/**
	 * If PayPal is connected, schedule a cron event to sync the webhooks in the background.
	 *
	 * @since 3.2.0
	 */
	if ( EDD\Gateways\PayPal\has_rest_api_connection() && EDD\Gateways\PayPal\Webhooks\get_webhook_id() ) {
		// Schedule a one time cron event to sync the webhooks.
		if ( ! wp_next_scheduled( 'edd_paypal_commerce_sync_webhooks' ) ) {
			wp_schedule_single_event( time() + ( 5 * MINUTE_IN_SECONDS ), 'edd_paypal_commerce_sync_webhooks' );
		}
	}

	/**
	 * If Stripe is active and EDD Pro or EDD Stripe is active, set a transient to check the license.
	 *
	 * @since 3.2.0
	 */
	if ( $set_stripe_transients && edd_is_gateway_active( 'stripe' ) && ( edd_is_pro() || edds_is_pro() ) ) {
		set_transient( 'edd_stripe_check_license', true, 30 );
		set_transient( 'edd_stripe_new_install', time(), HOUR_IN_SECONDS * 72 );
	}

	edd_update_db_version();
}
