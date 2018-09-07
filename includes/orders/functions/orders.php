<?php
/**
 * Order Functions
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
 * Add an order.
 *
 * @since 3.0
 *
 * @param array $data
 * @return int|false ID of newly created order, false on error.
 */
function edd_add_order( $data = array() ) {
	$orders = new EDD\Database\Queries\Order();

	return $orders->add_item( $data );
}

/**
 * Delete an order.
 *
 * @since 3.0
 *
 * @param int $order_id Order ID.
 * @return int
 */
function edd_delete_order( $order_id = 0 ) {
	$orders = new EDD\Database\Queries\Order();

	return $orders->delete_item( $order_id );
}

/**
 * Destroy an order.
 *
 * Completely deletes an order, and the items and adjustments with it.
 *
 * @todo switch to _destroy_ for items & adjustments
 *
 * @since 3.0
 *
 * @param int $order_id Order ID.
 * @return int
 */
function edd_destroy_order( $order_id = 0 ) {

	// Get items.
	$items = edd_get_order_items( array(
		'order_id'      => $order_id,
		'no_found_rows' => true,
	) );

	// Destroy items (and their adjustments).
	if ( ! empty( $items ) ) {
		foreach ( $items as $item ) {
			edd_delete_order_item( $item->id );
		}
	}

	// Get adjustments.
	$adjustments = edd_get_order_adjustments( array(
		'object_id'     => $order_id,
		'object_type'   => 'order',
		'no_found_rows' => true,
	) );

	// Destroy adjustments.
	if ( ! empty( $adjustments ) ) {
		foreach ( $adjustments as $adjustment ) {
			edd_delete_order_adjustment( $adjustment->id );
		}
	}

	// Get address.
	$address = edd_get_order_address_by( 'order_id', $order_id );

	// Destroy address.
	if ( $address ) {
		edd_delete_order_address( $address->id );
	}

	// Delete the order
	return edd_delete_order( $order_id );
}

/**
 * Update an order.
 *
 * @since 3.0
 *
 * @param int   $order_id Order ID.
 * @param array $data   Updated order data.
 * @return bool Whether or not the order was updated.
 */
function edd_update_order( $order_id = 0, $data = array() ) {
	$orders = new EDD\Database\Queries\Order();

	return $orders->update_item( $order_id, $data );
}

/**
 * Get an order by ID.
 *
 * @since 3.0
 *
 * @param int $order_id Order ID.
 * @return EDD\Orders\Order Order object.
 */
function edd_get_order( $order_id = 0 ) {
	return edd_get_order_by( 'id', $order_id );
}

/**
 * Get an order by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 *
 * @return \EDD\Orders\Order Order object.
 */
function edd_get_order_by( $field = '', $value = '' ) {

	// Instantiate a query object
	$orders = new EDD\Database\Queries\Order();

	// Get an item
	return $orders->get_item_by( $field, $value );
}

/**
 * Query for orders.
 *
 * @since 3.0
 *
 * @param array $args
 * @return EDD\Orders\Order[]
 */
function edd_get_orders( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30,
	) );

	// Instantiate a query object
	$orders = new EDD\Database\Queries\Order();

	// Return items
	return $orders->query( $r );
}

/**
 * Count orders.
 *
 * @since 3.0
 *
 * @param array $args
 * @return int
 */
function edd_count_orders( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count' => true,
	) );

	// Query for count(s)
	$orders = new EDD\Database\Queries\Order( $r );

	// Return count(s)
	return absint( $orders->found_items );
}

/**
 * Query for and return array of order counts, keyed by status.
 *
 * @since 3.0
 *
 * @return array
 */
function edd_get_order_counts( $args = array() ) {

	// Parse arguments
	$r = wp_parse_args( $args, array(
		'count'   => true,
		'groupby' => 'status',
		'type'    => 'sale'
	) );

	// Query for count
	$counts = new EDD\Database\Queries\Order( $r );

	// Format & return
	return edd_format_counts( $counts, $r['groupby'] );
}

/** Helpers *******************************************************************/

/**
 * Check if an order can be recovered.
 *
 * @since 3.0
 *
 * @param int $order_id Order ID.
 * @return bool Whether or not the order can be recovered.
 */
