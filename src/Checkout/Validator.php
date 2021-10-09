<?php
/**
 * Validator.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.x
 */

namespace EDD\Checkout;

use EDD\Checkout\Errors\ErrorCollection;
use EDD\Checkout\Errors\FormError;
use EDD\Checkout\Exceptions\ValidationException;
use EDD\Checkout\Traits\CollectsAccountInformation;

class Validator {

	use CollectsAccountInformation;

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @var ErrorCollection
	 */
	private $errorCollection;

	/**
	 * @param array $data Checkout form data.
	 */
	public function __construct( ErrorCollection $errorCollection ) {
		$this->errorCollection = $errorCollection;
	}

	/**
	 * Validates the entire checkout process.
	 *
	 * @throws ValidationException
	 */
	public function validate( Config $config, $data ) {
		$this->config = $config;
		$this->data   = $data;

		$this->validateFormFields();
		$this->validateUserData();

		if ( $this->errorCollection->hasErrors() ) {
			throw new ValidationException( $this->errorCollection );
		}
	}

	public function validateFormFields() {
		// Terms agreement.
		if (
			'1' === edd_get_option( 'show_agree_to_terms', false ) &&
			( ! isset( $this->data['edd_agree_to_terms'] ) || 1 != $this->data['edd_agree_to_terms'] )
		) {
			$this->errorCollection->add( new FormError(
				apply_filters( 'edd_agree_to_terms_text', __( 'You must agree to the terms of use.', 'easy-digital-downloads' ) ),
				'agree_to_terms',
				'edd_agree_to_terms'
			) );
		}

		// Privacy policy agreement.
		if (
			'1' === edd_get_option( 'show_agree_to_privacy_policy', false ) &&
			( ! isset( $this->data['edd_agree_to_privacy_policy'] ) || 1 != $this->data['edd_agree_to_privacy_policy'] )
		) {
			$this->errorCollection->add( new FormError(
				apply_filters( 'edd_agree_to_privacy_policy_text', __( 'You must agree to the privacy policy.', 'easy-digital-downloads' ) ),
				'agree_to_privacy_policy',
				'edd_agree_to_privacy_policy'
			) );
		}

		// Required fields.
		foreach ( edd_purchase_form_required_fields() as $field_name => $value ) {
			if ( empty( $this->data[ $field_name ] ) && ! empty( $value['error_id'] ) && ! empty( $value['error_message'] ) ) {
				$this->errorCollection->add( new FormError(
					$value['error_message'],
					$value['error_id'],
					$field_name
				) );
			}
		}
	}

	public function validateUserData() {
		if ( is_user_logged_in() ) {
			$this->validateLoggedInUser();
		} else {
			$this->validateGuestUser();
		}
	}

	/**
	 * @see edd_purchase_form_validate_logged_in_user()
	 */
	private function validateLoggedInUser() {
		$user = wp_get_current_user();
		if ( ! $user->ID ) {
			$this->errorCollection->add( new FormError(
				__( 'The user information is invalid.', 'easy-digital-downloads' ),
				'invalid_user'
			) );

			return;
		}

		$email = isset( $this->data['edd_email'] ) ? sanitize_email( $this->data['edd_email'] ) : $user->user_email;

		if ( ! is_email( $email ) ) {
			$this->errorCollection->add( new FormError(
				__( 'Invalid email.', 'easy-digital-downloads' ),
				'email_invalid',
				'edd_email'
			) );
		}
	}

	/**
	 * Validates user information for customers who are logged out.
	 * This includes both customers checking out as a guest, and those
	 * creating a new account during registration.
	 */
	private function validateGuestUser() {
		$accountInfo     = $this->getNewAccountInformation( $this->data );

		/*
		 * Validate email address.
		 * This is always required, including for guests, so we check it first.
		 */
		if ( ! empty( $accountInfo['user_email'] ) ) {
			if ( ! is_email( $accountInfo['user_email'] ) ) {
				$this->errorCollection->add( new FormError(
					__( 'Invalid email.', 'easy-digital-downloads' ),
					'email_invalid',
					'edd_email'
				) );
			} elseif ( is_multisite() && is_email_address_unsafe( $accountInfo['user_email'] ) ) {
				$this->errorCollection->add( new FormError(
					__( 'You cannot use that email address to signup at this time.', 'easy-digital-downloads' ),
					'email_unsafe',
					'edd_email'
				) );
			} elseif ( ! $this->isGuestCheckout( $this->data ) && email_exists( $accountInfo['user_email'] ) ) {
				$this->errorCollection->add( new FormError(
					__( 'Email already used. Login or use a different email to complete your purchase.', 'easy-digital-downloads' ),
					'email_used',
					'edd_email'
				) );
			}
		} else {
			$this->errorCollection->add( new FormError(
				__( 'Enter an email.', 'easy-digital-downloads' ),
				'email_empty',
				'edd_email'
			) );
		}

		// Guests don't need any further validation.
		if ( $this->isGuestCheckout( $this->data ) ) {
			if ( $this->config->allowGuestCheckout ) {
				$this->errorCollection->add( new FormError(
					__( 'You must register or login to complete your purchase', 'easy-digital-downloads' ),
					'registration_required'
				) );
			}

			return;
		}

		/*
		 * Validate username.
		 */
		if ( username_exists( $accountInfo['user_login'] ) ) {
			$this->errorCollection->add( new FormError(
				__( 'Username already exists.', 'easy-digital-downloads' ),
				'username_unavailable',
				'edd_user_login'
			) );
		} elseif ( ! edd_validate_username( $accountInfo['user_login'] ) ) {
			$message = is_multisite()
				? __( 'Invalid username. Only lowercase letters (a-z) and numbers are allowed.', 'easy-digital-downloads' )
				: __( 'Invalid username.', 'easy-digital-downloads' );

			$this->errorCollection->add( new FormError(
				$message,
				'username_invalid',
				'edd_user_login'
			) );
		}

		/*
		 * Validate password.
		 */
		if ( empty( $accountInfo['user_pass'] ) ) {
			$this->errorCollection->add( new FormError(
				__( 'Enter a password.', 'easy-digital-downloads' ),
				'password_empty',
				'edd_user_pass'
			) );
		} elseif ( empty( $accountInfo['pass_confirm'] ) ) {
			$this->errorCollection->add( new FormError(
				__( 'Confirm your password.', 'easy-digital-downloads' ),
				'confirmation_empty',
				'edd_user_pass_confirm'
			) );
		} elseif ( 0 !== strcmp( $accountInfo['user_pass'], $accountInfo['pass_confirm'] ) ) {
			$this->errorCollection->add( new FormError(
				__( 'Passwords do not match.', 'easy-digital-downloads' ),
				'password_mismatch',
				'edd_user_pass'
			) );
		}
	}

}
