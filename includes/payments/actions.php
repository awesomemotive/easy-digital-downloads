<?php
/**
 * Payment Actions
 *
 * @package     EDD
 * @subpackage  Payments
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Complete a purchase
 *
 * Performs all necessary actions to complete a purchase.
 * Triggered by the edd_update_payment_status() function.
 *
 * @since 1.0.8.3
 * @param int $payment_id the ID number of the payment
 * @param string $new_status the status of the payment, probably "publish"
 * @param string $old_status the status of the payment prior to being marked as "complete", probably "pending"
 * @return void
*/
function edd_complete_purchase( $payment_id, $new_status, $old_status ) {
	if ( $old_status == 'publish' || $old_status == 'complete' ) {
		return; // Make sure that payments are only completed once
	}

	// Make sure the payment completion is only processed when new status is complete
	if ( $new_status != 'publish' && $new_status != 'complete' ) {
		return;
	}

	$payment = new EDD_Payment( $payment_id );

	$creation_date  = get_post_field( 'post_date', $payment_id, 'raw' );
	$completed_date = $payment->completed_date;
	$user_info      = $payment->user_info;
	$customer_id    = $payment->customer_id;
	$amount         = $payment->total;
	$cart_details   = $payment->cart_details;

	do_action( 'edd_pre_complete_purchase', $payment_id );

	if ( is_array( $cart_details ) ) {

		// Increase purchase count and earnings
		foreach ( $cart_details as $cart_index => $download ) {

			// "bundle" or "default"
			$download_type = edd_get_download_type( $download['id'] );
			$price_id      = isset( $download['item_number']['options']['price_id'] ) ? (int) $download['item_number']['options']['price_id'] : false;
			// Increase earnings and fire actions once per quantity number
			for( $i = 0; $i < $download['quantity']; $i++ ) {

				// Ensure these actions only run once, ever
				if ( empty( $completed_date ) ) {

					edd_record_sale_in_log( $download['id'], $payment_id, $price_id, $creation_date );
					do_action( 'edd_complete_download_purchase', $download['id'], $payment_id, $download_type, $download, $cart_index );

				}

			}

			$increase_earnings = $download['price'];
			if ( ! empty( $download['fees'] ) ) {
				foreach ( $download['fees'] as $fee ) {
					if ( $fee['amount'] > 0 ) {
						continue;
					}
					$increase_earnings += $fee['amount'];
				}
			}

			// Increase the earnings for this download ID
			edd_increase_earnings( $download['id'], $increase_earnings );
			edd_increase_purchase_count( $download['id'], $download['quantity'] );

		}

		// Clear the total earnings cache
		delete_transient( 'edd_earnings_total' );
		// Clear the This Month earnings (this_monththis_month is NOT a typo)
		delete_transient( md5( 'edd_earnings_this_monththis_month' ) );
		delete_transient( md5( 'edd_earnings_todaytoday' ) );
	}


	// Increase the customer's purchase stats
	$customer = new EDD_Customer( $customer_id );
	$customer->increase_purchase_count();
	$customer->increase_value( $amount );

	edd_increase_total_earnings( $amount );

	// Check for discount codes and increment their use counts
	if ( ! empty( $user_info['discount'] ) && $user_info['discount'] !== 'none' ) {

		$discounts = array_map( 'trim', explode( ',', $user_info['discount'] ) );

		if( ! empty( $discounts ) ) {

			foreach( $discounts as $code ) {

				edd_increase_discount_usage( $code );

			}

		}
	}


	// Ensure this action only runs once ever
	if( empty( $completed_date ) ) {

		// Save the completed date
		$payment->completed_date = current_time( 'mysql' );
		$payment->save();

		do_action( 'edd_complete_purchase', $payment_id );

	}

	// Empty the shopping cart
	edd_empty_cart();
}
add_action( 'edd_update_payment_status', 'edd_complete_purchase', 100, 3 );


