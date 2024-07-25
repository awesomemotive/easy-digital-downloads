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
 * @throws \Exception If an error occurs during processing.
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
		EDD()->cart->set_tax_rate( null );

		/**
		 * @since unknown
		 * @todo document
		 */
		do_action( 'edd_pre_process_purchase' );

		// Make sure the cart isn't empty.
		if ( empty( EDD()->cart->contents ) && empty( EDD()->cart->fees ) ) {
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
		$user = edd_get_purchase_form_user( $valid_data, false );

		// Let extensions validate fields after user is logged in if user has used login/registration form.
		do_action( 'edd_checkout_user_error_checks', $user, $valid_data, $_POST );

		if ( false === $valid_data || ! $user || edd_get_errors() ) {
			$errors = edd_get_errors();
			if ( is_array( $errors ) ) {
				throw new \Exception( current( $errors ) );
			}

			throw new \Exception( esc_html__( 'Error processing purchase. Please reload the page and try again.', 'easy-digital-downloads' ) );
		}

		// Update a customer record if they have added/updated information.
		$customer = new EDD_Customer( $user['user_email'] );

		$name = $user['user_first'] . ' ' . $user['user_last'];

		if ( empty( $customer->name ) || $name !== $customer->name ) {
			$update_data = array(
				'name' => $name,
			);

			// Update the customer's name and update the user record too.
			$customer->update( $update_data );

			if ( is_user_logged_in() ) {
				wp_update_user(
					array(
						'ID'         => get_current_user_id(),
						'first_name' => $user['user_first'],
						'last_name'  => $user['user_last'],
					)
				);
			}
		}

		// Update the customer's address if different to what's in the database.
		if ( ! empty( $user['address'] ) ) {
			$address = wp_parse_args(
				$user['address'],
				array(
					'line1'   => '',
					'line2'   => '',
					'city'    => '',
					'state'   => '',
					'country' => '',
					'zip'     => '',
				)
			);

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
		}

		EDD\Sessions\PurchaseData::set( $valid_data, $user );

		$purchase_data = edd_get_purchase_session();
		if ( empty( $purchase_data ) ) {
			throw new \Exception( esc_html__( 'Error processing purchase. Please reload the page and try again.', 'easy-digital-downloads' ) );
		}

		/**
		 * Allows further processing...
		 */
		do_action( 'edd_gateway_' . $valid_data['gateway'], $purchase_data );
	} catch ( \Exception $e ) {
		return wp_send_json_error(
			array(
				'message' => $e->getMessage(),
			)
		);
	}
}
add_action( 'wp_ajax_edds_process_purchase_form', '_edds_process_purchase_form' );
add_action( 'wp_ajax_nopriv_edds_process_purchase_form', '_edds_process_purchase_form' );
