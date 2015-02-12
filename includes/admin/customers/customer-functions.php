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
