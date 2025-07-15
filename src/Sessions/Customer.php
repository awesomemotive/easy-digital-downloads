<?php
/**
 * Customer session handler.
 *
 * @package EDD\Sessions
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace EDD\Sessions;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Customer session handler.
 *
 * @since 3.5.0
 */
class Customer {

	/**
	 * Sets the customer data in the session.
	 *
	 * @since 3.5.0
	 * @param array $data The optional data to set in the session.
	 * @return array The session data.
	 */
	public static function set( array $data = array() ): array {

		$customer = false;
		if ( is_user_logged_in() ) {
			$customer = edd_get_customer_by( 'user_id', get_current_user_id() );
		}

		$session_data = wp_parse_args( $data, self::get_defaults() );
		if ( ! $customer ) {
			return self::maybe_set_customer_data( $session_data );
		}

		$name         = explode( ' ', $customer->name, 2 );
		$session_data = wp_parse_args(
			array(
				'customer_id' => $customer->id,
				'user_id'     => $customer->user_id,
				'name'        => $customer->name,
				'first_name'  => ! empty( $name[0] ) ? $name[0] : '',
				'last_name'   => ! empty( $name[1] ) ? $name[1] : '',
				'email'       => $customer->email,
			),
			$session_data
		);

		$session_data = array_map( 'sanitize_text_field', $session_data );

		return self::maybe_set_customer_data( $session_data );
	}

	/**
	 * Gets the customer data from the session.
	 *
	 * @since 3.5.0
	 * @return array
	 */
	public static function get(): array {
		$session_data = EDD()->session->get( 'customer' );
		if ( ! empty( $session_data ) ) {
			return $session_data;
		}

		if ( ! is_user_logged_in() ) {
			return self::get_defaults();
		}

		return self::set();
	}

	/**
	 * Sets customer data in the session if it contains meaningful values.
	 *
	 * @since 3.5.0
	 * @param array $session_data The session data to potentially set.
	 * @return array The session data that was set, or the original data if not set.
	 */
	private static function maybe_set_customer_data( array $session_data ): array {
		if ( is_user_logged_in() ) {
			$user_data = get_userdata( get_current_user_id() );

			// Map session data keys to proper WP_User properties and meta to avoid deprecation warnings.
			$user_mappings = array(
				'user_id'    => $user_data->ID,
				'email'      => $user_data->user_email,
				'name'       => $user_data->display_name,
				'first_name' => get_user_meta( $user_data->ID, 'first_name', true ),
				'last_name'  => get_user_meta( $user_data->ID, 'last_name', true ),
			);

			foreach ( $user_mappings as $key => $value ) {
				if ( empty( $session_data[ $key ] ) && ! empty( $value ) ) {
					$session_data[ $key ] = $value;
				}
			}
		}

		if ( empty( array_filter( $session_data ) ) ) {
			return $session_data;
		}

		return EDD()->session->set( 'customer', $session_data );
	}

	/**
	 * Gets the default customer data.
	 *
	 * @since 3.5.0
	 * @return array
	 */
	private static function get_defaults(): array {
		return array(
			'customer_id' => '',
			'user_id'     => '',
			'name'        => '',
			'first_name'  => '',
			'last_name'   => '',
			'email'       => '',
		);
	}
}
