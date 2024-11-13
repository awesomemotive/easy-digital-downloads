<?php
/**
 * Stripe payment method class.
 *
 * @since 3.3.5
 * @package EDD\Gateways\Stripe\PaymentMethods
 */

namespace EDD\Gateways\Stripe\PaymentMethods;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Grabpay class.
 */
class Grabpay extends Method {

	/**
	 * The ID of the payment method.
	 *
	 * @since 3.3.5
	 * @var string
	 */
	protected static $id = 'grabpay';

	/**
	 * The supported currencies for the payment method.
	 *
	 * @since 3.3.5
	 * @var array
	 */
	public static $currencies = array( 'MYR', 'SGD' );

	/**
	 * The supported countries for the payment method.
	 *
	 * @since 3.3.5
	 * @var array
	 */
	public static $countries = array(
		'my',
		'sg',
	);

	/**
	 * Gets the label for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_label() {
		return __( 'GrabPay', 'easy-digital-downloads' );
	}

	/**
	 * Gets the icon for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_icon(): string {
		return '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><g fill="none" fill-rule="evenodd"><path fill="#00B14F" d="M0 0h32v32H0z"></path><path fill="#FFF" fill-rule="nonzero" d="M17.393 6c2.053 0 3.533.226 4.772.729l.12.05v1.714C21.07 7.758 19.56 7.43 17.392 7.43a9.1 9.1 0 0 0-6.243 2.475c-1.63 1.53-2.58 3.588-2.62 5.662l-.002.138v.493c0 2.246.835 4.332 2.352 5.874 1.524 1.549 3.592 2.4 5.825 2.4 3.373 0 5.12-1.195 5.876-1.936l.035-.036.007-.005v-6.222h-6.289v-1.528h7.816v8.225l-.069.091c-.191.24-.77.888-1.847 1.531-1.57.934-3.43 1.409-5.529 1.409-2.612 0-5.053-1.009-6.874-2.843-1.79-1.802-2.79-4.212-2.83-6.8v-.655C7 10.444 11.76 6 17.393 6Zm0 2.555c1.935 0 3.41.31 4.757 1.002l.134.07v1.807c-1.185-.973-2.562-1.35-4.891-1.35-3.331 0-6.15 2.519-6.21 5.523l-.002.097v.493c0 1.521.573 2.94 1.615 3.994a5.451 5.451 0 0 0 3.91 1.628c1.55 0 2.502-.275 3.055-.478l.293-.113.014-.005v-2.298h-3.734v-1.528h5.163v4.588l-.034.032c-.38.359-1.694 1.33-4.758 1.33-3.85 0-6.988-3.143-7.05-7.032l-.001-.118v-.493c0-1.89.813-3.682 2.289-5.043a8.017 8.017 0 0 1 5.45-2.106Z"></path></g></svg>';
	}
}
