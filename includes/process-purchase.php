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
	global $edd_options;

	// no need to run on admin
	if ( is_admin() )
		return;

	// verify the nonce for this action
	if ( ! isset( $_POST['edd-nonce'] ) || ! wp_verify_nonce( $_POST['edd-nonce'], 'edd-purchase-nonce' ) )
		return;

	// make sure the cart isn't empty
	$cart = edd_get_cart_contents();
	if( empty( $cart ) ) {

		wp_die(
			sprintf(
				__( 'Your cart is empty, please return to the %ssite%s and try again.', 'edd' ),
				'<a href="' . esc_url( home_url() ) . '" title="' . get_bloginfo( 'name' ) . '">',
				'</a>'
			),
			__( 'Error', 'edd' )
		);

	}

	// validate the form $_POST data
	$valid_data = edd_purchase_form_validate_fields();

	// allow themes and plugins to hoook to errors
	do_action('edd_checkout_error_checks', $_POST);

	// check errors
	if ( false !== $errors = edd_get_errors() ) {
		// we have errors, send back to checkout
		edd_send_back_to_checkout( '?payment-mode=' . $valid_data['gateway'] );
		exit;
	}

	// check user
	if ( false === $user = edd_get_purchase_form_user( $valid_data ) ) {
		// something went wrong when collecting data, send back to checkout
		edd_send_back_to_checkout( '?payment-mode=' . $valid_data['gateway'] );
		exit;
	}


	// setup user information
	$user_info = array(
		'id' 		=> $user['user_id'],
		'email' 	=> $user['user_email'],
		'first_name'=> $user['user_first'],
		'last_name' => $user['user_last'],
		'discount' 	=> $valid_data['discount']
	);

	// setup purchase information
	$purchase_data = array(
		'downloads' 	=> edd_get_cart_contents(),
		'subtotal'		=> edd_get_cart_amount( false ), 	// amount before taxes
		'tax'			=> edd_get_cart_tax(), 				// taxed amount
		'price' 		=> edd_get_cart_amount(), 			// amount after taxes
		'purchase_key' 	=> strtolower( md5( uniqid() ) ), 	// random key
		'user_email' 	=> $user['user_email'],
		'date' 			=> date( 'Y-m-d H:i:s' ),
		'user_info' 	=> $user_info,
		'post_data' 	=> $_POST,
		'cart_details' 	=> edd_get_cart_content_details(),
		'gateway' 		=> $valid_data['gateway'],
		'card_info' 	=> $valid_data['cc_info']
	);

	// add the user data for hooks
	$valid_data['user'] = $user;

	// allow themes and plugins to hook before the gateway
	do_action( 'edd_checkout_before_gateway', $_POST, $user_info, $valid_data );

	// allow the purchase data to be modified before it is sent to the gateway
	$purchase_data = apply_filters(
		'edd_purchase_data_before_gateway',
		$purchase_data,
		$valid_data
	);

	// if the total amount in the cart is 0, send to the manaul gateway. This emulates a free download purchase
	if ( $purchase_data['price'] <= 0 ) {
		// revert to manual
		$valid_data['gateway'] = 'manual';
	}

	// used for showing download links to non logged-in users after purchase, and for other plugins needing purchase data.
	edd_set_purchase_session( $purchase_data );

	// send info to the gateway for payment processing
	edd_send_to_gateway( $valid_data['gateway'], $purchase_data );

	exit;
}
add_action( 'edd_purchase', 'edd_process_purchase_form' );


/**
 * Purchase Form Validate Fields
 *
 * @access      private
 * @since       1.0.8.1
 * @return      array
*/

