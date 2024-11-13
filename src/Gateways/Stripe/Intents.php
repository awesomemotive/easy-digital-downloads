<?php
/**
 * Intents class.
 *
 * @package EDD\Gateways\Stripe
 */

namespace EDD\Gateways\Stripe;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Intents class.
 */
class Intents {

	/**
	 * Retrieves an intent by its ID. Useful when needing to retrieve a PaymentIntent or SetupIntent by its ID
	 * without knowing which type of intent it is.
	 *
	 * @since 3.3.5
	 * @param string $intent_id The ID of the intent to retrieve.
	 * @param array  $args      Optional additional arguments for retrieving the intent.
	 *
	 * @return \EDD\Vendors\Stripe\PaymentIntent|\EDD\Vendors\Stripe\SetupIntent|false
	 */
	public static function get( string $intent_id, $args = array() ) {
		try {
			$intent = edds_api_request(
				'PaymentIntent',
				'retrieve',
				self::parse_args( $intent_id, $args )
			);
		} catch ( \Exception $e ) {
			try {
				$intent = edds_api_request(
					'SetupIntent',
					'retrieve',
					$intent_id
				);
			} catch ( \Exception $e ) {
				return false;
			}
		}

		return $intent;
	}

	/**
	 * Parse the arguments for the API request.
	 *
	 * @since 3.3.5
	 * @param string $intent_id The ID of the intent to retrieve.
	 * @param array  $args      Optional additional arguments for retrieving the intent.
	 * @return string|array
	 */
	private static function parse_args( string $intent_id, $args = array() ) {
		if ( empty( $args ) ) {
			return $intent_id;
		}

		return array(
			'id' => $intent_id,
			$args,
		);
	}
}
