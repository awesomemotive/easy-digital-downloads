<?php
/**
 * Gateway handler class.
 *
 * @package   EDD\Gateways
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.3.9
 */

namespace EDD\Gateways;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Registry class.
 *
 * @since 3.3.9
 */
class Registry {

	/**
	 * Get registered gateways.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	public static function get() {
		static $gateways;

		if ( is_null( $gateways ) ) {
			$gateways = array();

			foreach ( self::get_registered_classes() as $key => $class ) {
				$gateway = self::get_gateway_class( $key );
				if ( ! $gateway ) {
					continue;
				}

				$gateways[ $gateway->get_id() ] = array(
					'admin_label'    => $gateway->get_admin_label(),
					'checkout_label' => $gateway->get_checkout_label(),
					'supports'       => $gateway->get_supports(),
					'icons'          => $gateway->get_icons(),
				);
			}
		}

		return $gateways;
	}

	/**
	 * Get enabled gateways.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	public static function get_enabled() {
		$enabled      = (array) edd_get_option( 'gateways', array() );
		$gateway_list = array();

		foreach ( edd_get_payment_gateways() as $key => $gateway ) {
			if ( isset( $enabled[ $key ] ) && 1 === (int) $enabled[ $key ] ) {
				$gateway_list[ $key ] = $gateway;
			}
		}

		/**
		 * Filter the enabled payment gateways before the default is bumped to the
		 * front of the array.
		 *
		 * @since 3.0
		 *
		 * @param array $gateway_list List of enabled payment gateways
		 * @return array Array of sorted gateways
		 */
		return apply_filters( 'edd_enabled_payment_gateways_before_sort', $gateway_list );
	}

	/**
	 * Get enabled gateways, sorted so the default is first.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	public static function get_sorted() {
		$gateway_list       = self::get_enabled();
		$default_gateway_id = edd_get_default_gateway();

		// Only put default on top if it's active.
		if ( ! edd_is_gateway_active( $default_gateway_id ) ) {
			return $gateway_list;
		}

		$default_gateway = array( $default_gateway_id => $gateway_list[ $default_gateway_id ] );
		unset( $gateway_list[ $default_gateway_id ] );

		return array_merge( $default_gateway, $gateway_list );
	}

	/**
	 * Checks if a gateway is enabled.
	 *
	 * @since 3.3.9
	 * @param string $gateway Gateway ID.
	 * @return bool
	 */
	public static function is_enabled( $gateway ) {
		$gateways = self::get_enabled();

		return apply_filters( 'edd_is_gateway_active', array_key_exists( $gateway, $gateways ), $gateway, $gateways );
	}

	/**
	 * Get gateway class.
	 *
	 * @since 3.3.9
	 * @param string $gateway Gateway ID.
	 * @return mixed
	 */
	private static function get_gateway_class( $gateway ) {
		$registered = self::get_registered_classes();
		if ( ! array_key_exists( $gateway, $registered ) ) {
			return false;
		}

		if ( ! is_subclass_of( $registered[ $gateway ], '\\EDD\\Gateways\\Gateway' ) ) {
			return false;
		}

		return new $registered[ $gateway ]();
	}

	/**
	 * Get registered gateway classes.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	private static function get_registered_classes() {
		/**
		 * Filter the registered gateways.
		 *
		 * @since 3.3.9
		 *
		 * @param array $registered_gateways Registered gateways.
		 * @return array Registered gateways.
		 */
		return apply_filters(
			'edd_registered_gateways',
			array(
				'stripe'          => Stripe\Gateway::class,
				'square'          => Square\Gateway::class,
				'paypal_commerce' => PayPal\Gateway::class,
				'paypal'          => PayPalStandard\Gateway::class,
				'manual'          => StoreGateway\Gateway::class,
			)
		);
	}
}
