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
}
