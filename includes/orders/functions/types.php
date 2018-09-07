<?php

/**
 * 
 * @since 3.0
 *
 * @param array $args
 */
function edd_register_order_type( $args = array() ) {
	
}

/**
 * Retrieve the registered order types.
 *
 * @since 3.0
 *
 * @return array
 */
function edd_get_order_types() {
	static $types = null;

	// Avoid reprocessing the labels
	if ( null === $types ) {

		// Filter
		$types = (array) apply_filters( 'edd_get_order_types', array(
			'sale' => array(
				'labels' => array(
					'singular' => __( 'Sale',  'easy-digital-downloads' ),
					'plural'   => __( 'Sales', 'easy-digital-downloads' )
				)
			),
			'refund' => array(
				'labels' => array(
					'singular' => __( 'Refund',  'easy-digital-downloads' ),
					'plural'   => __( 'Refunds', 'easy-digital-downloads' )
				)
			),
			'invoice' => array(
				'labels' => array(
					'singular' => __( 'Invoice',  'easy-digital-downloads' ),
					'plural'   => __( 'Invoices', 'easy-digital-downloads' )
				)
			)
		) );
	}

	// Return
	return (array) $types;
}
