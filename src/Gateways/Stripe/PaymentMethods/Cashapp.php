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
 * Cashapp class.
 */
class Cashapp extends Method {

	/**
	 * The ID of the payment method.
	 *
	 * @since 3.3.5
	 * @var string
	 */
	protected static $id = 'cashapp';

	/**
	 * The supported currencies for the payment method.
	 *
	 * @since 3.3.5
	 * @var array
	 */
	public static $currencies = array( 'USD' );

	/**
	 * The supported countries for the payment method.
	 *
	 * @since 3.3.5
	 * @var array
	 */
	public static $countries = array( 'us' );

	/**
	 * Whether the payment method supports subscriptions.
	 *
	 * @since 3.3.5
	 * @var bool
	 */
	public static $subscriptions = true;

	/**
	 * Whether the payment method supports trials.
	 *
	 * @since 3.3.5
	 * @var bool
	 */
	public static $trials = true;

	/**
	 * The scope of the payment method.
	 *
	 * @since 3.3.5
	 * @var string
	 */
	public static $scope = 'popular';

	/**
	 * Gets the label for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_label() {
		return __( 'Cash App', 'easy-digital-downloads' );
	}

	/**
	 * Gets the icon for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_icon(): string {
		return '<svg aria-hidden="true" height="32" width="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#00D64F" d="M0 0h32v32H0z"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M16.892 10.264c1.782 0 3.49.693 4.607 1.643.282.24.705.24.97-.018l1.328-1.293a.646.646 0 0 0-.032-.968 10.625 10.625 0 0 0-3.56-1.92l.416-1.903C20.71 5.39 20.38 5 19.94 5h-2.567a.69.69 0 0 0-.68.528l-.376 1.693c-3.412.163-6.303 1.796-6.303 5.147 0 2.9 2.392 4.144 4.917 5.004 2.393.86 3.655 1.18 3.655 2.391 0 1.243-1.262 1.976-3.123 1.976-1.697 0-3.475-.537-4.854-1.841a.713.713 0 0 0-.974-.002l-1.427 1.35a.652.652 0 0 0 .004.96c1.113 1.036 2.522 1.786 4.13 2.206l-.391 1.763c-.092.413.234.803.676.806l2.57.019a.689.689 0 0 0 .686-.53l.371-1.695c4.085-.242 6.586-2.372 6.586-5.49 0-2.868-2.492-4.08-5.516-5.068-1.728-.606-3.224-1.02-3.224-2.263 0-1.21 1.397-1.69 2.792-1.69Z" fill="#fff"></path></svg>';
	}
}
