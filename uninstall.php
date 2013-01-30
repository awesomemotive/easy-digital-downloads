<?php

/**
 * Uninstall Easy Digital Downloads
 *
 * @package     Easy Digital Downloads
 * @subpackage  Uninstall
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.3
*/

// Exit if accessed directly
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

global $wpdb, $edd_options;


/* Delete all post type data */
$edd_post_types = array( 'download', 'edd_payment', 'edd_discount', 'edd_log' );
foreach( $edd_post_types as $post_type ) {

	$items = get_posts( array( 'post_type' => $post_type, 'numberposts' => -1, 'fields' => 'ids' ) );

	if ( $items ) {
		foreach ( $items as $item ) {
			wp_delete_post( $item, true);
		}
	}
}

/* Delete all taxonomy data */
$edd_taxonomies = array( 'download_tag', 'download_category', 'edd_log_type' );
foreach( $edd_taxonomies as $taxonomy ) {
	global $wp_taxonomies;
	$terms = get_terms( $taxonomy );
	foreach ( $terms as $term ) {
		wp_delete_term( $term->term_id, $taxonomy );
	}
	unset( $wp_taxonomies[$taxonomy] );
}

/* Delete plugin pages */
if( isset( $edd_options['purchase_page'] ) )
	wp_delete_post( $edd_options['purchase_page'], true );
if( isset( $edd_options['success_page'] ) )
	wp_delete_post( $edd_options['success_page'], true );
if( isset( $edd_options['failure_page'] ) )
	wp_delete_post( $edd_options['failure_page'], true );


/* Delete all plugin options */
delete_option( 'edd_settings_general' );
delete_option( 'edd_settings_gateways' );
delete_option( 'edd_settings_emails' );
delete_option( 'edd_settings_styles' );
delete_option( 'edd_settings_taxes' );
delete_option( 'edd_settings_misc' );