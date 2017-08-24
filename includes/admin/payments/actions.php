<?php
/**
 * Admin Payment Actions
 *
 * @package     EDD
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.9
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Process the payment details edit
 *
 * @access      private
 * @since       1.9
 * @return      void
*/
function edd_update_payment_details( $data ) {

	if( ! current_user_can( 'edit_shop_payments', $data['edd_payment_id'] ) ) {
		wp_die( __( 'You do not have permission to edit this payment record', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	check_admin_referer( 'edd_update_payment_details_nonce' );

	// Retrieve the payment ID
	$payment_id = absint( $data['edd_payment_id'] );
	$payment    = new EDD_Payment( $payment_id );

	// Retrieve existing payment meta
	$meta        = $payment->get_meta();
	$user_info   = $payment->user_info;

	$status      = $data['edd-payment-status'];
	$unlimited   = isset( $data['edd-unlimited-downloads'] ) ? '1' : '';
	$date        = sanitize_text_field( $data['edd-payment-date'] );
	$hour        = sanitize_text_field( $data['edd-payment-time-hour'] );

	// Restrict to our high and low
	if ( $hour > 23 ) {
		$hour = 23;
	} elseif ( $hour < 0 ) {
		$hour = 00;
	}

	$minute      = sanitize_text_field( $data['edd-payment-time-min'] );

	// Restrict to our high and low
	if ( $minute > 59 ) {
		$minute = 59;
	} elseif ( $minute < 0 ) {
		$minute = 00;
	}

	$address     = array_map( 'trim', $data['edd-payment-address'][0] );

	$curr_total  = edd_sanitize_amount( $payment->total );
	$new_total   = edd_sanitize_amount( $_POST['edd-payment-total'] );
	$tax         = isset( $_POST['edd-payment-tax'] ) ? edd_sanitize_amount( $_POST['edd-payment-tax'] ) : 0;
	$date        = date( 'Y-m-d', strtotime( $date ) ) . ' ' . $hour . ':' . $minute . ':00';

	$curr_customer_id  = sanitize_text_field( $data['edd-current-customer'] );
	$new_customer_id   = sanitize_text_field( $data['customer-id'] );

	// Setup purchased Downloads and price options
	$updated_downloads = isset( $_POST['edd-payment-details-downloads'] ) ? $_POST['edd-payment-details-downloads'] : false;

	if ( $updated_downloads ) {

		foreach ( $updated_downloads as $cart_position => $download ) {

			// If this item doesn't have a log yet, add one for each quantity count
			$has_log = absint( $download['has_log'] );
			$has_log = empty( $has_log ) ? false : true;

			if ( $has_log ) {

				$quantity   = isset( $download['quantity'] ) ? absint( $download['quantity']) : 1;
				$item_price = isset( $download['item_price'] ) ? $download['item_price'] : 0;
				$item_tax   = isset( $download['item_tax'] ) ? $download['item_tax'] : 0;

				// Format any items that are currency.
				$item_price = edd_format_amount( $item_price );
				$item_tax    = edd_format_amount( $item_tax );

				$args = array(
					'item_price' => $item_price,
					'quantity'   => $quantity,
					'tax'        => $item_tax,
				);

				$payment->modify_cart_item( $cart_position, $args );

			} else {

				// This
				if ( empty( $download['item_price'] ) ) {
					$download['item_price'] = 0.00;
				}

				if ( empty( $download['item_tax'] ) ) {
					$download['item_tax'] = 0.00;
				}

				$item_price  = $download['item_price'];
				$download_id = absint( $download['id'] );
				$quantity    = absint( $download['quantity'] ) > 0 ? absint( $download['quantity'] ) : 1;
				$price_id    = false;
				$tax         = $download['item_tax'];

				if ( edd_has_variable_prices( $download_id ) && isset( $download['price_id'] ) ) {
					$price_id = absint( $download['price_id'] );
				}

				// Set some defaults
				$args = array(
					'quantity'    => $quantity,
					'item_price'  => $item_price,
					'price_id'    => $price_id,
					'tax'         => $tax,
				);

				$payment->add_download( $download_id, $args );

			}

		}

		$deleted_downloads = json_decode( stripcslashes( $data['edd-payment-removed'] ), true );
		foreach ( $deleted_downloads as $deleted_download ) {
			$deleted_download = $deleted_download[0];

			if ( empty ( $deleted_download['id'] ) ) {
				continue;
			}

			$price_id = false;

			if ( edd_has_variable_prices( $deleted_download['id'] ) && isset( $deleted_download['price_id'] ) ) {
				$price_id = absint( $deleted_download['price_id'] );
			}

			$cart_index = isset( $deleted_download['cart_index'] ) ? absint( $deleted_download['cart_index'] ) : false;

			$args = array(
				'quantity'   => (int) $deleted_download['quantity'],
				'price_id'   => $price_id,
				'item_price' => (float) $deleted_download['amount'],
				'cart_index' => $cart_index
			);

			$payment->remove_download( $deleted_download['id'], $args );

			do_action( 'edd_remove_download_from_payment', $payment_id, $deleted_download['id'] );

		}


	}

	do_action( 'edd_update_edited_purchase', $payment_id );

	$payment->date = $date;

	$customer_changed = false;

	if ( isset( $data['edd-new-customer'] ) && $data['edd-new-customer'] == '1' ) {

		$email      = isset( $data['edd-new-customer-email'] ) ? sanitize_text_field( $data['edd-new-customer-email'] ) : '';
		$names      = isset( $data['edd-new-customer-name'] ) ? sanitize_text_field( $data['edd-new-customer-name'] ) : '';

		if ( empty( $email ) || empty( $names ) ) {
			wp_die( __( 'New Customers require a name and email address', 'easy-digital-downloads' ) );
		}

		$customer = new EDD_Customer( $email );
		if ( empty( $customer->id ) ) {
			$customer_data = array( 'name' => $names, 'email' => $email );
			$user_id       = email_exists( $email );
			if ( false !== $user_id ) {
				$customer_data['user_id'] = $user_id;
			}

			if ( ! $customer->create( $customer_data ) ) {
				// Failed to crete the new customer, assume the previous customer
				$customer_changed = false;
				$customer = new EDD_Customer( $curr_customer_id );
				edd_set_error( 'edd-payment-new-customer-fail', __( 'Error creating new customer', 'easy-digital-downloads' ) );
			}
		}

		$new_customer_id = $customer->id;

		$previous_customer = new EDD_Customer( $curr_customer_id );

		$customer_changed = true;

	} elseif ( $curr_customer_id !== $new_customer_id ) {

		$customer = new EDD_Customer( $new_customer_id );
		$email    = $customer->email;
		$names    = $customer->name;

		$previous_customer = new EDD_Customer( $curr_customer_id );

		$customer_changed = true;

	} else {

		$customer = new EDD_Customer( $curr_customer_id );
		$email    = $customer->email;
		$names    = $customer->name;

	}



	// Setup first and last name from input values
	$names      = explode( ' ', $names );
	$first_name = ! empty( $names[0] ) ? $names[0] : '';
	$last_name  = '';
	if( ! empty( $names[1] ) ) {
		unset( $names[0] );
		$last_name = implode( ' ', $names );
	}

	if ( $customer_changed ) {

		// Remove the stats and payment from the previous customer and attach it to the new customer
		$previous_customer->remove_payment( $payment_id, false );
		$customer->attach_payment( $payment_id, false );

		// If purchase was completed and not ever refunded, adjust stats of customers
		if( 'revoked' == $status || 'publish' == $status ) {

			$previous_customer->decrease_purchase_count();
			$previous_customer->decrease_value( $new_total );

			$customer->increase_purchase_count();
			$customer->increase_value( $new_total );
		}

		$payment->customer_id = $customer->id;
	}

	// Set new meta values
	$payment->user_id        = $customer->user_id;
	$payment->email          = $customer->email;
	$payment->first_name     = $first_name;
	$payment->last_name      = $last_name;
	$payment->address        = $address;

	$payment->total          = $new_total;
	$payment->tax            = $tax;

	$payment->has_unlimited_downloads = $unlimited;

	// Check for payment notes
	if ( ! empty( $data['edd-payment-note'] ) ) {

		$note  = wp_kses( $data['edd-payment-note'], array() );
		edd_insert_payment_note( $payment->ID, $note );

	}

	// Set new status
	$payment->status = $status;

	// Adjust total store earnings if the payment total has been changed
	if ( $new_total !== $curr_total && ( 'publish' == $status || 'revoked' == $status ) ) {

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

	$updated = $payment->save();

	if ( 0 === $updated ) {
		wp_die( __( 'Error Updating Payment', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 400 ) );
	}

	do_action( 'edd_updated_edited_purchase', $payment_id );

	wp_safe_redirect( admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&edd-message=payment-updated&id=' . $payment_id ) );
	exit;
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

		if( ! current_user_can( 'delete_shop_payments', $payment_id ) ) {
			wp_die( __( 'You do not have permission to edit this payment record', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		edd_delete_purchase( $payment_id );
		wp_redirect( admin_url( '/edit.php?post_type=download&page=edd-payment-history&edd-message=payment_deleted' ) );
		edd_die();
	}
}
add_action( 'edd_delete_payment', 'edd_trigger_purchase_delete' );

function edd_ajax_store_payment_note() {

	$payment_id = absint( $_POST['payment_id'] );
	$note       = wp_kses( $_POST['note'], array() );

	if( ! current_user_can( 'edit_shop_payments', $payment_id ) ) {
		wp_die( __( 'You do not have permission to edit this payment record', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	if( empty( $payment_id ) )
		die( '-1' );

	if( empty( $note ) )
		die( '-1' );

	$note_id = edd_insert_payment_note( $payment_id, $note );
	die( edd_get_payment_note_html( $note_id ) );
}
add_action( 'wp_ajax_edd_insert_payment_note', 'edd_ajax_store_payment_note' );

/**
 * Triggers a payment note deletion without ajax
 *
 * @since 1.6
 * @param array $data Arguments passed
 * @return void
*/
function edd_trigger_payment_note_deletion( $data ) {

	if( ! wp_verify_nonce( $data['_wpnonce'], 'edd_delete_payment_note_' . $data['note_id'] ) )
		return;

	if( ! current_user_can( 'edit_shop_payments', $data['payment_id'] ) ) {
		wp_die( __( 'You do not have permission to edit this payment record', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	$edit_order_url = admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&edd-message=payment-note-deleted&id=' . absint( $data['payment_id'] ) );

	edd_delete_payment_note( $data['note_id'], $data['payment_id'] );

	wp_redirect( $edit_order_url );
}
add_action( 'edd_delete_payment_note', 'edd_trigger_payment_note_deletion' );

/**
 * Delete a payment note deletion with ajax
 *
 * @since 1.6
 * @return void
*/
function edd_ajax_delete_payment_note() {

	if( ! current_user_can( 'edit_shop_payments', $_POST['payment_id'] ) ) {
		wp_die( __( 'You do not have permission to edit this payment record', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	if( edd_delete_payment_note( $_POST['note_id'], $_POST['payment_id'] ) ) {
		die( '1' );
	} else {
		die( '-1' );
	}

}
add_action( 'wp_ajax_edd_delete_payment_note', 'edd_ajax_delete_payment_note' );

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

	if( empty( $payment_id ) )
		die( '-2' );

	if( empty( $download_id ) )
		die( '-3' );

	$payment_key = edd_get_payment_key( $payment_id );
	$email       = edd_get_payment_user_email( $payment_id );

	$limit = edd_get_file_download_limit( $download_id );
	if ( ! empty( $limit ) ) {
		// Increase the file download limit when generating new links
		edd_set_file_download_limit_override( $download_id, $payment_id );
	}

	$files = edd_get_download_files( $download_id, $price_id );
	if( ! $files ) {
		die( '-4' );
	}

	$file_urls = '';

	foreach( $files as $file_key => $file ) {

		$file_urls .= edd_get_download_file_url( $payment_key, $email, $file_key, $download_id, $price_id );
		$file_urls .= "\n\n";

	}

	die( $file_urls );

}
add_action( 'wp_ajax_edd_get_file_download_link', 'edd_ajax_generate_file_download_link' );
