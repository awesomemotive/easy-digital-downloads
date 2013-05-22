<?php
/**
 * Post Type Functions
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registers and sets up the Downloads custom post type
 *
 * @since 1.0
 * @return void
 */
function edd_setup_edd_post_types() {

	$archives = defined( 'EDD_DISABLE_ARCHIVE' ) && EDD_DISABLE_ARCHIVE ? false : true;
	$slug     = defined( 'EDD_SLUG' ) ? EDD_SLUG : 'downloads';
	$rewrite  = defined( 'EDD_DISABLE_REWRITE' ) && EDD_DISABLE_REWRITE ? false : array('slug' => $slug, 'with_front' => false);

	$download_labels =  apply_filters( 'edd_download_labels', array(
		'name' 				=> '%2$s',
		'singular_name' 	=> '%1$s',
		'add_new' 			=> __( 'Add New', 'edd' ),
		'add_new_item' 		=> __( 'Add New %1$s', 'edd' ),
		'edit_item' 		=> __( 'Edit %1$s', 'edd' ),
		'new_item' 			=> __( 'New %1$s', 'edd' ),
		'all_items' 		=> __( 'All %2$s', 'edd' ),
		'view_item' 		=> __( 'View %1$s', 'edd' ),
		'search_items' 		=> __( 'Search %2$s', 'edd' ),
		'not_found' 		=> __( 'No %2$s found', 'edd' ),
		'not_found_in_trash'=> __( 'No %2$s found in Trash', 'edd' ),
		'parent_item_colon' => '',
		'menu_name' 		=> __( '%2$s', 'edd' )
	) );

	foreach ( $download_labels as $key => $value ) {
	   $download_labels[ $key ] = sprintf( $value, edd_get_label_singular(), edd_get_label_plural() );
	}

	$download_args = array(
		'labels' 			=> $download_labels,
		'public' 			=> true,
		'publicly_queryable'=> true,
		'show_ui' 			=> true,
		'show_in_menu' 		=> true,
		'query_var' 		=> true,
		'rewrite' 			=> $rewrite,
		'capability_type' 	=> 'product',
		'map_meta_cap'      => true,
		'has_archive' 		=> $archives,
		'hierarchical' 		=> false,
		'supports' 			=> apply_filters( 'edd_download_supports', array( 'title', 'editor', 'thumbnail', 'excerpt' ) ),
	);
	register_post_type( 'download', apply_filters( 'edd_download_post_type_args', $download_args ) );


	/** Payment Post Type */
	$payment_labels = array(
		'name' 				=> _x('Payments', 'post type general name', 'edd' ),
		'singular_name' 	=> _x('Payment', 'post type singular name', 'edd' ),
		'add_new' 			=> __( 'Add New', 'edd' ),
		'add_new_item' 		=> __( 'Add New Payment', 'edd' ),
		'edit_item' 		=> __( 'Edit Payment', 'edd' ),
		'new_item' 			=> __( 'New Payment', 'edd' ),
		'all_items' 		=> __( 'All Payments', 'edd' ),
		'view_item' 		=> __( 'View Payment', 'edd' ),
		'search_items' 		=> __( 'Search Payments', 'edd' ),
		'not_found' 		=>  __( 'No Payments found', 'edd' ),
		'not_found_in_trash'=> __( 'No Payments found in Trash', 'edd' ),
		'parent_item_colon' => '',
		'menu_name' 		=> __( 'Payment History', 'edd' )
	);

	$payment_args = array(
		'labels' 			=> apply_filters( 'edd_payment_labels', $payment_labels ),
		'public' 			=> false,
		'query_var' 		=> false,
		'rewrite' 			=> false,
		'capability_type' 	=> 'shop_payment',
		'map_meta_cap'      => true,
		'supports' 			=> array( 'title' ),
		'can_export'		=> true
	);
	register_post_type( 'edd_payment', $payment_args );


	/** Discounts Post Type */
	$discount_labels = array(
		'name' 				=> _x( 'Discounts', 'post type general name', 'edd' ),
		'singular_name' 	=> _x( 'Discount', 'post type singular name', 'edd' ),
		'add_new' 			=> __( 'Add New', 'edd' ),
		'add_new_item' 		=> __( 'Add New Discount', 'edd' ),
		'edit_item' 		=> __( 'Edit Discount', 'edd' ),
		'new_item' 			=> __( 'New Discount', 'edd' ),
		'all_items' 		=> __( 'All Discounts', 'edd' ),
		'view_item' 		=> __( 'View Discount', 'edd' ),
		'search_items' 		=> __( 'Search Discounts', 'edd' ),
		'not_found' 		=> __( 'No Discounts found', 'edd' ),
		'not_found_in_trash'=> __( 'No Discounts found in Trash', 'edd' ),
		'parent_item_colon' => '',
		'menu_name' 		=> __( 'Discounts', 'edd' )
	);

	$discount_args = array(
		'labels' 			=> apply_filters( 'edd_discount_labels', $discount_labels ),
		'public' 			=> false,
		'query_var' 		=> false,
		'rewrite' 			=> false,
		'show_ui'           => false,
		'capability_type' 	=> 'shop_discount',
		'map_meta_cap'      => true,
		'supports' 			=> array( 'title' ),
		'can_export'		=> true
	);
	register_post_type( 'edd_discount', $discount_args );
}
add_action( 'init', 'edd_setup_edd_post_types', 1 );

