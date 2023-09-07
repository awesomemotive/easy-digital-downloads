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
function edd_stripe_admin_js( $payment_id  = 0 ) {
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
