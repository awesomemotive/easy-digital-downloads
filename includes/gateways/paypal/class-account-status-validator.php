<?php
/**
 * Account Status Validator
 *
 * This validator helps to check the status of a PayPal merchant account
 * and ensure it's ready to receive payments. This is used to display
 * the connection status on the admin settings page, and also to help
 * determine if we should allow the merchant to start processing payments.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.11
 */

namespace EDD\Gateways\PayPal;

use EDD\Gateways\PayPal;

class AccountStatusValidator {

	/**
	 * @var bool Whether or not we have REST API credentials.
	 */
	public $has_rest_credentials = false;

	/**
	 * @var \WP_Error Errors thrown when checking REST API credentials.
	 */
	public $errors_for_credentials;

	/**
	 * @var array|false Merchant details, if any.
	 */
	public $merchant_details = false;

	/**
	 * @var \WP_Error Errors thrown when checking merchant account status.
	 */
	public $errors_for_merchant_account;

	/**
	 * @var null|object Webhook object from the API.
	 */
	public $webhook;

	/**
	 * @var array Enabled webhook event names.
	 */
	public $enabled_webhook_events = array();

	/**
	 * @var \WP_Error Errors thrown when checking webhook status.
	 */
	public $errors_for_webhook;

	/**
	 * @var string Identifier of the connected account -- if available.
	 */
	public $connected_account = '';

	/**
	 * @var string Current store mode.
	 */
	private $mode;

	/**
	 * AccountStatusValidator constructor.
	 *
	 * @param string $mode Mode to check (`live` or `sandbox`). If omitted, current store mode is used.
	 */
	public function __construct( $mode = '' ) {
		if ( empty( $mode ) ) {
			$mode = edd_is_test_mode() ? PayPal\API::MODE_SANDBOX : PayPal\API::MODE_LIVE;
		}
		$this->mode = $mode;

		// Set up base error objects.
		$this->errors_for_credentials      = new \WP_Error();
		$this->errors_for_merchant_account = new \WP_Error();
		$this->errors_for_webhook          = new \WP_Error();
	}

	/**
	 * Checks everything.
	 *
	 * @since 2.11
	 */
	public function check() {
		$this->check_rest();
		$this->check_merchant_account();
		$this->check_webhook();
	}

	/**
	 * Checks for valid REST API credentials.
	 *
	 * @since 2.11
	 */
	public function check_rest() {
		$credentials = array(
			'client_id'     => edd_get_option( 'paypal_' . $this->mode . '_client_id' ),
			'client_secret' => edd_get_option( 'paypal_' . $this->mode . '_client_secret' ),
		);

		foreach ( $credentials as $credential ) {
			if ( empty( $credential ) ) {
				$this->errors_for_credentials->add( 'no_credentials', __( 'Not connected.', 'easy-digital-downloads' ) );
				break;
			}
		}
	}

	/**
	 * Determines if the merchant account is ready to accept payments.
	 * It's possible (I think) to have valid API credentials ( @see AccountStatusValidator::check_rest() )
	 * but still be unable to start taking payments, such as because your account
	 * email hasn't been confirmed yet.
	 *
	 * @since 2.11
	 */
	public function check_merchant_account() {
		try {
			$this->merchant_details = MerchantAccount::retrieve();
			$this->merchant_details->validate();

			if ( ! $this->merchant_details->is_account_ready() ) {
				foreach ( $this->merchant_details->get_errors()->get_error_codes() as $code ) {
					$this->errors_for_merchant_account->add( $code, $this->merchant_details->get_errors()->get_error_message( $code ) );
				}
			}
		} catch ( Exceptions\MissingMerchantDetails $e ) {
			$this->errors_for_merchant_account->add( 'missing_merchant_details', __( 'Missing merchant details from PayPal. Please reconnect and make sure you click the button to be redirected back to your site.', 'easy-digital-downloads' ) );
		} catch ( Exceptions\InvalidMerchantDetails $e ) {
			$this->errors_for_merchant_account->add( 'invalid_merchant_details', $e->getMessage() );
		}
	}

	/**
	 * Confirms that the webhook is set up and has all the necessary events registered.
	 *
	 * @since 2.11
	 */
	public function check_webhook() {
		try {
			$this->webhook = Webhooks\get_webhook_details( $this->mode );
			if ( empty( $this->webhook->id ) ) {
				throw new \Exception( __( 'Webhook not configured. Some actions may not work properly.', 'easy-digital-downloads' ) );
			}

			// Now compare the events to make sure we have them all.
			$expected_events = array_keys( Webhooks\get_webhook_events( $this->mode ) );

			if ( ! empty( $this->webhook->event_types ) && is_array( $this->webhook->event_types ) ) {
				foreach ( $this->webhook->event_types as $event_type ) {
					if ( ! empty( $event_type->name ) && ! empty( $event_type->status ) && 'ENABLED' === strtoupper( $event_type->status ) ) {
						$this->enabled_webhook_events[] = $event_type->name;
					}
				}
			}

			$missing_events = array_diff( $expected_events, $this->enabled_webhook_events );
			$number_missing = count( $missing_events );

			if ( $number_missing ) {
				$this->errors_for_webhook->add( 'missing_events', _n(
					'Webhook is configured but missing an event. Click "Sync Webhook" to correct this.',
					'Webhook is configured but missing events. Click "Sync Webhook" to correct this.',
					$number_missing,
					'easy-digital-downloads'
				) );
			}
		} catch ( \Exception $e ) {
			$this->errors_for_webhook->add( 'webhook_missing', $e->getMessage() );
		}
	}

}
