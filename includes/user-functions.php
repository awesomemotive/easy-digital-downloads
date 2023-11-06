<?php
/**
 * User Functions
 *
 * Functions related to users / customers
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.8.6
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Get Users Purchases
 *
 * Retrieves a list of all purchases by a specific user.
 *
 * @since 1.0
 *
 * @param int|string   $user       User ID or email address.
 * @param int          $number     Number of purchases to retrieve
 * @param bool         $pagination Page number to retrieve
 * @param string|array $status     Either an array of statuses, a single status as a string literal or a comma
 *                                 separated list of statues. Default 'complete'.
 *
 * @return WP_Post[]|false List of all user purchases.
 */
function edd_get_users_purchases( $user = 0, $number = 20, $pagination = false, $status = 'complete' ) {
	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	if ( is_email( $user ) ) {
		$customers = edd_get_customers(
			array(
				'email' => $user,
			)
		);
		if ( $customers ) {
			$user = $customers[0]->user_id;
		}
	}

	// Bail if no user found.
	if ( empty( $user ) ) {
		return false;
	}

	if ( is_string( $status ) ) {
		if ( strpos( $status, ',' ) ) {
			$status = explode( ',', $status );
		} else {
			$status = 'publish' === $status
				? 'complete'
				: $status;

			$status = array( $status );
		}
	}

	if ( is_array( $status ) ) {
		$status = array_unique( $status );
	}

	if ( $pagination ) {
		if ( get_query_var( 'paged' ) ) {
			$paged = get_query_var( 'paged' );
		} elseif ( get_query_var( 'page' ) ) {
			$paged = get_query_var( 'page' );
		} else {
			$paged = 1;
		}
	}

	$args = array(
		'user'    => $user,
		'number'  => $number,
		'status'  => $status,
		'orderby' => 'date',
	);

	if ( $pagination ) {
		$args['page'] = $paged;
	} else {
		$args['nopaging'] = true;
	}

	if ( 'any' === $status ) {
		unset( $args['status'] );
	}

	$purchases = edd_get_payments( apply_filters( 'edd_get_users_purchases_args', $args ) );

	return $purchases
		? $purchases
		: false;
}

/**
 * Retrieve products purchased by a specific user.
 *
 * @since 2.0
 * @since 3.0 Refactored to use new query methods and to be more efficient.
 *
 * @param int|string $user   User ID or email address.
 * @param string     $status Order status.
 *
 * @return WP_Post[]|false Array of products, false otherwise.
 */
function edd_get_users_purchased_products( $user = 0, $status = 'complete' ) {

	if ( $status === 'publish' ) {
		$status = 'complete';
	}

	// Fall back to user ID
	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	// Bail if no user
	if ( empty( $user ) ) {
		return false;
	}

	// Try to get customer
	if ( is_numeric( $user ) ) {
		$customer = edd_get_customer_by( 'user_id', $user );
	} elseif ( is_email( $user ) ) {
		$customer = edd_get_customer_by( 'email', $user );
	} else {
		return false;
	}

	if ( empty( $customer ) ) {
		return false;
	}

	// Fetch the order IDs
	$number = apply_filters( 'edd_users_purchased_products_payments', 9999999 );

	$order_ids = edd_get_orders( array(
		'customer_id' => $customer->id,
		'fields'      => 'ids',
		'status'      => $status,
		'number'      => $number,
	) );

	$product_ids = edd_get_order_items( array(
		'order_id__in' => $order_ids,
		'number'       => $number,
		'fields'       => 'product_id',
	) );

	$product_ids = array_unique( $product_ids );

	// Bail if no product IDs found.
	if ( empty( $product_ids ) ) {
		return false;
	}

	$args = apply_filters( 'edd_get_users_purchased_products_args', array(
		'include'        => $product_ids,
		'post_type'      => 'download',
		'posts_per_page' => -1,
	) );

	return apply_filters( 'edd_users_purchased_products_list', get_posts( $args ) );
}

/**
 * Checks to see if a user has purchased a product.
 *
 * @since 1.0
 * @since 3.0 Refactored to be more efficient.
 *
 * @param int   $user_id   User ID.
 * @param array|int $downloads Download IDs to check against.
 * @param int $variable_price_id - the variable price ID to check for
 *
 * @return bool True if purchased, false otherwise.
 */
