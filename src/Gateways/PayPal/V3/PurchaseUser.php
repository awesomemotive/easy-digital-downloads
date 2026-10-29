<?php
/**
 * PayPal REST Checkout - Purchase User Resolution
 *
 * Resolves buyer identity for REST-based PayPal checkout flows (Unbranded
 * Card, Fastlane). Delegates to the standard EDD user-validation pipeline so
 * REST flows behave identically to a normal form submission.
 *
 * @package     EDD\Gateways\PayPal\V3
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.9
 */

namespace EDD\Gateways\PayPal\V3;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Resolves buyer identity and issues auth cookies for REST-based PayPal flows.
 *
 * @since 3.6.9
 */
class PurchaseUser {

	/**
	 * Session key naming the user this request authenticated at order creation.
	 *
	 * @since 3.7.3
	 *
	 * @var string
	 */
	private const AUTHENTICATED_USER_KEY = 'checkout_authenticated_user';

	/**
	 * Resolves the buyer's user data from a REST request's form_data.
	 *
	 * Runs the standard EDD user-validation pipeline the same way
	 * PurchaseData::start() does for a normal form submission. Covers four
	 * cases: already logged in, new registration, login, and guest checkout.
	 *
	 * @since 3.6.9
	 * @since 3.7.1 Fires edd_checkout_user_error_checks and refuses when it raises an error.
	 * @since 3.7.2 Takes the discounts from the cart and refuses one that does not validate.
	 *
	 * @param array  $form_data  Raw form data from the REST request body.
	 * @param string $email      Already-sanitized buyer email.
	 * @param string $first_name Already-sanitized first name.
	 * @param string $last_name  Already-sanitized last name.
	 * @param array  $address    Already-sanitized address array.
	 * @return array|\WP_Error Array with 'valid_data' and 'user' keys on success, or WP_Error on failure.
	 */
	public static function resolve( array $form_data, $email, $first_name, $last_name, array $address ) {
		$discount = edd_purchase_form_validate_discounts();
		$errors   = array_intersect_key( (array) edd_get_errors(), array_flip( array( 'invalid_discount', 'edd-discount-error' ) ) );
		if ( $errors ) {
			edd_unset_error( 'invalid_discount' );
			edd_unset_error( 'edd-discount-error' );
			return new \WP_Error( 'invalid_discount', reset( $errors ), array( 'status' => 400 ) );
		}

		$valid_data = array(
			'gateway'         => 'paypal_commerce',
			'discount'        => $discount,
			'cc_info'         => array(),
			'need_new_user'   => false,
			'need_user_login' => false,
			'logged_in_user'  => array(),
			'new_user_data'   => array(),
			'login_user_data' => array(),
			'guest_user_data' => array(),
		);

		$purchase_var = ! empty( $form_data['edd-purchase-var'] ) ? sanitize_text_field( $form_data['edd-purchase-var'] ) : '';

		if ( is_user_logged_in() ) {
			$valid_data['logged_in_user'] = array(
				'user_id'    => get_current_user_id(),
				'user_email' => $email,
				'user_first' => $first_name,
				'user_last'  => $last_name,
			);
		} elseif ( 'needs-to-register' === $purchase_var ) {
			// Populate $_POST so edd_purchase_form_validate_new_user() can read
			// the registration fields; password fields are not sanitized to
			// preserve the buyer's chosen characters.
			$_POST['edd_email']             = $email;
			$_POST['edd_first']             = $first_name;
			$_POST['edd_last']              = $last_name;
			$_POST['edd-purchase-var']      = 'needs-to-register';
			$_POST['edd_user_login']        = ! empty( $form_data['edd_user_login'] ) ? sanitize_user( $form_data['edd_user_login'] ) : '';
			$_POST['edd_user_pass']         = $form_data['edd_user_pass'] ?? '';
			// Honor an explicit confirm field; fall back to the password itself
			// so single-password-field themes don't fail the mismatch check.
			$_POST['edd_user_pass_confirm'] = ! empty( $form_data['edd_user_pass_confirm'] ) ? $form_data['edd_user_pass_confirm'] : $_POST['edd_user_pass'];

			$valid_data['need_new_user'] = true;
			$valid_data['new_user_data'] = edd_purchase_form_validate_new_user();

			$errors = edd_get_errors();
			if ( $errors ) {
				edd_clear_errors();
				return new \WP_Error( 'registration_failed', reset( $errors ), array( 'status' => 400 ) );
			}
		} elseif ( 'needs-to-login' === $purchase_var ) {
			// sanitize_text_field rather than sanitize_user because the login field
			// accepts an email address, which sanitize_user would mangle.
			$user_login = ! empty( $form_data['edd_user_login'] ) ? sanitize_text_field( $form_data['edd_user_login'] ) : '';
			$user_pass  = $form_data['edd_user_pass'] ?? '';

			$wp_user = edd_log_user_in( 0, $user_login, $user_pass, false );

			$errors = edd_get_errors();
			if ( $errors ) {
				edd_clear_errors();
				return new \WP_Error( 'login_failed', reset( $errors ), array( 'status' => 400 ) );
			}

			// edd_log_user_in() calls wp_set_current_user(), so is_user_logged_in()
			// is now true. Populate logged_in_user so edd_get_purchase_form_user()
			// resolves correctly through the logged-in branch.
			$valid_data['logged_in_user'] = array(
				'user_id'    => $wp_user->ID,
				'user_email' => $wp_user->user_email,
				'user_first' => $wp_user->first_name,
				'user_last'  => $wp_user->last_name,
			);
		} else {
			// Guest checkout — enforce the site's guest policy.
			if ( edd_no_guest_checkout() ) {
				return new \WP_Error(
					'registration_required',
					__( 'You must register or login to complete your purchase.', 'easy-digital-downloads' ),
					array( 'status' => 400 )
				);
			}
			$valid_data['guest_user_data'] = array(
				'user_id'    => 0,
				'user_email' => $email,
				'user_first' => $first_name,
				'user_last'  => $last_name,
			);
		}

		$user = edd_get_purchase_form_user( $valid_data, false );

		/**
		 * Let extensions validate fields after the user has been resolved.
		 *
		 * Fired here so a REST checkout is held to the same user checks a form
		 * submission is, including the check that refuses a guest submitting a
		 * registered account's email address.
		 *
		 * @since 3.7.1
		 *
		 * @param array|bool $user       The resolved user data.
		 * @param array      $valid_data The valid data.
		 * @param array      $form_data  The submitted form data.
		 */
		do_action( 'edd_checkout_user_error_checks', $user, $valid_data, $form_data );

		$errors = edd_get_errors();
		if ( empty( $user ) || $errors ) {
			edd_clear_errors();
			$message = $errors ? reset( $errors ) : __( 'An error occurred with your account. Please try again.', 'easy-digital-downloads' );
			return new \WP_Error( 'user_error', $message, array( 'status' => 400 ) );
		}

		$user['address'] = $address;

		return array(
			'valid_data' => $valid_data,
			'user'       => $user,
		);
	}

