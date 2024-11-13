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
 * Giropay class.
 */
class Giropay extends Method {

	/**
	 * The ID of the payment method.
	 *
	 * @since 3.3.5
	 * @var string
	 */
	protected static $id = 'giropay';

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
		return __( 'giropay', 'easy-digital-downloads' );
	}

	/**
	 * Gets the icon for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_icon(): string {
		return '<svg aria-hidden="true" height="32" width="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#F5F6F8" d="M0 0h32v32H0z"></path><g clip-path="url(#bi_giropay__a)" fill-rule="evenodd" clip-rule="evenodd"><path d="M4 11.191c0-1.485 1.239-2.69 2.766-2.69h18.468C26.762 8.5 28 9.705 28 11.19v9.618c0 1.486-1.238 2.691-2.766 2.691H6.766C5.239 23.5 4 22.295 4 20.81v-9.62Zm1.02 9.6c0 .945.783 1.71 1.75 1.71h9.213v-13H6.77c-.967 0-1.75.764-1.75 1.708v9.582Zm13.749-.103h2.272v-3.57h.025c.43.781 1.29 1.072 2.084 1.072 1.957 0 3.004-1.615 3.004-3.558 0-1.59-.997-3.32-2.815-3.32-1.035 0-1.994.418-2.45 1.339h-.025v-1.185h-2.095v9.222Zm5.037-6.005c0 1.047-.518 1.766-1.376 1.766-.758 0-1.39-.72-1.39-1.679 0-.984.556-1.716 1.39-1.716.885 0 1.376.759 1.376 1.629Z" fill="#04337B"></path><path d="M14.153 17.173v-5.71h-2.085v1.167h-.025c-.487-.795-1.282-1.317-2.266-1.317-2.073 0-3.008 1.441-3.008 3.365 0 1.913 1.14 3.253 2.969 3.253.922 0 1.69-.348 2.24-1.106h.026v.348c0 1.267-.717 1.875-2.047 1.875-.96 0-1.55-.198-2.253-.534l-.115 1.764a7.96 7.96 0 0 0 2.547.41c2.688 0 4.018-.857 4.018-3.515Zm-3.698-.956c-.69 0-1.305-.57-1.305-1.576 0-.88.448-1.614 1.305-1.614 1.037 0 1.472.807 1.472 1.527 0 .993-.653 1.663-1.472 1.663Z" fill="#EE3525"></path></g><defs><clipPath id="bi_giropay__a"><path fill="#fff" transform="translate(4 8.5)" d="M0 0h24v15H0z"></path></clipPath></defs></svg>';
	}
}