/**
 * Get Default Labels
 *
 * @since 1.0.8.3
 * @return array $defaults Default labels
 */
function edd_get_default_labels() {
	$defaults = array(
	   'singular' => __( 'Download', 'edd' ),
	   'plural' => __( 'Downloads', 'edd')
	);
	return apply_filters( 'edd_default_downloads_name', $defaults );
}

/**
 * Get Singular Label
 *
 * @since 1.0.8.3
 * @return string $defaults['singular'] Singular label
 */
function edd_get_label_singular( $lowercase = false ) {
	$defaults = edd_get_default_labels();
	return ($lowercase) ? strtolower( $defaults['singular'] ) : $defaults['singular'];
}

/**
 * Get Plural Label
 *
 * @since 1.0.8.3
 * @return string $defaults['plural'] Plural label
 */
function edd_get_label_plural( $lowercase = false ) {
	$defaults = edd_get_default_labels();
	return ( $lowercase ) ? strtolower( $defaults['plural'] ) : $defaults['plural'];
}

/**
 * Change default "Enter title here" input
 *
 * @since 1.4.0.2
 * @param string $title Default title placeholder text
 * @return string $title New placeholder text
 */
function edd_change_default_title( $title ) {
     $screen = get_current_screen();

     if  ( 'download' == $screen->post_type ) {
     	$label = edd_get_label_singular();
        $title = sprintf( __( 'Enter %s title here', 'edd' ), $label );
     }

     return $title;
}
add_filter( 'enter_title_here', 'edd_change_default_title' );

/**
 * Registers the custom taxonomies for the downloads custom post type
 *
 * @since 1.0
 * @return void
*/
function edd_setup_download_taxonomies() {

	$slug     = defined( 'EDD_SLUG' ) ? EDD_SLUG : 'downloads';

	/** Categories */
	$category_labels = array(
		'name' 				=> sprintf( _x( '%s Categories', 'taxonomy general name', 'edd' ), edd_get_label_singular() ),
		'singular_name' 	=> _x( 'Category', 'taxonomy singular name', 'edd' ),
		'search_items' 		=> __( 'Search Categories', 'edd'  ),
		'all_items' 		=> __( 'All Categories', 'edd'  ),
		'parent_item' 		=> __( 'Parent Category', 'edd'  ),
		'parent_item_colon' => __( 'Parent Category:', 'edd'  ),
		'edit_item' 		=> __( 'Edit Category', 'edd'  ),
		'update_item' 		=> __( 'Update Category', 'edd'  ),
		'add_new_item' 		=> __( 'Add New Category', 'edd'  ),
		'new_item_name' 	=> __( 'New Category Name', 'edd'  ),
		'menu_name' 		=> __( 'Categories', 'edd'  ),
	);

	$category_args = apply_filters( 'edd_download_category_args', array(
			'hierarchical' 	=> true,
			'labels' 		=> apply_filters('edd_download_category_labels', $category_labels),
			'show_ui' 		=> true,
			'query_var' 	=> 'download_category',
			'rewrite' 		=> array('slug' => $slug . '/category', 'with_front' => false, 'hierarchical' => true ),
			'capabilities'  => array( 'manage_terms' => 'manage_product_terms','edit_terms' => 'edit_product_terms','assign_terms' => 'assign_product_terms','delete_terms' => 'delete_product_terms' )
		)
	);
	register_taxonomy( 'download_category', array('download'), $category_args );
	register_taxonomy_for_object_type( 'download_category', 'download' );

	/** Tags */
	$tag_labels = array(
		'name' 				=> sprintf( _x( '%s Tags', 'taxonomy general name', 'edd' ), edd_get_label_singular() ),
		'singular_name' 	=> _x( 'Tag', 'taxonomy singular name', 'edd' ),
		'search_items' 		=> __( 'Search Tags', 'edd'  ),
		'all_items' 		=> __( 'All Tags', 'edd'  ),
		'parent_item' 		=> __( 'Parent Tag', 'edd'  ),
		'parent_item_colon' => __( 'Parent Tag:', 'edd'  ),
		'edit_item' 		=> __( 'Edit Tag', 'edd'  ),
		'update_item' 		=> __( 'Update Tag', 'edd'  ),
		'add_new_item' 		=> __( 'Add New Tag', 'edd'  ),
		'new_item_name' 	=> __( 'New Tag Name', 'edd'  ),
		'menu_name' 		=> __( 'Tags', 'edd'  ),
	);

	$tag_args = apply_filters( 'edd_download_tag_args', array(
			'hierarchical' 	=> false,
			'labels' 		=> apply_filters( 'edd_download_tag_labels', $tag_labels ),
			'show_ui' 		=> true,
			'query_var' 	=> 'download_tag',
			'rewrite' 		=> array( 'slug' => $slug . '/tag', 'with_front' => false, 'hierarchical' => true  ),
			'capabilities'  => array( 'manage_terms' => 'manage_product_terms','edit_terms' => 'edit_product_terms','assign_terms' => 'assign_product_terms','delete_terms' => 'delete_product_terms' )

		)
	);
	register_taxonomy( 'download_tag', array( 'download' ), $tag_args );
	register_taxonomy_for_object_type( 'download_tag', 'download' );
}
add_action( 'init', 'edd_setup_download_taxonomies', 0 );

