<?php

namespace EDD\Captcha;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\EventManagement\SubscriberInterface;

/**
 * Validate class.
 *
 * @since 3.5.3
 */
class Validate implements SubscriberInterface {

	/**
	 * The session key for the validated tokens.
	 *
	 * @since 3.5.3
	 * @var string
	 */
	private const SESSION_KEY = 'captcha_validated_tokens';

	/**
	 * Gets the subscribed events.
	 *
	 * @since 3.5.3
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'wp_ajax_edd_recaptcha_validate'        => 'validate',
			'wp_ajax_nopriv_edd_recaptcha_validate' => 'validate',
			'edd_pre_process_purchase'              => array( 'validate', 4 ),
		);
	}

	/**
	 * Validates the reCAPTCHA response.
	 *
	 * @since 3.5.3
	 * @return void
	 */
	public function validate() {
		$doing_checkout = doing_action( 'edd_pre_process_purchase' );
		if ( $doing_checkout ) {
			// Only validate on ajax requests.
			if ( ! edd_doing_ajax() ) {
				return;
			}
			if ( ! Utility::can_do_captcha() ) {
				return;
			}
		}

		$token = $this->get_token( $doing_checkout );
		if ( ! $token ) {
			$this->set_error( 'invalid_recaptcha_missing' );
			return;
		}

		$token_hash       = md5( $token );
		$validated_tokens = $this->get_validated_tokens();
		if ( isset( $validated_tokens[ $token_hash ] ) ) {
			$cached_result = $validated_tokens[ $token_hash ]['result'];
			if ( $doing_checkout && true !== $cached_result ) {
				edd_set_error( 'recaptcha_invalid', $cached_result['message'] );
			}
			return;
		}

		try {
			$args = array(
				'headers' => array(
					'Content-type' => 'application/x-www-form-urlencoded',
				),
				'body'    => array(
					'secret'   => edd_get_option( 'recaptcha_secret_key', false ),
					'response' => $token,
					'remoteip' => edd_get_ip(),
				),
			);

			$validated = $this->validate_recaptcha(
				wp_safe_remote_post(
					'https://www.google.com/recaptcha/api/siteverify',
					$args
				)
			);

			// Store the validation result in the session.
			$validated_tokens[ $token_hash ] = array(
				'result'    => $validated,
				'timestamp' => time(),
			);
			EDD()->session->set( self::SESSION_KEY, $validated_tokens );

			if ( $doing_checkout ) {
				if ( true !== $validated ) {
					edd_set_error( 'recaptcha_invalid', $validated['message'] );
				}
				return;
			}

			// No errors with data validation.
			if ( true === $validated ) {
				wp_send_json_success(
					array( 'success' => true )
				);
			} else {
				wp_send_json_error( $validated );
			}
		} catch ( \Exception $e ) {
			$this->set_error( 'invalid_recaptcha_bad' );
		}
	}

	/**
	 * Gets the validated tokens from the session.
	 * The session is used to cache the validation results to prevent duplicate requests.
	 *
	 * @since 3.5.3
	 * @return array
	 */
	protected function get_validated_tokens(): array {
		$validated_tokens = EDD()->session->get( self::SESSION_KEY );
		if ( ! is_array( $validated_tokens ) ) {
			return array();
		}

		// Clean up expired tokens (older than 2 minutes - reCAPTCHA tokens expire after 2 minutes).
		$current_time     = time();
		$validated_tokens = array_filter(
			$validated_tokens,
			function ( $data ) use ( $current_time ) {
				return isset( $data['timestamp'] ) && ( $current_time - $data['timestamp'] ) < 120;
			}
		);

		return $validated_tokens;
	}

	/**
	 * Gets the reCAPTCHA token from the request.
	 *
	 * @since 3.5.3
	 * @param bool $doing_checkout Whether the request is being made during checkout.
	 * @return string|false
	 */
	private function get_token( $doing_checkout ) {
		if ( $doing_checkout ) {
			return ! empty( $_POST['edd-blocks-recaptcha'] ) ? trim( sanitize_text_field( $_POST['edd-blocks-recaptcha'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification
		}

		if ( ! empty( $_POST['token'] ) ) {
			return trim( sanitize_text_field( $_POST['token'] ) );
		}

		return false;
	}

	/**
	 * Evaluates the reCAPTCHA response.
	 *
	 * @since 3.5.3
	 * @param array|\WP_Error $response The response from the reCAPTCHA API.
	 * @return bool
	 */
	private function validate_recaptcha( $response ) {
		if ( is_wp_error( $response ) ) {
			return $this->set_error( 'invalid_recaptcha_bad' );
		}

		$verify = json_decode( wp_remote_retrieve_body( $response ) );
		if ( true !== $verify->success ) {
			return $this->set_error( 'invalid_recaptcha_failed' );
		}

		if ( isset( $verify->score ) && (float) $verify->score < 0.5 ) {
			return $this->set_error( 'invalid_recaptcha_low_score' );
		}

		return true;
	}

	/**
	 * Set up the reCAPTCHA error.
	 *
	 * @since 3.5.3
	 * @param string $error_code The error code.
	 * @return void|false
	 */
	private function set_error( $error_code ) {
		if ( edd_doing_ajax() ) {
			wp_send_json_error(
				array(
					'error'   => $error_code,
					'message' => $this->get_error_message( $error_code ),
				)
			);
		}

		edd_set_error( $error_code, $this->get_error_message( $error_code ) );
		return false;
	}

	/**
	 * Gets the error message from the error code.
	 *
	 * @since 3.5.3
	 * @param string $error_code The error code.
	 * @return string
	 */
	private function get_error_message( $error_code ) {
		switch ( $error_code ) {
			case 'invalid_recaptcha_missing':
				$message = __( 'reCAPTCHA validation missing.', 'easy-digital-downloads' );
				break;

			case 'invalid_recaptcha_bad':
				$message = __( 'Unexpected reCAPTCHA error. Please try again.', 'easy-digital-downloads' );
				break;

			case 'invalid_recaptcha_failed':
				$message = __( 'reCAPTCHA verification failed. Please contact a site administrator.', 'easy-digital-downloads' );
				break;

			case 'invalid_recaptcha_low_score':
				$message = __( 'reCAPTCHA verification failed with low score. Please contact a site administrator.', 'easy-digital-downloads' );
				break;

			default:
				$message = __( 'There was an error validating the reCAPTCHA. Please try again.', 'easy-digital-downloads' );
		}

		return $message;
	}
}
