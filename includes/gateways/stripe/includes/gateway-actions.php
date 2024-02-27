<?php

/**
 * Register the statement_descriptor email tag.
 *
 * @since 2.6
 * @since 3.2.8 We're going to pull this directly from the Stripe charge, now that it needs to be managed there.
 * @return void
 */
function edd_stripe_register_email_tags() {
	edd_add_email_tag(
		'stripe_statement_descriptor',
		__( 'Outputs a line stating what charges will appear as on customer\'s credit card statements.', 'easy-digital-downloads' ),
		'edd_stripe_statement_descriptor_template_tag',
		__( 'Statement Descriptor', 'easy-digital-downloads' )
	);
}
add_action( 'edd_add_email_tags', 'edd_stripe_register_email_tags' );

/**
 * Swap the {statement_descriptor} email tag with the string from the option
 *
 * @since 2.6
 * @since 3.2.8 Updated to pull directly from Stripe's API, so it is the specific statement descriptor for their charge.
 *
 * @param int $order_id The ID of the order.
 *
 * @return string $statement_descriptor The statement descriptor.
 */
function edd_stripe_statement_descriptor_template_tag( $order_id ) {
	$transaction = edd_get_order_transaction_by( 'object_id', $order_id );

	if ( empty( $transaction ) || empty( $transaction->transaction_id ) || 'stripe' !== $transaction->gateway ) {
		return '';
	}

	// Now get the transaction from Stripe so we can look for the statement descriptor.
	try {
		$stripe_transaction = edds_api_request( 'charge', 'retrieve', $transaction->transaction_id );
	} catch ( Exception $e ) {
		return '';
	}

	if ( is_wp_error( $stripe_transaction ) || empty( $stripe_transaction->calculated_statement_descriptor ) ) {
		return '';
	}

	// If you want to filter this, use the %s to define where you want the actual statement descriptor to show in your message.
	$email_tag_output = apply_filters(
		'edd_stripe_statement_descriptor_email_tag',
		/* translators: %s is the statement descriptor */
		__( 'Charges will appear on your card statement as %s', 'easy-digital-downloads' )
	);

	return sprintf( $email_tag_output, $stripe_transaction->calculated_statement_descriptor );
}
