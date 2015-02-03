<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

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

	$defaults = array(
		'line1'   => '',
		'line2'   => '',
		'city'    => '',
		'country' => '',
		'zip'     => '',
		'state'   => '',
		'name'    => '',
		'email'   => '',
		'user_id' => ''
	);

	$customer_info = wp_parse_args( $customer_info, $defaults );

	// Sanitize the inputs
	$customer_info['line1'] = sanitize_text_field( $customer_info['line1'] );
	$customer_info['line2'] = sanitize_text_field( $customer_info['line2'] );
	$customer_info['city']  = sanitize_text_field( $customer_info['city'] );
	$customer_info['zip']   = sanitize_text_field( $customer_info['zip'] );
	$customer_info['state'] = sanitize_text_field( $customer_info['state'] );
	$customer_info['name']  = sanitize_text_field( $customer_info['name'] );
	$customer_info['email'] = sanitize_text_field( $customer_info['email'] );

	do_action( 'edd_pre_edit_customer', $customer_id, $customer_info );

	if ( ! is_email( $customer_info['email'] ) ) {
		edd_set_error( 'edd-invalid-customer-email', __( 'Please enter a valid email address', 'edd' ) );
	}



	if ( edd_get_errors() ) {
		return;
	}

	do_action( 'edd_post_edit_customer', $customer_id, $customer_info );

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
