<?php
/**
 * Rewritten core functions to provide compatibility with a full AJAX checkout.
 *
 * @package EDD_Stripe
 * @since   2.7.0
 */

/**
 * Maps serialized form data to global $_POST and $_REQUEST variables.
 *
 * This ensures any custom code that hooks in to actions inside an
 * AJAX processing step can utilize form field data.
 *
 * @since 2.7.3
 *
 * @param array $post_data $_POST data containing serialized form data.
 */
function _edds_map_form_data_to_request( $post_data ) {
	if ( ! isset( $post_data['form_data'] ) ) {
		return;
	}

	parse_str( $post_data['form_data'], $form_data );

	$_POST    = array_merge( $_POST, $form_data );
	$_REQUEST = array_merge( $_REQUEST, $_POST );
}

/**
 * When dealing with payments certain aspects only work if the payment
 * is being created inside the `edd_process_purchase_form()` function.
 *
 * Since this gateway uses multiple steps via AJAX requests this context gets lost.
 * Calling this function "fakes" that we are still in this process when creating
 * a new payment.
 *
 * Mainly this prevents `edd_insert_payment()` from creating multiple customers for
 * the same user by ensuring the checkout email address is added to the existing customer.
 *
 * @link https://github.com/easydigitaldownloads/easy-digital-downloads/blob/master/includes/payments/class-edd-payment.php#L2754
 *
 * @since 2.7.0
 */
function _edds_fake_process_purchase_step() {
	// Save current errors.
	$errors = edd_get_errors();

	// Clear any errors that might be used as a reason to attempt a redirect in the following action.
	edd_clear_errors();

	// Don't run any attached actions twice.
	remove_all_actions( 'edd_pre_process_purchase' );

	// Pretend we are about to process a purchase.
	do_action( 'edd_pre_process_purchase' );

	// Clear any errors that may have been set in the previous action.
	edd_clear_errors();

	// Restore original errors.
	if ( ! empty( $errors ) ) {
		foreach ( $errors as $error_id => $error_message ) {
			edd_set_error( $error_id, $error_message );
		}
	}
}

/**
 * A rewritten version of `edds_get_purchase_form_user()` that can be run during AJAX.
 *
 * @since 2.7.0
 *
 * @return array
 */
