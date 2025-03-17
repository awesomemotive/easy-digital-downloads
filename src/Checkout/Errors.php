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
			'wp_ajax_edd_check_email'        => 'check_email_ajax',
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
	 * @return void|bool|\WP_Error
	 */
	public function check_email_ajax() {
		EDD()->session->set( 'email_validated', null );
		$email = sanitize_email( $_POST['email'] );
		if ( is_user_logged_in() ) {
			$validate = $this->validate_logged_in_email( $email );
		} else {
			$validate = $this->validate_email( $email );
		}

		if ( edd_is_doing_unit_tests() ) {
			return $validate;
		}

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

		// If there is no user with this email, it's valid.
		$user = get_user_by( 'email', $email );
		if ( ! $user ) {
			return true;
		}

		// If there isn't a customer with this email, and guest checkout is enabled, it's valid.
		$customer = edd_get_customer_by( 'user_id', $user->ID );
		if ( ! $customer && empty( edd_get_option( 'logged_in_only', '' ) ) ) {
			return true;
		}

		return new \WP_Error( 'email_used', __( 'Email already used. Login or use a different email to complete your purchase.', 'easy-digital-downloads' ) );
	}

	/**
	 * Validates an email address for a logged in user.
	 *
	 * @since 3.3.7
	 * @param string $email The email address to validate.
	 * @return bool|\WP_Error
	 */
	private function validate_logged_in_email( $email ) {
		if ( ! is_email( $email ) ) {
			return new \WP_Error( 'invalid_email', __( 'Please enter a valid email address.', 'easy-digital-downloads' ) );
		}

		$user       = wp_get_current_user();
		$user_check = get_user_by( 'email', $email );
		if ( $user_check && (int) $user_check->ID !== (int) $user->ID ) {
			return new \WP_Error( 'email_used', __( 'Email already used. Log in or use a different email to complete your purchase.', 'easy-digital-downloads' ) );
		}

		$user_email      = $user->user_email;
		$emails_to_check = array_unique(
			array(
				$email,
				strtolower( $email ),
				$user_email,
				strtolower( $user_email ),
			)
		);

		$customer = edd_get_customer_by( 'user_id', get_current_user_id() );

		// If the current user has a customer record and the email address matches, we're good to go.
		if ( ! empty( $customer->email ) && in_array( strtolower( $customer->email ), $emails_to_check, true ) ) {
			return true;
		}

		$email_args = array(
			'email__in' => $emails_to_check,
		);
		if ( $customer ) {
			$email_args['customer_id__not_in'] = array( $customer->id );
		}
		$matching_emails = edd_get_customer_email_addresses( $email_args );
		if ( empty( $matching_emails ) ) {
			return true;
		}

		$existing_customer = false;
		// Check if any of the matching emails belong to an existing customer.
		foreach ( $matching_emails as $matching_email ) {
			$email_customer = edd_get_customer( $matching_email->customer_id );
			if ( $email_customer && (int) $email_customer->user_id !== (int) $user->ID ) {
				$existing_customer = true;
				break;
			}
		}

		if ( ! $existing_customer ) {
			return true;
		}

		return new \WP_Error(
			'edd-customer-email-exists',
			/* translators: %s: email address */
			sprintf( __( 'The email address %s is already in use.', 'easy-digital-downloads' ), $email )
		);
	}
}
