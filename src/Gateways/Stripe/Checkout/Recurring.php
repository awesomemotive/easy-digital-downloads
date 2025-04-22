<?php
/**
 * Recurring class.
 *
 * @package EDD\Gateways\Stripe\Checkout
 */

namespace EDD\Gateways\Stripe\Checkout;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Gateways\Stripe\Intents;

/**
 * Recurring class.
 */
class Recurring {

	/**
	 * Process the purchase form.
	 *
	 * @param \EDD\Orders\Order $order          EDD Order Object.
	 * @param string            $intent_id The PaymentIntent ID.
	 */
	public static function process( $order, $intent_id ) {
		global $edd_recurring_stripe;
		if ( ! $edd_recurring_stripe ) {
			return;
		}

		$purchase_data = edd_get_purchase_session();
		if ( ! $purchase_data || ! edd_recurring()->is_purchase_recurring( $purchase_data ) ) {
			return;
		}

		$intent = Intents::get(
			$intent_id,
			array(
				'expand' => array( 'latest_charge' ),
			)
		);
		if ( ! $intent ) {
			return;
		}

		self::maybe_filter_subscription_args( $intent, $order );

		$edd_recurring_stripe->process_purchase_form( $order, $intent );
		self::activate_subscriptions( $order );
	}

	/**
	 * Complete the EDD subscriptions.
	 *
	 * @since 3.3.5
	 * @param \EDD\Orders\Order $order The order object.
	 */
	public static function activate_subscriptions( $order ) {
		if ( 'complete' !== $order->status ) {
			return;
		}
		if ( ! class_exists( '\\EDD_Subscriptions_DB' ) ) {
			return;
		}
		$subscription_db = new \EDD_Subscriptions_DB();
		$subscriptions   = $subscription_db->get_subscriptions(
			array(
				'parent_payment_id' => $order->id,
				'status'            => 'pending',
			)
		);

		if ( empty( $subscriptions ) ) {
			return;
		}

		foreach ( $subscriptions as $subscription ) {
			$subscription->update(
				array(
					'status' => empty( $subscription->trial_period ) ? 'active' : 'trialling',
				)
			);
			$subscription->add_note( __( 'Subscription activated via EDD Stripe Checkout.', 'easy-digital-downloads' ) );
		}
	}

	/**
	 * Maybe filter the subscription args.
	 *
	 * @since 3.3.5
	 * @param \EDD\Vendor\Stripe\PaymentIntent $intent The PaymentIntent object.
	 * @param \EDD\Orders\Order                $order  The order object.
	 */
	private static function maybe_filter_subscription_args( $intent, $order ) {
		if ( empty( $intent->latest_charge ) || ! $intent->latest_charge instanceof \EDD\Vendor\Stripe\Charge ) {
			return;
		}

		$custom_payment_method_id = self::get_custom_payment_method( $intent->latest_charge, $order );
		if ( ! $custom_payment_method_id ) {
			return;
		}

		add_filter(
			'edd_recurring_create_subscription_args',
			function ( $args ) use ( $custom_payment_method_id ) {
				$args['default_payment_method'] = $custom_payment_method_id;

				return $args;
			}
		);
	}

	/**
	 * Try to get the custom payment method that was attached to the customer.
	 *
	 * @since 3.3.5
	 * @param \EDD\Vendor\Stripe\Charge $charge The charge object.
	 * @param \EDD\Orders\Order         $order  The order object.
	 * @return false|string
	 */
	private static function get_custom_payment_method( $charge, $order ) {
		if ( empty( $charge->payment_method_details ) ) {
			return false;
		}
		$payment_method_type = edd_get_order_meta( $order->id, 'stripe_payment_method_type', true );
		if ( in_array( $payment_method_type, array( 'card', 'link' ), true ) ) {
			return false;
		}

		if ( empty( $charge->payment_method_details->{$payment_method_type} ) ) {
			return false;
		}

		$payment_method = false;
		// Try to get the custom payment method ID from the charge.
		if ( ! empty( $charge->payment_method_details->{$payment_method_type}->generated_sepa_debit ) ) {
			return $charge->payment_method_details->{$payment_method_type}->generated_sepa_debit;
		}

		return $payment_method;
	}
}
