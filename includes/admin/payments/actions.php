<?php
/**
 * Admin Payment Actions
 *
 * @package     EDD
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.9
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Process the "Add Note" submission from the edit payment screen
 *
 * @access      private
 * @since       1.9
 * @return      void
*/
function edd_update_payment_totals( $data ) {

	if( ! current_user_can( 'edit_shop_payment', $data['edd_payment_id' ] ) ) {
		wp_die( __( 'You do not have permission to edit this payment record', 'edd' ), __( 'Error', 'edd' ) );
	}

	check_admin_referer( 'edd_update_payment_totals_nonce' );

	$payment_id = absint( $data['edd_payment_id'] );
	$total      = edd_sanitize_amount( $_POST['edd-payment-total'] );
	$tax        = edd_sanitize_amount( $_POST['edd-payment-tax'] );

	update_post_meta( $payment_id, '_edd_payment_total', $total );

	wp_safe_redirect( admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&edd-message=totals-updated&id=' . $payment_id ) );
}
add_action( 'edd_update_payment_totals', 'edd_update_payment_totals' );

/**
 * Process the "Add Note" submission from the edit payment screen
 *
 * @access      private
 * @since       1.9
 * @return      void
*/
function edd_add_payment_note( $data ) {

	if( ! current_user_can( 'edit_shop_payment', $_POST['edd_payment_id' ] ) ) {
		wp_die( __( 'You do not have permission to edit this payment record', 'edd' ), __( 'Error', 'edd' ) );
	}

	if ( ! empty( $data['edd-payment-note'] ) ) {
		$note    = wp_kses( $data['edd-payment-note'], array() );
		$note_id = edd_insert_payment_note( $_POST['edd_payment_id' ], $note );

		wp_safe_redirect( admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&edd-message=note-added&id=' . $_POST['edd_payment_id' ] ) );
	}
}
add_action( 'edd_add_payment_note', 'edd_add_payment_note' );