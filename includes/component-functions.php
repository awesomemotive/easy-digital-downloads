<?php
/**
 * Component Functions
 *
 * This file includes functions for interacting with EDD components. An EDD
 * component is comprised of:
 *
 * - Database table/schema/query
 * - Object interface
 * - Optional meta-data
 *
 * Some examples of EDD components are:
 *
 * - Customer
 * - Discount
 * - Order
 * - Order Item
 * - Note
 * - Log
 *
 * Add-ons and third party plugins are welcome to register their own component
 * in exactly the same way that EDD does internally.
 *
 * @package     EDD
 * @subpackage  Functions/Components
 * @since       3.0.0
*/

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Register a new EDD component (customer, discount, order, etc...)
 *
 * @since 3.0.0
 *
 * @param string $name
 * @param array  $args
 */
function edd_register_component( $name = '', $args = array() ) {

	// Sanitize the component name
	$name = sanitize_key( $name );

	// Bail if name or args are empty
	if ( empty( $name ) || empty( $args ) ) {
		return;
	}

	// Parse arguments
	$r = wp_parse_args( $args, array(
		'name'   => $name,
		'schema' => '\\EDD\\Database\\Schemas\\Base',
		'table'  => '\\EDD\\Database\\Tables\\Base',
		'query'  => '\\EDD\\Database\\Queries\\Base',
		'object' => '\\EDD\\Database\\Objects\\Base',
		'meta'   => false
	) );

	// Setup the component
	EDD()->components[ $name ] = new EDD\Component( $r );
}

/**
 * Get an EDD Component object
 *
 * @since 3.0.0
 * @param string $name
 *
 * @return mixed False if not exists, EDD_Component if exists
 */
function edd_get_component( $name = '' ) {
	$name = sanitize_key( $name );

	// Return component if exists, or false
	return isset( EDD()->components[ $name ] )
		? EDD()->components[ $name ]
		: false;
}

/**
 * Get an EDD Component interface
 *
 * @since 3.0.0
 * @param string $component
 * @param string $interface
 *
 * @return mixed False if not exists, EDD_Component interface if exists
 */
function edd_get_component_interface( $component = '', $interface = '' ) {

	// Get component
	$c = edd_get_component( $component );

	// Bail if no component
	if ( empty( $c ) ) {
		return $c;
	}

	// Return interface, or false if not exists
	return $c->get_interface( $interface );
}

/**
 * Setup all EDD components
 *
 * @since 3.0.0
 */
function edd_setup_components() {
	static $setup = false;

	// Never register components more than 1 time per request
	if ( false !== $setup ) {
		return;
	}

	// Register Customer
	edd_register_component( 'customer', array(
		'schema' => '\\EDD\\Database\\Schema\\Customers',
		'table'  => '\\EDD\\Database\\Tables\\Customers',
		'meta'   => '\\EDD\\Database\\Tables\\Customer_Meta',
		'query'  => '\\EDD\\Database\\Queries\\Customer',
		'object' => 'EDD_Customer'
	) );

	// Register discount
	edd_register_component( 'discount', array(
		'schema' => '\\EDD\\Database\\Schema\\Discounts',
		'table'  => '\\EDD\\Database\\Tables\\Discounts',
		'meta'   => '\\EDD\\Database\\Tables\\Discount_Meta',
		'query'  => '\\EDD\\Database\\Queries\\Discount',
		'object' => 'EDD_Discount'
	) );

	// Register note
	edd_register_component( 'note', array(
		'schema' => '\\EDD\\Database\\Schema\\Notes',
		'table'  => '\\EDD\\Database\\Tables\\Notes',
		'meta'   => '\\EDD\\Database\\Tables\\Note_Meta',
		'query'  => '\\EDD\\Database\\Queries\\Note',
		'object' => '\\EDD\\Notes\\Note'
	) );

	// Register order
	edd_register_component( 'order', array(
		'schema' => '\\EDD\\Database\\Schema\\Orders',
		'table'  => '\\EDD\\Database\\Tables\\Orders',
		'meta'   => '\\EDD\\Database\\Tables\\Order_Meta',
		'query'  => '\\EDD\\Database\\Queries\\Order',
		'object' => '\\EDD\\Orders\\Order'
	) );

	// Register order item
	edd_register_component( 'order_item', array(
		'schema' => '\\EDD\\Database\\Schema\\Order_Items',
		'table'  => '\\EDD\\Database\\Tables\\Order_Items',
		'meta'   => '\\EDD\\Database\\Tables\\Order_Item_Meta',
		'query'  => '\\EDD\\Database\\Queries\\Order_Item',
		'object' => '\\EDD\\Orders\\Order_Item'
	) );

	// Register log
	edd_register_component( 'log', array(
		'schema' => '\\EDD\\Database\\Schema\\Logs',
		'table'  => '\\EDD\\Database\\Tables\\Logs',
		'meta'   => '\\EDD\\Database\\Tables\\Log_Meta',
		'query'  => '\\EDD\\Database\\Queries\\Log',
		'object' => '\\EDD\\Logs\\Log'
	) );

	// Register log api request
	edd_register_component( 'log_api_request', array(
		'schema' => '\\EDD\\Database\\Schema\\Logs_Api_Requests',
		'table'  => '\\EDD\\Database\\Tables\\Logs_Api_Requests',
		'query'  => '\\EDD\\Database\\Queries\\Log_Api_Request',
		'object' => '\\EDD\\Logs\\Api_Request_Log',
		'meta'   => false
	) );

	// Register log api request
	edd_register_component( 'log_file_download', array(
		'schema' => '\\EDD\\Database\\Schema\\Logs_File_Downloads',
		'table'  => '\\EDD\\Database\\Tables\\Logs_File_Downloads',
		'query'  => '\\EDD\\Database\\Queries\\Log_File_Download',
		'object' => '\\EDD\\Logs\\File_Download_Log',
		'meta'   => false
	) );

	// Set the locally static setup var
	$setup = true;

	// Action to allow third party components to be setup
	do_action( 'edd_setup_components' );
}
