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

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Register a new EDD component (customer, adjustment, order, etc...)
 *
 * @since 3.0
 *
 * @param string $name Component name.
 * @param array  $args Component arguments.
 */
function edd_register_component( $name = '', $args = array() ) {

	// Sanitize the component name.
	$name = sanitize_key( $name );

	// Bail if name or args are empty.
	if ( empty( $name ) || empty( $args ) ) {
		return;
	}

	// Parse arguments.
	$r = wp_parse_args(
		$args,
		array(
			'name'   => $name,
			'schema' => '\\EDD\\Database\\Schema',
			'table'  => '\\EDD\\Database\\Table',
			'query'  => '\\EDD\\Database\\Query',
			'object' => '\\EDD\\Database\\Row',
			'meta'   => false,
		)
	);

	// Set up the component.
	EDD()->components[ $name ] = new EDD\Component( $r );

	// Component registered.
	do_action( 'edd_registered_component', $name, $r, $args );
}

/**
 * Get an EDD Component object
 *
 * @since 3.0
 * @param string $name Component name.
 *
 * @return EDD\Component|false False if not exists, EDD\Component if exists
 */
function edd_get_component( $name = '' ) {
	$name = sanitize_key( $name );

	// Return component if exists, or false.
	return isset( EDD()->components[ $name ] )
		? EDD()->components[ $name ]
		: false;
}

/**
 * Get an EDD Component interface
 *
 * @since 3.0
 * @param string $component           Component name.
 * @param string $component_interface Interface name.
 *
 * @return mixed False if not exists, EDD Component interface if exists
 */
function edd_get_component_interface( $component = '', $component_interface = '' ) {

	// Get component.
	$c = edd_get_component( $component );

	// Bail if no component.
	if ( empty( $c ) ) {
		return $c;
	}

	// Return interface, or false if not exists.
	return $c->get_interface( $component_interface );
}

/**
 * Setup all EDD components.
 *
 * @since 3.0
 */
function edd_setup_components() {
	static $setup = false;

	// Never register components more than 1 time per request.
	if ( false !== $setup ) {
		return;
	}

	EDD\Database\Components::register();

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

	// Get the components.
	$components = EDD()->components;

	// Bail if no components setup yet.
	if ( empty( $components ) ) {
		return;
	}

	// Drop all component tables.
	foreach ( $components as $component ) {

		// Objects.
		$object = $component->get_interface( 'table' );
		if ( $object instanceof \EDD\Database\Table && ! $object->exists() ) {
			$object->install();
		}

		// Meta.
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

	// Get the components.
	$components = EDD()->components;

	// Bail if no components setup yet.
	if ( empty( $components ) ) {
		return;
	}

	// Drop all component tables.
	foreach ( $components as $component ) {

		// Objects.
		$object = $component->get_interface( 'table' );
		if ( $object instanceof \EDD\Database\Table && $object->exists() ) {
			$object->uninstall();
		}

		// Meta.
		$meta = $component->get_interface( 'meta' );
		if ( $meta instanceof \EDD\Database\Table && $meta->exists() ) {
			$meta->uninstall();
		}
	}
}
