<?php
/**
 * Payment actions.
 *
 * @package EDD_Stripe
 * @since   2.7.0
 */

/**
 * If regional support is enabled, check if the card name field is required.
 */
function edds_maybe_disable_card_name() {
	// We no longer need a card name field for some regions, so remove the requirement if it's not needed.
	if ( false === edd_stripe()->has_regional_support() || false === edd_stripe()->regional_support->requires_card_name ) {
		add_filter(
			'edd_purchase_form_required_fields',
			function ( $required_fields ) {
				unset( $required_fields['card_name'] );
				return $required_fields;
			}
		);
		remove_action( 'edd_checkout_error_checks', 'edds_process_post_data' );
	}
}
add_action( 'edd_pre_process_purchase', 'edds_maybe_disable_card_name' );

/**
 * Starts the process of completing a purchase with Stripe.
 *
 * Generates an intent that can require user authorization before proceeding.
 *
 * @link https://stripe.com/docs/payments/intents
 * @since 2.7.0
 * @since 3.3.5 Updated to use the new EDD\Gateways\Stripe\Checkout\Form class.
 *
 * @param array $purchase_data The purchase data.
 * @throws \EDD_Stripe_Gateway_Exception If an error occurs during the purchase process.
 */
function edds_process_purchase_form( $purchase_data ) {
	$form = new EDD\Gateways\Stripe\Checkout\Form( $purchase_data );
	$form->process();
}
add_action( 'edd_gateway_stripe', 'edds_process_purchase_form' );

/**
 * Create an \EDD\Orders\Order.
 *
 * @since 2.9.0
 * @since 3.3.5 Updated to use the new EDD\Gateways\Stripe\Checkout\Complete class.
 */
function edds_create_and_complete_order() {
	$complete = new EDD\Gateways\Stripe\Checkout\Complete();
	$complete->process();
}
add_action( 'wp_ajax_edds_create_and_complete_order', 'edds_create_and_complete_order' );
add_action( 'wp_ajax_nopriv_edds_create_and_complete_order', 'edds_create_and_complete_order' );

/**
 * Uptick the rate limit card error count when a failure happens.
 *
 * @since 2.9.0
 */
function edds_payment_elements_rate_limit_tick() {
	// Increase the card error count.
	edd_stripe()->rate_limiting->increment_card_error_count();

	wp_send_json_success(
		array(
			'is_at_limit' => edd_stripe()->rate_limiting->has_hit_card_error_limit(),
			'message'     => edd_stripe()->rate_limiting->get_rate_limit_error_message(),
		)
	);
}
add_action( 'wp_ajax_edds_payment_elements_rate_limit_tick', 'edds_payment_elements_rate_limit_tick' );
add_action( 'wp_ajax_nopriv_edds_payment_elements_rate_limit_tick', 'edds_payment_elements_rate_limit_tick' );

/**
 * Generates a description based on the cart details.
 *
 * @param array $cart_details {
 *
 * }
 * @return string
 */
function edds_get_payment_description( $cart_details ) {
	$purchase_summary = '';

	if ( is_array( $cart_details ) && ! empty( $cart_details ) ) {
		foreach ( $cart_details as $item ) {
			$purchase_summary .= $item['name'];
			$price_id          = isset( $item['item_number']['options']['price_id'] )
				? absint( $item['item_number']['options']['price_id'] )
				: false;

			if ( false !== $price_id ) {
				$purchase_summary .= ' - ' . edd_get_price_option_name( $item['id'], $item['item_number']['options']['price_id'] );
			}

			$purchase_summary .= ', ';
		}

		$purchase_summary = rtrim( $purchase_summary, ', ' );
	}

	// Stripe has a maximum of 999 characters in the charge description.
	$purchase_summary = substr( $purchase_summary, 0, 1000 );

	return html_entity_decode( $purchase_summary, ENT_COMPAT, 'UTF-8' );
}

add_action(
	'init',
	function () {
		global $edd_recurring_stripe;
		if ( ! $edd_recurring_stripe ) {
			return;
		}

		/**
		 * With card/Link payments, the order creation and completion hooks fire in the same request.
		 * With other payment methods, the order can be created before the payment method is attached
		 * to the customer, which prevents Stripe from being able to create the subscription.
		 *
		 * @since 3.3.5
		 */
		remove_action( 'edds_order_created', array( $edd_recurring_stripe, 'process_purchase_form' ), 20, 2 );
		remove_action( 'edds_payment_complete', array( $edd_recurring_stripe, 'complete_subscriptions' ) );
		add_action(
			'edds_order_complete',
			array( EDD\Gateways\Stripe\Checkout\Recurring::class, 'process' ),
			20,
			2
		);
		add_action(
			'edds_stripe_event_charge.succeeded',
			array( EDD\Gateways\Stripe\Checkout\Recurring::class, 'activate_subscriptions' )
		);
	},
	100
);
