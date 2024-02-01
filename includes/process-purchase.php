<?php
/**
 * Process Purchase
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Process Purchase Form
 *
 * Handles the purchase form process.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function edd_process_purchase_form() {

	do_action( 'edd_pre_process_purchase' );

	// Make sure the cart isn't empty.
	if ( ! edd_get_cart_contents() && ! edd_cart_has_fees() ) {
		$valid_data = false;
		edd_set_error( 'empty_cart', __( 'Your cart is empty', 'easy-digital-downloads' ) );
	} else {
		// Validate the form $_POST data.
		$valid_data = edd_purchase_form_validate_fields();

		// Allow themes and plugins to hook to errors.
		do_action( 'edd_checkout_error_checks', $valid_data, $_POST );
	}

	$is_ajax = isset( $_POST['edd_ajax'] );

	if ( $is_ajax ) {
		if ( ! isset( $_POST['edd-process-checkout-nonce'] ) ) {
			edd_debug_log( __( 'Missing nonce when processing checkout. Please read the following for more information: https://easydigitaldownloads.com/development/2018/07/05/important-update-to-ajax-requests-in-easy-digital-downloads-2-9-4', 'easy-digital-downloads' ), true );
		}

		$nonce = isset( $_POST['edd-process-checkout-nonce'] ) ? sanitize_text_field( $_POST['edd-process-checkout-nonce'] ) : '';
		$nonce_verified = wp_verify_nonce( $nonce, 'edd-process-checkout' );

		if ( false === $nonce_verified ) {
			edd_set_error( 'checkout-nonce-error', __( 'Error processing purchase. Please reload the page and try again.', 'easy-digital-downloads' ) );
		}
	}

	// Process the login form.
	if ( isset( $_POST['edd_login_submit'] ) ) {
		edd_process_purchase_login();
	}

	// Validate the user.
	$user = edd_get_purchase_form_user( $valid_data, $is_ajax );

	// Let extensions validate fields after user is logged in if user has used login/registration form.
	do_action( 'edd_checkout_user_error_checks', $user, $valid_data, $_POST );

	if ( false === $valid_data || edd_get_errors() || ! $user ) {
		if ( $is_ajax ) {
			do_action( 'edd_ajax_checkout_errors' );
			edd_die();
		} else {
			return false;
		}
	}

	if ( $is_ajax ) {
		echo 'success';
		edd_die();
	}

	// Setup user information.
	$user_info = array(
		'id'         => $user['user_id'],
		'email'      => $user['user_email'],
		'first_name' => $user['user_first'],
		'last_name'  => $user['user_last'],
		'discount'   => $valid_data['discount'],
		'address'    => ! empty( $user['address'] ) ? $user['address'] : array(),
	);

	// Update a customer record if they have added/updated information.
	$customer = new EDD_Customer( $user_info['email'] );

	$name = $user_info['first_name'] . ' ' . $user_info['last_name'];
	if ( empty( $customer->name ) || $name != $customer->name ) {
		$update_data = array(
			'name' => $name
		);

		// Update the customer's name and update the user record too.
		$customer->update( $update_data );
		wp_update_user( array(
			'ID'         => get_current_user_id(),
			'first_name' => $user_info['first_name'],
			'last_name'  => $user_info['last_name']
		) );
	}

	// Update the customer's address if different to what's in the database.
	$address = wp_parse_args( $user_info['address'], array(
		'line1'   => '',
		'line2'   => '',
		'city'    => '',
		'state'   => '',
		'country' => '',
		'zip'     => '',
	) );

	$address = array(
		'address'     => $address['line1'],
		'address2'    => $address['line2'],
		'city'        => $address['city'],
		'region'      => $address['state'],
		'country'     => $address['country'],
		'postal_code' => $address['zip'],
	);

	$card_country = isset( $valid_data['cc_info']['card_country'] ) ? $valid_data['cc_info']['card_country'] : false;
	$card_state   = isset( $valid_data['cc_info']['card_state'] )   ? $valid_data['cc_info']['card_state']   : false;
	$card_zip     = isset( $valid_data['cc_info']['card_zip'] )     ? $valid_data['cc_info']['card_zip']     : false;

	// Set up the unique purchase key. If we are resuming a payment, we'll overwrite this with the existing key.

	$purchase_key     = edd_generate_order_payment_key( $user['user_email'] );
	$existing_payment = EDD()->session->get( 'edd_resume_payment' );

	if ( ! empty( $existing_payment ) ) {
		$order = edd_get_order( $existing_payment );
		if ( $order->is_recoverable() && ! empty( $order->payment_key ) ) {
			$purchase_key = $order->payment_key;
		}
	}

	// Setup purchase information.
	$purchase_data = array(
		'downloads'    => edd_get_cart_contents(),
		'fees'         => edd_get_cart_fees(),        // Any arbitrary fees that have been added to the cart.
		'subtotal'     => edd_get_cart_subtotal(),    // Amount before taxes and discounts.
		'discount'     => edd_get_cart_discounted_amount(), // Discounted amount.
		'tax'          => edd_get_cart_tax(),               // Taxed amount.
		'tax_rate'     => edd_use_taxes() ? edd_get_cart_tax_rate( $card_country, $card_state, $card_zip ) : 0, // Tax rate.
		'price'        => edd_get_cart_total(),    // Amount after taxes.
		'purchase_key' => $purchase_key,
		'user_email'   => $user['user_email'],
		'date'         => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
		'user_info'    => stripslashes_deep( $user_info ),
		'post_data'    => $_POST,
		'cart_details' => edd_get_cart_content_details(),
		'gateway'      => $valid_data['gateway'],
		'card_info'    => $valid_data['cc_info']
	);

	// Add the user data for hooks.
	$valid_data['user'] = $user;

	// Allow themes and plugins to hook before the gateway.
	do_action( 'edd_checkout_before_gateway', $_POST, $user_info, $valid_data );

	// If the total amount in the cart is 0, send to the manual gateway. This emulates a free download purchase.
	if ( ! $purchase_data['price'] ) {

		// Revert to manual.
		$purchase_data['gateway'] = 'manual';
		$_POST['edd-gateway']     = 'manual';
	}

	// Allow the purchase data to be modified before it is sent to the gateway.
	$purchase_data = apply_filters(
		'edd_purchase_data_before_gateway',
		$purchase_data,
		$valid_data
	);

	// Setup the data we're storing in the purchase session.
	$session_data = $purchase_data;

	// Make sure credit card numbers are never stored in sessions.
	unset( $session_data['card_info']['card_number'] );

	// Used for showing download links to non logged-in users after purchase, and for other plugins needing purchase data.
	edd_set_purchase_session( $session_data );

	// Send info to the gateway for payment processing.
	edd_send_to_gateway( $purchase_data['gateway'], $purchase_data );
	edd_die();
}
add_action( 'edd_purchase',                        'edd_process_purchase_form' );
add_action( 'wp_ajax_edd_process_checkout',        'edd_process_purchase_form' );
add_action( 'wp_ajax_nopriv_edd_process_checkout', 'edd_process_purchase_form' );

/**
 * Verify that when a logged in user makes a purchase that the email address
 * used doesn't belong to a different customer
 *
 * @since  2.6
 * @param  array $valid_data Validated data submitted for the purchase
 * @param  array $post       Additional $_POST data submitted
 * @return void
 */
