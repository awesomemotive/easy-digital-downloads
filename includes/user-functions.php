<?php
/**
 * User Functions
 *
 * Functions related to users / customers
 *
 * @package     Easy Digital Downloads
 * @subpackage  AJAX
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.8.6
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

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
function edd_get_users_purchases( $user = 0, $number = 20, $pagination = false ) {

	if ( empty( $user ) ) {
		global $user_ID;

		$user = $user_ID;
	}

	$mode = edd_is_test_mode() ? 'test' : 'live';

	if( $pagination ) {
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
		'status' => 'publish'
	) );

	if( $pagination )
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

	if( ! is_user_logged_in() )
		return false; // At some point this should support email checking

	$users_purchases = edd_get_users_purchases( $user_id );

	$return = false;

	if( ! is_array( $downloads ) ) {
		$downloads = array( $downloads );
	}

	if( $users_purchases ) {
		foreach( $users_purchases as $purchase ) {

			$purchase_meta = edd_get_payment_meta( $purchase->ID );
			$purchased_files = maybe_unserialize( $purchase_meta['downloads'] );

			if( is_array( $purchased_files ) ) {

				foreach( $purchased_files as $download ) {

					if( in_array( $download['id'], $downloads ) ) {

						if( !is_null( $variable_price_id ) && $variable_price_id !== false ) {

							if( $variable_price_id == $download['options']['price_id'] ) {

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
	if( is_null( $user_id ) ) {
		global $user_ID;
		$user_id = $user_ID;
	}

	if( edd_get_users_purchases( $user_id, 1 ) ) {
		return true; // User has at least one purchase
	}
	return false; // User has never purchased anything
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

	if( empty( $user ) )
		$user = get_current_user_id();

	$args = array(
		'number'   => -1,
		'mode'     => 'live',
		'user'     => $user,
		'status'   => 'publish'
	);

	$customer_purchases = edd_get_payments( $args );
	if( $customer_purchases )
		return count( $customer_purchases );
	return 0;
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
	$args = array(
		'number'   => -1,
		'mode'     => 'live',
		'user'     => $user,
		'status'   => 'publish'
	);

	$customer_purchases = edd_get_payments( $args );

	$amount = get_transient( md5( 'edd_customer_total_' . $user ) );
	if( false === $amount ) {

		$amount = 0;

		if( $customer_purchases ) :
			foreach( $customer_purchases as $purchase ) :

				$amount += edd_get_payment_amount( $purchase->ID );

			endforeach;
		endif;
		set_transient( md5( 'edd_customer_total_' . $user ), $amount );
	}

	return $amount;
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

	if( is_email( $user ) ) {

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