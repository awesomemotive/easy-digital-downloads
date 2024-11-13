<?php

namespace EDD\Checkout;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\Subscriber;

/**
 * Errors class.
 */
class Errors extends Subscriber {

	/**
	 * Gets the events this subscriber should be subscribed to.
	 *
	 * @since 3.3.5
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'edd_checkout_user_error_checks' => array( 'check_existing_users', 10, 3 ),
			'wp_ajax_nopriv_edd_check_email' => 'check_email_ajax',
		);
	}

	/**
	 * Checks if a user already exists during checkout.
	 *
	 * @since 3.3.5
	 * @param mixed $user       The user object.
	 * @param array $valid_data The valid data.
	 * @param array $posted     The posted data.
	 * @return void
	 */
	public function check_existing_users( $user, $valid_data, $posted ) {
		if ( is_user_logged_in() || empty( $user ) ) {
			return;
		}

		// If the email has already been validated, skip this check.
		if ( EDD()->session->get( 'email_validated' ) ) {
			return;
		}

		$email = false;
		if ( ! empty( $valid_data['guest_user_data']['user_email'] ) ) {
			$email = $valid_data['guest_user_data']['user_email'];
		} elseif ( ! empty( $posted['edd_email'] ) ) {
			$email = $posted['edd_email'];
		}
		if ( ! $email ) {
			return;
		}

		$validate = $this->validate_email( $email );
		if ( is_wp_error( $validate ) ) {
			edd_set_error( $validate->get_error_code(), $validate->get_error_message() );
		}
	}

	/**
	 * Checks if the email is valid and not already used.
	 *
	 * @since 3.3.5
	 * @return void
	 */
	public function check_email_ajax() {
		EDD()->session->set( 'email_validated', null );
		$email    = sanitize_email( $_POST['email'] );
		$validate = $this->validate_email( $email );
		if ( is_wp_error( $validate ) ) {
			wp_send_json_error( array( 'message' => $validate->get_error_message() ) );
		}

		EDD()->session->set( 'email_validated', $email );

		wp_send_json_success();
	}

	/**
	 * Validates an email address.
	 *
	 * @since 3.3.5
	 * @param string $email The email address to validate.
	 * @return bool|\WP_Error
	 */
	private function validate_email( $email ) {
		if ( ! is_email( $email ) ) {
			return new \WP_Error( 'invalid_email', __( 'Please enter a valid email address.', 'easy-digital-downloads' ) );
		}

		$user = get_user_by( 'email', $email );
		if ( $user ) {
			return new \WP_Error( 'email_used', __( 'Email already used. Login or use a different email to complete your purchase.', 'easy-digital-downloads' ) );
		}

		return true;
	}
}
