<?php
/**
 * Post Type Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Post Type Functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/


/**
 * Setup Download Post Type
 *
 * Registers the Downloads CPT.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_setup_edd_post_types() {

	$archives = true;
	if(defined('EDD_DISABLE_ARCHIVE') && EDD_DISABLE_ARCHIVE == true) {
		$archives = false;
	}
	
	$slug = 'downloads';
	if(defined('EDD_SLUG')) {
		$slug = EDD_SLUG;
	}
	
	$rewrite = array('slug' => $slug, 'with_front' => false);
	if(defined('EDD_DISABLE_REWRITE') && EDD_DISABLE_REWRITE == true) {
		$rewrite = false;
	}
	
	$download_labels =  apply_filters('edd_download_labels', array(
		'name' => '%2$s',
		'singular_name' => '%1$s',
		'add_new' => __('Add New', 'edd'),
		'add_new_item' => __('Add New %1$s', 'edd'),
		'edit_item' => __('Edit %1$s', 'edd'),
		'new_item' => __('New %1$s', 'edd'),
		'all_items' => __('All %2$s', 'edd'),
		'view_item' => __('View %1$s', 'edd'),
		'search_items' => __('Search %2$s', 'edd'),
		'not_found' =>  __('No %2$s found', 'edd'),
		'not_found_in_trash' => __('No %2$s found in Trash', 'edd'), 
		'parent_item_colon' => '',
		'menu_name' => __('%2$s', 'edd')
	) );
	
	foreach ($download_labels as $key => $value) {
	   $download_labels[$key] = sprintf( $value, edd_get_label_singular(), edd_get_label_plural() );
	}
	
	$download_args = array(
		'labels' => $download_labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true, 
		'show_in_menu' => true, 
		'query_var' => true,
		'rewrite' => $rewrite,
		'capability_type' => 'post',
		'has_archive' => $archives, 
		'hierarchical' => false,
		'supports' => apply_filters('edd_download_supports', array( 'title', 'editor', 'thumbnail' ) ),
	); 
	register_post_type('download', apply_filters( 'edd_download_post_type_args', $download_args ) );
	
	
	/* payment post type */	
	
	$payment_labels = array(
		'name' => _x('Payments', 'post type general name', 'edd'),
		'singular_name' => _x('Payment', 'post type singular name', 'edd'),
		'add_new' => __('Add New', 'edd'),
		'add_new_item' => __('Add New Payment', 'edd'),
		'edit_item' => __('Edit Payment', 'edd'),
		'new_item' => __('New Payment', 'edd'),
		'all_items' => __('All Payments', 'edd'),
		'view_item' => __('View Payment', 'edd'),
		'search_items' => __('Search Payments', 'edd'),
		'not_found' =>  __('No Payments found', 'edd'),
		'not_found_in_trash' => __('No Payments found in Trash', 'edd'), 
		'parent_item_colon' => '',
		'menu_name' => __('Payment History', 'edd')
	);
	
	$payment_args = array(
		'labels' => apply_filters('edd_payment_labels', $payment_labels),
		'public' => false,
		'publicly_queryable' => false,
		'show_ui' => false, 
		'show_in_menu' => false, 
		'show_in_nav_menu' => false, 
		'query_var' => false,
		'rewrite' => false,
		'capability_type' => 'post',
		'has_archive' => false, 
		'hierarchical' => false,
		'supports' => array( 'title' ),
		'menu_icon' => EDD_PLUGIN_URL . 'includes/images/media-button.png'
	); 
	register_post_type('edd_payment', $payment_args);
	
}
add_action('init', 'edd_setup_edd_post_types', 100);


/**
 * Get Default Label
 *
 * @access      public
 * @since       1.0.8.3
 * @return      array
*/

function edd_get_default_labels() {
	$defaults = array(
	   'singular' => __('Download','edd'),
	   'plural' => __('Downloads','edd')
	);
	return apply_filters( 'edd_default_downloads_name', $defaults );
}


/**
 * Get Label Singular
 *
 * @access      public
 * @since       1.0.8.3
 * @return      string
*/

function edd_get_label_singular($lowercase = false) {
	$defaults = edd_get_default_labels();
	return ($lowercase) ? strtolower($defaults['singular']) : $defaults['singular'];
}


/**
 * Get Label Plural
 *
 * @access      public
 * @since       1.0.8.3
 * @return      string
*/

function edd_get_label_plural($lowercase = false) {
	$defaults = edd_get_default_labels();
	return ($lowercase) ? strtolower($defaults['plural']) : $defaults['plural'];
}


/**
 * Setup Download Taxonomies
 *
 * Registers the custom taxonomies.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_setup_download_taxonomies() {

	$category_labels = array(
		'name' => _x( 'Categories', 'taxonomy general name', 'edd' ),
		'singular_name' => _x( 'Category', 'taxonomy singular name', 'edd' ),
		'search_items' =>  __( 'Search Categories', 'edd'  ),
		'all_items' => __( 'All Categories', 'edd'  ),
		'parent_item' => __( 'Parent Category', 'edd'  ),
		'parent_item_colon' => __( 'Parent Category:', 'edd'  ),
		'edit_item' => __( 'Edit Category', 'edd'  ), 
		'update_item' => __( 'Update Category', 'edd'  ),
		'add_new_item' => __( 'Add New Category', 'edd'  ),
		'new_item_name' => __( 'New Category Name', 'edd'  ),
		'menu_name' => __( 'Categories', 'edd'  ),
	); 	

	register_taxonomy('download_category', array('download'), array(
		'hierarchical' => true,
		'labels' => apply_filters('edd_download_category_labels', $category_labels),
		'show_ui' => true,
		'query_var' => 'download_category',
		'rewrite' => array('slug' => 'downloads/category')
	));
	
	$tag_labels = array(
		'name' => _x( 'Tags', 'taxonomy general name', 'edd' ),
		'singular_name' => _x( 'Tag', 'taxonomy singular name', 'edd' ),
		'search_items' =>  __( 'Search Tags', 'edd'  ),
		'all_items' => __( 'All Tags', 'edd'  ),
		'parent_item' => __( 'Parent Tag', 'edd'  ),
		'parent_item_colon' => __( 'Parent Tag:', 'edd'  ),
		'edit_item' => __( 'Edit Tag', 'edd'  ), 
		'update_item' => __( 'Update Tag', 'edd'  ),
		'add_new_item' => __( 'Add New Tag', 'edd'  ),
		'new_item_name' => __( 'New Tag Name', 'edd'  ),
		'menu_name' => __( 'Tags', 'edd'  ),
	); 	

	register_taxonomy('download_tag', array('download'), array(
		'hierarchical' => false,
		'labels' => apply_filters('edd_download_tag_labels', $tag_labels),
		'show_ui' => true,
		'query_var' => 'download_tag',
		'rewrite' => array('slug' => 'downloads/tag')
	));
}
add_action('init', 'edd_setup_download_taxonomies', 10);


/**
 * Updated Messages
 *
 * Returns an array of with all updated messages.
 *
 * @access      public
 * @since       1.0 
 * @return      array
*/

function edd_updated_messages( $messages ) {
	global $post, $post_ID;

	$messages['download'] = array(
		1 => __('Download updated.', 'edd' ),
		4 => __('Download updated.', 'edd' ),
		6 => __('Download published.', 'edd' ),
		7 => __('Download saved.', 'edd' ),
		8 => __('Download submitted.', 'edd'),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'edd_updated_messages' );