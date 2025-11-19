<?php
/**
 * Captcha Provider Interface.
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
 * Captcha Provider Interface.
 *
 * @since 3.6.1
 */
interface ProviderInterface {

	/**
	 * Get the provider ID.
	 *
	 * @since 3.6.1
	 * @return string
	 */
	public function get_id(): string;

		/**
		 * Get the provider name.
		 *
		 * @since 3.6.1
		 * @return string
		 */
	public function get_name(): string;

	/**
	 * Get the provider site key.
	 *
	 * @since 3.6.1
	 * @return string
	 */
	public function get_key(): string;

	/**
	 * Get the provider secret key.
	 *
	 * @since 3.6.1
	 * @return string
	 */
	public function get_secret_key(): string;

	/**
	 * Check if the provider is configured.
	 *
	 * @since 3.6.1
	 * @return bool
	 */
	public function is_configured(): bool;

	/**
	 * Get the script handle for the provider.
	 *
	 * @since 3.6.1
	 * @return string
	 */
	public function get_script_handle(): string;

	/**
	 * Get the handler script URL for the provider.
	 *
	 * The handler script contains provider-specific logic for executing
	 * the CAPTCHA and should define the window.EDDCaptchaHandler interface.
	 *
	 * @since 3.6.1
	 * @return string
	 */
	public function get_handler_script_url(): string;

	/**
	 * Validate the captcha response.
	 *
	 * @since 3.6.1
	 * @param string $response The response to validate.
	 * @return bool|array True if valid, error array if invalid.
	 */
	public function validate( $response );

	/**
	 * Get the provider-specific settings.
	 *
	 * @since 3.6.1
	 * @return array Array of settings for this provider.
	 */
	public function get_settings(): array;
}
