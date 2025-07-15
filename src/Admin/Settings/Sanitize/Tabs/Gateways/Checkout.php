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

		// If there are regional tax rates, we need to ensure the state field is always present.
		if ( self::has_regional_rates() ) {
			$value['state'] = 1;
		}

		return $value;
	}

	/**
	 * Sanitize the empty cart behavior.
	 *
	 * @since 3.5.0
	 * @param array $value The value to sanitize.
	 * @return array The sanitized value.
	 */
	protected static function sanitize_empty_cart_behavior( $value ) {
		$provider = edd_get_namespace( 'Admin\\Settings\\EmptyCartBehavior' );

		return $provider::validate_empty_cart_behavior( $value );
	}

	/**
	 * Sanitize the empty cart message.
	 *
	 * @since 3.5.0
	 * @param string $value The value to sanitize.
	 * @return string The sanitized value.
	 */
	public static function sanitize_empty_cart_message( $value ) {
		$provider = edd_get_namespace( 'Admin\\Settings\\EmptyCartBehavior' );

		return $provider::validate_empty_cart_message( $value );
	}

	/**
	 * Sanitize the empty cart redirect page.
	 *
	 * @since 3.5.0
	 * @param string $value The value to sanitize.
	 * @return string The sanitized value.
	 */
	public static function sanitize_empty_cart_redirect_page( $value ) {
		$provider = edd_get_namespace( 'Admin\\Settings\\EmptyCartBehavior' );

		return $provider::validate_empty_cart_redirect_page( $value );
	}

	/**
	 * Sanitize the empty cart redirect URL.
	 *
	 * @since 3.5.0
	 * @param string $value The value to sanitize.
	 * @return string The sanitized value.
	 */
	public static function sanitize_empty_cart_redirect_url( $value ) {
		$provider = edd_get_namespace( 'Admin\\Settings\\EmptyCartBehavior' );

		return $provider::validate_empty_cart_redirect_url( $value );
	}

	/**
	 * Checks if there are any active regional tax rates.
	 *
	 * @since 3.5.0
	 * @return bool
	 */
	private static function has_regional_rates() {
		$tax_rates = new \EDD\Database\Queries\TaxRate();

		return ! empty(
			$tax_rates->query(
				array(
					'scope'  => 'region',
					'status' => 'active',
					'number' => 1,
				)
			)
		);
	}
}