function edd_checkout_check_existing_email( $valid_data, $post ) {

	if ( ! is_user_logged_in() ) {
		return;
	}

	$email    = strtolower( $valid_data['logged_in_user']['user_email'] );
	$customer = edd_get_customer_by( 'user_id', get_current_user_id() );

	// If the current user has a customer record and the email address matches, we're good to go.
	if ( ! empty( $customer->email ) && strtolower( $customer->email ) === $email ) {
		return;
	}

	// If the current user has a customer record and the email address is in the list of emails, we're good to go.
	if (
		! empty( $customer->emails ) && // If the customer has emails.
		is_array( $customer->emails ) && // ...and if the customer emails are an array.
		in_array( $email, array_map( 'strtolower', $customer->emails ), true ) // ...and the email is in the array.
	) {
		return; // ...we're good to go here.
	}

	// Now we are checking to see if any other customers have this email address.
	$found_email = edd_get_customer_email_address_by( 'email', $email );
	if ( ! empty( $found_email->customer_id ) ) {
		edd_set_error(
			'edd-customer-email-exists',
			/* translators: %s: email address */
			sprintf( __( 'The email address %s is already in use.', 'easy-digital-downloads' ), $email )
		);
	}
}
add_action( 'edd_checkout_error_checks', 'edd_checkout_check_existing_email', 10, 2 );

/**
 * Process the checkout login form
 *
 * @access      private
 * @since       1.8
 * @return      void
 */
function edd_process_purchase_login() {

	$is_ajax = isset( $_POST['edd_ajax'] );

	if ( ! isset( $_POST['edd_login_nonce'] ) ) {
		edd_debug_log( __( 'Missing nonce when processing login during checkout. Please read the following for more information: https://easydigitaldownloads.com/development/2018/07/09/important-update-to-ajax-requests-in-easy-digital-downloads-2-9-4', 'easy-digital-downloads' ), true );
	}

	$nonce = isset( $_POST['edd_login_nonce'] ) ? sanitize_text_field( $_POST['edd_login_nonce'] ) : '';
	$nonce_verified = wp_verify_nonce( $nonce, 'edd-login-form' );
	if ( false === $nonce_verified ) {
		edd_set_error( 'edd-login-nonce-failed', __( 'Error processing login. Nonce failed.', 'easy-digital-downloads' ) );

		if ( $is_ajax ) {
			do_action( 'edd_ajax_checkout_errors' );
			edd_die();
		} else {
			edd_redirect( wp_get_referer() );
		}
	}

	$user_data = edd_purchase_form_validate_user_login();

	if ( edd_get_errors() || $user_data['user_id'] < 1 ) {
		if ( $is_ajax ) {
			do_action( 'edd_ajax_checkout_errors' );
			edd_die();
		} else {
			edd_redirect( wp_get_referer() );
		}
	}

	edd_log_user_in( $user_data['user_id'], $user_data['user_login'], $user_data['user_pass'] );

	if ( $is_ajax ) {
		echo 'success';
		edd_die();
	} else {
		edd_redirect( edd_get_checkout_uri( $_SERVER['QUERY_STRING'] ) );
	}
}
add_action( 'wp_ajax_edd_process_checkout_login', 'edd_process_purchase_login' );
add_action( 'wp_ajax_nopriv_edd_process_checkout_login', 'edd_process_purchase_login' );

/**
 * Purchase Form Validate Fields
 *
 * @access      private
 * @since       1.0.8.1
 * @return      bool|array
 */
function edd_purchase_form_validate_fields() {

	// Bail if there is no $_POST.
	if ( empty( $_POST ) ) {
		return false;
	}

	// Start an array to collect valid data.
	$valid_data = array(
		'gateway'          => edd_purchase_form_validate_gateway(),   // Gateway fallback.
		'discount'         => edd_purchase_form_validate_discounts(), // Set default discount.
		'need_new_user'    => false,     // New user flag.
		'need_user_login'  => false,     // Login user flag.
		'logged_user_data' => array(),   // Logged user collected data.
		'new_user_data'    => array(),   // New user collected data.
		'login_user_data'  => array(),   // Login user collected data.
		'guest_user_data'  => array(),   // Guest user collected data.
		'cc_info'          => edd_purchase_form_validate_cc(),    // Credit card info.
	);

	// Validate agree to terms.
	if ( '1' === edd_get_option( 'show_agree_to_terms', false ) ) {
		edd_purchase_form_validate_agree_to_terms();
	}

	// Validate agree to privacy policy.
	if ( '1' === edd_get_option( 'show_agree_to_privacy_policy', false ) ) {
		edd_purchase_form_validate_agree_to_privacy_policy();
	}

	// Collect logged in user data
	if ( is_user_logged_in() ) {
		$valid_data['logged_in_user'] = edd_purchase_form_validate_logged_in_user();

	} elseif ( isset( $_POST['edd-purchase-var'] ) && 'needs-to-register' === $_POST['edd-purchase-var'] ) {
		// Set new user registration as required.
		$valid_data['need_new_user'] = true;

		// Validate new user data.
		$valid_data['new_user_data'] = edd_purchase_form_validate_new_user();

	// Check if login validation is needed.
	} elseif ( isset( $_POST['edd-purchase-var'] ) && 'needs-to-login' === $_POST['edd-purchase-var'] ) {
		// Set user login as required.
		$valid_data['need_user_login'] = true;

		// Validate users login info.
		$valid_data['login_user_data'] = edd_purchase_form_validate_user_login();

	// Not registering or logging in, so setup guest user data
	} else {
		// Not registering or logging in, so setup guest user data.
		$valid_data['guest_user_data'] = edd_purchase_form_validate_guest_user();
	}

	// Return collected data.
	return $valid_data;
}

