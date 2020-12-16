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
		empty( $request['_wpnonce']   ) ||
		empty( $request['id']         ) ||
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
	if ( ! empty( $request['status'] ) && ( 'inherit' === $request['status'] ) || in_array( $request['status'], array_keys( edd_get_payment_statuses() ), true )) {
		$data['status'] = sanitize_key( $request['status'] );
	}

	// Update order item
	if ( ! empty( $data ) ) {
		edd_update_order_item( $request['order_item'], $data );
		edd_redirect( edd_get_admin_url( array(
			'page' => 'edd-payment-history',
			'view' => 'view-order-details',
			'id'   => absint( $request['id'] )
		) ) );
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

	// The date is entered in the WP timezone. We need to convert it to UTC prior to saving now.
	$date = edd_get_utc_equivalent_date( EDD()->utils->date( $date . ' ' . $hour . ':' . $minute . ':00', edd_get_timezone_id(), false ) );
	$date = $date->format( 'Y-m-d H:i:s' );

	$order_update_args['date_created'] = $date;

	// Customer
	$curr_customer_id = sanitize_text_field( $data['current-customer-id'] );
	$new_customer_id  = sanitize_text_field( $data['customer-id'] );

	// Create a new customer.
	if ( isset( $data['edd-new-customer'] ) && 1 === (int) $data['edd-new-customer'] ) {
		$email = isset( $data['edd-new-customer-email'] )
			? sanitize_text_field( $data['edd-new-customer-email'] )
			: '';

		// Sanitize first name
		$first_name = isset( $data['edd-new-customer-first-name'] )
			? sanitize_text_field( $data['edd-new-customer-first-name'] )
			: '';

		// Sanitize last name
		$last_name = isset( $data['edd-new-customer-last-name'] )
			? sanitize_text_field( $data['edd-new-customer-last-name'] )
			: '';

		// Combine
		$name = $first_name . ' ' . $last_name;

		if ( empty( $email ) || empty( $name ) ) {
			wp_die( esc_html__( 'New Customers require a name and email address', 'easy-digital-downloads' ) );
		}

		$customer = new EDD_Customer( $email );

		if ( empty( $customer->id ) ) {
			$customer_data = array(
				'name'  => $name,
				'email' => $email,
			);

			$user_id = email_exists( $email );

			if ( false !== $user_id ) {
				$customer_data['user_id'] = $user_id;
			}

			if ( ! $customer->create( $customer_data ) ) {
				$customer = new EDD_Customer( $curr_customer_id );
				edd_set_error( 'edd-payment-new-customer-fail', __( 'Error creating new customer', 'easy-digital-downloads' ) );
			}
		} else {
			wp_die( sprintf( __( 'A customer with the email address %s already exists. Please go back and assign this payment to them.', 'easy-digital-downloads' ), $email ) );
		}

		$previous_customer = new EDD_Customer( $curr_customer_id );
	} elseif ( $curr_customer_id !== $new_customer_id ) {
		$customer = new EDD_Customer( $new_customer_id );

		$previous_customer = new EDD_Customer( $curr_customer_id );
	} else {
		$customer = new EDD_Customer( $curr_customer_id );
	}

	// Remove the stats and payment from the previous customer and attach it to the new customer
	if ( isset( $previous_customer ) ) {
		$previous_customer->remove_payment( $order_id, false );
		$customer->attach_payment( $order_id, false );

		// If purchase was completed and not ever refunded, adjust stats of customers
		if ( 'revoked' === $new_status || 'complete' === $new_status ) {
			$previous_customer->recalculate_stats();

			if ( ! empty( $customer ) ) {
				$customer->recalculate_stats();
			}
		}

		$order_update_args['customer_id'] = $customer->id;
	}

	$order_update_args['user_id'] = $customer->user_id;
	$order_update_args['email']   = $customer->email;

	// Address
	$address = $data['edd_order_address'];

	// Setup first and last name from input values.
	$name       = $customer->name;
	$names      = explode( ' ', $name );
	$first_name = ! empty( $names[0] ) ? $names[0] : '';
	$last_name  = '';

	if ( ! empty( $names[1] ) ) {
		unset( $names[0] );
		$last_name = implode( ' ', $names );
	}

	edd_update_order_address( absint( $address['address_id'] ), array(
		'first_name'  => $first_name,
		'last_name'   => $last_name,
		'address'     => $address['address'],
		'address2'    => $address['address2'],
		'city'        => $address['city'],
		'region'      => $address['region'],
		'postal_code' => $address['postal_code'],
		'country'     => $address['country'],
	) );

	// Unlimited downloads.
	if ( 1 === (int) $unlimited ) {
		edd_update_order_meta( $order_id, 'unlimited_downloads', $unlimited );
	} else {
		edd_delete_order_meta( $order_id, 'unlimited_downloads' );
	}

	// Don't set the status in the update call (for back compat)
	unset( $order_update_args['status'] );

	// Attempt to update the order
	$updated = edd_update_order( $order_id, $order_update_args );

	// Bail if an error occurred
	if ( false === $updated ) {
		wp_die( __( 'Error updating order.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 400 ) );
	}

	// Check if the status has changed, if so, we need to invoke the pertinent
	// status processing method (for back compat)
	if ( $new_status !== $order->status ) {
		edd_update_order_status( $order_id, $new_status );
	}

	do_action( 'edd_updated_edited_purchase', $order_id );

	edd_redirect( edd_get_admin_url( array(
		'page'        => 'edd-payment-history',
		'view'        => 'view-order-details',
		'edd-message' => 'payment-updated',
		'id'          => $order_id
	) ) );
}
add_action( 'edd_update_payment_details', 'edd_update_payment_details' );

/**
 * Trigger a Purchase Deletion
 *
 * @since 1.3.4
 * @param array $data Arguments passed
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
 * New in 3.0, destroys an order, and all it's data, and related data.
 *
 * @since 3.0
 *
 * @param $data
 */
function edd_trigger_destroy_order( $data ) {
	if ( wp_verify_nonce( $data['_wpnonce'], 'edd_payment_nonce' ) ) {

		$payment_id = absint( $data['purchase_id'] );

		if ( ! current_user_can( 'delete_shop_payments', $payment_id ) ) {
			wp_die( __( 'You do not have permission to edit this payment record', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		edd_destroy_order( $payment_id );

		edd_redirect( admin_url( '/edit.php?post_type=download&page=edd-payment-history&edd-message=payment_deleted' ) );
	}
}
add_action( 'edd_delete_order', 'edd_trigger_destroy_order' );

/**
 * Trigger the action of moving an order to the 'trash' status
 *
 * @since 3.0
 *
 * @param $data
 * @return void
 */
function edd_trigger_trash_order( $data ) {
	if ( wp_verify_nonce( $data['_wpnonce'], 'edd_payment_nonce' ) ) {

		$payment_id = absint( $data['purchase_id'] );

		if ( ! current_user_can( 'delete_shop_payments', $payment_id ) ) {
			wp_die( __( 'You do not have permission to edit this payment record', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		edd_trash_order( $payment_id );

		$redirect = edd_get_admin_url( array(
			'page'        => 'edd-payment-history',
			'edd-message' => 'order_trashed',
			'order_type'  => esc_attr( $data['order_type'] ),
		) );

		edd_redirect( esc_url_raw( $redirect ) );
	}
}
add_action( 'edd_trash_order', 'edd_trigger_trash_order' );

/**
 * Trigger the action of restoring an order from the 'trash' status
 *
 * @since 3.0
 *
 * @param $data
 * @return void
 */
function edd_trigger_restore_order( $data ) {
	if ( wp_verify_nonce( $data['_wpnonce'], 'edd_payment_nonce' ) ) {

		$payment_id = absint( $data['purchase_id'] );

		if ( ! current_user_can( 'delete_shop_payments', $payment_id ) ) {
			wp_die( __( 'You do not have permission to edit this payment record', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		edd_restore_order( $payment_id );

		$redirect = edd_get_admin_url( array(
			'page'        => 'edd-payment-history',
			'edd-message' => 'order_restored',
			'order_type'  => esc_attr( $data['order_type'] ),
		) );

		edd_redirect( esc_url_raw( $redirect ) );
	}
}
add_action( 'edd_restore_order', 'edd_trigger_restore_order' );

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

	foreach( $files as $file_key => $file ) {
		$file_urls .= edd_get_download_file_url( $payment_key, $email, $file_key, $download_id, $price_id );
		$file_urls .= "\n\n";
	}

	die( $file_urls );
}
add_action( 'wp_ajax_edd_get_file_download_link', 'edd_ajax_generate_file_download_link' );

/**
 * Renders the refund form that is used to process a refund.
 *
 * @since 3.0
 *
 * @return void
 */
function edd_ajax_generate_refund_form() {

	// Verify we have a logged user.
	if ( ! is_user_logged_in() ) {
		$return = array(
			'success' => false,
			'message' => __( 'You must be logged in to perform this action.', 'easy-digital-downloads' ),
		);

		wp_send_json( $return, 401 );
	}

	// Verify the logged in user has permission to edit shop payments.
	if ( ! current_user_can( 'edit_shop_payments' ) ) {
		$return = array(
			'success' => false,
			'message' => __( 'Your account does not have permission to perform this action.', 'easy-digital-downloads' ),
		);

		wp_send_json( $return, 401 );
	}

	$order_id = isset( $_POST['order_id'] ) && is_numeric( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : false;

	if ( empty( $order_id ) ) {
		$return = array(
			'success' => false,
			'message' => __( 'Invalid order ID', 'easy-digital-downloads' ),
		);

		wp_send_json( $return, 400 );
	}

	$order = edd_get_order( $order_id );
	if ( empty( $order ) ) {
		$return = array(
			'success' => false,
			'message' => __( 'Invalid order', 'easy-digital-downloads' ),
		);

		wp_send_json( $return, 404 );
	}

	if ( 'refunded' === $order->status ) {
		$return = array(
			'success' => false,
			'message' => __( 'Order is already refunded', 'easy-digital-downloads' ),
		);

		wp_send_json( $return, 404 );
	}

	if ( 'refund' === $order->type ) {
		$return = array(
			'success' => false,
			'message' => __( 'Cannot refund an order that is already refunded.', 'easy-digital-downloads' ),
		);

		wp_send_json( $return, 404 );
	}

	// Output buffer the form before we include it in the JSON response.
	ob_start();
	?>
	<div id="edd-submit-refund-status" style="display: none;">
		<span class="edd-submit-refund-message"></span>
		<a class="edd-submit-refund-url" href=""><?php _e( 'View Refund', 'easy-digital-downloads' ); ?></a>
	</div>
	<?php
	// Load list table if not already loaded
	if ( ! class_exists( '\\EDD\\Admin\\Refund_Items_Table' ) ) {
		require_once 'class-refund-items-table.php';
	}

	$refund_items = new EDD\Admin\Refund_Items_Table();
	$refund_items->prepare_items();
	$refund_items->display();
	?>
	</table>
	<?php
	$html = trim( ob_get_clean() );

	$return = array(
		'success' => true,
		'html'    => $html,
	);

	wp_send_json( $return, 200 );

}
add_action( 'wp_ajax_edd_generate_refund_form', 'edd_ajax_generate_refund_form' );

/**
 * Processes the results from the Submit Refund form
 *
 * @since 3.0
 * @return void
 */
function edd_ajax_process_refund_form() {

	// Verify we have a logged user.
	if ( ! is_user_logged_in() ) {
		$return = array(
			'success' => false,
			'message' => __( 'You must be logged in to perform this action.', 'easy-digital-downloads' ),
		);

		wp_send_json( $return, 401 );
	}

	// Verify the logged in user has permission to edit shop payments.
	if ( ! current_user_can( 'edit_shop_payments' ) ) {
		$return = array(
			'success' => false,
			'message' => __( 'Your account does not have permission to perform this action', 'easy-digital-downloads' ),
		);

		wp_send_json( $return, 401 );
	}

	// Verify the nonce.
	$nonce = ! empty( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : false;
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'edd_process_refund' ) ) {
		$return = array(
			'success' => false,
			'message' => __( 'Nonce validation failed when submitting refund.', 'easy-digital-downloads' ),
		);

		wp_send_json( $return, 401 );
	}

	$order_id = absint( $_POST['order_id'] );
	$item_ids = array_map( 'absint', $_POST['item_ids'] );
	$refund_id = edd_refund_order( $order_id, $item_ids );

	if ( ! empty( $refund_id ) ) {
		$return = array(
			'success'    => true,
			'refund_id'  => $refund_id,
			'message'    => sprintf( __( 'Refund successfully processed.', 'easy-digital-downloads' ) ),
			'refund_url' => edd_get_admin_url(
				array(
					'page'      => 'edd-payment-history',
					'view'      => 'view-refund-details',
					'id'        => $refund_id,
				)
			)
		);
		wp_send_json( $return, 200 );
	} else {
		$return = array(
			'success'    => false,
			'message'    => sprintf( __( 'Unable to process refund.', 'easy-digital-downloads' ) ),
		);

		wp_send_json( $return, 200 );
	}
}
add_action( 'wp_ajax_edd_process_refund_form', 'edd_ajax_process_refund_form' );

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
	if ( '-1' === $action ) {
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
			case 'trash':
				edd_trash_order( $id );
				break;
			case 'restore':
				edd_restore_order( $id );
				break;
			case 'set-status-complete':
				edd_update_payment_status( $id, 'complete' );
				break;

			case 'set-status-pending':
				edd_update_payment_status( $id, 'pending' );
				break;

			case 'set-status-processing':
				edd_update_payment_status( $id, 'processing' );
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
