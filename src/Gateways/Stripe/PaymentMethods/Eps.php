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
 * Eps class.
 */
class Eps extends Method {

	/**
	 * The ID of the payment method.
	 *
	 * @since 3.3.5
	 * @var string
	 */
	protected static $id = 'eps';

	/**
	 * The supported currencies for the payment method.
	 *
	 * @since 3.3.5
	 * @var array
	 */
	public static $currencies = array( 'EUR' );

	/**
	 * Gets the label for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_label() {
		return __( 'EPS', 'easy-digital-downloads' );
	}

	/**
	 * Gets the icon for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_icon(): string {
		return '<svg aria-hidden="true" height="32" width="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#fff"></path><g fill-rule="nonzero"><path d="M26.834 16h-2.32a.465.465 0 0 1-.467-.464c0-.258.21-.485.468-.485h3.53v-1.747h-3.53a2.242 2.242 0 0 0-2.236 2.24 2.242 2.242 0 0 0 2.236 2.24h2.288c.258 0 .467.206.467.464s-.21.448-.467.448h-4.907c-.416.798-.821 1.481-1.643 1.823h6.581c1.212-.018 2.203-1.046 2.203-2.268 0-1.222-.991-2.233-2.203-2.251" fill="#5f6360"></path><path d="M17.943 13.304c-1.966 0-3.567 1.615-3.567 3.6v7.488h1.78V20.52h1.783c1.966 0 3.561-1.637 3.561-3.622 0-1.984-1.591-3.593-3.557-3.593zm0 5.392h-1.788v-1.799c0-1.005.798-1.822 1.788-1.822.989 0 1.794.817 1.794 1.822s-.805 1.8-1.794 1.8z" fill="#5f6360"></path><g fill="#a41760"><path d="M8.741 20.519c-1.683 0-3.098-1.203-3.48-2.774 0 0-.111-.519-.111-.861 0-.342.105-.867.105-.867a3.592 3.592 0 0 1 3.478-2.734 3.603 3.603 0 0 1 3.598 3.592v.872H7.08c.31.607.937.95 1.66.95h4.72l.006-5.14c0-.766-.625-1.392-1.39-1.392H5.39c-.764 0-1.389.607-1.389 1.374v6.707c0 .766.625 1.412 1.389 1.412h6.689c.685 0 1.257-.493 1.368-1.139z"></path><path d="M8.733 14.988c-.72 0-1.346.442-1.657 1.012h3.315c-.31-.57-.937-1.012-1.658-1.012m3.03-5.005c0-1.637-1.357-2.964-3.03-2.964-1.646 0-2.985 1.284-3.029 2.883v.911c0 .107.087.212.195.212h1.115c.11 0 .205-.105.205-.212v-.83c0-.817.679-1.482 1.514-1.482.836 0 1.515.665 1.515 1.482v.83c0 .107.088.212.197.212h1.115c.11 0 .203-.105.203-.212z"></path></g></g></g></svg>';
	}
}
