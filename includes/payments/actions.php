<?php
/**
 * Payment Actions
 *
 * @package     EDD
 * @subpackage  Payments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
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
 * @since 3.0 Updated to use new order methods.
 *
 * @param int    $order_id   Order ID.
 * @param string $new_status New order status.
 * @param string $old_status Old order status.
*/
function edd_complete_purchase( $order_id, $new_status, $old_status ) {

	// This specifically does not use edd_get_complete_order_statuses().
	$completed_statuses = array( 'publish', 'complete', 'completed' );
	// Make sure that payments are only completed once.
	if ( in_array( $old_status, $completed_statuses, true ) ) {
		return;
	}

	// Make sure the payment completion is only processed when new status is complete.
	if ( ! in_array( $new_status, $completed_statuses, true ) ) {
		return;
	}

	$order = edd_get_order( $order_id );

	if ( ! $order || 'sale' !== $order->type ) {
		return;
	}

	$completed_date = empty( $order->date_completed )
		? null
		: $order->date_completed;

	$customer_id = $order->customer_id;
	$amount      = $order->total;
	$order_items = $order->items;

	do_action( 'edd_pre_complete_purchase', $order_id );

	if ( is_array( $order_items ) ) {

		// Increase purchase count and earnings.
		foreach ( $order_items as $item ) {

			// "bundle" or "default"
			$download_type = edd_get_download_type( $item->product_id );

			// Increase earnings and fire actions once per quantity number.
			for ( $i = 0; $i < $item->quantity; $i++ ) {

				// Ensure these actions only run once, ever.
				if ( empty( $completed_date ) ) {

					// For backwards compatibility purposes, we need to construct an array and pass it
					// to edd_complete_download_purchase.
					$item_fees = array();

					foreach ( $item->get_fees() as $key => $item_fee ) {
						/** @var EDD\Orders\Order_Adjustment $item_fee */

						$download_id = $item->product_id;
						$price_id    = $item->price_id;
						$no_tax      = (bool) 0.00 === $item_fee->tax;
						$id          = is_null( $item_fee->type_key ) ? $item_fee->id : $item_fee->type_key;
						if ( array_key_exists( $id, $item_fees ) ) {
							$id .= '_2';
						}

						$item_fees[ $id ] = array(
							'amount'      => $item_fee->amount,
							'label'       => $item_fee->description,
							'no_tax'      => $no_tax ? $no_tax : false,
							'type'        => 'fee',
							'download_id' => $download_id,
							'price_id'    => $price_id ? $price_id : null,
						);
					}

					$item_options = array(
						'quantity' => $item->quantity,
						'price_id' => $item->price_id,
					);

					/*
					 * For backwards compatibility from pre-3.0: add in order item meta prefixed with `_option_`.
					 * While saving, we've migrated these values to order item meta, but people may still be looking
					 * for them in this cart details array, so we need to fill them back in.
					 */
					$order_item_meta = edd_get_order_item_meta( $item->id );
					if ( ! empty( $order_item_meta ) ) {
						foreach ( $order_item_meta as $item_meta_key => $item_meta_value ) {
							if ( '_option_' === substr( $item_meta_key, 0, 8 ) && isset( $item_meta_value[0] ) ) {
								$item_options[ str_replace( '_option_', '', $item_meta_key ) ] = $item_meta_value[0];
							}
						}
					}

					$cart_details = array(
						'name'        => $item->product_name,
						'id'          => $item->product_id,
						'item_number' => array(
							'id'       => $item->product_id,
							'quantity' => $item->quantity,
							'options'  => $item_options,
						),
						'item_price'  => $item->amount,
						'quantity'    => $item->quantity,
						'discount'    => $item->discount,
						'subtotal'    => $item->subtotal,
						'tax'         => $item->tax,
						'fees'        => $item_fees,
						'price'       => $item->amount,
					);

					do_action( 'edd_complete_download_purchase', $item->product_id, $order_id, $download_type, $cart_details, $item->cart_index );
				}
			}
		}

		// Clear the total earnings cache
		delete_transient( 'edd_earnings_total' );
		delete_transient( 'edd_earnings_total_without_tax' );

		// Clear the This Month earnings (this_monththis_month is NOT a typo)
		delete_transient( md5( 'edd_earnings_this_monththis_month' ) );
		delete_transient( md5( 'edd_earnings_todaytoday' ) );
	}

	edd_increase_total_earnings( $amount );

	// Check for discount codes and increment their use counts
	$discounts = $order->get_discounts();
	foreach ( $discounts as $adjustment ) {
		/** @var EDD\Orders\Order_Adjustment $adjustment */

		edd_increase_discount_usage( $adjustment->description );
	}

	// Ensure this action only runs once ever
	if ( empty( $completed_date ) ) {
		$date = EDD()->utils->date()->format( 'mysql' );

		$date_refundable = edd_get_refund_date( $date );
		$date_refundable = false === $date_refundable
			? ''
			: $date_refundable;

		// Save the completed date
		edd_update_order( $order_id, array(
			'date_completed'  => $date,
			'date_refundable' => $date_refundable,
		) );

		// Required for backwards compatibility.
		$payment  = edd_get_payment( $order_id );
		$customer = edd_get_customer( $customer_id );

		/**
		 * Runs **when** a purchase is marked as "complete".
		 *
		 * @since 2.8 Added EDD_Payment and EDD_Customer object to action.
		 *
		 * @param int          $order_id Payment ID.
		 * @param EDD_Payment  $payment    EDD_Payment object containing all payment data.
		 * @param EDD_Customer $customer   EDD_Customer object containing all customer data.
		 */
		do_action( 'edd_complete_purchase', $order_id, $payment, $customer );

		// If cron doesn't work on a site, allow the filter to use __return_false and run the events immediately.
		$use_cron = apply_filters( 'edd_use_after_payment_actions', true, $order_id );
		if ( false === $use_cron ) {

			if ( has_action( 'edd_after_payment_actions' ) ) {
				/**
				 * Runs **after** a purchase is marked as "complete".
				 *
				 * @see edd_process_after_payment_actions()
				 *
				 * @since 2.8 - Added EDD_Payment and EDD_Customer object to action.
				 *
				 * @param int          $order_id Payment ID.
				 * @param EDD_Payment  $payment    EDD_Payment object containing all payment data.
				 * @param EDD_Customer $customer   EDD_Customer object containing all customer data.
				 */
				do_action( 'edd_after_payment_actions', $order_id, $payment, $customer );
			}

			/**
			 * Runs **after** a purchase is marked as "complete".
			 *
			 * @since 3.2.0
			 *
			 * @param int          $order->id The order ID.
			 * @param EDD_Order    $order     The EDD_Order object containing all order data.
			 * @param EDD_Customer $customer  The EDD_Customer object containing all customer data.
			 */
			do_action( 'edd_after_order_actions', $order->id, $order, $customer );

			// Update the order with the date the actions were run in UTC.
			edd_update_order( $order->id, array( 'date_actions_run' => current_time( 'mysql' ) ) );
		}
	}

	// Empty the shopping cart.
	edd_empty_cart();
}
add_action( 'edd_update_payment_status', 'edd_complete_purchase', 100, 3 );

