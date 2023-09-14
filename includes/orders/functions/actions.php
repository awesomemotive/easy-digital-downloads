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
	$order_data = wp_parse_args( $args, array(
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

	// Defaults.
	$customer_id = 0;
	$user_id     = 0;
	$email       = '';
	$name        = '';

	// Create a new customer record.
	if ( isset( $order_data['edd-new-customer'] ) && 1 === absint( $order_data['edd-new-customer'] ) ) {

		// Sanitize first name.
		$first_name = isset( $order_data['edd-new-customer-first-name'] )
			? sanitize_text_field( $order_data['edd-new-customer-first-name'] )
			: '';

		// Sanitize last name.
		$last_name = isset( $order_data['edd-new-customer-last-name'] )
			? sanitize_text_field( $order_data['edd-new-customer-last-name'] )
			: '';

		// Combine.
		$name = trim( $first_name . ' ' . $last_name );

		// Sanitize the email address.
		$email = isset( $order_data['edd-new-customer-email'] )
			? sanitize_email( $order_data['edd-new-customer-email'] )
			: '';

		$new_customer_args = array(
			'name'  => $name,
			'email' => $email,
		);

		// Determine if there is an existing user with this email address.
		$possible_user = get_user_by( 'email', $email );
		if ( $possible_user instanceof WP_User ) {
			$new_customer_args['user_id'] = $possible_user->ID;
		}

		// Save to database.
		$customer_id = edd_add_customer(
			$new_customer_args
		);

		$customer = edd_get_customer( $customer_id );

	// Existing customer.
	} elseif ( isset( $order_data['edd-new-customer'] ) && 0 === absint( $order_data['edd-new-customer'] ) && isset( $order_data['customer-id'] ) ) {
		$customer_id = absint( $order_data['customer-id'] );
		$customer    = edd_get_customer( $customer_id );

		if ( $customer ) {
			$email   = $customer->email;
			$user_id = $customer->user_id;
			$name    = $customer->name;
		}
	}

	/** Insert order **********************************************************/

	// Parse order status.
	$status = sanitize_text_field( $order_data['edd-payment-status'] );

	if ( empty( $status ) || ! in_array( $status, array_keys( edd_get_payment_statuses() ), true ) ) {
		$status = 'complete';
	}

	// Get the date string.
	$date_string = EDD()->utils->get_date_string(
		sanitize_text_field( $order_data['edd-payment-date'] ),
		sanitize_text_field( $order_data['edd-payment-time-hour'] ),
		sanitize_text_field( $order_data['edd-payment-time-min'] )
	);

	// The date is entered in the WP timezone. We need to convert it to UTC prior to saving now.
	$date = edd_get_utc_date_string( $date_string );

	// Get mode
	$mode = edd_is_test_mode()
		? 'test'
		: 'live';

	// Amounts
	$order_subtotal = floatval( $order_data['subtotal'] );
	$order_tax      = floatval( $order_data['tax'] );
	$order_discount = floatval( $order_data['discount'] );
	$order_total    = floatval( $order_data['total'] );

	$tax_rate  = false;
	// If taxes are enabled, get the tax rate for the order location.
	if ( edd_use_taxes() ) {
		$country = ! empty( $order_data['edd_order_address']['country'] )
			? $order_data['edd_order_address']['country']
			: false;

		$region = ! empty( $order_data['edd_order_address']['region'] )
			? $order_data['edd_order_address']['region']
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
			'ip'           => sanitize_text_field( $order_data['ip'] ),
			'gateway'      => sanitize_text_field( $order_data['gateway'] ),
			'mode'         => $mode,
			'currency'     => edd_get_currency(),
			'payment_key'  => $order_data['payment_key'] ? sanitize_text_field( $order_data['payment_key'] ) : edd_generate_order_payment_key( $email ),
			'tax_rate_id'  => ! empty( $tax_rate->id ) ? $tax_rate->id : null,
			'subtotal'     => $order_subtotal,
			'tax'          => $order_tax,
			'discount'     => $order_discount,
			'total'        => $order_total,
			'date_created' => $date,
			'order_number' => edd_set_order_number(),
		)
	);

	// Attach order to the customer record.
	if ( ! empty( $customer ) ) {
		$customer->attach_payment( $order_id, false );
	}

	// If we have tax, but no tax rate, manually save the percentage.
	if ( empty( $tax_rate->id ) && $order_tax > 0 ) {
		$tax_rate_percentage = $order_data['tax_rate'];
		if ( ! empty( $tax_rate_percentage ) ) {
			if ( $tax_rate_percentage > 0 && $tax_rate_percentage < 1 ) {
				$tax_rate_percentage = $tax_rate_percentage * 100;
			}

			edd_update_order_meta( $order_id, 'tax_rate', $tax_rate_percentage );
		}
	}

	/** Insert order address **************************************************/

	if ( isset( $order_data['edd_order_address'] ) ) {

		// Parse args
		$address = wp_parse_args( $order_data['edd_order_address'], array(
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
	foreach ( $order_data['adjustments'] as $key => $adjustment ) {
		if ( 'order_item' === $adjustment['object_type'] ) {
			$order_data['downloads'][ $adjustment['object_id'] ]['adjustments'][] = $adjustment;

			unset( $order_data['adjustments'][ $key ] );
		}
	}

	if ( ! empty( $order_data['downloads'] ) ) {

		// Re-index downloads.
		$order_data['downloads'] = array_values( $order_data['downloads'] );

		$downloads = array_reverse( $order_data['downloads'] );

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
			$price_id = isset( $download['price_id'] ) && is_numeric( $download['price_id'] )
				? absint( $download['price_id'] )
				: null;

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
				'price_id'     => $price_id,
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

					foreach ( $order_item_adjustments as $index => $order_item_adjustment ) {

						// Discounts are not tracked at the Order Item level.
						if ( 'discount' === $order_item_adjustment['type'] ) {
							continue;
						}

						$type_key = ! empty( $order_item_adjustment['description'] )
							? sanitize_text_field( strtolower( sanitize_title( $order_item_adjustment['description'] ) ) )
							: $index;

							$order_item_adjustment_subtotal = floatval( $order_item_adjustment['subtotal'] );
							$order_item_adjustment_tax      = floatval( $order_item_adjustment['tax'] );
							$order_item_adjustment_total    = floatval( $order_item_adjustment['total'] );

						edd_add_order_adjustment( array(
							'object_id'   => $order_item_id,
							'object_type' => 'order_item',
							'type'        => sanitize_text_field( $order_item_adjustment['type'] ),
							'type_key'    => $type_key,
							'description' => sanitize_text_field( $order_item_adjustment['description'] ),
							'subtotal'    => $order_item_adjustment_subtotal,
							'tax'         => $order_item_adjustment_tax,
							'total'       => $order_item_adjustment_total,
						) );
					}
				}
			}
		}
	}

	/** Insert adjustments ****************************************************/

	// Adjustments.
	if ( isset( $order_data['adjustments'] ) ) {
		$adjustments = array_reverse( $order_data['adjustments'] );

		foreach ( $adjustments as $index => $adjustment ) {
			if ( 'order_item' === $adjustment['object_type'] ) {
				continue;
			}

			$type_key = ! empty( $adjustment['description'] )
				? sanitize_text_field( strtolower( sanitize_title( $adjustment['description'] ) ) )
				: $index;

			$adjustment_subtotal = floatval( $adjustment['subtotal'] );
			$adjustment_tax      = floatval( $adjustment['tax'] );
			$adjustment_total    = floatval( $adjustment['total'] );

			edd_add_order_adjustment( array(
				'object_id'   => $order_id,
				'object_type' => 'order',
				'type'        => sanitize_text_field( $adjustment['type'] ),
				'type_key'    => $type_key,
				'description' => sanitize_text_field( $adjustment['description'] ),
				'subtotal'    => $adjustment_subtotal,
				'tax'         => $adjustment_tax,
				'total'       => $adjustment_total,
			) );
		}
	}

	// Discounts.
	if ( isset( $order_data['discounts'] ) ) {
		$discounts = array_reverse( $order_data['discounts'] );

		foreach ( $discounts as $discount ) {
			$d = edd_get_discount( absint( $discount['type_id'] ) );

			if ( empty( $d ) ) {
				continue;
			}

			$discount_subtotal = floatval( $discount['subtotal'] );
			$discount_total    = floatval( $discount['total'] );

			// Store discount.
			edd_add_order_adjustment( array(
				'object_id'   => $order_id,
				'object_type' => 'order',
				'type_id'     => intval( $discount['type_id'] ),
				'type'        => 'discount',
				'description' => sanitize_text_field( $discount['code'] ),
				'subtotal'    => $discount_subtotal,
				'total'       => $discount_total,
			) );
		}
	}

	// Insert transaction ID.
	if ( ! empty( $order_data['transaction_id'] ) ) {
		edd_add_order_transaction( array(
			'object_id'      => $order_id,
			'object_type'    => 'order',
			'transaction_id' => sanitize_text_field( $order_data['transaction_id'] ),
			'gateway'        => sanitize_text_field( $order_data['gateway'] ),
			'status'         => 'complete',
			'total'          => $order_total,
		) );
	}

	// Unlimited downloads.
	if ( isset( $order_data['edd-unlimited-downloads'] ) && 1 === (int) $order_data['edd-unlimited-downloads'] ) {
		edd_update_order_meta( $order_id, 'unlimited_downloads', 1 );
	}

	// Stop purchase receipt from being sent.
	if ( ! isset( $order_data['edd_order_send_receipt'] ) ) {
		remove_action( 'edd_complete_purchase', 'edd_trigger_purchase_receipt', 999 );
	}

	// Trigger edd_complete_purchase.
	if ( 'complete' === $status ) {
		edd_update_order_status( $order_id, $status );
	}

	/**
	 * Action hook which runs after a manual order has been added to the database.
	 *
	 * @since 3.0
	 * @param int   $order_id   The new order ID.
	 * @param array $order_data The array of order data.
	 * @param array $args       The original form data.
	 */
	do_action( 'edd_post_add_manual_order', $order_id, $order_data, $args );

	// Redirect to `Edit Order` page.
	edd_redirect( edd_get_admin_url( array(
		'page'        => 'edd-payment-history',
		'view'        => 'view-order-details',
		'id'          => urlencode( $order_id ),
		'edd-message' => 'order_added',
	) ) );
}
add_action( 'edd_add_order', 'edd_add_manual_order' );
