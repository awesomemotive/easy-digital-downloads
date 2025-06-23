<?php
/**
 * Currency helper for the Square integration.
 *
 * @package     EDD\Gateways\Square\Helpers
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Helpers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Currency helper for the Square integration.
 *
 * @since 3.4.0
 */
class Currency {
	/**
	 * Get the Square currency.
	 *
	 * @since 3.4.0
	 * @return string The Square currency.
	 */
	public static function get_currency( $currency = '' ) {
		$currency = ! empty( $currency ) ? $currency : edd_get_currency();
		return strtoupper( $currency );
	}

	/**
	 * Check if the currency is a zero decimal currency.
	 *
	 * @since 3.4.0
	 * @param string $currency The currency to check.
	 * @return bool True if the currency is a zero decimal currency, false otherwise.
	 */
	public static function is_zero_decimal_currency( $currency = '' ) {
		$currency = ! empty( $currency ) ? $currency : edd_get_currency();
		return in_array( strtoupper( $currency ), array( 'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF' ), true );
	}

	/**
	 * Check if the currency is supported.
	 *
	 * Square only accepts payments in the currency of the merchant account.
	 *
	 * @since 3.4.0
	 * @return bool True if supported, false otherwise.
	 */
	public static function is_currency_supported() {
		$currency = edd_get_currency();
		$mode     = Mode::get();

		$merchant_currency = edd_get_option( "square_{$mode}_currency" );

		return strtolower( $currency ) === strtolower( $merchant_currency );
	}
}
