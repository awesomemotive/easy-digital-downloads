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

	wp_die( __( 'You do not have permission to view this file.', 'edd' ), __( 'Error', 'edd' ), array( 'response' => 403 ) );
}
add_action( 'template_redirect', 'edd_block_attachments' );