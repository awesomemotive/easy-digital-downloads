<?php
/**
 * Theme Compatibility
 *
 * Functions for compatibility with specific themes.
 *
 * @package     Easy Digital Downloads
 * @subpackage  Theme Compatibility
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

function edd_responsive_download_post_class( $classes, $class, $post_id ) {

	if( ! is_singular( 'download' ) )
		return $classes;

	if( ( $key = array_search( 'download', $classes ) ) )
		unset( $classes[ $key ] );

	return $classes;
}
add_filter( 'post_class', 'edd_responsive_download_post_class', 999, 3 );