function _edds_get_purchase_form_user( $valid_data = array() ) {
	// Initialize user
	$user = false;

	if ( is_user_logged_in() ) {

		// Set the valid user as the logged in collected data.
		$user = $valid_data['logged_in_user'];

	} elseif ( true === $valid_data['need_new_user'] || true === $valid_data['need_user_login'] ) {

		// Ensure $_COOKIE is available without a new HTTP request.
		add_action( 'set_logged_in_cookie', 'edds_set_logged_in_cookie_global' );

		// New user registration.
		if ( true === $valid_data['need_new_user'] ) {

			// Set user.
			$user = $valid_data['new_user_data'];

			// Register and login new user.
			$user['user_id'] = edd_register_and_login_new_user( $user );

		} elseif ( true === $valid_data['need_user_login'] ) { // User login.

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

		remove_action( 'set_logged_in_cookie', 'edds_set_logged_in_cookie_global' );
	}

	// Check guest checkout.
	if ( false === $user && false === edd_no_guest_checkout() ) {
		// Set user.
		$user = $valid_data['guest_user_data'];
	}

	// Verify we have an user.
	if ( false === $user || empty( $user ) ) {
		return false;
	}

	// Get user first name.
	if ( ! isset( $user['user_first'] ) || strlen( trim( $user['user_first'] ) ) < 1 ) {
		$user['user_first'] = isset( $_POST["edd_first"] ) ? strip_tags( trim( $_POST["edd_first"] ) ) : '';
	}

	// Get user last name.
	if ( ! isset( $user['user_last'] ) || strlen( trim( $user['user_last'] ) ) < 1 ) {
		$user['user_last'] = isset( $_POST["edd_last"] ) ? strip_tags( trim( $_POST["edd_last"] ) ) : '';
	}

	// Get the user's billing address details.
	$user['address'] = array();
	$user['address']['line1']   = ! empty( $_POST['card_address']    ) ? sanitize_text_field( $_POST['card_address']    ) : '';
	$user['address']['line2']   = ! empty( $_POST['card_address_2']  ) ? sanitize_text_field( $_POST['card_address_2']  ) : '';
	$user['address']['city']    = ! empty( $_POST['card_city']       ) ? sanitize_text_field( $_POST['card_city']       ) : '';
	$user['address']['state']   = ! empty( $_POST['card_state']      ) ? sanitize_text_field( $_POST['card_state']      ) : '';
	$user['address']['country'] = ! empty( $_POST['billing_country'] ) ? sanitize_text_field( $_POST['billing_country'] ) : '';
	$user['address']['zip']     = ! empty( $_POST['card_zip']        ) ? sanitize_text_field( $_POST['card_zip']        ) : '';

	if ( empty( $user['address']['country'] ) ) {
		$user['address'] = false; // Country will always be set if address fields are present.
	}

	if ( ! empty( $user['user_id'] ) && $user['user_id'] > 0 && ! empty( $user['address'] ) ) {
		$customer = edd_get_customer_by( 'user_id', $user['user_id'] );
		if ( $customer ) {
			edd_maybe_add_customer_address( $customer->id, $user['address'] );
		}
	}

	// Return valid user.
	return $user;
}

/**
 * A rewritten version of `edd_process_purchase_form()` that allows for full AJAX processing.
 *
 * `edd_process_purchase_form()` is run up until:
 *
 * if ( $is_ajax ) {
 *   echo 'success';
 *   edd_die();
 * }
 *
 * Then this function is called which reruns the start of `edd_process_purchase_form()` and
 * continues the rest of the processing.
 *
 * @link https://github.com/easydigitaldownloads/easy-digital-downloads/blob/master/includes/process-purchase.php
 *
 * @since 2.7.0
 */
function _edds_process_purchase_form() {
	// Unset any Errors so they aren't left over form other attempts.
	edd_clear_errors();

	// Catch exceptions at a high level.
	try {
		// `edd_process_purchase_form()` and subsequent code executions are written
		// expecting form processing to happen via a POST request from a client form.
		//
		// This version is called from an AJAX POST request, so the form data is sent
		// in a serialized string to ensure all fields are available.
		//
		// Map and merge formData to $_POST so it's accessible in other functions.
		parse_str( $_POST['form_data'], $form_data );
		$_POST    = array_merge( $_POST, $form_data );
		$_REQUEST = array_merge( $_REQUEST, $_POST );

		/*
		 * Reset the tax rate so that it will be recalculated correctly.
		 * This is only needed on EDD 3.0+.
		 */
		if ( method_exists( EDD()->cart, 'set_tax_rate' ) ) {
			EDD()->cart->set_tax_rate( null );
		}

		/**
		 * @since unknown
		 * @todo document
		 */
		do_action( 'edd_pre_process_purchase' );

		// Make sure the cart isn't empty.
		if ( empty( EDD()->cart->contents ) && empty( EDD()->cart->fees) ) {
			throw new \Exception( esc_html__( 'Your cart is empty.', 'easy-digital-downloads' ) );
		}

		if ( ! isset( $_POST['edd-process-checkout-nonce'] ) ) {
			edd_debug_log( __( 'Missing nonce when processing checkout. Please read the following for more information: https://easydigitaldownloads.com/development/2018/07/05/important-update-to-ajax-requests-in-easy-digital-downloads-2-9-4', 'easy-digital-downloads' ), true );
		}

		// Verify the checkout session only.
		if ( false === edds_verify() ) {
			throw new \Exception( esc_html__( 'Error processing purchase. Please reload the page and try again.', 'easy-digital-downloads' ) );
		}

		// Validate the form $_POST data.
		$valid_data = edd_purchase_form_validate_fields();

		// Allow themes and plugins to hook to errors.
		//
		// In the future these should throw exceptions, existing `edd_set_error()` usage will be caught below.
		do_action( 'edd_checkout_error_checks', $valid_data, $_POST );

		// Validate the user.
		$user = _edds_get_purchase_form_user( $valid_data );

		// Let extensions validate fields after user is logged in if user has used login/registration form
		do_action( 'edd_checkout_user_error_checks', $user, $valid_data, $_POST );

		if ( false === $valid_data || edd_get_errors() || ! $user ) {
			$errors = edd_get_errors();

			if ( is_array( $errors ) ) {
				throw new \Exception( current( $errors ) );
			}
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

		// Update the customer's address if different to what's in the database
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

		if ( ! empty( $user['user_id'] ) && $user['user_id'] > 0 && ! empty( $address ) ) {
			$customer = edd_get_customer_by( 'user_id', $user['user_id'] );
			if ( $customer ) {
				edd_maybe_add_customer_address( $customer->id, $user['address'] );
			}
		}

		$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';

		$card_country = isset( $valid_data['cc_info']['card_country'] ) ? $valid_data['cc_info']['card_country'] : false;
		$card_state   = isset( $valid_data['cc_info']['card_state'] )   ? $valid_data['cc_info']['card_state']   : false;
		$card_zip     = isset( $valid_data['cc_info']['card_zip'] )     ? $valid_data['cc_info']['card_zip']     : false;

		// Set up the unique purchase key. If we are resuming a payment, we'll overwrite this with the existing key.
		$purchase_key     = strtolower( md5( $user['user_email'] . date( 'Y-m-d H:i:s' ) . $auth_key . uniqid( 'edd', true ) ) );
		$existing_payment = EDD()->session->get( 'edd_resume_payment' );

		if ( ! empty( $existing_payment ) ) {
			$payment = new EDD_Payment( $existing_payment );

			if( $payment->is_recoverable() && ! empty( $payment->key ) ) {
				$purchase_key = $payment->key;
			}
		}

		// Setup purchase information.
		$purchase_data = array(
			'downloads'    => edd_get_cart_contents(),
			'fees'         => edd_get_cart_fees(),        // Any arbitrary fees that have been added to the cart
			'subtotal'     => edd_get_cart_subtotal(),    // Amount before taxes and discounts
			'discount'     => edd_get_cart_discounted_amount(), // Discounted amount
			'tax'          => edd_get_cart_tax(),               // Taxed amount
			'tax_rate'     => edd_use_taxes() ? edd_get_cart_tax_rate( $card_country, $card_state, $card_zip ) : 0, // Tax rate
			'price'        => edd_get_cart_total(),    // Amount after taxes
			'purchase_key' => $purchase_key,
			'user_email'   => $user['user_email'],
			'date'         => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
			'user_info'    => stripslashes_deep( $user_info ),
			'post_data'    => $_POST,
			'cart_details' => edd_get_cart_content_details(),
			'gateway'      => $valid_data['gateway'],
			'card_info'    => $valid_data['cc_info']
		);

		// Add the user data for hooks
		$valid_data['user'] = $user;

		// Allow themes and plugins to hook before the gateway
		do_action( 'edd_checkout_before_gateway', $_POST, $user_info, $valid_data );

		// Store payment method data.
		$purchase_data['gateway_nonce'] = wp_create_nonce( 'edd-gateway' );

		// Allow the purchase data to be modified before it is sent to the gateway
		$purchase_data = apply_filters(
			'edd_purchase_data_before_gateway',
			$purchase_data,
			$valid_data
		);

		// Setup the data we're storing in the purchase session
		$session_data = $purchase_data;

		// Used for showing download links to non logged-in users after purchase, and for other plugins needing purchase data.
		edd_set_purchase_session( $session_data );

		/**
		 * Allows further processing...
		 */
		do_action( 'edd_gateway_' . $purchase_data['gateway'], $purchase_data );
	} catch( \Exception $e ) {
		return wp_send_json_error( array(
			'message' => $e->getMessage(),
		) );
	}
}
add_action( 'wp_ajax_edds_process_purchase_form', '_edds_process_purchase_form' );
add_action( 'wp_ajax_nopriv_edds_process_purchase_form', '_edds_process_purchase_form' );
