<?php
/**
 * Payment Request: Functions
 *
 * @package EDD_Stripe
 * @since   2.8.0
 */

/**
 * Determines if Payment Requests are enabled.
 *
 * @since 2.8.0
 *
 * @param array|string $context Context the Payment Request Button is being output in.
 *                              Default empty, checks if any are enabled.
 * @return bool
 */
function edds_prb_is_enabled( $context = array() ) {
	// Stripe gateway is not active. Disabled.
	if ( false === edd_is_gateway_active( 'stripe' ) ) {
		return false;
	}

	if ( 'payment-elements' === edds_get_elements_mode() ) {
		return false;
	}

	// Gather allowed and enabled contexts.
	$allowed_contexts = array( 'single', 'archive', 'checkout' );
	$enabled_contexts = array_keys(
		(array) edd_get_option( 'stripe_prb', array() )
	);

	if ( ! is_array( $context ) ) {
		$context = array( $context );
	}

	// Nothing particular is being checked for; check if any values are checked.
	if ( empty( $context ) ) {
		return count( $enabled_contexts ) > 0;
	}

	// Passed context is not allowed. Disabled.
	if ( 0 === count( array_intersect( $context, $allowed_contexts ) ) ) {
		return false;
	}

	// Passed context is not enabled in setting. Disabled.
	if ( 0 === count( array_intersect( $context, $enabled_contexts ) ) ) {
		return false;
	}

	// Taxes are enabled. Disabled.
	$taxes = edd_use_taxes();

	if ( true === $taxes ) {
		return false;
	}

	// Recurring is enabled and a trial is in the cart. Disabled.
	//
	// Disabling for cart context here to avoid further adjusting the already
	// complex filtering of active gateways in checkout.php
	if (
		function_exists( 'edd_recurring' ) &&
		edd_recurring()->cart_has_free_trial()
	) {
		return false;
	}

	return true;
}

/**
 * Retrieves data for a Payment Request Button for a single Download.
 *
 * @since 2.8.0
 *
 * @param int       $download_id Download ID.
 * @param false|int $price_id Price ID. Default will be used if not set. Default false.
 * @param int       $quantity Quantity. Default 1.
 * @return array Payment Request Button data.
 */
function edds_prb_get_download_data( $download_id, $price_id = false, $quantity = 1 ) {
	$data  = array(
		'currency'      => strtolower( edd_get_currency() ),
		'country'       => strtoupper( edd_get_shop_country() ),
		'total'         => array(),
		'display-items' => array(),
	);

	$download = edd_get_download( $download_id );

	// Return early if no Download can be found.
	if ( ! $download ) {
		return array();
	}

	// Hacky way to ensure we don't display quantity for Recurring
	// downloads. The quantity field is output incorrectly.
	//
	// https://github.com/easydigitaldownloads/edd-recurring/issues/880
	if ( defined( 'EDD_RECURRING_VERSION' ) ) {
		$recurring = false;

		if ( false !== $price_id ) {
			$recurring = edd_recurring()->is_price_recurring( $download_id, $price_id );
		} else {
			$recurring = edd_recurring()->is_recurring( $download_id );
		}

		if ( true === $recurring ) {
			$quantity = 1;
		}
	}

	// Find price.
	$variable_pricing = $download->has_variable_prices();
	$price            = 0;

	if ( $variable_pricing ) {
		if ( false === $price_id ) {
			$price_id = edd_get_default_variable_price( $download_id );
		}

		$prices = $download->prices;

		$price    = isset( $prices[ $price_id ] )
			? $prices[ $price_id ]['amount']
			: false;

		$name = sprintf(
			'%1$s - %2$s',
			$download->get_name(),
			edd_get_price_option_name( $download->ID, $price_id )
		);
	} else {
		$price = $download->price;
		$name  = $download->get_name();
	}

	if ( false === edds_is_zero_decimal_currency() ) {
		$price = round( $price * 100 );
	}

	$price = ( $price * $quantity );

	// Add total.
	$data['total'] = array(
		'label'  => __( 'Total', 'easy-digital-downloads' ),
		'amount' => $price,
	);

	// Add Display items.
	$has_quantity = edd_item_quantities_enabled() && ! edd_download_quantities_disabled( $download_id );

	$quantity = true === $has_quantity
		? $quantity
		: 1;

	$data['display-items'][] = array(
		'label'  => sprintf(
			'%s%s',
			strip_tags( $name ),
			/* translators: %d: Quantity.*/
			( $quantity > 1 ? sprintf( __( ' × %d', 'easy-digital-downloads' ), $quantity ) : '' )
		),
		'amount' => $price,
	);

	return $data;
}

/**
 * Retrieves data for a Payment Request Button for the cart.
 *
 * @since 2.8.0
 *
 * @return array Payment Request Button data.
 */
function edds_prb_get_cart_data() {
	$data  = array(
		'currency'      => strtolower( edd_get_currency() ),
		'country'       => strtoupper( edd_get_shop_country() ),
		'total'         => array(),
		'display-items' => array(),
	);

	$total = edd_get_cart_total();

	if ( false === edds_is_zero_decimal_currency() ) {
		$total = round( $total * 100 );
	}

	// Add total.
	$data['total'] = array(
		'label'  => __( 'Total', 'easy-digital-downloads' ),
		'amount' => $total,
	);

	// Add Display items.
	$cart_items = edd_get_cart_contents();

	foreach ( $cart_items as $key => $item ) {
		$has_quantity = edd_item_quantities_enabled() && ! edd_download_quantities_disabled( $item['id'] );

		$quantity = true === $has_quantity
			? edd_get_cart_item_quantity( $item['id'], $item['options'] )
			: 1;

		$price = edd_get_cart_item_price( $item['id'], $item['options'] );

		if ( false === edds_is_zero_decimal_currency() ) {
			$price = round( $price * 100 );
		}

		$price = ( $price * $quantity );

		$data['display-items'][] = array(
			'label'  => sprintf(
				'%s%s',
				strip_tags( edd_get_cart_item_name( $item ) ),
				( $quantity > 1 ? sprintf( __( ' × %d', 'easy-digital-downloads' ), $quantity ) : '' )
			),
			'amount' => $price,
		);
	}

	return $data;
}
