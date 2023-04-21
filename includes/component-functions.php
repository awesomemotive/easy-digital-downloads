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
 * - Adjustment
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
 * @since       3.0
*/

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Register a new EDD component (customer, adjustment, order, etc...)
 *
 * @since 3.0
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
		'schema' => '\\EDD\\Database\\Schema',
		'table'  => '\\EDD\\Database\\Table',
		'query'  => '\\EDD\\Database\\Query',
		'object' => '\\EDD\\Database\\Row',
		'meta'   => false
	) );

	// Setup the component
	EDD()->components[ $name ] = new EDD\Component( $r );

	// Component registered
	do_action( 'edd_registered_component', $name, $r, $args );
}

/**
 * Get an EDD Component object
 *
 * @since 3.0
 * @param string $name
 *
 * @return EDD\Component|false False if not exists, EDD\Component if exists
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
 * @since 3.0
 * @param string $component
 * @param string $interface
 *
 * @return mixed False if not exists, EDD Component interface if exists
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
 * @since 3.0
 */
function edd_setup_components() {
	static $setup = false;

	// Never register components more than 1 time per request
	if ( false !== $setup ) {
		return;
	}

	// Register customer.
	edd_register_component( 'customer', array(
		'schema' => '\\EDD\\Database\\Schemas\\Customers',
		'table'  => '\\EDD\\Database\\Tables\\Customers',
		'meta'   => '\\EDD\\Database\\Tables\\Customer_Meta',
		'query'  => '\\EDD\\Database\\Queries\\Customer',
		'object' => 'EDD_Customer'
	) );

	// Register customer address.
	edd_register_component( 'customer_address', array(
		'schema' => '\\EDD\\Database\\Schemas\\Customer_Addresses',
		'table'  => '\\EDD\\Database\\Tables\\Customer_Addresses',
		'query'  => '\\EDD\\Database\\Queries\\Customer_Address',
		'object' => '\\EDD\\Customers\\Customer_Address',
		'meta'   => false
	) );

	// Register customer email address.
	edd_register_component( 'customer_email_address', array(
		'schema' => '\\EDD\\Database\\Schemas\\Customer_Email_Addresses',
		'table'  => '\\EDD\\Database\\Tables\\Customer_Email_Addresses',
		'query'  => '\\EDD\\Database\\Queries\\Customer_Email_Address',
		'object' => '\\EDD\\Customers\\Customer_Email_Address',
		'meta'   => false
	) );

	// Register adjustment.
	edd_register_component( 'adjustment', array(
		'schema' => '\\EDD\\Database\\Schemas\\Adjustments',
		'table'  => '\\EDD\\Database\\Tables\\Adjustments',
		'meta'   => '\\EDD\\Database\\Tables\\Adjustment_Meta',
		'query'  => '\\EDD\\Database\\Queries\\Adjustment',
		'object' => '\\EDD\\Adjustments\\Adjustment'
	) );

	// Register note.
	edd_register_component( 'note', array(
		'schema' => '\\EDD\\Database\\Schemas\\Notes',
		'table'  => '\\EDD\\Database\\Tables\\Notes',
		'meta'   => '\\EDD\\Database\\Tables\\Note_Meta',
		'query'  => '\\EDD\\Database\\Queries\\Note',
		'object' => '\\EDD\\Notes\\Note'
	) );

	// Register order.
	edd_register_component( 'order', array(
		'schema' => '\\EDD\\Database\\Schemas\\Orders',
		'table'  => '\\EDD\\Database\\Tables\\Orders',
		'meta'   => '\\EDD\\Database\\Tables\\Order_Meta',
		'query'  => '\\EDD\\Database\\Queries\\Order',
		'object' => '\\EDD\\Orders\\Order'
	) );

	// Register order item.
	edd_register_component( 'order_item', array(
		'schema' => '\\EDD\\Database\\Schemas\\Order_Items',
		'table'  => '\\EDD\\Database\\Tables\\Order_Items',
		'meta'   => '\\EDD\\Database\\Tables\\Order_Item_Meta',
		'query'  => '\\EDD\\Database\\Queries\\Order_Item',
		'object' => '\\EDD\\Orders\\Order_Item'
	) );

	// Register order adjustment.
	edd_register_component( 'order_adjustment', array(
		'schema' => '\\EDD\\Database\\Schemas\\Order_Adjustments',
		'table'  => '\\EDD\\Database\\Tables\\Order_Adjustments',
		'meta'   => '\\EDD\\Database\\Tables\\Order_Adjustment_Meta',
		'query'  => '\\EDD\\Database\\Queries\\Order_Adjustment',
		'object' => '\\EDD\\Orders\\Order_Adjustment',
	) );

	// Register order address.
	edd_register_component( 'order_address', array(
		'schema' => '\\EDD\\Database\\Schemas\\Order_Addresses',
		'table'  => '\\EDD\\Database\\Tables\\Order_Addresses',
		'query'  => '\\EDD\\Database\\Queries\\Order_Address',
		'object' => '\\EDD\\Orders\\Order_Address',
		'meta'   => false
	) );

	// Register order transaction.
	edd_register_component( 'order_transaction', array(
		'schema' => '\\EDD\\Database\\Schemas\\Order_Transactions',
		'table'  => '\\EDD\\Database\\Tables\\Order_Transactions',
		'query'  => '\\EDD\\Database\\Queries\\Order_Transaction',
		'object' => '\\EDD\\Orders\\Order_Transaction',
		'meta'   => false
	) );

	// Register log.
	edd_register_component( 'log', array(
		'schema' => '\\EDD\\Database\\Schemas\\Logs',
		'table'  => '\\EDD\\Database\\Tables\\Logs',
		'meta'   => '\\EDD\\Database\\Tables\\Log_Meta',
		'query'  => '\\EDD\\Database\\Queries\\Log',
		'object' => '\\EDD\\Logs\\Log'
	) );

	// Register log API request.
	edd_register_component( 'log_api_request', array(
		'schema' => '\\EDD\\Database\\Schemas\\Logs_Api_Requests',
		'table'  => '\\EDD\\Database\\Tables\\Logs_Api_Requests',
		'meta'   => '\\EDD\\Database\\Tables\\Logs_Api_Request_Meta',
		'query'  => '\\EDD\\Database\\Queries\\Log_Api_Request',
		'object' => '\\EDD\\Logs\\Api_Request_Log',
	) );

	// Register log file download.
	edd_register_component( 'log_file_download', array(
		'schema' => '\\EDD\\Database\\Schemas\\Logs_File_Downloads',
		'table'  => '\\EDD\\Database\\Tables\\Logs_File_Downloads',
		'meta'   => '\\EDD\\Database\\Tables\\Logs_File_Download_Meta',
		'query'  => '\\EDD\\Database\\Queries\\Log_File_Download',
		'object' => '\\EDD\\Logs\\File_Download_Log',
	) );

	edd_register_component( 'notification', array(
		'schema' => '\\EDD\\Database\\Schemas\\Notifications',
		'table'  => '\\EDD\\Database\\Tables\\Notifications',
		'query'  => '\\EDD\\Database\\Queries\\Notification',
		'object' => '\\EDD\\Notifications\\Notification',
		'meta'   => false,
	) );

	// Set the locally static setup var.
	$setup = true;

	// Action to allow third party components to be setup.
	do_action( 'edd_setup_components' );
}