function edd_is_order_recoverable( $order_id = 0 ) {
	$order = edd_get_order( $order_id );

	if ( ! $order ) {
		return false;
	}

	$recoverable_statuses = apply_filters( 'edd_recoverable_payment_statuses', array( 'pending', 'abandoned', 'failed' ) );

	$transaction_id = $order->get_transaction_id();

	if ( in_array( $order->status, $recoverable_statuses, true ) && empty( $transaction_id ) ) {
		return true;
	}

	return false;
}

/**
 * Update the status of an entire order.
 *
 * @since 3.0
 *
 * @param int    $order_id   Order ID.
 * @param string $new_status New order status.
 *
 * @return bool True if the status was updated successfully, false otherwise.
 */
function edd_update_order_status( $order_id = 0, $new_status = '' ) {

	// Bail if order and status are empty
	if ( empty( $order_id ) || empty( $new_status ) ) {
		return false;
	}

	// Get the order
	$order = edd_get_order( $order_id );

	// Bail if order not found
	if ( empty( $order ) ) {
		return false;
	}

	/**
	 * For backwards compatibility purposes, we need an instance of EDD_Payment so that the correct actions
	 * are invoked.
	 */
	$payment = edd_get_payment( $order_id );

	// Override to `publish`
	if ( in_array( $new_status, array( 'completed', 'complete' ), true ) ) {
		$new_status = 'publish';
	}

	// Get the old (current) status
	$old_status = $order->status;

	// We do not allow status changes if the status is the same to that stored in the database.
	// This prevents the `edd_update_payment_status` action from being triggered unnecessarily.
	if ( $old_status === $new_status ) {
		return false;
	}

	// Backwards compatibility
	$do_change = apply_filters( 'edd_should_update_payment_status', true,       $order_id, $new_status, $old_status );
	$do_change = apply_filters( 'edd_should_update_order_status',   $do_change, $order_id, $new_status, $old_status );

	$updated = false;

	if ( ! empty( $do_change ) ) {
		/**
		 * We need to update the status on the EDD_Payment instance so that the
		 * correct actions are invoked if the status is changing to something
		 * that requires interception by the payment gateway (e.g. refunds).
		 */
		$payment->status = $new_status;
		$updated         = $payment->save();
	}

	return $updated;
}

/**
 * Generate the correct parameters required to insert a new order into the database
 * based on the order details passed by the gateway.
 *
 * @since 3.0
 *
 * @param array $order_data Order data.
 *
 * @return int|bool Integer of order ID if successful, false otherwise.
 */
