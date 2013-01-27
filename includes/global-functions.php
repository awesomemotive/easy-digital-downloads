<?php

/**
 * Get Button Colors
 *
 * Returns an array of button colors.
 *
 * @access      public
 * @since       1.0
 * @return      array
 */
function edd_get_button_colors() {
	$colors = array(
		'gray'      => __( 'Gray', 'edd' ),
		'blue'      => __( 'Blue', 'edd' ),
		'green'     => __( 'Green', 'edd' ),
		'yellow'    => __( 'Yellow', 'edd' ),
		'dark-gray' => __( 'Dark Gray', 'edd' ),
	);

	return apply_filters( 'edd_button_colors', $colors );
}

/**
 * Get Button Styles
 *
 * Returns an array of button styles.
 *
 * @access      public
 * @since       1.2.2
 * @return      array
*/

function edd_get_button_styles() {
	$styles = array(
		'button'	=> __( 'Button', 'edd' ),
		'plain'     => __( 'Plain Text', 'edd' )
	);

	return apply_filters( 'edd_button_styles', $styles );
}

/**
 * Get Email Templates
 *
 * @access private
 * @since 1.0.8.2
 * @return array
 */
function edd_get_email_templates() {
	$templates = array(
		'default' => __( 'Default Template', 'edd' ),
		'none'    => __( 'No template, plain text only', 'edd' )
	);
	return apply_filters( 'edd_email_templates', $templates );
}


/**
 * Get Payment Gateways
 *
 * Rreturns a list of all available gateways.
 *
 * @access      public
 * @since       1.0
 * @return      array
*/

function edd_get_payment_gateways() {

	// Default, built-in gateways
	$gateways = array(
		'paypal' => array('admin_label' => 'PayPal', 'checkout_label' => 'PayPal'),
		'manual' => array('admin_label' => __('Test Payment', 'edd'), 'checkout_label' => __('Test Payment', 'edd')),
	);

	return apply_filters( 'edd_payment_gateways', $gateways );

}

/**
 * Get Currencies
 *
 * @access      public
 * @since       1.0
 * @return      array
*/

function edd_get_currencies() {
	$currencies = array(
		'USD' => __('US Dollars (&#36;)', 'edd'),
		'EUR' => __('Euros (&euro;)', 'edd'),
		'GBP' => __('Pounds Sterling (&pound;)', 'edd'),
		'AUD' => __('Australian Dollars (&#36;)', 'edd'),
		'BRL' => __('Brazilian Real (&#36;)', 'edd'),
		'CAD' => __('Canadian Dollars (&#36;)', 'edd'),
		'CZK' => __('Czech Koruna', 'edd'),
		'DKK' => __('Danish Krone', 'edd'),
		'HKD' => __('Hong Kong Dollar (&#36;)', 'edd'),
		'HUF' => __('Hungarian Forint', 'edd'),
		'ILS' => __('Israeli Shekel', 'edd'),
		'JPY' => __('Japanese Yen (&yen;)', 'edd'),
		'MYR' => __('Malaysian Ringgits', 'edd'),
		'MXN' => __('Mexican Peso (&#36;)', 'edd'),
		'NZD' => __('New Zealand Dollar (&#36;)', 'edd'),
		'NOK' => __('Norwegian Krone', 'edd'),
		'PHP' => __('Philippine Pesos', 'edd'),
		'PLN' => __('Polish Zloty', 'edd'),
		'SGD' => __('Singapore Dollar (&#36;)', 'edd'),
		'SEK' => __('Swedish Krona', 'edd'),
		'CHF' => __('Swiss Franc', 'edd'),
		'TWD' => __('Taiwan New Dollars', 'edd'),
		'THB' => __('Thai Baht', 'edd'),
		'INR' => __('Indian Rupee', 'edd'),
		'TRY' => __('Turkish Lira', 'edd'),
		'RIAL' => __('Iranian Rial', 'edd')
	);

	return apply_filters( 'edd_currencies', $currencies );
}