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

