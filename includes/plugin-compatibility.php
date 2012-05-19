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


/**
 * Remove Restrict Meta Box
 *
 * Removes the "Restrict This Content" meta box from Restrict Content Pro.
 *
 * @access      private
 * @since       1.0 
 * @return      array
*/

function edd_remove_restrict_meta_box($post_types) {
	$post_types[] = 'download';
	
	return $post_types;
}
add_filter('rcp_metabox_excluded_post_types', 'edd_remove_restrict_meta_box', 999);