/**
 * Registers Custom Post Statuses which are used by the Payments and Discount
 * Codes
 *
 * @since 1.0.9.1
 * @return void
 */
function edd_register_post_type_statuses() {
	// Payment Statuses
	register_post_status( 'refunded', array(
		'label'                     => _x( 'Refunded', 'Refunded payment status', 'edd' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'edd' )
	) );
	register_post_status( 'failed', array(
		'label'                     => _x( 'Failed', 'Failed payment status', 'edd' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'edd' )
	)  );
	register_post_status( 'revoked', array(
		'label'                     => _x( 'Revoked', 'Revoked payment status', 'edd' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Revoked <span class="count">(%s)</span>', 'Revoked <span class="count">(%s)</span>', 'edd' )
	)  );
	register_post_status( 'abandoned', array(
		'label'                     => _x( 'Abandoned', 'Abandoned payment status', 'edd' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Abandoned <span class="count">(%s)</span>', 'Abandoned <span class="count">(%s)</span>', 'edd' )
	)  );

	// Discount Code Statuses
	register_post_status( 'active', array(
		'label'                     => _x( 'Active', 'Active discount code status', 'edd' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'edd' )
	)  );
	register_post_status( 'inactive', array(
		'label'                     => _x( 'Inactive', 'Inactive discount code status', 'edd' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', 'edd' )
	)  );
}
add_action( 'init', 'edd_register_post_type_statuses' );

/**
 * Updated Messages
 *
 * Returns an array of with all updated messages.
 *
 * @since 1.0
 * @param array $messages Post updated message
 * @return array $messages New post updated messages
 */
function edd_updated_messages( $messages ) {
	global $post, $post_ID;

	$url1 = '<a href="' . get_permalink( $post_ID ) . '">';
	$url2 = edd_get_label_singular();
	$url3 = '</a>';

	$messages['download'] = array(
		1 => sprintf( __( '%2$s updated. %1$sView %2$s%3$s.', 'edd' ), $url1, $url2, $url3 ),
		4 => sprintf( __( '%2$s updated. %1$sView %2$s%3$s.', 'edd' ), $url1, $url2, $url3 ),
		6 => sprintf( __( '%2$s published. %1$sView %2$s%3$s.', 'edd' ), $url1, $url2, $url3 ),
		7 => sprintf( __( '%2$s saved. %1$sView %2$s%3$s.', 'edd' ), $url1, $url2, $url3 ),
		8 => sprintf( __( '%2$s submitted. %1$sView %2$s%3$s.', 'edd' ), $url1, $url2, $url3 )
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'edd_updated_messages' );
