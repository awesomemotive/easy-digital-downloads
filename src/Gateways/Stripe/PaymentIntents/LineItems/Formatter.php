<?php
/**
 * Line Items Formatter
 *
 * Handles amount formatting for Stripe line items.
 *
 * @package EDD\Gateways\Stripe\PaymentIntents\LineItems
 * @since 3.6.1
 */

namespace EDD\Gateways\Stripe\PaymentIntents\LineItems;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Formatter class.
 *
 * @since 3.6.1
 */
class Formatter {

	/**
	 * Format an amount for Stripe API (convert to cents for non-zero-decimal currencies).
	 *
	 * @since 3.6.1
	 * @param float $amount The amount in dollars/currency.
	 * @return int The amount in the smallest currency unit (cents).
	 */
	public static function format_amount( $amount ) {
		if ( edds_is_zero_decimal_currency() ) {
			return (int) $amount;
		}

		return (int) round( $amount * 100, 0 );
	}

	/**
	 * Format an amount from cents back to dollars for display/logging.
	 *
	 * @since 3.6.1
	 * @param int $amount_in_cents The amount in cents.
	 * @return float The amount in dollars.
	 */
	public static function cents_to_dollars( $amount_in_cents ) {
		if ( edds_is_zero_decimal_currency() ) {
			return (float) $amount_in_cents;
		}

		return (float) ( $amount_in_cents / 100 );
	}

	/**
	 * Sanitize a product code for Stripe.
	 *
	 * @since 3.6.1
	 * @param string $code The product code.
	 * @return string Sanitized product code.
	 */
	public static function sanitize_product_code( $code ) {
		$code = sanitize_text_field( $code );

		return substr( $code, 0, 12 );
	}

	/**
	 * Sanitize a product name for Stripe.
	 *
	 * @since 3.6.1
	 * @param string $name The product name.
	 * @return string Sanitized product name.
	 */
	public static function sanitize_product_name( $name ) {
		// Decode HTML entities.
		$name = html_entity_decode( $name, ENT_QUOTES, 'UTF-8' );

		// Remove any remaining HTML tags.
		$name = strip_tags( $name );

		// Convert curly quotes to straight quotes.
		$name = str_replace(
			array( "\xE2\x80\x9C", "\xE2\x80\x9D", "\xE2\x80\x98", "\xE2\x80\x99" ), // Curly quotes.
			array( '"', '"', "'", "'" ), // Straight quotes.
			$name
		);

		// Limit length to Stripe's maximum (1024 chars).
		// Stripe will truncate to 26 alphanumeric chars for card networks automatically.
		return substr( $name, 0, 1024 );
	}
}
