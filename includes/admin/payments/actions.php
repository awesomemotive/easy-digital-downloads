<?php
/**
 * Admin Payment Actions
 *
 * @package     EDD
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.9
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Handle order item changes
 *
 * @since 3.0
 *
 * @param array $request
 *
 * @return boolean
 */
function edd_handle_order_item_change( $request = array() ) {

	// Bail if missing necessary properties
	if (
		empty( $request['_wpnonce'] ) ||
		empty( $request['id'] ) ||
		empty( $request['order_item'] )
	) {
		return false;
	}

	// Bail if nonce check fails
	if ( ! wp_verify_nonce( $request['_wpnonce'], 'edd_order_item_nonce' ) ) {
		return false;
	}

	// Default data
	$data = array();

	// Maybe add status to data to update
	if ( ! empty( $request['status'] ) && ( 'inherit' === $request['status'] ) || in_array( $request['status'], array_keys( edd_get_payment_statuses() ), true ) ) {
		$data['status'] = sanitize_key( $request['status'] );
	}

	// Update order item
	if ( ! empty( $data ) ) {
		edd_update_order_item( $request['order_item'], $data );
		edd_redirect(
			edd_get_admin_url(
				array(
					'page' => 'edd-payment-history',
					'view' => 'view-order-details',
					'id'   => absint( $request['id'] ),
				)
			)
		);
	}
}
add_action( 'edd_handle_order_item_change', 'edd_handle_order_item_change' );

/**
 * Process the changes from the `View Order Details` page.
 *
 * @since 1.9
 * @since 3.0 Refactored to use new core objects and query methods.
 *
 * @param array $data Order data.
 */
