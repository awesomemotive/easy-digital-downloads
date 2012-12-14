<?php
/**
 * Payment Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Payment Functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */


/**
 * Get Payments
 *
 * Retrieve payments from the database.
 *
 * Since 1.2, this function takes an array of arguments, instead of individual parameters.
 * All of the original paremeters remain, but can be passed in any order via the array.
 *
 * $args['offset'] = 0, $args['number'] = 20, $args['mode'] = 'live', $args['orderby'] = 'ID', $args['order'] = 'DESC',
 * $args['user'] = null, $args['status'] = 'any', $args['meta_key'] = null
 *
 * @param array $args Arguments for the payments to retrieve
 * @access      public
 * @since       1.0
 * @return      object
 */
function edd_get_payments( $args = array() ) {
	$defaults = array(
		'offset'   => 0,
		'number'   => 20,
		'mode'     => 'live',
		'orderby'  => 'ID',
		'order'    => 'DESC',
		'user'     => null,
		'status'   => 'any',
		'meta_key' => null,
		'year'     => null,
		'month'    => null,
		'day'      => null,
	);

	$args = wp_parse_args( $args, $defaults );

	$payment_args = array(
		'post_type'      => 'edd_payment',
		'posts_per_page' => $args['number'],
		'offset'         => $args['offset'],
		'order'          => $args['order'],
		'orderby'        => $args['orderby'],
		'post_status'    => $args['status'],
		'year'           => $args['year'],
		'monthnum'       => $args['month'],
		'day'            => $args['day']
	);

	if ( !is_null( $args['meta_key'] ) )
		$payment_args['meta_key'] = $args['meta_key'];

	if ( !is_null( $args['user'] ) ) {
		if ( is_numeric( $args['user'] ) ) {
			$user_key = '_edd_payment_user_id';
		} else {
			$user_key = '_edd_payment_user_email';
		}
		$payment_args['meta_query'] = array(
			array(
				'key'   => $user_key,
				'value' => $args['user']
			)
		);
	}

	if ( $args['mode'] != 'all' ) {
		if ( isset( $payment_args['meta_query'] ) ) {

			// append to the user meta query
			$payment_args['meta_query'][1] = array(
				'key'   => '_edd_payment_mode',
				'value' => $args['mode']
			);

		} else {

			// create a new meta query
			$payment_args['meta_query'] = array(
				array(
					'key'   => '_edd_payment_mode',
					'value' => $args['mode']
				)
			);

		}
	}
	$payments = get_posts( apply_filters( 'edd_get_payments_args', $payment_args ) );
	if ( $payments ) {
		return $payments;
	}
	return false;
}


/**
 * Insert Payment
 *
 * @param array $payment_data The data to insert
 * @access      public
 * @since       1.0
 * @return      int Payment ID, 0 on false.
 */

function edd_insert_payment( $payment_data = array() ) {

	if ( empty( $payment_data ) )
		return 0;

	// construct the payment title
	if ( isset( $payment_data['user_info']['first_name'] ) || isset( $payment_data['user_info']['last_name'] ) ) {
		$payment_title = $payment_data['user_info']['first_name'] . ' ' . $payment_data['user_info']['last_name'];
	} else {
		$payment_title = $payment_data['user_email'];
	}

	if ( isset( $payment_data['status'] ) ) {
		$status = $payment_data['status'];
	} else {
		$status = 'pending';
	}

	// create a blank payment
	$payment = wp_insert_post( array( 'post_title' => $payment_title, 'post_status' => $status, 'post_type' => 'edd_payment', 'post_date' => $payment_data['date'] ) );

	if ( $payment ) {

		$payment_meta = array(
			'amount'       => $payment_data['price'],
			'date'         => $payment_data['date'],
			'email'        => $payment_data['user_email'],
			'key'          => $payment_data['purchase_key'],
			'currency'     => $payment_data['currency'],
			'downloads'    => serialize( $payment_data['downloads'] ),
			'user_info'    => serialize( $payment_data['user_info'] ),
			'cart_details' => serialize( $payment_data['cart_details'] ),
			'user_id'      => $payment_data['user_info']['id']
		);

		// NOTE: tax info is added to meta in tax-functions.php with edd_record_taxed_amount()

		$mode    = edd_is_test_mode() ? 'test' : 'live';
		$gateway = isset( $_POST['edd-gateway'] ) ? $_POST['edd-gateway'] : '';

		// record the payment details
		update_post_meta( $payment, '_edd_payment_meta', apply_filters( 'edd_payment_meta', $payment_meta, $payment_data ) );
		update_post_meta( $payment, '_edd_payment_user_id', $payment_data['user_info']['id'] );
		update_post_meta( $payment, '_edd_payment_user_email', $payment_data['user_email'] );
		update_post_meta( $payment, '_edd_payment_user_ip', edd_get_ip() );
		update_post_meta( $payment, '_edd_payment_purchase_key', $payment_data['purchase_key'] );
		update_post_meta( $payment, '_edd_payment_total', $payment_data['price'] );
		update_post_meta( $payment, '_edd_payment_mode', $mode );
		update_post_meta( $payment, '_edd_payment_gateway', $gateway );

		// clear the user's purchased cache
		delete_transient( 'edd_user_' . $payment_data['user_info']['id'] . '_purchases' );

		do_action( 'edd_insert_payment', $payment, $payment_data );

		return $payment; // return the ID
	}
	// return false if no payment was inserted
	return 0;
}


