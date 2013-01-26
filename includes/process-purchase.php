<?php
/**
 * Process Purchase
 *
 * @package     Easy Digital Downloads
 * @subpackage  Process Purchase
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Process Purchase Form
 *
 * Handles the purchase form process.
 *
 * @access      private
 * @since       1.0
 * @version     1.0.8.1
 * @return      void
*/
function edd_process_purchase_form() {
	// Verify the nonce for this action
	if ( ! isset( $_POST['edd-nonce'] ) || ! wp_verify_nonce( $_POST['edd-nonce'], 'edd-purchase-nonce' ) ) {
		edd_set_error( 'nonce_failed', __( 'Security check failed. Please refresh the page and try again.', 'edd') );

	// Make sure the cart isn't empty
	} else if ( ! edd_get_cart_contents() ) {
		edd_set_error( 'empty_cart', __( 'Your cart is empty.', 'edd') );

	} else {

		// Validate the form $_POST data
		$valid_data = edd_purchase_form_validate_fields();

		// Allow themes and plugins to hoook to errors
		do_action( 'edd_checkout_error_checks', $valid_data, $_POST );
	}

	$is_ajax = ! empty( $_POST['action'] ) && ( $_POST['action'] == 'edd_process_checkout' );

	if ( edd_get_errors() || !$user = edd_get_purchase_form_user( $valid_data ) ) {
		if ( $is_ajax ) {
			edd_print_errors();
			exit;
		} else {
			return false;
		}
	}

	if ( $is_ajax ) {
		echo 'success';
		exit;
	}

	// Setup user information
	$user_info = array(
		'id' 		=> $user['user_id'],
		'email' 	=> $user['user_email'],
		'first_name'=> $user['user_first'],
		'last_name' => $user['user_last'],
		'discount' 	=> $valid_data['discount']
	);

	// Setup purchase information
	$purchase_data = array(
		'downloads' 	=> edd_get_cart_contents(),
		'subtotal'		=> edd_get_cart_subtotal(),		 	// Amount before taxes and discounts
		'discount'		=> edd_get_cart_discounted_amount(),// Discounted amount
		'tax'			=> edd_get_cart_tax(), 				// Taxed amount
		'price' 		=> edd_get_cart_total(), 			// Amount after taxes
		'purchase_key' 	=> strtolower( md5( uniqid() ) ), 	// Random key
		'user_email' 	=> $user['user_email'],
		'date' 			=> date( 'Y-m-d H:i:s' ),
		'user_info' 	=> $user_info,
		'post_data' 	=> $_POST,
		'cart_details' 	=> edd_get_cart_content_details(),
		'gateway' 		=> $valid_data['gateway'],
		'card_info' 	=> $valid_data['cc_info']
	);

	// Add the user data for hooks
	$valid_data['user'] = $user;

	// Allow themes and plugins to hook before the gateway
	do_action( 'edd_checkout_before_gateway', $_POST, $user_info, $valid_data );

	// Allow the purchase data to be modified before it is sent to the gateway
	$purchase_data = apply_filters(
		'edd_purchase_data_before_gateway',
		$purchase_data,
		$valid_data
	);

	// If the total amount in the cart is 0, send to the manaul gateway. This emulates a free download purchase
	if ( $purchase_data['price'] <= 0 ) {
		// Revert to manual
		$valid_data['gateway'] = 'manual';
	}

	// Used for showing download links to non logged-in users after purchase, and for other plugins needing purchase data.
	edd_set_purchase_session( $purchase_data );

	// Send info to the gateway for payment processing
	edd_send_to_gateway( $valid_data['gateway'], $purchase_data );
	exit;
}
add_action( 'edd_purchase', 'edd_process_purchase_form' );
add_action( 'wp_ajax_edd_process_checkout', 'edd_process_purchase_form' );
add_action( 'wp_ajax_nopriv_edd_process_checkout', 'edd_process_purchase_form' );