/**
 * Purchase Form Validate Gateway
 *
 * @access      private
 * @since       1.0
 * @return      string
 */
function edd_purchase_form_validate_gateway() {

	$gateway = edd_get_default_gateway();

	// Check if a gateway value is present
	if ( ! empty( $_REQUEST['edd-gateway'] ) ) {

		$gateway = sanitize_text_field( $_REQUEST['edd-gateway'] );

		if ( '0.00' == edd_get_cart_total() ) {
			$gateway = 'manual';

		} elseif ( ! edd_is_gateway_active( $gateway ) ) {
			edd_set_error( 'invalid_gateway', __( 'The selected payment gateway is not enabled', 'easy-digital-downloads' ) );
		}
	}

	return $gateway;
}

/**
 * Purchase Form Validate Discounts
 *
 * @access      private
 * @since       1.0.8.1
 * @return      string
 */
function edd_purchase_form_validate_discounts() {
	// Retrieve the discount stored in cookies
	$discounts = edd_get_cart_discounts();

	$user = '';
	if ( isset( $_POST['edd_user_login'] ) && ! empty( $_POST['edd_user_login'] ) ) {
		$user = sanitize_text_field( $_POST['edd_user_login'] );
	} elseif ( isset( $_POST['edd_email'] ) && ! empty($_POST['edd_email'] ) ) {
		$user = sanitize_text_field( $_POST['edd_email'] );
	} elseif ( is_user_logged_in() ) {
		$user = wp_get_current_user()->user_email;
	}

	$error = false;

	// Check for valid discount(s) is present
	if ( ! empty( $_POST['edd-discount'] ) && __( 'Enter discount', 'easy-digital-downloads' ) != $_POST['edd-discount'] ) {
		// Check for a posted discount
		$posted_discount = isset( $_POST['edd-discount'] ) ? trim( $_POST['edd-discount'] ) : false;

		// Add the posted discount to the discounts
		if ( $posted_discount && ( empty( $discounts ) || edd_multiple_discounts_allowed() ) && edd_is_discount_valid( $posted_discount, $user ) ) {
			edd_set_cart_discount( $posted_discount );
		}

	}

	// If we have discounts, loop through them
	if ( ! empty( $discounts ) ) {

		foreach ( $discounts as $discount ) {
			// Check if valid
			if (  ! edd_is_discount_valid( $discount, $user ) ) {
				// Discount is not valid
				$error = true;
			}
		}
	} else {
		// No discounts
		return 'none';
	}

	if ( $error ) {
		edd_set_error( 'invalid_discount', __( 'One or more of the discounts you entered is invalid', 'easy-digital-downloads' ) );
	}

	return implode( ', ', $discounts );
}

/**
 * Purchase Form Validate Agree To Terms
 *
 * @access      private
 * @since       1.0.8.1
 * @return      void
 */
function edd_purchase_form_validate_agree_to_terms() {

	// User did not agree
	if ( ! isset( $_POST['edd_agree_to_terms'] ) || $_POST['edd_agree_to_terms'] != 1 ) {
		edd_set_error( 'agree_to_terms', apply_filters( 'edd_agree_to_terms_text', __( 'You must agree to the terms of use', 'easy-digital-downloads' ) ) );
	}
}

/**
 * Purchase Form Validate Agree To Privacy Policy
 *
 * @since       2.9.1
 * @return      void
 */
function edd_purchase_form_validate_agree_to_privacy_policy() {

	// User did not agree
	if ( ! isset( $_POST['edd_agree_to_privacy_policy'] ) || $_POST['edd_agree_to_privacy_policy'] != 1 ) {
		edd_set_error( 'agree_to_privacy_policy', apply_filters( 'edd_agree_to_privacy_policy_text', __( 'You must agree to the privacy policy', 'easy-digital-downloads' ) ) );
	}
}

/**
 * Purchase Form Required Fields
 *
 * @access      private
 * @since       1.5
 * @return      array
 */
function edd_purchase_form_required_fields() {

	// These fields are _always_ required
	$required_fields = array(
		'edd_email' => array(
			'error_id'      => 'invalid_email',
			'error_message' => __( 'Please enter a valid email address', 'easy-digital-downloads' )
		),
		'edd_first' => array(
			'error_id'      => 'invalid_first_name',
			'error_message' => __( 'Please enter your first name', 'easy-digital-downloads' )
		)
	);

	// Let payment gateways and other extensions determine if address fields should be required
	$require_address = apply_filters( 'edd_require_billing_address', edd_use_taxes() && edd_get_cart_total() );

	if ( ! empty( $require_address ) ) {

		// Zip
		$required_fields['card_zip'] = array(
			'error_id'      => 'invalid_zip_code',
			'error_message' => __( 'Please enter your zip / postal code', 'easy-digital-downloads' )
		);

		// City
		$required_fields['card_city'] = array(
			'error_id'      => 'invalid_city',
			'error_message' => __( 'Please enter your billing city', 'easy-digital-downloads' )
		);

		// Country
		$required_fields['billing_country'] = array(
			'error_id'      => 'invalid_country',
			'error_message' => __( 'Please select your billing country', 'easy-digital-downloads' )
		);

		// State/Region
		$required_fields['card_state'] = array(
			'error_id'      => 'invalid_state',
			'error_message' => __( 'Please enter billing state / region', 'easy-digital-downloads' )
		);

		// Check if the Customer's Country has been passed in and if it has no states.
		if ( isset( $_POST['billing_country'] ) && isset( $required_fields['card_state'] ) ) {
			$customer_billing_country = sanitize_text_field( $_POST['billing_country'] );
			$states = edd_get_shop_states( $customer_billing_country );

			// If this country has no states, remove the requirement of a card_state.
			if ( empty( $states ) ) {
				unset( $required_fields['card_state'] );
			}
		}
	}

	// Filter & return
	return (array) apply_filters( 'edd_purchase_form_required_fields', $required_fields );
}