function edd_has_user_purchased( $user_id = 0, $downloads = array(), $variable_price_id = null ) {
	global $wpdb;

	// Bail if no user ID passed.
	if ( empty( $user_id ) ) {
		return false;
	}

	/**
	 * Fires before the queries execute.
	 *
	 * @since 2.7.7
	 */
	do_action( 'edd_has_user_purchased_before', $user_id, $downloads, $variable_price_id );

	if ( ! is_array( $downloads ) ) {
		$downloads = array( $downloads );
	}

	// Bail if no downloads passed.
	if ( empty( $downloads ) ) {
		return false;
	}

	$number = apply_filters( 'edd_users_purchased_products_payments', 9999999 );

	$where_id   = "'" . implode( "', '", $wpdb->_escape( $downloads ) ) . "'";
	$product_id = "oi.product_id IN ({$where_id})";

	$price_id = isset( $variable_price_id )
		? $wpdb->prepare( 'AND oi.price_id = %d', absint( $variable_price_id ) )
		: '';

	$statuses  = "'" . implode( "', '", $wpdb->_escape( edd_get_deliverable_order_item_statuses() ) ). "'";
	$status_id = " AND oi.status IN({$statuses})";

	// Perform a direct database query as it is more efficient.
	$sql = $wpdb->prepare("
		SELECT COUNT(o.id) AS count
		FROM {$wpdb->edd_orders} o
		INNER JOIN {$wpdb->edd_order_items} oi ON o.id = oi.order_id
		WHERE {$product_id} {$price_id} {$status_id}
		AND o.type = 'sale'
		AND user_id = %d
		LIMIT %d",
		absint( $user_id ),
		$number
	);

	$result = (int) $wpdb->get_var( $sql );

	$return = 0 === $result
		? false
		: true;

	/**
	 * @since 2.7.7
	 *
	 * Filter has purchased result
	 */
	return apply_filters( 'edd_has_user_purchased', $return, $user_id, $downloads, $variable_price_id );
}

/**
 * Check if a user has made any purchases.
 *
 * @since 1.0
 *
 * @param int $user_id User ID.
 * @return bool True if user has purchased, false otherwise.
 */
function edd_has_purchases( $user_id = null ) {

	// Maybe fallback to logged in user.
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	if ( empty( $user_id ) ) {
		return false;
	}

	return (bool) edd_get_orders(
		array(
			'user_id'    => $user_id,
			'type'       => 'sale',
			'status__in' => edd_get_complete_order_statuses(),
			'number'     => 1,
		)
	);
}


/**
 * Get purchase statistics for user.
 *
 * @since 1.6
 * @since 3.0 Updated to use new query method.
 *
 * @param int|string $user User ID or email address.
 *
 * @return array|false $stats Number of purchases and total amount spent by customer. False otherwise.
 */
function edd_get_purchase_stats_by_user( $user = '' ) {
	if ( is_email( $user ) ) {
		$field = 'email';
	} elseif ( is_numeric( $user ) ) {
		$field = 'user_id';
	} else {
		return false;
	}

	$stats    = array();
	$customer = edd_get_customer_by( $field, $user );

	if ( $customer ) {
		$stats['purchases']   = edd_count_orders( array( $field => $user ) );
		$stats['total_spent'] = edd_sanitize_amount( $customer->purchase_value );
	}

	return (array) apply_filters( 'edd_purchase_stats_by_user', $stats, $user );
}


/**
 * Count number of purchases of a customer.
 *
 * @since 1.3
 *
 * @param string|int $user User ID or email.
 * @return int Number of purchases.
 */
function edd_count_purchases_of_customer( $user = null ) {
	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	$stats = ! empty( $user )
		? edd_get_purchase_stats_by_user( $user )
		: false;

	return isset( $stats['purchases'] )
		? $stats['purchases']
		: 0;
}

/**
 * Calculates the total amount spent by a user
 *
 * @since       1.3
 * @param       mixed $user - ID or email
 * @return      float - the total amount the user has spent
 */
function edd_purchase_total_of_user( $user = null ) {
	$stats = edd_get_purchase_stats_by_user( $user );

	return isset( $stats['total_spent'] ) ? $stats['total_spent'] : 0.00;
}

