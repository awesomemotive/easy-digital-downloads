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
 * AmazonPay class.
 */
class AmazonPay extends Method {

	/**
	 * The ID of the payment method.
	 *
	 * @since 3.3.5
	 * @var string
	 */
	protected static $id = 'amazon_pay';

	/**
	 * The supported currencies for the payment method.
	 *
	 * @since 3.3.5
	 * @var array
	 */
	public static $currencies = array( 'USD' );

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
		return __( 'Amazon Pay', 'easy-digital-downloads' );
	}

	/**
	 * Gets the icon for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_icon(): string {
		return '<svg aria-hidden="true" height="32" width="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#333E48" d="M0 0h32v32H0z"></path><path fill="#F90" d="M24.661 23.376C14.946 28 8.917 24.132 5.057 21.782c-.239-.148-.645.035-.293.44 1.286 1.558 5.5 5.316 11 5.316 5.505 0 8.78-3.003 9.189-3.527.407-.52.12-.806-.292-.635Zm2.729-1.506c-.261-.34-1.587-.404-2.421-.301-.836.1-2.09.61-1.98.917.055.115.17.063.744.011.575-.057 2.187-.26 2.523.179.338.442-.514 2.549-.67 2.888-.15.34.058.427.34.201.279-.226.783-.812 1.12-1.64.337-.834.542-1.997.344-2.255Z"></path><path fill="#fff" fill-rule="evenodd" d="M18.129 13.942c0 1.213.03 2.225-.583 3.302-.495.876-1.279 1.415-2.155 1.415-1.195 0-1.892-.911-1.892-2.256 0-2.654 2.378-3.135 4.63-3.135v.674Zm3.14 7.59a.65.65 0 0 1-.736.075c-1.034-.859-1.218-1.258-1.787-2.076-1.708 1.743-2.917 2.264-5.133 2.264-2.619 0-4.66-1.616-4.66-4.853 0-2.527 1.371-4.248 3.32-5.09 1.69-.744 4.051-.875 5.856-1.08v-.404c0-.74.056-1.616-.377-2.255-.381-.574-1.108-.81-1.748-.81-1.186 0-2.246.608-2.505 1.87-.052.28-.258.556-.538.569l-3.022-.324c-.254-.057-.535-.263-.465-.653C10.171 5.104 13.477 4 16.438 4c1.515 0 3.495.403 4.69 1.55 1.516 1.415 1.372 3.303 1.372 5.357v4.853c0 1.458.604 2.097 1.173 2.886.202.28.246.617-.008.828-.636.53-1.766 1.515-2.387 2.067l-.01-.009" clip-rule="evenodd"></path></svg>';
	}
}
