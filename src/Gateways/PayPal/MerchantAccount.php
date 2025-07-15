<?php
/**
 * PayPal Merchant Account Details
 *
 * Contains information about the connected PayPal account.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.11
 */

namespace EDD\Gateways\PayPal;

use EDD\Gateways\PayPal\Exceptions\InvalidMerchantDetails;
use EDD\Gateways\PayPal\Exceptions\MissingMerchantDetails;

class MerchantAccount {

	/**
	 * @var string Merchant ID of the seller's PayPal account.
	 */
	public $merchant_id;

	/**
	 * @var bool Indicates whether the seller account can receive payments.
	 */
	public $payments_receivable;

	/**
	 * @var bool Indicates whether the primary email of the seller has been confirmed.
	 */
	public $primary_email_confirmed;

	/**
	 * @var array An array of all products that are integrated with the partner for the seller.
	 */
	public $products;

	/**
	 * @var \WP_Error
	 */
	private $wp_error;

	/**
	 * @var string The tracking ID of the merchant account.
	 */
	public $tracking_id;

	/**
	 * @var string The legal name of the merchant account.
	 */
	public $legal_name;

	/**
	 * MerchantAccount constructor.
	 *
	 * @param array $details
	 */
	public function __construct( $details ) {
		foreach ( $details as $key => $value ) {
			if ( ! property_exists( $this, $key ) ) {
				continue;
			}

			$this->{$key} = $value;
		}

		$this->wp_error = new \WP_Error();
	}

	/**
	 * Builds a new MerchantAccount object from a JSON object.
	 *
	 * @since 2.11
	 *
	 * @param string $json
	 *
	 * @return MerchantAccount
	 */
	public static function from_json( $json ) {
		$merchant_details = json_decode( $json, true );
		if ( empty( $merchant_details ) || ! is_array( $merchant_details ) ) {
			$merchant_details = array();
		}

		return new MerchantAccount( $merchant_details );
	}

	/**
	 * Converts the account details to JSON.
	 *
	 * @since 2.11
	 *
	 * @return string|false
	 */
	public function to_json() {
		return json_encode( get_object_vars( $this ) );
	}

	/**
	 * Determines whether or not the details are valid.
	 * Note: This does NOT determine actual "ready to accept payments" status, it just
	 * verifies that we have all the information we need to determine that.
	 *
	 * @throws MissingMerchantDetails
	 * @throws InvalidMerchantDetails
	 */
	public function validate() {
		if ( empty( $this->merchant_id ) ) {
			throw new MissingMerchantDetails();
		}

		$required_properties = array(
			'merchant_id',
			'payments_receivable',
			'primary_email_confirmed',
			'products',
		);

		$valid_properties = array();
		foreach( $required_properties as $property ) {
			if ( property_exists( $this, $property ) && ! is_null( $this->{$property} ) ) {
				$valid_properties[] = $property;
			}
		}

		$difference = array_diff( $required_properties, $valid_properties );

		if ( $difference ) {
			throw new InvalidMerchantDetails(
				'Please click "Re-Check Payment Status" below to confirm your payment status.'
			);
		}
	}

	/**
	 * Determines whether or not the account is ready to accept payments.
	 *
	 * @since 2.11
	 *
	 * @return bool
	 */
	public function is_account_ready() {
		if ( ! $this->payments_receivable ) {
			$this->wp_error->add( 'payments_receivable', __( 'Your account is unable to receive payments. Please contact PayPal customer support.', 'easy-digital-downloads' ) );
		}

		if ( ! $this->primary_email_confirmed ) {
			$this->wp_error->add(
				'primary_email_confirmed',
				__( 'Your PayPal email address needs to be confirmed.', 'easy-digital-downloads' )
			);
		}

		return empty( $this->wp_error->errors );
	}

	/**
	 * Retrieves errors preventing the account from being "ready".
	 *
	 * @see   MerchantAccount::is_account_ready()
	 *
	 * @since 2.11
	 *
	 * @return \WP_Error
	 */
	public function get_errors() {
		return $this->wp_error;
	}

	/**
	 * Returns the option name for the current mode.
	 *
	 * @since 2.11
	 *
	 * @return string
	 */
	private static function get_option_name() {
		return sprintf(
			'edd_paypal_%s_merchant_details',
			edd_is_test_mode() ? API::MODE_SANDBOX : API::MODE_LIVE
		);
	}

	/**
	 * Saves the merchant details.
	 *
	 * @since 2.11
	 */
	public function save() {
		update_option( self::get_option_name(), $this->to_json() );
	}

	/**
	 * Retrieves the saved merchant details.
	 *
	 * @since 2.11
	 *
	 * @return MerchantAccount
	 * @throws \InvalidArgumentException
	 */
	public static function retrieve() {
		return self::from_json( get_option( self::get_option_name(), '' ) );
	}

}
