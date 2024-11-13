<?php
/**
 * Stripe payment method card class.
 *
 * @since 3.3.5
 * @package EDD\Gateways\Stripe\PaymentMethods
 */

namespace EDD\Gateways\Stripe\PaymentMethods;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Affirm class.
 */
class Affirm extends Method {

	/**
	 * The ID of the payment method.
	 *
	 * @since 3.3.5
	 * @var string
	 */
	protected static $id = 'affirm';

	/**
	 * The currencies supported by the payment method.
	 *
	 * @since 3.3.5
	 * @var array
	 */
	public static $currencies = array( 'USD', 'CAD' );

	/**
	 * The countries supported by the payment method.
	 *
	 * @since 3.3.5
	 * @var array
	 */
	public static $countries = array( 'us', 'ca' );

	/**
	 * Gets the label for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_label() {
		return __( 'Affirm', 'easy-digital-downloads' );
	}

	/**
	 * Gets the icon for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_icon(): string {
		return '<svg aria-hidden="true" height="32" width="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#4A4AF4" d="M0 0h32v32H0z"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M7.928 23.279A9.596 9.596 0 0 1 6.349 18c0-5.321 4.33-9.65 9.651-9.65 5.32 0 9.65 4.329 9.65 9.65a9.59 9.59 0 0 1-1.58 5.279h2.703A11.925 11.925 0 0 0 28 18c0-6.617-5.383-12-12-12S4 11.383 4 18c0 1.893.443 3.685 1.227 5.279h2.701Z" fill="#fff"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M15.33 21.365c-.922 0-1.43-.394-1.43-1.045 0-1.353 1.654-1.68 3.807-1.68 0 1.417-.948 2.725-2.377 2.725Zm.832-9.066c-1.757 0-3.78.828-4.877 1.704l1.002 2.109c.88-.804 2.303-1.495 3.588-1.495 1.22 0 1.894.41 1.894 1.231 0 .552-.448.864-1.29.941-3.167.292-5.626 1.28-5.626 3.711 0 1.927 1.39 3.092 3.673 3.092 1.53 0 2.732-.85 3.38-1.972v1.659h2.85v-6.947c0-2.868-1.995-4.033-4.594-4.033Z" fill="#fff"></path></svg>';
	}
}