/**
 * Purchase Form Validate Logged In User
 *
 * @access      private
 * @since       1.0
 * @return      array
 */
function edd_purchase_form_validate_logged_in_user() {
	global $user_ID;

	// Start empty array to collect valid user data
	$valid_user_data = array(
		'user_id' => -1
	);

	// Verify there is a user_ID
	if ( $user_ID > 0 ) {
		// Get the logged in user data
		$user_data = get_userdata( $user_ID );

		$fields = edd_purchase_form_required_fields();

		// Loop through required fields and show error messages
		foreach ( $fields as $field_name => $value ) {
			if ( empty( $_POST[ $field_name ] ) && ! empty( $value['error_id'] ) && ! empty( $value['error_message'] ) ) {
				edd_set_error( $value['error_id'], $value['error_message'] );
			}
		}

		// Verify data
		if ( $user_data ) {
			// Collected logged in user data
			$valid_user_data = array(
				'user_id'    => $user_ID,
				'user_email' => isset( $_POST['edd_email'] ) ? sanitize_email( $_POST['edd_email'] ) : $user_data->user_email,
				'user_first' => isset( $_POST['edd_first'] ) && ! empty( $_POST['edd_first'] ) ? sanitize_text_field( $_POST['edd_first'] ) : $user_data->first_name,
				'user_last'  => isset( $_POST['edd_last'] ) && ! empty( $_POST['edd_last']  ) ? sanitize_text_field( $_POST['edd_last']  ) : $user_data->last_name,
			);

			if ( ! is_email( $valid_user_data['user_email'] ) ) {
				edd_set_error( 'email_invalid', __( 'Invalid email', 'easy-digital-downloads' ) );
			}

		} else {
			// Set invalid user error
			edd_set_error( 'invalid_user', __( 'The user information is invalid', 'easy-digital-downloads' ) );
		}
	}

	// Return user data
	return $valid_user_data;
}

/**
 * Purchase Form Validate New User
 *
 * @access      private
 * @since       1.0.8.1
 * @return      array
 */
function edd_purchase_form_validate_new_user() {
	$registering_new_user = false;

	/** Sanitize **************************************************************/

	// Sanitize first name
	$user_first = isset( $_POST['edd_first'] )
		? sanitize_text_field( $_POST['edd_first'] )
		: '';

	// Sanitize last name
	$user_last = isset( $_POST['edd_last'] )
		? sanitize_text_field( $_POST['edd_last'] )
		: '';

	// Sanitize user login.
	$user_login = isset( $_POST['edd_user_login'] )
		? sanitize_user( $_POST['edd_user_login'] )
		: false;

	// Sanitize email address (allowed formatting only)
	$user_email   = isset( $_POST['edd_email'] )
		? sanitize_email( $_POST['edd_email'] )
		: false;

	// Trim front/back whitespace from password (don't alter characters)
	$user_pass    = isset( $_POST['edd_user_pass'] )
		? trim( $_POST['edd_user_pass'] )
		: false;

	// Trim front/back whitespace from password (don't alter characters)
	$pass_confirm = isset( $_POST['edd_user_pass_confirm'] )
		? trim( $_POST['edd_user_pass_confirm'] )
		: false;

	/** Required Fields *******************************************************/

	// Get required fields to loop through
	$fields = edd_purchase_form_required_fields();

	// Loop through required fields and provide error messages if missing
	foreach ( $fields as $field_name => $value ) {
		if ( empty( $_POST[ $field_name ] ) && ! empty( $value['error_id'] ) && ! empty( $value['error_message'] ) ) {
			edd_set_error( $value['error_id'], $value['error_message'] );
		}
	}

	/** Setup Userdata ********************************************************/

	// Start an empty array to collect valid user data.
	$valid_user_data = array(
		'user_id'    => 0,
		'user_first' => $user_first,
		'user_last'  => $user_last
	);

	/** Check Login ***********************************************************/

	// Check if we have a username to register
	if ( ! empty( $user_login ) && strlen( $user_login ) > 0 ) {
		$registering_new_user = true;

		// Error if username already exists.
		if ( username_exists( $user_login ) ) {
			edd_set_error( 'username_unavailable', __( 'Username already exists', 'easy-digital-downloads' ) );

		// Error if username is not valid
		} elseif ( ! edd_validate_username( $user_login ) ) {
			is_multisite()
				? edd_set_error( 'username_invalid', __( 'Invalid username. Only lowercase letters (a-z) and numbers are allowed', 'easy-digital-downloads' ) )
				: edd_set_error( 'username_invalid', __( 'Invalid username',                                                       'easy-digital-downloads' ) );

		// Add login to valid user data
		} else {
			// All the checks have run and it's good to go.
			$valid_user_data['user_login'] = $user_login;
		}

	// Error if users are required to register and no login was provided
	} elseif ( edd_no_guest_checkout() ) {
		edd_set_error( 'registration_required', __( 'You must register or login to complete your purchase', 'easy-digital-downloads' ) );
	}

	/** Check Email ***********************************************************/

	// Check if we have an email to verify
	if ( ! empty( $user_email ) && strlen( $user_email ) > 0 ) {

		// Error if invalid email address
		if ( ! is_email( $user_email ) ) {
			edd_set_error( 'email_invalid', __( 'Invalid email', 'easy-digital-downloads' ) );

		// Email address is unsafe (multisite only)
		} elseif ( is_multisite() && is_email_address_unsafe( $user_email ) ) {
			edd_set_error( 'email_unsafe', __( 'You cannot use that email address to signup at this time.', 'easy-digital-downloads' ) );
		} elseif ( true === $registering_new_user ) {
			// Check if email exists.
			$customers = edd_get_customers(
				array(
					'email'           => $user_email,
					'user_id__not_in' => array( null ),
				)
			);
			if ( email_exists( $user_email ) || ! empty( $customers ) ) {
				edd_set_error( 'email_used', __( 'Email already used. Login or use a different email to complete your purchase.', 'easy-digital-downloads' ) );
			} else {
				$valid_user_data['user_email'] = $user_email;
			}
		} else {
			// Add email to valid user data.
			$valid_user_data['user_email'] = $user_email;
		}

	// Error if no email address was provided
	} else {
		// No email.
		edd_set_error( 'email_empty', __( 'Enter an email', 'easy-digital-downloads' ) );
	}

	/** Check Password ********************************************************/

	// Check password
	if ( ! empty( $user_pass ) && ! empty( $pass_confirm ) ) {

		// Error if passwords do not match
		if ( 0 !== strcmp( $user_pass, $pass_confirm ) ) {
			edd_set_error( 'password_mismatch', __( 'Passwords do not match', 'easy-digital-downloads' ) );

		// Add password to valid user data
		} else {
			// All is good to go.
			$valid_user_data['user_pass'] = $user_pass;
		}

	// Error if no password when signing up
	} elseif ( true === $registering_new_user ) {
		if ( empty( $user_pass ) ) {
			edd_set_error( 'password_empty',     __( 'Enter a password', 'easy-digital-downloads' ) );
		} elseif ( empty( $pass_confirm ) ) {
			edd_set_error( 'confirmation_empty', __( 'Confirm your password', 'easy-digital-downloads' ) );
		}
	}

	// Cast as array and return
	return (array) $valid_user_data;
}

