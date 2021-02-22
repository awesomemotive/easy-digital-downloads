<?php
/**
 * Order Action Functions
 *
 * @package     EDD
 * @subpackage  Orders
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
use EDD\Adjustments\Adjustment;

defined( 'ABSPATH' ) || exit;

/**
 * Manually add an order.
 *
 * @since 3.0
 *
 * @param array $args Order form data.
 * @return void
 */
function edd_add_manual_order( $args = array() ) {
	// Bail if user cannot manage shop settings or no data was passed.
	if ( empty( $args ) || ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	// Set up parameters.
	$nonce = isset( $_POST['edd_add_order_nonce'] )
		? sanitize_text_field( $_POST['edd_add_order_nonce'] )
		: '';

	// Bail if nonce fails.
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'edd_add_order_nonce' ) ) {
		return;
	}

	// Get now one time to avoid microsecond issues
	$now = EDD()->utils->date( 'now', null, true )->timestamp;

	// Parse args.
	$data = wp_parse_args( $args, array(
		'downloads'               => array(),
		'adjustments'             => array(),
		'subtotal'                => 0.00,
		'tax'                     => 0.00,
		'total'                   => 0.00,
		'discount'                => 0.00,
		'edd-payment-status'      => 'complete',
		'payment_key'             => '',
		'gateway'                 => '',
		'transaction_id'          => '',
		'receipt'                 => '',
		'edd-payment-date'        => date( 'Y-m-d', $now ),
		'edd-payment-time-hour'   => date( 'G',     $now ),
		'edd-payment-time-min'    => date( 'i',     $now ),
		'edd-unlimited-downloads' => 0,
	) );

	/** Customer data *********************************************************/

	// Defaults
	$customer_id = 0;
	$user_id     = 0;
	$email       = '';
	$name        = '';

	// Create a new customer record.
	if ( isset( $data['edd-new-customer'] ) && 1 === absint( $data['edd-new-customer'] ) ) {

		// Sanitize first name
		$first_name = isset( $data['edd-new-customer-first-name'] )
			? sanitize_text_field( $data['edd-new-customer-first-name'] )
			: '';

		// Sanitize last name
		$last_name = isset( $data['edd-new-customer-last-name'] )
			? sanitize_text_field( $data['edd-new-customer-last-name'] )
			: '';

		// Combine
		$name = trim( $first_name . ' ' . $last_name );

		// Sanitize the email address
		$email = isset( $data['edd-new-customer-email'] )
			? sanitize_email( $data['edd-new-customer-email'] )
			: '';

		// Save to database.
		$customer_id = edd_add_customer( array(
			'name'  => $name,
			'email' => $email,
		) );

		$customer = edd_get_customer( $customer_id );

	// Existing customer.
	} elseif ( isset( $data['edd-new-customer'] ) && 0 === absint( $data['edd-new-customer'] ) && isset( $data['customer-id'] ) ) {
		$customer_id = absint( $data['customer-id'] );

		$customer = edd_get_customer( $customer_id );

		if ( $customer ) {
			$email   = $customer->email;
			$user_id = $customer->user_id;
			$name    = $customer->name;
		}
	}

	/** Insert order **********************************************************/

	// Parse order status.
	$status = sanitize_text_field( $data['edd-payment-status'] );

	if ( empty( $status ) || ! in_array( $status, array_keys( edd_get_payment_statuses() ), true ) ) {
		$status = 'complete';
	}

	// Parse date.
	$date = sanitize_text_field( $data['edd-payment-date'] );
	$hour = sanitize_text_field( $data['edd-payment-time-hour'] );

	// Restrict to our high and low.
	if ( $hour > 23 ) {
		$hour = 23;
	} elseif ( $hour < 0 ) {
		$hour = 00;
	}

	$minute = sanitize_text_field( $data['edd-payment-time-min'] );

	// Restrict to our high and low.
	if ( $minute > 59 ) {
		$minute = 59;
	} elseif ( $minute < 0 ) {
		$minute = 00;
	}

	// The date is entered in the WP timezone. We need to convert it to UTC prior to saving now.
	$date = edd_get_utc_equivalent_date( EDD()->utils->date( $date . ' ' . $hour . ':' . $minute . ':00', edd_get_timezone_id(), false ) );
	$date = $date->format( 'Y-m-d H:i:s' );

	// Get mode
	$mode = edd_is_test_mode()
		? 'test'
		: 'live';

	// Amounts
	$order_subtotal = floatval( $data['subtotal'] );
	$order_tax      = floatval( $data['tax'] );
	$order_discount = floatval( $data['discount'] );
	$order_total    = floatval( $data['total'] );

	$tax_rate  = false;
	// If taxes are enabled, get the tax rate for the order location.
	if ( edd_use_taxes() ) {
		$country = ! empty( $data['edd_order_address']['country'] )
			? $data['edd_order_address']['country']
			: false;

		$region = ! empty( $data['edd_order_address']['region'] )
			? $data['edd_order_address']['region']
			: false;

		$tax_rate = edd_get_tax_rate_by_location(
			array(
				'country' => $country,
				'region'  => $region,
			)
		);
	}

	// Add the order ID
	$order_id = edd_add_order(
		array(
			'status'       => 'pending', // Always insert as pending initially.
			'user_id'      => $user_id,
			'customer_id'  => $customer_id,
			'email'        => $email,
			'ip'           => sanitize_text_field( $data['ip'] ),
			'gateway'      => sanitize_text_field( $data['gateway'] ),
			'mode'         => $mode,
			'currency'     => edd_get_currency(),
			'payment_key'  => $data['payment_key'] ? sanitize_text_field( $data['payment_key'] ) : edd_generate_order_payment_key( $email ),
			'tax_rate_id'  => ! empty( $tax_rate->id ) ? $tax_rate->id : null,
			'subtotal'     => $order_subtotal,
			'tax'          => $order_tax,
			'discount'     => $order_discount,
			'total'        => $order_total,
			'date_created' => $date,
		)
	);

	// Attach order to the customer record.
	if ( ! empty( $customer ) ) {
		$customer->attach_payment( $order_id, false );
	}

	/** Insert order address **************************************************/

	if ( isset( $data['edd_order_address'] ) ) {

		// Parse args
		$address = wp_parse_args( $data['edd_order_address'], array(
			'name'        => $name,
			'address'     => '',
			'address2'    => '',
			'city'        => '',
			'postal_code' => '',
			'country'     => '',
			'region'      => '',
		) );

		$order_address_data             = $address;
		$order_address_data['order_id'] = $order_id;

		// Remove empty data.
		$order_address_data = array_filter( $order_address_data );

		// Add to edd_order_addresses table.
		edd_add_order_address( $order_address_data );

		// Maybe add the address to the edd_customer_addresses.
		$customer_address_data = $order_address_data;

		// We don't need to pass this data to edd_maybe_add_customer_address().
		unset( $customer_address_data['order_id'] );

		edd_maybe_add_customer_address( $customer->id, $customer_address_data );
	}

	/** Insert order items ****************************************************/

	// Any adjustments specific to an order item need to be added to the item.
	foreach ( $data['adjustments'] as $key => $adjustment ) {
		if ( 'order_item' === $adjustment['object_type'] ) {
			$data['downloads'][ $adjustment['object_id'] ]['adjustments'][] = $adjustment;

			unset( $data['adjustments'][ $key ] );
		}
	}

	if ( ! empty( $data['downloads'] ) ) {

		// Re-index downloads.
		$data['downloads'] = array_values( $data['downloads'] );

		$downloads = array_reverse( $data['downloads'] );

		foreach ( $downloads as $cart_key => $download ) {
			$d = edd_get_download( absint( $download['id'] ) );

			// Skip if download no longer exists
			if ( empty( $d ) ) {
				continue;
			}

			// Quantity.
			$quantity = isset( $download['quantity'] )
				? absint( $download['quantity'] )
				: 1;

			// Price ID.
			$price_id = isset( $download['price_id'] )
				? absint( $download['price_id'] )
				: false;

			// Amounts.
			$amount = isset( $download[ 'amount' ] )
				? floatval( $download[ 'amount' ] )
				: 0.00;

			$subtotal = isset( $download[ 'subtotal' ] )
				? floatval( $download[ 'subtotal' ] )
				: 0.00;

			$discount = isset( $download[ 'discount' ] )
				? floatval( $download[ 'discount' ] )
				: 0.00;

			$tax = isset( $download[ 'tax' ] )
				? floatval( $download[ 'tax' ] )
				: 0.00;

			$total = isset( $download[ 'total' ] )
				? floatval( $download[ 'total' ] )
				: 0.00;

			// Add to edd_order_items table.
			$order_item_id = edd_add_order_item( array(
				'order_id'     => $order_id,
				'product_id'   => absint( $download['id'] ),
				'product_name' => edd_get_download_name( $download['id'], absint( $price_id ) ),
				'price_id'     => absint( $price_id ),
				'cart_index'   => $cart_key,
				'type'         => 'download',
				'status'       => 'complete',
				'quantity'     => $quantity,
				'amount'       => $amount,
				'subtotal'     => $subtotal,
				'discount'     => $discount,
				'tax'          => $tax,
				'total'        => $total,
			) );

			if ( false !== $order_item_id ) {
				if ( isset( $download['adjustments'] ) ) {
					$order_item_adjustments = array_reverse( $download['adjustments'] );

					foreach ( $order_item_adjustments as $order_item_adjustment ) {

						// Discounts are not tracked at the Order Item level.
						if ( 'discount' === $order_item_adjustment['type'] ) {
							continue;
						}

						edd_add_order_adjustment( array(
							'object_id'   => $order_item_id,
							'object_type' => 'order_item',
							'type'        => sanitize_text_field( $order_item_adjustment['type'] ),
							'description' => sanitize_text_field( $order_item_adjustment['description'] ),
							'subtotal'    => floatval( $order_item_adjustment['subtotal'] ),
							'total'       => floatval( $order_item_adjustment['total'] ),
						) );
					}
				}

				// Increase the earnings for this download.
				edd_increase_earnings( absint( $download['id'] ), $total );
				edd_increase_purchase_count( absint( $download['id'] ), $quantity );
			}
		}
	}

	/** Insert adjustments ****************************************************/

	// Adjustments.
	if ( isset( $data['adjustments'] ) ) {
		$adjustments = array_reverse( $data['adjustments'] );

		foreach ( $adjustments as $adjustment ) {
			if ( 'order_item' === $adjustment['object_type'] ) {
				continue;
			}

			edd_add_order_adjustment( array(
				'object_id'   => $order_id,
				'object_type' => 'order',
				'type'        => sanitize_text_field( $adjustment['type'] ),
				'description' => sanitize_text_field( $adjustment['description'] ),
				'subtotal'    => floatval( $adjustment['subtotal'] ),
				'total'       => floatval( $adjustment['total'] ),
			) );
		}
	}

	// Discounts.
	if ( isset( $data['discounts'] ) ) {
		$discounts = array_reverse( $data['discounts'] );

		foreach ( $discounts as $discount ) {
			$d = edd_get_discount( absint( $discount['type_id'] ) );

			if ( empty( $d ) ) {
				continue;
			}

			// Store discount.
			edd_add_order_adjustment( array(
				'object_id'   => $order_id,
				'object_type' => 'order',
				'type_id'     => intval( $discount['type_id'] ),
				'type'        => 'discount',
				'description' => sanitize_text_field( $discount['code'] ),
				'subtotal'    => floatval( $discount['subtotal'] ),
				'total'       => floatval( $discount['total'] ),
			) );

			// Increase discount usage.
			$d->increase_usage();
		}
	}

	// Insert transaction ID.
	if ( ! empty( $data['transaction_id'] ) ) {
		edd_add_order_transaction( array(
			'object_id'      => $order_id,
			'object_type'    => 'order',
			'transaction_id' => sanitize_text_field( $data['transaction_id'] ),
			'gateway'        => sanitize_text_field( $data['gateway'] ),
			'status'         => 'complete',
			'total'          => $order_total,
		) );
	}

	// Unlimited downloads.
	if ( isset( $data['edd-unlimited-downloads'] ) && 1 === (int) $data['edd-unlimited-downloads'] ) {
		edd_update_order_meta( $order_id, 'unlimited_downloads', 1 );
	}

	if ( ! empty( $customer ) ) {
		$customer->recalculate_stats();
	}

	edd_increase_total_earnings( $order_total );

	// Setup order number.
	$order_number = '';

	if ( edd_get_option( 'enable_sequential' ) ) {
		$number = edd_get_next_payment_number();

		$order_number = edd_format_payment_number( $number );

		update_option( 'edd_last_payment_number', $number );

		// Update totals & maybe add order number.
		edd_update_order( $order_id, array(
			'order_number' => $order_number,
		) );
	}

	// Stop purchase receipt from being sent.
	if ( ! isset( $data['edd_order_send_receipt'] ) ) {
		remove_action( 'edd_complete_purchase', 'edd_trigger_purchase_receipt', 999 );
	}

	// Trigger edd_complete_purchase.
	if ( 'complete' === $status ) {
		edd_update_order_status( $order_id, $status );
	}

	// Redirect to `Edit Order` page.
	edd_redirect( edd_get_admin_url( array(
		'page'        => 'edd-payment-history',
		'view'        => 'view-order-details',
		'id'          => urlencode( $order_id ),
		'edd-message' => 'order_added',
	) ) );
}
add_action( 'edd_add_order', 'edd_add_manual_order' );
