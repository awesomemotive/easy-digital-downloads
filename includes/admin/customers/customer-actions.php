<?php
/**
 * Customers - Admin Actions.
 *
 * @package     EDD
 * @subpackage  Admin/Customers
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.3
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Update customer.
 *
 * @since 2.3
 * @since 3.0 Updated to use new query methods and custom tables.
 *
 * @param array $args Form data being passed.
 * @return false|array $output Response message.
 */
function edd_edit_customer( $args = array() ) {

	// Bail if nothing new to edit.
	if ( empty( $args ) || empty( $args['customerinfo'] ) || empty( $args['_wpnonce'] ) ) {
		return false;
	}

	// Bail if user cannot edit customers.
	if ( ! is_admin() || ! current_user_can( edd_get_edit_customers_role() ) ) {
		wp_die( esc_html__( 'You do not have permission to edit this customer.', 'easy-digital-downloads' ) );
	}

	$customer_info = $args['customerinfo'];
	$customer_id   = (int) $customer_info['id'];
	$nonce         = $args['_wpnonce'];

	// Bail if nonce check fails
	if ( ! wp_verify_nonce( $nonce, 'edit-customer' ) ) {
		wp_die( esc_html__( 'Nonce verification failed.', 'easy-digital-downloads' ) );
	}

	// Bail if customer does not exist.
	$customer = edd_get_customer( $customer_id );
	if ( ! $customer ) {
		return false;
	}

	// Parse customer info with defaults.
	$customer_info = wp_parse_args( $customer_info, array(
		'name'         => '',
		'email'        => '',
		'date_created' => '',
		'user_id'      => 0
	) );

	if ( ! is_email( $customer_info['email'] ) ) {
		edd_set_error( 'edd-invalid-email', __( 'Please enter a valid email address.', 'easy-digital-downloads' ) );
	}

	if ( (int) $customer_info['user_id'] !== (int) $customer->user_id ) {

		// Make sure we don't already have this user attached to a customer
		if ( ! empty( $customer_info['user_id'] ) && false !== edd_get_customer_by( 'user_id', $customer_info['user_id'] ) ) {
			/* translators: %d: user ID */
			edd_set_error( 'edd-invalid-customer-user_id', sprintf( __( 'The User ID %d is already associated with a different customer.', 'easy-digital-downloads' ), $customer_info['user_id'] ) );
		}

		// Make sure it's actually a user
		$user = get_user_by( 'id', $customer_info['user_id'] );
		if ( ! empty( $customer_info['user_id'] ) && false === $user ) {
			/* translators: %d: user ID */
			edd_set_error( 'edd-invalid-user_id', sprintf( __( 'The User ID %d does not exist. Please assign an existing user.', 'easy-digital-downloads' ), $customer_info['user_id'] ) );
		}
	}

	// Record this for later
	$previous_user_id = $customer->user_id;

	// Bail if errors exist.
	if ( edd_get_errors() ) {
		return false;
	}

	$user_id = absint( $customer_info['user_id'] );

	if ( empty( $user_id ) && ! empty( $customer_info['user_login'] ) ) {

		// See if they gave an email, otherwise we'll assume login
		$user_by_field = is_email( $customer_info['user_login'] )
			? 'email'
			: 'login';

		$user = get_user_by( $user_by_field, $customer_info['user_login'] );

		if ( $user ) {
			$user_id = $user->ID;
		} else {
			/* translators: %s: user login or email address */
			edd_set_error( 'edd-invalid-user-string', sprintf( __( 'Failed to attach user. The login or email address %s was not found.', 'easy-digital-downloads' ), $customer_info['user_login'] ) );
		}
	}

	// Setup the customer address, if present.
	$address = array();

	$address['address'] = isset( $customer_info['address'] )
		? $customer_info['address']
		: '';

	$address['address2'] = isset( $customer_info['address2'] )
		? $customer_info['address2']
		: '';

	$address['city'] = isset( $customer_info['city'] )
		? $customer_info['city']
		: '';

	$address['country'] = isset( $customer_info['country'] )
		? $customer_info['country']
		: '';

	$address['postal_code'] = isset( $customer_info['postal_code'] )
		? $customer_info['postal_code']
		: '';

	$address['region'] = isset( $customer_info['region'] )
		? $customer_info['region']
		: '';

	// Sanitize the inputs
	$customer_data                 = array();
	$customer_data['name']         = strip_tags( stripslashes( $customer_info['name'] ) );
	$customer_data['email']        = $customer_info['email'];
	$customer_data['user_id']      = $user_id;
	$customer_data['date_created'] = gmdate( 'Y-m-d H:i:s', strtotime( $customer_info['date_created'] ) );
	$customer_data['status']       = $customer_info['status'];

	$customer_data = apply_filters( 'edd_edit_customer_info', $customer_data, $customer_id );
	$address       = apply_filters( 'edd_edit_customer_address', $address, $customer_id );

	$customer_data = array_map( 'sanitize_text_field', $customer_data );
	$address       = array_map( 'sanitize_text_field', $address );

	do_action( 'edd_pre_edit_customer', $customer_id, $customer_data, $address );

	$output         = array();
	$previous_email = $customer->email;

	// Add new address before update to skip exists checks
	if ( $previous_email !== $customer_data['email'] ) {
		$customer->add_email( $customer_data['email'], true );
	}

	// Update customer
	if ( $customer->update( $customer_data ) ) {
		$current_address        = $customer->get_address();
		$address['customer_id'] = $customer->id;

		if ( $current_address ) {
			edd_update_customer_address( $current_address->id, $address );
		} else {
			$address['is_primary'] = true;
			edd_add_customer_address( $address );
		}

		$output['success']       = true;
		$customer_data           = array_merge( $customer_data, $address );
		$output['customer_info'] = $customer_data;
	} else {
		$output['success'] = false;
	}

	do_action( 'edd_post_edit_customer', $customer_id, $customer_data );

	if ( edd_doing_ajax() ) {
		wp_send_json( $output );
	}

	return $output;
}
add_action( 'edd_edit-customer', 'edd_edit_customer', 10, 1 );

