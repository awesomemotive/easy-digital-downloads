<?php
/**
 * Stripe payment method class.
 *
 * @since 3.3.6
 * @package EDD\Gateways\Stripe\PaymentMethods
 */

namespace EDD\Gateways\Stripe\PaymentMethods;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Twint class.
 */
class Twint extends Method {

	/**
	 * The ID of the payment method.
	 *
	 * @since 3.3.6
	 * @var string
	 */
	protected static $id = 'twint';

	/**
	 * The supported currencies for the payment method.
	 *
	 * @since 3.3.6
	 * @var array
	 */
	public static $currencies = array( 'CHF' );

	/**
	 * The supported countries for the payment method.
	 *
	 * @since 3.3.6
	 * @var array
	 */
	public static $countries = array(
		'ch',
	);

	/**
	 * Gets the label for the payment method.
	 *
	 * @since 3.3.6
	 * @return string
	 */
	public static function get_label() {
		return __( 'TWINT', 'easy-digital-downloads' );
	}

	/**
	 * Gets the icon for the payment method.
	 *
	 * @since 3.3.6
	 * @return string
	 */
	public static function get_icon(): string {
		return '<svg aria-hidden="true" width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><path fill="#000" d="M0 0h32v32H0z"></path><path fill="#fff" d="M27 21.225c0 .323-.233.724-.512.878l-9.976 5.717c-.28.17-.745.17-1.024 0l-9.976-5.717c-.28-.17-.512-.555-.512-.878V9.775c0-.323.233-.724.512-.878l9.976-5.717c.28-.17.745-.17 1.024 0l9.976 5.717c.28.17.512.555.512.878v11.45Z"></path><path fill="url(#bi_twint__a)" d="m20.633 15.285-2.56 3.652-1.317-1.96 1.517-2.197c.276-.386.889-1.47.184-2.94-.567-1.187-1.793-1.766-2.866-1.766-1.073 0-2.253.535-2.866 1.767-.705 1.41-.092 2.523.169 2.895 0 0 .843 1.202 1.547 2.211l1.15 1.604 1.716 2.538c.016.015.291.416.767.416.46 0 .735-.401.781-.446l4.03-5.774h-2.252Zm-5.042.089s-.674-.995-1.103-1.678c-.476-.742.06-1.84 1.103-1.84 1.057 0 1.578 1.098 1.103 1.84-.429.698-1.103 1.678-1.103 1.678Z"></path><path fill="url(#bi_twint__b)" d="m13.108 18.818-2.528-3.43s-.675-.994-1.104-1.677c-.475-.742.062-1.84 1.104-1.84.138 0 .26.014.367.044l.89-1.574a3.45 3.45 0 0 0-1.257-.252c-1.073 0-2.253.534-2.866 1.766-.705 1.41-.092 2.524.168 2.895l4.414 6.339c.03.06.322.46.782.46.475 0 .735-.386.781-.445l1.333-1.96-1.149-1.633-.935 1.307Z"></path><defs><radialGradient id="bi_twint__a" cx="0" cy="0" r="1" gradientTransform="rotate(45 -4.41 19.541) scale(21.2876)" gradientUnits="userSpaceOnUse"><stop stop-color="#FC0"></stop><stop offset="0.092" stop-color="#FFC800"></stop><stop offset="0.174" stop-color="#FFBD00"></stop><stop offset="0.253" stop-color="#FFAB00"></stop><stop offset="0.33" stop-color="#FF9100"></stop><stop offset="0.405" stop-color="#FF7000"></stop><stop offset="0.479" stop-color="#FF4700"></stop><stop offset="0.55" stop-color="#FF1800"></stop><stop offset="0.582" stop-color="red"></stop><stop offset="1" stop-color="red"></stop></radialGradient><linearGradient id="bi_twint__b" x1="6.737" x2="15.421" y1="12.026" y2="20.71" gradientUnits="userSpaceOnUse"><stop stop-color="#00B4E6"></stop><stop offset="0.201" stop-color="#00B0E3"></stop><stop offset="0.39" stop-color="#01A5DB"></stop><stop offset="0.574" stop-color="#0292CD"></stop><stop offset="0.755" stop-color="#0377BA"></stop><stop offset="0.932" stop-color="#0455A1"></stop><stop offset="1" stop-color="#054696"></stop></linearGradient></defs></svg>';
	}
}
