<?php
/**
 * Manage deprecations.
 *
 * @package EDD_Stripe
 * @since   2.7.0
 */

/**
 * Process stripe checkout submission
 *
 * @access      public
 * @since       1.0
 * @return      void
 */
function edds_process_stripe_payment( $purchase_data ) {
	_edd_deprecated_function( 'edds_process_stripe_payment', '2.7.0', 'edds_process_purchase_form', debug_backtrace() );

	return edds_process_purchase_form( $purchase_data );
}

/**
 * Database Upgrade actions
 *
 * @access      public
 * @since       2.5.8
 * @return      void
 */
function edds_plugin_database_upgrades() {
	_edd_deprecated_function(
		__FUNCTION__,
		'2.8.1',
		null,
		debug_backtrace()
	);

	edd_stripe()->database_upgrades();
}

/**
 * Internationalization
 *
 * @since       1.6.6
 * @return      void
 */
function edds_textdomain() {
	_edd_deprecated_function(
		__FUNCTION__,
		'2.8.1',
		null,
		debug_backtrace()
	);

	edd_stripe()->load_textdomain();
}

/**
 * Register our payment gateway
 *
 * @since       1.0
 * @return      array
 */
function edds_register_gateway( $gateways ) {
	_edd_deprecated_function(
		__FUNCTION__,
		'2.8.1',
		null,
		debug_backtrace()
	);

	return edd_stripe()->register_gateway( $gateways );
}

/**
 * Process refund in Stripe, in EDD 2.x
 * For EDD 3.0, see `edd_stripe_maybe_refund_charge()`
 *
 * @see edd_stripe_maybe_refund_charge()
 *
 * @access      public
 * @since       1.8
 * @deprecated  2.9.0
 * @return      void
 */
function edd_stripe_process_refund( $payment_id, $new_status, $old_status ) {
	_edd_deprecated_function(
		__FUNCTION__,
		'2.9.0',
		'edd_stripe_maybe_refund_charge',
		debug_backtrace()
	);
}

/**
 * Load our admin javascript
 *
 * @access      public
 * @since       1.8
 * @deprecated  2.9.0 - Deprecated as 2.9.0 requires EDD 3.1+
 * @return      void
 */
function edd_stripe_admin_js( $payment_id = 0 ) {
	/**
	 * Since Stripe 2.9.0 requires EDD 3.1, we no longer need to load this.
	 */
	_edd_deprecated_function(
		__FUNCTION__,
		'2.9.0',
		null,
		debug_backtrace()
	);
}

/**
 * Display the payment status filters
 *
 * @since 1.6
 * @deprecated 2.9.0
 * @param array $views  The array of views for the payments/orders table.
 * @return array
 */
function edds_payment_status_filters( $views ) {
	_edd_deprecated_function(
		__FUNCTION__,
		'2.9.0',
		null,
		debug_backtrace()
	);
}


/**
 * Renamed Apple Pay functions after we removed "prb" from them.
 *
 * Most of these were used internally only, and were hooked into display notices.
 * No real documetnation is needed here.
 */
function edds_prb_apple_pay_admin_notices_register() {
	_edd_deprecated_function( __FUNCTION__, '2.9.1', 'edds_apple_pay_admin_notices_register' );
	return edds_apple_pay_admin_notices_register();
}

function edds_prb_apple_pay_get_fileinfo() {
	_edd_deprecated_function( __FUNCTION__, '2.9.1', 'edds_apple_pay_get_fileinfo' );
	return edds_apple_pay_get_fileinfo();
}

function edds_prb_apple_pay_is_valid() {
	_edd_deprecated_function( __FUNCTION__, '2.9.1', 'edds_apple_pay_is_valid' );
	return edds_apple_pay_is_valid();
}

function edds_prb_apple_pay_has_domain_verification_file() {
	_edd_deprecated_function( __FUNCTION__, '2.9.1', 'edds_apple_pay_has_domain_verification_file' );
	return edds_apple_pay_has_domain_verification_file();
}

function edds_prb_apple_pay_has_domain_verification() {
	_edd_deprecated_function( __FUNCTION__, '2.9.1', 'edds_apple_pay_has_domain_verification' );
	return edds_apple_pay_has_domain_verification();
}

function edds_prb_apple_pay_create_directory_and_move_file() {
	_edd_deprecated_function( __FUNCTION__, '2.9.1', 'edds_apple_pay_create_directory_and_move_file' );
	edds_apple_pay_create_directory_and_move_file();
}

function edds_prb_apple_pay_check_domain() {
	_edd_deprecated_function( __FUNCTION__, '2.9.1', 'edds_apple_pay_check_domain' );
	edds_apple_pay_check_domain();
}

function edds_prb_apple_pay_verify_domain() {
	_edd_deprecated_function( __FUNCTION__, '2.9.1', 'edds_apple_pay_verify_domain' );
	edds_apple_pay_verify_domain();
}

function edds_prb_apple_pay_admin_notices_print() {
	_edd_deprecated_function( __FUNCTION__, '2.9.1', 'edds_apple_pay_admin_notices_print' );
	edds_apple_pay_admin_notices_print();
}

/**
 * Register payment statuses for preapproval
 *
 * @since 1.6
 * @deprecated 3.2.0
 * @return void
 */
function edds_register_post_statuses() {}

/**
 * Trigger preapproved payment charge
 *
 * @since 1.6
 * @deprecated 3.2.0
 * @return void
 */
function edds_process_preapproved_charge() {}

