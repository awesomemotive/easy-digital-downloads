<?php
/**
 * User Functions
 *
 * Functions related to users / customers
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2015, Pippin Williamson
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

	if ( 0 === $user ) {
		return false;
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

	$args = array(
		'user'    => $user,
		'number'  => $number,
		'status'  => $status,
		'orderby' => 'date'
	);

	if ( $pagination ) {

		$args['page'] = $paged;

	} else {

		$args['nopaging'] = true;

	}

	$by_user_id = is_numeric( $user ) ? true : false;
	$customer   = new EDD_Customer( $user, $by_user_id );

	if( ! empty( $customer->payment_ids ) ) {

		unset( $args['user'] );
		$args['post__in'] = array_map( 'absint', explode( ',', $customer->payment_ids ) );

	}

	$purchases = edd_get_payments( apply_filters( 'edd_get_users_purchases_args', $args ) );

	// No purchases
	if ( ! $purchases )
		return false;

	return $purchases;
}

/**
 * Get Users Purchased Products
 *
 * Returns a list of unique products purchased by a specific user
 *
 * @since  2.0
 *
 * @param int    $user User ID or email address
 * @param string $status
 *
 * @return bool|object List of unique products purchased by user
 */
function edd_get_users_purchased_products( $user = 0, $status = 'complete' ) {
	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	if ( empty( $user ) ) {
		return false;
	}

	$by_user_id = is_numeric( $user ) ? true : false;

	$customer = new EDD_Customer( $user, $by_user_id );

	if ( empty( $customer->payment_ids ) ) {
		return false;
	}

	// Get all the items purchased
	$payment_ids    = array_reverse( explode( ',', $customer->payment_ids ) );
	$limit_payments = apply_filters( 'edd_users_purchased_products_payments', 50 );
	if ( ! empty( $limit_payments ) ) {
		$payment_ids = array_slice( $payment_ids, 0, $limit_payments );
	}
	$purchase_data  = array();

	foreach ( $payment_ids as $payment_id ) {
		$purchase_data[] = edd_get_payment_meta_downloads( $payment_id );
	}

	if ( empty( $purchase_data ) ) {
		return false;
	}

	// Grab only the post ids of the products purchased on this order
	$purchase_product_ids = array();
	foreach ( $purchase_data as $purchase_meta ) {
		$purchase_product_ids[] = @wp_list_pluck( $purchase_meta, 'id' );
	}

	// Ensure that grabbed products actually HAVE downloads
	$purchase_product_ids = array_filter( $purchase_product_ids );

	if ( empty( $purchase_product_ids ) ) {
		return false;
	}

	// Merge all orders into a single array of all items purchased
	$purchased_products = array();
	foreach ( $purchase_product_ids as $product ) {
		$purchased_products = array_merge( $product, $purchased_products );
	}

	// Only include each product purchased once
	$product_ids = array_unique( $purchased_products );

	// Make sure we still have some products and a first item
	if ( empty ( $product_ids ) || ! isset( $product_ids[0] ) )
		return false;

	$post_type 	 = get_post_type( $product_ids[0] );

	$args = apply_filters( 'edd_get_users_purchased_products_args', array(
		'include'			=> $product_ids,
		'post_type' 		=> $post_type,
		'posts_per_page'  	=> -1
	) );

	return apply_filters( 'edd_users_purchased_products_list', get_posts( $args ) );
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

	if( empty( $user_id ) ) {
		return false;
	}

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

	if ( is_email( $user ) ) {

		$field = 'email';

	} elseif ( is_numeric( $user ) ) {

		$field = 'user_id';

	}

	$stats    = array();
	$customer = EDD()->customers->get_customer_by( $field, $user );

	if( $customer ) {

		$customer = new EDD_Customer( $customer->id );

		$stats['purchases']   = absint( $customer->purchase_count );
		$stats['total_spent'] = edd_sanitize_amount( $customer->purchase_value );

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
	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	$stats = ! empty( $user ) ? edd_get_purchase_stats_by_user( $user ) : false;

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
 * @param       string $username The username to validate
 * @return      bool
 */
function edd_validate_username( $username ) {
	$sanitized = sanitize_user( $username, false );
	$valid = ( $sanitized == $username );
	return (bool) apply_filters( 'edd_validate_username', $valid, $username );
}

/**
 * Attach the newly created user_id to a customer, if one exists
 *
 * @since  2.4.6
 * @param  int $user_id The User ID that was created
 * @return void
 */
function edd_connect_existing_customer_to_new_user( $user_id ) {
	$email = get_the_author_meta( 'user_email', $user_id );

	// Update the user ID on the customer
	$customer = new EDD_Customer( $email );

	if( $customer->id > 0 ) {
		$customer->update( array( 'user_id' => $user_id ) );
	}
}
add_action( 'user_register', 'edd_connect_existing_customer_to_new_user', 10, 1 );

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

		// Set a flag to force the account to be verified before purchase history can be accessed
		edd_set_user_to_pending( $user_id );

		edd_send_user_verification_email( $user_id );

		foreach( $payments as $payment ) {
			if( intval( edd_get_payment_user_id( $payment->ID ) ) > 0 ) {
				continue; // This payment already associated with an account
			}

			$meta                    = edd_get_payment_meta( $payment->ID );
			$meta['user_info']       = maybe_unserialize( $meta['user_info'] );
			$meta['user_info']['id'] = $user_id;
			$meta['user_info']       = $meta['user_info'];

			// Store the updated user ID in the payment meta
			edd_update_payment_meta( $payment->ID, '_edd_payment_meta', $meta );
			edd_update_payment_meta( $payment->ID, '_edd_payment_user_id', $user_id );
		}
	}

}
add_action( 'user_register', 'edd_add_past_purchases_to_new_user', 10, 1 );


