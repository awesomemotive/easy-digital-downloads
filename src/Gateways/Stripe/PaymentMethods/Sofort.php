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
 * Sofort class.
 */
class Sofort extends Method {

	/**
	 * The ID of the payment method.
	 *
	 * @since 3.3.5
	 * @var string
	 */
	protected static $id = 'sofort';

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
		return __( 'SOFORT', 'easy-digital-downloads' );
	}

	/**
	 * Gets the icon for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_icon(): string {
		return '<svg aria-hidden="true" height="32" width="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#2E3235" d="M0 0h32v32H0z"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M6.2 26h16.232l3.334-20H9.534L6.2 26Zm9.371-12.366c0 .357.302.536 1.616 1.208 1.68.865 2.74 1.76 2.74 3.385 0 1.999-1.647 3.549-4.704 3.549-1.964 0-3.264-.671-4.166-1.327l.507-3.102c.871.82 2.345 1.625 3.613 1.625.728 0 1.282-.253 1.282-.7 0-.418-.332-.627-1.649-1.313-1.471-.76-2.709-1.625-2.709-3.28 0-1.924 1.696-3.52 4.659-3.52 1.49 0 3.105.52 4.858 1.516a7.348 7.348 0 0 0-1.457 2.574c-1.24-1.192-2.504-1.287-3.338-1.287-.729 0-1.252.224-1.252.672Z" fill="#E5E5E0"></path></svg>';
	}
}