function edd_purchase_form_validate_fields() {
	global $edd_options;

	// check if there is $_POST
	if ( empty( $_POST ) ) return;

	// start an array to collect valid data
	$valid_data = array(
		'gateway'				=> '',		 // gateway fallback
		'discount'				=> 'none',	 // set default discount
		'need_new_user'			=> false,	 // new user flag
		'need_user_login'		=> false,	 // login user flag
		'logged_user_data'		=> array(),  // logged user collected data
		'new_user_data'			=> array(),	 // new user collected data
		'login_user_data'		=> array(),	 // login user collected data
		'guest_user_data'		=> array(),	 // guest user collected data
		'cc_info'				=> array()	 // credit card info
	);

	// validate the gateway
	$valid_data['gateway'] = edd_purchase_form_validate_gateway();

	// validate discounts
	$valid_data['discount'] = edd_purchase_form_validate_discounts();

	// collect credit card info
	$valid_data['cc_info'] = edd_get_purchase_cc_info();

	// validate agree to terms
	if ( isset( $edd_options['show_agree_to_terms'] ) )
	edd_purchase_form_validate_agree_to_terms();

	// check if user is logged in
	if ( is_user_logged_in() ) {
		// collect logged in user data
		$valid_data['logged_in_user'] = edd_purchase_form_validate_logged_in_user();

	} else if ( isset( $_POST['edd-purchase-var'] ) && $_POST['edd-purchase-var'] == 'needs-to-register' ) {

	   // set new user registrarion as required
	  $valid_data['need_new_user'] = true;

	   // validate new user data
	  $valid_data['new_user_data'] = edd_purchase_form_validate_new_user();

   // check if login validation is needed
	} else if ( isset( $_POST['edd-purchase-var'] ) && $_POST['edd-purchase-var'] == 'needs-to-login' ) {

		// set user login as required
		$valid_data['need_user_login'] = true;

		// validate users login info
		$valid_data['login_user_data'] = edd_purchase_form_validate_user_login();
	} else {

		// not registering or logging in, so setup guest user data
		$valid_data['guest_user_data'] = edd_purchase_form_validate_guest_user();

	}

	// return collected data
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
	// check if a gateway value is present
	if ( isset( $_POST['edd-gateway'] ) && trim( $_POST['edd-gateway'] ) != '' ) {
		// clean gateway
		$gateway = strip_tags( $_POST['edd-gateway'] );
		// verify if gateway is active
		if ( edd_is_gateway_active( $gateway ) ) {
			// return active gateway
			return $gateway;
		} else if ( edd_get_cart_amount() <= 0 ) {
			return 'manual';
		} else {
			// set invalid gateway error
			edd_set_error( 'invalid_gateway', __( 'The selected gateway is not active', 'edd' ) );
		}
	} else {
		// no gateway is present
		edd_set_error( 'empty_gateway', __( 'No gateway has been selected', 'edd' ) );
	}

	// return empty
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
	// check for valid discount is present
	if ( isset( $_POST['edd-discount'] ) && trim( $_POST['edd-discount'] ) != '' ) {
		// clean discount
		$discount = sanitize_text_field( $_POST['edd-discount'] );
		$user     = isset( $_POST['edd_user_login'] ) ? sanitize_text_field( $_POST['edd_user_login'] ) : sanitize_email( $_POST['edd_email'] );
		// check if validates
		if (  edd_is_discount_valid( $discount, $user ) ) {
			// return clean discount
			return $discount;
		// invalid discount
		} else {
			// set invalid discount error
			edd_set_error( 'invalid_discount', __( 'The discount you entered is invalid', 'edd' ) );
		}
	}
	// return default value
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
	// validate agree to terms
	if ( !isset( $_POST['edd_agree_to_terms'] ) || $_POST['edd_agree_to_terms'] != 1 ) {
		// user did not agree
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

	// start empty array to collect valid user data
	$valid_user_data = array(
		// assume there will be errors
		'user_id' => -1
	);

	// verify there is a user_ID
	if ( $user_ID > 0 ) {

		// get the logged in user data
		$user_data = get_userdata( $user_ID );

		if( !is_email( $_POST['edd_email'] ) ) {
			// if the user enters an email other than the stored email, we must verify it
			edd_set_error( 'invalid_email', __( 'Please enter a valid email address.', 'edd' ) );
		}

		// verify data
		if ( $user_data ) {
			// collected logged in user data
			$valid_user_data = array(
				'user_id' 		=> $user_ID,
				'user_email' 	=> sanitize_email( $_POST['edd_email'] ),
				'user_first' 	=> sanitize_text_field( $_POST['edd_first'] ),
				'user_last' 	=> sanitize_text_field( $_POST['edd_last'] ),
			);
		} else {
			// set invalid user error
			edd_set_error( 'invalid_user', __( 'The user information is invalid.', 'edd' ) );
		}
	}

	// return user data
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
		// assume there will be errors
		'user_id' => -1,
		// get first name
		'user_first' => isset( $_POST["edd_first"] ) ? strip_tags( trim( $_POST["edd_first"] ) ) : '',
		// get last name
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
			// username already registered
			edd_set_error( 'username_unavailable', __( 'Username already taken', 'edd' ) );
		// Check if it's valid
		} else if( ! edd_validate_username( $user_login ) ) {
		   // invalid username
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
		// no email
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
 * @return      void
*/

function edd_purchase_form_validate_user_login() {
	// start an array to collect valid user data
	$valid_user_data = array(
		// assume there will be errors
		'user_id' => -1
	);

	// username
	if ( !isset( $_POST['edd_user_login'] ) || $_POST['edd_user_login'] == '' ) {
		edd_set_error( 'must_log_in', __( 'You must login or register to complete your purchase', 'edd' ) );
		return $valid_user_data;
	}

	// get the user by login
	$user_data = get_user_by( 'login', strip_tags( $_POST['edd_user_login'] ) );

	// check if user exists
	if( $user_data ) {

		// get password
		$user_pass = isset( $_POST["edd_user_pass"] ) ? $_POST["edd_user_pass"] : false;

		// check user_pass
		if ( $user_pass ) {
			// check if password is valid
			if ( ! wp_check_password( $user_pass, $user_data->user_pass, $user_data->ID ) ) {
				// incorrect password
				edd_set_error( 'password_incorrect', __( 'The password you entered is incorrect', 'edd' ) );
			// all is correct
			} else {
				// repopulate the valid user data array
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
			// empty password
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
 * @return		void
*/

function edd_purchase_form_validate_guest_user() {
	// start an array to collect valid user data
	$valid_user_data = array(
		// set a default id for guests
		'user_id' => 0,
	);

	// get the guest email
	$guest_email = isset( $_POST['edd_email'] ) ? $_POST['edd_email'] : false;

	// check email
	if ( $guest_email && strlen( $guest_email ) > 0 ) {
		// validate email
		if( !is_email( $guest_email ) ) {
			// invalid email
			edd_set_error( 'email_invalid', __( 'Invalid email', 'edd' ) );
		} else {
			// all is good to go
			$valid_user_data['user_email'] = $guest_email;
		}
	} else {
		// no email
		edd_set_error( 'email_empty', __( 'Enter an email', 'edd' ) );
	}

	return $valid_user_data;
}


/**
 * Register And Login New User
 *
 * @access		private
 * @since		1.0.8.1
 * @return		integer
*/

function edd_register_and_login_new_user( $user_data = array() ) {
	// verify the array
	if ( empty( $user_data ) )
		return -1;

	// insert new user
	$user_id = wp_insert_user(array(
			'user_login'		=> $user_data['user_login'],
			'user_pass'			=> $user_data['user_pass'],
			'user_email'		=> $user_data['user_email'],
			'first_name'		=> $user_data['user_first'],
			'last_name'			=> $user_data['user_last'],
			'user_registered'	=> date('Y-m-d H:i:s'),
			'role'				=> 'subscriber'
		)
	);

	// validate inserted user
	if( is_wp_error( $user_id ) )
		return -1;

	// allow themes and plugins to hook
	do_action('edd_insert_user', $user_id);

	// login new user
	edd_log_user_in( $user_id, $user_data['user_login'], $user_data['user_pass'] );

	// return user id
	return $user_id;
}


/**
 * Get Purchase Form User
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
		// set the valid user as the logged in collected data
		$user = $valid_data['logged_in_user'];
	}
	// otherwise check if we have to register or login users
	else if( $valid_data['need_new_user'] === true || $valid_data['need_user_login'] === true  ) {
		// new user registration
		if( $valid_data['need_new_user'] === true ) {
			// set user
			$user = $valid_data['new_user_data'];
			// register and login new user
			$user['user_id'] = edd_register_and_login_new_user( $user );
		// user login
		} else if( $valid_data['need_user_login'] === true ) {
			// set user
			$user = $valid_data['login_user_data'];
			// login user
			edd_log_user_in( $user['user_id'], $user['user_login'], $user['user_pass'] );
		}
	}

	// check guest checkout
	if( false === $user && false === edd_no_guest_checkout() ) {
		// set user
		$user = $valid_data['guest_user_data'];
	}

	// verify we have an user
	if( false === $user || empty( $user ) ) {
		// return false
		return false;
	}

	// get user first name
	if( !isset( $user['user_first'] ) || strlen( trim( $user['user_first'] ) ) < 1 ) {
		$user['user_first'] = isset( $_POST["edd_first"] ) ? strip_tags( trim( $_POST["edd_first"] ) ) : '';
	}

	// get user last name
	if( !isset( $user['user_last'] ) || strlen( trim( $user['user_last'] ) ) < 1 ) {
		$user['user_last'] = isset( $_POST["edd_last"] ) ? strip_tags( trim( $_POST["edd_last"] ) ) : '';
	}

	// return valid user
	return $user;
}


/**
 * Get Credit Card Info
 *
 * @access		private
 * @since		1.2
 * @return		array
*/

function edd_get_purchase_cc_info( $valid_data = array() ) {

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

	// return cc info
	return $cc_info;
}


/**
 * Send To Success Page
 *
 * Sends the user to the succes page.
 *
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
 * Send Back to Checkout
 *
 * Used to redirect a user back to the purchase
 * page if there are errors present.
 *
 * @access      public
 * @since       1.0
 * @return      void
*/

function edd_send_back_to_checkout( $query_string = null ) {
	global $edd_options;

	$redirect = edd_get_checkout_uri();

	if($query_string)
		$redirect .= $query_string;

	wp_redirect($redirect);
	exit;
}


/**
 * Get Success Page URL
 *
 * Gets the success page URL.
 *
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