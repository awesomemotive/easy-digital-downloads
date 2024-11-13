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
 * Ideal class.
 */
class Ideal extends Method {

	/**
	 * The ID of the payment method.
	 *
	 * @since 3.3.5
	 * @var string
	 */
	protected static $id = 'ideal';

	/**
	 * The supported currencies for the payment method.
	 *
	 * @since 3.3.5
	 * @var array
	 */
	public static $currencies = array( 'EUR' );

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
	 * Whether the payment method supports subscriptions.
	 *
	 * @since 3.3.5
	 * @var bool
	 */
	public static $subscriptions = true;

	/**
	 * Gets the label for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_label() {
		return __( 'iDEAL', 'easy-digital-downloads' );
	}

	/**
	 * Gets the icon for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_icon(): string {
		return '<svg aria-hidden="true" height="32" width="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#DB308B" d="M0 0h32v32H0z"></path><g clip-path="url(#bi_ideal__a)" fill-rule="evenodd" clip-rule="evenodd"><path d="M17.876 8c2.394 0 4.39.639 5.771 1.847C25.209 11.213 26 13.283 26 16c0 5.384-2.657 8-8.124 8H7V8h10.876Z" fill="#fff"></path><path d="M17.845 8.196c2.34 0 4.29.623 5.64 1.802 1.526 1.332 2.3 3.352 2.3 6.002 0 5.252-2.598 7.804-7.94 7.804H7.215V8.196h10.63ZM18.074 7H6v18h12.074v-.003c2.636-.035 4.726-.68 6.209-1.92C26.086 21.57 27 19.189 27 16c0-1.524-.24-2.891-.715-4.063a7.404 7.404 0 0 0-1.993-2.833c-1.53-1.336-3.677-2.059-6.218-2.1V7Z" fill="#000"></path><path d="M17.678 21.24h-3.53V10.524h3.53-.143c2.945 0 6.078 1.14 6.078 5.372 0 4.473-3.133 5.343-6.078 5.343h.143v.001Z" fill="#DB4093"></path><path d="M10.852 14.557c1.085 0 1.965-.862 1.965-1.925 0-1.063-.88-1.925-1.965-1.925s-1.964.862-1.964 1.925c0 1.063.88 1.925 1.964 1.925Zm-1.767.896v5.645h3.47v-5.645h-3.47Z" fill="#000"></path></g><defs><clipPath id="bi_ideal__a"><path fill="#fff" transform="translate(6 7)" d="M0 0h21v18H0z"></path></clipPath></defs></svg>';
	}
}