/**
 * Purchase Form Validate User Login
 *
 * @access      private
 * @since       1.0.8.1
 * @return      array
 */
function edd_purchase_form_validate_user_login() {

	// Start an array to collect valid user data.
	$valid_user_data = array(
		'user_id' => 0,
	);

	$user_login = ! empty( $_POST['edd_user_login'] ) ? sanitize_text_field( $_POST['edd_user_login'] ) : '';
	$user_pass  = ! empty( $_POST['edd_user_pass'] ) ? $_POST['edd_user_pass'] : '';

	// Username.
	if ( empty( $user_login ) && edd_no_guest_checkout() ) {
		edd_set_error( 'must_log_in', __( 'You must log in or register to complete your purchase', 'easy-digital-downloads' ) );
		return $valid_user_data;
	}

	$user = edd_log_user_in( 0, $user_login, $user_pass, false );

	if ( ! $user instanceof WP_User ) {
		return $valid_user_data;
	}

	// Populate the valid user data array.
	return array(
		'user_id'    => $user->ID,
		'user_login' => $user->user_login,
		'user_email' => $user->user_email,
		'user_first' => $user->first_name,
		'user_last'  => $user->last_name,
		'user_pass'  => $user_pass,
	);
}

/**
 * Purchase Form Validate Guest User
 *
 * @access  private
 * @since  1.0.8.1
 * @return  array
 */
function edd_purchase_form_validate_guest_user() {

	// Start an array to collect valid user data
	$valid_user_data = array(
		'user_id' => 0
	);

	// Show error message if user must be logged in
	if ( edd_logged_in_only() ) {
		edd_set_error( 'logged_in_only', __( 'You must be logged into an account to purchase', 'easy-digital-downloads' ) );
	}

	// Get the guest email
	$guest_email = isset( $_POST['edd_email'] )
		? sanitize_email( $_POST['edd_email'] )
		: false;

	// Check email
	if ( ! empty( $guest_email ) && strlen( $guest_email ) > 0 ) {

		// Invalid email
		if ( ! is_email( $guest_email ) ) {
			edd_set_error( 'email_invalid', __( 'Invalid email', 'easy-digital-downloads' ) );

		// Email address is unsafe (multisite only)
		} elseif ( is_multisite() && is_email_address_unsafe( $guest_email ) ) {
			edd_set_error( 'email_unsafe', __( 'You cannot use that email address at this time.', 'easy-digital-downloads' ) );

		// All is good to go
		} else {
			$valid_user_data['user_email'] = $guest_email;
		}

	// No email
	} else {
		edd_set_error( 'email_empty', __( 'Enter an email', 'easy-digital-downloads' ) );
	}

	// Get fields
	$fields = edd_purchase_form_required_fields();

	// Loop through required fields and show error messages
	foreach ( $fields as $field_name => $value ) {
		if ( empty( $_POST[ $field_name ] ) && ! empty( $value['error_id'] ) && ! empty( $value['error_message'] ) ) {
			edd_set_error( $value['error_id'], $value['error_message'] );
		}
	}

	return (array) $valid_user_data;
}

/**
 * Register And Login New User.
 *
 * @since 1.0.8.1
 *
 * @param array $user_data The data provided by the checkout page's registration form.
 * @return integer
 */
function edd_register_and_login_new_user( $user_data = array() ) {

	// Verify the array.
	if ( empty( $user_data ) ) {
		return -1;
	}

	// Bail if errors
	if ( edd_get_errors() ) {
		return -1;
	}

	$user_args = apply_filters(
		'edd_insert_user_args',
		array(
			'user_login'      => isset( $user_data['user_login'] ) ? $user_data['user_login'] : '',
			'user_pass'       => isset( $user_data['user_pass'] ) ? $user_data['user_pass'] : '',
			'user_email'      => isset( $user_data['user_email'] ) ? $user_data['user_email'] : '',
			'first_name'      => isset( $user_data['user_first'] ) ? $user_data['user_first'] : '',
			'last_name'       => isset( $user_data['user_last'] ) ? $user_data['user_last'] : '',
			'user_registered' => date( 'Y-m-d H:i:s' ),
			'role'            => get_option( 'default_role' ),
		),
		$user_data
	);

	// Insert new user.
	$user_id = wp_insert_user( $user_args );

	// Validate inserted user.
	if ( is_wp_error( $user_id ) ) {
		return -1;
	}

	// Allow themes and plugins to filter the user data.
	$user_data = apply_filters( 'edd_insert_user_data', $user_data, $user_args );

	// Allow themes and plugins to hook.
	do_action( 'edd_insert_user', $user_id, $user_data );

	// Login new user.
	$user = edd_log_user_in( $user_id, $user_data['user_login'], $user_data['user_pass'] );

	// If we have errors after trying to use wp_signon, return -1.
	if ( edd_get_errors() ) {
		return -1;
	}

	// Return user id.
	return $user->ID;
}

/**
 * Get Purchase Form User
 *
 * @since 1.0.8.1
 * @since 3.0 Remove `update_user_meta()` call to update the user's address
 *            as it is done later on in the order flow where a customer ID
 *            is available.
 *
 * @param array $valid_data
 * @access  private
 * @since  1.0.8.1
 *
 * @param   array $valid_data The validated data from the checkout form validation.
 * @return  array
 */
