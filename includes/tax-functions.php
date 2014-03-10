<?php
/**
 * Tax Functions
 *
 * These are functions used for checking if taxes are enabled, calculating taxes, etc.
 * Functions for retrieving tax amounts and such for individual payments are in
 * includes/payment-functions.php and includes/cart-functions.php
 *
 * @package     EDD
 * @subpackage  Functions/Taxes
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.3
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Checks if taxes are enabled by using the option set from the EDD Settings.
 * The value returned can be filtered.
 *
 * @since 1.3.3
 * @global $edd_options
 * @return bool Whether or not taxes are enabled
 */
function edd_use_taxes() {
	global $edd_options;

	return apply_filters( 'edd_use_taxes', isset( $edd_options['enable_taxes'] ) );
}

/**
 * Checks if the user has enabled the option to calculate taxes after discounts
 * have been entered
 *
 * @since 1.4.1
 * @global $edd_options
 * @return bool Whether or not taxes are calculated after discount
 */
function edd_taxes_after_discounts() {
	global $edd_options;
	$ret = isset( $edd_options['taxes_after_discounts'] ) && edd_use_taxes();
	return apply_filters( 'edd_taxes_after_discounts', $ret );
}

/**
 * Retrieve tax rates
 *
 * @since 1.6
 * @global $edd_options
 * @return array Defined tax rates
 */
function edd_get_tax_rates() {

	$rates = get_option( 'edd_tax_rates', array() );
	return apply_filters( 'edd_get_tax_rates', $rates );
}

/**
 * Get taxation rate
 *
 * @since 1.3.3
 * @global $edd_options
 *
 * @param bool $country
 * @param bool $state
 * @return mixed|void
 */
function edd_get_tax_rate( $country = false, $state = false ) {
	global $edd_options;

	$rate = isset( $edd_options['tax_rate'] ) ? (float) $edd_options['tax_rate'] : 0;

	$user_address = edd_get_customer_address();

	if( empty( $country ) ) {
		if( ! empty( $_POST['billing_country'] ) ) {
			$country = $_POST['billing_country'];
		} elseif( is_user_logged_in() && ! empty( $user_address ) ) {
			$country = $user_address['country'];
		}
		$country = ! empty( $country ) ? $country : edd_get_shop_country();
	}

	if( empty( $state ) ) {
		if( ! empty( $_POST['state'] ) ) {
			$state = $_POST['state'];
		} elseif( is_user_logged_in() && ! empty( $user_address ) ) {
			$state = $user_address['state'];
		}
		$state = ! empty( $state ) ? $state : edd_get_shop_state();
	}

	if( ! empty( $country ) ) {
		$tax_rates   = edd_get_tax_rates();

		if( ! empty( $tax_rates ) ) {

			// Locate the tax rate for this country / state, if it exists
			foreach( $tax_rates as $key => $tax_rate ) {

				if( $country != $tax_rate['country'] )
					continue;

				if( ! empty( $tax_rate['global'] ) ) {
					if( ! empty( $tax_rate['rate'] ) ) {
						$rate = number_format( $tax_rate['rate'], 4 );
					}
				} else {

					if( empty( $tax_rate['state'] ) || strtolower( $state ) != strtolower( $tax_rate['state'] ) )
						continue;

					$state_rate = $tax_rate['rate'];
					if( 0 !== $state_rate || ! empty( $state_rate ) ) {
						$rate = number_format( $state_rate, 4 );
					}
				}
			}
		}
	}

	if( $rate > 1 ) {
		// Convert to a number we can use
		$rate = $rate / 100;
	}
	return apply_filters( 'edd_tax_rate', $rate, $country, $state );
}

/**
 * Retrieve a fully formatted tax rate
 *
 * @since 1.9
 * @param string $country The country to retrieve a rate for
 * @param string $state The state to retrieve a rate for
 * @return string Formatted rate
 */
function edd_get_formatted_tax_rate( $country = false, $state = false ) {
	$rate = edd_get_tax_rate( $country, $state );
	$rate = round( $rate * 100, 4 );
	$formatted = $rate .= '%';
	return apply_filters( 'edd_formatted_tax_rate', $formatted, $rate, $country, $state );
}