/**
 * Add an email address to the customer from within the admin and log a customer note
 *
 * @since  2.6
 * @param  array $args  Array of arguments: nonce, customer id, and email address
 * @return mixed        Echos JSON if doing AJAX. Returns array of success (bool) and message (string) if not AJAX.
 */
function edd_add_customer_email( $args = array() ) {

	if ( ! is_admin() || ! current_user_can( edd_get_edit_customers_role() ) ) {
		wp_die( __( 'You do not have permission to edit this customer.', 'easy-digital-downloads' ) );
	}

	$output = array();

	if ( empty( $args ) || empty( $args['email'] ) || empty( $args['customer_id'] ) ) {

		$output['success'] = false;

		if ( empty( $args['email'] ) ) {
			$output['message'] = __( 'Email address is missing.', 'easy-digital-downloads' );
		} else if ( empty( $args['customer_id'] ) ) {
			$output['message'] = __( 'Customer ID is required.', 'easy-digital-downloads' );
		} else {
			$output['message'] = __( 'An error has occured. Please try again.', 'easy-digital-downloads' );
		}

	} else if ( ! wp_verify_nonce( $args['_wpnonce'], 'edd-add-customer-email' ) ) {
		$output = array(
			'success' => false,
			'message' => __( 'Nonce verification failed.', 'easy-digital-downloads' ),
		);

	} else if ( ! is_email( $args['email'] ) ) {
		$output = array(
			'success' => false,
			'message' => __( 'Invalid email address.', 'easy-digital-downloads' ),
		);

	} else {
		$email             = sanitize_email( $args['email'] );
		$customer_id       = (int) $args['customer_id'];
		$primary           = 'true' === $args['primary'] ? true : false;
		$customer          = new EDD_Customer( $customer_id );
		$customer_email_id = $customer->add_email( $email, $primary );

		if ( false === $customer_email_id ) {

			if ( in_array( $email, $customer->emails, true ) ) {
				$output = array(
					'success'  => false,
					'message'  => __( 'Email already associated with this customer.', 'easy-digital-downloads' ),
				);

			} else {
				$output = array(
					'success' => false,
					'message' => __( 'Email address is already associated with another customer.', 'easy-digital-downloads' ),
				);
			}

		} else {
			$redirect = edd_get_admin_url(
				array(
					'page'         => 'edd-customers',
					'view'         => 'overview',
					'id'           => absint( $customer_id ),
					'edd-message'  => 'email-added',
					'edd-email-id' => absint( $customer_email_id ),
				)
			);
			$output   = array(
				'success'  => true,
				'message'  => __( 'Email successfully added to customer.', 'easy-digital-downloads' ),
				'redirect' => $redirect . '#edd_general_emails',
			);

			$user       = wp_get_current_user();
			$user_login = ! empty( $user->user_login ) ? $user->user_login : edd_get_bot_name();
			/* translators: 1. email address; 2. username */
			$customer_note = sprintf( __( 'Email address %1$s added by %2$s', 'easy-digital-downloads' ), $email, $user_login );
			$customer->add_note( $customer_note );

			if ( $primary ) {
				/* translators: 1. email address; 2. username */
				$customer_note =  sprintf( __( 'Email address %1$s set as primary by %2$s', 'easy-digital-downloads' ), $email, $user_login );
				$customer->add_note( $customer_note );
			}
		}
	}

	if ( ! isset( $customer_id ) ) {
		$customer_id = isset( $args['customer_id'] ) ? $args['customer_id'] : false;
	}

	do_action( 'edd_post_add_customer_email', $customer_id, $args );

	if ( edd_doing_ajax() ) {
		wp_send_json( $output );
	}

	return $output;
}
add_action( 'edd_customer-add-email', 'edd_add_customer_email', 10, 1 );

