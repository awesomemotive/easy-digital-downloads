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
		$items[ $i ] = array(
			'name'        => stripslashes_deep( html_entity_decode( edd_get_cart_item_name( $item ), ENT_COMPAT, 'UTF-8' ) ),
			'quantity'    => $item['quantity'],
			'unit_amount' => array(
				'currency_code' => edd_get_currency(),
				'value'         => edd_sanitize_amount( $item_amount ),
			),
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
