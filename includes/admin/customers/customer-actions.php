<?php

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

	do_action( 'edd_pre_insert_customer_note', $customer_id, $customer_note );

	$date     = current_time( 'mysql' );
	$date_gmt = current_time( 'mysql', 1 );
	$user_id  = is_admin() ? get_current_user_id() : 0;

	$note_id  = wp_insert_comment( wp_filter_comment( array(
		'comment_post_ID'      => 0,
		'comment_content'      => $customer_note,
		'user_id'              => $user_id,
		'comment_date'         => $date,
		'comment_date_gmt'     => $date_gmt,
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
		ob_start();
		?>
		<div id="customer-note-<?php echo $note_id; ?>" class="customer-note-wrapper dashboard-comment-wrap comment-item">
			<span class="row-actions right">
				<?php $delete_url = wp_nonce_url( admin_url( 'edit.php?post_type=download&page=edd-customers&view=notes&edd-action=delete-customer-note&note_id=' . $note_id . '&id=' . $customer_id ), 'delete-customer-note' ); ?>
				<a href="<?php echo $delete_url; ?>" data-nonce="<?php echo wp_create_nonce( 'delete-customer-note' ); ?>" data-note-id="<?php echo $note_id; ?>" class="delete"><?php _e( 'Delete', 'edd' ); ?></a>
			</span>
			<span class="avatar-wrap left">
				<?php $user_data = get_userdata( $user_id ); ?>
				<?php echo get_avatar( $user_data->user_email, 32 ); ?>
			</span>
			<span class="note-meta-wrap">
				<?php echo $user_data->user_nicename; ?>
				 @ <?php echo date_i18n( get_option( 'time_format' ), strtotime( $date ), true ); ?>
				 <?php _e( 'on', 'edd' ); ?> <?php echo date_i18n( get_option( 'date_format' ), strtotime( $date ), true ); ?>
			</span>
			<span class="note-content-wrap">
				<?php echo $customer_note; ?>
			</span>
		</div>
		<?php
		$output = ob_get_contents();
		ob_end_clean();

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			echo $output;
			exit;
		}

		return $note_id;

	}

	return false;

}
add_action( 'edd_add-customer-note', 'edd_customer_save_note', 10, 1 );

/**
 * Delete a cutomer note
 * @param  array $get The $_GET array of items
 * @return void
 */
function edd_customer_delete_note( $args ) {

	$customer_edit_role = apply_filters( 'edd_view_customers_role', 'view_shop_reports' );
	if ( ! is_admin() || ! current_user_can( $customer_edit_role ) ) {
		wp_die( __( 'You do not have permission to delete this customer.', 'edd' ) );
	}

	if ( empty( $args ) ) {
		return;
	}

	$note_id = (int)$args['note_id'];
	$nonce   = $args['_wpnonce'];

	if ( ! wp_verify_nonce( $nonce, 'delete-customer-note' ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'edd' ) );
	}

	$success = wp_delete_comment( $note_id, true );

	return $success;

}
add_action( 'edd_delete-customer-note', 'edd_customer_delete_note', 10, 1 );
