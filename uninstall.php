<?php
/**
 * Uninstall Easy Digital Downloads
 *
 * @package     EDD
 * @subpackage  Uninstall
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.3
 */

// Exit if accessed directly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

// Load EDD file
include_once( 'easy-digital-downloads.php' );

global $wpdb, $edd_options, $wp_roles;

/** Delete All the Custom Post Types */
$edd_post_types = array( 'download', 'edd_payment', 'edd_discount', 'edd_log' );
foreach ( $edd_post_types as $post_type ) {

	$items = get_posts( array( 'post_type' => $post_type, 'numberposts' => -1, 'fields' => 'ids' ) );

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
if ( isset( $edd_options['purchase_page'] ) )
	wp_delete_post( $edd_options['purchase_page'], true );
if ( isset( $edd_options['success_page'] ) )
	wp_delete_post( $edd_options['success_page'], true );
if ( isset( $edd_options['failure_page'] ) )
	wp_delete_post( $edd_options['failure_page'], true );

/** Delete all the Plugin Options */
delete_option( 'edd_settings_general' );
delete_option( 'edd_settings_gateways' );
delete_option( 'edd_settings_emails' );
delete_option( 'edd_settings_styles' );
delete_option( 'edd_settings_taxes' );
delete_option( 'edd_settings_misc' );

/** Delete Capabilities */
EDD()->roles->remove_caps();

/** Delete the Roles */
$edd_roles = array( 'shop_manager', 'shop_accountant', 'shop_worker', 'shop_vendor' );
foreach ( $edd_roles as $role ) {
	remove_role( $role );
}