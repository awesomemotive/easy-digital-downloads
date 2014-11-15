<?php
/**
 * Query Filters
 *
 * These functions register the front-end query vars
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2014, Pippin Williamson
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

	wp_die( __( 'You do not have permission to view this file.', 'edd' ), __( 'Error', 'edd' ), array( 'response' => 403 ) );
}
add_action( 'template_redirect', 'edd_block_attachments' );

/**
 * Filters on canonical redirects
 *
 * @since 2.2
 * @return string
 */
function edd_redirect_canonical( $redirect_url, $requested_url ) {

	if( ! is_front_page() ) {
		return $redirect_url;
	}

	if( 'page' === get_option( 'show_on_front' ) ) {

		/*
		 * If we are on the homepage and the homepage contains the [downloads] shortcode, we have
		 * to prevent the canonical redirect from taking place, otherwise /page/# gets sent back to just /
		 *
		 * See https://github.com/easydigitaldownloads/Easy-Digital-Downloads/issues/2743
		 * See https://github.com/easydigitaldownloads/Easy-Digital-Downloads/issues/2632
		 */

		if( ! function_exists( 'has_shortcode' ) ) {
			return $redirect_url;
		}

		$content = get_post_field( 'post_content', get_queried_object_id() );

		if( has_shortcode( $content, 'downloads' ) ) {
			$redirect_url = $requested_url;
		}

	}

	return $redirect_url;

}
//add_filter( 'redirect_canonical', 'edd_redirect_canonical', 0, 2 );