/**
 * Calculate the taxed amount
 *
 * @since 1.3.3
 * @param $amount float The original amount to calculate a tax cost
 * @param $country string The country to calculate tax for. Will use default if not passed
 * @param $state string The state to calculate tax for. Will use default if not passed
 * @return float $tax Taxed amount
 */
function edd_calculate_tax( $amount = 0, $country = false, $state = false ) {
	global $edd_options;

	$rate = edd_get_tax_rate( $country, $state );
	$tax  = 0.00;

	if ( edd_use_taxes() ) {

		if ( edd_prices_include_tax() ) {
			$pre_tax = ( $amount / ( 1 + $rate ) );
			$tax     = $amount - $pre_tax;
		} else {
			$tax = $amount * $rate;
		}

	}

	return apply_filters( 'edd_taxed_amount', $tax, $rate, $country, $state );
}

/**
 * Stores the tax info in the payment meta
 *
 * @since 1.3.3
 * @param $year int The year to retrieve taxes for, i.e. 2012
 * @uses edd_get_sales_tax_for_year()
 * @return void
*/
function edd_sales_tax_for_year( $year = null ) {
	echo edd_currency_filter( edd_format_amount( edd_get_sales_tax_for_year( $year ) ) );
}

/**
 * Gets the sales tax for the current year
 *
 * @since 1.3.3
 * @param $year int The year to retrieve taxes for, i.e. 2012
 * @uses edd_get_payment_tax()
 * @return float $tax Sales tax
 */
function edd_get_sales_tax_for_year( $year = null ) {
	
	if ( ! empty( $year ) ) {

		// Start at zero
		$tax = 0;

		$args = array(
			'post_type' 		=> 'edd_payment',
			'post_status'       => array( 'publish', 'revoked' ),
			'posts_per_page' 	=> -1,
			'year' 				=> $year,
			'fields'			=> 'ids'
		);

		$payments = get_posts( $args );

		if( $payments ) {

			foreach( $payments as $payment ) {
				$tax += edd_get_payment_tax( $payment );
			}

		}

	}

	return apply_filters( 'edd_get_sales_tax_for_year', $tax, $year );
}

/**
 * Is the cart taxed?
 *
 * This used to include a check for local tax opt-in, but that was ripped out in v1.6, so this is just a wrapper now
 *
 * @since 1.5
 * @return bool
 */
function edd_is_cart_taxed() {
	return edd_use_taxes();
}

/**
 * Check if the individual product prices include tax
 *
 * @since 1.5
 * @global $edd_options
 * @return bool $include_tax
*/
function edd_prices_include_tax() {
	global $edd_options;

	$ret = isset( $edd_options['prices_include_tax'] ) && $edd_options['prices_include_tax'] == 'yes' && edd_use_taxes();

	return apply_filters( 'edd_prices_include_tax', $ret );
}

/**
 * Checks whether the user has enabled display of taxes on the checkout
 *
 * @since 1.5
 * @global $edd_options
 * @return bool $include_tax
 */
function edd_prices_show_tax_on_checkout() {
	global $edd_options;
	$ret = isset( $edd_options['checkout_include_tax'] ) && $edd_options['checkout_include_tax'] == 'yes' && edd_use_taxes();
	return apply_filters( 'edd_taxes_on_prices_on_checkout', $ret );
}

/**
 * Check to see if we should show included taxes
 *
 * Some countries (notably in the EU) require included taxes to be displayed.
 *
 * @since 1.7
 * @author Daniel J Griffiths
 * @return bool
 */
function edd_display_tax_rate() {
	global $edd_options;

	$ret = edd_use_taxes() && isset( $edd_options['display_tax_rate'] );

	return apply_filters( 'edd_display_tax_rate', $ret );
}

/**
 * Should we show address fields for taxation purposes?
 *
 * @since 1.y
 * @return bool
 */
function edd_cart_needs_tax_address_fields() {

	if( ! edd_is_cart_taxed() )
		return false;

	return ! did_action( 'edd_after_cc_fields', 'edd_default_cc_address_fields' );

}

/**
 * Is this Download excluded from tax?
 *
 * @since 1.9
 * @return bool
 */
function edd_download_is_tax_exclusive( $download_id = 0 ) {
	$ret = (bool) get_post_meta( $download_id, '_edd_download_tax_exclusive', true );
	return apply_filters( 'edd_download_is_tax_exclusive', $ret, $download_id );
}