function edd_build_order( $order_data = array() ) {

	// Bail if no order data passed.
	if ( empty( $order_data ) ) {
		return false;
	}

	/* Order recovery ********************************************************/

	$resume_order   = false;
	$existing_order = EDD()->session->get( 'edd_resume_payment' );

	if ( ! empty( $existing_order ) ) {
		$order = edd_get_order( $existing_order );

		if ( $order ) {
			$recoverable_statuses = apply_filters( 'edd_recoverable_payment_statuses', array( 'pending', 'abandoned', 'failed' ) );

			$transaction_id = $order->get_transaction_id();

			if ( in_array( $order->status, $recoverable_statuses, true ) && empty( $transaction_id ) ) {
				$payment      = edd_get_payment( $existing_order );
				$resume_order = true;
			}
		}
	}

	if ( $resume_order ) {
		$payment->date = date( 'Y-m-d G:i:s', current_time( 'timestamp' ) );

		$payment->add_note( __( 'Payment recovery processed', 'easy-digital-downloads' ) );

		// Since things could have been added/removed since we first crated this...rebuild the cart details.
		foreach ( $payment->fees as $fee_index => $fee ) {
			$payment->remove_fee_by( 'index', $fee_index, true );
		}

		foreach ( $payment->downloads as $cart_index => $download ) {
			$item_args = array(
				'quantity'   => isset( $download['quantity'] ) ? $download['quantity'] : 1,
				'cart_index' => $cart_index,
			);
			$payment->remove_download( $download['id'], $item_args );
		}

		if ( strtolower( $payment->email ) !== strtolower( $order_data['user_info']['email'] ) ) {

			// Remove the payment from the previous customer.
			$previous_customer = new EDD_Customer( $payment->customer_id );
			$previous_customer->remove_payment( $payment->ID, false );

			// Redefine the email first and last names.
			$payment->email      = $order_data['user_info']['email'];
			$payment->first_name = $order_data['user_info']['first_name'];
			$payment->last_name  = $order_data['user_info']['last_name'];
		}

		// Remove any remainders of possible fees from items.
		$payment->save();
	}

	/** Setup order information ***********************************************/

	$gateway = ! empty( $order_data['gateway'] ) ? $order_data['gateway'] : '';
	$gateway = empty( $gateway ) && isset( $_POST['edd-gateway'] ) // WPCS: CSRF ok.
		? sanitize_key( $_POST['edd-gateway'] )
		: $gateway;

	if ( ! $resume_order ) {

		// Allow for post_date to be passed in.
		if ( isset( $order_data['post_date'] ) ) {
			$order_data['date_created'] = $order_data['post_date'];
			unset( $order_data['post_date'] );
		}
	}

	// Build order information based on data passed from the gateway.
	$order_args = array(
		'parent'       => ! empty( $order_data['parent'] ) ? absint( $order_data['parent'] ) : '',
		'order_number' => '',
		'status'       => ! empty( $order_data['status'] ) ? $order_data['status'] : 'pending',
		'user_id'      => $order_data['user_info']['id'],
		'email'        => $order_data['user_info']['email'],
		'ip'           => edd_get_ip(),
		'gateway'      => $gateway,
		'mode'         => edd_is_test_mode() ? 'test' : 'live',
		'currency'     => ! empty( $order_data['currency'] ) ? $order_data['currency'] : edd_get_currency(),
		'payment_key'  => $order_data['purchase_key'],
		'date_created' => ! empty( $order_data['date_created'] ) ? $order_data['date_created'] : '',
	);

	/** Setup customer ********************************************************/

	$customer = new stdClass();

	if ( did_action( 'edd_pre_process_purchase' ) && is_user_logged_in() ) {
		$customer = new EDD_Customer( get_current_user_id(), true );

		// Customer is logged in but used a different email to purchase so we need to assign that email address to their customer record.
		if ( ! empty( $customer->id ) && ( $order_args['email'] !== $customer->email ) ) {
			$customer->add_email( $order_args['email'] );
		}
	}

	if ( empty( $customer->id ) ) {
		$customer = new EDD_Customer( $order_args['email'] );

		if ( empty( $order_data['user_info']['first_name'] ) && empty( $order_data['user_info']['last_name'] ) ) {
			$name = $order_args['email'];
		} else {
			$name = $order_data['user_info']['first_name'] . ' ' . $order_data['user_info']['last_name'];
		}

		$customer->create( array(
			'name'    => $name,
			'email'   => $order_args['email'],
			'user_id' => $order_args['user_id'],
		) );
	}

	// If the customer name was initially empty, update the record to store the name used at checkout.
	if ( empty( $customer->name ) ) {
		$customer->update( array(
			'name' => $order_data['user_info']['first_name'] . ' ' . $order_data['user_info']['last_name'],
		) );
	}

	$order_args['customer_id'] = $customer->id;

	/** Insert order **********************************************************/

	// Add order into the edd_orders table.
	$order_id = true === $resume_order
		? $payment->ID
		: edd_add_order( $order_args );

	// Attach order to the customer record.
	$customer->attach_payment( $order_id, false );

	// Declare variables to store amounts for the order.
	$subtotal       = 0.00;
	$total_tax      = 0.00;
	$total_discount = 0.00;
	$total_fees     = 0.00;
	$order_total    = 0.00;

	/** Insert order address *************************************************/

	$order_data['user_info']['address'] = isset( $order_data['user_info']['address'] )
		? $order_data['user_info']['address']
		: array();

	$order_data['user_info']['address'] = wp_parse_args( $order_data['user_info']['address'], array(
		'line1'   => '',
		'line2'   => '',
		'city'    => '',
		'zip'     => '',
		'country' => '',
		'state'   => '',
	) );

	$order_address_data = array(
		'order_id'    => $order_id,
		'first_name'  => $order_data['user_info']['first_name'],
		'last_name'   => $order_data['user_info']['last_name'],
		'address'     => $order_data['user_info']['address']['line1'],
		'address2'    => $order_data['user_info']['address']['line2'],
		'city'        => $order_data['user_info']['address']['city'],
		'region'      => $order_data['user_info']['address']['state'],
		'country'     => $order_data['user_info']['address']['country'],
		'postal_code' => $order_data['user_info']['address']['zip'],
	);

	// Remove empty data.
	$order_address_data = array_filter( $order_address_data );

	// Add to edd_order_addresses table.
	edd_add_order_address( $order_address_data );

	// Maybe add the address to the edd_customer_addresses.
	$customer_address_data = $order_address_data;

	// We don't need to pass this data to edd_maybe_add_customer_address().
	unset( $customer_address_data['order_id'] );
	unset( $customer_address_data['first_name'] );
	unset( $customer_address_data['last_name'] );

	edd_maybe_add_customer_address( $customer->id, $customer_address_data );

	/** Insert order items ****************************************************/

	$decimal_filter = edd_currency_decimal_filter();

	if ( is_array( $order_data['cart_details'] ) && ! empty( $order_data['cart_details'] ) ) {
		foreach ( $order_data['cart_details'] as $key => $item ) {

			// First, we need to check that what is being added is a valid download.
			$download = edd_get_download( $item['id'] );

			// Skip if download is missing or not actually a download.
			if ( empty( $download ) || ( 'download' !== $download->post_type ) ) {
				continue;
			}

			// Get price ID.
			$price_id = isset( $item['item_number']['options']['price_id'] )
				? absint( $item['item_number']['options']['price_id'] )
				: 0;

			// Build a base array of information for each order item.
			$item['discount'] = isset( $item['discount'] )
				? $item['discount']
				: 0.00;

			$item['subtotal'] = isset( $item['subtotal'] )
				? $item['subtotal']
				: (float) $item['quantity'] * $item['item_price'];

			$order_item_args = array(
				'order_id'     => $order_id,
				'product_id'   => $item['id'],
				'product_name' => $item['name'],
				'price_id'     => $price_id,
				'cart_index'   => $key,
				'type'         => 'download',
				'status'       => ! empty( $order_data['status'] ) ? $order_data['status'] : 'pending',
				'quantity'     => $item['quantity'],
				'amount'       => $item['item_price'],
				'subtotal'     => $item['subtotal'],
				'discount'     => $item['discount'],
				'tax'          => $item['tax'],
				'total'        => $item['price'],
				'item_price'   => $item['item_price'], // Added for backwards compatibility
			);

			/**
			 * Allow the order item arguments to be filtered.
			 *
			 * This is here for backwards compatibility purposes.
			 *
			 * @since 3.0
			 *
			 * @param array $order_item_args Order item arguments.
			 * @param int   $download->ID    Download ID.
			 */
			$order_item_args = apply_filters( 'edd_payment_add_download_args', $order_item_args, $download->ID );
			$order_item_args = wp_parse_args( $order_item_args, array(
				'quantity'   => 1,
				'price_id'   => false,
				'amount'     => false,
				'item_price' => false,
				'discount'   => 0.00,
				'tax'        => 0.00,
			) );

			// The item_price key could have been changed by a filter.
			// This exists for backwards compatibility purposes.
			$order_item_args['amount'] = $order_item_args['item_price'];
			unset( $order_item_args['item_price'] );

			// Try to use what's passed in via the args.
			if ( false !== $order_item_args['amount'] ) {
				$item_price = $order_item_args['amount'];

				// Deal with variable pricing.
			} elseif ( $download->has_variable_prices() ) {
				$prices = $download->get_prices();

				if ( $order_item_args['price_id'] && array_key_exists( $order_item_args['price_id'], (array) $prices ) ) {
					$item_price = $prices[ $order_item_args['price_id'] ]['amount'];
				} else {
					$item_price                  = edd_get_lowest_price_option( $download->ID );
					$order_item_args['price_id'] = edd_get_lowest_price_id( $download->ID );
				}

				// Fallback to getting it directly.
			} else {
				$item_price = edd_get_download_price( $download->ID );
			}

			// Sanitize price & quantity.
			$item_price = edd_sanitize_amount( $item_price );
			$quantity   = edd_item_quantities_enabled()
				? absint( $order_item_args['quantity'] )
				: 1;

			// Subtotal needs to be updated with the sanitized amount.
			$order_item_args['subtotal'] = round( $item_price * $quantity, $decimal_filter );

			if ( edd_prices_include_tax() ) {
				$order_item_args['subtotal'] -= round( $order_item_args['tax'], $decimal_filter );
			}

			$total = $order_item_args['subtotal'] - $order_item_args['discount'] + $order_item_args['tax'];

			// Do not allow totals to go negative
			// TODO: probably remove for handling returns
			if ( $total < 0 ) {
				$total = 0;
			}

			// Sanitize all the amounts.
			$order_item_args['amount']   = round( $item_price,                  $decimal_filter );
			$order_item_args['subtotal'] = round( $order_item_args['subtotal'], $decimal_filter );
			$order_item_args['tax']      = round( $order_item_args['tax'],      $decimal_filter );
			$order_item_args['total']    = round( $total,                       $decimal_filter );

			$order_item_id = edd_add_order_item( $order_item_args );

			// Store order item fees as adjustments.
			if ( isset( $item['fees'] ) && ! empty( $item['fees'] ) ) {
				foreach ( $item['fees'] as $fee_id => $fee ) {

					$adjustment_data = array(
						'object_id'   => $order_item_id,
						'object_type' => 'order_item',
						'type'        => 'fee',
						'description' => $fee['label'],
						'subtotal'    => $fee['amount'],
						'total'       => $fee['amount'],
					);

					if ( isset( $fee['no_tax'] ) && ( true === $fee['no_tax'] ) ) {
						$adjustment_data['tax'] = 0.00;
					}

					// Add the adjustment.
					$adjustment_id = edd_add_order_adjustment( $adjustment_data );

					edd_add_order_adjustment_meta( $adjustment_id, 'fee_id', $fee_id );
					edd_add_order_adjustment_meta( $adjustment_id, 'download_id', $fee['download_id'] );

					if ( isset( $fee['price_id'] ) && ! is_null( $fee['price_id'] ) ) {
						edd_add_order_adjustment_meta( $adjustment_id, 'price_id', $fee['price_id'] );
					}
				}
			}

			// Maybe store order tax.
			if ( edd_use_taxes() ) {
				$country = ! empty( $order_data['user_info']['address']['country'] )
					? $order_data['user_info']['address']['country']
					: false;

				$state = ! empty( $order_data['user_info']['address']['state'] )
					? $order_data['user_info']['address']['state']
					: false;

				$zip = ! empty( $order_data['user_info']['address']['zip'] )
					? $order_data['user_info']['address']['zip']
					: false;

				$tax_rate = isset( $item['tax_rate'] )
					? floatval( $item['tax_rate'] )
					: edd_get_cart_tax_rate( $country, $state, $zip );

				if ( 0 < $tax_rate ) {

					// Always store tax rate, even if empty.
					edd_add_order_adjustment( array(
						'object_id'   => $order_item_id,
						'object_type' => 'order_item',
						'type'        => 'tax_rate',
						'total'       => $tax_rate,
					) );
				}
			}

			$subtotal       += (float) $order_item_args['subtotal'];
			$total_tax      += (float) $order_item_args['tax'];
			$total_discount += (float) $order_item_args['discount'];
		}
	}

	/** Insert order adjustments **********************************************/

	// Insert fees.
	$fees = edd_get_cart_fees();

	// Process fees.
	if ( ! empty( $fees ) ) {
		foreach ( $fees as $key => $fee ) {

			// Skip adding fee if it was specific to a download in the cart.
			if ( isset( $fee['download_id'] ) && ! empty( $fee['download_id'] ) ) {
				continue;
			}

			add_filter( 'edd_prices_include_tax', '__return_false' );

			$tax = ( isset( $fee['no_tax'] ) && false === $fee['no_tax'] ) || $fee['amount'] < 0
				? floatval( edd_calculate_tax( $fee['amount'] ) )
				: 0.00;

			remove_filter( 'edd_prices_include_tax', '__return_false' );

			$args = array(
				'object_id'   => $order_id,
				'object_type' => 'order',
				'type'        => 'fee',
				'description' => $fee['label'],
				'subtotal'    => floatval( $fee['amount'] ),
				'tax'         => $tax,
				'total'       => floatval( $fee['amount'] ) + $tax,
			);

			// Add the adjustment.
			$adjustment_id = edd_add_order_adjustment( $args );

			edd_add_order_adjustment_meta( $adjustment_id, 'fee_id', $key );

			if ( isset( $fee['price_id'] ) && ! is_null( $fee['price_id'] ) ) {
				edd_add_order_adjustment_meta( $adjustment_id, 'price_id', $fee['price_id'] );
			}

			$total_fees += (float) $fee['amount'];
			$total_tax  += $tax;
		}
	}

	// Insert discounts.
	$discounts = ! empty( $order_data['user_info']['discount'] )
		? $order_data['user_info']['discount']
		: array();

	if ( ! is_array( $discounts ) ) {
		/** @var string $discounts */
		$discounts = explode( ',', $discounts );
	}

	if ( ! empty( $discounts ) && ( 'none' !== $discounts[0] ) ) {
		/** @var array $discounts */
		foreach ( $discounts as $discount ) {
			$discount = edd_get_discount_by( 'code', $discount );

			if ( $discount ) {
				$discounted_amount = $subtotal - $discount->get_discounted_amount( $subtotal );

				edd_add_order_adjustment( array(
					'object_id'   => $order_id,
					'object_type' => 'order',
					'type_id'     => $discount->id,
					'type'        => 'discount',
					'description' => $discount,
					'subtotal'    => $discounted_amount,
					'total'       => $discounted_amount,
				) );
			}
		}
	}

	// Calculate order total (this needs more flexibility)
	$order_total =
		  $subtotal       // Total of all items
		- $total_discount // Total of all discounts
		+ $total_tax      // Total of all taxes
		+ $total_fees;    // Total of all fees

	// Setup order number.
	if ( edd_get_option( 'enable_sequential' ) ) {
		$number = edd_get_next_payment_number();

		$order_args['order_number'] = edd_format_payment_number( $number );

		update_option( 'edd_last_payment_number', $number );
	}

	// Update the order with all of the newly computed values.
	edd_update_order( $order_id, array(
		'order_number' => $order_args['order_number'],
		'subtotal'     => $subtotal,
		'tax'          => $total_tax,
		'discount'     => $total_discount,
		'total'        => $order_total,
	) );

	if ( edd_get_option( 'show_agree_to_terms', false ) && ! empty( $_POST['edd_agree_to_terms'] ) ) { // WPCS: CSRF ok.
		$order_data['agree_to_terms_time'] = current_time( 'timestamp' );
	}

	if ( edd_get_option( 'show_agree_to_privacy_policy', false ) && ! empty( $_POST['edd_agree_to_privacy_policy'] ) ) { // WPCS: CSRF ok.
		$order_data['agree_to_privacy_time'] = current_time( 'timestamp' );
	}

	/**
	 * Fires after an order has been inserted.
	 *
	 * @internal This hook exists for backwards compatibility.
	 *
	 * @since 1.0
	 *
	 * @param int   $order_id   ID of the new order.
	 * @param array $order_data Array of original order data.
	 */
	do_action( 'edd_insert_payment', $order_id, $order_data );

	/**
	 * Executes after an order has been fully built from the sum of its parts.
	 *
	 * @since 3.0
	 *
	 * @param int   $order_id   ID of the new order.
	 * @param array $order_data Array of original order data.
	 */
	do_action( 'edd_built_order', $order_id, $order_data );

	// Return order ID, or false
	return ! empty( $order_id )
		? $order_id
		: false;
}

