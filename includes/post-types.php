<?php
/**
 * Post Type Functions
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2015, Pippin Williamson
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
		'name'                  => _x( '%2$s', 'download post type name', 'easy-digital-downloads' ),
		'singular_name'         => _x( '%1$s', 'singular download post type name', 'easy-digital-downloads' ),
		'add_new'               => __( 'Add New', 'easy-digital-downloads' ),
		'add_new_item'          => __( 'Add New %1$s', 'easy-digital-downloads' ),
		'edit_item'             => __( 'Edit %1$s', 'easy-digital-downloads' ),
		'new_item'              => __( 'New %1$s', 'easy-digital-downloads' ),
		'all_items'             => __( 'All %2$s', 'easy-digital-downloads' ),
		'view_item'             => __( 'View %1$s', 'easy-digital-downloads' ),
		'search_items'          => __( 'Search %2$s', 'easy-digital-downloads' ),
		'not_found'             => __( 'No %2$s found', 'easy-digital-downloads' ),
		'not_found_in_trash'    => __( 'No %2$s found in Trash', 'easy-digital-downloads' ),
		'parent_item_colon'     => '',
		'menu_name'             => _x( '%2$s', 'download post type menu name', 'easy-digital-downloads' ),
		'featured_image'        => __( '%1$s Image', 'easy-digital-downloads' ),
		'set_featured_image'    => __( 'Set %1$s Image', 'easy-digital-downloads' ),
		'remove_featured_image' => __( 'Remove %1$s Image', 'easy-digital-downloads' ),
		'use_featured_image'    => __( 'Use as %1$s Image', 'easy-digital-downloads' ),
		'attributes'            => __( '%1$s Attributes', 'easy-digital-downloads' ),
		'filter_items_list'     => __( 'Filter %2$s list', 'easy-digital-downloads' ),
		'items_list_navigation' => __( '%2$s list navigation', 'easy-digital-downloads' ),
		'items_list'            => __( '%2$s list', 'easy-digital-downloads' ),
	) );

	foreach ( $download_labels as $key => $value ) {
		$download_labels[ $key ] = sprintf( $value, edd_get_label_singular(), edd_get_label_plural() );
	}

	$download_args = array(
		'labels'             => $download_labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'menu_icon'          => 'dashicons-download',
		'rewrite'            => $rewrite,
		'capability_type'    => 'product',
		'map_meta_cap'       => true,
		'has_archive'        => $archives,
		'hierarchical'       => false,
		'supports'           => apply_filters( 'edd_download_supports', array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author' ) ),
	);
	register_post_type( 'download', apply_filters( 'edd_download_post_type_args', $download_args ) );


	/** Payment Post Type */
	$payment_labels = array(
		'name'               => _x( 'Payments', 'post type general name', 'easy-digital-downloads' ),
		'singular_name'      => _x( 'Payment', 'post type singular name', 'easy-digital-downloads' ),
		'add_new'            => __( 'Add New', 'easy-digital-downloads' ),
		'add_new_item'       => __( 'Add New Payment', 'easy-digital-downloads' ),
		'edit_item'          => __( 'Edit Payment', 'easy-digital-downloads' ),
		'new_item'           => __( 'New Payment', 'easy-digital-downloads' ),
		'all_items'          => __( 'All Payments', 'easy-digital-downloads' ),
		'view_item'          => __( 'View Payment', 'easy-digital-downloads' ),
		'search_items'       => __( 'Search Payments', 'easy-digital-downloads' ),
		'not_found'          => __( 'No Payments found', 'easy-digital-downloads' ),
		'not_found_in_trash' => __( 'No Payments found in Trash', 'easy-digital-downloads' ),
		'parent_item_colon'  => '',
		'menu_name'          => __( 'Payment History', 'easy-digital-downloads' )
	);

	$payment_args = array(
		'labels'          => apply_filters( 'edd_payment_labels', $payment_labels ),
		'public'          => false,
		'query_var'       => false,
		'rewrite'         => false,
		'capability_type' => 'shop_payment',
		'map_meta_cap'    => true,
		'supports'        => array( 'title' ),
		'can_export'      => true
	);
	register_post_type( 'edd_payment', $payment_args );


	/** Discounts Post Type */
	$discount_labels = array(
		'name'               => _x( 'Discounts', 'post type general name', 'easy-digital-downloads' ),
		'singular_name'      => _x( 'Discount', 'post type singular name', 'easy-digital-downloads' ),
		'add_new'            => __( 'Add New', 'easy-digital-downloads' ),
		'add_new_item'       => __( 'Add New Discount', 'easy-digital-downloads' ),
		'edit_item'          => __( 'Edit Discount', 'easy-digital-downloads' ),
		'new_item'           => __( 'New Discount', 'easy-digital-downloads' ),
		'all_items'          => __( 'All Discounts', 'easy-digital-downloads' ),
		'view_item'          => __( 'View Discount', 'easy-digital-downloads' ),
		'search_items'       => __( 'Search Discounts', 'easy-digital-downloads' ),
		'not_found'          => __( 'No Discounts found', 'easy-digital-downloads' ),
		'not_found_in_trash' => __( 'No Discounts found in Trash', 'easy-digital-downloads' ),
		'parent_item_colon'  => '',
		'menu_name'          => __( 'Discounts', 'easy-digital-downloads' )
	);

	$discount_args = array(
		'labels'          => apply_filters( 'edd_discount_labels', $discount_labels ),
		'public'          => false,
		'query_var'       => false,
		'rewrite'         => false,
		'show_ui'         => false,
		'capability_type' => 'shop_discount',
		'map_meta_cap'    => true,
		'supports'        => array( 'title' ),
		'can_export'      => true
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
	   'singular' => __( 'Download', 'easy-digital-downloads' ),
	   'plural'   => __( 'Downloads','easy-digital-downloads' )
	);
	return apply_filters( 'edd_default_downloads_name', $defaults );
}