/**
 * Counts the total number of files a user (or customer if an email address is
 * given) has downloaded
 *
 * @since       1.3
 * @since       3.0 Updated to use edd_count_file_download_logs.
 * @param       mixed $user - ID or email
 * @return      int - The total number of files the user has downloaded
 */
function edd_count_file_downloads_of_user( $user ) {

	// If we got an email, look up the customer ID and call the direct query
	// for customer download counts.
	if ( is_email( $user ) ) {
		return edd_count_file_downloads_of_customer( $user );
	}

	$customer = edd_get_customer_by( 'user_id', $user );

	return ! empty( $customer->id ) ? edd_count_file_download_logs(
		array(
			'customer_id' => $customer->id,
		)
	) : 0;
}

/**
 * Counts the total number of files a customer has downloaded.
 *
 * @since unknown
 * @since 3.0     Updated to use edd_count_file_download_logs.
 * @param string|int $customer_id_or_email The email address or id of the customer.
 *
 * @return int The total number of files the customer has downloaded.
 */
function edd_count_file_downloads_of_customer( $customer_id_or_email = '' ) {
	$customer = new EDD_Customer( $customer_id_or_email );

	return edd_count_file_download_logs(
		array(
			'customer_id' => $customer->id,
		)
	);
}

/**
 * Validate a potential username
 *
 * @since       1.3.4
 * @param       string $username The username to validate
 * @return      bool
 */
function edd_validate_username( $username ) {
	$sanitized = sanitize_user( $username, false );
	$valid     = ( $sanitized == $username );

	return (bool) apply_filters( 'edd_validate_username', $valid, $username );
}

/**
 * Attach the customer to an existing user account when completing guest purchase.
 *
 * This only runs when a user account already exists and a guest purchase is made
 * with the account's email address.
 *
 * After attaching the customer to the user ID, the account is set to pending.
 *
 * @since  2.8
 * @param  bool   $success     True if payment was added successfully, false otherwise.
 * @param  int    $payment_id  The ID of the EDD_Payment that was added.
 * @param  int    $customer_id The ID of the EDD_Customer object.
 * @param  object $customer    The EDD_Customer object.
 * @return void
 */
function edd_connect_guest_customer_to_existing_user( $success, $payment_id, $customer_id, $customer ) {

	// If for some reason we don't get a customer object here, return.
	if ( ! $customer instanceof EDD_Customer ) {
		return;
	}

	if ( ! empty( $customer->user_id ) || empty( $customer->email ) ) {
		return;
	}

	$user = get_user_by( 'email', $customer->email );

	if ( ! $user instanceof WP_User ) {
		return;
	}

	$customer->update( array( 'user_id' => $user->ID ) );

	// Set a flag to force the account to be verified before purchase history can be accessed.
	edd_set_user_to_pending( $user->ID );
	edd_send_user_verification_email( $user->ID );

}
add_action( 'edd_customer_post_attach_payment', 'edd_connect_guest_customer_to_existing_user', 10, 4 );

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
 * @since       1.6
 * @param       int $user_id - the new user's ID
 * @return      void
 */
function edd_add_past_purchases_to_new_user( $user_id ) {

	$email    = get_the_author_meta( 'user_email', $user_id );

	if ( empty( $email ) ) {
		return;
	}

	$payments = edd_get_payments( array( 's' => $email, 'output' => 'payments' ) );

	if( $payments ) {

		// Set a flag to force the account to be verified before purchase history can be accessed
		edd_set_user_to_pending( $user_id );

		edd_send_user_verification_email( $user_id );

		foreach( $payments as $payment ) {
			if ( is_object( $payment ) && $payment instanceof EDD_Payment ) {
				if ( intval( $payment->user_id ) > 0 ) {
					continue; // This payment already associated with an account
				}

				$payment->user_id = $user_id;
				$payment->save();
			}
		}
	}

}
add_action( 'user_register', 'edd_add_past_purchases_to_new_user', 10, 1 );


/**
 * Counts the total number of customers.
 *
 * @since 		1.7
 * @return 		int - The total number of customers.
 */
function edd_count_total_customers( $args = array() ) {
	return edd_count_customers();
}


/**
 * Returns the saved address for a customer
 *
 * @since 1.8
 * @since 3.0 Update to use new query methods.

 * @param int $user_id User ID.
 * @return array Customer address.
 */
