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

class Validator {

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
				apply_filters( 'edd_agree_to_terms_text', __( 'You must agree to the terms of use', 'easy-digital-downloads' ) ),
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
				apply_filters( 'edd_agree_to_privacy_policy_text', __( 'You must agree to the privacy policy', 'easy-digital-downloads' ) ),
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
				__( 'The user information is invalid', 'easy-digital-downloads' ),
				'invalid_user'
			) );

			return;
		}

		$email = isset( $this->data['edd_email'] ) ? sanitize_email( $this->data['edd_email'] ) : $user->user_email;

		if ( ! is_email( $email ) ) {
			$this->errorCollection->add( new FormError(
				__( 'Invalid email', 'easy-digital-downloads' ),
				'email_invalid',
				'edd_email'
			) );
		}
	}

	private function validateGuestUser() {
		return;

		/*if ( isset( $this->data['edd-purchase-var'] ) && 'needs-to-register' === $this->data['edd-purchase-var'] ) {
			$this->validateNewUser();
		} elseif ( isset( $this->data['edd-purchase-var'] ) && 'needs-to-login' === $this->data['edd-purchase-var'] ) {
			$this->validateUserLogin();
		} else {
			$this->validateGuestCheckout();
		}*/
	}

}
