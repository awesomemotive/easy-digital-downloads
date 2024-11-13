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
 * Alipay class.
 */
class Alipay extends Method {

	/**
	 * The ID of the payment method.
	 *
	 * @since 3.3.5
	 * @var string
	 */
	protected static $id = 'alipay';

	/**
	 * The supported currencies for the payment method.
	 *
	 * @since 3.3.5
	 * @var array
	 */
	public static $currencies = array( 'CNY', 'AUD', 'CAD', 'EUR', 'GBP', 'HKD', 'JPY', 'SGD', 'MYR', 'NZD', 'USD' );

	/**
	 * The supported countries for the payment method.
	 *
	 * @since 3.3.5
	 * @var array
	 */
	public static $countries = array(
		'au',
		'at',
		'be',
		'bg',
		'ca',
		'hr',
		'cy',
		'cz',
		'dk',
		'ee',
		'fi',
		'fr',
		'de',
		'gi',
		'gr',
		'hk',
		'ie',
		'it',
		'jp',
		'li',
		'lv',
		'lt',
		'lu',
		'my',
		'mt',
		'nl',
		'nz',
		'no',
		'pt',
		'ro',
		'sg',
		'sk',
		'si',
		'es',
		'se',
		'ch',
		'gb',
		'us',
	);

	/**
	 * Gets the label for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_label() {
		return __( 'Alipay', 'easy-digital-downloads' );
	}

	/**
	 * Gets the icon for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_icon(): string {
		return '<svg aria-hidden="true" height="32" width="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#1c9fe5"></path><path d="M23.104 18.98a142.494 142.494 0 0 0 11.052 3.848c2.044.85 0 5.668-2.159 4.674-2.444-1.066-7.359-3.245-11.097-5.108C18.822 24.842 15.556 28 10.907 28 6.775 28 4 25.568 4 21.943c0-3.053 2.11-6.137 6.82-6.137 2.697 0 5.47.766 8.785 1.922a25.007 25.007 0 0 0 1.529-3.838l-11.981-.006v-1.848l6.162.015V9.63H7.808V7.81l7.507.006V5.115c0-.708.38-1.115 1.042-1.115h3.14v3.827l7.442.005v1.805h-7.44v2.431l6.088.016s-.754 3.904-2.483 6.897zM5.691 21.79v-.004c0 1.736 1.351 3.489 4.64 3.489 2.54 0 5.028-1.52 7.408-4.522-3.181-1.592-4.886-2.357-7.348-2.357-2.394 0-4.7 1.164-4.7 3.394z" fill="#fff" fill-rule="nonzero"></path></g></svg>';
	}
}
