<?php
/**
 * Currency Functions
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     3.0
 */

use EDD\Currency\Currency;
use EDD\Currency\Money_Formatter;

/**
 * Get Currencies
 *
 * @since 1.0
 * @return array $currencies A list of the available currencies
 */
function edd_get_currencies() {
	$currencies = array(
		'USD'  => __( 'US Dollars (&#36;)', 'easy-digital-downloads' ),
		'EUR'  => __( 'Euros (&euro;)', 'easy-digital-downloads' ),
		'GBP'  => __( 'Pound Sterling (&pound;)', 'easy-digital-downloads' ),
		'AUD'  => __( 'Australian Dollars (&#36;)', 'easy-digital-downloads' ),
		'BRL'  => __( 'Brazilian Real (R&#36;)', 'easy-digital-downloads' ),
		'CAD'  => __( 'Canadian Dollars (&#36;)', 'easy-digital-downloads' ),
		'CZK'  => __( 'Czech Koruna', 'easy-digital-downloads' ),
		'DKK'  => __( 'Danish Krone', 'easy-digital-downloads' ),
		'HKD'  => __( 'Hong Kong Dollar (&#36;)', 'easy-digital-downloads' ),
		'HUF'  => __( 'Hungarian Forint', 'easy-digital-downloads' ),
		'ILS'  => __( 'Israeli Shekel (&#8362;)', 'easy-digital-downloads' ),
		'JPY'  => __( 'Japanese Yen (&yen;)', 'easy-digital-downloads' ),
		'MYR'  => __( 'Malaysian Ringgits', 'easy-digital-downloads' ),
		'MXN'  => __( 'Mexican Peso (&#36;)', 'easy-digital-downloads' ),
		'NZD'  => __( 'New Zealand Dollar (&#36;)', 'easy-digital-downloads' ),
		'NOK'  => __( 'Norwegian Krone', 'easy-digital-downloads' ),
		'PHP'  => __( 'Philippine Pesos', 'easy-digital-downloads' ),
		'PLN'  => __( 'Polish Zloty', 'easy-digital-downloads' ),
		'SGD'  => __( 'Singapore Dollar (&#36;)', 'easy-digital-downloads' ),
		'SEK'  => __( 'Swedish Krona', 'easy-digital-downloads' ),
		'CHF'  => __( 'Swiss Franc', 'easy-digital-downloads' ),
		'TWD'  => __( 'Taiwan New Dollars', 'easy-digital-downloads' ),
		'THB'  => __( 'Thai Baht (&#3647;)', 'easy-digital-downloads' ),
		'INR'  => __( 'Indian Rupee (&#8377;)', 'easy-digital-downloads' ),
		'TRY'  => __( 'Turkish Lira (&#8378;)', 'easy-digital-downloads' ),
		'RIAL' => __( 'Iranian Rial (&#65020;)', 'easy-digital-downloads' ),
		'RUB'  => __( 'Russian Rubles', 'easy-digital-downloads' ),
		'AOA'  => __( 'Angolan Kwanza', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_currencies', $currencies );
}

/**
 * Accepts an amount (ideally from the database, unmodified) and formats it
 * for display. The amount itself is formatted and the currency prefix/suffix
 * is applied and positioned.
 *
 * @since 3.0
 *
 * @param int|float|string $amount
 * @param string           $currency
 *
 * @return string
 */
function edd_display_amount( $amount, $currency ) {
	$formatter = new Money_Formatter( $amount, new Currency( $currency ) );

	return $formatter->format_for_display()
		->apply_symbol();
}

/**
 * Get the store's set currency
 *
 * @since 1.5.2
 * @return string The currency code
 */
function edd_get_currency() {
	$currency = edd_get_option( 'currency', 'USD' );
	return apply_filters( 'edd_currency', $currency );
}

/**
 * Given a currency determine the symbol to use. If no currency given, site default is used.
 * If no symbol is determined, the currency string is returned.
 *
 * @since  2.2
 *
 * @param string $currency The currency string
 *
 * @return string           The symbol to use for the currency
 */
function edd_currency_symbol( $currency = '' ) {
	if ( empty( $currency ) ) {
		$currency = edd_get_currency();
	}

	$currency = new Currency( $currency );

	return $currency->symbol;
}

/**
 * Get the name of a currency
 *
 * @since 2.2
 *
 * @param string $code The currency code
 *
 * @return string The currency's name
 */
function edd_get_currency_name( $code = 'USD' ) {
	$currencies = edd_get_currencies();
	$name       = isset( $currencies[ $code ] ) ? $currencies[ $code ] : $code;
	return apply_filters( 'edd_currency_name', $name );
}

