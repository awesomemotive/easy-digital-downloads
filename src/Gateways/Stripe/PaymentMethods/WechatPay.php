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
 * WechatPay class.
 */
class WechatPay extends Method {

	/**
	 * The ID of the payment method.
	 *
	 * @since 3.3.5
	 * @var string
	 */
	protected static $id = 'wechat_pay';

	/**
	 * Gets the label for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_label() {
		return __( 'WeChat Pay', 'easy-digital-downloads' );
	}

	/**
	 * Gets the icon for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_icon(): string {
		return '<svg aria-hidden="true" height="32" width="32" viewBox="0 0 32 33" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#F5F6F8" d="M0 .098h32v32H0z"></path><path d="M12.71 18.392a.783.783 0 0 1-.36.081.786.786 0 0 1-.694-.407l-.055-.108-2.192-4.666a.338.338 0 0 1-.027-.163.366.366 0 0 1 .11-.271.384.384 0 0 1 .278-.109.45.45 0 0 1 .25.082l2.58 1.79c.2.12.43.185.665.19.143 0 .284-.028.416-.081l12.096-5.263c-2.193-2.523-5.771-4.15-9.793-4.15C9.354 5.316 4 9.683 4 15.081c0 2.93 1.609 5.589 4.133 7.38a.698.698 0 0 1 .278.869c-.194.732-.527 1.927-.527 1.98a.956.956 0 0 0-.055.298.366.366 0 0 0 .11.27.387.387 0 0 0 .277.109.319.319 0 0 0 .222-.081l2.608-1.493c.192-.116.412-.181.638-.19.122.005.243.023.36.054 1.27.363 2.588.546 3.912.544 6.602 0 11.983-4.368 11.983-9.766a8.44 8.44 0 0 0-1.359-4.53l-13.786 7.812-.084.054Z" fill="#1AAD19"></path></svg>';
	}
}
