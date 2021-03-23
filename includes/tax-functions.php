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
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.3
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Checks if taxes are enabled by using the option set from the EDD Settings.
 * The value returned can be filtered.
 *
 * @since 1.3.3
 *
 * @return bool True if taxes are enabled, false otherwise..
 */
function edd_use_taxes() {
	$ret = edd_get_option( 'enable_taxes', false );

	return (bool) apply_filters( 'edd_use_taxes', $ret );
}

/**
 * Retrieve tax rates
 *
 * @since 1.6
 * @since 3.0 Updated to use new query class.
 *            Added $output parameter to output an array of EDD\Adjustments\Adjustment objects, if set to `object`.
 *            Added $args parameter.
 *
 * @param array  $args   Query arguments
 * @param string $output Optional. Type of data to output. Any of ARRAY_N | OBJECT.
 *
 * @return array|\EDD\Adjustments\Adjustment[] Tax rates.
 */
function edd_get_tax_rates( $args = array(), $output = ARRAY_N ) {

	if ( isset( $args['status'] ) && 'active' === $args['status'] ) {
		add_filter( 'edd_adjustments_query_clauses', 'edd_active_tax_rates_query_clauses' );
	}

	// Instantiate a query object
	$adjustments = new EDD\Database\Queries\Adjustment();

	// Parse args
	$r = wp_parse_args( $args, array(
		'number'  => 30,
		'type'    => 'tax_rate',
		'orderby' => 'date_created',
		'order'   => 'ASC',
	) );

	if ( isset( $args['status'] ) && 'active' === $args['status'] ) {
		remove_filter( 'edd_adjustments_query_clauses', 'edd_active_tax_rates_query_clauses' );
	}

	$adjustments->query( $r );

	if ( OBJECT === $output ) {
		return $adjustments->items;
	}

	$rates = array();

	if ( $adjustments->items ) {
		foreach ( $adjustments->items as $tax_rate ) {
			$rate = array(
				'id'      => absint( $tax_rate->id ),
				'country' => esc_attr( $tax_rate->name ),
				'rate'    => floatval( $tax_rate->amount ),
			);

			if ( isset( $tax_rate->description ) && ! empty( $tax_rate->description ) ) {
				$rate['state'] = esc_attr( $tax_rate->description );
			} else {
				$rate['state'] = '';
			}

			if ( 'country' === $tax_rate->scope ) {
				$rate['global'] = '1';
			} else {
				$rate['global'] = '0';
			}

			$rates[] = $rate;
		}
	}

	return (array) apply_filters( 'edd_get_tax_rates', $rates );
}

/**
 * Query for and return array of tax rates counts, keyed by status.
 *
 * @since 3.0
 *
 * @return array
 */
function edd_get_tax_rate_counts( $args = array() ) {

	// Parse arguments
	$r = wp_parse_args( $args, array(
		'count'   => true,
		'groupby' => 'status',
		'type'    => 'tax_rate'
	) );

	// Query for count.
	$counts = new EDD\Database\Queries\Adjustment( $r );

	// Format & return
	return edd_format_counts( $counts, $r['groupby'] );
}

/**
 * Add a WHERE clause to ensure only active tax rates are returned.
 *
 * @since 3.0
 *
 * @param array $clauses Query clauses.
 * @return array $clauses Updated query clauses.
 */
function edd_active_tax_rates_query_clauses( $clauses ) {
	$date = \Carbon\Carbon::now( edd_get_timezone_id() )->toDateTimeString();

	$clauses['where'] .= "
		AND ( start_date < '{$date}' OR start_date IS NULL )
		AND ( end_date > '{$date}' OR end_date IS NULL )
	";

	return $clauses;
}

/**
 * Get taxation rate.
 *
 * @since 1.3.3
 * @since 3.0 Refactored to work with custom tables and start and end dates.
 *            Renamed $state parameter to $region.
 *            Added $fallback parameter to only get rate for passed Country and Region.
 *
 * @param string  $country Country.
 * @param string  $region  Region.
 * @param boolean $fallback Fall back to (in order): server $_POST data, the current Customer's
 *                          address information, then your store's Business Country setting.
 *                          Default true.
 *
 * @return float
 */
