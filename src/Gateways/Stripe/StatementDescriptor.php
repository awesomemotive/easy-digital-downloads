<?php
/**
 * Stripe Statement Descriptor.
 *
 * @package     EDD\Gateways\Stripe
 * @since       3.2.8
 */

namespace EDD\Gateways\Stripe;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class to handle the Stripe statement descriptor.
 *
 * @since 3.2.8
 */
class StatementDescriptor {

	/**
	 * The maximum length of the statement descriptor.
	 *
	 * @var int
	 */
	public static $max_length = 22;

	/**
	 * The characters that are not supported by Stripe.
	 *
	 * @since 3.2.8 Unsupported characters are < > \ ' " *
	 *
	 * @var array
	 */
	public static $unsupported_characters = array( '<', '>', '\\', '\'', '"', '*' );

	/**
	 * Get the statement descriptor suffix.
	 *
	 * @since 3.2.8
	 *
	 * @param string $suffix The suffix to sanitize.
	 *
	 * @return string
	 */
	public static function sanitize_suffix( $suffix = '' ) {
		if ( ! self::include_purchase_summary() ) {
			return '';
		}

		if ( empty( $suffix ) ) {
			return '';
		}

		// Determine if we need to include a latin character in the suffix.
		if ( self::needs_latin_character( $suffix ) ) {
			// Get a latin character to use in the suffix.
			$latin_char = self::get_latin_character();

			// Add the latin character to the suffix.
			$suffix = $latin_char . $suffix;
		}

		// Remove unsupported characters.
		$suffix = self::remove_unsupported_characters( $suffix );

		// The combination of prefix and suffix is longer than max length, truncate the suffix.
		$length = self::prefix_length() + strlen( $suffix );
		if ( $length > self::$max_length ) {
			$suffix = substr( $suffix, 0, self::$max_length - self::prefix_length() );
		}

		return strtoupper( $suffix );
	}

	/**
	 * Get the prefix for the statement descriptor.
	 *
	 * This is in the Stripe account settings for card_payments, and we store it for performance.
	 * Visiting the Stripe settings page in EDD will update this value, if it's changed in Stripe.
	 *
	 * @since 3.2.8
	 *
	 * @return string
	 */
	private static function get_prefix() {
		// If we already have a prefix saved, just return it.
		if ( ! empty( edd_get_option( 'stripe_statement_descriptor_prefix', '' ) ) ) {
			return edd_get_option( 'stripe_statement_descriptor_prefix' );
		}

		$prefix = '';

		// There isn't a prefix saved already, get one and save it.
		try {
			$account_id = edd_stripe()->connect()->get_connect_id();

			if ( ! empty( $account_id ) ) {

				$account = edds_api_request( 'Account', 'retrieve', $account_id );

				if ( ! is_wp_error( $account ) ) {
					$prefix = $account->settings->card_payments->statement_descriptor_prefix ?? '';
				}

				// If we got a prefix, save it so we don't have to look it up again.
				edd_update_option( 'stripe_statement_descriptor_prefix', $prefix );
			}
		} catch ( \Exception $e ) {
			// do nothing.
		}

		return $prefix;
	}

	/**
	 * Determine if we need to include a latin character in the suffix.
	 *
	 * Stripe has the requirement of a combination of a prefix and suffix contains at least one latin character.
	 *
	 * @since 3.2.8
	 *
	 * @param string $suffix The suffix to check.
	 *
	 * @return bool
	 */
	private static function needs_latin_character( $suffix ) {
		// If the suffix already has a latin character, we don't need to add one.
		if ( preg_match( '/[a-zA-Z]/', $suffix ) ) {
			return false;
		}

		// If the prefix already has a latin character, we don't need to add one.
		if ( preg_match( '/[a-zA-Z]/', self::get_prefix() ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get a latin character to use in our suffix.
	 *
	 * We'll use the first latin character in the prefix to ensure that the statement descriptor is valid.
	 * If it doesn't contain one, we'll just use 'E', for EDD. We'll avoid the letter 'O' by default here as it could be confused
	 * with the number 0 depending on how the statement descriptor is displayed at the financial institution.
	 *
	 * @since 3.2.8
	 *
	 * @return string
	 */
	private static function get_latin_character() {
		// Get the first character of the prefix.
		$latin_character = substr( self::get_prefix(), 0, 1 );
		if ( ! preg_match( '/[a-zA-Z]/', $latin_character ) ) {
			$latin_character = 'E';
		}

		return strtoupper( $latin_character ) . '-';
	}

	/**
	 * Get the length of the prefix.
	 *
	 * Stripe always adds a * and a space at the end of the prefix, so we need to account for that.
	 *
	 * @since 3.2.8
	 *
	 * @return int
	 */
	private static function prefix_length() {
		return strlen( self::get_prefix() . '* ' );
	}

	/**
	 * Remove any unsupported characters from the suffix.
	 *
	 * @since 3.2.8
	 *
	 * @param string $suffix The suffix to sanitize.
	 *
	 * @return string
	 */
	private static function remove_unsupported_characters( $suffix ) {
		// Remove any characters that are not supported by Stripe.
		$suffix = str_replace( self::$unsupported_characters, '', $suffix );

		// Remove any spaces in the suffix.
		$suffix = str_replace( ' ', '', $suffix );

		return $suffix;
	}

	/**
	 * Whether to include the purchase summary in the payment descriptor.
	 *
	 * @since 3.2.8
	 *
	 * @return bool
	 */
	public static function include_purchase_summary() {
		return (bool) edd_get_option( 'stripe_include_purchase_summary_in_statement_descriptor', false );
	}
}