/**
 * Counts the total number of customers.
 *
 * @access 		public
 * @since 		1.7
 * @return 		int - The total number of customers.
 */
function edd_count_total_customers() {
	return EDD()->customers->count();
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

	if( empty( $user_id ) || empty( $user_data ) ) {
		return;
	}

	$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	$message  = sprintf( __( 'New user registration on your site %s:' ), $blogname ) . "\r\n\r\n";
	$message .= sprintf( __( 'Username: %s'), $user_data['user_login'] ) . "\r\n\r\n";
	$message .= sprintf( __( 'E-mail: %s'), $user_data['user_email'] ) . "\r\n";

	@wp_mail( get_option( 'admin_email' ), sprintf( __('[%s] New User Registration' ), $blogname ), $message );

	$message  = sprintf( __( 'Username: %s' ), $user_data['user_login'] ) . "\r\n";
	$message .= sprintf( __( 'Password: %s' ), __( '[Password entered at checkout]', 'easy-digital-downloads' ) ) . "\r\n";
	$message .= wp_login_url() . "\r\n";

	wp_mail( $user_data['user_email'], sprintf( __( '[%s] Your username and password' ), $blogname ), $message );

}
add_action( 'edd_insert_user', 'edd_new_user_notification', 10, 2 );

/**
 * Set a user's status to pending
 *
 * @since  2.4.4
 * @param  integer $user_id The User ID to set to pending
 * @return bool             If the update was successful
 */
function edd_set_user_to_pending( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		return false;
	}

	do_action( 'edd_pre_set_user_to_pending', $user_id );

	$update_successful = (bool) update_user_meta( $user_id, '_edd_pending_verification', '1' );

	do_action( 'edd_post_set_user_to_pending', $user_id, $update_successful );

	return $update_successful;
}

/**
 * Set the user from pending to active
 *
 * @since  2.4.4
 * @param  integer $user_id The User ID to activate
 * @return bool             If the user was marked as active or not
 */
function edd_set_user_to_verified( $user_id = 0 ) {

	if ( empty( $user_id ) ) {
		return false;
	}

	if ( ! edd_user_pending_verification( $user_id ) ) {
		return false;
	}

	do_action( 'edd_pre_set_user_to_active', $user_id );

	$update_successful = delete_user_meta( $user_id, '_edd_pending_verification', '1' );

	do_action( 'edd_post_set_user_to_active', $user_id, $update_successful );

	return $update_successful;
}

/**
 * Determines if the user account is pending verification. Pending accounts cannot view purchase history
 *
 * @access  public
 * @since   2.4.4
 * @return  bool
 */
