<?php
/**
 * Discounts Manager.
 *
 * @package EDD\Admin\Discounts
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.3.9
 */

namespace EDD\Admin\Discounts;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;

/**
 * Discounts Manager.
 *
 * @since 3.3.9
 */
class Manager implements SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'edd_add_discount'  => 'add',
			'edd_edit_discount' => 'update',
		);
	}

	/**
	 * Adds a discount.
	 *
	 * @since 3.3.9
	 * @param array $data Data.
	 */
	public function add( $data = array() ) {
		if ( ! $this->can_edit_discount( $data ) ) {
			wp_die( __( 'You do not have permission to add discount codes.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		if ( $this->discount_code_exists( $data['code'] ) ) {
			edd_redirect(
				add_query_arg(
					'edd-message',
					'discount_exists',
					edd_get_admin_url(
						array(
							'page' => 'edd-discounts',
							'view' => 'add_discount',
						)
					)
				)
			);
		}

		$to_add  = $this->validate_form_data( $data );
		$created = edd_add_discount( $to_add );
		$arg     = ! empty( $created )
			? 'discount_added'
			: 'discount_add_failed';

		edd_redirect(
			edd_get_admin_url(
				array(
					'page'        => 'edd-discounts',
					'view'        => 'edit_discount',
					'discount'    => absint( $created ),
					'edd-message' => sanitize_key( $arg ),
				)
			)
		);
	}

	/**
	 * Updates a discount.
	 *
	 * @since 3.3.9
	 * @param array $data Data.
	 */
	public function update( $data = array() ) {

		if ( ! $this->can_edit_discount( $data ) ) {
			wp_die( __( 'You do not have permission to edit discount codes.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		$discount = $this->get_discount( $data );
		if ( ! $discount ) {
			wp_die( __( 'Invalid discount.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		if ( $this->discount_code_exists( $data['code'], $discount->id ) ) {
			edd_redirect(
				add_query_arg(
					'edd-message',
					'discount_exists',
					edd_get_admin_url(
						array(
							'page'     => 'edd-discounts',
							'view'     => 'edit_discount',
							'discount' => absint( $discount->id ),
						)
					)
				)
			);
		}

		$to_update = $this->validate_form_data( $data );

		// Attempt to update.
		$updated = edd_update_discount( $discount->id, $to_update );
		$arg     = ! empty( $updated )
			? 'discount_updated'
			: 'discount_not_changed';

		edd_redirect(
			edd_get_admin_url(
				array(
					'page'        => 'edd-discounts',
					'view'        => 'edit_discount',
					'discount'    => absint( $discount->id ),
					'edd-message' => sanitize_key( $arg ),
				)
			)
		);
	}

	/**
	 * Checks if the current user can edit a discount.
	 *
	 * @since 3.3.9
	 * @param array $data Data.
	 * @return bool
	 */
	private function can_edit_discount( $data ) {
		// Bail if no nonce or nonce fails.
		if ( ! isset( $data['edd-discount-nonce'] ) || ! wp_verify_nonce( $data['edd-discount-nonce'], 'edd_discount_nonce' ) ) {
			wp_die( __( 'Invalid nonce', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		// Bail if current user cannot manage shop discounts.
		if ( ! current_user_can( 'manage_shop_discounts' ) ) {
			wp_die( __( 'You do not have permission to edit discount codes', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		$sanitized_amount = (float) edd_sanitize_amount( $data['amount'] );
		if ( empty( $data['amount'] ) || 0.00 === $sanitized_amount ) {
			edd_redirect( add_query_arg( 'edd-message', 'discount_invalid_amount' ) );
		}

		return true;
	}

	/**
	 * Gets a discount.
	 *
	 * @since 3.3.9
	 * @param array $data Data.
	 * @return \EDD_Discount
	 */
	private function get_discount( $data ) {
		// Bail if discount does not exist.
		if ( empty( $data['discount-id'] ) ) {
			wp_die( __( 'No discount ID supplied', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		// Setup default discount values.
		$discount_id = absint( $data['discount-id'] );
		$discount    = edd_get_discount( $discount_id );

		// Bail if no discount.
		if ( empty( $discount ) || ( $discount->id <= 0 ) ) {
			wp_die( __( 'Invalid discount', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		return $discount;
	}

	/**
	 * Validates form data.
	 *
	 * @since 3.3.9
	 * @param array $data Data.
	 * @return array
	 */
	private function validate_form_data( $data = array() ) {

		// Set the update defaults.
		$validated_data = array(
			'min_charge_amount' => 0,
			'max_uses'          => 0,
			'once_per_customer' => 0,
		);

		$data = array_filter( $data );
		$data = wp_parse_args( $data, $validated_data );

		foreach ( $data as $column => $value ) {
			switch ( $column ) {
				// We skip these here as they are handled below.
				case 'start_date':
				case 'start':
				case 'end_date':
				case 'expiration':
				case 'edd-redirect':
				case 'edd-action':
				case 'edd-discount-nonce':
				case '_wp_http_referer':
					break;

				case 'amount':
				case 'min_charge_amount':
					$validated_data[ $column ] = edd_sanitize_amount( $value );
					break;

				case 'once_per_customer':
					$validated_data['once_per_customer'] = (int) (bool) $value;
					break;

				default:
					$validated_data[ $column ] = is_array( $value )
						? array_map( 'sanitize_text_field', $value )
						: sanitize_text_field( $value );
					break;
			}
		}

		// Dates.
		$validated_data['start_date'] = $this->get_date( $data, 'start' );
		$validated_data['end_date']   = $this->get_date( $data, 'end' );

		// Known & accepted core discount meta.
		$validated_data['product_reqs']      = isset( $data['product_reqs'] ) ? preg_filter( '/\d|\d_\d/', '$0', (array) $data['product_reqs'] ) : ''; // only accepts patterns like 123 or 123_4.
		$validated_data['excluded_products'] = isset( $data['excluded_products'] ) ? wp_parse_id_list( $data['excluded_products'] ) : '';
		$validated_data['categories']        = ! empty( $data['categories'] ) ? wp_parse_id_list( $data['categories'] ) : array();
		$validated_data['term_condition']    = isset( $data['term_condition'] ) ? $data['term_condition'] : '';

		// Strip out known non-columns.
		$to_strip = array(

			// Legacy.
			'discount-id',

			// Time.
			'start_date_minute',
			'start_date_hour',
			'end_date_minute',
			'end_date_hour',
		);

		// Loop through fields to update, and unset known bad keys.
		foreach ( $validated_data as $key => $value ) {
			if ( in_array( $key, $to_strip, true ) ) {
				unset( $validated_data[ $key ] );
			}
		}

		return $validated_data;
	}

	/**
	 * Gets a date.
	 *
	 * @since 3.3.9
	 * @param array  $data Data.
	 * @param string $key  Key.
	 * @return string
	 */
	private function get_date( $data, $key ) {
		if ( empty( $data[ "{$key}_date" ] ) ) {
			return null;
		}

		$date        = sanitize_text_field( $data[ "{$key}_date" ] );
		$date_hour   = isset( $data[ "{$key}_date_hour" ] ) && (int) $data[ "{$key}_date_hour" ] >= 0 && (int) $data[ "{$key}_date_hour" ] <= 23
			? intval( $data[ "{$key}_date_hour" ] )
			: '00';
		$date_minute = isset( $data[ "{$key}_date_minute" ] ) && (int) $data[ "{$key}_date_minute" ] >= 0 && (int) $data[ "{$key}_date_minute" ] <= 59
			? intval( $data[ "{$key}_date_minute" ] )
			: '00';

		$date_string = EDD()->utils->get_date_string(
			$date,
			$date_hour,
			$date_minute
		);

		// The start date is entered in the user's WP timezone. We need to convert it to UTC prior to saving now.
		return edd_get_utc_date_string( $date_string );
	}

	/**
	 * Checks if a discount code exists.
	 *
	 * @since 3.3.9
	 * @param string $code       Code.
	 * @param int    $discount_id Discount ID.
	 * @return bool
	 */
	private function discount_code_exists( $code, $discount_id = null ) {
		if ( empty( $discount_id ) ) {
			return (bool) edd_get_discount_by_code( $code );
		}

		return ! empty(
			edd_get_discounts(
				array(
					'code'       => $code,
					'id__not_in' => array( $discount_id ),
				)
			)
		);
	}
}
