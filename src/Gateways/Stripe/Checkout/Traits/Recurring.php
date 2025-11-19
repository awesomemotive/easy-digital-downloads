<?php
/**
 * Recurring trait.
 *
 * @package EDD\Gateways\Stripe\Checkout\Traits
 * @copyright (c) 2025, Sandhills Development, LLC
 * @license https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.1
 */

namespace EDD\Gateways\Stripe\Checkout\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

trait Recurring {

	/**
	 * Indicates whether the cart contains a subscription.
	 *
	 * @var bool
	 */
	private $cart_contains_subscription;

	/**
	 * Checks if the cart contains a subscription.
	 *
	 * @since 3.3.5
	 * @return bool Returns true if the cart contains a subscription, false otherwise.
	 */
	private function cart_contains_subscription() {
		if ( is_null( $this->cart_contains_subscription ) ) {
			$this->cart_contains_subscription = (bool) ( function_exists( 'edd_recurring' ) && edd_recurring()->cart_contains_recurring() );
		}

		return $this->cart_contains_subscription;
	}

	/**
	 * Checks if the cart has a free trial.
	 *
	 * @since 3.3.5
	 * @return bool Returns true if the cart has a free trial, false otherwise.
	 */
	private function cart_has_free_trial() {
		return $this->cart_contains_subscription() && edd_recurring()->cart_has_free_trial();
	}
}
