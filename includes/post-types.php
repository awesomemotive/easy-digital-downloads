<?php

function edd_setup_download_post_type() {

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
	
	$menu_position = 15;
	if(defined('EDD_MENU_POSITION') && is_numeric(EDD_MENU_POSITION)) {
		$menu_position = EDD_MENU_POSITION;
	}
	
	$download_labels = array(
		'name' => _x('Downloads', 'post type general name', 'edd'),
		'singular_name' => _x('Download', 'post type singular name', 'edd'),
		'add_new' => __('Add New', 'edd'),
		'add_new_item' => __('Add New Download', 'edd'),
		'edit_item' => __('Edit Download', 'edd'),
		'new_item' => __('New Download', 'edd'),
		'all_items' => __('All Downloads', 'edd'),
		'view_item' => __('View Download', 'edd'),
		'search_items' => __('Search Downloads', 'edd'),
		'not_found' =>  __('No Downloads found', 'edd'),
		'not_found_in_trash' => __('No Downloads found in Trash', 'edd'), 
		'parent_item_colon' => '',
		'menu_name' => __('Downloads', 'edd')
	);
	
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
		'menu_position' => $menu_position,
		'supports' => array( 'title', 'editor', 'thumbnail' ),
	); 
	register_post_type('download', $download_args);
	
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
		'labels' => $payment_labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true, 
		'show_in_menu' => false, 
		'query_var' => true,
		'rewrite' => false,
		'capability_type' => 'post',
		'has_archive' => false, 
		'hierarchical' => false,
		'supports' => array( 'title' ),
		'menu_icon' => EDD_PLUGIN_URL . 'includes/images/media-button.png'
	); 
	register_post_type('edd_payment', $payment_args);
	
}
add_action('init', 'edd_setup_download_post_type', 100);

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
		'labels' => $category_labels,
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
		'labels' => $tag_labels,
		'show_ui' => true,
		'query_var' => 'download_tag',
		'rewrite' => array('slug' => 'downloads/tag')
	));
}
add_action('init', 'edd_setup_download_taxonomies', 10);

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