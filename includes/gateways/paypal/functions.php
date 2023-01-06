<?php
/**
 * PayPal Commerce Functions
 *
 * @package    easy-digital-downloads
 * @subpackage Gateways\PayPal
 * @copyright  Copyright (c) 2021, Sandhills Development, LLC
 * @license    GPL2+
 * @since      2.11
 */

namespace EDD\Gateways\PayPal;

use EDD\Gateways\PayPal\Exceptions\Authentication_Exception;

/**
 * Determines whether or not there's a valid REST API connection.
 *
 * @param string $mode Mode to check (`live` or `sandbox`).
 *
 * @since 2.11
 * @return bool
 */
function has_rest_api_connection( $mode = '' ) {
	try {
		$api = new API( $mode );

		return true;
	} catch ( Authentication_Exception $e ) {
		return false;
	}
}

/**
 * Determines whether or not the account is ready to accept payments.
 * Requirements:
 *
 *      - API keys must be set.
 *      - Merchant account must be ready to accept payments.
 *
 * @see API::set_credentials()
 * @see AccountStatusValidator::check_merchant_account()
 *
 * @since 2.11
 *
 * @param string $mode
 *
 * @return bool
 */
function ready_to_accept_payments( $mode = '' ) {
	if ( empty( $mode ) ) {
		$mode = edd_is_test_mode() ? API::MODE_SANDBOX : API::MODE_LIVE;
	}

	if ( ! has_rest_api_connection( $mode ) ) {
		return false;
	}

	$validator = new AccountStatusValidator( $mode );
	$validator->check_merchant_account();

	return empty( $validator->errors_for_merchant_account->errors );
}

/**
 * Determines whether or not PayPal Standard should be enabled.
 * This returns true if the store owner previously had a PayPal Standard connection but has not yet
 * connected to the new REST API implementation.
 *
 * If PayPal Standard is enabled, then PayPal payments run through the legacy API.
 *
 * @param string $mode If omitted, current site mode is used.
 *
 * @since 2.11
 * @return bool
 */
function paypal_standard_enabled( $mode = '' ) {
	if ( empty( $mode ) ) {
		$mode = edd_is_test_mode() ? API::MODE_SANDBOX : API::MODE_LIVE;
	}

	$rest_connection = has_rest_api_connection( $mode );
	$enabled         = ! $rest_connection && edd_get_option( 'paypal_email' );

	/**
	 * Filters whether or not PayPal Standard is enabled.
	 *
	 * @since 2.11
	 *
	 * @param bool $enabled
	 */
	return apply_filters( 'edd_paypal_standard_enabled', $enabled );
}

/**
 * Returns the partner merchant ID for a given mode.
 *
 * @param string $mode If omitted, current site mode is used.
 *
 * @since 2.11
 * @return string
 */
function get_partner_merchant_id( $mode = '' ) {
	if ( empty( $mode ) ) {
		$mode = edd_is_test_mode() ? API::MODE_SANDBOX : API::MODE_LIVE;
	}

	if ( API::MODE_LIVE === $mode ) {
		return EDD_PAYPAL_MERCHANT_ID;
	} else {
		return EDD_PAYPAL_SANDBOX_MERCHANT_ID;
	}
}

/**
 * Returns the styles used for the PayPal buttons.
 *
 * @return array
 */
function get_button_styles() {
	$styles = array(
		'layout' => 'vertical',
		'size'   => 'responsive',
		'shape'  => 'rect',
		'color'  => 'gold',
		'label'  => 'paypal'
	);

	if ( ! edd_is_checkout() ) {
		$styles['layout'] = 'horizontal';
		$styles['label']  = 'buynow';
	}

	/**
	 * Filters the button styles.
	 *
	 * @since 2.11
	 */
	return apply_filters( 'edd_paypal_smart_button_style', $styles );
}

/**
 * Gets the PayPal purchase units without the individual item breakdown.
 *
 * @since 2.11.2
 *
 * @param int   $payment_id    The payment/order ID.
 * @param array $purchase_data The array of purchase data.
 * @param array $payment_args  The array created to insert the payment into the database.
 *
 * @return array
 */
function get_order_purchase_units_without_breakdown( $payment_id, $purchase_data, $payment_args ) {
	$order_amount = array(
		'currency_code' => edd_get_currency(),
		'value'         => (string) edd_sanitize_amount( $purchase_data['price'] ),
	);
	if ( (float) $purchase_data['tax'] > 0 ) {
		$order_amount['breakdown'] = array(
			'item_total' => array(
				'currency_code' => edd_get_currency(),
				'value'         => (string) edd_sanitize_amount( $purchase_data['price'] - $purchase_data['tax'] )
			),
			'tax_total'  => array(
				'currency_code' => edd_get_currency(),
				'value'         => (string) edd_sanitize_amount( $purchase_data['tax'] ),
			)
		);
	}

	return array(
		'reference_id' => $payment_args['purchase_key'],
		'amount'       => $order_amount,
		'custom_id'    => $payment_id
	);
}

