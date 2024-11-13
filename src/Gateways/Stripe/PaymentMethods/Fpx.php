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
 * Fpx class.
 */
class Fpx extends Method {

	/**
	 * The ID of the payment method.
	 *
	 * @since 3.3.5
	 * @var string
	 */
	protected static $id = 'fpx';

	/**
	 * The supported currencies for the payment method.
	 *
	 * @since 3.3.5
	 * @var array
	 */
	public static $currencies = array( 'MYR' );

	/**
	 * The supported countries for the payment method.
	 *
	 * @since 3.3.5
	 * @var array
	 */
	public static $countries = array( 'my' );

	/**
	 * Gets the label for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_label() {
		return __( 'FPX', 'easy-digital-downloads' );
	}

	/**
	 * Gets the icon for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_icon(): string {
		return '<svg aria-hidden="true" width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M29.174 13.9757c-2.8569-3.6805-5.7383-7.34192-8.644-10.98397-.46-.578-1.132-1.27-1.916-.876-.53.264-1.012 1.05-1.066 1.64-.178 1.938-.164 3.89-.184 5.836-.002.22.22.45997.38.66197 1.208 1.542 2.436 3.07 3.636 4.616.334.43.52.78.58 1.114-.06.468-.246.704-.58 1.134-1.2 1.546-2.428 3.08-3.636 4.622-.16.204-.382.452-.38.672.02 1.946.006 3.898.184 5.834.054.59.536 1.376 1.066 1.64.784.392 1.456-.304 1.916-.882 2.9059-3.6446 5.7872-7.3087 8.644-10.992.508-.654.776-1.092.826-2.028-.05-.68-.32-1.354-.826-2.008Z" fill="#1F2C5C"></path><path fill-rule="evenodd" d="M2.826 13.9757c2.852-3.68 5.74-7.33797 8.644-10.98397.46-.578 1.132-1.27 1.916-.876.53.264 1.012 1.05 1.066 1.64.178 1.938.164 3.89.184 5.836.002.22-.22.45997-.38.66197-1.208 1.542-2.436 3.07-3.636 4.616-.334.43-.522.78-.58 1.114.058.468.246.704.58 1.134 1.2 1.546 2.428 3.08 3.636 4.622.16.204.382.452.38.672-.02 1.946-.006 3.898-.184 5.834-.054.59-.536 1.376-1.066 1.64-.784.392-1.456-.304-1.916-.882-2.90579-3.6447-5.78719-7.3088-8.644-10.992-.508-.654-.776-1.092-.826-2.028.05-.68.32-1.354.826-2.008Z" fill="#1A8ACB"></path></svg>';
	}
}