function edd_get_purchase_form_user( $valid_data = array(), $is_ajax = null ) {

	// Default variables
	$user    = false;
	$is_ajax = ( null === $is_ajax ) ? edd_doing_ajax() : $is_ajax;

	// Bail if during the ajax submission (check for errors only)
	if ( $is_ajax ) {
		return true;

	// Set the valid user as the logged in collected data
	} elseif ( is_user_logged_in() ) {
		$user = $valid_data['logged_in_user'];

	// New user registration
	} elseif ( true === $valid_data['need_new_user'] || true === $valid_data['need_user_login'] ) {
		// This ensures $_COOKIE is available without a new HTTP request.
		add_action( 'set_logged_in_cookie', 'edd_set_logged_in_cookie' );

		if ( true === $valid_data['need_new_user'] ) {

			// Set user
			$user = $valid_data['new_user_data'];

			// Register and login new user.
			$user['user_id'] = edd_register_and_login_new_user( $user );

		// User login
		} elseif ( true === $valid_data['need_user_login'] ) {
			/*
			 * The login form is now processed in the edd_process_purchase_login() function.
			 * This is still here for backwards compatibility.
			 * This also allows the old login process to still work if a user removes the
			 * checkout login submit button.
			 *
			 * This also ensures that the customer is logged in correctly if they click "Purchase"
			 * instead of submitting the login form, meaning the customer is logged in during the purchase process.
			 */

			// Set user.
			$user = $valid_data['login_user_data'];

			// Login user.
			if ( empty( $user ) || -1 === $user['user_id'] ) {
				edd_set_error( 'invalid_user', __( 'The user information is invalid', 'easy-digital-downloads' ) );
				return false;
			} else {
				edd_log_user_in( $user['user_id'], $user['user_login'], $user['user_pass'] );
			}
		}

		remove_action( 'set_logged_in_cookie', 'edd_set_logged_in_cookie' );
	}

	// Check guest checkout
	if ( empty( $user ) && ( false === edd_no_guest_checkout() ) ) {
		$user = $valid_data['guest_user_data'];
	}

	// Bail if no user.
	if ( empty( $user ) ) {
		return false;
	}

	// Get user first name.
	if ( ! isset( $user['user_first'] ) || strlen( trim( $user['user_first'] ) ) < 1 ) {
		$user['user_first'] = isset( $_POST['edd_first'] )
			? strip_tags( trim( $_POST['edd_first'] ) )
			: '';
	}

	// Get user last name.
	if ( ! isset( $user['user_last'] ) || strlen( trim( $user['user_last'] ) ) < 1 ) {
		$user['user_last'] = isset( $_POST['edd_last'] )
			? strip_tags( trim( $_POST['edd_last'] ) )
			: '';
	}

	// Get the user's billing address details.
	$user['address'] = array();
	$user['address']['line1']   = ! empty( $_POST['card_address']    ) ? sanitize_text_field( $_POST['card_address']    ) : '';
	$user['address']['line2']   = ! empty( $_POST['card_address_2']  ) ? sanitize_text_field( $_POST['card_address_2']  ) : '';
	$user['address']['city']    = ! empty( $_POST['card_city']       ) ? sanitize_text_field( $_POST['card_city']       ) : '';
	$user['address']['state']   = ! empty( $_POST['card_state']      ) ? sanitize_text_field( $_POST['card_state']      ) : '';
	$user['address']['country'] = ! empty( $_POST['billing_country'] ) ? sanitize_text_field( $_POST['billing_country'] ) : '';
	$user['address']['zip']     = ! empty( $_POST['card_zip']        ) ? sanitize_text_field( $_POST['card_zip']        ) : '';

	// Country will always be set if address fields are present
	if ( empty( $user['address']['country'] ) ) {
		$user['address'] = false;
	}

	// Return valid user.
	return $user;
}

/**
 * Sets the $_COOKIE global when a logged in cookie is available.
 *
 * We need the global to be immediately available so calls to wp_create_nonce()
 * within the same session will use the newly available data.
 *
 * @since 2.11
 *
 * @link https://wordpress.stackexchange.com/a/184055
 *
 * @param string $logged_in_cookie The logged-in cookie value.
 */
function edd_set_logged_in_cookie( $logged_in_cookie ) {
	$_COOKIE[ LOGGED_IN_COOKIE ] = $logged_in_cookie;
}

/**
 * Validates the credit card info
 *
 * @access  private
 * @since  1.4.4
 * @return  array
 */
function edd_purchase_form_validate_cc() {
	$card_data = edd_get_purchase_cc_info();

	// Validate the card zip
	if ( ! empty( $card_data['card_zip'] ) && edd_get_cart_total() > 0.00 ) {
		if ( ! edd_purchase_form_validate_cc_zip( $card_data['card_zip'], $card_data['card_country'] ) ) {
			edd_set_error( 'invalid_cc_zip', __( 'The zip / postal code you entered for your billing address is invalid', 'easy-digital-downloads' ) );
		}
	}

	// This should validate card numbers at some point too
	return $card_data;
}

/**
 * Get Credit Card Info
 *
 * @access  private
 * @since  1.4.4
 * @return  array
 */
function edd_get_purchase_cc_info() {
	$cc_info = array();
	$cc_info['card_name']      = isset( $_POST['card_name'] )       ? sanitize_text_field( $_POST['card_name'] )       : '';
	$cc_info['card_number']    = isset( $_POST['card_number'] )     ? sanitize_text_field( $_POST['card_number'] )     : '';
	$cc_info['card_cvc']       = isset( $_POST['card_cvc'] )        ? sanitize_text_field( $_POST['card_cvc'] )        : '';
	$cc_info['card_exp_month'] = isset( $_POST['card_exp_month'] )  ? sanitize_text_field( $_POST['card_exp_month'] )  : '';
	$cc_info['card_exp_year']  = isset( $_POST['card_exp_year'] )   ? sanitize_text_field( $_POST['card_exp_year'] )   : '';
	$cc_info['card_address']   = isset( $_POST['card_address'] )    ? sanitize_text_field( $_POST['card_address'] )    : '';
	$cc_info['card_address_2'] = isset( $_POST['card_address_2'] )  ? sanitize_text_field( $_POST['card_address_2'] )  : '';
	$cc_info['card_city']      = isset( $_POST['card_city'] )       ? sanitize_text_field( $_POST['card_city'] )       : '';
	$cc_info['card_state']     = isset( $_POST['card_state'] )      ? sanitize_text_field( $_POST['card_state'] )      : '';
	$cc_info['card_country']   = isset( $_POST['billing_country'] ) ? sanitize_text_field( $_POST['billing_country'] ) : '';
	$cc_info['card_zip']       = isset( $_POST['card_zip'] )        ? sanitize_text_field( $_POST['card_zip'] )        : '';

	// Return cc info
	return $cc_info;
}

