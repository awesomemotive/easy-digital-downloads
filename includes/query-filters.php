<?php
/**
 * Registers front end query vars
  *
 * @access      public
 * @since       1.2.2
 * @return      array
*/

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

	if( !is_attachment() )
		return;

	$parent = get_post_field( 'post_parent', get_the_ID() );

	if( ! $parent )
		return;

	if( 'download' != get_post_type( $parent ) )
		return;

	wp_die( __( 'You do not have permission to view this file.', 'edd' ), __( 'Error', 'edd' ) );

}
add_action( 'template_redirect', 'edd_block_attachments' );