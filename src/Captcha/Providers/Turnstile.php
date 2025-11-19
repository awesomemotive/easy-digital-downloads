<?php
/**
 * Cloudflare Turnstile Provider class.
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
 * Cloudflare Turnstile Provider class.
 *
 * @since 3.6.1
 */
class Turnstile extends Provider {

	/**
	 * Get the provider ID.
	 *
	 * @since 3.6.1
	 * @return string
	 */
	public function get_id(): string {
		return 'turnstile';
	}

	/**
	 * Get the provider name.
	 *
	 * @since 3.6.1
	 * @return string
	 */
	public function get_name(): string {
		return __( 'Cloudflare Turnstile', 'easy-digital-downloads' );
	}

	/**
	 * Get the provider site key.
	 *
	 * @since 3.6.1
	 * @return string
	 */
	public function get_key(): string {
		return edd_get_option( 'turnstile_site_key', '' );
	}

	/**
	 * Get the provider secret key.
	 *
	 * @since 3.6.1
	 * @return string
	 */
	public function get_secret_key(): string {
		return edd_get_option( 'turnstile_secret_key', '' );
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
		return 'https://challenges.cloudflare.com/turnstile/v0/api.js';
	}

	/**
	 * Get the script handle for the provider.
	 *
	 * @since 3.6.1
	 * @return string
	 */
	public function get_script_handle(): string {
		return 'cloudflare-turnstile';
	}

	/**
	 * Get the script version for the provider.
	 *
	 * Turnstile doesn't accept query parameters, so return null to prevent versioning.
	 *
	 * @since 3.6.1
	 * @return string|null
	 */
	public function get_script_version(): ?string {
		return null;
	}

	/**
	 * Get the handler script URL for the provider.
	 *
	 * @since 3.6.1
	 * @return string
	 */
	public function get_handler_script_url(): string {
		return EDD_PLUGIN_URL . 'assets/js/captcha/turnstile-handler.js';
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
			'https://challenges.cloudflare.com/turnstile/v0/siteverify',
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
					__( '%1$sRegister with Cloudflare%2$s to get Turnstile keys. Setting the keys here will enable Turnstile on your registration block and when a user requests a password reset using the login block.', 'easy-digital-downloads' ),
					'<a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank">',
					'</a>'
				),
				'type'  => 'descriptive_text',
				'class' => $css_class,
			),
			"{$provider_id}_site_key"   => array(
				'id'    => "{$provider_id}_site_key",
				'name'  => __( 'Turnstile Site Key', 'easy-digital-downloads' ),
				'type'  => 'text',
				'std'   => '',
				'class' => $css_class,
			),
			"{$provider_id}_secret_key" => array(
				'id'    => "{$provider_id}_secret_key",
				'name'  => __( 'Turnstile Secret Key', 'easy-digital-downloads' ),
				'type'  => 'password',
				'std'   => '',
				'class' => $css_class,
			),
		);
	}
}