/**
 * Validate zip code based on country code
 *
 * @since  1.4.4
 *
 * @param int     $zip
 * @param string  $country_code
 *
 * @return bool|mixed|void
 */
function edd_purchase_form_validate_cc_zip( $zip = 0, $country_code = '' ) {
	$ret = false;

	if ( empty( $zip ) || empty( $country_code ) ) {
		return $ret;
	}

	$country_code = strtoupper( $country_code );

	$zip_regex = array(
		"AD" => "AD\d{3}",
		"AM" => "(37)?\d{4}",
		"AR" => "^([A-Z]{1}\d{4}[A-Z]{3}|[A-Z]{1}\d{4}|\d{4})$",
		"AS" => "96799",
		"AT" => "\d{4}",
		"AU" => "^(0[289][0-9]{2})|([1345689][0-9]{3})|(2[0-8][0-9]{2})|(290[0-9])|(291[0-4])|(7[0-4][0-9]{2})|(7[8-9][0-9]{2})$",
		"AX" => "22\d{3}",
		"AZ" => "\d{4}",
		"BA" => "\d{5}",
		"BB" => "(BB\d{5})?",
		"BD" => "\d{4}",
		"BE" => "^[1-9]{1}[0-9]{3}$",
		"BG" => "\d{4}",
		"BH" => "((1[0-2]|[2-9])\d{2})?",
		"BM" => "[A-Z]{2}[ ]?[A-Z0-9]{2}",
		"BN" => "[A-Z]{2}[ ]?\d{4}",
		"BR" => "\d{5}[\-]?\d{3}",
		"BY" => "\d{6}",
		"CA" => "^[ABCEGHJKLMNPRSTVXY]{1}\d{1}[A-Z]{1} *\d{1}[A-Z]{1}\d{1}$",
		"CC" => "6799",
		"CH" => "^[1-9][0-9][0-9][0-9]$",
		"CK" => "\d{4}",
		"CL" => "\d{7}",
		"CN" => "\d{6}",
		"CR" => "\d{4,5}|\d{3}-\d{4}",
		"CS" => "\d{5}",
		"CV" => "\d{4}",
		"CX" => "6798",
		"CY" => "\d{4}",
		"CZ" => "\d{3}[ ]?\d{2}",
		"DE" => "\b((?:0[1-46-9]\d{3})|(?:[1-357-9]\d{4})|(?:[4][0-24-9]\d{3})|(?:[6][013-9]\d{3}))\b",
		"DK" => "^([D-d][K-k])?( |-)?[1-9]{1}[0-9]{3}$",
		"DO" => "\d{5}",
		"DZ" => "\d{5}",
		"EC" => "([A-Z]\d{4}[A-Z]|(?:[A-Z]{2})?\d{6})?",
		"EE" => "\d{5}",
		"EG" => "\d{5}",
		"ES" => "^([1-9]{2}|[0-9][1-9]|[1-9][0-9])[0-9]{3}$",
		"ET" => "\d{4}",
		"FI" => "\d{5}",
		"FK" => "FIQQ 1ZZ",
		"FM" => "(9694[1-4])([ \-]\d{4})?",
		"FO" => "\d{3}",
		"FR" => "^(F-)?((2[A|B])|[0-9]{2})[0-9]{3}$",
		"GE" => "\d{4}",
		"GF" => "9[78]3\d{2}",
		"GL" => "39\d{2}",
		"GN" => "\d{3}",
		"GP" => "9[78][01]\d{2}",
		"GR" => "\d{3}[ ]?\d{2}",
		"GS" => "SIQQ 1ZZ",
		"GT" => "\d{5}",
		"GU" => "969[123]\d([ \-]\d{4})?",
		"GW" => "\d{4}",
		"HM" => "\d{4}",
		"HN" => "(?:\d{5})?",
		"HR" => "\d{5}",
		"HT" => "\d{4}",
		"HU" => "\d{4}",
		"ID" => "\d{5}",
		"IE" => "((D|DUBLIN)?([1-9]|6[wW]|1[0-8]|2[024]))?",
		"IL" => "\d{5}",
		"IN"=> "^[1-9][0-9][0-9][0-9][0-9][0-9]$", //india
		"IO" => "BBND 1ZZ",
		"IQ" => "\d{5}",
		"IS" => "\d{3}",
		"IT" => "^(V-|I-)?[0-9]{5}$",
		"JO" => "\d{5}",
		"JP" => "\d{3}-\d{4}",
		"KE" => "\d{5}",
		"KG" => "\d{6}",
		"KH" => "\d{5}",
		"KR" => "\d{5}",
		"KW" => "\d{5}",
		"KZ" => "\d{6}",
		"LA" => "\d{5}",
		"LB" => "(\d{4}([ ]?\d{4})?)?",
		"LI" => "(948[5-9])|(949[0-7])",
		"LK" => "\d{5}",
		"LR" => "\d{4}",
		"LS" => "\d{3}",
		"LT" => "\d{5}",
		"LU" => "\d{4}",
		"LV" => "\d{4}",
		"MA" => "\d{5}",
		"MC" => "980\d{2}",
		"MD" => "\d{4}",
		"ME" => "8\d{4}",
		"MG" => "\d{3}",
		"MH" => "969[67]\d([ \-]\d{4})?",
		"MK" => "\d{4}",
		"MN" => "\d{5}",
		"MP" => "9695[012]([ \-]\d{4})?",
		"MQ" => "9[78]2\d{2}",
		"MT" => "[A-Z]{3}[ ]?\d{2,4}",
		"MU" => "(\d{3}[A-Z]{2}\d{3})?",
		"MV" => "\d{5}",
		"MX" => "\d{5}",
		"MY" => "\d{5}",
		"NC" => "988\d{2}",
		"NE" => "\d{4}",
		"NF" => "2899",
		"NG" => "(\d{6})?",
		"NI" => "((\d{4}-)?\d{3}-\d{3}(-\d{1})?)?",
		"NL" => "^[1-9][0-9]{3}\s?([a-zA-Z]{2})?$",
		"NO" => "\d{4}",
		"NP" => "\d{5}",
		"NZ" => "\d{4}",
		"OM" => "(PC )?\d{3}",
		"PF" => "987\d{2}",
		"PG" => "\d{3}",
		"PH" => "\d{4}",
		"PK" => "\d{5}",
		"PL" => "\d{2}-\d{3}",
		"PM" => "9[78]5\d{2}",
		"PN" => "PCRN 1ZZ",
		"PR" => "00[679]\d{2}([ \-]\d{4})?",
		"PT" => "\d{4}([\-]\d{3})?",
		"PW" => "96940",
		"PY" => "\d{4}",
		"RE" => "9[78]4\d{2}",
		"RO" => "\d{6}",
		"RS" => "\d{5}",
		"RU" => "\d{6}",
		"SA" => "\d{5}",
		"SE" => "^(s-|S-){0,1}[0-9]{3}\s?[0-9]{2}$",
		"SG" => "\d{6}",
		"SH" => "(ASCN|STHL) 1ZZ",
		"SI" => "\d{4}",
		"SJ" => "\d{4}",
		"SK" => "\d{3}[ ]?\d{2}",
		"SM" => "4789\d",
		"SN" => "\d{5}",
		"SO" => "\d{5}",
		"SZ" => "[HLMS]\d{3}",
		"TC" => "TKCA 1ZZ",
		"TH" => "\d{5}",
		"TJ" => "\d{6}",
		"TM" => "\d{6}",
		"TN" => "\d{4}",
		"TR" => "\d{5}",
		"TW" => "\d{3}(\d{2})?",
		"UA" => "\d{5}",
		"UK" => "^(GIR|[A-Z]\d[A-Z\d]??|[A-Z]{2}\d[A-Z\d]??)[ ]??(\d[A-Z]{2})$",
		"US" => "^\d{5}([\-]?\d{4})?$",
		"UY" => "\d{5}",
		"UZ" => "\d{6}",
		"VA" => "00120",
		"VE" => "\d{4}",
		"VI" => "008(([0-4]\d)|(5[01]))([ \-]\d{4})?",
		"WF" => "986\d{2}",
		"YT" => "976\d{2}",
		"YU" => "\d{5}",
		"ZA" => "\d{4}",
		"ZM" => "\d{5}"
	);

	if ( ! isset ( $zip_regex[ $country_code ] ) || preg_match( "/" . $zip_regex[ $country_code ] . "/i", $zip ) ) {
		$ret = true;
	}

	return apply_filters( 'edd_is_zip_valid', $ret, $zip, $country_code );
}

