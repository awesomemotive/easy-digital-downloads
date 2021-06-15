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
	private $errors;

	/**
	 * MerchantAccount constructor.
	 *
	 * @param array $details
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( $details ) {
		$this->errors = new \WP_Error();

		if ( ! $this->is_valid( $details ) ) {
			throw new \InvalidArgumentException( __( 'Invalid merchant details.', 'easy-digital-downloads' ) );
		}

		foreach ( $details as $key => $value ) {
			$this->{$key} = $value;
		}
	}

	/**
	 * Builds a new MerchantAccount object from a JSON object.
	 *
	 * @since 2.11
	 *
	 * @param string $json
	 *
	 * @return MerchantAccount
	 * @throws \InvalidArgumentException
	 */
	public static function from_json( $json ) {
		return new MerchantAccount( json_decode( $json, true ) );
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
	 * @param array $details
	 *
	 * @return bool
	 */
	private function is_valid( $details ) {
		$required_properties = array(
			'merchant_id',
			'payments_receivable',
			'primary_email_confirmed',
			'products',
		);

		return ! array_diff( $required_properties, array_keys( $details ) );
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
			$this->errors->add( 'payments_receivable', __( 'Account is unable to receive payments.', 'easy-digital-downloads' ) );
		}

		if ( ! $this->primary_email_confirmed ) {
			$this->errors->add(
				'primary_email_confirmed',
				__( 'Your PayPal email needs to be confirmed before you can accept payments.', 'easy-digital-downloads' )
			);
		}

		return empty( $this->errors->errors );
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
		return $this->errors;
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