/**
 * Install all component database tables
 *
 * This function installs all database tables used by all components (including
 * third-party and add-ons that use the Component API)
 *
 * This is used by unit tests and tools.
 *
 * @since 3.0
 */
function edd_install_component_database_tables() {

	// Get the components
	$components = EDD()->components;

	// Bail if no components setup yet
	if ( empty( $components ) ) {
		return;
	}

	// Drop all component tables
	foreach ( $components as $component ) {

		// Objects
		$object = $component->get_interface( 'table' );
		if ( $object instanceof \EDD\Database\Table && ! $object->exists() ) {
			$object->install();
		}

		// Meta
		$meta = $component->get_interface( 'meta' );
		if ( $meta instanceof \EDD\Database\Table && ! $meta->exists() ) {
			$meta->install();
		}
	}
}

/**
 * Uninstall all component database tables
 *
 * This function is destructive and disastrous, so do not call it directly
 * unless you fully intend to destroy all data (including third-party add-ons
 * that use the Component API)
 *
 * This is used by unit tests and tools.
 *
 * @since 3.0
 */
function edd_uninstall_component_database_tables() {

	// Get the components
	$components = EDD()->components;

	// Bail if no components setup yet
	if ( empty( $components ) ) {
		return;
	}

	// Drop all component tables
	foreach ( $components as $component ) {

		// Objects
		$object = $component->get_interface( 'table' );
		if ( $object instanceof \EDD\Database\Table && $object->exists() ) {
			$object->uninstall();
		}

		// Meta
		$meta = $component->get_interface( 'meta' );
		if ( $meta instanceof \EDD\Database\Table && $meta->exists() ) {
			$meta->uninstall();
		}
	}
}