/**
 * Remove an email address to the customer from within the admin and log a customer note
 * and redirect back to the customer interface for feedback
 *
 * @since  2.6
 * @return void
 */
function edd_remove_customer_email() {
	if ( empty( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		return false;
	}

	if ( empty( $_GET['email'] ) || ! is_email( $_GET['email'] ) ) {
		return false;
	}

	if ( empty( $_GET['_wpnonce'] ) ) {
		return false;
	}

	$nonce = $_GET['_wpnonce'];
	if ( ! wp_verify_nonce( $nonce, 'edd-remove-customer-email' ) ) {
		wp_die( __( 'Nonce verification failed.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	if ( ! current_user_can( edd_get_edit_customers_role() ) ) {
		wp_die( __( 'You do not have permission to edit this customer.', 'easy-digital-downloads' ) );
	}

	$customer = new EDD_Customer( $_GET['id'] );
	if ( $customer->remove_email( $_GET['email'] ) ) {
		$url           = edd_get_admin_url(
			array(
				'page'        => 'edd-customers',
				'view'        => 'overview',
				'id'          => urlencode( $customer->id ),
				'edd-message' => 'email-removed',
			)
		);
		$user       = wp_get_current_user();
		$user_login = ! empty( $user->user_login ) ? $user->user_login : edd_get_bot_name();
		/* translators: 1. email address; 2. username */
		$customer_note = sprintf( __( 'Email address %1$s removed by %2$s', 'easy-digital-downloads' ), sanitize_email( $_GET['email'] ), $user_login );
		$customer->add_note( $customer_note );

	} else {
		$url = edd_get_admin_url(
			array(
				'page'        => 'edd-customers',
				'view'        => 'overview',
				'id'          => urlencode( $customer->id ),
				'edd-message' => 'email-remove-failed',
			)
		);
	}

	edd_redirect( $url . '#edd_general_emails' );
}
add_action( 'edd_customer-remove-email', 'edd_remove_customer_email', 10 );

/**
 * Set an email address as the primary for a customer from within the admin and log a customer note
 * and redirect back to the customer interface for feedback
 *
 * @since  2.6
 * @return void
 */
function edd_set_customer_primary_email() {
	if ( empty( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		return false;
	}

	if ( empty( $_GET['email'] ) || ! is_email( $_GET['email'] ) ) {
		return false;
	}

	if ( empty( $_GET['_wpnonce'] ) ) {
		return false;
	}

	$nonce = $_GET['_wpnonce'];
	if ( ! wp_verify_nonce( $nonce, 'edd-set-customer-primary-email' ) ) {
		wp_die( __( 'Nonce verification failed.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	if ( ! current_user_can( edd_get_edit_customers_role() ) ) {
		wp_die( __( 'You do not have permission to edit this customer.', 'easy-digital-downloads' ) );
	}

	$customer = new EDD_Customer( $_GET['id'] );
	if ( $customer->set_primary_email( $_GET['email'] ) ) {
		$url           = edd_get_admin_url(
			array(
				'page'        => 'edd-customers',
				'view'        => 'overview',
				'id'          => urlencode( $customer->id ),
				'edd-message' => 'primary-email-updated',
			)
		);
		$user          = wp_get_current_user();
		$user_login    = ! empty( $user->user_login ) ? $user->user_login : edd_get_bot_name();
		$customer_note = sprintf( __( 'Email address %s set as primary by %s', 'easy-digital-downloads' ), sanitize_email( $_GET['email'] ), $user_login );
		$customer->add_note( $customer_note );

	} else {
		$url = edd_get_admin_url(
			array(
				'page'        => 'edd-customers',
				'view'        => 'overview',
				'id'          => urlencode( $customer->id ),
				'edd-message' => 'primary-email-failed',
			)
		);
	}

	edd_redirect( $url . '#edd_general_emails' );
}
add_action( 'edd_customer-primary-email', 'edd_set_customer_primary_email', 10 );

/**
 * Delete a customer
 *
 * @since  2.3
 * @param  array $args The $_POST array being passed
 * @return int         Whether it was a successful deletion
 */
function edd_customer_delete( $args = array() ) {

	if ( ! is_admin() || ! current_user_can( edd_get_edit_customers_role() ) ) {
		wp_die( __( 'You do not have permission to delete this customer.', 'easy-digital-downloads' ) );
	}

	if ( empty( $args ) ) {
		return;
	}

	$customer_id = (int)$args['customer_id'];
	$confirm     = ! empty( $args['edd-customer-delete-confirm'] );
	$remove_data = ! empty( $args['edd-customer-delete-records'] );
	$nonce       = $args['_wpnonce'];

	if ( ! wp_verify_nonce( $nonce, 'delete-customer' ) ) {
		wp_die( __( 'Nonce verification failed.', 'easy-digital-downloads' ) );
	}

	if ( ! $confirm ) {
		edd_set_error( 'customer-delete-no-confirm', __( 'Please confirm you want to delete this customer', 'easy-digital-downloads' ) );
	}

	if ( edd_get_errors() ) {
		edd_redirect(
			edd_get_admin_url(
				array(
					'page' => 'edd-customers',
					'view' => 'overview',
					'id'   => absint( $customer_id ),
				)
			)
		);
	}

	$customer = new EDD_Customer( $customer_id );

	do_action( 'edd_pre_delete_customer', $customer_id, $confirm, $remove_data );

	$success = false;

	if ( $customer->id > 0 ) {

		$payments_array = explode( ',', $customer->payment_ids );
		$success        = edd_destroy_customer( $customer->id );

		if ( $success ) {

			if ( $remove_data ) {

				// Remove all payments, logs, etc
				foreach ( $payments_array as $payment_id ) {
					edd_destroy_order( $payment_id );
				}

			} else {

				// Just set the payments to customer_id of 0
				foreach ( $payments_array as $payment_id ) {
					edd_update_payment_meta( $payment_id, '_edd_payment_customer_id', 0 );
				}
			}

			$redirect = edd_get_admin_url( array( 'page' => 'edd-customers', 'edd-message' => 'customer-deleted' ) );

		} else {
			edd_set_error( 'edd-customer-delete-failed', __( 'Error deleting customer', 'easy-digital-downloads' ) );
			$redirect = edd_get_admin_url( array( 'page' => 'edd-customers', 'view' => 'delete', 'id' => absint( $customer_id ) ) );
		}

	} else {
		edd_set_error( 'edd-customer-delete-invalid-id', __( 'Invalid Customer ID', 'easy-digital-downloads' ) );
		$redirect = edd_get_admin_url( array( 'page' => 'edd-customers' ) );
	}

	edd_redirect( $redirect );
}
add_action( 'edd_delete-customer', 'edd_customer_delete', 10, 1 );

/**
 * Disconnect a user ID from a customer
 *
 * @since  2.3
 * @param  array $args Array of arguments
 * @return bool        If the disconnect was successful
 */
function edd_disconnect_customer_user_id( $args = array() ) {
	if ( ! is_admin() || ! current_user_can( edd_get_edit_customers_role() ) ) {
		wp_die( __( 'You do not have permission to edit this customer.', 'easy-digital-downloads' ) );
	}

	if ( empty( $args ) ) {
		return;
	}

	$customer_id   = (int)$args['customer_id'];
	$nonce         = $args['_wpnonce'];

	if ( ! wp_verify_nonce( $nonce, 'edit-customer' ) ) {
		wp_die( __( 'Nonce verification failed.', 'easy-digital-downloads' ) );
	}

	$customer = new EDD_Customer( $customer_id );
	if ( empty( $customer->id ) ) {
		return false;
	}

	do_action( 'edd_pre_customer_disconnect_user_id', $customer_id, $customer->user_id );

	$customer_args = array( 'user_id' => 0 );

	if ( $customer->update( $customer_args ) ) {

		$output['success'] = true;

	} else {

		$output['success'] = false;
		edd_set_error( 'edd-disconnect-user-fail', __( 'Failed to disconnect user from customer', 'easy-digital-downloads' ) );
	}

	do_action( 'edd_post_customer_disconnect_user_id', $customer_id );

	if ( edd_doing_ajax() ) {
		wp_send_json( $output );
	}

	return $output;
}
add_action( 'edd_disconnect-userid', 'edd_disconnect_customer_user_id', 10, 1 );

/**
 * Process manual verification of customer account by admin
 *
 * @since  2.4.8
 * @return void
 */
function edd_process_admin_user_verification() {

	if ( empty( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		return false;
	}

	if ( empty( $_GET['_wpnonce'] ) ) {
		return false;
	}

	$nonce = $_GET['_wpnonce'];
	if ( ! wp_verify_nonce( $nonce, 'edd-verify-user' ) ) {
		wp_die( __( 'Nonce verification failed.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	if ( ! is_admin() || ! current_user_can( edd_get_edit_customers_role() ) ) {
		wp_die( __( 'You do not have permission to edit this customer.', 'easy-digital-downloads' ) );
	}

	$customer = new EDD_Customer( $_GET['id'] );
	edd_set_user_to_verified( $customer->user_id );

	$url = edd_get_admin_url(
		array(
			'page'        => 'edd-customers',
			'view'        => 'overview',
			'id'          => absint( $customer->id ),
			'edd-message' => 'user-verified',
		)
	);

	edd_redirect( $url );
}
add_action( 'edd_verify_user_admin', 'edd_process_admin_user_verification' );

/**
 * Register the reset single customer stats batch processor
 * @since  2.5
 */
function edd_register_batch_single_customer_recount_tool() {
	add_action( 'edd_batch_export_class_include', 'edd_include_single_customer_recount_tool_batch_processer', 10, 1 );
}
add_action( 'edd_register_batch_exporter', 'edd_register_batch_single_customer_recount_tool', 10 );

/**
 * Loads the tools batch processing class for recounting stats for a single customer
 *
 * @since  2.5
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function edd_include_single_customer_recount_tool_batch_processer( $class ) {
	if ( 'EDD_Tools_Recount_Single_Customer_Stats' === $class ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/tools/class-edd-tools-recount-single-customer-stats.php';
	}
}

/**
 * Removes a customer address
 *
 * @since  3.0
 * @return void
 */
function edd_remove_customer_address() {
	if ( ! is_admin() || ! current_user_can( edd_get_edit_customers_role() ) ) {
		wp_die( __( 'You do not have permission to perform this action.', 'easy-digital-downloads' ) );
	}

	if ( empty( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) || empty( $_GET['_wpnonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'edd-remove-customer-address' ) ) {
		wp_die( __( 'Nonce verification failed.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	$address = edd_fetch_customer_address( absint( $_GET['id'] ) );
	$removed = $address instanceof EDD\Customers\Customer_Address ? edd_delete_customer_address( absint( $_GET['id'] ) ) : false;

	$url = edd_get_admin_url( array(
		'page'        => 'edd-customers',
		'view'        => 'overview',
		'id'          => urlencode( $address->customer_id ),
		'edd-message' => 'address-removed'
	) );

	if ( $removed ) {
		$url = add_query_arg( 'edd-message', 'address-removed', $url );
	} else {
		$url = add_query_arg( 'edd-message', 'address-remove-failed', $url );
	}

	edd_redirect( $url . '#edd_general_addresses' );
}
add_action( 'edd_customer-remove-address', 'edd_remove_customer_address', 10 );
