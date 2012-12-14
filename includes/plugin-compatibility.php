<?php
/**
 * Plugin Compatibility
 *
 * Functions for compatibility with other plugins.
 *
 * @package     Easy Digital Downloads
 * @subpackage  Gateway Functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Remove Restrict Meta Box
 *
 * Removes the "Restrict This Content" meta box from Restrict Content Pro.
 *
 * @access      private
 * @since       1.0
 * @return      array
*/

function edd_remove_restrict_meta_box( $post_types ) {
	$post_types[] = 'download';

	return $post_types;
}
add_filter( 'rcp_metabox_excluded_post_types', 'edd_remove_restrict_meta_box', 999 );


/**
 * Disables admin sorting of Post Types Order
 *
 * When sorting downloads by price, earnings, sales, date, or name,
 * we need to remove the posts_orderby that Post Types Order imposes
 *
 * @access      private
 * @since       1.2.2
 * @return      void
*/

function edd_remove_post_types_order() {
	remove_filter( 'posts_orderby', 'CPTOrderPosts' );
}
add_action( 'load-edit.php', 'edd_remove_post_types_order' );


/**
 * Disables opengraph tags on the checkout page
 *
 * There is a bizarre conflict that makes the checkout errors not get displayed
 * when the Jetpack opengraph tags are displayed
 *
 * @access      private
 * @since       1.3.3.1
 * @return      bool
*/

function edd_disable_jetpack_og_on_checkout() {
	if( edd_is_checkout() )
		return false;
	return true;
}
add_filter( 'jetpack_enable_open_graph', 'edd_disable_jetpack_og_on_checkout', 999 );