	/**
	 * Records the user this request authenticated, for the capture-side re-issue.
	 *
	 * Call after the order is built and claimed, so it reflects who is signed in
	 * at that point. A guest clears the key, so an earlier attempt cannot carry
	 * its value into a later one.
	 *
	 * @since 3.7.3
	 *
	 * @return void
	 */
	public static function remember_authenticated_user() {
		$user_id = get_current_user_id();
		if ( $user_id ) {
			EDD()->session->set( self::AUTHENTICATED_USER_KEY, $user_id );
			return;
		}

		EDD()->session->set( self::AUTHENTICATED_USER_KEY, null );
	}

	/**
	 * Re-issues the auth cookie for the user recorded at order creation, when that
	 * user owns the order.
	 *
	 * The cookie set during the /create response may be stripped by proxy
	 * layers (e.g. Cloudflare APO); repeating it on the capture response —
	 * which is the one the browser navigates away from — ensures the buyer
	 * lands on the success page logged in.
	 *
	 * Deliberate side effect: for proxy-stripped buyers wp_signon() already
	 * fires wp_login server-side during create_order(); this call fires it a
	 * second time on capture. Duplicate audit-log entries and last-login stamps
	 * are the trade-off for reliable session delivery in that scenario.
	 *
	 * @since 3.6.9
	 * @since 3.7.3 Re-issues only for the user recorded at order creation, not the order's user_id.
	 *
	 * @param int $order_id EDD order ID.
	 * @return void
	 */
	public static function maybe_log_in_after_capture( $order_id ) {
		$authenticated_user_id = absint( EDD()->session->get( self::AUTHENTICATED_USER_KEY ) );

		// Single-use: consume the marker unconditionally, even on the early-returns
		// below, so it can never linger in the session for a later request.
		EDD()->session->set( self::AUTHENTICATED_USER_KEY, null );

		if ( is_user_logged_in() ) {
			return;
		}

		if ( empty( $authenticated_user_id ) ) {
			return;
		}

		$order = edd_get_order( $order_id );
		if ( ! $order || (int) $order->user_id !== $authenticated_user_id ) {
			return;
		}

		$wp_user = get_userdata( $authenticated_user_id );
		if ( $wp_user ) {
			wp_set_auth_cookie( $wp_user->ID );
			do_action( 'wp_login', $wp_user->user_login, $wp_user );
		}
	}
}