function edd_get_customer_address( $user_id = 0 ) {

	// Maybe fall back to logged in user ID.
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$customer = edd_get_customer_by( 'user_id', $user_id );

	$parsed_address = array();

	if ( $customer ) {
		$address = $customer->get_address();

		if ( $address instanceof EDD\Customers\Customer_Address ) {
			$parsed_address = array(
				'line1'   => $address->address,
				'line2'   => $address->address2,
				'city'    => $address->city,
				'zip'     => $address->postal_code,
				'country' => $address->country,
				'state'   => $address->region,
			);
		}
	}

	$address = wp_parse_args( $parsed_address, array(
		'line1'   => '',
		'line2'   => '',
		'city'    => '',
		'zip'     => '',
		'country' => '',
		'state'   => '',
	) );

	return $address;
}

/**
 * Sends the new user notification email when a user registers during checkout
 *
 * @since       1.8.8
 * @param int   $user_id
 * @param array $user_data
 *
 * @return      void
 */
function edd_new_user_notification( $user_id = 0, $user_data = array() ) {

	if( empty( $user_id ) || empty( $user_data ) ) {
		return;
	}

	$emails     = EDD()->emails;
	$from_name  = edd_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_email = edd_get_option( 'from_email', get_bloginfo( 'admin_email' ) );

	// Setup and send the new user email for Admins.
	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_address', $from_email );

	/* translators: Site name */
	$admin_subject  = apply_filters( 'edd_user_registration_admin_email_subject', sprintf( __( '[%s] New User Registration', 'easy-digital-downloads' ), $from_name ), $user_data );
	$admin_heading  = apply_filters( 'edd_user_registration_admin_email_heading', __( 'New user registration', 'easy-digital-downloads' ), $user_data );
	/* translators: the username */
	$admin_message  = sprintf( __( 'Username: %s', 'easy-digital-downloads' ), $user_data['user_login'] ) . "\r\n\r\n";
	/* translators: the user email */
	$admin_message .= sprintf( __( 'E-mail: %s', 'easy-digital-downloads' ), $user_data['user_email'] ) . "\r\n";

	$admin_message = apply_filters( 'edd_user_registration_admin_email_message', $admin_message, $user_data );

	$emails->__set( 'heading', $admin_heading );

	$emails->send( get_option( 'admin_email' ), $admin_subject, $admin_message );

	// Setup and send the new user email for the end user.
	/* translators: Site name */
	$user_subject  = apply_filters( 'edd_user_registration_email_subject', sprintf( __( '[%s] Your username and password', 'easy-digital-downloads' ), $from_name ), $user_data );
	$user_heading  = apply_filters( 'edd_user_registration_email_heading', __( 'Your account info', 'easy-digital-downloads' ), $user_data );
	$user_message  = apply_filters( 'edd_user_registration_email_username', sprintf( __( 'Username: %s', 'easy-digital-downloads' ), $user_data['user_login'] ) . "\r\n", $user_data );

	if ( did_action( 'edd_pre_process_purchase' ) ) {
		$password_message = __( 'Password entered at checkout', 'easy-digital-downloads' );
	} else {
		$password_message = __( 'Password entered at registration', 'easy-digital-downloads' );
	}

	/* translators: %s: password message */
	$user_message .= apply_filters( 'edd_user_registration_email_password', sprintf( __( 'Password: %s', 'easy-digital-downloads' ), '[' . $password_message . ']' ) . "\r\n" );

	$login_url = apply_filters( 'edd_user_registration_email_login_url', wp_login_url() );
	if( $emails->html ) {

		$user_message .= '<a href="' . esc_url( $login_url ) . '"> ' . esc_attr__( 'Click here to log in', 'easy-digital-downloads' ) . ' &rarr;</a>' . "\r\n";

	} else {
		/* translators: %s: login URL */
		$user_message .= sprintf( __( 'To log in, visit: %s', 'easy-digital-downloads' ), esc_url( $login_url ) ) . "\r\n";
	}

	$user_message = apply_filters( 'edd_user_registration_email_message', $user_message, $user_data );

	$emails->__set( 'heading', $user_heading );

	$emails->send( $user_data['user_email'], $user_subject, $user_message );

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
 * @since   2.4.4
 * @return  bool
 */
function edd_user_pending_verification( $user_id = null ) {

	if( is_null( $user_id ) ) {
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
 * @since   2.4.4
 * @return  string
 */
function edd_get_user_verification_url( $user_id = 0 ) {

	if( empty( $user_id ) ) {
		return false;
	}

	$base_url = add_query_arg( array(
		'edd_action' => 'verify_user',
		'user_id'    => absint( $user_id ),
		'ttl'        => strtotime( '+24 hours' )
	), untrailingslashit( edd_get_user_verification_page() ) );

	$token = edd_get_user_verification_token( $base_url );
	$url   = add_query_arg( 'token', $token, $base_url );

	return apply_filters( 'edd_get_user_verification_url', $url, $user_id );

}

/**
 * Gets the URL that triggers a new verification email to be sent
 *
 * @since   2.4.4
 * @return  string
 */
function edd_get_user_verification_request_url( $user_id = 0 ) {

	if( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$url = esc_url( wp_nonce_url( add_query_arg( array(
		'edd_action' => 'send_verification_email'
	) ), 'edd-request-verification' ) );

	return apply_filters( 'edd_get_user_verification_request_url', $url, $user_id );

}

/**
 * Sends an email to the specified user with a URL to verify their account
 *
 * @since   2.4.4
 * @param int $user_id
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

	$name       = $user_data->display_name;
	$url        = edd_get_user_verification_url( $user_id );
	$from_name  = edd_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_email = edd_get_option( 'from_email', get_bloginfo( 'admin_email' ) );
	$subject    = apply_filters( 'edd_user_verification_email_subject', __( 'Verify your account', 'easy-digital-downloads' ), $user_id );
	$heading    = apply_filters( 'edd_user_verification_email_heading', __( 'Verify your account', 'easy-digital-downloads' ), $user_id );
	$message    = sprintf(
		/* translators: 1: User display name, 2: Site name, 3: Verification URL */
		__( 'Hello %1$s,

		Your account with %2$s needs to be verified before you can access your purchase history. <a href="%3$s">Click here</a> to verify your account.

		Link missing? Visit the following URL: %3$s', 'easy-digital-downloads' ),
		$name,
		$from_name,
		esc_url_raw( $url ),
		// This parameter is not used, but is added for compatibility with past translations.
		esc_url_raw( $url )
	);

	$message    = apply_filters( 'edd_user_verification_email_message', $message, $user_id );

	$emails     = new EDD_Emails;

	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_address', $from_email );
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

			$link_text = sprintf(
				/* translators: %s: URL to request a new verification link */
				__( 'Sorry but your account verification link has expired. <a href="%s">Click here</a> to request a new verification URL.', 'easy-digital-downloads' ),
				esc_url( edd_get_user_verification_request_url() )
			);

			wp_die( apply_filters( 'edd_verification_link_expired_text', $link_text ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );

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

	edd_redirect( $redirect );

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

	edd_redirect( $redirect );
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

/**
 * Modify User Profile
 *
 * Modifies the output of profile.php to add key generation/revocation
 *
 * @since 2.6
 * @param object $user Current user info
 * @return void
 */
function edd_show_user_api_key_field( $user ) {

	// Bail if no user, or user ID is not current user
	if ( empty( $user ) || ( get_current_user_id() !== $user->ID ) ) {
		return;
	}

	/**
	 * Show API User Key Fields
	 *
	 * Allows showing/hiding the user API Key fields. By default will only try to show on admin pages. The filter
	 * allows for developers to choose to show it in other places that the WordPress profile editor hooks are used
	 * like bbPress
	 *
	 * @since 2.9.1
	 *
	 * @param boolean If EDD should attempt to load the user API fields
	 * @param WP_User The User object currently being viewed.
	 */
	$show_fields = apply_filters( 'edd_show_user_api_key_fields', is_admin(), $user );
	if ( ! $show_fields ) {
		return;
	}

	if ( ( edd_get_option( 'api_allow_user_keys', false ) || current_user_can( 'manage_shop_settings' ) ) && current_user_can( 'edit_user', $user->ID ) ) {
		$user = get_userdata( $user->ID );
		$public_key = EDD()->api->get_user_public_key( $user->ID );
		$secret_key = EDD()->api->get_user_secret_key( $user->ID );
		$token      = EDD()->api->get_token( $user->ID );
		?>

		<style type="text/css">
			.edd-api-keys strong {
				display: inline-block;
				width: 100px;
				font-weight: 400;
				font-size: 13px;
			}

			.edd-api-keys .code {
				width: 230px;
				display: inline-block;
				background: #fafafa;
				font-size: 11px;
				box-shadow: none;
				cursor: text;
				border-radius: 2px;
			}
		</style>

		<table class="form-table">
			<tbody>
			<tr>
				<th>
					<?php _e( 'Downloads API Keys', 'easy-digital-downloads' ); ?>
				</th>
				<td>
					<?php if ( empty( $user->edd_user_public_key ) ) { ?>
						<p class="description">
							<label>
								<input name="edd_set_api_key" type="checkbox" id="edd_set_api_key" value="0"/>
								<?php _e( 'Generate API Key', 'easy-digital-downloads' ); ?>
							</label>
						</p>
					<?php } else { ?>
						<div class="edd-api-keys">
							<strong><?php _e( 'Public Key:', 'easy-digital-downloads' ); ?></strong>
								<input type="text" readonly="readonly" class="code" id="publickey" value="<?php echo esc_attr( $public_key ); ?>"/>
								<br/>
							<strong><?php _e( 'Secret Key:', 'easy-digital-downloads' ); ?></strong>
								<input type="text" readonly="readonly" class="code" id="privatekey" value="<?php echo esc_attr( $secret_key ); ?>"/>
								<br/>
							<strong><?php _e( 'Token:',      'easy-digital-downloads' ); ?></strong>
								<input type="text" readonly="readonly" class="code" id="token" value="<?php echo esc_attr( EDD()->api->get_token( $user->ID ) ); ?>"/>
								<br/>
							<p class="description">
								<label>
									<input name="edd_set_api_key" type="checkbox" id="edd_set_api_key" value="0" />
									<?php _e( 'Revoke API Keys', 'easy-digital-downloads' ); ?>
								</label>
							</p>
						</div>
					<?php } ?>
				</td>
			</tr>
			</tbody>
		</table>

		<?php if ( wp_is_mobile() ) : ?>
		<table class="form-table">
			<tbody>
			<tr>
				<th>
					<?php
					/* translators: %s: iOS App URL */
					printf( __( 'Easy Digital Downloads <a href="%s">iOS App</a>', 'easy-digital-downloads' ), 'https://itunes.apple.com/us/app/easy-digital-downloads-2/id1169488828?ls=1&mt=8' );
					?>
				</th>
				<td>
					<?php
					$sitename = get_bloginfo( 'name' );
					$ios_url  = 'edd://new?sitename=' . $sitename . '&siteurl=' . home_url() . '&key=' . $public_key . '&token=' . $token;
					?>
					<a class="button button-secondary" href="<?php echo esc_url( $ios_url ); ?>"><?php esc_html_e( 'Add to iOS App', 'easy-digital-downloads' ); ?></a>
				</td>
			</tr>
			</tbody>
		</table>
		<?php endif; ?>

	<?php }
}
add_action( 'show_user_profile', 'edd_show_user_api_key_field' );
add_action( 'edit_user_profile', 'edd_show_user_api_key_field' );

/**
 * Generate and Save API key
 *
 * Generates the key requested by user_key_field and stores it in the database
 *
 * @since 2.6
 * @param int $user_id
 * @return void
 */
function edd_update_user_api_key( $user_id ) {
	if ( current_user_can( 'edit_user', $user_id ) && isset( $_POST['edd_set_api_key'] ) ) {

		$user       = get_userdata( $user_id );
		$public_key = EDD()->api->get_user_public_key( $user_id );

		if ( empty( $public_key ) ) {
			$new_public_key = EDD()->api->generate_public_key( $user->user_email );
			$new_secret_key = EDD()->api->generate_private_key( $user->ID );

			update_user_meta( $user_id, $new_public_key, 'edd_user_public_key' );
			update_user_meta( $user_id, $new_secret_key, 'edd_user_secret_key' );
		} else {
			EDD()->api->revoke_api_key( $user_id );
		}
	}
}
add_action( 'personal_options_update',  'edd_update_user_api_key' );
add_action( 'edit_user_profile_update', 'edd_update_user_api_key' );