/**
 * Clone an existing order.
 *
 * @since 3.0
 *
 * @param int     $order_id            Order ID.
 * @param boolean $clone_relationships True to clone order items and adjustments,
 *                                     false otherwise.
 * @param array $args                  Arguments that are used in place of cloned
 *                                     order attributes.
 *
 * @return int|boolean New order ID on success, false on failure.
 */
function edd_clone_order( $order_id = 0, $clone_relationships = false, $args = array() ) {

	// Bail if no order ID passed.
	if ( empty( $order_id ) ) {
		return false;
	}

	// Fetch the order.
	$order = edd_get_order( $order_id );

	// Bail if the order was not found.
	if ( ! $order ) {
		return false;
	}

	// Parse arguments.
	$r = wp_parse_args( $args, $order->to_array() );

	// Remove order ID and order number.
	unset( $r['id'] );
	unset( $r['order_number'] );

	// Remove dates.
	unset( $r['date_created'] );
	unset( $r['date_modified'] );
	unset( $r['date_completed'] );
	unset( $r['date_refundable'] );

	// Remove payment key.
	unset( $r['payment_key'] );

	// Remove object vars.
	unset( $r['address'] );
	unset( $r['adjustments'] );
	unset( $r['items'] );

	$new_order_id = edd_add_order( $r );

	if ( $clone_relationships ) {
		$items = edd_get_order_items( array(
			'order_id' => $order_id,
		) );

		if ( $items ) {
			foreach ( $items as $item ) {
				$r = $item->to_array();

				// Remove original item data.
				unset( $r['id'] );
				unset( $r['date_created'] );
				unset( $r['date_modified'] );

				// Point order item to the new order ID.
				$r['order_id'] = $new_order_id;

				$order_item_id = edd_add_order_item( $r );

				$metadata = edd_get_order_item_meta( $item->id );

				if ( $metadata ) {
					foreach ( $metadata as $meta_key => $meta_value ) {
						edd_add_order_item_meta( $order_item_id, $meta_key, $meta_value );
					}
				}

				$adjustments = edd_get_order_adjustments( array(
					'object_id'   => $item->id,
					'object_type' => 'order_item',
				) );

				if ( $adjustments ) {
					foreach ( $adjustments as $adjustment ) {
						$r = $adjustment->to_array();

						// Remove original adjustment data.
						unset( $r['id'] );
						unset( $r['date_created'] );
						unset( $r['date_modified'] );

						// Point order item to the new order ID.
						$r['object_id']   = $order_item_id;
						$r['object_type'] = 'order_item';

						$adjustment_id = edd_add_order_adjustment( $r );

						$metadata = edd_get_order_adjustment_meta( $adjustment->id );

						if ( $metadata ) {
							foreach ( $metadata as $meta_key => $meta_value ) {
								edd_add_order_adjustment_meta( $adjustment_id, $meta_key, $meta_value );
							}
						}
					}
				}
			}
		}

		$adjustments = edd_get_order_adjustments( array(
			'object_id'   => $order_id,
			'object_type' => 'order',
		) );

		if ( $adjustments ) {
			foreach ( $adjustments as $adjustment ) {
				$r = $adjustment->to_array();

				// Remove original adjustment data.
				unset( $r['id'] );
				unset( $r['date_created'] );
				unset( $r['date_modified'] );

				// Point order item to the new order ID.
				$r['object_id']   = $new_order_id;
				$r['object_type'] = 'order';

				$adjustment_id = edd_add_order_adjustment( $r );

				$metadata = edd_get_order_adjustment_meta( $adjustment->id );

				if ( $metadata ) {
					foreach ( $metadata as $meta_key => $meta_value ) {
						edd_add_order_adjustment_meta( $adjustment_id, $meta_key, $meta_value );
					}
				}
			}
		}

		if ( $order->address ) {
			$r = $order->address->to_array();

			// Remove original address data.
			unset( $r['order_id'] );
			unset( $r['date_created'] );
			unset( $r['date_modified'] );

			$r['order_id'] = $new_order_id;

			edd_add_order_address( $r );
		}
	}

	return $new_order_id;
}
