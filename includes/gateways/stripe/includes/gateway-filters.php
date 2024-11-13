<?php

/**
 * Removes Stripe from active gateways if application requirements are not met.
 *
 * @since 2.8.1
 * @deprecated 3.2.0
 * @param array $enabled_gateways Enabled gateways that allow purchasing.
 * @return array
 */
function edds_validate_gateway_requirements( $enabled_gateways ) {
	if ( false === edds_has_met_requirements() ) {
		unset( $enabled_gateways['stripe'] );
	}

	return $enabled_gateways;
}

/**
 * Injects the Stripe token and customer email into the pre-gateway data
 *
 * @since 2.0
 *
 * @param array $purchase_data
 * @return array
 */
function edd_stripe_straight_to_gateway_data( $purchase_data ) {

	$gateways = edd_get_enabled_payment_gateways();

	if ( isset( $gateways['stripe'] ) ) {
		$_REQUEST['edd-gateway']  = 'stripe';
		$purchase_data['gateway'] = 'stripe';
	}

	return $purchase_data;
}
add_filter( 'edd_straight_to_gateway_purchase_data', 'edd_stripe_straight_to_gateway_data' );

/**
 * Process the POST Data for the Credit Card Form, if a token wasn't supplied
 *
 * @since  2.2
 * @return array The credit card data from the $_POST
 */
function edds_process_post_data( $purchase_data ) {
	if ( ! isset( $purchase_data['gateway'] ) || 'stripe' !== $purchase_data['gateway'] ) {
		return;
	}

	$elements_mode = edds_get_elements_mode();
	// These are card-elements validations.
	if ( 'card-elements' === $elements_mode ) {
		if ( isset( $_POST['edd_stripe_existing_card'] ) && 'new' !== $_POST['edd_stripe_existing_card'] ) {
			return;
		}

		// Require a name for new cards.
		if ( ! isset( $_POST['card_name'] ) || strlen( trim( $_POST['card_name'] ) ) === 0 ) {
			edd_set_error( 'no_card_name', __( 'Please enter a name for the credit card.', 'easy-digital-downloads' ) );
		}
	}
}
add_action( 'edd_checkout_error_checks', 'edds_process_post_data' );

/**
 * Retrieves the locale used for Checkout modal window
 *
 * @since  2.5
 * @return string The locale to use
 */
function edds_get_stripe_checkout_locale() {
	return apply_filters( 'edd_stripe_checkout_locale', 'auto' );
}

/**
 * Given a transaction ID, generate a link to the Stripe transaction ID details
 *
 * @since  1.9.1
 * @param  string $transaction_id The Transaction ID
 * @param  int    $payment_id     The payment ID for this transaction
 * @return string                 A link to the Stripe transaction details
 */
function edd_stripe_link_transaction_id( $transaction_id, $payment_id ) {

	$order = edd_get_order( $payment_id );
	$test  = 'test' === $order->mode ? 'test/' : '';

	if ( 'preapproval' === $order->status ) {
		$url = '<a href="https://dashboard.stripe.com/' . esc_attr( $test ) . 'setup_intents/' . esc_attr( $transaction_id ) . '" target="_blank">' . esc_html( $transaction_id ) . '</a>';
	} else {
		$url = '<a href="https://dashboard.stripe.com/' . esc_attr( $test ) . 'payments/' . esc_attr( $transaction_id ) . '" target="_blank">' . esc_html( $transaction_id ) . '</a>';
	}
	return apply_filters( 'edd_stripe_link_payment_details_transaction_id', $url );
}
add_filter( 'edd_payment_details_transaction_id-stripe', 'edd_stripe_link_transaction_id', 10, 2 );

/**
 * Modifies the checkout label for a specific gateway in Easy Digital Downloads Pro.
 *
 * @param string $label The original checkout label.
 * @param string $gateway The gateway being used for the checkout.
 * @param object $order The order object.
 * @return string The modified checkout label.
 */
function edds_gateway_checkout_label( $label, $gateway, $order ) {
	if ( 'stripe' !== $gateway || empty( $order ) ) {
		return $label;
	}

	$type = edd_get_order_meta( $order->id, 'stripe_payment_method_type', true );
	if ( ! $type ) {
		return $label;
	}

	$type_label = EDD\Gateways\Stripe\PaymentMethods::get_label( $type );
	if ( $type_label ) {
		if ( 'edd_gateway_admin_label' === current_filter() ) {
			return $label . ' (' . $type_label . ')';
		}

		return $type_label;
	}

	return $label;
}
add_filter( 'edd_gateway_checkout_label', 'edds_gateway_checkout_label', 10, 3 );
add_filter( 'edd_gateway_admin_label', 'edds_gateway_checkout_label', 10, 3 );


/**
 * When redirecting to the Stripe success screen outside of a purchase session,
 * show the correct content.
 *
 * @since 3.3.5
 * @param string $content
 * @return string
 */
function edds_success_page_content( $content ) {
	ob_start();

	// If the status is set, show the processing template. Likely a bank transfer.
	if ( ! empty( $_GET['status']) ) {
		edd_get_template_part( 'payment', 'processing' );
		edd_empty_cart();
	} else {
		// Authenticated offsite (Cash App, etc).
		edd_get_template_part( 'stripe', 'success' );
	}

	return ob_get_clean();
}
add_filter( 'edd_payment_confirm_stripe', 'edds_success_page_content' );
