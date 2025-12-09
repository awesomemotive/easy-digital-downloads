<?php
/**
 * Abstract Captcha Provider class.
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
 * Abstract Captcha Provider class.
 *
 * @since 3.6.1
 */
abstract class Provider implements ProviderInterface {
	use \EDD\Admin\Settings\Traits\Helpers;

	/**
	 * Cached provider instances.
	 *
	 * @since 3.6.1
	 * @var array
	 */
	private static $providers = array();

	/**
	 * Get the script URL for the provider.
	 *
	 * @since 3.6.1
	 * @return string
	 */
	abstract protected function get_provider_script_url(): string;

	/**
	 * Get the script version for the provider.
	 *
	 * Return null to prevent WordPress from adding a version query parameter.
	 *
	 * @since 3.6.1
	 * @return string|null
	 */
	public function get_script_version(): ?string {
		return null;
	}

	/**
	 * Get the script URL for the provider.
	 *
	 * @since 3.6.1
	 * @return string
	 */
	public function get_script_url(): string {
		/**
		 * Filters the script URL for the provider.
		 *
		 * @since 3.6.1
		 * @param string $script_url The script URL.
		 * @param Provider $provider The provider instance.
		 */
		return apply_filters(
			"edd_captcha_provider_script_url_{$this->get_id()}",
			$this->get_provider_script_url(),
			$this
		);
	}

	/**
	 * Get all available providers.
	 *
	 * @since 3.6.1
	 * @return array Array of provider instances keyed by ID.
	 */
	public static function get_available_providers(): array {
		if ( empty( self::$providers ) ) {
			self::$providers = array(
				'recaptcha' => new Recaptcha(),
				'turnstile' => new Turnstile(),
			);

			/**
			 * Filters the available CAPTCHA providers.
			 *
			 * @since 3.6.1
			 * @param array $providers Array of provider instances keyed by ID.
			 */
			self::$providers = apply_filters( 'edd_captcha_providers', self::$providers );
		}

		return self::$providers;
	}

	/**
	 * Get a specific provider by ID.
	 *
	 * @since 3.6.1
	 * @param string $id The provider ID.
	 * @return Provider|null The provider instance or null if not found.
	 */
	public static function get_provider_by_id( string $id ): ?Provider {
		$providers = self::get_available_providers();

		return $providers[ $id ] ?? null;
	}

	/**
	 * Get the active provider based on settings.
	 *
	 * @since 3.6.1
	 * @return Provider|null The active provider instance or null if none configured.
	 */
	public static function get_active_provider(): ?Provider {
		$provider_id = edd_get_option( 'captcha_provider' );
		if ( 'none' === $provider_id ) {
			return null;
		}

		// Backwards compatibility: if no provider is set but reCAPTCHA keys exist, use reCAPTCHA.
		if ( empty( $provider_id ) ) {
			$recaptcha_site_key   = edd_get_option( 'recaptcha_site_key', '' );
			$recaptcha_secret_key = edd_get_option( 'recaptcha_secret_key', '' );

			if ( ! empty( $recaptcha_site_key ) && ! empty( $recaptcha_secret_key ) ) {
				$provider_id = 'recaptcha';
			}
		}

		if ( empty( $provider_id ) ) {
			return null;
		}

		$provider = self::get_provider_by_id( $provider_id );

		// Only return if the provider is properly configured.
		if ( $provider && $provider->is_configured() ) {
			return $provider;
		}

		return null;
	}

	/**
	 * Get the validated response from the API.
	 *
	 * @since 3.6.1
	 * @param string $response The response to validate.
	 * @return object|null The validated response or null if invalid.
	 */
	protected function get_response_body( $response ): ?object {
		$body = json_decode( wp_remote_retrieve_body( $response ) );

		if ( empty( $body ) || ! isset( $body->success ) || true !== $body->success ) {
			return null;
		}

		return $body;
	}

	/**
	 * Get the CSS class for a setting.
	 *
	 * @since 3.6.1
	 * @param array $additional_classes Additional classes to add to the CSS class.
	 * @return string The CSS class.
	 */
	protected function get_setting_css_class( $additional_classes = array() ) {
		return $this->get_requires_css_class( 'captcha_provider', $additional_classes, $this->get_id() );
	}
}
