<?php
/**
 * User Functions
 *
 * Functions related to users / customers
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.8.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get Users Purchases
 *
 * Retrieves a list of all purchases by a specific user.
 *
 * @since  1.0
 *
 * @param int    $user User ID or email address
 * @param int    $number Number of purchases to retrieve
 * @param bool   $pagination
 * @param string $status
 *
 * @return bool|object List of all user purchases
 */
function edd_get_users_purchases( $user = 0, $number = 20, $pagination = false, $status = 'complete' ) {
	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	$status = $status === 'complete' ? 'publish' : $status;

	if ( $pagination ) {
		if ( get_query_var( 'paged' ) )
			$paged = get_query_var('paged');
		else if ( get_query_var( 'page' ) )
			$paged = get_query_var( 'page' );
		else
			$paged = 1;
	}

	$args = apply_filters( 'edd_get_users_purchases_args', array(
		'user'   => $user,
		'number' => $number,
		'status' => $status
	) );

	if ( $pagination )
		$args['page'] = $paged;
	else
		$args['nopaging'] = true;

	$purchases = edd_get_payments( $args );

	// No purchases
	if ( ! $purchases )
		return false;

	return $purchases;
}

/**
 * Has User Purchased
 *
 * Checks to see if a user has purchased a download.
 *
 * @access      public
 * @since       1.0
 * @param       int $user_id - the ID of the user to check
 * @param       array $downloads - Array of IDs to check if purchased. If an int is passed, it will be converted to an array
 * @param       int $variable_price_id - the variable price ID to check for
 * @return      boolean - true if has purchased, false otherwise
 */
function edd_has_user_purchased( $user_id, $downloads, $variable_price_id = null ) {

	$users_purchases = edd_get_users_purchases( $user_id );

	$return = false;

	if ( ! is_array( $downloads ) ) {
		$downloads = array( $downloads );
	}

	if ( $users_purchases ) {
		foreach ( $users_purchases as $purchase ) {

			$purchased_files = edd_get_payment_meta_downloads( $purchase->ID );

			if ( is_array( $purchased_files ) ) {
				foreach ( $purchased_files as $download ) {
					if ( in_array( $download['id'], $downloads ) ) {
						$variable_prices = edd_has_variable_prices( $download['id'] );
						if ( $variable_prices && ! is_null( $variable_price_id ) && $variable_price_id !== false ) {
							if ( isset( $download['options']['price_id'] ) && $variable_price_id == $download['options']['price_id'] ) {
								return true;
							} else {
								$return = false;
							}
						} else {
							$return = true;
						}
					}
				}
			}
		}
	}

	return $return;
}

/**
 * Has Purchases
 *
 * Checks to see if a user has purchased at least one item.
 *
 * @access      public
 * @since       1.0
 * @param       $user_id int - the ID of the user to check
 * @return      bool - true if has purchased, false other wise.
 */
function edd_has_purchases( $user_id = null ) {
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	if ( edd_get_users_purchases( $user_id, 1 ) ) {
		return true; // User has at least one purchase
	}
	return false; // User has never purchased anything
}


/**
 * Get Purchase Status for User
 *
 * Retrieves the purchase count and the total amount spent for a specific user
 *
 * @access      public
 * @since       1.6
 * @param       $user int|string - the ID or email of the customer to retrieve stats for
 * @param       $mode string - "test" or "live"
 * @return      array
 */
function edd_get_purchase_stats_by_user( $user = '' ) {

	global $wpdb;

	$stats = array(
		'purchases'   => 0,
		'total_spent' => 0
	);

	if( is_email( $user ) )
		$field = 'email';
	elseif( is_numeric( $user ) )
		$field = 'id';
	else
		return $stats;

	$stats = wp_cache_get( $user, 'customers' );

	if( false == $stats ) {

		$query = "SELECT {$wpdb->prefix}meta_1.meta_value AS payment_total
			FROM {$wpdb->prefix}postmeta {$wpdb->prefix}meta_1
			LEFT JOIN {$wpdb->prefix}postmeta {$wpdb->prefix}meta_2
				ON {$wpdb->prefix}meta_1.post_id = {$wpdb->prefix}meta_2.post_id
				AND {$wpdb->prefix}meta_1.meta_key = '_edd_payment_total'
			INNER JOIN {$wpdb->prefix}posts
				ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}meta_2.post_id
				AND {$wpdb->prefix}posts.post_status = 'publish'
			WHERE {$wpdb->prefix}meta_2.meta_key = '_edd_payment_user_{$field}'
			AND {$wpdb->prefix}meta_2.meta_value = '%s'";

		$purchases = $wpdb->get_col( $wpdb->prepare( $query, $user ) );

		$purchases = array_filter( $purchases );

		if( $purchases ) {
			$stats['purchases']   = count( $purchases );
			$stats['total_spent'] = round( array_sum( $purchases ), 2 );
		}

		wp_cache_set( $user, $stats, 'customers' );

	}

	return (array) apply_filters( 'edd_purchase_stats_by_user', $stats, $user );
}


