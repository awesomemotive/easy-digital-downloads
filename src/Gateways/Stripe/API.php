<?php
/**
 * Manage the Stripe API PHP bindings usage.
 *
 * @package EDD\Gateways\Stripe
 * @since   2.7.0
 */

namespace EDD\Gateways\Stripe;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Vendor\Stripe\Stripe;

/**
 * Implements a wrapper for the Stripe API PHP bindings.
 *
 * @since 2.7.0
 */
class API {

	/**
	 * Configures the Stripe API before each request.
	 *
	 * @since 2.7.0
	 * @deprecated 3.6.1 The API key and version are now set before each request.
	 */
	public function __construct() {}

	/**
	 * Makes an API request.
	 *
	 * Requires a Stripe object and method, with optional additional arguments.
	 *
	 * @since 2.7.0
	 *
	 * @link https://github.com/stripe/stripe-php
	 *
	 * @throws \EDD_Stripe_Utils_Exceptions_Stripe_Object_Not_Found When attempting to call an object or method that is not available.
	 * @throws \EDD_Stripe_Utils_Exceptions_Stripe_API_Unmet_Requirements When the application requirements are not met.
	 *
	 * @param string $stripe_object  Stripe object, such as Customer, Subscription, PaymentMethod, etc.
	 * @param string $method         Method to call on the object, such as update, retrieve, etc.
	 * @param mixed  ...$args        Optional arguments to pass to the request.
	 * @return \EDD\Vendor\Stripe\Stripe\StripeObject
	 */
	public function request( $stripe_object, $method, ...$args ) {
		// Nothing should be executing API requests if the application requirements
		// have not been met. However, a final safety net is added here.
		if ( false === edds_has_met_requirements() ) {
			throw new \EDD_Stripe_Utils_Exceptions_Stripe_API_Unmet_Requirements(
				__( 'Unable to process request: Unmet Stripe payment gateway requirements. Please contact the website administrator.', 'easy-digital-downloads' )
			);
		}

		$classname = 'EDD\\Vendor\\Stripe\\' . $stripe_object;

		if ( ! is_callable( array( $classname, $method ) ) ) {
			throw new \EDD_Stripe_Utils_Exceptions_Stripe_Object_Not_Found(
				sprintf(
					/* translators: %1$s Stripe API class name. %2$s Stripe API method name. */
					esc_html__( 'Unable to call %1$s::%2$s', 'easy-digital-downloads' ),
					$classname,
					$method
				)
			);
		}

		if ( $this->should_expand_charges( $stripe_object, $method, $args ) ) {
			// Format: retrieve( 'id' ) or retrieve( 'id', [] ).
			if ( ! isset( $args[1] ) ) {
				$args[1] = array();
			}
			if ( ! isset( $args[1]['expand'] ) ) {
				$args[1]['expand'] = array( 'charges' );
			}
		}

		$this->set_api_key();
		$this->set_app_info();
		$this->set_api_version();

		/**
		 * Allows action to be taken before a Stripe API request is made.
		 *
		 * @since 2.8.0
		 *
		 * @param API    $this          API class.
		 * @param string $stripe_object Stripe object, such as Customer, Subscription, PaymentMethod, etc.
		 * @param string $method        Method to call on the object, such as update, retrieve, etc.
		 * @param mixed  ...$args       Additional arguments to pass to the request.
		 */
		do_action( 'edds_pre_stripe_api_request', $this, $stripe_object, $method, $args );

		// @todo Filter arguments and per-request options?
		//
		// Need to account for:
		//
		// ::retrieve( array() );
		// ::retrieve( array(), array() );
		// ::retrieve( '123' );
		// ::retrieve( '123', array() );
		// ::update( '123', array() );
		// ::update( '123', array(), array() );

		return call_user_func_array( array( $classname, $method ), $args );
	}

	/**
	 * Sets API key for all proceeding requests.
	 *
	 * @since 2.7.0
	 */
	public function set_api_key() {
		$secret_key = edd_get_option( ( edd_is_test_mode() ? 'test' : 'live' ) . '_secret_key' );

		Stripe::setApiKey( trim( $secret_key ) );
	}

	/**
	 * Sets application info for all proceeding requests.
	 *
	 * @link https://stripe.com/docs/building-plugins#setappinfo
	 *
	 * @since 2.7.0
	 */
	public function set_app_info() {
		Stripe::setAppInfo(
			'WordPress Easy Digital Downloads - Stripe',
			EDD_VERSION,
			esc_url( site_url() ),
			EDD_STRIPE_PARTNER_ID
		);
	}

	/**
	 * Sets API version for all proceeding requests.
	 *
	 * @link https://stripe.com/docs/building-plugins#set-api-version
	 *
	 * @since 2.7.0
	 */
	public function set_api_version() {
		Stripe::setApiVersion( self::get_api_version() );
	}

	/**
	 * Gets the API version for all proceeding requests.
	 *
	 * @since 3.6.1
	 * @return string The API version.
	 */
	public static function get_api_version() {

		$version = defined( 'EDD_STRIPE_API_VERSION' ) ? EDD_STRIPE_API_VERSION : '2022-11-15';
		if ( 'card-elements' === edds_get_elements_mode() ) {
			return $version;
		}

		return '2024-04-10';
	}

	/**
	 * Checks if the charges should be expanded for a PaymentIntent retrieve request.
	 * Auto-expand charges for PaymentIntent retrieves to ensure compatibility across API versions.
	 * This is required until Recurring stops looking for $payment_intent->charges.
	 *
	 * @since 3.6.1
	 * @param string $stripe_object The Stripe object.
	 * @param string $method The method.
	 * @param array  $args The arguments.
	 * @return bool
	 */
	private function should_expand_charges( $stripe_object, $method, $args ): bool {

		if ( ! defined( 'EDD_RECURRING_VERSION' ) || version_compare( EDD_RECURRING_VERSION, '2.13.5', '>=' ) ) {
			return false;
		}

		if ( 'PaymentIntent' !== $stripe_object || 'retrieve' !== $method ) {
			return false;
		}

		return ! empty( $args[0] ) && is_string( $args[0] );
	}
}
