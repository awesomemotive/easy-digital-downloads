<?php
/**
 * Webhook Validator
 *
 * @link       https://developer.paypal.com/docs/api/webhooks/v1/#verify-webhook-signature_post
 *
 * @package    easy-digital-downloads
 * @subpackage Gateways\PayPal\Webhooks
 * @copyright  Copyright (c) 2021, Sandhills Development, LLC
 * @license    GPL2+
 * @since      2.11
 */

namespace EDD\Gateways\PayPal\Webhooks;

use EDD\Gateways\PayPal\API;
use EDD\Gateways\PayPal\Exceptions\API_Exception;

class Webhook_Validator {

	/**
	 * Headers from the webhook
	 *
	 * @var array
	 * @since 2.11
	 */
	private $headers;

	/**
	 * Webhook event
	 *
	 * @var object
	 * @since 2.11
	 */
	private $event;

	/**
	 * Maps the incoming header key to the outgoing API request key.
	 *
	 * @var string[]
	 * @since 2.11
	 */
	private $header_map = array(
		'PAYPAL-AUTH-ALGO'         => 'auth_algo',
		'PAYPAL-CERT-URL'          => 'cert_url',
		'PAYPAL-TRANSMISSION-ID'   => 'transmission_id',
		'PAYPAL-TRANSMISSION-SIG'  => 'transmission_sig',
		'PAYPAL-TRANSMISSION-TIME' => 'transmission_time'
	);

	/**
	 * Webhook_Validator constructor.
	 *
	 * @param array  $headers
	 * @param object $event
	 *
	 * @since 2.11
	 */
	public function __construct( $headers, $event ) {
		$this->headers = array_change_key_case( $headers, CASE_UPPER );
		$this->event   = $event;
	}

	/**
	 * Verifies the signature.
	 *
	 * @since 2.11
	 * @return true
	 * @throws API_Exception
	 * @throws \InvalidArgumentException
	 */
	public function verify_signature() {
		$api = new API();

		$response = $api->make_request( 'v1/notifications/verify-webhook-signature', $this->get_body() );

		if ( 200 !== $api->last_response_code ) {
			throw new API_Exception( sprintf(
				'Invalid response code: %d. Response: %s',
				$api->last_response_code,
				json_encode( $response )
			) );
		}

		if ( empty( $response->verification_status ) || 'SUCCESS' !== strtoupper( $response->verification_status ) ) {
			throw new API_Exception( sprintf(
				'Verification failure. Response: %s',
				json_encode( $response )
			) );
		}

		return true;
	}

	/**
	 * Validates that we have all the required headers.
	 *
	 * @since 2.11
	 * @throws \InvalidArgumentException
	 */
	private function validate_headers() {
		foreach ( array_keys( $this->header_map ) as $required_key ) {
			if ( ! array_key_exists( $required_key, $this->headers ) ) {
				throw new \InvalidArgumentException( sprintf(
					'Missing PayPal header %s',
					$required_key
				) );
			}
		}
	}

	/**
	 * Retrieves the webhook ID for the current mode.
	 *
	 * @since 2.11
	 * @return string
	 * @throws \Exception
	 */
	private function get_webhook_id() {
		$id = get_webhook_id();

		if ( empty( $id ) ) {
			throw new \Exception( 'No webhook created in current mode.' );
		}

		return $id;
	}

	/**
	 * Builds arguments for the body of the API request.
	 *
	 * @return array
	 * @throws \InvalidArgumentException
	 * @throws \Exception
	 */
	private function get_body() {
		$this->validate_headers();

		$body = array(
			'webhook_id'    => $this->get_webhook_id(),
			'webhook_event' => $this->event
		);

		// Add arguments from the headers.
		foreach ( $this->header_map as $header_key => $body_key ) {
			$body[ $body_key ] = $this->headers[ $header_key ];
		}

		return $body;
	}

	/**
	 * Validates the webhook from the current request.
	 *
	 * @param object $event Webhook event.
	 *
	 * @since 2.11
	 * @return true
	 * @throws API_Exception
	 * @throws \InvalidArgumentException
	 */
	public static function validate_from_request( $event ) {
		$validator = new Webhook_Validator( getallheaders(), $event );

		return $validator->verify_signature();
	}

}
