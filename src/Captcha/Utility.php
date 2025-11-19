<?php
/**
 * EDD Captcha Utility
 *
 * @package     EDD\Captcha
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.3
 */

namespace EDD\Captcha;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Utility class.
 *
 * @since 3.5.3
 */
class Utility {

	/**
	 * Evaluates whether the checkout CAPTCHA should be displayed.
	 *
	 * @since 3.5.3
	 * @return bool
	 */
	public static function can_do_captcha(): bool {
		// Check if a provider is configured.
		$provider = \EDD\Captcha\Providers\Provider::get_active_provider();
		if ( ! $provider ) {
			return false;
		}

		$captcha_enabled = edd_get_option( 'recaptcha_checkout', '' );
		if ( empty( $captcha_enabled ) ) {
			return false;
		}

		$can_do_captcha = true;
		if ( 'guests' === $captcha_enabled && is_user_logged_in() ) {
			$can_do_captcha = false;
		}

		/**
		 * Filters whether the CAPTCHA should be displayed on the checkout page.
		 *
		 * @since 3.6.1
		 * @param bool     $can_do_captcha Whether the CAPTCHA should be displayed.
		 * @param string   $captcha_enabled The CAPTCHA enabled setting.
		 * @param Provider $provider        The active CAPTCHA provider.
		 */
		$can_do_captcha = (bool) apply_filters( 'edd_can_captcha_checkout', $can_do_captcha, $captcha_enabled, $provider );

		/**
		 * Backwards compatibility filter for old filter name.
		 *
		 * @since 3.5.3
		 * @deprecated 3.6.0 Use 'edd_can_captcha_checkout' instead.
		 * @param bool   $can_do_captcha   Whether the recaptcha should be displayed.
		 * @param string $captcha_enabled The recaptcha enabled setting.
		 */
		return (bool) apply_filters_deprecated( 'edd_can_recaptcha_checkout', array( $can_do_captcha, $captcha_enabled ), '3.6.1', 'edd_can_captcha_checkout' );
	}
}