function edd_update_payment_details( $data = array() ) {

	// Bail if an empty array is passed.
	if ( empty( $data ) ) {
		wp_die( esc_html__( 'You do not have permission to edit this payment record', 'easy-digital-downloads' ), esc_html__( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	// Bail if the user does not have the correct permissions.
	if ( ! current_user_can( 'edit_shop_payments', $data['edd_payment_id'] ) ) {
		wp_die( esc_html__( 'You do not have permission to edit this payment record', 'easy-digital-downloads' ), esc_html__( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	check_admin_referer( 'edd_update_payment_details_nonce' );

	// Retrieve the order ID and set up the order.
	$order_id = absint( $data['edd_payment_id'] );
	$order    = edd_get_order( $order_id );

	$order_update_args = array();

	$unlimited  = isset( $data['edd-unlimited-downloads'] ) ? '1' : null;
	$new_status = sanitize_key( $data['edd-payment-status'] );
	$date       = sanitize_text_field( $data['edd-payment-date'] );
	$hour       = sanitize_text_field( $data['edd-payment-time-hour'] );

	// Restrict to our high and low
	if ( $hour > 23 ) {
		$hour = 23;
	} elseif ( $hour < 0 ) {
		$hour = 00;
	}

	$minute = sanitize_text_field( $data['edd-payment-time-min'] );

	// Restrict to our high and low
	if ( $minute > 59 ) {
		$minute = 59;
	} elseif ( $minute < 0 ) {
		$minute = 00;
	}

	// Date
	$date = date( 'Y-m-d', strtotime( $date ) ) . ' ' . $hour . ':' . $minute . ':00';

	// Address
	$address = $data['edd_order_address'];

	// Totals
	$curr_total = edd_sanitize_amount( $order->total );
	$curr_tax   = edd_sanitize_amount( $order->tax );
	$new_total  = isset( $data['edd-payment-total'] ) ? edd_sanitize_amount( $data['edd-payment-total'] ) : $curr_total;
	$tax        = isset( $data['edd-payment-tax'] ) ? edd_sanitize_amount( $data['edd-payment-tax'] ) : $curr_tax;

	// Customer
	$curr_customer_id = sanitize_text_field( $data['edd-current-customer'] );
	$new_customer_id  = sanitize_text_field( $data['customer-id'] );

	// Totals
	$new_subtotal = 0.00;
	$new_tax      = 0.00;

	// Setup purchased Downloads and price options
	$updated_downloads = isset( $_POST['edd-payment-details-downloads'] )
		? $_POST['edd-payment-details-downloads']
		: false;

	if ( ! empty( $updated_downloads ) && is_array( $updated_downloads ) ) {
		foreach ( $updated_downloads as $cart_index => $download ) {

			// Check if the item exists in the database.
			$item_exists = (bool) 0 < absint( $download['order_item_id'] );

			if ( $item_exists ) {
				/** @var EDD\Orders\Order_Item $order_item */
				$order_item = edd_get_order_item( absint( $download['order_item_id'] ) );

				$quantity   = isset( $download['quantity'] ) ? absint( $download['quantity'] ) : 1;
				$item_price = isset( $download['item_price'] ) ? $download['item_price'] : 0;
				$item_tax   = isset( $download['item_tax'] ) ? $download['item_tax'] : 0;

				// Format any items that have a currency.
				$item_price = edd_format_amount( $item_price );
				$item_tax   = edd_format_amount( $item_tax );

				// Increase running totals.
				$new_subtotal += ( floatval( $item_price ) * $quantity ) - $order_item->discount;
				$new_tax      += $item_tax;

				$args = array(
					'cart_index' => $cart_index,
					'quantity'   => $quantity,
					'amount'     => $item_price,
					'subtotal'   => $item_price * $quantity,
					'tax'        => $item_tax,
				);

				edd_update_order_item( absint( $download['order_item_id'] ), $args );
			} else {
				if ( empty( $download['item_price'] ) ) {
					$download['item_price'] = 0.00;
				}

				if ( empty( $download['item_tax'] ) ) {
					$download['item_tax'] = 0.00;
				}

				$item_price  = $download['item_price'];
				$download_id = absint( $download['id'] );
				$quantity    = absint( $download['quantity'] ) > 0 ? absint( $download['quantity'] ) : 1;
				$price_id    = 0;
				$tax         = $download['item_tax'];

				if ( edd_has_variable_prices( $download_id ) && isset( $download['price_id'] ) ) {
					$price_id = absint( $download['price_id'] );
				}

				// Set some defaults
				$args = array(
					'order_id'     => $order_id,
					'product_id'   => $download_id,
					'product_name' => get_the_title( $download_id ),
					'price_id'     => $price_id,
					'cart_index'   => $cart_index,
					'quantity'     => $quantity,
					'amount'       => $item_price,
					'subtotal'     => $item_price * $quantity,
					'tax'          => $tax,
					'total'        => ( $item_price * $quantity ) + $tax,
				);

				// Increase running totals.
				$new_subtotal += floatval( $item_price ) * $quantity;
				$new_tax      += $tax;

				edd_add_order_item( $args );
			}
		}

		$deleted_downloads = json_decode( stripcslashes( $data['edd-payment-removed'] ), true );

		foreach ( $deleted_downloads as $deleted_download ) {
			$deleted_download = $deleted_download[0];

			if ( empty( $deleted_download['id'] ) ) {
				continue;
			}

			/** @var EDD\Orders\Order_Item $order_item */
			$order_item = edd_get_order_item( absint( $deleted_download['order_item_id'] ) );

			$new_subtotal -= (float) $deleted_download['amount'] * $deleted_download['quantity'];
			$new_tax      -= (float) $order_item->tax;

			edd_delete_order_item( absint( $deleted_download['order_item_id'] ) );

			do_action( 'edd_remove_download_from_payment', $order_id, $deleted_download['id'] );
		}
	}

	do_action( 'edd_update_edited_purchase', $order_id );

	$order_update_args['date_created'] = $date;

	$customer_changed = false;

	// Create a new customer.
	if ( isset( $data['edd-new-customer'] ) && 1 === (int) $data['edd-new-customer'] ) {
		$email = isset( $data['edd-new-customer-email'] )
			? sanitize_text_field( $data['edd-new-customer-email'] )
			: '';

		$names = isset( $data['edd-new-customer-name'] )
			? sanitize_text_field( $data['edd-new-customer-name'] )
			: '';

		if ( empty( $email ) || empty( $names ) ) {
			wp_die( esc_html__( 'New Customers require a name and email address', 'easy-digital-downloads' ) );
		}

		$customer = new EDD_Customer( $email );
		if ( empty( $customer->id ) ) {
			$customer_data = array(
				'name'  => $names,
				'email' => $email,
			);

			$user_id = email_exists( $email );

			if ( false !== $user_id ) {
				$customer_data['user_id'] = $user_id;
			}

			if ( ! $customer->create( $customer_data ) ) {
				// Failed to crete the new customer, assume the previous customer
				$customer_changed = false;

				$customer = new EDD_Customer( $curr_customer_id );
				edd_set_error( 'edd-payment-new-customer-fail', __( 'Error creating new customer', 'easy-digital-downloads' ) );
			}
		} else {
			wp_die( sprintf( __( 'A customer with the email address %s already exists. Please go back and use the "Assign to another customer" link to assign this payment to them.', 'easy-digital-downloads' ), $email ) );
		}

		$new_customer_id   = $customer->id;
		$previous_customer = new EDD_Customer( $curr_customer_id );
		$customer_changed  = true;
	} elseif ( $curr_customer_id !== $new_customer_id ) {
		$customer = new EDD_Customer( $new_customer_id );
		$email    = $customer->email;
		$names    = $customer->name;

		$previous_customer = new EDD_Customer( $curr_customer_id );
		$customer_changed  = true;
	} else {
		$customer = new EDD_Customer( $curr_customer_id );
		$email    = $customer->email;
		$names    = $customer->name;
	}

	// Setup first and last name from input values.
	$names      = explode( ' ', $names );
	$first_name = ! empty( $names[0] ) ? $names[0] : '';
	$last_name  = '';

	if ( ! empty( $names[1] ) ) {
		unset( $names[0] );
		$last_name = implode( ' ', $names );
	}

	if ( $customer_changed ) {

		// Remove the stats and payment from the previous customer and attach it to the new customer
		$previous_customer->remove_payment( $order_id, false );
		$customer->attach_payment( $order_id, false );

		// If purchase was completed and not ever refunded, adjust stats of customers
		if ( 'revoked' === $status || 'publish' === $status ) {
			$previous_customer->recalculate_stats();
			$customer->recalculate_stats();
		}

		$order_update_args['customer_id'] = $customer->id;
	}

	// Set new order values.
	$order_update_args['user_id'] = $customer->user_id;
	$order_update_args['email']   = $customer->email;
	$order_update_args['tax']     = $new_tax;
	$order_update_args['total']   = $new_total;

	edd_update_order_address(
		absint( $address['address_id'] ),
		array(
			'first_name'  => $first_name,
			'last_name'   => $last_name,
			'address'     => $address['address'],
			'address2'    => $address['address2'],
			'city'        => $address['city'],
			'region'      => $address['region'],
			'postal_code' => $address['postal_code'],
			'country'     => $address['country'],
		)
	);

	if ( 1 === (int) $unlimited ) {
		edd_update_order_meta( $order_id, 'unlimited_downloads', $unlimited );
	} else {
		edd_delete_order_meta( $order_id, 'unlimited_downloads' );
	}

	// Adjust total store earnings if the payment total has been changed
	if ( $new_total !== $curr_total && ( 'publish' === $status || 'revoked' === $status ) ) {
		if ( $new_total > $curr_total ) {

			// Increase if our new total is higher
			$difference = $new_total - $curr_total;
			edd_increase_total_earnings( $difference );
		} elseif ( $curr_total > $new_total ) {

			// Decrease if our new total is lower
			$difference = $curr_total - $new_total;
			edd_decrease_total_earnings( $difference );
		}
	}

	// Don't set the status in the update call (for back compat)
	unset( $order_update_args['status'] );

	// Attempt to update the order
	$updated = edd_update_order( $order_id, $order_update_args );

	// Check if the status has changed, if so, we need to invoke the pertinent
	// status processing method (for back compat)
	if ( $new_status !== $order->status ) {
		edd_update_order_status( $order_id, $new_status );
	}

	// Bail if an error occurred
	if ( false === $updated ) {
		wp_die( __( 'Error Updating Payment', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 400 ) );
	}

	do_action( 'edd_updated_edited_purchase', $order_id );

	edd_redirect(
		edd_get_admin_url(
			array(
				'page'        => 'edd-payment-history',
				'view'        => 'view-order-details',
				'edd-message' => 'payment-updated',
				'id'          => $order_id,
			)
		)
	);
}
add_action( 'edd_update_payment_details', 'edd_update_payment_details' );

/**
 * Trigger a Purchase Deletion
 *
 * @since 1.3.4
 * @param $data Arguments passed
 * @return void
 */
function edd_trigger_purchase_delete( $data ) {
	if ( wp_verify_nonce( $data['_wpnonce'], 'edd_payment_nonce' ) ) {
		$payment_id = absint( $data['purchase_id'] );

		if ( ! current_user_can( 'delete_shop_payments', $payment_id ) ) {
			wp_die( __( 'You do not have permission to edit this payment record', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		edd_delete_purchase( $payment_id );

		edd_redirect( admin_url( '/edit.php?post_type=download&page=edd-payment-history&edd-message=payment_deleted' ) );
	}
}
add_action( 'edd_delete_payment', 'edd_trigger_purchase_delete' );

/**
 * Retrieves a new download link for a purchased file
 *
 * @since 2.0
 * @return string
 */
function edd_ajax_generate_file_download_link() {
	$customer_view_role = apply_filters( 'edd_view_customers_role', 'view_shop_reports' );
	if ( ! current_user_can( $customer_view_role ) ) {
		die( '-1' );
	}

	$payment_id  = absint( $_POST['payment_id'] );
	$download_id = absint( $_POST['download_id'] );
	$price_id    = absint( $_POST['price_id'] );

	if ( empty( $payment_id ) ) {
		die( '-2' );
	}

	if ( empty( $download_id ) ) {
		die( '-3' );
	}

	$payment_key = edd_get_payment_key( $payment_id );
	$email       = edd_get_payment_user_email( $payment_id );

	$limit = edd_get_file_download_limit( $download_id );
	if ( ! empty( $limit ) ) {
		// Increase the file download limit when generating new links
		edd_set_file_download_limit_override( $download_id, $payment_id );
	}

	$files = edd_get_download_files( $download_id, $price_id );
	if ( ! $files ) {
		die( '-4' );
	}

	$file_urls = '';

	foreach ( $files as $file_key => $file ) {
		$file_urls .= edd_get_download_file_url( $payment_key, $email, $file_key, $download_id, $price_id );
		$file_urls .= "\n\n";
	}

	die( $file_urls );
}
add_action( 'wp_ajax_edd_get_file_download_link', 'edd_ajax_generate_file_download_link' );

/**
 * Process Orders list table bulk actions. This is necessary because we need to
 * redirect to ensure filters do not get applied when bulk actions are being
 * processed. This processing cannot happen within the `EDD_Payment_History_Table`
 * class as at that point, it is too late to do a redirect.
 *
 * @since 3.0
 */
function edd_orders_list_table_process_bulk_actions() {

	// Bail if this method was called directly.
	if ( 'load-download_page_edd-payment-history' !== current_action() ) {
		_doing_it_wrong( __FUNCTION__, 'This method is not meant to be called directly.', 'EDD 3.0' );
	}

	$action = isset( $_REQUEST['action'] ) // WPCS: CSRF ok.
		? sanitize_text_field( $_REQUEST['action'] )
		: '';

	// Bail if we aren't processing bulk actions.
	if ( 'action' !== $action ) {
		return;
	}

	$ids = isset( $_GET['order'] ) // WPCS: CSRF ok.
		? $_GET['order']
		: false;

	if ( ! is_array( $ids ) ) {
		$ids = array( $ids );
	}

	if ( empty( $action ) ) {
		return;
	}

	$ids = wp_parse_id_list( $ids );

	foreach ( $ids as $id ) {
		switch ( $action ) {
			case 'delete':
				edd_delete_purchase( $id );
				break;

			case 'set-status-publish':
				edd_update_payment_status( $id, 'publish' );
				break;

			case 'set-status-pending':
				edd_update_payment_status( $id, 'pending' );
				break;

			case 'set-status-processing':
				edd_update_payment_status( $id, 'processing' );
				break;

			case 'set-status-refunded':
				edd_update_payment_status( $id, 'refunded' );
				break;

			case 'set-status-revoked':
				edd_update_payment_status( $id, 'revoked' );
				break;

			case 'set-status-failed':
				edd_update_payment_status( $id, 'failed' );
				break;

			case 'set-status-abandoned':
				edd_update_payment_status( $id, 'abandoned' );
				break;

			case 'set-status-preapproval':
				edd_update_payment_status( $id, 'preapproval' );
				break;

			case 'set-status-cancelled':
				edd_update_payment_status( $id, 'cancelled' );
				break;

			case 'resend-receipt':
				edd_email_purchase_receipt( $id, false );
				break;
		}

		do_action( 'edd_payments_table_do_bulk_action', $id, $action );
	}

	wp_redirect( wp_get_referer() );
}
add_action( 'load-download_page_edd-payment-history', 'edd_orders_list_table_process_bulk_actions' );
