<?php
/**
 * User Functions
 *
 * Functions related to users / customers
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2013, Pippin Williamson
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
 * @access public
 * @since  1.0
 * @param  int|string $user   User ID or email address
 * @param  int $number        Number of purchases to retrieve
 *
 * @return array List of all user purchases
 */
function edd_get_users_purchases( $user = 0, $number = 20, $pagination = false, $status = 'complete' ) {
	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	$status = $status === 'complete' ? 'publish' : $status;

	$mode = edd_is_test_mode() ? 'test' : 'live';

	if ( $pagination ) {
		if ( get_query_var( 'paged' ) )
			$paged = get_query_var('paged');
		else if ( get_query_var( 'page' ) )
			$paged = get_query_var( 'page' );
		else
			$paged = 1;
	}

	$args = apply_filters( 'edd_get_users_purchases_args', array(
		'mode'   => $mode,
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
	if ( ! is_user_logged_in() )
		return false; // At some point this should support email checking

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
 * CRetrieves the purchase count and the total amount spent for a specific user
 *
 * @access      public
 * @since       1.6
 * @param       $user int|string - the ID or email of the customer to retrieve stats for
 * @param       $mode string - "test" or "live"
 * @return      array
 */
function edd_get_purchase_stats_by_user( $user = '', $mode = 'live' ) {

	global $wpdb;

	if( is_email( $user ) )
		$field = 'email';
	elseif( is_numeric( $user ) )
		$field = 'id';
	else
		return false;

	$stats = array(
		'purchases'   => 0,
		'total_spent' => 0
	);

	$query = "SELECT {$wpdb->prefix}mb.meta_value AS payment_total
		FROM {$wpdb->prefix}postmeta {$wpdb->prefix}m
		LEFT JOIN {$wpdb->prefix}postmeta {$wpdb->prefix}ma
			ON {$wpdb->prefix}ma.post_id = {$wpdb->prefix}m.post_id
			AND {$wpdb->prefix}ma.meta_key = '_edd_payment_user_{$field}'
			AND {$wpdb->prefix}ma.meta_value = '%s'
		LEFT JOIN {$wpdb->prefix}postmeta {$wpdb->prefix}mb
			ON {$wpdb->prefix}mb.post_id = {$wpdb->prefix}ma.post_id
			AND {$wpdb->prefix}mb.meta_key = '_edd_payment_total'
		INNER JOIN {$wpdb->prefix}posts {$wpdb->prefix}
			ON {$wpdb->prefix}.id = {$wpdb->prefix}m.post_id
			AND {$wpdb->prefix}.post_status = 'publish'
		WHERE {$wpdb->prefix}m.meta_key = '_edd_payment_mode'
		AND {$wpdb->prefix}m.meta_value = '%s'";

	$purchases = $wpdb->get_col( $wpdb->prepare( $query, $user, $mode ) );

	$purchases = array_filter( $purchases );

	if( $purchases ) {
		$stats['purchases']   = count( $purchases );
		$stats['total_spent'] = round( array_sum( $purchases ), 2 );
	}

	return (array) apply_filters( 'edd_purchase_stats_by_user', $stats, $user, $mode );
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

	$mode  = edd_is_test_mode() ? 'test' : 'live';

	$stats = edd_get_purchase_stats_by_user( $user, $mode );

	return $stats['purchases'];
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

	global $wpdb;

	$mode  = edd_is_test_mode() ? 'test' : 'live';

	$stats = edd_get_purchase_stats_by_user( $user, $mode );

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
 * @param       $username string - the username to validate
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

	$email    = get_user_meta( $user_id, 'user_email', true );
	$mode     = edd_is_test_mode() ? 'test' : 'live';
	$payments = edd_get_payments( array( 's' => $email, 'mode' => $mode ) );
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
		}
	}

}
add_action( 'user_register', 'edd_add_past_purchases_to_new_user' );