/**
 * Cancel a preapproved payment
 *
 * @since 1.6
 * @deprecated 3.2.0
 * @return void
 */
function edds_process_preapproved_cancel() {}

/**
 * Show the Process / Cancel buttons for preapproved payments
 *
 * @since 1.6
 * @deprecated 3.2.0
 * @return string
 */
function edds_payments_column_data( $value, $payment_id, $column_name ) {
	return $value;
}

/**
 * Charge a preapproved payment
 *
 * @since 1.6
 * @deprecated 3.2.0
 * @return bool
 */
function edds_charge_preapproved( $payment_id = 0 ) {
	_edds_deprecated_function( __FUNCTION__, '3.2.0', 'EDD_Stripe_Pro\Preapproval\charge_preapproved' );
	if ( ! function_exists( 'EDD\StripePro\Preapproval\charge_preapproved' ) ) {
		return false;
	}

	return EDD\StripePro\Admin\Preapproval\charge_preapproved( $payment_id );
}

/**
 * Completes a Payment authorization.
 *
 * @since 2.7.0
 * @deprecated 3.2.0
 */
function edds_complete_payment_authorization() {}

/**
 * Retrieves a sanitized statement descriptor.
 *
 * @since 2.6.19
 * @deprecated 3.2.8
 *
 * @return string $statement_descriptor Sanitized statement descriptor.
 */
function edds_get_statement_descriptor() {
	_edd_deprecated_function( __FUNCTION__, '3.2.8' );
	$statement_descriptor = edd_get_option( 'stripe_statement_descriptor', '' );
	$statement_descriptor = edds_sanitize_statement_descriptor( $statement_descriptor );

	return $statement_descriptor;
}

/**
 * Retrieves a list of unsupported characters for Stripe statement descriptors.
 *
 * @since 2.6.19
 * @deprecated 3.2.8
 *
 * @return array $unsupported_characters List of unsupported characters.
 */
function edds_get_statement_descriptor_unsupported_characters() {
	_edd_deprecated_function( __FUNCTION__, '3.2.8' );

	$unsupported_characters = array(
		'<',
		'>',
		'"',
		'\'',
		'\\',
		'*',
	);

	/**
	 * Filters the list of unsupported characters for Stripe statement descriptors.
	 *
	 * @since 2.6.19
	 *
	 * @param array $unsupported_characters List of unsupported characters.
	 */
	$unsupported_characters = apply_filters( 'edds_get_statement_descriptor_unsupported_characters', $unsupported_characters );

	return $unsupported_characters;
}

/**
 * Sanitizes a string to be used for a statement descriptor.
 *
 * @since 2.6.19
 * @deprecated 3.2.8
 *
 * @link https://stripe.com/docs/connect/statement-descriptors#requirements
 *
 * @param string $statement_descriptor Statement descriptor to sanitize.
 * @return string $statement_descriptor Sanitized statement descriptor.
 */
function edds_sanitize_statement_descriptor( $statement_descriptor ) {
	_edd_deprecated_function( __FUNCTION__, '3.2.8' );

	$unsupported_characters = edds_get_statement_descriptor_unsupported_characters();

	$statement_descriptor = trim( str_replace( $unsupported_characters, '', $statement_descriptor ) );
	$statement_descriptor = substr( $statement_descriptor, 0, 22 );

	return $statement_descriptor;
}

/**
 * Listen for Stripe Webhooks.
 *
 * We've moved all webhook listeners to the EDD\Gateways\Stripe\Webhooks\Listener class.
 *
 * @since 1.5
 * @deprecated 3.3.0
 */
function edds_stripe_event_listener() {
	_edd_deprecated_function( __FUNCTION__, '3.3.0', 'EDD\Gateways\Stripe\Webhooks\Listener' );
}

/**
 * A rewritten version of `edds_get_purchase_form_user()` that can be run during AJAX.
 *
 * @since 2.7.0
 * @deprecated 3.3.2 Use `edd_get_purchase_form_user()` instead.
 *
 * @return array
 */
function _edds_get_purchase_form_user( $valid_data = array() ) {
	// Initialize user.
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
		$user['user_first'] = isset( $_POST['edd_first'] ) ? strip_tags( trim( $_POST['edd_first'] ) ) : '';
	}

	// Get user last name.
	if ( ! isset( $user['user_last'] ) || strlen( trim( $user['user_last'] ) ) < 1 ) {
		$user['user_last'] = isset( $_POST['edd_last'] ) ? strip_tags( trim( $_POST['edd_last'] ) ) : '';
	}

	// Get the user's billing address details.
	$user['address']            = array();
	$user['address']['line1']   = ! empty( $_POST['card_address'] ) ? sanitize_text_field( $_POST['card_address'] ) : '';
	$user['address']['line2']   = ! empty( $_POST['card_address_2'] ) ? sanitize_text_field( $_POST['card_address_2'] ) : '';
	$user['address']['city']    = ! empty( $_POST['card_city'] ) ? sanitize_text_field( $_POST['card_city'] ) : '';
	$user['address']['state']   = ! empty( $_POST['card_state'] ) ? sanitize_text_field( $_POST['card_state'] ) : '';
	$user['address']['country'] = ! empty( $_POST['billing_country'] ) ? sanitize_text_field( $_POST['billing_country'] ) : '';
	$user['address']['zip']     = ! empty( $_POST['card_zip'] ) ? sanitize_text_field( $_POST['card_zip'] ) : '';

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
