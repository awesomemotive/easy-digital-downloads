<?php
/**
 * Handle available payment methods for the Stripe Payment Element.
 *
 * @package EDD\Gateways\Stripe
 * @since 3.3.5
 */

namespace EDD\Gateways\Stripe;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Payment methods for Stripe.
 *
 * @since 3.3.5
 */
class PaymentMethods {

	/**
	 * Retrieves the payment method label for the specified type.
	 *
	 * @since 3.3.5
	 * @param string $type The type of payment method to retrieve.
	 * @return string The payment method label.
	 */
	public static function get_label( $type ) {
		$payment_method = self::get_payment_method( $type );

		return $payment_method ? $payment_method::get_label() : '';
	}

	/**
	 * Retrieves a list of Stripe payment methods.
	 *
	 * @since 3.3.5
	 * @return array The list of Stripe payment methods.
	 */
	public static function list() {
		$methods = array();
		foreach ( self::get_registered_methods() as $method ) {
			$payment_method = self::get_payment_method( $method );
			if ( $payment_method ) {
				$methods[ $method ] = $payment_method::get_label();
			}
		}

		return $methods;
	}

	/**
	 * Retrieves the payment methods that are available for this store.
	 *
	 * @since 3.3.5
	 * @return array The payment methods that are enabled in Stripe.
	 */
	public static function get_configurations() {
		$mode           = edd_is_test_mode() ? 'test' : 'live';
		$configurations = new \EDD\Utils\Transient( "edd_stripe_pmc_{$mode}" );
		$value          = $configurations->get();
		if ( $value ) {
			return $value;
		}

		$pmc = self::get_pmc();
		if ( ! $pmc ) {
			return false;
		}

		$configurations->set( $pmc );

		return $pmc;
	}

	/**
	 * Retrieves a specific payment method configuration ID.
	 *
	 * @since 3.3.5
	 * @param string $type The type of payment method configuration to retrieve.
	 * @return string The configuration ID.
	 */
	public static function get_configuration_id( $type = '' ) {
		$base           = 'edd20241002';
		$configurations = self::get_configurations();
		if ( empty( $configurations ) || ! is_array( $configurations ) || ! array_key_exists( $base, $configurations ) ) {
			return false;
		}
		$configuration = $base;
		if ( $type ) {
			$configuration = "{$base}-{$type}";
		}

		return array_key_exists( $configuration, $configurations ) ? $configurations[ $configuration ] : false;
	}

	/**
	 * Retrieves the base configuration for the payment methods.
	 *
	 * @since 3.3.5
	 * @return array The array of payment methods in the base configuration.
	 */
	public static function get_base_configuration() {
		$configuration_id = self::get_configuration_id();
		if ( ! $configuration_id ) {
			return false;
		}
		if ( ! edd_stripe()->connect->is_connected ) {
			return false;
		}

		$configuration = new \EDD\Utils\Transient( $configuration_id );
		$value         = $configuration->get();
		if ( $value ) {
			return $value;
		}

		try {
			$value = edds_api_request(
				'PaymentMethodConfiguration',
				'retrieve',
				$configuration_id
			);
		} catch ( \Exception $e ) {
			return false;
		}

		$methods = array();
		foreach ( self::list() as $method => $label ) {
			if ( ! isset( $value->$method ) ) {
				continue;
			}
			$methods[ $method ] = $value->$method;
		}

		$configuration->set( $methods );

		return $methods;
	}

	/**
	 * Checks if the Affirm payment method is supported.
	 *
	 * @since 3.3.5
	 * @return bool True if the Affirm payment method is supported, false otherwise.
	 */
	public static function affirm_requires_support() {
		if ( 'payment-elements' !== edds_get_elements_mode() ) {
			return false;
		}

		$affirm = self::get_payment_method( 'affirm' );
		if ( ! $affirm ) {
			return false;
		}

		if ( ! in_array( edd_get_currency(), $affirm::$currencies, true ) ) {
			return false;
		}

		$display = edd_get_option( 'stripe_billing_fields', 'full' );
		if ( ! in_array( $display, array( 'full', 'zip_country' ), true ) ) {
			return false;
		}

		if ( edd_get_cart_total() < 50 ) {
			return false;
		}

		if ( function_exists( 'edd_recurring' ) && edd_recurring()->cart_contains_recurring() ) {
			return false;
		}

		$payment_configuration = self::get_base_configuration();

		return $payment_configuration && ! empty( $payment_configuration['affirm']['available'] );
	}

	/**
	 * Retrieves the supported payment methods.
	 *
	 * @since 3.3.5
	 * @return array The supported payment methods.
	 */
	private static function get_registered_methods() {
		return array(
			'acss_debit',
			'ach_debit',
			'affirm',
			'alipay',
			'amazon_pay',
			'apple_pay',
			'bacs_debit',
			'bancontact',
			'card',
			'cartes_bancaires',
			'cashapp',
			'eps',
			'fpx',
			'giropay',
			'google_pay',
			'grabpay',
			'ideal',
			'link',
			'p24',
			'revolut_pay',
			'sepa_debit',
			'sofort',
			'twint',
			'us_bank_account',
			'wechat_pay',
		);
	}

	/**
	 * Retrieves the class name for the specified payment method.
	 *
	 * @since 3.3.5
	 * @param string $method The payment method.
	 * @return string|false The class name for the specified payment method, or false if not found.
	 */
	public static function get_payment_method( $method ) {
		$method    = str_replace( '_', ' ', $method );
		$method    = ucwords( $method );
		$method    = str_replace( ' ', '', $method );
		$classname = __NAMESPACE__ . '\\PaymentMethods\\' . $method;

		return class_exists( $classname ) ? $classname : false;
	}

	/**
	 * Resets the payment method configuration options.
	 *
	 * @since 3.3.5
	 */
	public static function reset() {
		delete_option( 'edd_stripe_account_capabilities' );
		foreach ( array( 'test', 'live' ) as $mode ) {
			$option = get_option( "edd_stripe_pmc_{$mode}" );
			if ( $option ) {
				$configurations = json_decode( $option, true );
				if ( ! empty( $configurations['value']['edd20241002'] ) ) {
					delete_option( $configurations['value']['edd20241002'] );
				}
				delete_option( "edd_stripe_pmc_{$mode}" );
			}
		}
	}

	/**
	 * Retrieves the payment method configuration for EDD.
	 *
	 * @since 3.3.5
	 * @return object The configuration for the specified payment method.
	 */
	private static function get_pmc() {
		if ( ! edd_stripe()->connect() || ! edd_stripe()->connect->is_connected ) {
			return false;
		}

		try {
			$pmc = edds_api_request(
				'PaymentMethodConfiguration',
				'all',
				array(
					'application' => 'ca_CCnYEUzwAy5xzFgSVNc8C5jw7Zagm5cH',
				)
			);
		} catch ( \Exception $e ) {
			edd_record_gateway_error( 'stripe', 'edds_api_error', $e->getMessage() );

			return false;
		}

		$configurations = array();
		foreach ( $pmc->data as $configuration ) {
			$configurations[ strtolower( $configuration->name ) ] = $configuration->id;
		}

		return $configurations;
	}
}