/**
 * Update Payment Status
 *
 * Updates a payment status, and performs all necessary functions to mark it as complete, and to finish a purchase.
 *
 * @param int    $payment_id The payment to update
 * @param string $new_status New status for the payment
 * @access      public
 * @since       1.0
 * @return      void
 */

function edd_update_payment_status( $payment_id, $new_status = 'publish' ) {

	if ( $new_status == 'completed' || $new_status == 'complete' ) {
		$new_status = 'publish';
	}

	$payment = get_post( $payment_id );

	if ( is_wp_error( $payment ) || !is_object( $payment ) )
		return;

	if ( $payment->post_status == 'publish' ) {
		//return;
	}

	$old_status = $payment->post_status;

	do_action( 'edd_before_payment_status_change', $payment_id, $new_status, $old_status );

	$update_fields = array( 'ID' => $payment_id, 'post_status' => $new_status );

	wp_update_post( apply_filters( 'edd_update_payment_status_fields', $update_fields ) );

	do_action( 'edd_update_payment_status', $payment_id, $new_status, $old_status );
}


/**
 * Undos a purchase, including the decrease of sale and earning stats
 *
 * Used for when refunding or deleting a purchase
 *
 * @access  public
 * @since   1.0.8.1
 * @param   int $download_id - the ID number of the download
 * @param   int $payment_id  - the ID number of the purchase
 * @return     void
 */

function edd_undo_purchase( $download_id, $payment_id ) {

	$payment = get_post( $payment_id );
	if ( edd_get_payment_status( $payment ) == 'refunded' )
		return; // payment has already been reversed

	edd_decrease_purchase_count( $download_id );

	$purchase_meta = edd_get_payment_meta( $payment_id );

	$user_purchase_info = maybe_unserialize( $purchase_meta['user_info'] );

	$cart_details = maybe_unserialize( $purchase_meta['cart_details'] );

	$amount = null;
	if ( is_array( $cart_details ) ) {
		$cart_item_id = array_search( $download_id, $cart_details );
		$amount       = isset( $cart_details[$cart_item_id]['price'] ) ? $cart_details[$cart_item_id]['price'] : null;
	}

	$amount = edd_get_download_final_price( $download_id, $user_purchase_info, $amount );

	edd_decrease_earnings( $download_id, $amount );

}


/**
 * Check For Existing (and paid) Payment
 *
 * @param int $payment_id The payment to check for
 * @access      public
 * @since       1.0
 * @return      boolean
 */

function edd_check_for_existing_payment( $payment_id ) {
	$payment = get_post( $payment_id );
	if ( $payment && $payment->post_status == 'publish' ) {
		return true; // payment exists
	}
	return false; // this payment doesn't exist
}


/**
 * Get Payment Status
 *
 * Retrieves the status of a payment.
 *
 * @param string $payment      Payment to get status for
 * @param bool   $return_label Whether to return status label or status
 *
 * @access      public
 * @since       1.0
 * @return bool|mixed
 */
function edd_get_payment_status( $payment = OBJECT, $return_label = false ) {
	if ( !is_object( $payment ) && !isset( $payment->post_status ) )
		return false;

	$statuses = edd_get_payment_statuses();
	if ( !is_array( $statuses ) || empty( $statuses ) )
		return false;

	if ( array_key_exists( $payment->post_status, $statuses ) ) {
		if ( true === $return_label ) {
			return $statuses[$payment->post_status];
		} else {
			return array_search( $payment->post_status, $statuses );
		}
	}

	return false;
}


/**
 * Get Payment Statuses
 *
 * Retrieves all available statuses for payments
 *
 * @access      public
 * @since       1.0.8.1
 * @return      string
 */

function edd_get_payment_statuses() {

	$payment_statuses = array(
		'pending'  => __( 'Pending', 'edd' ),
		'publish'  => __( 'Complete', 'edd' ),
		'refunded' => __( 'Refunded', 'edd' )
	);

	return apply_filters( 'edd_payment_statuses', $payment_statuses );

}

/**
 * Registers custom statuses
 *
 * @access      public
 * @since       1.0.9.1
 * @return      integer
 */

function edd_register_payment_status() {
	register_post_status( 'refunded' );
}

