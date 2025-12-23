<?php
/**
 * Validate Trait
 *
 * Holds the methods for validating the PayPal IPN.
 *
 * @package    EDD\Gateways\PayPal\Traits
 * @copyright  Copyright (c) 2025, Sandhills Development, LLC
 * @license    https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      3.6.3
 */

namespace EDD\Gateways\PayPal\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Validate Trait
 *
 * Holds the methods for validating the PayPal IPN.
 *
 * @since 3.6.3
 */
trait Validate {

	/**
	 * Validates IPN data beyond PayPal's verification.
	 * These checks MUST pass even if PayPal verification is unavailable.
	 *
	 * @since 3.6.3
	 * @return true
	 * @throws \Exception If validation fails.
	 */
	private function validate_ipn_data() {
		// 1. Validate transaction ID format (basic sanity check)
		if ( ! empty( $this->transaction_id ) && ! preg_match( '/^[A-Z0-9]{17}$/', $this->transaction_id ) ) {
			// PayPal transaction IDs are typically 17 alphanumeric characters.
			$this->debug_log( 'Invalid transaction ID format: ' . $this->transaction_id );
			throw new \Exception( 'Invalid transaction ID format' );
		}

		// 2. Validate currency code format
		if ( ! empty( $this->currency_code ) && ! preg_match( '/^[A-Z]{3}$/', $this->currency_code ) ) {
			$this->debug_log( 'Invalid currency code format: ' . $this->currency_code );
			throw new \Exception( 'Invalid currency code' );
		}

		// 3. Validate timestamp freshness (prevent old IPN replay)
		if ( ! empty( $this->posted['payment_date'] ) ) {
			$payment_time = strtotime( $this->posted['payment_date'] );
			$current_time = time();
			$age_hours    = ( $current_time - $payment_time ) / HOUR_IN_SECONDS;

			// Flag if IPN is older than 96 hours (still process, but log).
			if ( $age_hours > 96 ) {
				$this->debug_log( 'WARNING: IPN is ' . round( $age_hours, 2 ) . ' hours old. Potential replay attack.' );
				edd_record_gateway_error(
					__( 'IPN Warning', 'easy-digital-downloads' ),
					/* translators: %1$s: Age in hours; %2$s: Transaction ID */
					sprintf( __( 'Processing old IPN (%1$s hours old). Transaction ID: %2$s', 'easy-digital-downloads' ), round( $age_hours, 2 ), $this->transaction_id )
				);
			}
		}

		return true;
	}

	/**
	 * Validates subscription amount against expected amount.
	 *
	 * @since 3.6.3
	 * @param \EDD_Subscription $subscription The subscription object.
	 * @param float             $ipn_amount The amount from the IPN.
	 * @return true
	 * @throws \Exception If validation fails.
	 */
	private function validate_subscription_amount( $subscription, $ipn_amount ) {
		// Get the subscription amount.
		$expected_amount = $subscription->recurring_amount;

		// Allow for small floating point differences (2 cents).
		$tolerance  = 0.02;
		$difference = abs( $expected_amount - $ipn_amount );

		if ( $difference > $tolerance ) {
			$this->debug_log(
				sprintf(
					'Amount mismatch for subscription %d. Expected: %s, Received: %s, Difference: %s',
					$subscription->id,
					$expected_amount,
					$ipn_amount,
					$difference
				)
			);

			edd_record_gateway_error(
				__( 'IPN Amount Mismatch', 'easy-digital-downloads' ),
				sprintf(
					/* translators: %1$d: Subscription ID; %2$s: Expected amount; %3$s: Received amount; %4$s: Transaction ID */
					__( 'Subscription %1$d amount mismatch. Expected: %2$s, Received: %3$s. Transaction ID: %4$s', 'easy-digital-downloads' ),
					$subscription->id,
					edd_currency_filter( edd_format_amount( $expected_amount ), $this->currency_code ),
					edd_currency_filter( edd_format_amount( $ipn_amount ), $this->currency_code ),
					$this->transaction_id
				)
			);

			// Add note to subscription.
			$subscription->add_note(
				sprintf(
					/* translators: %1$s: Expected amount; %2$s: Received amount; %3$s: Transaction ID */
					__( 'IPN amount mismatch detected. Expected: %1$s, Received: %2$s. Transaction not processed. Transaction ID: %3$s', 'easy-digital-downloads' ),
					edd_currency_filter( edd_format_amount( $expected_amount ), $this->currency_code ),
					edd_currency_filter( edd_format_amount( $ipn_amount ), $this->currency_code ),
					$this->transaction_id
				)
			);

			throw new \Exception( 'Amount validation failed' );
		}

		return true;
	}
}
