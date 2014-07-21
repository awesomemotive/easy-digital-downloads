<?php
/**
 * Admin Payment Actions
 *
 * @package     EDD
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2014, Pippin Williamson
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

	if( ! current_user_can( 'edit_shop_payment', $data['edd_payment_id' ] ) ) {
		wp_die( __( 'You do not have permission to edit this payment record', 'edd' ), __( 'Error', 'edd' ) );
	}

	check_admin_referer( 'edd_update_payment_details_nonce' );

	// Retrieve the payment ID
	$payment_id = absint( $data['edd_payment_id'] );

	// Retrieve existing payment meta
	$meta       = edd_get_payment_meta( $payment_id );
	$user_info  = edd_get_payment_meta_user_info( $payment_id );

	$status     = $data['edd-payment-status'];
	$unlimited  = isset( $data['edd-unlimited-downloads'] ) ? '1' : '';
	$user_id    = intval( $data['edd-payment-user-id'] );
	$date       = sanitize_text_field( $data['edd-payment-date'] );
	$hour       = sanitize_text_field( $data['edd-payment-time-hour'] );
	$minute     = sanitize_text_field( $data['edd-payment-time-min'] );
	$email      = sanitize_text_field( $data['edd-payment-user-email'] );
	$names      = sanitize_text_field( $data['edd-payment-user-name'] );
	$address    = array_map( 'trim', $data['edd-payment-address'][0] );

	$total      = edd_sanitize_amount( $_POST['edd-payment-total'] );
	$tax        = isset( $_POST['edd-payment-tax'] ) ? edd_sanitize_amount( $_POST['edd-payment-tax'] ) : 0;

	// Setup date from input values
	$date       = date( 'Y-m-d', strtotime( $date ) ) . ' ' . $hour . ':' . $minute . ':00';

	// Setup first and last name from input values
	$names      = explode( ' ', $names );
	$first_name = ! empty( $names[0] ) ? $names[0] : '';
	if( ! empty( $names[1] ) ) {
		unset( $names[0] );
		$last_name = implode( ' ', $names );
	}

	// Setup purchased Downloads and price options
	$updated_downloads = isset( $_POST['edd-payment-details-downloads'] ) ? $_POST['edd-payment-details-downloads'] : false;
	if( $updated_downloads && ! empty( $_POST['edd-payment-downloads-changed'] ) ) {
		$downloads    = array();
		$cart_details = array();
		$i = 0;
		foreach( $updated_downloads as $download ) {
			$item             = array();
			$item['id']       = absint( $download['id'] );
			$item['quantity'] = absint( $download['quantity'] );
			$price_id         = (int) $download['price_id'];

			if( $price_id !== false && edd_has_variable_prices( $item['id'] ) ) {
				$item['options'] = array(
					'price_id'   => $price_id
				);
			}
			$downloads[] = $item;

			$cart_item   = array();
			$cart_item['item_number'] = $item;

			$cart_details[$i] = array(
				'name'        => get_the_title( $download['id'] ),
				'id'          => $download['id'],
				'item_number' => $item,
				'price'       => $download['amount'],
				'item_price'  => round( $download['amount'] / $download['quantity'], 2 ),
				'quantity'    => $download['quantity'],
				'discount'    => 0,
				'tax'         => 0,
			);
			$i++;
		}

		$meta['downloads']    = $downloads;
		$meta['cart_details'] = $cart_details;
	}

	// Set new meta values
	$user_info['id']         = $user_id;
	$user_info['email']      = $email;
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

	do_action( 'edd_update_edited_purchase', $payment_id );

	// Update main payment record
	wp_update_post( array(
		'ID'        => $payment_id,
		'post_date' => $date
	) );

	// Set new status
	edd_update_payment_status( $payment_id, $status );

	update_post_meta( $payment_id, '_edd_payment_user_id',             $user_id );
	update_post_meta( $payment_id, '_edd_payment_user_email',          $email   );
	update_post_meta( $payment_id, '_edd_payment_meta',                $meta    );
	update_post_meta( $payment_id, '_edd_payment_total',               $total   );
	update_post_meta( $payment_id, '_edd_payment_downloads',           $total   );
	update_post_meta( $payment_id, '_edd_payment_unlimited_downloads', $unlimited );

	do_action( 'edd_updated_edited_purchase', $payment_id );

	wp_safe_redirect( admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&edd-message=payment-updated&id=' . $payment_id ) );
	exit;
}
add_action( 'edd_update_payment_details', 'edd_update_payment_details' );

function edd_ajax_store_payment_note() {

	$payment_id = absint( $_POST['payment_id'] );
	$note       = wp_kses( $_POST['note'], array() );

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

	if( ! current_user_can( 'edit_shop_payment', $data['payment_id' ] ) ) {
		wp_die( __( 'You do not have permission to edit this payment record', 'edd' ), __( 'Error', 'edd' ) );
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
 * @param array $data Arguments passed
 * @return void
*/
function edd_ajax_delete_payment_note() {

	if( ! current_user_can( 'edit_shop_payment', $_POST['payment_id' ] ) ) {
		wp_die( __( 'You do not have permission to edit this payment record', 'edd' ), __( 'Error', 'edd' ) );
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
