<?php
/**
 * Error message helper for the Square integration.
 *
 * @package     EDD\Gateways\Square\Helpers\Errors
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Helpers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Error message helper for the Square integration.
 *
 * We setup all error messages here, so that we can allow them to be translated.
 *
 * @since 3.4.0
 */
class Errors {

	/**
	 * Get the error message for a Square error.
	 *
	 * @since 3.4.0
	 * @param string $error_code The error code.
	 *
	 * @return string The error message.
	 */
	public static function get_error_message( $error_code ) {
		if ( isset( self::error_messages()[ $error_code ] ) ) {
			return self::error_messages()[ $error_code ];
		}

		return __( 'An unknown error occurred. Please try again.', 'easy-digital-downloads' );
	}

	/**
	 * The list of translated error messages.
	 *
	 * @since 3.4.0
	 * @var array
	 */
	private static function error_messages() {
		$try_again           = __( 'Please verify your information and try again.', 'easy-digital-downloads' );
		$card_issuer_message = __( 'Please contact your card issuer.', 'easy-digital-downloads' );
		$declined_message    = __( 'Card Declined.', 'easy-digital-downloads' ) . ' ' . $card_issuer_message;
		$contact_support     = __( 'Please contact support.', 'easy-digital-downloads' );

		return array(
			'ADDRESS_VERIFICATION_FAILURE'           => __( 'Invalid Postal Code. Please try again.', 'easy-digital-downloads' ),
			'APPLE_TTP_PIN_TOKEN'                    => $try_again,
			'CARDHOLDER_INSUFFICIENT_PERMISSIONS'    => $card_issuer_message,
			'CARD_EXPIRED'                           => __( 'Card Expired.', 'easy-digital-downloads' ) . ' ' . $card_issuer_message,
			'CARD_NOT_SUPPORTED'                     => __( 'Card Not Supported.', 'easy-digital-downloads' ) . ' ' . $card_issuer_message,
			'CARD_TOKEN_EXPIRED'                     => __( 'Payment Verification Expired.', 'easy-digital-downloads' ) . ' ' . $try_again,
			'CARD_TOKEN_USED'                        => __( 'Payment Verification Error.', 'easy-digital-downloads' ) . ' ' . $try_again,
			'CVV_FAILURE'                            => __( 'Invalid CVV.', 'easy-digital-downloads' ) . ' ' . $try_again,
			'EXPIRATION_FAILURE'                     => __( 'Invalid Expiration Date.', 'easy-digital-downloads' ) . ' ' . $try_again,
			'GENERIC_DECLINE'                        => $declined_message,
			'GIFT_CARD_AVAILABLE_AMOUNT'             => __( 'Invalid Payment Method.', 'easy-digital-downloads' ),
			'INSUFFICIENT_FUNDS'                     => __( 'Insufficient Funds.', 'easy-digital-downloads' ) . ' ' . $card_issuer_message,
			'INSUFFICIENT_PERMISSIONS'               => __( 'Insufficient Permissions.', 'easy-digital-downloads' ) . ' ' . $card_issuer_message,
			'INVALID_ACCOUNT'                        => __( 'Invalid Account.', 'easy-digital-downloads' ) . ' ' . $card_issuer_message,
			'INVALID_CARD'                           => __( 'Invalid Card.', 'easy-digital-downloads' ) . ' ' . $card_issuer_message,
			'INVALID_CARD_DATA'                      => __( 'Invalid Card.', 'easy-digital-downloads' ),
			'INVALID_EMAIL_ADDRESS'                  => __( 'Invalid Email Address.', 'easy-digital-downloads' ) . ' ' . $try_again,
			'INVALID_EXPIRATION'                     => __( 'Invalid Expiration Date.', 'easy-digital-downloads' ) . ' ' . $try_again,
			'INVALID_FEES'                           => __( 'Invalid Fees.', 'easy-digital-downloads' ) . ' ' . $try_again,
			'INVALID_LOCATION'                       => __( 'Invalid Location.', 'easy-digital-downloads' ) . ' ' . $try_again,
			'INVALID_PHONE_NUMBER'                   => __( 'Invalid Phone Number.', 'easy-digital-downloads' ) . ' ' . $try_again,
			'INVALID_PIN'                            => __( 'Invalid PIN.', 'easy-digital-downloads' ) . ' ' . $try_again,
			'INVALID_POSTAL_CODE'                    => __( 'Invalid Postal Code.', 'easy-digital-downloads' ) . ' ' . $try_again,
			'MANUALLY_ENTERED_PAYMENT_NOT_SUPPORTED' => $contact_support,
			'PAN_FAILURE'                            => __( 'Invalid Card.', 'easy-digital-downloads' ) . ' ' . $try_again,
			'PAYMENT_AMOUNT_MISMATCH'                => $contact_support,
			'PAYMENT_LIMIT_EXCEEDED'                 => $contact_support,
			'TRANSACTION_LIMIT'                      => $contact_support,
			'VOICE_FAILURE'                          => $card_issuer_message,
			'ALLOWABLE_PIN_TRIES_EXCEEDED'           => __( 'Invalid PIN.', 'easy-digital-downloads' ) . ' ' . $card_issuer_message,
			'BAD_EXPIRATION'                         => __( 'Invalid Expiration Date.', 'easy-digital-downloads' ) . ' ' . $try_again,
			'CARD_DECLINED_VERIFICATION_REQUIRED'    => __( 'Card Declined.', 'easy-digital-downloads' ) . ' ' . $card_issuer_message,
			'CHIP_INSERTION_REQUIRED'                => __( 'Card Requires Chip.', 'easy-digital-downloads' ) . ' ' . $card_issuer_message,
			'CARD_PROCESSING_NOT_ENABLED'            => $contact_support,
			'TEMPORARY_ERROR'                        => $try_again,
		);
	}
}
