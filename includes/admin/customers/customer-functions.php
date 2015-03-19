<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

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
		'delete'    => 'edd_customers_delete_view',
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
function edd_register_default_customer_tabs( $tabs ) {

	$default_tabs = array(
		'overview' => array( 'dashicon' => 'dashicons-admin-users', 'title' => __( 'Customer Profile', 'edd' ) ),
		'notes'    => array( 'dashicon' => 'dashicons-admin-comments', 'title' => __( 'Customer Notes', 'edd' ) )
	);

	return array_merge( $tabs, $default_tabs );
}
add_filter( 'edd_customer_tabs', 'edd_register_default_customer_tabs', 1, 1 );

/**
 * Register the Delete icon as late as possible so it's at the bottom
 *
 * @since  2.3.1
 * @param  array $tabs An array of existing tabs
 * @return array       The altered list of tabs, with 'delete' at the bottom
 */
function edd_register_delete_customer_tab( $tabs ) {

	$tabs['delete'] = array( 'dashicon' => 'dashicons-trash', 'title' => __( 'Delete Customer', 'edd' ) );

	return $tabs;
}
add_filter( 'edd_customer_tabs', 'edd_register_delete_customer_tab', PHP_INT_MAX, 1 );
