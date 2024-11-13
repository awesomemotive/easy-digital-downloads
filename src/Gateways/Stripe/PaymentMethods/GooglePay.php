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
 * GooglePay class.
 */
class GooglePay extends Method {

	/**
	 * The ID of the payment method.
	 *
	 * @since 3.3.5
	 * @var string
	 */
	protected static $id = 'google_pay';

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
		return __( 'Google Pay', 'easy-digital-downloads' );
	}

	/**
	 * Gets the icon for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_icon(): string {
		return '<svg aria-hidden="true" height="32" width="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#F5F6F8" d="M0 0h32v32H0z"></path><path d="M22.075 9.085H9.905C6.1 9.085 2.99 12.196 2.99 16c0 3.803 3.111 6.915 6.915 6.915h12.17c3.803 0 6.915-3.112 6.915-6.915s-3.112-6.915-6.915-6.915Z" fill="#fff"></path><path d="M22.075 9.645c.854 0 1.684.17 2.465.501a6.413 6.413 0 0 1 3.388 3.388c.332.782.502 1.612.502 2.466 0 .854-.17 1.683-.502 2.465a6.412 6.412 0 0 1-3.388 3.388 6.272 6.272 0 0 1-2.465.502H9.905c-.854 0-1.684-.17-2.466-.502a6.41 6.41 0 0 1-3.388-3.388A6.272 6.272 0 0 1 3.55 16c0-.854.17-1.684.501-2.466a6.411 6.411 0 0 1 3.388-3.388 6.274 6.274 0 0 1 2.466-.501h12.17Zm0-.56H9.905C6.1 9.085 2.99 12.196 2.99 16c0 3.803 3.111 6.915 6.915 6.915h12.17c3.803 0 6.915-3.112 6.915-6.915s-3.112-6.915-6.915-6.915Z" fill="#3C4043"></path><path d="M15.388 16.49v2.092h-.664v-5.165h1.76c.446 0 .826.149 1.137.446.319.297.478.66.478 1.09 0 .438-.16.801-.477 1.095-.308.294-.689.44-1.138.44h-1.096v.003Zm0-2.437v1.801h1.11c.263 0 .484-.09.657-.266a.87.87 0 0 0 .266-.632.86.86 0 0 0-.266-.626.861.861 0 0 0-.657-.273h-1.11v-.004Zm4.446.878c.491 0 .878.132 1.162.395.284.262.425.622.425 1.078v2.178h-.632v-.49h-.028c-.273.404-.64.605-1.096.605-.39 0-.716-.115-.979-.346a1.104 1.104 0 0 1-.394-.865c0-.366.139-.657.415-.87.277-.219.647-.326 1.107-.326.394 0 .719.073.971.218v-.152a.754.754 0 0 0-.273-.588.937.937 0 0 0-.643-.242c-.37 0-.664.156-.878.47l-.585-.366c.322-.467.8-.699 1.428-.699Zm-.857 2.566c0 .173.072.318.221.432a.813.813 0 0 0 .515.173c.28 0 .53-.104.747-.311a.976.976 0 0 0 .329-.73c-.208-.162-.495-.245-.865-.245-.27 0-.494.065-.674.193-.183.135-.273.298-.273.488Zm6.053-2.452-2.212 5.09h-.684l.822-1.78-1.459-3.31h.723l1.051 2.538h.014l1.023-2.538h.723Z" fill="#3C4043"></path><path d="M12.748 16.069c0-.217-.02-.424-.056-.623H9.91v1.141h1.602c-.065.38-.274.704-.595.92v.74h.954c.557-.516.877-1.278.877-2.178Z" fill="#4285F4"></path><path d="M10.918 17.506c-.266.18-.608.284-1.008.284-.772 0-1.428-.52-1.663-1.222h-.984v.763a2.962 2.962 0 0 0 2.647 1.631c.8 0 1.473-.263 1.962-.716l-.954-.74Z" fill="#34A853"></path><path d="M8.155 16.001c0-.197.033-.387.092-.566v-.764h-.984a2.955 2.955 0 0 0 0 2.66l.984-.763a1.782 1.782 0 0 1-.092-.567Z" fill="#FABB05"></path><path d="M9.91 14.212c.437 0 .828.15 1.137.444l.845-.844a2.846 2.846 0 0 0-1.982-.772c-1.157 0-2.16.664-2.647 1.631l.984.764c.235-.702.89-1.223 1.663-1.223Z" fill="#E94235"></path></svg>';
	}
}
