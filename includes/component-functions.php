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
		'schema' => 'EDD_DB_Schema',
		'table'  => 'EDD_DB_Table',
		'query'  => 'EDD_DB_Query',
		'object' => 'EDD_DB_Object',
		'meta'   => false
	) );

	// Setup the component
	EDD()->components[ $name ] = new EDD_Component( $r );
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
		'schema' => 'EDD_DB_Schema_Customers',
		'table'  => 'EDD_DB_Table_Customers',
		'meta'   => 'EDD_DB_Table_Customer_Meta',
		'query'  => 'EDD_Customer_Query',
		'object' => 'EDD_Customer'
	) );

	// Register discount
	edd_register_component( 'discount', array(
		'schema' => 'EDD_DB_Schema_Discounts',
		'table'  => 'EDD_DB_Table_Discounts',
		'meta'   => 'EDD_DB_Table_Discount_Meta',
		'query'  => 'EDD_Discount_Query',
		'object' => 'EDD_Discount'
	) );

	// Register note
	edd_register_component( 'note', array(
		'schema' => 'EDD_DB_Schema_Notes',
		'table'  => 'EDD_DB_Table_Notes',
		'meta'   => 'EDD_DB_Table_Note_Meta',
		'query'  => 'EDD_Note_Query',
		'object' => 'EDD_Note'
	) );

	// Register order
	edd_register_component( 'order', array(
		'schema' => 'EDD_DB_Schema_Orders',
		'table'  => 'EDD_DB_Table_Orders',
		'meta'   => 'EDD_DB_Table_Order_Meta',
		'query'  => 'EDD_Order_Query',
		'object' => 'EDD_Order'
	) );

	// Register order item
	edd_register_component( 'order_item', array(
		'schema' => 'EDD_DB_Schema_Order_Items',
		'table'  => 'EDD_DB_Table_Order_Items',
		'meta'   => 'EDD_DB_Table_Order_Item_Meta',
		'query'  => 'EDD_Order_Item_Query',
		'object' => 'EDD_Order_Item'
	) );

	// Register log
	edd_register_component( 'log', array(
		'schema' => 'EDD_DB_Schema_Logs',
		'table'  => 'EDD_DB_Table_Logs',
		'meta'   => 'EDD_DB_Table_Log_Meta',
		'query'  => 'EDD_Log_Query',
		'object' => 'EDD_Log'
	) );

	// Register log api request
	edd_register_component( 'log_api_request', array(
		'schema' => 'EDD_DB_Schema_Logs_Api_Requests',
		'table'  => 'EDD_DB_Table_Logs_Api_Requests',
		'query'  => 'EDD_Log_Api_Request_Query',
		'object' => 'EDD_Log_Api_Request',
		'meta'   => false
	) );

	// Register log api request
	edd_register_component( 'log_file_download', array(
		'schema' => 'EDD_DB_Schema_Logs_File_Downloads',
		'table'  => 'EDD_DB_Table_Logs_File_Downloads',
		'query'  => 'EDD_Log_File_Download_Query',
		'object' => 'EDD_Log_File_Download',
		'meta'   => false
	) );

	// Set the locally static setup var
	$setup = true;

	// Action to allow third party components to be setup
	do_action( 'edd_setup_components' );
}
