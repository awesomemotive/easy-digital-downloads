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
    
    // look for the purchase action
    if ( ! isset( $_POST['edd-action'] ) || $_POST['edd-action'] != 'purchase' )
    return;
    
    // verify the nonce for this action
    if ( ! isset( $_POST['edd-nonce'] ) || ! wp_verify_nonce( $_POST['edd-nonce'], 'edd-purchase-nonce' ) )
    return;

    // validate the form $_POST data
    $valid_data = edd_purchase_form_validate_fields(); 
    
	// allow themes and plugins to hoook to errors
	do_action('edd_checkout_error_checks', $_POST);

	// check errors
	if ( false !== $errors = edd_get_errors() ) {
		// we have errors, send back to checkout
		edd_send_back_to_checkout('?payment-mode=' . $valid_data['gateway']);
		exit;
    }
    
    // check user
    if ( false === $user = edd_get_purchase_form_user( $valid_data ) ) {
        // something went wrong when collecting data, send back to checkout
		edd_send_back_to_checkout('?payment-mode=' . $valid_data['gateway']);
		exit;
    }
    
    // set the valid user
    $valid_data['user'] = $user;

	// setup user information
	$user_info = array(
		'id' => $user['user_id'],
		'email' => $user['user_email'],
		'first_name' => $user['user_first'], 
		'last_name' => $user['user_last'], 
		'discount' => $valid_data['discount']
	 );	
		
	// setup purchase information
	$purchase_data = array( 
		'downloads' => edd_get_cart_contents(), 
		'price' => edd_get_cart_amount(), 
		'purchase_key' => strtolower( md5( uniqid() ) ), // random key
		'user_email' => $user['user_email'], 
		'date' => date( 'Y-m-d H:i:s' ), 
		'user_info' => $user_info, 
		'post_data' => $_POST, 
		'cart_details' => edd_get_cart_content_details()
	 );
		
	// allow themes and plugins to hook before the gateway
	do_action( 'edd_checkout_before_gateway', $_POST, $user_info, $valid_data );
		
	// allow the purchase data to be modified before it is sent to the gateway
	$purchase_data = apply_filters( 'edd_purchase_data_before_gateway', $purchase_data, $valid_data );
		
	// if the total amount in the cart is 0, send to the manaul gateway. This emulates a free download purchase
	if ( $purchase_data['price'] <= 0 ) {
		$valid_data['gateway'] = 'manual';
	}
		
	// send info to the gateway for payment processing
	edd_send_to_gateway( $valid_data['gateway'], $purchase_data );
	exit;
}
add_action('edd_purchase', 'edd_process_purchase_form');


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
        'gateway'               => '',       // gateway fallback
        'discount'              => 'none',   // set default discount
        'no_guest_checkout'     => false,    // guest checkout flag
        'need_new_user'         => false,    // new user flag
        'need_user_login'       => false,    // login user flag
        'new_user_data'         => array(),  // new user collected data
        'login_user_data'       => array(),  // login user collected data
        'guest_user_data'       => array(),  // guest user collected data
    );
    
    // validate the gateway
    $valid_data['gateway'] = edd_purchase_form_validate_gateway();
    
    // validate discounts
    $valid_data['discount'] = edd_purchase_form_validate_discounts();

    // validate agree to terms
    if ( isset( $edd_options['show_agree_to_terms'] ) )
    edd_purchase_form_validate_agree_to_terms();
    	
	// validate user info if required
	if ( edd_no_guest_checkout() ) {	
	    
	    // set guest checkout to true
	    $valid_data['no_guest_checkout'] = true;
	    
    	// check if registration validation is needed
    	if ( isset( $_POST['edd-purchase-var'] ) && $_POST['edd-purchase-var'] == 'needs-to-register' ) {
    	    
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
    	}
    	
	} else {
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
        } else {
            // set invalid gateway erro
            edd_set_error( 'invalid_gateway', __( 'The selected gateway is not active', 'edd' ) );
        }
    }
    // no gateway is present
    edd_set_error( 'empty_gateway', __( 'No gateway has been selected', 'edd' ) );
    
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
    if ( isset( $_POST['edd-discount'] ) && $_POST['edd-discount'] != '' ) {
        // clean discount
        $discount = strip_tags( $_POST['edd-discount'] );
        // check if validates
        if (  edd_is_discount_valid( $discount ) ) {
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
    if ( ! isset( $_POST['edd_agree_to_terms'] ) || $_POST['edd_agree_to_terms'] != 1  ) {
        // user did not agree
		edd_set_error( 'agree_to_terms', __( 'You must agree to the terms of use', 'edd' ) );
	}
}


/**
 * Purchase Form Validate New User
 *
 * @access      private
 * @since       1.0.8.1
 * @return      array
*/

function edd_purchase_form_validate_new_user() {
        // start empty array to collect valid user data
        $valid_user_data = array(
            // assume there will be errors
            'user_id' => -1 
        );
    
        // check the new user's credentials against existing ones
		$user_login	  = isset( $_POST["edd_user_login"] ) ? trim( $_POST["edd_user_login"] ) : false;
		$user_email	  = isset( $_POST['edd_email'] ) ? trim( $_POST['edd_email'] ) : false;
		$user_pass	  = isset( $_POST["edd_user_pass"] ) ? trim( $_POST["edd_user_pass"] ) : false;
		$pass_confirm = isset( $_POST["edd_user_pass_confirm"] ) ? trim( $_POST["edd_user_pass_confirm"] ) : false;
		        
        // check if we have an username to register
        if ( $user_login && strlen( $user_login ) > 0 ) {
		    // we have an user name, check if it already exists
			if ( username_exists( $user_login ) ) {
			    // username already registered
				edd_set_error( 'username_unavailable', __( 'Username already taken', 'edd' ) );
			// check if it's valid
			} else if ( ! validate_username( $user_login ) ) {
			    // invalid username
				edd_set_error( 'username_invalid', __( 'Invalid username', 'edd' ) );
			} else {
			    // all is good to go
			    $valid_user_data['user_login'] = $user_login;
			}
		} else {
		    // no username
    		edd_set_error( 'username_empty', __( 'Enter a username', 'edd' ) );
		}
					
		// check if we have an email to verify
		if ( $user_email && strlen( $user_email ) > 0 ) {
		    // validate email 
		    if ( ! is_email( $user_email ) ) {
		        // invalid email
                edd_set_error( 'email_invalid', __( 'Invalid email', 'edd' ) );
            // check if email exists
		    } else if ( email_exists( $user_email ) ) {
    			// email address already registered
    			edd_set_error( 'email_used', __( 'Email already used', 'edd' ) );
    		} else {
    		    // all is good to go
    			$valid_user_data['user_email'] = $user_email;
    		}
		} else {
		    // no email
		    edd_set_error( 'username_empty', __( 'Enter an email', 'edd' ) );
		}
				
		// check password
		if ( $user_pass && $pass_confirm ) {
		    // verify confirmation matches
		    if ( $user_pass != $pass_confirm ) {
    			// passwords do not match
    			edd_set_error( 'password_mismatch', __( 'Passwords don\'t match', 'edd' ) );
    		} else {
    		    // all is good to go
    			$valid_user_data['user_pass'] = $user_pass;
    		}
		} else {
		    // pass or confrimation missing
		    if ( $pass_confirm ) {
		        // password invalid
		        edd_set_error( 'password_empty', __( 'Enter a password', 'edd' ) );
		    } else {
		        // confirmation invalid 
		        edd_set_error( 'confirmation_empty', __( 'Enter the password confirmation', 'edd' ) );
		    }
		}
		
		// get user first and last name
		$valid_user_data['user_first']	= isset( $_POST["edd_first"] ) ? strip_tags( trim( $_POST["edd_first"] ) ) : '';
		$valid_user_data['user_last']	= isset( $_POST['edd_last'] ) ? strip_tags( trim( $_POST['edd_last'] ) ) : '';
		
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
    if ( ! isset( $_POST['edd-username'] ) || $_POST['edd-username'] == '' ) {
		edd_set_error( 'must_log_in', __( 'You must login or register to complete your purchase', 'edd' ) );
		return $valid_user_data;
	}
	
	// get the user by login
	$user_data = get_user_by( 'login', strip_tags( $_POST['edd-username'] ) );
	
	// check if user exists
	if ( $user_data ) {
		
		// get password
		$user_pass  = isset( $_POST["edd-password"] ) ? $_POST["edd-password"] : false;
		
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
	    //  no username
		edd_set_error( 'username_incorrect', __( 'The username you entered does not exist', 'edd' ) );
	}
	
	return $valid_user_data;
	
}


/**
 * Purchase Form Validate Guest User
 *
 * @access      private
 * @since       1.0.8.1
 * @return      void
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
	    if ( ! is_email( $guest_email ) ) {
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
	
	// get user first and last name
	$valid_user_data['user_first']	= isset( $_POST["edd_first"] ) ? strip_tags( $_POST["edd_first"] ) : '';
	$valid_user_data['user_last']	= isset( $_POST['edd_last'] ) ? strip_tags( $_POST['edd_last'] ) : '';
	
	return $valid_user_data;    
}


/**
 * Register And Login New User
 *
 * @access      private
 * @since       1.0.8.1
 * @return      integer
*/

function edd_register_and_login_new_user( $user_data = array() ) {
    // verify the array
    if ( empty( $user_data ) )
    return -1;
    
    // insert new user
    $user_id = wp_insert_user(array(
			'user_login'		=> $user_data['user_login'],
			'user_pass'	 		=> $user_data['user_pass'],
			'user_email'		=> $user_data['user_email'],
			'first_name'		=> $user_data['user_first'],
			'last_name'			=> $user_data['user_last'],
			'user_registered'	=> date('Y-m-d H:i:s'),
			'role'				=> 'subscriber'
		)
	);
	
	// validate inserted user
	if ( is_wp_error( $user_id ) )
	return -1;
	
	// allow themes and plugins to hook
	do_action('edd_insert_user', $user_id);
	
	// login new user
	edd_log_user_in($user_id, $user_login, $user_pass);
	
	// return user id
	return $user_id;
}


/**
 * Get Purchase Form User
 *
 * @access      private
 * @since       1.0 
 * @return      array
*/

function edd_get_purchase_form_user( $valid_data = array() ) {
    // check data array
    if ( ! isset( $valid_data['no_guest_checkout'] ) )
    return;
    
    // initialize user
    $user = false;
    
    // check guest checkout
    if ( $valid_data['no_guest_checkout'] === true ) {
        // new user registration
        if ( $valid_data['need_new_user'] === true ) {
            // set user
            $user = $valid_data['new_user_data'];
            // register and login new user
            $user['user_id'] = edd_register_and_login_new_user( $user );
        // user login
        } else if ( $valid_data['need_user_login'] === true ) {
            // set user
            $user = $valid_data['login_user_data'];
            // login user
            edd_log_user_in( $user['user_id'], $user['user_login'], $user['user_pass'] );
        }
    // guest checkout
    } else {
        // set user
        $user = $valid_data['guest_user_data'];
    }
    
    // verify we have an user
    if ( empty( $user ) || ( !isset( $user['user_id']) || $user['user_id'] === -1 ) ) {
        return -1;
    }
    
    return $user;
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

function edd_send_to_success_page($query_string = null) {
	global $edd_options;
	$redirect = get_permalink($edd_options['success_page']);
	if($query_string)
		$redirect .= $query_string;
	wp_redirect($redirect); exit;
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

function edd_send_back_to_checkout($query_string = null) {
	global $edd_options;
	$redirect = get_permalink($edd_options['purchase_page']);
	if($query_string)
		$redirect .= $query_string;
	wp_redirect($redirect); exit;
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

function edd_get_success_page_url($query_string = null) {
	global $edd_options;
	$success_page = get_permalink($edd_options['success_page']);
	if($query_string)
		$success_page .= $query_string;
	return $success_page;
}