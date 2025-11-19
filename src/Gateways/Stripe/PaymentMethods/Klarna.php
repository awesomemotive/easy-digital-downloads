<?php
/**
 * Klarna payment method class.
 *
 * @since 3.6.1
 * @package EDD\Gateways\Stripe\PaymentMethods
 */

namespace EDD\Gateways\Stripe\PaymentMethods;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Klarna class.
 */
class Klarna extends Method {

	/**
	 * The ID of the payment method.
	 *
	 * @since 3.6.1
	 * @var string
	 */
	protected static $id = 'klarna';

	/**
	 * Whether the payment method supports subscriptions.
	 *
	 * @since 3.6.1
	 * @var bool
	 */
	public static $subscriptions = true;

	/**
	 * The currencies supported by the payment method.
	 *
	 * @since 3.6.1
	 * @var array
	 */
	public static $currencies = array( 'AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'NOK', 'NZD', 'PLN', 'RON', 'SEK', 'USD' );

	/**
	 * The countries supported by the payment method.
	 *
	 * @since 3.6.1
	 * @var array
	 */
	public static $countries = array(
		'au',
		'at',
		'be',
		'bu',
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
		'hu',
		'ie',
		'it',
		'jp',
		'lv',
		'li',
		'lt',
		'lu',
		'mt',
		'mx',
		'nl',
		'nz',
		'no',
		'pl',
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
	 * @since 3.6.1
	 * @return string
	 */
	public static function get_label() {
		return __( 'Klarna', 'easy-digital-downloads' );
	}

	/**
	 * Gets the icon for the payment method.
	 *
	 * @since 3.6.1
	 * @return string
	 */
	public static function get_icon(): string {
		return '<svg aria-hidden="true" width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><path fill="#FFA8CD" d="M0 0h32v32H0z"></path><path fill="#0B051D" d="M23.665 6h-4.342c0 3.571-2.185 6.771-5.506 9.057l-1.305.914V6H8v20h4.512v-9.914L19.975 26h5.506l-7.18-9.486c3.264-2.371 5.392-6.057 5.364-10.514Z"></path></svg>';
	}
}