/**
 * Gets the PayPal purchase units. The order breakdown includes the order items, tax, and discount.
 *
 * @since 2.11.2
 * @param int   $payment_id    The payment/order ID.
 * @param array $purchase_data The array of purchase data.
 * @param array $payment_args  The array created to insert the payment into the database.
 * @return array
 */
function get_order_purchase_units( $payment_id, $purchase_data, $payment_args ) {

	$currency       = edd_get_currency();
	$order_subtotal = $purchase_data['subtotal'];
	$items          = get_order_items( $purchase_data );
	// Adjust the order subtotal if any items are discounted.
	foreach ( $items as &$item ) {
		// A discount can be negative, so cast it to an absolute value for comparison.
		if ( (float) abs( $item['discount'] ) > 0 ) {
			$order_subtotal -= $item['discount'];
		}

		// The discount amount is not passed to PayPal as part of the $item.
		unset( $item['discount'] );
	}

	$discount = 0;
	// Fees which are not item specific need to be added to the PayPal data as order items.
	if ( ! empty( $purchase_data['fees'] ) ) {
		foreach ( $purchase_data['fees'] as $fee ) {
			if ( ! empty( $fee['download_id'] ) ) {
				continue;
			}
			// Positive fees.
			if ( floatval( $fee['amount'] ) > 0 ) {
				$items[]         = array(
					'name'        => stripslashes_deep( html_entity_decode( wp_strip_all_tags( $fee['label'] ), ENT_COMPAT, 'UTF-8' ) ),
					'unit_amount' => array(
						'currency_code' => $currency,
						'value'         => (string) edd_sanitize_amount( $fee['amount'] ),
					),
					'quantity'    => 1,
				);
				$order_subtotal += abs( $fee['amount'] );
			} else {
				// This is a negative fee (discount) not assigned to a specific Download
				$discount += abs( $fee['amount'] );
			}
		}
	}

	$order_amount = array(
		'currency_code' => $currency,
		'value'         => (string) edd_sanitize_amount( $purchase_data['price'] ),
		'breakdown'     => array(
			'item_total' => array(
				'currency_code' => $currency,
				'value'         => (string) edd_sanitize_amount( $order_subtotal ),
			),
		),
	);

	$tax = (float) $purchase_data['tax'] > 0 ? $purchase_data['tax'] : 0;
	if ( $tax > 0 ) {
		$order_amount['breakdown']['tax_total'] = array(
			'currency_code' => $currency,
			'value'         => (string) edd_sanitize_amount( $tax ),
		);
	}

	// This is only added by negative global fees.
	if ( $discount > 0 ) {
		$order_amount['breakdown']['discount'] = array(
			'currency_code' => $currency,
			'value'         => (string) edd_sanitize_amount( $discount ),
		);
	}

	return array(
		wp_parse_args( array(
			'amount' => $order_amount,
			'items'  => $items
		), get_order_purchase_units_without_breakdown( $payment_id, $purchase_data, $payment_args ) )
	);
}

/**
 * Gets an array of order items, formatted for PayPal, from the $purchase_data.
 *
 * @since 2.11.2
 * @param array $purchase_data
 * @return array
 */
function get_order_items( $purchase_data ) {
	// Create an array of items for the order.
	$items = array();
	if ( ! is_array( $purchase_data['cart_details'] ) || empty( $purchase_data['cart_details'] ) ) {
		return $items;
	}
	$i = 0;
	foreach ( $purchase_data['cart_details'] as $item ) {
		$item_amount = ( $item['subtotal'] / $item['quantity'] ) - ( $item['discount'] / $item['quantity'] );

		if ( $item_amount <= 0 ) {
			$item_amount = 0;
		}

		$substr_func = function_exists( 'mb_substr' ) ? 'mb_substr' : 'substr';
		$name        = $substr_func( edd_get_cart_item_name( $item ), 0, 127 );

		$items[ $i ] = array(
			'name'        => stripslashes_deep( html_entity_decode( $name, ENT_COMPAT, 'UTF-8' ) ),
			'quantity'    => $item['quantity'],
			'unit_amount' => array(
				'currency_code' => edd_get_currency(),
				'value'         => (string) edd_sanitize_amount( $item_amount ),
			),
			'discount'    => $item['discount'], // This is unset later and never sent to PayPal.
		);
		if ( edd_use_skus() ) {
			$sku = edd_get_download_sku( $item['id'] );
			if ( ! empty( $sku ) && '-' !== $sku ) {
				$items[ $i ]['sku'] = $sku;
			}
		}
		$i++;
	}

	return $items;
}

/**
 * Attempts to detect if there's an item total mismatch. This means the individual item breakdowns don't
 * add up to our proposed totals.
 *
 * @link https://github.com/easydigitaldownloads/easy-digital-downloads/pull/8835#issuecomment-921759101
 * @internal Not intended for public use.
 *
 * @since 2.11.2
 *
 * @param object $response
 *
 * @return bool
 */
function _is_item_total_mismatch( $response ) {
	if ( ! isset( $response->details ) || ! is_array( $response->details ) ) {
		return false;
	}

	foreach( $response->details as $detail ) {
		if ( ! empty( $detail->issue ) && 'ITEM_TOTAL_MISMATCH' === strtoupper( $detail->issue ) ) {
			return true;
		}
	}

	return false;
}