add_action( 'init', 'edd_register_payment_status' );


/**
 * Get Earnings By Date
 *
 * @param null $day
 * @param int  $month_num
 * @param int  $year
 * @access      public
 * @since       1.0
 * @return      int
 */
function edd_get_earnings_by_date( $day = null, $month_num, $year ) {
	$args = array(
		'post_type'      => 'edd_payment',
		'posts_per_page' => -1,
		'year'           => $year,
		'monthnum'       => $month_num,
		'meta_key'       => '_edd_payment_mode',
		'meta_value'     => 'live',
		'post_status'    => 'publish'
	);
	if ( $day )
		$args['day'] = $day;

	$sales = get_posts( $args );
	$total = 0;
	if ( $sales ) {
		foreach ( $sales as $sale ) {
			$sale_meta = edd_get_payment_meta( $sale->ID );
			$amount    = $sale_meta['amount'];
			$total     = $total + $amount;
		}
	}
	return $total;
}

/**
 * Get Sales of By Date
 *
 * @param null $day
 * @param null $month_num
 * @param      $year
 * @access      public
 * @author      Sunny Ratilal
 * @since       1.1.4.0
 * @return      int
 */
function edd_get_sales_by_date( $day = null, $month_num = null, $year ) {
	$args = array(
		'post_type'      => 'edd_payment',
		'posts_per_page' => -1,
		'year'           => $year,
		'meta_key'       => '_edd_payment_mode',
		'meta_value'     => 'live'
	);

	if ( !empty( $month_num ) )
		$args['monthnum'] = $month_num;

	if ( !empty( $day ) )
		$args['day'] = $day;

	$sales = get_posts( $args );
	$total = 0;
	if ( $sales ) {
		$total = count( $sales );
	}
	return $total;
}


/**
 * Is Payment Complete
 *
 * Checks whether a payment has been marked as complete.
 *
 * @access      public
 * @since       1.0.8
 * @param       $payment_id INT the ID number of the payment to check
 * @return      boolean true if complete, false otherwise
 */
function edd_is_payment_complete( $payment_id ) {
	$payment = get_post( $payment_id );
	if ( $payment )
		if ( $payment->post_status == 'publish' )
			return true;
	return false;
}


/**
 * Get Downloads Of Purchase
 *
 * Retrieves an array of all files purchased.
 *
 * @param           $payment_id
 * @param null      $payment_meta
 *
 * @access      public
 * @since       1.0
 * @param       int $payment_id - the ID number of the purchase
 * @return      mixed - array if purchase exists, false otherwise
 */
function edd_get_downloads_of_purchase( $payment_id, $payment_meta = null ) {
	if ( is_null( $payment_meta ) ) {
		$payment_meta = edd_get_payment_meta( $payment_id );
	}
	$downloads = maybe_unserialize( $payment_meta['downloads'] );
	if ( $downloads )
		return $downloads;
	return false;
}


/**
 * Get Total Sales
 *
 * @access      public
 * @author      Sunny Ratilal
 * @since       1.2.2
 * @return      int
 */
function edd_get_total_sales() {
	$sales = get_posts(
		array(
			'post_type'      => 'edd_payment',
			'posts_per_page' => -1,
			'meta_key'       => '_edd_payment_mode',
			'meta_value'     => 'live'
		)
	);
	$total = 0;
	if ( $sales ) {
		$total = count( $sales );
	}
	return $total;
}


/**
 * Get Total Earnings
 *
 * @access      public
 * @since       1.2
 * @return      float
 */
function edd_get_total_earnings() {

	$total = (float) 0;
	//$earnings = get_transient( 'edd_searnings_total' );
	//if( false === $earnings ) {
	$payments = edd_get_payments( array(
		'offset' => 0,
		'number' => -1,
		'mode'   => 'live',
		'status' => 'publish',
	) );
	if ( $payments ) {
		foreach ( $payments as $payment ) {
			$total += edd_get_payment_amount( $payment->ID );
		}
	}
	//set_transient( 'edd_earnings_total', $payments, 1800 );
	//}
	return apply_filters( 'edd_total_earnings', $total );
}

/**
 * Get Payment Meta
 *
 * @param string $payment_id
 * @access      public
 * @since       1.2
 * @return      array
 */
function edd_get_payment_meta( $payment_id ) {
	$meta = get_post_meta( $payment_id, '_edd_payment_meta', true );

	return apply_filters( 'edd_get_payment_meta', $meta );
}

/**
 * Get `user_info` from payment meta
 *
 * @param string $payment_id
 * @access      public
 * @since       1.2
 * @return      array
 */
function edd_get_payment_meta_user_info( $payment_id ) {
	$payment_meta = edd_get_payment_meta( $payment_id );
	$user_info    = maybe_unserialize( $payment_meta['user_info'] );

	return apply_filters( 'edd_payment_meta_user_info', $user_info );
}

