<?php
/**
 * Registers front end query vars
 *
 * @access      public
 * @since       1.2.2
 * @return      array
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function edd_query_vars( $vars ) {
	$vars[] = 'edd_action';
	$vars[] = 'cart_item';
	return $vars;
}
add_filter( 'query_vars', 'edd_query_vars' );

/**
 * Blocks access to Download attachments
  *
 * @access      public
 * @since       1.2.2
 * @return      void
 */
function edd_block_attachments() {
	if ( !is_attachment() )
		return;

	$parent   = get_post_field( 'post_parent', get_the_ID() );
	$uri      = wp_get_attachment_url( get_the_ID() );
	$edd_file = strpos( $uri, '/edd/' );

	if ( ! $parent && false === $edd_file )
		return;

	if ( 'download' != get_post_type( $parent ) && false === $edd_file )
		return;

	wp_die( __( 'You do not have permission to view this file.', 'edd' ), __( 'Error', 'edd' ) );
}
add_action( 'template_redirect', 'edd_block_attachments' );