/**
 * Get Singular Label
 *
 * @since 1.0.8.3
 *
 * @param bool $lowercase
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
	 // If a frontend plugin uses this filter (check extensions before changing this function)
	 if ( !is_admin() ) {
		$label = edd_get_label_singular();
		$title = sprintf( __( 'Enter %s name here', 'easy-digital-downloads' ), $label );
		return $title;
	 }

	 $screen = get_current_screen();

	 if ( 'download' == $screen->post_type ) {
		$label = edd_get_label_singular();
		$title = sprintf( __( 'Enter %s name here', 'easy-digital-downloads' ), $label );
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
		'name'              => sprintf( _x( '%s Categories', 'taxonomy general name', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'singular_name'     => sprintf( _x( '%s Category', 'taxonomy singular name', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'search_items'      => sprintf( __( 'Search %s Categories', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'all_items'         => sprintf( __( 'All %s Categories', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'parent_item'       => sprintf( __( 'Parent %s Category', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'parent_item_colon' => sprintf( __( 'Parent %s Category:', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'edit_item'         => sprintf( __( 'Edit %s Category', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'update_item'       => sprintf( __( 'Update %s Category', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'add_new_item'      => sprintf( __( 'Add New %s Category', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'new_item_name'     => sprintf( __( 'New %s Category Name', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'menu_name'         => __( 'Categories', 'easy-digital-downloads' ),
	);

	$category_args = apply_filters( 'edd_download_category_args', array(
			'hierarchical' => true,
			'labels'       => apply_filters('edd_download_category_labels', $category_labels),
			'show_ui'      => true,
			'query_var'    => 'download_category',
			'rewrite'      => array('slug' => $slug . '/category', 'with_front' => false, 'hierarchical' => true ),
			'capabilities' => array( 'manage_terms' => 'manage_product_terms','edit_terms' => 'edit_product_terms','assign_terms' => 'assign_product_terms','delete_terms' => 'delete_product_terms' )
		)
	);
	register_taxonomy( 'download_category', array('download'), $category_args );
	register_taxonomy_for_object_type( 'download_category', 'download' );

	/** Tags */
	$tag_labels = array(
		'name'                  => sprintf( _x( '%s Tags', 'taxonomy general name', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'singular_name'         => sprintf( _x( '%s Tag', 'taxonomy singular name', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'search_items'          => sprintf( __( 'Search %s Tags', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'all_items'             => sprintf( __( 'All %s Tags', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'parent_item'           => sprintf( __( 'Parent %s Tag', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'parent_item_colon'     => sprintf( __( 'Parent %s Tag:', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'edit_item'             => sprintf( __( 'Edit %s Tag', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'update_item'           => sprintf( __( 'Update %s Tag', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'add_new_item'          => sprintf( __( 'Add New %s Tag', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'new_item_name'         => sprintf( __( 'New %s Tag Name', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'menu_name'             => __( 'Tags', 'easy-digital-downloads' ),
		'choose_from_most_used' => sprintf( __( 'Choose from most used %s tags', 'easy-digital-downloads' ), edd_get_label_singular() ),
	);

	$tag_args = apply_filters( 'edd_download_tag_args', array(
			'hierarchical' => false,
			'labels'       => apply_filters( 'edd_download_tag_labels', $tag_labels ),
			'show_ui'      => true,
			'query_var'    => 'download_tag',
			'rewrite'      => array( 'slug' => $slug . '/tag', 'with_front' => false, 'hierarchical' => true  ),
			'capabilities' => array( 'manage_terms' => 'manage_product_terms','edit_terms' => 'edit_product_terms','assign_terms' => 'assign_product_terms','delete_terms' => 'delete_product_terms' )
		)
	);
	register_taxonomy( 'download_tag', array( 'download' ), $tag_args );
	register_taxonomy_for_object_type( 'download_tag', 'download' );
}
add_action( 'init', 'edd_setup_download_taxonomies', 0 );

/**
 * Get the singular and plural labels for a download taxonomy
 *
 * @since  2.4
 * @param  string $taxonomy The Taxonomy to get labels for
 * @return array            Associative array of labels (name = plural)
 */
function edd_get_taxonomy_labels( $taxonomy = 'download_category' ) {
	$allowed_taxonomies = apply_filters( 'edd_allowed_download_taxonomies', array( 'download_category', 'download_tag' ) );

	if ( ! in_array( $taxonomy, $allowed_taxonomies ) ) {
		return false;
	}

	$labels   = array();
	$taxonomy = get_taxonomy( $taxonomy );

	if ( false !== $taxonomy ) {
		$singular  = $taxonomy->labels->singular_name;
		$name      = $taxonomy->labels->name;
		$menu_name = $taxonomy->labels->menu_name;

		$labels = array(
			'name'          => $name,
			'singular_name' => $singular,
			'menu_name'     => $menu_name,
		);
	}

	return apply_filters( 'edd_get_taxonomy_labels', $labels, $taxonomy );
}

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
		'label'                     => _x( 'Refunded', 'Refunded payment status', 'easy-digital-downloads' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'easy-digital-downloads' )
	) );
	register_post_status( 'failed', array(
		'label'                     => _x( 'Failed', 'Failed payment status', 'easy-digital-downloads' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'easy-digital-downloads' )
	)  );
	register_post_status( 'revoked', array(
		'label'                     => _x( 'Revoked', 'Revoked payment status', 'easy-digital-downloads' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Revoked <span class="count">(%s)</span>', 'Revoked <span class="count">(%s)</span>', 'easy-digital-downloads' )
	)  );
	register_post_status( 'abandoned', array(
		'label'                     => _x( 'Abandoned', 'Abandoned payment status', 'easy-digital-downloads' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Abandoned <span class="count">(%s)</span>', 'Abandoned <span class="count">(%s)</span>', 'easy-digital-downloads' )
	)  );
	register_post_status( 'processing', array(
		'label'                     => _x( 'Processing', 'Processing payment status', 'easy-digital-downloads' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Processing <span class="count">(%s)</span>', 'Processing <span class="count">(%s)</span>', 'easy-digital-downloads' )
	)  );

	// Discount Code Statuses
	register_post_status( 'active', array(
		'label'                     => _x( 'Active', 'Active discount code status', 'easy-digital-downloads' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'easy-digital-downloads' )
	)  );
	register_post_status( 'inactive', array(
		'label'                     => _x( 'Inactive', 'Inactive discount code status', 'easy-digital-downloads' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', 'easy-digital-downloads' )
	)  );
}
add_action( 'init', 'edd_register_post_type_statuses', 2 );

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
		1 => sprintf( __( '%2$s updated. %1$sView %2$s%3$s.', 'easy-digital-downloads' ), $url1, $url2, $url3 ),
		4 => sprintf( __( '%2$s updated. %1$sView %2$s%3$s.', 'easy-digital-downloads' ), $url1, $url2, $url3 ),
		6 => sprintf( __( '%2$s published. %1$sView %2$s%3$s.', 'easy-digital-downloads' ), $url1, $url2, $url3 ),
		7 => sprintf( __( '%2$s saved. %1$sView %2$s%3$s.', 'easy-digital-downloads' ), $url1, $url2, $url3 ),
		8 => sprintf( __( '%2$s submitted. %1$sView %2$s%3$s.', 'easy-digital-downloads' ), $url1, $url2, $url3 )
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'edd_updated_messages' );

/**
 * Updated bulk messages
 *
 * @since 2.3
 * @param array $bulk_messages Post updated messages
 * @param array $bulk_counts Post counts
 * @return array $bulk_messages New post updated messages
 */
function edd_bulk_updated_messages( $bulk_messages, $bulk_counts ) {
	$singular = edd_get_label_singular();
	$plural   = edd_get_label_plural();

	$bulk_messages['download'] = array(
		'updated'   => sprintf( _n( '%1$s %2$s updated.', '%1$s %3$s updated.', $bulk_counts['updated'], 'easy-digital-downloads' ), $bulk_counts['updated'], $singular, $plural ),
		'locked'    => sprintf( _n( '%1$s %2$s not updated, somebody is editing it.', '%1$s %3$s not updated, somebody is editing them.', $bulk_counts['locked'], 'easy-digital-downloads' ), $bulk_counts['locked'], $singular, $plural ),
		'deleted'   => sprintf( _n( '%1$s %2$s permanently deleted.', '%1$s %3$s permanently deleted.', $bulk_counts['deleted'], 'easy-digital-downloads' ), $bulk_counts['deleted'], $singular, $plural ),
		'trashed'   => sprintf( _n( '%1$s %2$s moved to the Trash.', '%1$s %3$s moved to the Trash.', $bulk_counts['trashed'], 'easy-digital-downloads' ), $bulk_counts['trashed'], $singular, $plural ),
		'untrashed' => sprintf( _n( '%1$s %2$s restored from the Trash.', '%1$s %3$s restored from the Trash.', $bulk_counts['untrashed'], 'easy-digital-downloads' ), $bulk_counts['untrashed'], $singular, $plural )
	);

	return $bulk_messages;
}
add_filter( 'bulk_post_updated_messages', 'edd_bulk_updated_messages', 10, 2 );

/**
 * Add row actions for the downloads custom post type
 *
 * @since 2.5
 * @param  array $actions
 * @param  WP_Post $post
 * @return array
 */
function  edd_download_row_actions( $actions, $post ) {
	if ( 'download' === $post->post_type ) {
		return array_merge( array( 'id' => 'ID: ' . $post->ID ), $actions );
	}

	return $actions;
}
add_filter( 'post_row_actions', 'edd_download_row_actions', 2, 100 );
