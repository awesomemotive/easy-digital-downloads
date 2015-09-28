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

	// Retrieve existing payment meta
	$meta        = edd_get_payment_meta( $payment_id );
	$user_info   = edd_get_payment_meta_user_info( $payment_id );

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

	$curr_total  = edd_sanitize_amount( edd_get_payment_amount( $payment_id ) );
	$new_total   = edd_sanitize_amount( $_POST['edd-payment-total'] );
	$tax         = isset( $_POST['edd-payment-tax'] ) ? edd_sanitize_amount( $_POST['edd-payment-tax'] ) : 0;
	$date       = date( 'Y-m-d', strtotime( $date ) ) . ' ' . $hour . ':' . $minute . ':00';

	$curr_customer_id  = sanitize_text_field( $data['edd-current-customer'] );
	$new_customer_id   = sanitize_text_field( $data['customer-id'] );

	// Setup purchased Downloads and price options
	$updated_downloads = isset( $_POST['edd-payment-details-downloads'] ) ? $_POST['edd-payment-details-downloads'] : false;
	if( $updated_downloads && ! empty( $_POST['edd-payment-downloads-changed'] ) ) {

		$downloads    = array();
		$cart_details = array();
		$i = 0;
		foreach( $updated_downloads as $download ) {

			if( empty( $download['amount'] ) ) {
				$download['amount'] = '0.00';
			}

			$item             = array();
			$item['id']       = absint( $download['id'] );
			$item['quantity'] = absint( $download['quantity'] ) > 0 ? absint( $download['quantity'] ) : 1;
			$price_id         = (int) $download['price_id'];
			$has_log          = absint( $download['has_log'] );

			if( $price_id !== false && edd_has_variable_prices( $item['id'] ) ) {
				$item['options'] = array(
					'price_id'   => $price_id
				);
			}
			$downloads[] = $item;

			$cart_item   = array();
			$cart_item['item_number'] = $item;

			$item_price = round( $download['amount'] / $item['quantity'], edd_currency_decimal_filter() );

			$cart_details[$i] = array(
				'name'        => get_the_title( $download['id'] ),
				'id'          => $download['id'],
				'item_number' => $item,
				'price'       => $download['amount'],
				'item_price'  => $item_price,
				'subtotal'    => $download['amount'],
				'quantity'    => $download['quantity'],
				'discount'    => 0,
				'tax'         => 0,
			);

			// If this item doesn't have a log yet, add one for each quantity count
			if ( empty( $has_log ) ) {

				$log_date =  date( 'Y-m-d G:i:s', current_time( 'timestamp', true ) );
				$price_id = $price_id !== false ? $price_id : 0;

				$y = 0;

				while ( $y < $download['quantity'] ) {

					edd_record_sale_in_log( $download['id'], $payment_id, $price_id, $log_date );
					$y++;

				}

				edd_increase_purchase_count( $download['id'], $download['quantity'] );
				edd_increase_earnings( $download['id'], $download['amount'] );


			}

			$i++;
		}

		$meta['downloads']    = $downloads;
		$meta['cart_details'] = $cart_details;

		$deleted_downloads = json_decode( stripcslashes( $data['edd-payment-removed'] ), true );

		foreach ( $deleted_downloads as $deleted_download ) {
			$deleted_download = $deleted_download[0];

			if ( empty ( $deleted_download['id'] ) ) {
				continue;
			}

			$price_id = empty( $deleted_download['price_id'] ) ? 0 : (int) $deleted_download['price_id'];

			$log_args = array(
				'post_type'   => 'edd_log',
				'post_parent' => $deleted_download['id'],
				'numberposts' => $deleted_download['quantity'],
				'meta_query'  => array(
					array(
						'key'     => '_edd_log_payment_id',
						'value'   => $payment_id,
						'compare' => '=',
					),
					array(
						'key'     => '_edd_log_price_id',
						'value'   => $price_id,
						'compare' => '='
					)
				)
			);

			$found_logs = get_posts( $log_args );
			foreach ( $found_logs as $log ) {
				wp_delete_post( $log->ID, true );
			}

			edd_decrease_purchase_count( $deleted_download['id'], $deleted_download['quantity'] );
			edd_decrease_earnings( $deleted_download['id'], $deleted_download['amount'] );

			do_action( 'edd_remove_download_from_payment', $payment_id, $deleted_download['id'] );

		}


	}

	do_action( 'edd_update_edited_purchase', $payment_id );

	// Update main payment record
	$updated = wp_update_post( array(
		'ID'        => $payment_id,
		'post_date' => $date
	) );

	if ( 0 === $updated ) {
		wp_die( __( 'Error Updating Payment', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 400 ) );
	}

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

		update_post_meta( $payment_id, '_edd_payment_customer_id',  $customer->id );
	}

	// Set new meta values
	$user_info['id']         = $customer->user_id;
	$user_info['email']      = $customer->email;
	$user_info['first_name'] = $first_name;
	$user_info['last_name']  = $last_name;
	$user_info['address']    = $address;
	$meta['user_info']       = $user_info;
	$meta['tax']             = $tax;

	// Check for payment notes
	if ( ! empty( $data['edd-payment-note'] ) ) {

		$note  = wp_kses( $data['edd-payment-note'], array() );
		edd_insert_payment_note( $payment_id, $note );

	}

	// Set new status
	edd_update_payment_status( $payment_id, $status );

	edd_update_payment_meta( $payment_id, '_edd_payment_user_id',             $customer->user_id   );
	edd_update_payment_meta( $payment_id, '_edd_payment_user_email',          $customer->email     );
	edd_update_payment_meta( $payment_id, '_edd_payment_meta',                $meta      );
	edd_update_payment_meta( $payment_id, '_edd_payment_total',               $new_total );

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

	edd_update_payment_meta( $payment_id, '_edd_payment_downloads',           $new_total );
	edd_update_payment_meta( $payment_id, '_edd_payment_unlimited_downloads', $unlimited );

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

		if( ! current_user_can( 'edit_shop_payments', $payment_id ) ) {
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

	if( ! current_user_can( 'view_shop_reports' ) ) {
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