/**
 * Record payment status change
 *
 * @since 1.4.3
 * @param int $payment_id the ID number of the payment
 * @param string $new_status the status of the payment, probably "publish"
 * @param string $old_status the status of the payment prior to being marked as "complete", probably "pending"
 * @return void
 */
function edd_record_status_change( $payment_id, $new_status, $old_status ) {

	// Get the list of statuses so that status in the payment note can be translated
	$stati      = edd_get_payment_statuses();
	$old_status = isset( $stati[ $old_status ] ) ? $stati[ $old_status ] : $old_status;
	$new_status = isset( $stati[ $new_status ] ) ? $stati[ $new_status ] : $new_status;

	$status_change = sprintf( __( 'Status changed from %s to %s', 'easy-digital-downloads' ), $old_status, $new_status );

	edd_insert_payment_note( $payment_id, $status_change );
}
add_action( 'edd_update_payment_status', 'edd_record_status_change', 100, 3 );

/**
 * Flushes the current user's purchase history transient when a payment status
 * is updated
 *
 * @since 1.2.2
 *
 * @param int $payment_id the ID number of the payment
 * @param string $new_status the status of the payment, probably "publish"
 * @param string $old_status the status of the payment prior to being marked as "complete", probably "pending"
 */
function edd_clear_user_history_cache( $payment_id, $new_status, $old_status ) {
	$payment = new EDD_Payment( $payment_id );

	if( ! empty( $payment->user_id ) ) {
		delete_transient( 'edd_user_' . $payment->user_id . '_purchases' );
	}
}
add_action( 'edd_update_payment_status', 'edd_clear_user_history_cache', 10, 3 );

/**
 * Updates all old payments, prior to 1.2, with new
 * meta for the total purchase amount
 *
 * This is so that payments can be queried by their totals
 *
 * @since 1.2
 * @param array $data Arguments passed
 * @return void
*/
function edd_update_old_payments_with_totals( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'edd_upgrade_payments_nonce' ) ) {
		return;
	}

	if ( get_option( 'edd_payment_totals_upgraded' ) ) {
		return;
	}

	$payments = edd_get_payments( array(
		'offset' => 0,
		'number' => -1,
		'mode'   => 'all',
	) );

	if ( $payments ) {
		foreach ( $payments as $payment ) {

			$payment = new EDD_Payment( $payment->ID );
			$meta    = $payment->get_meta();

			$payment->total = $meta['amount'];
			$payment->save();
		}
	}

	add_option( 'edd_payment_totals_upgraded', 1 );
}
add_action( 'edd_upgrade_payments', 'edd_update_old_payments_with_totals' );

/**
 * Updates week-old+ 'pending' orders to 'abandoned'
 *
 * @since 1.6
 * @return void
*/
function edd_mark_abandoned_orders() {
	$args = array(
		'status' => 'pending',
		'number' => -1,
		'output' => 'edd_payments',
	);

	add_filter( 'posts_where', 'edd_filter_where_older_than_week' );

	$payments = edd_get_payments( $args );

	remove_filter( 'posts_where', 'edd_filter_where_older_than_week' );

	if( $payments ) {
		foreach( $payments as $payment ) {
			if( 'pending' === $payment->post_status ) {
				$payment->status = 'abandoned';
				$payment->save();
			}
		}
	}
}
add_action( 'edd_weekly_scheduled_events', 'edd_mark_abandoned_orders' );

/**
 * Listens to the updated_postmeta hook for our backwards compatible payment_meta updates, and runs through them
 *
 * @since  2.3
 * @param  int $meta_id    The Meta ID that was updated
 * @param  int $object_id  The Object ID that was updated (post ID)
 * @param  string $meta_key   The Meta key that was updated
 * @param  string|int|float $meta_value The Value being updated
 * @return bool|int             If successful the number of rows updated, if it fails, false
 */
