<?php
/**
 * Internationalization
 *
 * @package EDD_Stripe
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 2.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns a list of error codes and corresponding localized error messages.
 *
 * @since 2.8.0
 *
 * @return array $error_list List of error codes and corresponding error messages.
 */
function edds_get_localized_error_messages() {
	$generic_different_payment_method  = __( 'There was an error processing your payment. Please try with a different payment method.', 'easy-digital-downloads' );
	$generic_contact_issuer_message    = __( 'There was an error processing your payment. Please contact your card issuer for more information.', 'easy-digital-downloads' );

	$error_list = array(
		'incomplete'                            => __( 'Payment processing cancelled; your order is not yet complete.', 'easy-digital-downloads' ),
		'invalid_number'                        => __( 'The card number is not a valid credit card number.', 'easy-digital-downloads' ),
		'invalid_expiry_month'                  => __( 'The card\'s expiration month is invalid.', 'easy-digital-downloads' ),
		'invalid_expiry_year'                   => __( 'The card\'s expiration year is invalid.', 'easy-digital-downloads' ),
		'invalid_cvc'                           => __( 'The card\'s security code is invalid.', 'easy-digital-downloads' ),
		'incorrect_number'                      => __( 'The card number you provided is incorrect. Please check the number and try again.', 'easy-digital-downloads' ),
		'incomplete_number'                     => __( 'The card number is incomplete.', 'easy-digital-downloads' ),
		'incomplete_cvc'                        => __( 'The card\'s security code is incomplete.', 'easy-digital-downloads' ),
		'incomplete_expiry'                     => __( 'The card\'s expiration date is incomplete.', 'easy-digital-downloads' ),
		'incorrect_cvc'                         => __( 'The card\'s security code is incorrect.', 'easy-digital-downloads' ),
		'incorrect_zip'                         => __( 'The card\'s zip code failed validation.', 'easy-digital-downloads' ),
		'invalid_expiry_year_past'              => __( 'The card\'s expiration year is in the past.', 'easy-digital-downloads' ),
		'processing_error'                      => __( 'An error occurred while processing the card. Please try again.', 'easy-digital-downloads' ),
		'email_invalid'                         => __( 'Invalid email address, please correct and try again.', 'easy-digital-downloads' ),
		'authentication_required'               => __( 'Your purchase may require additional authentication. Please try again and confirm any authentication requests.', 'easy-digital-downloads' ),
		'approve_with_id'                       => __( 'There was an error processing your payment. Please try again, and if you continue to have problems, contact your card issuer.', 'easy-digital-downloads' ),
		'currency_not_supported'                => __( 'Your payment method is not authorized to make purchases in this currency. Please contact your card issuer.', 'easy-digital-downloads' ),
		'expired_card'                          => __( 'The payment method you have provided is expired. Please try a different payment method.', 'easy-digital-downloads' ),
		'try_again_later'                       => __( 'There was an error processing your payment. Please try again.', 'easy-digital-downloads' ),
		'generic_decline'                       => $generic_different_payment_method,
		'payment_intent_payment_attempt_failed' => $generic_different_payment_method,
		'invalid_request_error'                 => $generic_different_payment_method,
		'insufficient_funds'                    => $generic_different_payment_method,
		'pin_try_exceeded'                      => $generic_different_payment_method,
		'withdrawal_count_limit_exceeded'       => $generic_different_payment_method,
		'call_issuer'                           => $generic_contact_issuer_message,
		'card_not_supported'                    => $generic_contact_issuer_message,
		'card_velocity_exceeded'                => $generic_contact_issuer_message,
		'do_not_honor'                          => $generic_contact_issuer_message,
		'do_not_try_again'                      => $generic_contact_issuer_message,
		'invalid_account'                       => $generic_contact_issuer_message,
		'invalid_amount'                        => $generic_contact_issuer_message,
		'issuer_not_available'                  => $generic_contact_issuer_message,
		'new_account_information_available'     => $generic_contact_issuer_message,
		'no_action_taken'                       => $generic_contact_issuer_message,
		'not_permitted'                         => $generic_contact_issuer_message,
		'pickup_card'                           => $generic_contact_issuer_message,
		'restricted_card'                       => $generic_contact_issuer_message,
		'revocation_of_all_authorizations'      => $generic_contact_issuer_message,
		'revocation_of_authorization'           => $generic_contact_issuer_message,
		'security_violation'                    => $generic_contact_issuer_message,
		'service_not_allowed'                   => $generic_contact_issuer_message,
		'stop_payment_order'                    => $generic_contact_issuer_message,
		'transaction_not_allowed'               => $generic_contact_issuer_message,
		'card_declined'                         => $generic_contact_issuer_message,
	);

	/**
	 * Filters the list of available error codes and corresponding error messages.
	 *
	 * @since 2.8.0
	 *
	 * @param array $error_list List of error codes and corresponding error messages.
	 */
	$error_list = apply_filters( 'edds_get_localized_error_list', $error_list );

	return $error_list;
}

/**
 * Returns a localized error message for a corresponding Stripe
 * error code.
 *
 * @link https://stripe.com/docs/error-codes
 *
 * @since 2.8.0
 *
 * @param string $error_code Error code.
 * @param string $error_message Original error message to return if a localized version does not exist.
 * @return string $error_message Potentially localized error message.
 */
function edds_get_localized_error_message( $error_code, $error_message, $decline_code = false ) {
	$error_list = edds_get_localized_error_messages();

	if (
		( 'card_declined' === $error_code || 'payment_intent_payment_attempt_failed' === $error_code || 'payment_intent_payment_attempt_failed' === $error_code ) &&
		false !== $decline_code &&
		! empty( $error_list[ $decline_code ] )
	) {
		$error_message = $error_list[ $decline_code ];
	} elseif ( ! empty( $error_list[ $error_code ] ) ) {
		$error_message = $error_list[ $error_code ];
	}

	// To make development and testing easier for store owners, append the error and decline codes.
	if ( 'local' === wp_get_environment_type() ) {
		$error_message .= ' Error Code: ' . $error_code;
		if ( false !== $decline_code ) {
			$error_message .= ' Decline Code: ' . $decline_code;
		}
	}

	return $error_message;
}

/**
 * Returns a translatable string for informing the user that carts can only contain a single subscription and not be 'mixed'.
 *
 * @since 2.9.1
 *
 * @return string The translatable string for the single subscription requirement error.
 */
function edds_get_single_subscription_cart_error() {
	return 	esc_html__(
		'Subscriptions must be purchased individually. Please update your cart to only contain a single subscription.',
		'easy-digital-downloads'
	);
}
