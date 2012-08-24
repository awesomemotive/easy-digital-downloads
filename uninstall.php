<?php
// if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();

// Delete edd options
delete_option('edd_settings_general');
delete_option('edd_settings_gateways');
delete_option('edd_settings_emails');
delete_option('edd_settings_styles');
delete_option('edd_settings_misc');
delete_option('edd_discounts'); 

// Remove custom post type data
foreach($edd_post_types as $post_type) {
	$edd_post_type = get_posts( array( 'post_type' => $post_type, 'numberposts' => -1 ) );
	
	if ($edd_post_type) {
		foreach ( $edd_post_type as $post ) {
			wp_delete_post($post->ID, true);
		}
	}  
}

$edd_taxonomies = array('download_tag', 'download_category');

// Remove taxonomies and terms
foreach($edd_taxonomies as $taxonomy) {
	global $wp_taxonomies;
	$terms = get_terms( $taxonomy ); 
	foreach ( $terms as $term ) {
		wp_delete_term( $term->term_id, $taxonomy );
	}
	unset( $wp_taxonomies[$taxonomy] );	
}

// Flush rewrite rules
$GLOBALS['wp_rewrite']->flush_rules();
?>