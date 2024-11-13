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
 * CartesBancaires class.
 */
class CartesBancaires extends Method {

	/**
	 * The ID of the payment method.
	 *
	 * @since 3.3.5
	 * @var string
	 */
	protected static $id = 'cartes_bancaires';

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
	 * Gets the label for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_label() {
		return __( 'Cartes Bancaires', 'easy-digital-downloads' );
	}

	/**
	 * Gets the icon for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_icon(): string {
		return '<svg aria-hidden="true" height="32" width="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#016797" d="M0 0h32v32H0z"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M17.434 10.495v5.115h8.164a2.557 2.557 0 0 0 0-5.115h-8.164Zm-.697 5.115c-.046-1.006-.297-1.909-.72-2.675a5.076 5.076 0 0 0-2.007-2.007c-.852-.47-1.874-.728-3.019-.728H9.91c-1.145 0-2.166.258-3.018.728a5.076 5.076 0 0 0-2.007 2.007c-.471.853-.729 1.874-.729 3.019s.258 2.166.729 3.019A5.076 5.076 0 0 0 6.89 20.98c.852.47 1.873.728 3.018.728h1.082c1.145 0 2.167-.257 3.019-.728a5.076 5.076 0 0 0 2.007-2.007c.423-.766.674-1.669.72-2.675h-6.09v-.688h6.09Zm.697.688v5.115h8.164a2.557 2.557 0 0 0 0-5.115h-8.164Z" fill="#fff"></path></svg>';
	}
}