function edd_update_payment_backwards_compat( $meta_id, $object_id, $meta_key, $meta_value ) {

	$meta_keys = array( '_edd_payment_meta', '_edd_payment_tax' );

	if ( ! in_array( $meta_key, $meta_keys ) ) {
		return;
	}

	global $wpdb;
	switch( $meta_key ) {

		case '_edd_payment_meta':
			$meta_value   = maybe_unserialize( $meta_value );

			if( ! isset( $meta_value['tax'] ) ){
				return;
			}

			$tax_value    = $meta_value['tax'];

			$data         = array( 'meta_value' => $tax_value );
			$where        = array( 'post_id'  => $object_id, 'meta_key' => '_edd_payment_tax' );
			$data_format  = array( '%f' );
			$where_format = array( '%d', '%s' );
			break;

		case '_edd_payment_tax':
			$tax_value    = ! empty( $meta_value ) ? $meta_value : 0;
			$current_meta = edd_get_payment_meta( $object_id, '_edd_payment_meta', true );

			$current_meta['tax'] = $tax_value;
			$new_meta            = maybe_serialize( $current_meta );

			$data         = array( 'meta_value' => $new_meta );
			$where        = array( 'post_id' => $object_id, 'meta_key' => '_edd_payment_meta' );
			$data_format  = array( '%s' );
			$where_format = array( '%d', '%s' );

			break;

	}

	$updated = $wpdb->update( $wpdb->postmeta, $data, $where, $data_format, $where_format );

	if ( ! empty( $updated ) ) {
		// Since we did a direct DB query, clear the postmeta cache.
		wp_cache_delete( $object_id, 'post_meta' );
	}

	return $updated;


}
add_action( 'updated_postmeta', 'edd_update_payment_backwards_compat', 10, 4 );

/**
 * Deletes edd_stats_ transients that have expired to prevent database clogs
 *
 * @since 2.6.7
 * @return void
*/
function edd_cleanup_stats_transients() {
	global $wpdb;

	if ( defined( 'WP_SETUP_CONFIG' ) ) {
		return;
	}

	if ( defined( 'WP_INSTALLING' ) ) {
		return;
	}

	$now        = current_time( 'timestamp' );
	$transients = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE '%\_transient_timeout\_edd\_stats\_%' AND option_value+0 < $now LIMIT 0, 200;" );
	$to_delete  = array();

	if( ! empty( $transients ) ) {

		foreach( $transients as $transient ) {

			$to_delete[] = $transient->option_name;
			$to_delete[] = str_replace( '_timeout', '', $transient->option_name );

		}

	}

	if ( ! empty( $to_delete ) ) {

		$option_names = implode( "','", $to_delete );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name IN ('$option_names')"  );

	}

}
add_action( 'edd_daily_scheduled_events', 'edd_cleanup_stats_transients' );

/**
 * Process an attempt to complete a recoverable payment.
 *
 * @since  2.7
 * @return void
 */
function edd_recover_payment() {
	if ( empty( $_GET['payment_id'] ) ) {
		return;
	}

	$payment = new EDD_Payment( $_GET['payment_id'] );
	if ( $payment->ID !== (int) $_GET['payment_id'] ) {
		return;
	}

	if ( ! $payment->is_recoverable() ) {
		return;
	}

	if ( is_user_logged_in() && $payment->user_id != get_current_user_id() ) {
		$redirect = get_permalink( edd_get_option( 'purchase_history_page' ) );
		edd_set_error( 'edd-payment-recovery-user-mismatch', __( 'Error resuming payment.', 'easy-digital-downloads' ) );
		wp_redirect( $redirect );
	}

	$payment->add_note( __( 'Payment recovery triggered URL', 'easy-digital-downloads' ) );

	// Empty out the cart.
	EDD()->cart->empty_cart();

	// Recover any downloads.
	foreach ( $payment->cart_details as $download ) {
		edd_add_to_cart( $download['id'], $download['item_number']['options'] );

		// Recover any item specific fees.
		if ( ! empty( $download['fees'] ) ) {
			foreach ( $download['fees'] as $fee ) {
				EDD()->fees->add_fee( $fee );
			}
		}
	}

	// Recover any global fees.
	foreach ( $payment->fees as $fee ) {
		EDD()->fees->add_fee( $fee );
	}

	// Recover any discounts.
	if ( 'none' !== $payment->discounts && ! empty( $payment->discounts ) ){
		$discounts = ! is_array( $payment->discounts ) ? explode( ',', $payment->discounts ) : $payment->discounts;

		foreach ( $discounts as $discount ) {
			edd_set_cart_discount( $discount );
		}
	}

	EDD()->session->set( 'edd_resume_payment', $payment->ID );

	$redirect_args = array( 'payment-mode' => $payment->gateway );
	$redirect      = add_query_arg( $redirect_args, edd_get_checkout_uri() );
	wp_redirect( $redirect );
	exit;
}
add_action( 'edd_recover_payment', 'edd_recover_payment' );

