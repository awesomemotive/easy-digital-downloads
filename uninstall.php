<?php
/**
 * Uninstall Easy Digital Downloads
 *
 * @package     EDD
 * @subpackage  Uninstall
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.3
 */

// Exit if accessed directly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

// Load EDD file
include_once( 'easy-digital-downloads.php' );

global $wpdb, $edd_options, $wp_roles;

if( edd_get_option( 'uninstall_on_delete' ) ) {

	/** Delete All the Custom Post Types */
	$edd_post_types = array( 'download', 'edd_payment', 'edd_discount', 'edd_log' );
	foreach ( $edd_post_types as $post_type ) {

		$items = get_posts( array( 'post_type' => $post_type, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) );

		if ( $items ) {
			foreach ( $items as $item ) {
				wp_delete_post( $item, true);
			}
		}
	}

	/** Delete All the Taxonomies */
	$edd_taxonomies = array( 'download_tag', 'download_category', 'edd_log_type' );
	foreach ( $edd_taxonomies as $taxonomy ) {
		global $wp_taxonomies;
		$terms = get_terms( $taxonomy );
		if ( $terms ) {
			foreach ( $terms as $term ) {
				wp_delete_term( $term->term_id, $taxonomy );
			}
		}
		unset( $wp_taxonomies[ $taxonomy ] );
	}

	/** Delete the Plugin Pages */
	$edd_created_pages = array( 'purchase_page', 'success_page', 'failure_page', 'purchase_history_page' );
	foreach ( $edd_created_pages as $p ) {
		if ( isset( $edd_options[ $p ] ) ) {
			wp_delete_post( $edd_options[ $p ], true );
		}
	}

	/** Delete all the Plugin Options */
	delete_option( 'edd_settings' );

	/** Delete Capabilities */
	EDD()->roles->remove_caps();

	/** Delete the Roles */
	$edd_roles = array( 'shop_manager', 'shop_accountant', 'shop_worker', 'shop_vendor' );
	foreach ( $edd_roles as $role ) {
		remove_role( $role );
	}

	/** Cleanup Cron Events */
	wp_clear_scheduled_hook( 'edd_daily_scheduled_events' );
	wp_clear_scheduled_hook( 'edd_daily_cron' );
	wp_clear_scheduled_hook( 'edd_weekly_cron' );
}