/**
 * Purchase Form Validate Fields
 *
 * @access      private
 * @since       1.0.8.1
 * @return      bool|array
*/
function edd_purchase_form_validate_fields() {
	global $edd_options;

	// Check if there is $_POST
	if ( empty( $_POST ) ) return false;

	// Start an array to collect valid data
	$valid_data = array(
		'gateway'				=> '',		 // Gateway fallback
		'discount'				=> 'none',	 // Set default discount
		'need_new_user'			=> false,	 // New user flag
		'need_user_login'		=> false,	 // Login user flag
		'logged_user_data'		=> array(),  // Logged user collected data
		'new_user_data'			=> array(),	 // New user collected data
		'login_user_data'		=> array(),	 // Login user collected data
		'guest_user_data'		=> array(),	 // Guest user collected data
		'cc_info'				=> array()	 // Credit card info
	);

	// Validate the gateway
	$valid_data['gateway'] = edd_purchase_form_validate_gateway();

	// Validate discounts
	$valid_data['discount'] = edd_purchase_form_validate_discounts();

	// Collect credit card info
	$valid_data['cc_info'] = edd_get_purchase_cc_info();

	// Validate agree to terms
	if ( isset( $edd_options['show_agree_to_terms'] ) )
		edd_purchase_form_validate_agree_to_terms();

	// Check if user is logged in
	if ( is_user_logged_in() ) {
		// Collect logged in user data
		$valid_data['logged_in_user'] = edd_purchase_form_validate_logged_in_user();

	} else if ( isset( $_POST['edd-purchase-var'] ) && $_POST['edd-purchase-var'] == 'needs-to-register' ) {

	   // Set new user registrarion as required
	  $valid_data['need_new_user'] = true;

	   // Validate new user data
	  $valid_data['new_user_data'] = edd_purchase_form_validate_new_user();

   // Check if login validation is needed
	} else if ( isset( $_POST['edd-purchase-var'] ) && $_POST['edd-purchase-var'] == 'needs-to-login' ) {

		// Set user login as required
		$valid_data['need_user_login'] = true;

		// Validate users login info
		$valid_data['login_user_data'] = edd_purchase_form_validate_user_login();
	} else {

		// Not registering or logging in, so setup guest user data
		$valid_data['guest_user_data'] = edd_purchase_form_validate_guest_user();

	}

	// Return collected data
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
	// Check if a gateway value is present
	if ( isset( $_POST['edd-gateway'] ) && trim( $_POST['edd-gateway'] ) != '' ) {
		// Clean gateway
		$gateway = strip_tags( $_POST['edd-gateway'] );
		// Verify if gateway is active
		if ( edd_is_gateway_active( $gateway ) ) {
			// Return active gateway
			return $gateway;
		} else if ( edd_get_cart_amount() <= 0 ) {
			return 'manual';
		} else {
			// Set invalid gateway error
			edd_set_error( 'invalid_gateway', __( 'The selected gateway is not active', 'edd' ) );
		}
	} else {
		// No gateway is present
		edd_set_error( 'empty_gateway', __( 'No gateway has been selected', 'edd' ) );
	}

	// Return empty
	return '';
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

	// Check for valid discount is present
	if ( ! empty( $_POST['edd-discount'] ) || $discounts !== false  ) {

		if( empty( $discounts ) ) {

			$discount = sanitize_text_field( $_POST['edd-discount'] );

		} else {

			// Use the discount stored in the cookies
			$discount = $discounts[0];

			// at some point this will support multiple discounts

		}

		$user = isset( $_POST['edd_user_login'] ) ? sanitize_text_field( $_POST['edd_user_login'] ) : sanitize_email( $_POST['edd_email'] );

		// Check if validates
		if (  edd_is_discount_valid( $discount, $user ) ) {
			// Return clean discount
			return $discount;
		} else {
			// Set invalid discount error
			edd_set_error( 'invalid_discount', __( 'The discount you entered is invalid', 'edd' ) );
		}
	}
	// Return default value
	return 'none';
}


