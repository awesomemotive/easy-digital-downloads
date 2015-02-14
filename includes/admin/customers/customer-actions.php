<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Processes a custom edit
 *
 * @since  2.3
 * @param  array $args The $_POST array being passeed
 * @return array $output Response messages
 */
function edd_edit_customer( $args ) {

	$customer_edit_role = apply_filters( 'edd_view_customers_role', 'view_shop_reports' );

	if ( ! is_admin() || ! current_user_can( $customer_edit_role ) ) {
		wp_die( __( 'You do not have permission to edit this customer.', 'edd' ) );
	}

	if ( empty( $args ) ) {
		return;
	}

	$customer_info = $args['customerinfo'];
	$customer_id   = (int)$args['customerinfo']['id'];
	$nonce         = $args['_wpnonce'];

	if ( ! wp_verify_nonce( $nonce, 'edit-customer' ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'edd' ) );
	}

	$customer = new EDD_Customer( $customer_id );
	if ( empty( $customer->id ) ) {
		return false;
	}

	$defaults = array(
		'line1'   => '',
		'line2'   => '',
		'city'    => '',
		'country' => '',
		'zip'     => '',
		'state'   => '',
		'name'    => '',
		'email'   => '',
		'user_id' => 0
	);

	$customer_info = wp_parse_args( $customer_info, $defaults );

	// Sanitize the inputs
	$address = array();
	$address['line1']         = $customer_info['line1'];
	$address['line2']         = $customer_info['line2'];
	$address['city']          = $customer_info['city'];
	$address['zip']           = $customer_info['zip'];
	$address['state']         = $customer_info['state'];
	$address['country']       = $customer_info['country'];
	$customer_data = array();
	$customer_data['name']    = $customer_info['name'];
	$customer_data['email']   = $customer_info['email'];
	$customer_data['user_id'] = $customer_info['user_id'];

	$customer_data = apply_filters( 'edd_edit_customer_info', $customer_data, $customer_id );
	$address       = apply_filters( 'edd_edit_customer_address', $address, $customer_id );

	$customer_data = array_map( 'sanitize_text_field', $customer_data );
	$address       = array_map( 'sanitize_text_field', $address );

	do_action( 'edd_pre_edit_customer', $customer_id, $customer_data, $address );

	if ( ! is_email( $customer_data['email'] ) ) {
		edd_set_error( 'edd-invalid-email', __( 'Please enter a valid email address.', 'edd' ) );
	}

	if ( (int) $customer_data['user_id'] != (int) $customer->user_id ) {

		// Make sure we don't already have this user attached to a customer
		if ( ! empty( $customer_data['user_id'] ) && false !== EDD()->customers->get_customer_by( 'user_id', $customer_data['user_id'] ) ) {
			edd_set_error( 'edd-invlid-customer-user_id', sprintf( __( 'The User ID %d is already associated with a different customer.', 'edd' ), $customer_data['user_id'] ) );
		}

		// Make sure it's actually a user
		$user = get_user_by( 'id', $customer_data['user_id'] );
		if ( ! empty( $customer_data['user_id'] ) && false === $user ) {
			edd_set_error( 'edd-invalid-user_id', sprintf( __( 'The User ID %d does not exist. Please assign an existing user.', 'edd' ), $customer_data['user_id'] ) );
		}

	}

	// Record this for later
	$previous_user_id  = $customer->user_id;

	if ( edd_get_errors() ) {
		return;
	}

	$output         = array();
	$previous_email = $customer->email;

	if ( $customer->update( $customer_data ) ) {

		if ( ! empty( $customer->user_id ) ) {
			update_user_meta( $customer->user_id, '_edd_user_address', $address );
		}

		// Update some payment meta if we need to
		$payments_array = explode( ',', $customer->payment_ids );

		if ( $customer->email != $previous_email ) {
			foreach ( $payments_array as $payment_id ) {
				edd_update_payment_meta( $payment_id, '_edd_payment_user_email', $customer->email );
			}
		}

		if ( $customer->user_id != $previous_user_id ) {
			foreach ( $payments_array as $payment_id ) {
				edd_update_payment_meta( $payment_id, '_edd_payment_user_id', $customer->user_id );
			}
		}

		$output['success']       = true;
		$customer_data           = array_merge( $customer_data, $address );
		$output['customer_info'] = $customer_data;

	} else {

		$output['success'] = false;

	}

	do_action( 'edd_post_edit_customer', $customer_id, $customer_data );

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		header( 'Content-Type: application/json' );
		echo json_encode( $output );
		wp_die();
	}

	return $output;

}
add_action( 'edd_edit-customer', 'edd_edit_customer', 10, 1 );

/**
 * Save a customer note being added
 *
 * @since  2.3
 * @param  array $args The $_POST array being passeed
 * @return int         The Note ID that was saved, or 0 if nothing was saved
 */
function edd_customer_save_note( $args ) {

	$customer_edit_role = apply_filters( 'edd_view_customers_role', 'view_shop_reports' );

	if ( ! is_admin() || ! current_user_can( $customer_edit_role ) ) {
		wp_die( __( 'You do not have permission to edit this customer.', 'edd' ) );
	}

	if ( empty( $args ) ) {
		return;
	}

	$customer_note = trim( sanitize_text_field( $args['customer_note'] ) );
	$customer_id   = (int)$args['customer_id'];
	$nonce         = $args['add_customer_note_nonce'];

	if ( ! wp_verify_nonce( $nonce, 'add-customer-note' ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'edd' ) );
	}

	if ( empty( $customer_note ) ) {
		edd_set_error( 'empty-customer-note', __( 'A note is required', 'edd' ) );
	}

	if ( edd_get_errors() ) {
		return;
	}

	$customer = new EDD_Customer( $customer_id );
	$new_note = $customer->add_note( $customer_note );

	do_action( 'edd_pre_insert_customer_note', $customer_id, $new_note );

	if ( ! empty( $new_note ) && ! empty( $customer->id ) ) {

		ob_start();
		?>
		<div class="customer-note-wrapper dashboard-comment-wrap comment-item">
			<span class="note-content-wrap">
				<?php echo stripslashes( $new_note ); ?>
			</span>
		</div>
		<?php
		$output = ob_get_contents();
		ob_end_clean();

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			echo $output;
			exit;
		}

		return $new_note;

	}

	return false;

}
add_action( 'edd_add-customer-note', 'edd_customer_save_note', 10, 1 );