/**
 * Count number of purchases of a customer
 *
 * Returns total number of purchases a customer has made
 *
 * @access      public
 * @since       1.3
 * @param       $user mixed - ID or email
 * @return      int - the total number of purchases
 */
function edd_count_purchases_of_customer( $user = null ) {
	if ( empty( $user ) )
		$user = get_current_user_id();

	$stats = edd_get_purchase_stats_by_user( $user );

	return isset( $stats['purchases'] ) ? $stats['purchases'] : 0;
}

/**
 * Calculates the total amount spent by a user
 *
 * @access      public
 * @since       1.3
 * @param       $user mixed - ID or email
 * @return      float - the total amount the user has spent
 */
function edd_purchase_total_of_user( $user = null ) {

	$stats = edd_get_purchase_stats_by_user( $user );

	return $stats['total_spent'];
}

/**
 * Counts the total number of files a customer has downloaded
 *
 * @access      public
 * @since       1.3
 * @param       $user mixed - ID or email
 * @return      int - The total number of files the user has downloaded
 */
function edd_count_file_downloads_of_user( $user ) {
	global $edd_logs;

	if ( is_email( $user ) ) {
		$meta_query = array(
			array(
				'key'     => '_edd_log_user_info',
				'value'   => $user,
				'compare' => 'LIKE'
			)
		);
	} else {
		$meta_query = array(
			array(
				'key'     => '_edd_log_user_id',
				'value'   => $user
			)
		);
	}

	return $edd_logs->get_log_count( null, 'file_download', $meta_query );
}

/**
 * Validate a potential username
 *
 * @access      public
 * @since       1.3.4
 * @param       string $username string - the username to validate
 * @return      bool
 */
function edd_validate_username( $username ) {
	$sanitized = sanitize_user( $username, false );
	$valid = ( $sanitized == $username );
	return (bool) apply_filters( 'edd_validate_username', $valid, $username );
}


/**
 * Looks up purchases by email that match the registering user
 *
 * This is for users that purchased as a guest and then came
 * back and created an account.
 *
 * @access      public
 * @since       1.6
 * @param       $user_id INT - the new user's ID
 * @return      void
 */
function edd_add_past_purchases_to_new_user( $user_id ) {

	$email    = get_the_author_meta( 'user_email', $user_id );

	$payments = edd_get_payments( array( 's' => $email ) );
	
	if( $payments ) {
		foreach( $payments as $payment ) {
			if( intval( edd_get_payment_user_id( $payment->ID ) ) > 0 )
				continue; // This payment already associated with an account

			$meta                    = edd_get_payment_meta( $payment->ID );
			$meta['user_info']       = maybe_unserialize( $meta['user_info'] );
			$meta['user_info']['id'] = $user_id;
			$meta['user_info']       = serialize( $meta['user_info'] );

			// Store the updated user ID in the payment meta
			update_post_meta( $payment->ID, '_edd_payment_meta', $meta );
			update_post_meta( $payment->ID, '_edd_payment_user_id', $user_id );
		}
	}

}
add_action( 'user_register', 'edd_add_past_purchases_to_new_user' );


/**
 * Counts the total number of customers.
 *
 * @access 		public
 * @since 		1.7
 * @global object $wpdb Used to query the database using the WordPress
 *   Database API
 * @return 		int - The total number of customers.
 */
function edd_count_total_customers() {
	global $wpdb;

	$count = wp_cache_get( 'customer_count', 'customers' );

	if( false == $count ) {
	
		$count = $wpdb->get_col( "SELECT COUNT(DISTINCT meta_value) FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_user_email'" );

		$count = $count[0];

		wp_cache_set( 'customer_count', $count, 'customers', 3600 );

	}

	return $count;
}


/**
 * Returns the saved address for a customer
 *
 * @access 		public
 * @since 		1.8
 * @return 		array - The customer's address, if any
 */
function edd_get_customer_address( $user_id = 0 ) {
	if( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$address = get_user_meta( $user_id, '_edd_user_address', true );

	if( ! isset( $address['line1'] ) )
		$address['line1'] = '';

	if( ! isset( $address['line2'] ) )
		$address['line2'] = '';

	if( ! isset( $address['city'] ) )
		$address['city'] = '';

	if( ! isset( $address['zip'] ) )
		$address['zip'] = '';

	if( ! isset( $address['country'] ) )
		$address['country'] = '';

	if( ! isset( $address['state'] ) )
		$address['state'] = '';

	return $address;
}

/**
 * Sends the new user notification email when a user registers during checkout
 *
 * @access 		public
 * @since 		1.8.8
 * @return 		void
 */
function edd_new_user_notification( $user_id = 0, $user_data = array() ) {
	wp_new_user_notification( $user_id, __( '[Password entered at checkout]', 'edd' ) );
}
add_action( 'edd_insert_user', 'edd_new_user_notification', 10, 2 );
