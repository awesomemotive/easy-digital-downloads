<?php
/**
 * Query Filters
 *
 * These functions register the front-end query vars
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Blocks access to Download attachments
 *
 * Only blocks files that are listed as downloadable files for the product
 *
 * @since 1.2.2
 * @return void
 */
function edd_block_attachments() {
	if ( ! is_attachment() )
		return;

	$parent   = get_post_field( 'post_parent', get_the_ID() );
	$uri      = wp_get_attachment_url( get_the_ID() );
	$edd_file = strpos( $uri, '/edd/' );

	if ( ! $parent && false === $edd_file ) {
		return;
	}

	if ( 'download' != get_post_type( $parent ) && false === $edd_file ) {
		return;
	}

	$files      = edd_get_download_files( $parent );
	$restricted = wp_list_pluck( $files, 'file' );

	if ( ! in_array( $uri, $restricted ) ) {
		return;
	}

	wp_die( __( 'You do not have permission to view this file.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
}
add_action( 'template_redirect', 'edd_block_attachments' );


/**
 * Removes our tracking query arg so as not to interfere with the WP query, see https://core.trac.wordpress.org/ticket/25143
 *
 * @since 2.4.3
 */
function edd_unset_discount_query_arg( $query ) {

	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$discount = $query->get( 'discount' );

	if ( ! empty( $discount ) ) {

		// unset ref var from $wp_query
		$query->set( 'discount', null );

		global $wp;

		// unset ref var from $wp
		unset( $wp->query_vars[ 'discount' ] );

		// if in home (because $wp->query_vars is empty) and 'show_on_front' is page
		if ( empty( $wp->query_vars ) && get_option( 'show_on_front' ) === 'page' ) {

		 	// reset and re-parse query vars
			$wp->query_vars['page_id'] = get_option( 'page_on_front' );
			$query->parse_query( $wp->query_vars );

		}

	}

}
add_action( 'pre_get_posts', 'edd_unset_discount_query_arg', 999999 );

/**
 * Filters on canonical redirects
 *
 * @since 2.4.3
 * @return string
 */
function edd_prevent_canonical_redirect( $redirect_url, $requested_url ) {

	if( ! is_front_page() ) {
		return $redirect_url;
	}

	$discount = get_query_var( 'discount' );

	if( ! empty( $discount ) || false !== strpos( $requested_url, 'discount' ) ) {

		$redirect_url = $requested_url;

	}

	return $redirect_url;

}
add_action( 'redirect_canonical', 'edd_prevent_canonical_redirect', 0, 2 );

/**
 * Auto flush permalinks wth a soft flush when a 404 error is detected on an EDD page
 *
 * @since 2.4.3
 * @return string
 */
function edd_refresh_permalinks_on_bad_404() {

	global $wp;

	if( ! is_404() ) {
		return;
	}

	if( isset( $_GET['edd-flush'] ) ) {
		return;
	}

	if( false === get_transient( 'edd_refresh_404_permalinks' ) ) {

		$slug  = defined( 'EDD_SLUG' ) ? EDD_SLUG : 'downloads';

		$parts = explode( '/', $wp->request );

		if( $slug !== $parts[0] ) {
			return;
		}

		flush_rewrite_rules( false );

		set_transient( 'edd_refresh_404_permalinks', 1, HOUR_IN_SECONDS * 12 );

		wp_redirect( home_url( add_query_arg( array( 'edd-flush' => 1 ), $wp->request ) ) ); exit;

	}
}
add_action( 'template_redirect', 'edd_refresh_permalinks_on_bad_404' );