/**
 * Purchase Form Validate Agree To Terms
 *
 * @access      private
 * @since       1.0.8.1
 * @return      void
*/
function edd_purchase_form_validate_agree_to_terms() {
	// Validate agree to terms
	if ( !isset( $_POST['edd_agree_to_terms'] ) || $_POST['edd_agree_to_terms'] != 1 ) {
		// User did not agree
		edd_set_error( 'agree_to_terms', apply_filters( 'edd_agree_to_terms_text', __( 'You must agree to the terms of use', 'edd' ) ) );
	}
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
		// Assume there will be errors
		'user_id' => -1
	);

	// Verify there is a user_ID
	if ( $user_ID > 0 ) {

		// Get the logged in user data
		$user_data = get_userdata( $user_ID );

		if( ! is_email( $_POST['edd_email'] ) ) {
			edd_set_error( 'invalid_email', __( 'Please enter a valid email address.', 'edd' ) );
		}

		if ( empty( $_POST['edd_first'] ) ) {
			edd_set_error( 'invalid_name', __( 'Please enter your first name.', 'edd' ) );
		}

		// Verify data
		if ( $user_data ) {
			// Collected logged in user data
			$valid_user_data = array(
				'user_id' 		=> $user_ID,
				'user_email' 	=> sanitize_email( $_POST['edd_email'] ),
				'user_first' 	=> sanitize_text_field( $_POST['edd_first'] ),
				'user_last' 	=> sanitize_text_field( $_POST['edd_last'] ),
			);
		} else {
			// Set invalid user error
			edd_set_error( 'invalid_user', __( 'The user information is invalid.', 'edd' ) );
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

	// Start an empty array to collect valid user data
	$valid_user_data = array(
		// Assume there will be errors
		'user_id' => -1,
		// Get first name
		'user_first' => isset( $_POST["edd_first"] ) ? strip_tags( trim( $_POST["edd_first"] ) ) : '',
		// Get last name
		'user_last' => isset( $_POST["edd_last"] ) ? strip_tags( trim( $_POST["edd_last"] ) ) : '',
	);

	// Check the new user's credentials against existing ones
	$user_login	  = isset( $_POST["edd_user_login"] ) ? trim( $_POST["edd_user_login"] ) : false;
	$user_email	  = isset( $_POST['edd_email'] ) ? trim( $_POST['edd_email'] ) : false;
	$user_pass	  = isset( $_POST["edd_user_pass"] ) ? trim( $_POST["edd_user_pass"] ) : false;
	$pass_confirm = isset( $_POST["edd_user_pass_confirm"] ) ? trim( $_POST["edd_user_pass_confirm"] ) : false;


	// Check if we have an username to register
	if( $user_login && strlen( $user_login ) > 0 ) {
		$registering_new_user = true;

		// We have an user name, check if it already exists
		if( username_exists( $user_login ) ) {
			// Username already registered
			edd_set_error( 'username_unavailable', __( 'Username already taken', 'edd' ) );
		// Check if it's valid
		} else if( ! edd_validate_username( $user_login ) ) {
		   // Invalid username
			if( is_multisite() )
				edd_set_error( 'username_invalid', __( 'Invalid username. Only lowercase letters (a-z) and numbers are allowed', 'edd' ) );
			else
				edd_set_error( 'username_invalid', __( 'Invalid username', 'edd' ) );

		} else {
			// All the checks have run and it's good to go
			$valid_user_data['user_login'] = $user_login;
		}
	} else {
		if ( edd_no_guest_checkout() ) {
			edd_set_error( 'registration_required', __( 'You must register or login to complete your purchase', 'edd' ) );
		}
	}

	// Check if we have an email to verify
	if ( $user_email && strlen( $user_email ) > 0 ) {
		// Validate email
		if ( !is_email( $user_email ) ) {
		   edd_set_error( 'email_invalid', __('Invalid email', 'edd') );
		// Check if email exists
		} else if ( email_exists( $user_email ) && $registering_new_user ) {
			edd_set_error( 'email_used', __('Email already used', 'edd') );
		} else {
		   // All the checks have run and it's good to go
			$valid_user_data['user_email'] = $user_email;
		}
	} else {
		// No email
		edd_set_error( 'email_empty', __('Enter an email', 'edd') );
	}

	// Check password
	if( $user_pass && $pass_confirm ) {
		// Verify confirmation matches
		if( $user_pass != $pass_confirm ) {
			// Passwords do not match
			edd_set_error( 'password_mismatch', __( 'Passwords don\'t match', 'edd' ) );
		} else {
			// All is good to go
			$valid_user_data['user_pass'] = $user_pass;
		}
	} else {
		// Password or confrimation missing
		if ( ! $user_pass && $registering_new_user ) {
			// The password is invalid
			edd_set_error( 'password_empty', __( 'Enter a password', 'edd' ) );
		} else if ( ! $pass_confirm && $registering_new_user ) {
			// Confirmation password is invalid
			edd_set_error( 'confirmation_empty', __( 'Enter the password confirmation', 'edd' ) );
		}
	}

	return $valid_user_data;
}

/**
 * Purchase Form Validate User Login
 *
 * @access      private
 * @since       1.0.8.1
 * @return      array
*/
function edd_purchase_form_validate_user_login() {
	// Start an array to collect valid user data
	$valid_user_data = array(
		// Assume there will be errors
		'user_id' => -1
	);

	// Username
	if ( !isset( $_POST['edd_user_login'] ) || $_POST['edd_user_login'] == '' ) {
		edd_set_error( 'must_log_in', __( 'You must login or register to complete your purchase', 'edd' ) );
		return $valid_user_data;
	}

	// Get the user by login
	$user_data = get_user_by( 'login', strip_tags( $_POST['edd_user_login'] ) );

	// Check if user exists
	if( $user_data ) {

		// Get password
		$user_pass = isset( $_POST["edd_user_pass"] ) ? $_POST["edd_user_pass"] : false;

		// Check user_pass
		if ( $user_pass ) {
			// Check if password is valid
			if ( ! wp_check_password( $user_pass, $user_data->user_pass, $user_data->ID ) ) {
				// Incorrect password
				edd_set_error( 'password_incorrect', __( 'The password you entered is incorrect', 'edd' ) );
			// All is correct
			} else {
				// Repopulate the valid user data array
				$valid_user_data = array(
					'user_id' => $user_data->ID,
					'user_login' => $user_data->user_login,
					'user_email' => $user_data->user_email,
					'user_first' => $user_data->first_name,
					'user_last' => $user_data->last_name,
					'user_pass' => $user_pass,
				);
			}
		} else {
			// Empty password
			edd_set_error( 'password_empty', __( 'Enter a password', 'edd' ) );
		}
	} else {
		//	no username
		edd_set_error( 'username_incorrect', __( 'The username you entered does not exist', 'edd' ) );
	}

	return $valid_user_data;

}


/**
 * Purchase Form Validate Guest User
 *
 * @access		private
 * @since		1.0.8.1
 * @return		array
*/
function edd_purchase_form_validate_guest_user() {
	// Start an array to collect valid user data
	$valid_user_data = array(
		// Set a default id for guests
		'user_id' => 0,
	);

	// Get the guest email
	$guest_email = isset( $_POST['edd_email'] ) ? $_POST['edd_email'] : false;

	// Check email
	if ( $guest_email && strlen( $guest_email ) > 0 ) {
		// Validate email
		if( !is_email( $guest_email ) ) {
			// Invalid email
			edd_set_error( 'email_invalid', __( 'Invalid email', 'edd' ) );
		} else {
			// All is good to go
			$valid_user_data['user_email'] = $guest_email;
		}
	} else {
		// No email
		edd_set_error( 'email_empty', __( 'Enter an email', 'edd' ) );
	}

	return $valid_user_data;
}


/**
 * Register And Login New User
 *
 * @param array $user_data
 *
 * @access		private
 * @since		1.0.8.1
 * @return		integer
*/
function edd_register_and_login_new_user( $user_data = array() ) {
	// Verify the array
	if ( empty( $user_data ) )
		return -1;

	$user_args = array(
		'user_login'      => $user_data['user_login'],
		'user_pass'       => $user_data['user_pass'],
		'user_email'      => $user_data['user_email'],
		'first_name'      => $user_data['user_first'],
		'last_name'       => $user_data['user_last'],
		'user_registered' => date('Y-m-d H:i:s'),
		'role'            => get_option( 'default_role' )
	);

	// Insert new user
	$user_id = wp_insert_user( apply_filters( 'edd_insert_user_args', $user_args ) );

	// Validate inserted user
	if( is_wp_error( $user_id ) )
		return -1;

	// Allow themes and plugins to hook
	do_action( 'edd_insert_user', $user_id );

	// Login new user
	edd_log_user_in( $user_id, $user_data['user_login'], $user_data['user_pass'] );

	// Return user id
	return $user_id;
}


/**
 * Get Purchase Form User
 *
 * @param array $valid_data
 *
 * @access		private
 * @since		1.0.8.1
 * @return		array
*/
function edd_get_purchase_form_user( $valid_data = array() ) {
	// Initialize user
	$user = false;

	// Check if user is logged in
	if( is_user_logged_in() ) {
		// Set the valid user as the logged in collected data
		$user = $valid_data['logged_in_user'];
	}
	// Otherwise check if we have to register or login users
	else if( $valid_data['need_new_user'] === true || $valid_data['need_user_login'] === true  ) {
		// New user registration
		if( $valid_data['need_new_user'] === true ) {
			// Set user
			$user = $valid_data['new_user_data'];
			// Register and login new user
			$user['user_id'] = edd_register_and_login_new_user( $user );
		// User login
		} else if( $valid_data['need_user_login'] === true ) {
			// Set user
			$user = $valid_data['login_user_data'];
			// Login user
			edd_log_user_in( $user['user_id'], $user['user_login'], $user['user_pass'] );
		}
	}

	// Check guest checkout
	if( false === $user && false === edd_no_guest_checkout() ) {
		// Set user
		$user = $valid_data['guest_user_data'];
	}

	// Verify we have an user
	if( false === $user || empty( $user ) ) {
		// Return false
		return false;
	}

	// Get user first name
	if( !isset( $user['user_first'] ) || strlen( trim( $user['user_first'] ) ) < 1 ) {
		$user['user_first'] = isset( $_POST["edd_first"] ) ? strip_tags( trim( $_POST["edd_first"] ) ) : '';
	}

	// Get user last name
	if( !isset( $user['user_last'] ) || strlen( trim( $user['user_last'] ) ) < 1 ) {
		$user['user_last'] = isset( $_POST["edd_last"] ) ? strip_tags( trim( $_POST["edd_last"] ) ) : '';
	}

	// Return valid user
	return $user;
}


/**
 * Get Credit Card Info
 *
 * @access		private
 * @since		1.2
 * @return		array
*/
function edd_get_purchase_cc_info() {

	$cc_info = array();
	$cc_info['card_name'] 		= isset( $_POST['card_name'] ) 		? sanitize_text_field( $_POST['card_name'] ) 		: '';
	$cc_info['card_number'] 	= isset( $_POST['card_number'] ) 	? sanitize_text_field( $_POST['card_number'] ) 		: '';
	$cc_info['card_cvc'] 		= isset( $_POST['card_cvc'] ) 		? sanitize_text_field( $_POST['card_cvc'] ) 		: '';
	$cc_info['card_exp_month'] 	= isset( $_POST['card_exp_month'] ) ? sanitize_text_field( $_POST['card_exp_month'] ) 	: '';
	$cc_info['card_exp_year'] 	= isset( $_POST['card_exp_year'] ) 	? sanitize_text_field( $_POST['card_exp_year'] ) 	: '';
	$cc_info['card_address'] 	= isset( $_POST['card_address'] ) 	? sanitize_text_field( $_POST['card_address'] ) 	: '';
	$cc_info['card_address_2'] 	= isset( $_POST['card_address_2'] ) ? sanitize_text_field( $_POST['card_address_2'] ) 	: '';
	$cc_info['card_city'] 		= isset( $_POST['card_city'] ) 		? sanitize_text_field( $_POST['card_city'] ) 		: '';
	$cc_info['card_country'] 	= isset( $_POST['billing_country'] )? sanitize_text_field( $_POST['billing_country'] ) 	: '';
	$cc_info['card_zip'] 		= isset( $_POST['card_zip'] )		? sanitize_text_field( $_POST['card_zip'] ) 		: '';

	switch( $cc_info['card_country'] ) :
		case 'US' :
			$cc_info['card_state'] = isset( $_POST['card_state_us'] )	? sanitize_text_field( $_POST['card_state_us'] ) 	: '';
			break;
		case 'CA' :
			$cc_info['card_state'] = isset( $_POST['card_state_ca'] )	? sanitize_text_field( $_POST['card_state_ca'] ) 	: '';
			break;
		default :
			$cc_info['card_state'] = isset( $_POST['card_state_other'] )? sanitize_text_field( $_POST['card_state_other'] ) : '';
			break;
	endswitch;

	// Return cc info
	return $cc_info;
}


/**
 * Send To Success Page
 *
 * Sends the user to the succes page.
 *
 * @param string $query_string
 * @access      public
 * @since       1.0
 * @return      void
*/
function edd_send_to_success_page( $query_string = null ) {
	global $edd_options;

	$redirect = get_permalink($edd_options['success_page']);

	if( $query_string )
		$redirect .= $query_string;

	wp_redirect( apply_filters('edd_success_page_redirect', $redirect, $_POST['edd-gateway'], $query_string) );
	exit;
}


/**
 * Send back to checkout.
 *
 * Used to redirect a user back to the purchase
 * page if there are errors present.
 *
 * @param array $args
 * @access public
 * @since  1.0
 * @return Void
 */
function edd_send_back_to_checkout( $args = array() ) {

	$redirect = edd_get_checkout_uri();

	if ( ! empty( $args ) ) {
		// Check for backward compatibility
		if ( is_string( $args ) )
			$args = str_replace( '?', '', $args );

		$args = wp_parse_args( $args );

		$redirect = add_query_arg( $args, $redirect );
	}

	wp_redirect( apply_filters( 'edd_send_back_to_checkout', $redirect, $args ) );
	exit;
}


/**
 * Get Success Page URL
 *
 * Gets the success page URL.
 *
 * @param string $query_string
 * @access      public
 * @since       1.0
 * @return      string
*/
function edd_get_success_page_url( $query_string = null ) {
	global $edd_options;

	$success_page = get_permalink($edd_options['success_page']);
	if($query_string)
		$success_page .= $query_string;

	return apply_filters( 'edd_success_page_url', $success_page );
}