/**
 * If the payment trying to be recovered has a User ID associated with it, be sure it's the same user.
 *
 * @since  2.7
 * @return void
 */
function edd_recovery_user_mismatch() {
	if ( ! edd_is_checkout() ) {
		return;
	}

	$resuming_payment = EDD()->session->get( 'edd_resume_payment' );
	if ( $resuming_payment ) {
		$payment = new EDD_Payment( $resuming_payment );
		if ( is_user_logged_in() && $payment->user_id != get_current_user_id() ) {
			edd_empty_cart();
			edd_set_error( 'edd-payment-recovery-user-mismatch', __( 'Error resuming payment.', 'easy-digital-downloads' ) );
			wp_redirect( get_permalink( edd_get_option( 'purchase_page' ) ) );
			exit;
		}
	}
}
add_action( 'template_redirect', 'edd_recovery_user_mismatch' );

/**
 * If the payment trying to be recovered has a User ID associated with it, we need them to log in.
 *
 * @since  2.7
 * @return void
 */
function edd_recovery_force_login_fields() {
	$resuming_payment = EDD()->session->get( 'edd_resume_payment' );
	if ( $resuming_payment ) {
		$payment = new EDD_Payment( $resuming_payment );
		if ( $payment->user_id > 0 && ( ! is_user_logged_in() ) ) {
			?>
			<div class="edd-alert edd-alert-info">
				<p><?php _e( 'To complete this payment, please login to your account.', 'easy-digital-downloads' ); ?></p>
				<p>
					<a href="<?php echo wp_lostpassword_url(); ?>" title="<?php _e( 'Lost Password', 'easy-digital-downloads' ); ?>">
						<?php _e( 'Lost Password?', 'easy-digital-downloads' ); ?>
					</a>
				</p>
			</div>
			<?php
			$show_register_form = edd_get_option( 'show_register_form', 'none' );

			if ( 'both' === $show_register_form || 'login' === $show_register_form ) {
				return;
			}
			do_action( 'edd_purchase_form_login_fields' );
		}
	}
}
add_action( 'edd_purchase_form_before_register_login', 'edd_recovery_force_login_fields' );

/**
 * When processing the payment, check if the resuming payment has a user id and that it matches the logged in user.
 *
 * @since 2.7
 * @param $verified_data
 * @param $post_data
 */
function edd_recovery_verify_logged_in( $verified_data, $post_data ) {
	$resuming_payment = EDD()->session->get( 'edd_resume_payment' );
	if ( $resuming_payment ) {
		$payment = new EDD_Payment( $resuming_payment );
		if ( ! empty( $payment->user_id ) && ( ! is_user_logged_in() || $payment->user_id != get_current_user_id() ) ) {
			edd_set_error( 'recovery_requires_login', __( 'To complete this payment, please login to your account.', 'easy-digital-downloads' ) );
		}
	}
}
add_action( 'edd_checkout_error_checks', 'edd_recovery_verify_logged_in', 10, 2 );
