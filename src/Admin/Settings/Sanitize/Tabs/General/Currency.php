<?php
/**
 * Sanitizes the Currency section.
 *
 * @since 3.3.3
 * @package EDD\Admin\Settings\Sanitize\Tabs\General
 */

namespace EDD\Admin\Settings\Sanitize\Tabs\General;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Admin\Settings\Sanitize\Tabs\Section;
use EDD\Settings\Setting;

/**
 * Sanitizes the currency section.
 *
 * @since 3.3.3
 */
class Currency extends Section {
	/**
	 * Sanitize the currency.
	 *
	 * @since 3.3.3
	 * @param string $value The currency.
	 *
	 * @return string
	 */
	protected static function sanitize_currency( $value ) {
		if ( empty( $value ) ) {
			return $value;
		}

		$registered_currencies = edd_get_currencies();
		$is_registered         = array_key_exists( $value, $registered_currencies );

		if ( ! $is_registered ) {
			$current_currency = Setting::get( 'currency', 'USD' );
			$value            = array_key_exists( $current_currency, $registered_currencies ) ? $current_currency : 'USD';
		}

		return $value;
	}
}
