<?php

/**
 * Save a customer note being added
 *
 * @since  2.3
 * @param  array $post The $_POST array being passeed
 * @return int         The Note ID that was saved, or 0 if nothing was saved
 */
function edd_customer_save_note( $post ) {

	$customer_edit_role = apply_filters( 'edd_view_customers_role', 'view_shop_reports' );
	if ( ! is_admin() || ! current_user_can( $customer_edit_role ) ) {
		wp_die( __( 'You do not have permission to edit this customer.', 'edd' ) );
	}

	if ( empty( $post ) ) {
		return;
	}

	$customer_note = sanitize_text_field( $post['customer-note'] );
	$customer_id   = (int)$post['customer-id'];
	$nonce         = $post['add-customer-note-nonce'];

	if ( ! wp_verify_nonce( $nonce, 'add-customer-note' ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'edd' ) );
	}

	do_action( 'edd_pre_insert_customer_note', $customer_id, $customer_note );

	$note_id = wp_insert_comment( wp_filter_comment( array(
		'comment_post_ID'      => 0,
		'comment_content'      => $customer_note,
		'user_id'              => is_admin() ? get_current_user_id() : 0,
		'comment_date'         => current_time( 'mysql' ),
		'comment_date_gmt'     => current_time( 'mysql', 1 ),
		'comment_approved'     => 1,
		'comment_parent'       => 0,
		'comment_author'       => '',
		'comment_author_IP'    => '',
		'comment_author_url'   => '',
		'comment_author_email' => '',
		'comment_type'         => 'edd_customer_note'

	) ) );

	do_action( 'edd_insert_customer_note', $note_id, $customer_id, $customer_note );

	if ( $note_id ) {

		update_comment_meta( $note_id, 'edd_customer_id', $customer_id );
		return $note_id;

	}

	return 0;

}
add_action( 'edd_add-customer-note', 'edd_customer_save_note', 10, 1 );

/**
 * Delete a cutomer note
 * @param  array $get The $_GET array of items
 * @return void
 */
function edd_customer_delete_note( $get ) {

	$customer_edit_role = apply_filters( 'edd_view_customers_role', 'view_shop_reports' );
	if ( ! is_admin() || ! current_user_can( $customer_edit_role ) ) {
		wp_die( __( 'You do not have permission to delete this customer.', 'edd' ) );
	}

	if ( empty( $get ) ) {
		return;
	}

	$note_id = (int)$get['note_id'];
	$nonce   = $get['_wpnonce'];

	if ( ! wp_verify_nonce( $nonce, 'delete-customer-note' ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'edd' ) );
	}

	$success = wp_delete_comment( $note_id, true );

}
add_action( 'edd_delete-customer-note', 'edd_customer_delete_note', 10, 1 );