/**
 * Get `downloads` from payment meta
 *
 * @param string $payment_id
 * @access      public
 * @since       1.2
 * @return      array
 */
function edd_get_payment_meta_downloads( $payment_id ) {
	$payment_meta = edd_get_payment_meta( $payment_id );
	$downloads    = maybe_unserialize( $payment_meta['downloads'] );

	return apply_filters( 'edd_payment_meta_downloads', $downloads );
}

/**
 * Get `cart_details` from payment meta
 *
 * @param string $payment_id
 * @access      public
 * @since       1.2
 * @return      array
 */
function edd_get_payment_meta_cart_details( $payment_id ) {
	$payment_meta = edd_get_payment_meta( $payment_id );
	$cart_details = maybe_unserialize( $payment_meta['cart_details'] );

	return apply_filters( 'edd_payment_meta_cart_details', $cart_details );
}

/**
 * Get the user email associated with a payment
 *
 * @param string $payment_id
 * @access      public
 * @since       1.2
 * @return      array
 */
function edd_get_payment_user_email( $payment_id ) {
	$email = get_post_meta( $payment_id, '_edd_payment_user_email', true );

	return apply_filters( 'edd_payment_user_email', $email );
}

/**
 * Get the gateway associated with a payment
 *
 * @param string $payment_id
 * @access      public
 * @since       1.2
 * @return      array
 */
function edd_get_payment_gateway( $payment_id ) {
	$gateway = get_post_meta( $payment_id, '_edd_payment_gateway', true );

	return apply_filters( 'edd_payment_gateway', $gateway );
}


/**
 * Get the amount associated with a payment
 *
 * @param string $payment_id
 * @access      public
 * @since       1.2
 * @return      array
 */
function edd_get_payment_amount( $payment_id ) {
	$amount = get_post_meta( $payment_id, '_edd_payment_total', true );

	if ( !$amount ) {
		$payment_meta = edd_get_payment_meta( $payment_id );
		$amount       = $payment_meta['amount'];
	}
	return apply_filters( 'edd_payment_amount', $amount );
}

/**
 * Retrieves subtotal for payment
 *
 * This is the amount before taxes
 *
 * Returns a full formatted amount
 *
 * @param mixed $payment_id
 * @param bool  $payment_meta
 * @access      public
 * @since       1.3.3
 * @return      mixed
 */
function edd_payment_subtotal( $payment_id = 0, $payment_meta = false ) {

	$subtotal = edd_get_payment_subtotal( $payment_id, $payment_meta );

	return edd_currency_filter( edd_format_amount( $subtotal ) );

}


/**
 * Retrieves subtotal of payment
 *
 * This is the amount before taxes. If no subtotal is present, normal amount is returned
 *
 * Returns a non formatted amount
 *
 * @param mixed $payment_id
 * @param bool  $payment_meta
 * @access      public
 * @since       1.3.3
 * @return      float
 */
function edd_get_payment_subtotal( $payment_id = 0, $payment_meta = false ) {
	if ( !$payment_meta )
		$payment_meta = edd_get_payment_meta( $payment_id );

	$subtotal = isset( $payment_meta['subtotal'] ) ? $payment_meta['subtotal'] : $payment_meta['amount'];

	return apply_filters( 'edd_get_payment_subtotal', $subtotal, $payment_id );

}


/**
 * Retrieves taxed amount on payment
 *
 * Returns a full formatted amount
 *
 * @param mixed $payment_id
 * @param bool  $payment_meta
 * @access      public
 * @since       1.3.3
 * @return      string
 */
function edd_payment_tax( $payment_id = 0, $payment_meta = false ) {

	$tax = edd_get_payment_tax( $payment_id, $payment_meta );

	return edd_currency_filter( edd_format_amount( $tax ) );

}


/**
 * Retrieves taxed amount on payment
 *
 * Returns a non formatted amount
 *
 * @param mixed $payment_id
 * @param bool  $payment_meta
 * @access      public
 * @since       1.3.3
 * @return      float
 */
function edd_get_payment_tax( $payment_id = 0, $payment_meta = false ) {
	if ( !$payment_meta )
		$payment_meta = edd_get_payment_meta( $payment_id );

	$tax = isset( $payment_meta['tax'] ) ? $payment_meta['tax'] : 0;

	return apply_filters( 'edd_get_payment_tax', $tax, $payment_id );

}


/**
 * Retrieve the purchase ID based on the purchase key
 *
 * @access public
 * @since  1.3.2
 *
 * @param  string $key the purchase key to search for
 * @return int $order_id
 */
function edd_get_purchase_id_by_key( $key ) {
	global $wpdb;

	$payment = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_purchase_key' AND meta_value = %s LIMIT 1", $key ) );

	if ( $payment != null )
		return $payment;

	return 0;
}