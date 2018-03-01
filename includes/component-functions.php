<?php

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

	return isset( EDD()->component[ $name ] )
		? EDD()->components[ $name ]
		: false;
}
