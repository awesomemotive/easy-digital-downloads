<?php
/**
 * Order Type Functions
 *
 * @package     EDD
 * @subpackage  Orders
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Retrieve the registered order types.
 *
 * @since 3.0
 *
 * @return array
 */
function edd_get_order_types() {

	// Get the
	$component = edd_get_component( 'order' );

	// Setup an empty types array
	if ( ! isset( $component->types ) ) {
		$component->types = array();
	}

	// Return types
	return (array) $component->types;
}

/**
 * Register an Order Type.
 *
 * @since 3.0
 *
 * @param array $args
 */
function edd_register_order_type( $type = '', $args = array() ) {

	// Sanitize the type
	$type = sanitize_key( $type );

	// Parse args
	$r = wp_parse_args( $args, array(
		'show_ui' => true,
		'labels'  => array(
			'singular' => '',
			'plural'   => ''
		)
	) );

	// Get the
	$component = edd_get_component( 'order' );

	// Setup an empty types array
	if ( ! isset( $component->types ) ) {
		$component->types = array();
	}

	// Add the order type to the `types` array
	$component->types[ $type ] = $r;
}

/**
 * Register the default Order Types.
 *
 * @since 3.0
 */
function edd_register_default_order_types( $name = '' ) {

	// Bail if not the `order` name
	if ( 'order' !== $name ) {
		return;
	}

	// Sales
	edd_register_order_type( 'sale', array(
		'labels' => array(
			'singular' => __( 'Order',  'easy-digital-downloads' ),
			'plural'   => __( 'Orders', 'easy-digital-downloads' )
		)
	) );

	// Refunds
	edd_register_order_type( 'refund', array(
		'labels' => array(
			'singular' => __( 'Refund',  'easy-digital-downloads' ),
			'plural'   => __( 'Refunds', 'easy-digital-downloads' )
		)
	) );

	// Invoices
	edd_register_order_type( 'invoice', array(
		'show_ui' => false,
		'labels'  => array(
			'singular' => __( 'Invoice',  'easy-digital-downloads' ),
			'plural'   => __( 'Invoices', 'easy-digital-downloads' )
		)
	) );
}
add_action( 'edd_registered_component', 'edd_register_default_order_types' );
