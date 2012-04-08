<?php

/************************************************
* Functions for compatibility with other plugins
************************************************/

// removes the "Restrict This Content" meta box from Restrict Content Pro
function edd_remove_restrict_meta_box($post_types) {
	$post_types[] = 'download';
	
	return $post_types;
}
add_filter('rcp_metabox_excluded_post_types', 'edd_remove_restrict_meta_box', 999);

// seo meta boxes serve no purpose on the downloads post type
function edd_remove_seo_metaboxes() {
	
	global $typenow;
	
	if(!isset($typenow) || $typenow != 'download')
		return;
	
	/** WordPress SEO by Yoast */
	if ( class_exists( 'WPSEO_Metabox' ) )
		remove_meta_box( 'wpseo_meta', 'edd', 'normal' );
		
	/** All in One SEO Pack */
	if ( class_exists( 'All_in_One_SEO_Pack' ) )
		remove_meta_box( 'aiosp', 'edd', 'advanced' );
		
	/** Platinum SEO */
	if ( class_exists( 'Platinum_SEO_Pack' ) )
		remove_meta_box( 'postpsp', 'edd', 'normal' );	
}
add_action('add_meta_boxes', 'edd_remove_seo_metaboxes', 999);