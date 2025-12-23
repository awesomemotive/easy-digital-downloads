<?php
/**
 * Data Trait
 *
 * Holds the methods for validating the PayPal IPN.
 *
 * @package    EDD\Gateways\PayPal\Traits
 * @copyright  Copyright (c) 2025, Sandhills Development, LLC
 * @license    https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      3.6.3
 */

namespace EDD\Gateways\PayPal\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Trait for getting data from the PayPal IPN.
 *
 * @since 3.6.3
 */
trait Data {

	/**
	 * Gets the encoded data array.
	 *
	 * @since 3.2.0
	 * @return array|bool
	 */
	private function get_encoded_data_array() {

		nocache_headers();

		// Set initial post data to empty string.
		$post_data = '';

		// Fallback just in case post_max_size is lower than needed.
		if ( ini_get( 'allow_url_fopen' ) ) {
			$post_data = file_get_contents( 'php://input' );
		} else {
			// If allow_url_fopen is not enabled, then make sure that post_max_size is large enough.
			ini_set( 'post_max_size', '12M' );
		}

		// Start the encoded data collection with notification command.
		$encoded_data = 'cmd=_notify-validate';

		// Get current arg separator.
		$arg_separator = edd_get_php_arg_separator_output();

		// Verify there is a post_data.
		if ( $post_data || strlen( $post_data ) > 0 ) {

			// Append the data.
			$encoded_data .= $arg_separator . $post_data;

		} else {

			// Check if POST is empty.
			if ( empty( $this->posted ) ) {
				// Nothing to do.
				$this->debug_log( 'post data not detected, bailing' );
				return false;
			}

			// Loop through each POST.
			foreach ( $this->posted as $key => $value ) {

				// Encode the value and append the data.
				$encoded_data .= $arg_separator . "$key=" . urlencode( $value );
			}
		}

		// Convert collected post data to an array.
		parse_str( $encoded_data, $encoded_data_array );

		return $encoded_data_array;
	}

	/**
	 * Note: Amounts get more properly sanitized on insert.
	 *
	 * @see EDD_Subscription::add_payment()
	 * @since 3.2.0
	 * @return float
	 */
	private function get_amount() {
		if ( isset( $this->posted['amount'] ) ) {
			return (float) $this->posted['amount'];
		}
		if ( isset( $this->posted['mc_gross'] ) ) {
			return (float) $this->posted['mc_gross'];
		}

		return 0;
	}

	/**
	 * Gets the order ID from the transaction ID.
	 *
	 * @since 3.2.0
	 * @return int
	 */
	private function get_order_id() {
		return ! empty( $this->posted['parent_txn_id'] ) ?
			edd_get_order_id_from_transaction_id( $this->posted['parent_txn_id'] ) :
			0;
	}

	/**
	 * Gets the payment date from the IPN data.
	 *
	 * @since 3.2.0
	 * @return false|string
	 */
	private function get_payment_date() {
		if ( empty( $this->posted['payment_date'] ) ) {
			return false;
		}
		// Create a DateTime object of the payment_date, so we can adjust as needed.
		$subscription_payment_date = new \DateTime( $this->posted['payment_date'] );

		// To make sure we don't inadvertently fail, make sure the date was parsed correctly before working with it.
		if ( ! $subscription_payment_date instanceof \DateTime ) {
			return false;
		}

		/**
		 * Convert to GMT, as that is what EDD 3.0 expects the times to be in.
		 */
		$subscription_payment_date->setTimezone( new \DateTimeZone( 'GMT' ) );

		// Now add the date into the arguments for creating the renewal payment.
		return $subscription_payment_date->format( 'Y-m-d H:i:s' );
	}
}
