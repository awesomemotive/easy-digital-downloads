<?php

/**
 * Get the customer notes for a customer ID
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