function edd_get_tax_rate( $country = '', $region = '', $fallback = true ) {

	// This global is used to avoid excessive DB lookups per request.
	global $edd_determined_tax_rates;

	// First we only check for non-fallback options, because fallback logic happens later on.
	$tax_rate_key = sanitize_key( strtolower( $country . '_' . $region ) );
	if ( is_array( $edd_determined_tax_rates ) && ! $fallback && array_key_exists( $tax_rate_key, $edd_determined_tax_rates ) ) {
		return $edd_determined_tax_rates[ $tax_rate_key ];
	}

	// Default rate
	$rate = (float) edd_get_option( 'tax_rate', 0 );

	// Get the address, to try to get the tax rate
	$user_address = edd_get_customer_address();

	$address_line_1 = ! empty( $_POST['card_address'] )
		? sanitize_text_field( $_POST['card_address'] )
		: '';

	$address_line_2 = ! empty( $_POST['card_address_2'] )
		? sanitize_text_field( $_POST['card_address_2'] )
		: '';

	$city = ! empty( $_POST['card_city'] )
		? sanitize_text_field( $_POST['card_city'] )
		: '';

	$zip = ! empty( $_POST['card_zip'] )
		? sanitize_text_field( $_POST['card_zip'] )
		: '';

	// Country
	if ( empty( $country ) && true === $fallback ) {
		if ( ! empty( $_POST['billing_country'] ) ) {
			$country = $_POST['billing_country'];
		} elseif ( is_user_logged_in() && ! empty( $user_address['country'] ) ) {
			$country = $user_address['country'];
		}

		$country = empty( $country )
			? edd_get_shop_country()
			: $country;
	}

	// Region
	if ( empty( $region ) && true === $fallback ) {
		if ( ! empty( $_POST['state'] ) ) {
			$region = $_POST['state'];
		} elseif ( ! empty( $_POST['card_state'] ) ) {
			$region = $_POST['card_state'];
		} elseif ( is_user_logged_in() && ! empty( $user_address['state'] ) ) {
			$region = $user_address['state'];
		}

		$region = empty( $region )
			? edd_get_shop_state()
			: $region;
	}

	// Check global variable again. This is after the fallback logic has run.
	$tax_rate_key = sanitize_key( strtolower( $country . '_' . $region ) );
	if ( is_array( $edd_determined_tax_rates ) && array_key_exists( $tax_rate_key, $edd_determined_tax_rates ) ) {
		return $edd_determined_tax_rates[ $tax_rate_key ];
	}

	$tax_rate = edd_get_tax_rate_by_location(
		array(
			'country' => $country,
			'region'  => $region,
		)
	);
	if ( $tax_rate ) {
		$rate = $tax_rate->amount;
	}

	// Convert to a number we can use
	$rate = $rate / 100;

	/**
	 * Allow the tax rate to be filtered.
	 *
	 * @since 1.3.3
	 * @since 3.0 Added entire customer address.
	 *
	 * @param float  $rate           Calculated tax rate.
	 * @param string $country        Country.
	 * @param string $region         Region.
	 * @param string $address_line_1 First line of address.
	 * @param string $address_line_2 Second line of address.
	 * @param string $city           City.
	 * @param string $zip            ZIP code.
	 */
	$rate = apply_filters( 'edd_tax_rate', $rate, $country, $region, $address_line_1, $address_line_2, $city, $zip );

	// Update global variable so we'll bypass all this logic if this gets run again in the same request.
	$edd_determined_tax_rates[ $tax_rate_key ] = $rate;

	return $rate;
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
	$rate      = edd_get_tax_rate( $country, $state );
	$rate      = round( $rate * 100, 4 );
	$formatted = $rate .= '%';

	return apply_filters( 'edd_formatted_tax_rate', $formatted, $rate, $country, $state );
}

/**
 * Calculate the taxed amount.
 *
 * @since 1.3.3
 * @since 3.0 Renamed $state parameter to $region.
 *            Added $fallback parameter.
 *            Added `$tax_rate` parameter.
 *
 * @param float      $amount   Amount.
 * @param string     $country  Country. Default base country.
 * @param string     $region   Region. Default base region.
 * @param boolean    $fallback Fall back to (in order): server $_POST data, the current Customer's
 *                             address information, then your store's Business Country setting.
 *                             Default true.
 * @param null|float $tax_rate Tax rate to use for the calculataion. If `null`, the rate is retrieved using
 *                             `edd_get_tax_rate()`.
 *
 * @return float $tax Taxed amount.
 */
