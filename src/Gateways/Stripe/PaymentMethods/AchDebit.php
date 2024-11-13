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
 * Achdebit class.
 */
class AchDebit extends Method {

	/**
	 * The ID of the payment method.
	 *
	 * @since 3.3.5
	 * @var string
	 */
	protected static $id = 'ach_debit';

	/**
	 * The currencies supported by the payment method.
	 *
	 * @since 3.3.5
	 * @var array
	 */
	public static $currencies = array( 'USD' );

	/**
	 * The countries supported by the payment method.
	 *
	 * @since 3.3.5
	 * @var array
	 */
	public static $countries = array( 'us' );

	/**
	 * Gets the label for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_label() {
		return __( 'ACH Direct Debit', 'easy-digital-downloads' );
	}

	/**
	 * Gets the icon for the payment method.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public static function get_icon(): string {
		return '<svg aria-hidden="true" height="32" width="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#D8DEE4" d="M0 0h32v32H0z"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M15.592 5.093a.938.938 0 0 1 .816 0l8.885 4.29c.433.209.707.646.707 1.126V12.5c0 .69-.56 1.25-1.25 1.25h-1.875v5.983l2.434 1.217c.424.212.691.645.691 1.118v1.682c0 .69-.56 1.25-1.25 1.25H7.25C6.56 25 6 24.44 6 23.75v-1.68c0-.476.27-.91.696-1.122l2.462-1.216-.028-5.982H7.25c-.69 0-1.25-.56-1.25-1.25v-1.991c0-.48.275-.917.707-1.126l8.885-4.29ZM21 20.313V13.75h-1.875v6.563a.937.937 0 1 1-1.875 0V13.75h-2.5v6.563a.937.937 0 1 1-1.875 0V13.75h-1.87l.031 6.558a.937.937 0 0 1-.522.845l-2.639 1.304v.668h16.25v-.67l-2.607-1.304a.937.937 0 0 1-.518-.838Zm3.125-8.438v-.974L16 6.979 7.875 10.9v.974h16.25Z" fill="#474E5A"></path></svg>';
	}
}
