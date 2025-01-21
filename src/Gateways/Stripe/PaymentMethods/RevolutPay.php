<?php
/**
 * Stripe payment method class.
 *
 * @since 3.3.6
 * @package EDD\Gateways\Stripe\PaymentMethods
 */

namespace EDD\Gateways\Stripe\PaymentMethods;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * RevolutPay class.
 */
class RevolutPay extends Method {

	/**
	 * The ID of the payment method.
	 *
	 * @since 3.3.6
	 * @var string
	 */
	protected static $id = 'revolut_pay';

	/**
	 * The supported currencies for the payment method.
	 *
	 * @since 3.3.6
	 * @var array
	 */
	public static $currencies = array( 'EUR', 'GBP' );

	/**
	 * Whether the payment method supports subscriptions.
	 *
	 * @since 3.3.6
	 * @var bool
	 */
	public static $subscriptions = true;

	/**
	 * Whether the payment method supports trials.
	 *
	 * @since 3.3.6
	 * @var bool
	 */
	public static $trials = true;

	/**
	 * Gets the label for the payment method.
	 *
	 * @since 3.3.6
	 * @return string
	 */
	public static function get_label() {
		return __( 'Revolut Pay', 'easy-digital-downloads' );
	}

	/**
	 * Gets the icon for the payment method.
	 *
	 * @since 3.3.6
	 * @return string
	 */
	public static function get_icon(): string {
		return '<svg aria-hidden="true" width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><path fill="#191C1F" d="M0 0h32v32H0z"></path><path fill="#fff" d="M13.465 10.936H10V26h3.465V10.936Z"></path><path fill="#fff" d="M24.332 11.797C24.332 8.601 21.911 6 18.935 6H10v3.217h8.51c1.347 0 2.462 1.138 2.487 2.536.012.7-.232 1.36-.688 1.86-.456.5-1.066.776-1.717.776h-3.315c-.117 0-.213.102-.213.229v2.86a.24.24 0 0 0 .04.133L20.73 26h4.117l-5.638-8.412c2.84-.153 5.124-2.717 5.124-5.79Z"></path></svg>';
	}
}