function edd_calculate_tax( $amount = 0.00, $country = '', $region = '', $fallback = true, $tax_rate = null ) {
	$rate = ( null === $tax_rate ) ? edd_get_tax_rate( $country, $region, $fallback ) : $tax_rate;
	$tax  = 0.00;

	if ( edd_use_taxes() && $amount > 0 ) {

		if ( edd_prices_include_tax() ) {
			$pre_tax = ( $amount / ( 1 + $rate ) );
			$tax     = $amount - $pre_tax;
		} else {
			$tax = $amount * $rate;
		}
	}

	/**
	 * Filter the taxed amount.
	 *
	 * @since 1.5.3
	 *
	 * @param float $tax      Taxed amount.
	 * @param float $rate     Tax rate applied.
	 * @param string $country Country.
	 * @param string $region  Region.
	 */
	return apply_filters( 'edd_taxed_amount', $tax, $rate, $country, $region );
}

/**
 * Returns the formatted tax amount for the given year
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
 * Gets the sales tax for the given year
 *
 * @since 1.3.3
 * @param $year int The year to retrieve taxes for, i.e. 2012
 * @uses edd_get_payment_tax()
 * @return float $tax Sales tax
 */
function edd_get_sales_tax_for_year( $year = null ) {
	global $wpdb;

	// Start at zero
	$tax = 0;

	if ( ! empty( $year ) ) {
		$year = absint( $year );

		$tax = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(tax) FROM {$wpdb->edd_orders} WHERE status IN('complete', 'revoked') AND YEAR(date_created) = %d", $year ) );
	}

	if ( ! $tax || is_null( $tax ) ) {
		$tax = 0.00;
	}

	// Filter & return
	return (float) apply_filters( 'edd_get_sales_tax_for_year', $tax, $year );
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
 * @return bool $include_tax
 */
function edd_prices_include_tax() {
	$ret = ( edd_get_option( 'prices_include_tax', false ) === 'yes' && edd_use_taxes() );

	return apply_filters( 'edd_prices_include_tax', $ret );
}

/**
 * Checks whether the user has enabled display of taxes on the checkout
 *
 * @since 1.5
 * @return bool $include_tax
 */
function edd_prices_show_tax_on_checkout() {
	$ret = ( edd_get_option( 'checkout_include_tax', false ) === 'yes' && edd_use_taxes() );

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
	$ret = edd_use_taxes() && edd_get_option( 'display_tax_rate', false );

	return apply_filters( 'edd_display_tax_rate', $ret );
}

/**
 * Should we show address fields for taxation purposes?
 *
 * @since 1.y
 * @return bool
 */
function edd_cart_needs_tax_address_fields() {

	if ( ! edd_is_cart_taxed() ) {
		return false;
	}

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

	return (Bool) apply_filters( 'edd_download_is_tax_exclusive', $ret, $download_id );
}

/**
 * Gets the tax rate object from the database for a given country / region.
 * Used in `edd_get_tax_rate`, `edd_build_order`, `edd_add_manual_order`.
 * If a regional tax rate is found, it will be returned immediately,
 * so rates with a scope of `country` may be overridden by a more specific rate.
 *
 * @param array $args {
 *     Country and, optionally, region to get the tax rate for.
 *
 *     @type string $country Required - country to check.
 *     @type string $region  Optional - check a specific region within the country.
 * }
 * @return \EDD\Database\Rows\Adjustment|false
 *
 * @since 3.0
 */
function edd_get_tax_rate_by_location( $args ) {

	$rate = false;
	if ( empty( $args['country'] ) ) {
		return $rate;
	}

	// Fetch all the tax rates from the database.
	// The region is not passed in deliberately in order to check for country-wide tax rates.
	$tax_rates = edd_get_tax_rates(
		array(
			'name'   => $args['country'],
			'status' => 'active',
		),
		OBJECT
	);

	if ( empty( $tax_rates ) ) {
		return $rate;
	}

	foreach ( $tax_rates as $tax_rate ) {

		// Regional tax rate.
		if ( ! empty( $args['region'] ) && ! empty( $tax_rate->description ) ) {
			if ( strtolower( $args['region'] ) !== strtolower( $tax_rate->description ) ) {
				continue;
			}

			$regional_rate = $tax_rate->amount;

			if ( ! empty( $regional_rate ) ) {
				return $tax_rate;
			}
		} elseif ( 'country' === $tax_rate->scope ) {
			// Countrywide tax rate.
			$rate = $tax_rate;
		}
	}

	return $rate;
}