/**
 * Updates week-old+ 'pending' orders to 'abandoned'
 *
 *  This function is only intended to be used by WordPress cron.
 *
 * @since 1.6
 * @return void
*/
function edd_mark_abandoned_orders() {

	// Bail if not in WordPress cron
	if ( ! edd_doing_cron() ) {
		return;
	}

	$args = array(
		'status' => 'pending',
		'number' => 9999999,
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

	if (
		// Logged in, but wrong user ID
		( is_user_logged_in() && $payment->user_id != get_current_user_id() )

		// ...OR...
		||

		// Logged out, but payment is for a user
		( ! is_user_logged_in() && ! empty( $payment->user_id ) )
	) {
		$redirect = get_permalink( edd_get_option( 'purchase_history_page' ) );
		edd_set_error( 'edd-payment-recovery-user-mismatch', __( 'Error resuming payment.', 'easy-digital-downloads' ) );
		edd_redirect( $redirect );
	}

	$payment->add_note( __( 'Payment recovery triggered URL', 'easy-digital-downloads' ) );

	// Empty out the cart.
	EDD()->cart->empty_cart();

	// Recover any downloads.
	foreach ( $payment->cart_details as $download ) {
		edd_add_to_cart( $download['id'], $download['item_number']['options'] );

		// Recover any item specific fees.
		if ( ! empty( $download['fees'] ) ) {
			foreach ( $download['fees'] as $key => $fee ) {
				$fee['id'] = ! empty( $fee['id'] ) ? $fee['id'] : $key;
				EDD()->fees->add_fee( $fee );
			}
		}
	}

	// Recover any global fees.
	foreach ( $payment->fees as $key => $fee ) {
		$fee['id'] = ! empty( $fee['id'] ) ? $fee['id'] : $key;
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

	$redirect_args = array( 'payment-mode' => urlencode( $payment->gateway ) );
	$redirect      = add_query_arg( $redirect_args, edd_get_checkout_uri() );
	edd_redirect( $redirect );
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
			edd_redirect( get_permalink( edd_get_option( 'purchase_page' ) ) );
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
		$payment        = new EDD_Payment( $resuming_payment );
		$requires_login = edd_no_guest_checkout();
		if ( ( $requires_login && ! is_user_logged_in() ) && ( $payment->user_id > 0 && ( ! is_user_logged_in() ) ) ) {
			?>
			<div class="edd-alert edd-alert-info">
				<p><?php _e( 'To complete this payment, please login to your account.', 'easy-digital-downloads' ); ?></p>
				<p>
					<a href="<?php echo esc_url( edd_get_lostpassword_url() ); ?>" title="<?php esc_attr_e( 'Lost Password', 'easy-digital-downloads' ); ?>">
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
		$payment    = new EDD_Payment( $resuming_payment );
		$same_user  = ! empty( $payment->user_id ) && ( is_user_logged_in() && $payment->user_id == get_current_user_id() );
		$same_email = strtolower( $payment->email ) === strtolower( $post_data['edd_email'] );

		if ( ( is_user_logged_in() && ! $same_user ) || ( ! is_user_logged_in() && (int) $payment->user_id > 0 && ! $same_email ) ) {
			edd_set_error( 'recovery_requires_login', __( 'To complete this payment, please login to your account.', 'easy-digital-downloads' ) );
		}
	}
}
add_action( 'edd_checkout_error_checks', 'edd_recovery_verify_logged_in', 10, 2 );
