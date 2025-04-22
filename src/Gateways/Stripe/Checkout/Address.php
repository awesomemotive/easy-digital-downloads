<?php
/**
 * Address handling for the checkout.
 *
 * @package     EDD\Gateways\Stripe\Checkout
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.8
 */

namespace EDD\Gateways\Stripe\Checkout;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Checkout\Address as CheckoutAddress;

/**
 * Address class.
 */
class Address extends CheckoutAddress {

	/**
	 * Gets the fields to display.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	public function get_fields() {
		$fields = parent::get_fields();

		if ( ! in_array( 'address', $fields, true ) && \EDD\Gateways\Stripe\PaymentMethods::affirm_requires_support() ) {
			return array_unique( array_merge( array( 'country', 'address', 'address2', 'city', 'state', 'zip' ), $fields ) );
		}

		return $fields;
	}

	/**
	 * Sets up the customer data.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	protected function set_up_customer() {
		$customer = parent::set_up_customer();

		if ( ! is_user_logged_in() ) {
			return $customer;
		}

		$existing_cards = edd_stripe_get_existing_cards( get_current_user_id() );
		if ( empty( $existing_cards ) ) {
			return $customer;
		}

		foreach ( $existing_cards as $card ) {
			if ( false === $card['default'] ) {
				continue;
			}

			$source              = $card['source'];
			$customer['address'] = array(
				'line1'   => $source->address_line1,
				'line2'   => $source->address_line2,
				'city'    => $source->address_city,
				'zip'     => $source->address_zip,
				'state'   => $source->address_state,
				'country' => $source->address_country,
			);
			break;
		}

		return $customer;
	}
}
