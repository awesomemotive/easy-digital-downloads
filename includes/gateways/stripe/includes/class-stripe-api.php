<?php
/**
 * Manage the Stripe API PHP bindings usage.
 *
 * @package EDD_Stripe
 * @since   2.7.0
 */

use \EDD\Vendor\Stripe\Stripe;

/**
 * Implements a wrapper for the Stripe API PHP bindings.
 *
 * @since 2.7.0
 */
class EDD_Stripe_API {

	/**
	 * Configures the Stripe API before each request.
	 *
	 * @since 2.7.0
	 */
	public function __construct() {
		add_action( 'edds_pre_stripe_api_request', array( $this, 'set_api_key' ) );
		add_action( 'edds_pre_stripe_api_request', array( $this, 'set_app_info' ) );
		add_action( 'edds_pre_stripe_api_request', array( $this, 'set_api_version' ) );
	}

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
	 * @throws \Stripe\Exception
	 *
	 * @param string $object  Stripe object, such as Customer, Subscription, PaymentMethod, etc.
	 * @param string $method  Method to call on the object, such as update, retrieve, etc.
	 * @param mixed  ...$args Additional arguments to pass to the request.
	 * @return \EDD\Vendor\Stripe\Stripe\StripeObject
	 */
	public function request( $object, $method ) {
		// Nothing should be executing API requests if the application requirements
		// have not been met. However, a final safety net is added here.
		if ( false === edds_has_met_requirements() ) {
			throw new EDD_Stripe_Utils_Exceptions_Stripe_API_Unmet_Requirements(
				__( 'Unable to process request: Unmet Stripe payment gateway requirements. Please contact the website administrator.', 'easy-digital-downloads' )
			);
		}

		$classname = 'EDD\\Vendor\\Stripe\\' . $object;

		// Retrieve additional arguments.
		$args = func_get_args();
		unset( $args[0] ); // Removes $object.
		unset( $args[1] ); // Removes $method.

		// Reset keys.
		$args = array_values( $args );

		if ( ! is_callable( array( $classname, $method ) ) ) {
			throw new EDD_Stripe_Utils_Exceptions_Stripe_Object_Not_Found(
				sprintf(
					/* translators: %1$s Stripe API class name. %2$s Stripe API method name. */
					esc_html__( 'Unable to call %1$s::%2$s', 'easy-digital-downloads' ),
					$classname,
					$method
				)
			);
		}

		/**
		 * Allows action to be taken before a Stripe API request is made.
		 *
		 * @since 2.8.0
		 *
		 * @param EDD_Stripe_API $this   EDD_Stripe_API class.
		 * @param string         $object Stripe object, such as Customer, Subscription, PaymentMethod, etc.
		 * @param string         $method Method to call on the object, such as update, retrieve, etc.
		 * @param mixed          $args   Additional arguments to pass to the request.
		 */
		do_action( 'edds_pre_stripe_api_request', $this, $object, $method, $args );

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
		Stripe::setApiVersion( EDD_STRIPE_API_VERSION );
	}
}
