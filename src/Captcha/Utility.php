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
	 * Evaluates whether the checkout recaptcha should be displayed.
	 *
	 * @since 3.5.3
	 * @return bool
	 */
	public static function can_do_captcha(): bool {
		$recaptcha_enabled = edd_get_option( 'recaptcha_checkout', '' );
		if ( empty( $recaptcha_enabled ) ) {
			return false;
		}

		$can_do_captcha = true;
		if ( 'guests' === $recaptcha_enabled && is_user_logged_in() ) {
			$can_do_captcha = false;
		}

		/**
		 * Filters whether the recaptcha should be displayed on the checkout page.
		 *
		 * @since 3.5.3
		 * @param bool   $can_do_captcha  Whether the recaptcha should be displayed.
		 * @param string $recaptcha_enabled The recaptcha enabled setting.
		 */
		return (bool) apply_filters( 'edd_can_recaptcha_checkout', $can_do_captcha, $recaptcha_enabled );
	}
}