/**
 * Check the purchase to ensure a banned email is not allowed through
 *
 * @since       2.0
 * @return      void
 */
function edd_check_purchase_email( $valid_data, $posted ) {

	$banned = edd_get_banned_emails();

	if ( empty( $banned ) ) {
		return;
	}

	$user_emails = array( $posted['edd_email'] );
	if ( is_user_logged_in() ) {

		// The user is logged in, check that their account email is not banned
		$user_data     = get_userdata( get_current_user_id() );
		$user_emails[] = $user_data->user_email;

	} elseif ( isset( $posted['edd-purchase-var'] ) && $posted['edd-purchase-var'] == 'needs-to-login' ) {

		// The user is logging in, check that their email is not banned
		if ( $user_data = get_user_by( 'login', $posted['edd_user_login'] ) ) {
			$user_emails[] = $user_data->user_email;
		}

	}

	foreach ( $user_emails as $email ) {

		// Set an error and give the customer a general error (don't alert
		// them that they were banned)
		if ( edd_is_email_banned( $email ) ) {
			edd_set_error( 'email_banned', __( 'An internal error has occurred, please try again or contact support.', 'easy-digital-downloads' ) );
			break;
		}
	}

}
add_action( 'edd_checkout_error_checks', 'edd_check_purchase_email', 10, 2 );

/**
 * Checks the length of the user's email address.
 *
 * @since 3.1.0.5
 * @param array $valid_data  The array of validated data.
 * @param array $posted_data The array of posted data.
 * @return void
 */
function edd_check_purchase_email_length( $valid_data, $posted_data ) {
	// Customer emails are limited to 100 characters.
	if ( ! empty( $posted_data['edd_email'] ) && strlen( $posted_data['edd_email'] ) > 100 ) {
		edd_set_error( 'email_length', __( 'Your email address must be shorter than 100 characters.', 'easy-digital-downloads' ) );
	}
}
add_action( 'edd_checkout_error_checks', 'edd_check_purchase_email_length', 10, 2 );

/**
 * Process a straight-to-gateway purchase
 *
 * @since 1.7
 * @return void
 */
function edd_process_straight_to_gateway( $data ) {

	$download_id = $data['download_id'];
	$options     = isset( $data['edd_options'] ) ? $data['edd_options'] : array();
	$quantity    = isset( $data['edd_download_quantity'] ) ? $data['edd_download_quantity'] : 1;

	if ( empty( $download_id ) || ! edd_get_download( $download_id ) ) {
		return;
	}

	$purchase_data    = edd_build_straight_to_gateway_data( $download_id, $options, $quantity );
	$enabled_gateways = edd_get_enabled_payment_gateways();

	if ( ! array_key_exists( $purchase_data['gateway'], $enabled_gateways ) ) {
		foreach ( $purchase_data['downloads'] as $download ) {
			$options = isset( $download['options'] ) ? $download['options'] : array();

			$options['quantity'] = isset( $download['quantity'] ) ? $download['quantity'] : 1;
			edd_add_to_cart( $download['id'], $options );
		}

		edd_set_error( 'edd-straight-to-gateway-error', __( 'There was an error completing your purchase. Please try again.', 'easy-digital-downloads' ) );
		edd_redirect( edd_get_checkout_uri() );
	}

	edd_set_purchase_session( $purchase_data );
	edd_send_to_gateway( $purchase_data['gateway'], $purchase_data );
}
add_action( 'edd_straight_to_gateway', 'edd_process_straight_to_gateway' );
