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
defined( 'ABSPATH' ) || exit;

/**
 * Manually add an order.
 *
 * @since 3.0
 *
 * @param array $args Order form data.
 * @return int|bool Order ID if successful, false otherwise.
 */
function edd_add_manual_order( $args = array() ) {

	// Bail if user cannot manage shop settings or no data was passed.
	if ( empty( $args ) || ! current_user_can( 'manage_shop_settings' ) ) {
		return false;
	}

	// Set up parameters.
	$nonce = isset( $_POST['edd_add_order_nonce'] )
		? sanitize_text_field( $_POST['edd_add_order_nonce'] )
		: '';

	// Bail if nonce fails.
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'edd_add_order_nonce' ) ) {
		return false;
	}

	// Get now one time to avoid microsecond issues
	$now = EDD()->utils->date( 'now' )->timestamp;

	// Parse args.
	$data = wp_parse_args( $args, array(
		'downloads'               => array(),
		'edd-payment-status'      => 'publish',
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
		$name = $first_name . ' ' . $last_name;

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
		}
	}

	/** Insert order **********************************************************/

	// Parse order status.
	$status = sanitize_text_field( $data['edd-payment-status'] );

	if ( empty( $status ) || ! in_array( $status, array_keys( edd_get_payment_statuses() ), true ) ) {
		$status = 'publish';
	}

	// Parse date.
	$date = $data['edd-payment-date'] . ' ' . $data['edd-payment-time-hour'] . ':' . $data['edd-payment-time-min'];

	// Get mode
	$mode = edd_is_test_mode()
		? 'test'
		: 'live';

	// Get completed date if publish
	$completed = ( 'publish' === $data['edd-payment-status'] )
		? $date
		: '';

	// Add the order ID
	$order_id = edd_add_order( array(
		'status'         => 'pending', // Always insert as pending initially.
		'user_id'        => $user_id,
		'customer_id'    => $customer_id,
		'email'          => $email,
		'ip'             => sanitize_text_field( $data['ip'] ),
		'gateway'        => sanitize_text_field( $data['gateway'] ),
		'mode'           => $mode,
		'currency'       => edd_get_currency(),
		'payment_key'    => sanitize_text_field( $data['payment_key'] ),
		'date_created'   => $date,
		'date_completed' => $completed,
	) );

	// Attach order to the customer record.
	if ( ! empty( $customer ) ) {
		$customer->attach_payment( $order_id, false );
	}

	// Declare variables to store amounts for the order.
	$order_subtotal = 0.00;
	$total_tax      = 0.00;
	$total_discount = 0.00;
	$order_total    = 0.00;

	/** Insert order address **************************************************/

	if ( isset( $data['edd_order_address'] ) ) {

		// Parse args
		$address = wp_parse_args( $data['edd_order_address'], array(
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

	if ( ! empty( $data['downloads'] ) ) {

		// Re-index downloads.
		$data['downloads'] = array_values( $data['downloads'] );

		foreach ( $data['downloads'] as $cart_key => $download ) {
			$d = edd_get_download( absint( $download['id'] ) );

			// Skip if download no longer exists
			if ( empty( $d ) ) {
				continue;
			}

			$discount = 0.00;
			$tax      = 0.00;

			// Quantity.
			$quantity = isset( $download['quantity'] )
				? absint( $download['quantity'] )
				: 1;

			// Price ID.
			$price_id = isset( $download['price_id'] )
				? absint( $download['price_id'] )
				: false;

			// Fetch variable price.
			if ( $d->has_variable_prices() && false !== $price_id ) {
				$prices = $d->get_prices();

				if ( isset( $prices[ $price_id ] ) ) {
					$amount = $prices[ $price_id ]['amount'];
				} else {
					$amount   = edd_get_lowest_price_option( $d->ID );
					$price_id = edd_get_lowest_price_id( $d->ID );
				}

			// Fetch flat price.
			} else {
				$amount = $d->get_price();
			}

			$amount   = isset( $data['edd_add_order_override'] )
				? floatval( $download['total'] )
				: floatval( $amount );
			$subtotal = floatval( $amount * $quantity );

			// Apply percent discounts.
			if ( isset( $data['adjustments']['discount'] ) ) {
				$discounts = wp_filter_object_list( $data['adjustments']['discount'], array( 'type' => 'percent' ) );

				if ( ! empty( $discounts ) ) {
					foreach ( $discounts as $discount ) {
						$dis = edd_get_discount( absint( $discount['id'] ) );

						// Skip if discount not found.
						if ( empty( $dis ) ) {
							continue;
						}

						$discount = $subtotal * ( $dis->amount / 100 );
					}
				}
			}

			if ( edd_use_taxes() ) {
				$tax = edd_prices_include_tax()
					? 0.00
					: edd_calculate_tax( $subtotal - $discount, $address['country'], $address['region'] );

				$tax = isset( $data['edd_add_order_override'] )
					? floatval( $download['tax'] )
					: $tax;
			}

			// Calculate total.
			$total = isset( $data['edd_add_order_override'] )
				? $download['total']
				: floatval( $subtotal - $discount + $tax );

			// Add to edd_order_items table.
			edd_add_order_item( array(
				'order_id'     => $order_id,
				'product_id'   => absint( $download['id'] ),
				'product_name' => $d->post_title,
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

			// Increase the earnings for this download.
			edd_increase_earnings( absint( $download['id'] ), $total );
			edd_increase_purchase_count( absint( $download['id'] ), $quantity );

			// Update running totals.
			$order_subtotal += $subtotal;
			$total_tax      += $tax;
			$total_discount += $discount;
			$order_total    += $total;
		}
	}

	/** Insert adjustments ****************************************************/

	// Credit needs to be applied first.
	if ( ! empty( $data['adjustments']['credit'] ) ) {
		foreach ( $data['adjustments']['credit'] as $adjustment ) {
			edd_add_order_adjustment( array(
				'object_id'   => $order_id,
				'object_type' => 'order',
				'type'        => 'credit',
				'subtotal'    => floatval( $adjustment['amount'] ),
				'total'       => floatval( $adjustment['amount'] ),
			) );

			// Subtract from order total.
			$order_total -= floatval( $adjustment['amount'] );
		}
	}

	// Discounts are applied last.
	if ( ! empty( $data['adjustments']['discount'] ) ) {
		foreach ( $data['adjustments']['discount'] as $adjustment ) {
			$discount = edd_get_discount( absint( $adjustment['id'] ) );

			// Skip if discount doesn't exist
			if ( empty( $discount ) ) {
				continue;
			}

			// Only add flat discounts to $total_discount.
			if ( 'flat' === $discount->amount_type ) {
				$amount          = floatval( $discount->amount );
				$total_discount += $amount;
			} else {
				$amount = floatval( $order_subtotal * ( $discount->amount / 100 ) );
			}

			// Store discount.
			edd_add_order_adjustment( array(
				'object_id'   => $order_id,
				'object_type' => 'order',
				'type_id'     => $discount->id,
				'type'        => 'discount',
				'description' => $discount->code,
				'subtotal'    => $amount,
				'total'       => $amount,
			) );

			// Increase discount usage.
			$discount->increase_usage();

			// Subtract from order total.
			$order_total -= $amount;
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

	$customer->recalculate_stats();
	edd_increase_total_earnings( $order_total );

	// Setup order number.
	$order_number = '';

	if ( edd_get_option( 'enable_sequential' ) ) {
		$number = edd_get_next_payment_number();

		$order_number = edd_format_payment_number( $number );

		update_option( 'edd_last_payment_number', $number );
	}

	// Update totals & maybe add order number.
	edd_update_order( $order_id, array(
		'order_number' => $order_number,
		'subtotal'     => $order_subtotal,
		'tax'          => $total_tax,
		'discount'     => $total_discount,
		'total'        => $order_total,
	) );

	// Stop purchase receipt from being sent.
	if ( ! isset( $data['edd_order_send_receipt'] ) ) {
		remove_action( 'edd_complete_purchase', 'edd_trigger_purchase_receipt', 999 );
	}

	// Trigger edd_complete_purchase.
	if ( 'publish' === $status ) {
		edd_update_order_status( $order_id, $status );
	}

	// Redirect to `Edit Order` page.
	edd_redirect( edd_get_admin_url( array(
		'page' => 'edd-payment-history',
		'view' => 'view-order-details',
		'id'   => $order_id,
	) ) );
}
add_action( 'edd_add_order', 'edd_add_manual_order' );
