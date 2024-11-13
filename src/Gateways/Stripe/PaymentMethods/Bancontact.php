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
 * Bancontact class.
 */
class Bancontact extends Method {

	/**
	 * The ID of the payment method.
	 *
	 * @since 3.3.5
	 * @var string
	 */
	protected static $id = 'bancontact';

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
		return __( 'Bancontact', 'easy-digital-downloads' );
	}

	/**
	 * Gets the icon for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_icon(): string {
		return '<svg aria-hidden="true" height="32" width="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#EBF1F7" d="M0 0h32v32H0z"></path><g clip-path="url(#bi_bancontact__a)"><path d="M8.164 21.448c3.918 0 5.877-2.612 7.836-5.224H3v5.224h5.164Z" fill="url(#bi_bancontact__b)"></path><path d="M23.836 11c-3.918 0-5.877 2.612-7.836 5.224h13V11h-5.164Z" fill="url(#bi_bancontact__c)"></path></g><defs><linearGradient id="bi_bancontact__b" x1="5.629" y1="19.077" x2="15.139" y2="15.544" gradientUnits="userSpaceOnUse"><stop stop-color="#005AB9"></stop><stop offset="1" stop-color="#1E3764"></stop></linearGradient><linearGradient id="bi_bancontact__c" x1="16.787" y1="16.677" x2="26.885" y2="13.232" gradientUnits="userSpaceOnUse"><stop stop-color="#FBA900"></stop><stop offset="1" stop-color="#FFD800"></stop></linearGradient><clipPath id="bi_bancontact__a"><path fill="#fff" transform="translate(3 11)" d="M0 0h26v10.447H0z"></path></clipPath></defs></svg>';
	}
}
