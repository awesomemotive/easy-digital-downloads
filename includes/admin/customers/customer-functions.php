<?php

/**
 * Get the customer notes for a customer ID
 *
 * @since  2.3
 * @param  int $customer_id The Customer ID to request notes for
 * @return object               An object of comment objects, empty object if none found
 */
function edd_get_customer_notes( $customer_id ) {

	$default_args = array(
		'meta_key'   => 'edd_customer_id',
		'meta_value' => $customer_id
	);

	$args = apply_filters( 'edd_customer_notes_query_args', $default_args, $customer_id );

	do_action( 'edd_get_customer_notes', $customer_id );

	$comments_query = new WP_Comment_Query;
	$customer_notes = $comments_query->query( $args );

	return $customer_notes;
}

/**
 * Register a view for the single customer view
 *
 * @since  2.3
 * @param  array $views An array of existing views
 * @return array        The altered list of views
 */
function edd_register_default_customer_views( $views ) {

	$default_views = array(
		'overview'  => 'edd_customers_view',
		'delete'    => 'edd_customers_delete',
		'notes'     => 'edd_customer_notes_view'
	);

	return array_merge( $views, $default_views );

}
add_filter( 'edd_customer_views', 'edd_register_default_customer_views', 1, 1 );

/**
 * Register a tab for the single customer view
 *
 * @since  2.3
 * @param  array $tabs An array of existing tabs
 * @return array       The altered list of tabs
 */
function edd_register_default_cutomer_tabs( $tabs ) {

	$default_tabs = array(
		'overview' => array( 'dashicon' => 'dashicons-admin-users', 'title' => __( 'Customer Profile', 'edd' ) ),
		'notes'    => array( 'dashicon' => 'dashicons-admin-comments', 'title' => __( 'Customer Notes', 'edd' ) )
	);

	return array_merge( $tabs, $default_tabs );
}
add_filter( 'edd_customer_tabs', 'edd_register_default_cutomer_tabs', 1, 1 );
