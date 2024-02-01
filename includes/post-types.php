<?php
/**
 * Post Type Functions
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Registers and sets up the Downloads custom post type
 *
 * @since 1.0
 * @since 3.2.0 Removes `edd_payment` and `edd_discount` from registered post types.
 * @return void
 */
function edd_setup_edd_post_types() {
	$archives = defined( 'EDD_DISABLE_ARCHIVE' ) && EDD_DISABLE_ARCHIVE
		? false
		: true;

	$slug = defined( 'EDD_SLUG' )
		? EDD_SLUG
		: 'downloads';

	$rewrite = defined( 'EDD_DISABLE_REWRITE' ) && EDD_DISABLE_REWRITE
		? false
		: array(
			'slug'       => $slug,
			'with_front' => false,
		);

	$download_labels = apply_filters( 'edd_download_labels', array(
		'name'                  => _x( '%2$s', 'download post type name', 'easy-digital-downloads' ),
		'singular_name'         => _x( '%1$s', 'singular download post type name', 'easy-digital-downloads' ),
		'add_new'               => __( 'Add New', 'easy-digital-downloads' ),
		'add_new_item'          => __( 'Add New %1$s', 'easy-digital-downloads' ),
		'edit_item'             => __( 'Edit %1$s', 'easy-digital-downloads' ),
		'new_item'              => __( 'New %1$s', 'easy-digital-downloads' ),
		'all_items'             => __( '%2$s', 'easy-digital-downloads' ),
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
		'show_in_rest'       => true,
		'rest_base'          => 'edd-downloads',
		'supports'           => apply_filters( 'edd_download_supports', array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author' ) ),
	);
	register_post_type( 'download', apply_filters( 'edd_download_post_type_args', $download_args ) );
}
add_action( 'init', 'edd_setup_edd_post_types', 1 );

/**
 * Adds support to post-types that should allow for Downloads to be inserted
 * into their post_content areas.
 *
 * By default, this covers only core Post and Page types.
 *
 * @since 3.0
 */
function edd_setup_post_type_support() {
	add_post_type_support( 'post', 'edd_insert_download' );
	add_post_type_support( 'page', 'edd_insert_download' );
}
add_action( 'init', 'edd_setup_post_type_support' );

/**
 * Get default labels.
 *
 * @since 1.0.8.3
 *
 * @return array $defaults Default labels
 */
function edd_get_default_labels() {
	$defaults = array(
		'singular' => __( 'Download', 'easy-digital-downloads' ),
		'plural'   => __( 'Downloads', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_default_downloads_name', $defaults );
}

/**
 * Get singular label.
 *
 * @since 1.0.8.3
 *
 * @param bool $lowercase Optional. Default false.
 * @return string Singular label.
 */
function edd_get_label_singular( $lowercase = false ) {
	$defaults = edd_get_default_labels();

	return $lowercase
		? strtolower( $defaults['singular'] )
		: $defaults['singular'];
}

/**
 * Get plural label.
 *
 * @since 1.0.8.3
 *
 * @param bool $lowercase Optional. Default false.
 * @return string Plural label.
 */
function edd_get_label_plural( $lowercase = false ) {
	$defaults = edd_get_default_labels();

	return $lowercase
		? strtolower( $defaults['plural'] )
		: $defaults['plural'];
}

/**
 * Change default "Enter title here" input.
 *
 * @since 1.4.0.2
 *
 * @param string $title Default title placeholder text.
 * @return string $title New placeholder text.
 */
function edd_change_default_title( $title ) {

	// If a frontend plugin uses this filter (check extensions before changing this function).
	if ( ! is_admin() ) {
		$label = edd_get_label_singular();
		$title = sprintf( __( 'Enter %s name here', 'easy-digital-downloads' ), $label );

		return $title;
	}

	$screen = get_current_screen();

	if ( 'download' === $screen->post_type ) {
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
	$slug = defined( 'EDD_SLUG' ) ? EDD_SLUG : 'downloads';

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
			'labels'       => apply_filters( 'edd_download_category_labels', $category_labels ),
			'show_ui'      => true,
			'query_var'    => 'download_category',
			'rewrite'      => array( 'slug' => $slug . '/category', 'with_front' => false, 'hierarchical' => true ),
			'show_in_rest'          => true,
			'rest_base'             => 'edd-categories',
			'rest_controller_class' => 'WP_REST_Terms_Controller',
			'capabilities' => array(
				'manage_terms' => 'manage_product_terms',
				'edit_terms'   => 'edit_product_terms',
				'assign_terms' => 'assign_product_terms',
				'delete_terms' => 'delete_product_terms',
			),
		)
	);
	register_taxonomy( 'download_category', array( 'download' ), $category_args );
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
			'rewrite'      => array( 'slug' => $slug . '/tag', 'with_front' => false, 'hierarchical' => true ),
			'show_in_rest'          => true,
			'rest_base'             => 'edd-tags',
			'rest_controller_class' => 'WP_REST_Terms_Controller',
			'capabilities' => array(
				'manage_terms' => 'manage_product_terms',
				'edit_terms'   => 'edit_product_terms',
				'assign_terms' => 'assign_product_terms',
				'delete_terms' => 'delete_product_terms',
			),
		)
	);

	register_taxonomy( 'download_tag', array( 'download' ), $tag_args );
	register_taxonomy_for_object_type( 'download_tag', 'download' );
}
add_action( 'init', 'edd_setup_download_taxonomies', 0 );

/**
 * Gets the names for the default download taxonomies.
 *
 * @since 3.0
 * @return array
 */
function edd_get_download_taxonomies() {
	return apply_filters(
		'edd_download_taxonomies',
		array(
			'download_category',
			'download_tag',
		)
	);
}

/**
 * Get the singular and plural labels for a download taxonomy
 *
 * @since  2.4
 * @param  string $taxonomy The Taxonomy to get labels for
 * @return array            Associative array of labels (name = plural)
 */
function edd_get_taxonomy_labels( $taxonomy = 'download_category' ) {
	$allowed_taxonomies = apply_filters( 'edd_allowed_download_taxonomies', array(
		'download_category',
		'download_tag',
	) );

	if ( ! in_array( $taxonomy, $allowed_taxonomies, true ) ) {
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
 * Post updated messages.
 *
 * @since 1.0
 *
 * @param array $messages Post updated message
 * @return array $messages New post updated messages
 */
function edd_updated_messages( $messages ) {
	global $post, $post_ID;

	$url1 = '<a href="' . esc_url( get_permalink( $post_ID ) ) . '">';
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
 * Add bulk action updated messages for downloads.
 *
 * @since 2.3
 *
 * @param array[] $bulk_messages Arrays of messages, each keyed by the corresponding post type. Messages are
 *                               keyed with 'updated', 'locked', 'deleted', 'trashed', and 'untrashed'.
 * @param int[]   $bulk_counts   Array of item counts for each message, used to build internationalized strings.
 *
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
 *
 * @param string[] $actions An array of row action links. Defaults are
 *                          'Edit', 'Quick Edit', 'Restore', 'Trash',
 *                          'Delete Permanently', 'Preview', and 'View'.
 * @param WP_Post  $post    The post object.
 *
 * @return array
 */
function edd_download_row_actions( $actions, $post ) {
	if ( 'download' === $post->post_type ) {
		return array_merge( array( 'id' => '#' . $post->ID ), $actions );
	}

	return $actions;
}
add_filter( 'post_row_actions', 'edd_download_row_actions', 2, 100 );

/**
 * Adds the custom page state display to the Pages list.
 *
 * @param array   $post_states The existing registered post states.
 * @param WP_Post $post        The post to possibly append the post state to.
 * @since 3.1
 */
function edd_display_post_states( $post_states, $post ) {
	if ( intval( edd_get_option( 'purchase_page' ) ) === $post->ID ) {
		$post_states['edd_purchase_page'] = __( 'Checkout Page', 'easy-digital-downloads' );
	}

	if ( intval( edd_get_option( 'success_page' ) ) === $post->ID ) {
		$post_states['edd_success_page'] = __( 'Success Page', 'easy-digital-downloads' );
	}

	if ( intval( edd_get_option( 'failure_page' ) ) === $post->ID ) {
		$post_states['edd_failure_page'] = __( 'Failed Transaction Page', 'easy-digital-downloads' );
	}

	if ( intval( edd_get_option( 'purchase_history_page' ) ) === $post->ID ) {
		$post_states['edd_purchase_history_page'] = __( 'Purchase History Page', 'easy-digital-downloads' );
	}

	if ( intval( edd_get_option( 'login_redirect_page' ) ) === $post->ID ) {
		$post_states['edd_login_redirect_page'] = __( 'Login Redirect Page', 'easy-digital-downloads' );
	}

	return $post_states;
}
add_filter( 'display_post_states', 'edd_display_post_states', 10, 2 );

/**
 * Adds EDD custom roles to the REST API authors query.
 *
 * @param array $prepared_args
 * @param array $request
 * @return array
 */
function edd_add_custom_roles_rest_user_query( $prepared_args, $request ) {
	// If the args don't match the authors query, return early.
	if ( empty( $prepared_args['who'] ) || 'authors' !== $prepared_args['who'] ) {
		return $prepared_args;
	}

	// Get the referer so we can look for the download post type by post ID.
	$referer = wp_parse_url( wp_get_referer() );

	if ( empty( $referer['query'] ) ) {
		return $prepared_args;
	}

	$post_id = (int) filter_var( $referer['query'], FILTER_SANITIZE_NUMBER_INT );
	if ( empty( $post_id ) || 'download' !== get_post_type( $post_id ) ) {
		return $prepared_args;
	}

	$roles = new WP_Roles();
	$who   = array();
	foreach ( $roles->role_objects as $role ) {
		if ( array_key_exists( 'edit_products', $role->capabilities ) && ! empty( $role->capabilities['edit_product'] ) ) {
			$who[] = $role->name;
			continue;
		}
	}
	if ( ! empty( $who ) ) {
		unset( $prepared_args['who'] );
		$prepared_args['role__in'] = $who;
	}

	return $prepared_args;
}
add_filter( 'rest_user_query', 'edd_add_custom_roles_rest_user_query', 15, 2 );
