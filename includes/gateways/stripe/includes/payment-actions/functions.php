<?php

/**
 * Sets up a \Stripe\Customer object based on the current purchase data.
 *
 * @param array $purchase_data {
 *
 * }
 * @return \Stripe\Customer|false $customer Stripe Customer if one is created or false on error.
 */
function edds_checkout_setup_customer( $purchase_data ) {
	$customer           = false;
	$stripe_customer_id = '';
	if ( is_user_logged_in() ) {
		$stripe_customer_id = edds_get_stripe_customer_id( get_current_user_id() );
	}
	if ( empty( $stripe_customer_id ) ) {
		// No customer ID found, let's look one up based on the email.
		$stripe_customer_id = edds_get_stripe_customer_id( $purchase_data['user_email'], false );
	}

	$customer_name = '';
	if ( ! empty( $purchase_data['user_info']['first_name'] ) ) {
		$customer_name .= sanitize_text_field( $purchase_data['user_info']['first_name'] );
	}

	if ( ! empty( $purchase_data['user_info']['last_name'] ) ) {
		$customer_name .= ' ' . sanitize_text_field( $purchase_data['user_info']['last_name'] );
	}

	$customer_args = array(
		'email'       => $purchase_data['user_email'],
		'description' => $purchase_data['user_email'],
		'name'        => $customer_name,
	);

	/**
	 * Filters the arguments used to create a Customer in Stripe.
	 *
	 * @since unknown
	 *
	 * @param array $customer_args {
	 *   Arguments to create a Stripe Customer.
	 *
	 *   @link https://stripe.com/docs/api/customers/create
	 * }
	 * @param array $purchase_data {
	 *   Cart purchase data if in the checkout context. Empty otherwise.
	 * }
	 */
	$customer_args = apply_filters( 'edds_create_customer_args', $customer_args, $purchase_data );
	$customer      = edds_get_stripe_customer( $stripe_customer_id, $customer_args );

	return $customer;
}

/**
 * If selected, refunds a charge in Stripe when creating a new refund record.
 * This handles refunds in EDD 3.0+. For EDD 2.x see `edd_stripe_process_refund()`
 * @see edd_stripe_process_refund()
 *
 * @since 2.8.7
 *
 * @param int  $order_id     ID of the order we're processing a refund for.
 * @param int  $refund_id    ID of the newly created refund record.
 * @param bool $all_refunded Whether or not this was a full refund.
 */
function edd_stripe_maybe_refund_charge( $order_id, $refund_id, $all_refunded ) {
	if ( ! current_user_can( 'edit_shop_payments', $order_id ) ) {
		return;
	}

	if ( empty( $_POST['data'] ) ) {
		return;
	}

	$order = edd_get_order( $order_id );
	if ( empty( $order->gateway ) || 'stripe' !== $order->gateway ) {
		return;
	}

	edd_debug_log( sprintf( 'Stripe - Maybe processing refund for order #%d.', $order_id ) );

	// Get our data out of the serialized string.
	parse_str( $_POST['data'], $form_data );

	if ( empty( $form_data['edd-stripe-refund'] ) ) {
		edd_debug_log( 'Stripe - Exiting refund process, as checkbox was not selected.' );

		edd_add_note( array(
			'object_id'   => $order_id,
			'object_type' => 'order',
			'user_id'     => is_admin() ? get_current_user_id() : 0,
			'content'     => __( 'Charge not refunded in Stripe, as checkbox was not selected.', 'easy-digital-downloads' )
		) );

		return;
	}

	edd_debug_log( 'Stripe - Refund checkbox was selected, proceeding to refund charge.' );

	$refund = edd_get_order( $refund_id );
	if ( empty( $refund->total ) ) {
		edd_debug_log( sprintf(
			'Stripe - Exiting refund for order #%d - refund total is empty.',
			$order_id
		) );

		return;
	}

	try {
		edd_refund_stripe_purchase( $order, $refund );
	} catch ( \Exception $e ) {
		edd_debug_log( sprintf( 'Exception thrown while refunding order #%d. Message: %s', $order_id, $e->getMessage() ) );
	}
}
add_action( 'edd_refund_order', 'edd_stripe_maybe_refund_charge', 10, 3 );
