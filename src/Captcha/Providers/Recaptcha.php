<?php
/**
 * reCAPTCHA Provider class.
 *
 * @package EDD\Captcha\Providers
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.1
 */

namespace EDD\Captcha\Providers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * reCAPTCHA Provider class.
 *
 * @since 3.6.1
 */
class Recaptcha extends Provider {

	/**
	 * Get the provider ID.
	 *
	 * @since 3.6.1
	 * @return string
	 */
	public function get_id(): string {
		return 'recaptcha';
	}

	/**
	 * Get the provider name.
	 *
	 * @since 3.6.1
	 * @return string
	 */
	public function get_name(): string {
		return __( 'reCAPTCHA v3', 'easy-digital-downloads' );
	}

	/**
	 * Get the provider site key.
	 *
	 * @since 3.6.1
	 * @return string
	 */
	public function get_key(): string {
		return edd_get_option( 'recaptcha_site_key', '' );
	}

	/**
	 * Get the provider secret key.
	 *
	 * @since 3.6.1
	 * @return string
	 */
	public function get_secret_key(): string {
		return edd_get_option( 'recaptcha_secret_key', '' );
	}

	/**
	 * Check if the provider is configured.
	 *
	 * @since 3.6.1
	 * @return bool
	 */
	public function is_configured(): bool {
		return ! empty( $this->get_key() ) && ! empty( $this->get_secret_key() );
	}

	/**
	 * Get the script URL for the provider.
	 *
	 * @since 3.6.1
	 * @return string
	 */
	protected function get_provider_script_url(): string {
		return add_query_arg(
			array(
				'render' => $this->get_key(),
			),
			'https://www.google.com/recaptcha/api.js'
		);
	}

	/**
	 * Get the script handle for the provider.
	 *
	 * @since 3.6.1
	 * @return string
	 */
	public function get_script_handle(): string {
		return 'google-recaptcha';
	}

	/**
	 * Get the script version for the provider.
	 *
	 * @since 3.6.1
	 * @return string|null
	 */
	public function get_script_version(): ?string {
		return '3';
	}

	/**
	 * Get the handler script URL for the provider.
	 *
	 * @since 3.6.1
	 * @return string
	 */
	public function get_handler_script_url(): string {
		return EDD_PLUGIN_URL . 'assets/js/captcha/recaptcha-handler.js';
	}

	/**
	 * Validate the captcha response.
	 *
	 * @since 3.6.1
	 * @param string $response The response token to validate.
	 * @return bool|array True if valid, error array if invalid.
	 */
	public function validate( $response ) {
		$args = array(
			'headers' => array(
				'Content-type' => 'application/x-www-form-urlencoded',
			),
			'body'    => array(
				'secret'   => $this->get_secret_key(),
				'response' => $response,
				'remoteip' => edd_get_ip(),
			),
		);

		$api_response = wp_safe_remote_post(
			'https://www.google.com/recaptcha/api/siteverify',
			$args
		);

		if ( is_wp_error( $api_response ) ) {
			return array(
				'error'   => 'invalid_captcha_bad',
				'message' => __( 'Unexpected CAPTCHA error. Please try again.', 'easy-digital-downloads' ),
			);
		}

		$verify = $this->get_response_body( $api_response );
		if ( ! $verify ) {
			return array(
				'error'   => 'invalid_captcha_failed',
				'message' => __( 'CAPTCHA verification failed. Please contact a site administrator.', 'easy-digital-downloads' ),
			);
		}

		// Check score for reCAPTCHA v3.
		if ( isset( $verify->score ) && (float) $verify->score < 0.5 ) {
			return array(
				'error'   => 'invalid_captcha_low_score',
				'message' => __( 'CAPTCHA verification failed with low score. Please contact a site administrator.', 'easy-digital-downloads' ),
			);
		}

		return true;
	}

	/**
	 * Get the provider-specific settings.
	 *
	 * @since 3.6.1
	 * @return array Array of settings for this provider.
	 */
	public function get_settings(): array {
		$provider_id = $this->get_id();
		$css_class   = $this->get_setting_css_class();

		return array(
			$provider_id                => array(
				'id'    => $provider_id,
				'name'  => $this->get_name(),
				'desc'  => sprintf(
					/* translators: 1. opening anchor tag; 2. closing anchor tag */
					__( '%1$sRegister with Google%2$s to get reCAPTCHA v3 keys. Setting the keys here will enable reCAPTCHA on your registration block and when a user requests a password reset using the login block.', 'easy-digital-downloads' ),
					'<a href="https://www.google.com/recaptcha/admin#list" target="_blank">',
					'</a>'
				),
				'type'  => 'descriptive_text',
				'class' => $css_class,
			),
			"{$provider_id}_site_key"   => array(
				'id'    => "{$provider_id}_site_key",
				'name'  => __( 'reCAPTCHA Site Key', 'easy-digital-downloads' ),
				'type'  => 'text',
				'class' => $css_class,
			),
			"{$provider_id}_secret_key" => array(
				'id'    => "{$provider_id}_secret_key",
				'name'  => __( 'reCAPTCHA Secret Key', 'easy-digital-downloads' ),
				'type'  => 'password',
				'class' => $css_class,
			),
		);
	}
}
