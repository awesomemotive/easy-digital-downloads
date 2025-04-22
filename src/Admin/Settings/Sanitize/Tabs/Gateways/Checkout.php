<?php
/**
 * Sanitizes the Checkout section.
 *
 * @since 3.3.3
 * @package EDD\Admin\Settings\Sanitize\Tabs\Gateways
 */

namespace EDD\Admin\Settings\Sanitize\Tabs\Gateways;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Admin\Settings\Sanitize\Tabs\Section;

/**
 * Sanitizes the Checkout section.
 *
 * @since 3.3.3
 */
class Checkout extends Section {
	/**
	 * Sanitize the banned emails list.
	 *
	 * @since 3.3.3
	 * @param string $value The value to sanitize.
	 * @return string
	 */
	protected static function sanitize_banned_emails( $value ) {
		$emails = '';
		if ( ! empty( $value ) ) {
			// Sanitize the input.
			$emails = array_map( 'trim', explode( "\n", $value ) );
			$emails = array_unique( $emails );
			$emails = array_map( 'sanitize_text_field', $emails );

			foreach ( $emails as $id => $email ) {
				if ( ! is_email( $email ) && '@' !== $email[0] && '.' !== $email[0] ) {
					unset( $emails[ $id ] );
				}
			}

			// Before return, make sure the array is re-indexed.
			$emails = array_values( $emails );
		}

		return $emails;
	}

	/**
	 * Sanitize the address checkout fields.
	 *
	 * @since 3.3.8
	 * @param string $value The value to sanitize.
	 * @return string
	 */
	protected static function sanitize_checkout_address_fields( $value ) {
		if ( ! edd_use_taxes() ) {
			return $value;
		}

		// If taxes are enabled at all, we need to ensure the country field is always present.
		$value['country'] = 1;

		$has_regional_rates = edd_get_adjustments(
			array(
				'type'   => 'tax_rate',
				'scope'  => 'region',
				'status' => 'active',
				'number' => 1,
			)
		);

		if ( ! empty( $has_regional_rates ) ) {
			$value['state'] = 1;
		}

		return $value;
	}
}