function edd_user_pending_verification( $user_id = 0 ) {

	if( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	// No need to run a DB lookup on an empty user id
	if ( empty( $user_id ) ) {
		return false;
	}

	$pending = get_user_meta( $user_id, '_edd_pending_verification', true );

	return (bool) apply_filters( 'edd_user_pending_verification', ! empty( $pending ), $user_id );

}

/**
 * Gets the activation URL for the specified user
 *
 * @access  public
 * @since   2.4.4
 * @return  string
 */
function edd_get_user_verification_url( $user_id = 0 ) {

	if( empty( $user_id ) ) {
		return false;
	}

	$base_url = add_query_arg( array(
		'edd_action' => 'verify_user',
		'user_id'    => $user_id,
		'ttl'        => strtotime( '+24 hours' )
	), untrailingslashit( edd_get_user_verification_page() ) );

	$token = edd_get_user_verification_token( $base_url );
	$url   = add_query_arg( 'token', $token, $base_url );

	return apply_filters( 'edd_get_user_verification_url', $url, $user_id );

}

/**
 * Gets the URL that triggers a new verification email to be sent
 *
 * @access  public
 * @since   2.4.4
 * @return  string
 */
function edd_get_user_verification_request_url( $user_id = 0 ) {

	if( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$url = wp_nonce_url( add_query_arg( array(
		'edd_action' => 'send_verification_email'
	) ), 'edd-request-verification' );

	return apply_filters( 'edd_get_user_verification_request_url', $url, $user_id );

}

/**
 * Sends an email to the specified user with a URL to verify their account
 *
 * @access  public
 * @since   2.4.4
 * @return  void
 */
function edd_send_user_verification_email( $user_id = 0 ) {

	if( empty( $user_id ) ) {
		return;
	}

	if( ! edd_user_pending_verification( $user_id ) ) {
		return;
	}

	$user_data  = get_userdata( $user_id );

	if( ! $user_data ) {
		return;
	}

	$verify_url = edd_get_user_verification_url( $user_id );
	$name       = $user_data->display_name;
	$url        = edd_get_user_verification_url( $user_id );
	$from_name  = edd_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_email = edd_get_option( 'from_email', get_bloginfo( 'admin_email' ) );
	$subject    = apply_filters( 'edd_user_verification_email_subject', __( 'Verify your account', 'easy-digital-downloads' ), $user_id );
	$heading    = apply_filters( 'edd_user_verification_email_heading', __( 'Verify your account', 'easy-digital-downloads' ), $user_id );
	$message    = sprintf( __( "Hello %s,\n\nYour account with %s needs to be verified before you can access your purchase history. <a href='%s'>Click here</a> to verify your account.", 'easy-digital-downloads' ), $name, $from_name, $url );
	$message    = apply_filters( 'edd_user_verification_email_message', $message, $user_id );

	$emails     = new EDD_Emails;

	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'heading', $heading );

	$emails->send( $user_data->user_email, $subject, $message );

}

/**
 * Generates a token for a user verification URL.
 *
 * An 'o' query parameter on a URL can include optional variables to test
 * against when verifying a token without passing those variables around in
 * the URL. For example, downloads can be limited to the IP that the URL was
 * generated for by adding 'o=ip' to the query string.
 *
 * Or suppose when WordPress requested a URL for automatic updates, the user
 * agent could be tested to ensure the URL is only valid for requests from
 * that user agent.
 *
 * @since  2.4.4
 *
 * @param  string $url The URL to generate a token for.
 * @return string The token for the URL.
 */
function edd_get_user_verification_token( $url = '' ) {

	$args    = array();
	$hash    = apply_filters( 'edd_get_user_verification_token_algorithm', 'sha256' );
	$secret  = apply_filters( 'edd_get_user_verification_token_secret', hash( $hash, wp_salt() ) );

	/*
	 * Add additional args to the URL for generating the token.
	 * Allows for restricting access to IP and/or user agent.
	 */
	$parts   = parse_url( $url );
	$options = array();

	if ( isset( $parts['query'] ) ) {

		wp_parse_str( $parts['query'], $query_args );

		// o = option checks (ip, user agent).
		if ( ! empty( $query_args['o'] ) ) {

			// Multiple options can be checked by separating them with a colon in the query parameter.
			$options = explode( ':', rawurldecode( $query_args['o'] ) );

			if ( in_array( 'ip', $options ) ) {

				$args['ip'] = edd_get_ip();

			}

			if ( in_array( 'ua', $options ) ) {

				$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
				$args['user_agent'] = rawurlencode( $ua );

			}

		}

	}

	/*
	 * Filter to modify arguments and allow custom options to be tested.
	 * Be sure to rawurlencode any custom options for consistent results.
	 */
	$args = apply_filters( 'edd_get_user_verification_token_args', $args, $url, $options );

	$args['secret'] = $secret;
	$args['token']  = false; // Removes a token if present.

	$url   = add_query_arg( $args, $url );
	$parts = parse_url( $url );

	// In the event there isn't a path, set an empty one so we can MD5 the token
	if ( ! isset( $parts['path'] ) ) {

		$parts['path'] = '';

	}

	$token = md5( $parts['path'] . '?' . $parts['query'] );

	return $token;

}

/**
 * Generate a token for a URL and match it against the existing token to make
 * sure the URL hasn't been tampered with.
 *
 * @since  2.4.4
 *
 * @param  string $url URL to test.
 * @return bool
 */
function edd_validate_user_verification_token( $url = '' ) {

	$ret        = false;
	$parts      = parse_url( $url );
	$query_args = array();

	if ( isset( $parts['query'] ) ) {

		wp_parse_str( $parts['query'], $query_args );

		if ( isset( $query_args['ttl'] ) && current_time( 'timestamp' ) > $query_args['ttl'] ) {

			do_action( 'edd_user_verification_token_expired' );

			wp_die( apply_filters( 'edd_verification_link_expired_text', __( 'Sorry but your account verification link has expired. <a href="#">Click here</a> to request a new verification URL.', 'easy-digital-downloads' ) ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );

		}

		if ( isset( $query_args['token'] ) && $query_args['token'] == edd_get_user_verification_token( $url ) ) {

			$ret = true;

		}

	}

	return apply_filters( 'edd_validate_user_verification_token', $ret, $url, $query_args );
}

/**
 * Processes an account verification email request
 *
 * @since  2.4.4
 *
 * @return void
 */
function edd_process_user_verification_request() {

	if( ! wp_verify_nonce( $_GET['_wpnonce'], 'edd-request-verification' ) ) {
		wp_die( __( 'Nonce verification failed.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	if( ! is_user_logged_in() ) {
		wp_die( __( 'You must be logged in to verify your account.', 'easy-digital-downloads' ), __( 'Notice', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	if( ! edd_user_pending_verification( get_current_user_id() ) ) {
		wp_die( __( 'Your account has already been verified.', 'easy-digital-downloads' ), __( 'Notice', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_send_user_verification_email( get_current_user_id() );

	$redirect = apply_filters(
		'edd_user_account_verification_request_redirect',
		add_query_arg( 'edd-verify-request', '1', edd_get_user_verification_page() )
	);

	wp_safe_redirect( $redirect );
	exit;

}
add_action( 'edd_send_verification_email', 'edd_process_user_verification_request' );

/**
 * Processes an account verification
 *
 * @since 2.4.4
 *
 * @return void
 */
function edd_process_user_account_verification() {

	if( empty( $_GET['token'] ) ) {
		return false;
	}

	if( empty( $_GET['user_id'] ) ) {
		return false;
	}

	if( empty( $_GET['ttl'] ) ) {
		return false;
	}

	$parts = parse_url( add_query_arg( array() ) );
	wp_parse_str( $parts['query'], $query_args );
	$url = add_query_arg( $query_args, untrailingslashit( edd_get_user_verification_page() ) );

	if( ! edd_validate_user_verification_token( $url ) ) {

		do_action( 'edd_invalid_user_verification_token' );

		wp_die( __( 'Invalid verification token provided.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_set_user_to_verified( absint( $_GET['user_id'] ) );

	do_action( 'edd_user_verification_token_validated' );

	$redirect = apply_filters(
		'edd_user_account_verified_redirect',
		add_query_arg( 'edd-verify-success', '1', edd_get_user_verification_page() )
	);

	wp_safe_redirect( $redirect );
	exit;

}
add_action( 'edd_verify_user', 'edd_process_user_account_verification' );

/**
 * Retrieves the purchase history page, or main URL for the account verification process
 *
 * @since  2.4.6
 * @return string The base URL to use for account verification
 */
function edd_get_user_verification_page() {
	$url              = home_url();
	$purchase_history = edd_get_option( 'purchase_history_page', 0 );

	if ( ! empty( $purchase_history ) ) {
		$url = get_permalink( $purchase_history );
	}

	return apply_filters( 'edd_user_verification_base_url', $url );
}

/**
 * When a user is deleted, detach that user id from the customer record
 *
 * @since  2.5
 * @param  int $user_id The User ID being deleted
 * @return bool         If the detachment was successful
 */
function edd_detach_deleted_user( $user_id ) {

	$customer = new EDD_Customer( $user_id, true );
	$detached = false;

	if ( $customer->id > 0 ) {
		$detached = $customer->update( array( 'user_id' => 0 ) );
	}

	do_action( 'edd_detach_deleted_user', $user_id, $customer, $detached );

	return $detached;
}
add_action( 'delete_user', 'edd_detach_deleted_user', 10, 1 );
