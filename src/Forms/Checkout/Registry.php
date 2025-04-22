<?php
/**
 * Checkout fields registry.
 *
 * @package     EDD\Forms\Checkout
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.8
 */

namespace EDD\Forms\Checkout;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Registry class.
 *
 * @since 3.3.8
 */
class Registry {

	/**
	 * The required fields.
	 *
	 * @since 3.3.8
	 * @var array
	 */
	private static $required_fields = array();

	/**
	 * Get the fields.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	public static function get_fields() {
		return array(
			'country'   => array(
				'label' => __( 'Country', 'easy-digital-downloads' ),
				'class' => Country::class,
			),
			'address'   => array(
				'label' => __( 'Address', 'easy-digital-downloads' ),
				'class' => Address::class,
			),
			'address_2' => array(
				'label' => __( 'Address 2', 'easy-digital-downloads' ),
				'class' => Address2::class,
			),
			'city'      => array(
				'label' => __( 'City', 'easy-digital-downloads' ),
				'class' => City::class,
			),
			'state'     => array(
				'label' => __( 'State / Province', 'easy-digital-downloads' ),
				'class' => State::class,
			),
			'zip'       => array(
				'label' => __( 'Postal Code', 'easy-digital-downloads' ),
				'class' => PostalCode::class,
			),
			'phone'     => array(
				'label' => __( 'Phone', 'easy-digital-downloads' ),
				'class' => Phone::class,
			),
		);
	}

	/**
	 * Get the allowed fields.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	public static function get_allowed_fields() {
		return array_keys( self::get_fields() );
	}

	/**
	 * Get the checkout fields.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	public static function get_checkout_fields(): array {
		$option = edd_get_option( 'checkout_address_fields', false );
		if ( false !== $option ) {
			$order = edd_get_option( 'checkout_address_fields_order' );
			if ( ! empty( $order ) ) {
				$order  = array_flip( explode( ',', $order ) );
				$order  = array_intersect_key( $order, $option );
				$option = array_merge( $order, $option );
			}

			return $option;
		}

		// Originally, custom address fields were set by the Stripe gateway.
		$stripe = edd_get_option( 'stripe_billing_fields' );
		if ( empty( $stripe ) ) {
			return array();
		}

		if ( 'full' === $stripe ) {
			return array(
				'country'   => 1,
				'address'   => 1,
				'address_2' => 1,
				'city'      => 1,
				'state'     => 1,
				'zip'       => 1,
			);
		}

		if ( 'zip_country' === $stripe ) {
			return array(
				'country' => 1,
				'zip'     => 1,
			);
		}

		return array();
	}

	/**
	 * Get the required fields.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	public static function get_required_fields(): array {
		/**
		 * Filter the required fields for the purchase form.
		 *
		 * @param array $required_fields The required fields.
		 */
		return (array) apply_filters( 'edd_purchase_form_required_fields', self::set_required_fields() );
	}

	/**
	 * Set the required fields.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	private static function set_required_fields(): array {
		if ( self::can_return_static_value() ) {
			return self::$required_fields;
		}

		self::$required_fields = array(
			'edd_email' => array(
				'error_id'      => 'invalid_email',
				'error_message' => __( 'Please enter a valid email address.', 'easy-digital-downloads' ),
			),
			'edd_first' => array(
				'error_id'      => 'invalid_first_name',
				'error_message' => __( 'Please enter your first name.', 'easy-digital-downloads' ),
			),
		);

		if ( ! self::is_address_required() ) {
			return self::$required_fields;
		}

		$default_required_fields = array(
			'billing_country' => array(
				'field'         => 'country',
				'error_id'      => 'invalid_country',
				'error_message' => __( 'Please select your billing country.', 'easy-digital-downloads' ),
			),
			'card_state'      => array(
				'field'         => 'state',
				'error_id'      => 'invalid_state',
				'error_message' => __( 'Please enter billing state / region.', 'easy-digital-downloads' ),
			),
			'card_city'       => array(
				'field'         => 'city',
				'error_id'      => 'invalid_city',
				'error_message' => __( 'Please enter your billing city.', 'easy-digital-downloads' ),
			),
			'card_zip'        => array(
				'field'         => 'zip',
				'error_id'      => 'invalid_zip_code',
				'error_message' => __( 'Please enter your zip / postal code.', 'easy-digital-downloads' ),
			),
		);
		$checkout_fields         = self::get_checkout_fields();

		foreach ( $default_required_fields as $field => $data ) {
			if ( array_key_exists( $data['field'], $checkout_fields ) && empty( $checkout_fields[ $data['field'] ] ) ) {
				continue;
			}

			self::$required_fields[ $field ] = $data;
		}

		// Check if the Customer's Country has been passed in and if it has no states.
		if ( isset( self::$required_fields['card_state'] ) && ! empty( $_POST['billing_country'] ) ) {
			$customer_billing_country = sanitize_text_field( $_POST['billing_country'] );
			$states                   = edd_get_shop_states( $customer_billing_country );

			// If this country has no states, remove the requirement of a card_state.
			if ( empty( $states ) ) {
				unset( self::$required_fields['card_state'] );
			}
		}

		return self::$required_fields;
	}

	/**
	 * Checks if the address is required.
	 *
	 * @since 3.3.8
	 * @return bool
	 */
	private static function is_address_required() {
		return apply_filters( 'edd_require_billing_address', edd_use_taxes() && edd_get_cart_total() );
	}

	/**
	 * Checks if the static value can be returned.
	 *
	 * @since 3.3.8
	 * @return bool
	 */
	private static function can_return_static_value() {
		if ( empty( self::$required_fields ) ) {
			return false;
		}

		return did_action( 'template_redirect' ) && ! edd_is_doing_unit_tests();
	}
}
