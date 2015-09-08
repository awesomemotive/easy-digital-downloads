<?php
/**
 * Uninstall Easy Digital Downloads
 *
 * @package     EDD
 * @subpackage  Uninstall
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.3
 */

// Exit if accessed directly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

// Load EDD file
include_once( 'easy-digital-downloads.php' );

global $wpdb, $wp_roles;

if( edd_get_option( 'uninstall_on_delete' ) ) {

	/** Delete All the Custom Post Types */
	$edd_taxonomies = array( 'download_category', 'download_tag', 'edd_log_type', );
	$edd_post_types = array( 'download', 'edd_payment', 'edd_discount', 'edd_log' );
	foreach ( $edd_post_types as $post_type ) {

		$edd_taxonomies = array_merge( $edd_taxonomies, get_object_taxonomies( $post_type ) );
		$items = get_posts( array( 'post_type' => $post_type, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) );

		if ( $items ) {
			foreach ( $items as $item ) {
				wp_delete_post( $item, true);
			}
		}
	}

	/** Delete All the Terms & Taxonomies */
	foreach ( array_unique( array_filter( $edd_taxonomies ) ) as $taxonomy ) {

		$terms = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s') ORDER BY t.name ASC", $taxonomy ) );

		// Delete Terms
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
				$wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
			}
		}

		// Delete Taxonomies
		$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $taxonomy ), array( '%s' ) );
	}

	/** Delete the Plugin Pages */
	$edd_created_pages = array( 'purchase_page', 'success_page', 'failure_page', 'purchase_history_page' );
	foreach ( $edd_created_pages as $p ) {
		$page = edd_get_option( $p, false );
		if ( $page ) {
			wp_delete_post( $page, true );
		}
	}

	/** Delete all the Plugin Options */
	delete_option( 'edd_settings' );
	delete_option( 'edd_version' );

	/** Delete Capabilities */
	EDD()->roles->remove_caps();

	/** Delete the Roles */
	$edd_roles = array( 'shop_manager', 'shop_accountant', 'shop_worker', 'shop_vendor' );
	foreach ( $edd_roles as $role ) {
		remove_role( $role );
	}

	// Remove all database tables
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "edd_customers" );

	/** Cleanup Cron Events */
	wp_clear_scheduled_hook( 'edd_daily_scheduled_events' );
	wp_clear_scheduled_hook( 'edd_daily_cron' );
	wp_clear_scheduled_hook( 'edd_weekly_cron' );
}
