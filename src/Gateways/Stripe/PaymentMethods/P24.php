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
 * P24 class.
 */
class P24 extends Method {

	/**
	 * The ID of the payment method.
	 *
	 * @since 3.3.5
	 * @var string
	 */
	protected static $id = 'p24';

	/**
	 * The supported currencies for the payment method.
	 *
	 * @since 3.3.5
	 * @var array
	 */
	public static $currencies = array( 'EUR', 'PLN' );

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
	 * Gets the label for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_label() {
		return __( 'Przelewy24', 'easy-digital-downloads' );
	}

	/**
	 * Gets the icon for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_icon(): string {
		return '<svg aria-hidden="true" height="32" width="32" viewBox="0 0 32 33" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#F5F6F8" d="M0 .097h32v32H0z"></path><g clip-path="url(#bi_p24__a)" fill-rule="evenodd" clip-rule="evenodd"><path d="m18.28 22.057-.155.818h-6.556l.308-1.616c.172-.905.47-1.498.898-1.78.427-.28 1.355-.496 2.784-.647 1.142-.117 1.85-.276 2.12-.477.273-.202.488-.723.648-1.565.14-.738.102-1.217-.117-1.437-.219-.22-.765-.33-1.641-.33-1.094 0-1.8.09-2.12.268-.322.177-.547.606-.675 1.285l-.11.641h-1.007l.092-.446c.195-1.026.555-1.71 1.08-2.053.526-.34 1.48-.512 2.862-.512 1.227 0 2.017.185 2.369.553.353.37.426 1.088.223 2.157-.195 1.026-.521 1.713-.98 2.058-.46.346-1.359.58-2.698.7-1.176.11-1.894.261-2.153.454-.26.192-.474.739-.645 1.64l-.055.29h5.529Zm8.623-7.747-1.1 5.782h1.362l-.156.818h-1.36l-.377 1.98h-1.025l.376-1.98H19.53l.215-1.137 5.573-5.463h1.587-.001Zm-2.126 5.782.981-5.16h-.02l-5.208 5.16h4.247Z" fill="#99A0A6"></path><path d="M4.656 14.206 3 22.86h1.052l.653-3.41h2.69l.412-.007c1.097 0 1.88-.18 2.346-.54.467-.358.796-1.032.985-2.02.197-1.032.14-1.736-.17-2.112-.31-.376-.992-.564-2.043-.564h-4.27Zm2.699 4.419H4.862l.688-3.595h2.828l.401.007c.743 0 1.191.11 1.347.333.156.222.153.75-.007 1.587-.136.714-.37 1.171-.702 1.37-.331.198-1.02.298-2.062.298Z" fill="#D40E2B"></path><path d="m9.142 11.056-1.012-.67A22.12 22.12 0 0 1 11.847 9l.186.914c-.915.26-1.88.632-2.89 1.142Zm11.481-.502a10.827 10.827 0 0 0-2.992-1l.818-1.457h.023c2.362.01 4.24.308 5.72.721l-3.569 1.737Zm-13.414.302 1.034.7c-.471.27-.953.57-1.443.905H4.793s.83-.738 2.415-1.605Zm10.026-2.709-.484 1.291a12.351 12.351 0 0 0-4.016.264l-.138-.924c1.52-.359 3.074-.57 4.638-.632v.001Zm8.84 1.304c2.14.938 2.925 2.006 2.925 2.006h-6.92s-.228-.197-.659-.472l4.654-1.534Z" fill="#99A0A6"></path></g><defs><clipPath id="bi_p24__a"><path fill="#fff" transform="translate(3 8.097)" d="M0 0h26v14.793H0z"></path></clipPath></defs></svg>';
	}
}
