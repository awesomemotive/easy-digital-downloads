<?php
/**
 * PayPal REST API Token
 *
 * @package    easy-digital-downloads
 * @subpackage Gateways\PayPal
 * @copyright  Copyright (c) 2021, Sandhills Development, LLC
 * @license    GPL2+
 */

namespace EDD\Gateways\PayPal;

class Token {

	/**
	 * Token object
	 *
	 * @var object
	 */
	private $token_object;

	/**
	 * Token constructor.
	 *
	 * @param $token_object
	 *
	 * @throws \RuntimeException
	 */
	public function __construct( $token_object ) {
		if ( is_object( $token_object ) && ! isset( $token_object->created ) ) {
			$token_object->created = time();
		}

		if ( ! $this->is_valid( $token_object ) ) {
			throw new \RuntimeException( __( 'Invalid token.', 'easy-digital-downloads' ) );
		}

		$this->token_object = $token_object;
	}

	/**
	 * Creates a new token from a JSON string.
	 *
	 * @param string $json
	 *
	 * @return Token
	 * @throws \Exception
	 */
	public static function from_json( $json ) {
		return new Token( json_decode( $json ) );
	}

	/**
	 * Returns the token object as a JSON string.
	 *
	 * @since 2.11
	 * @return string|false
	 */
	public function to_json() {
		return json_encode( $this->token_object );
	}

	/**
	 * Determines whether or not the token has expired.
	 *
	 * @since 2.11
	 * @return bool
	 */
	public function is_expired() {
		// Regenerate tokens 10 minutes early, just in case.
		$expires_in = $this->token_object->expires_in - ( 10 * MINUTE_IN_SECONDS );

		return time() > $this->token_object->created + $expires_in;
	}

	/**
	 * Returns the access token.
	 *
	 * @since 2.11
	 * @return string
	 */
	public function token() {
		return $this->token_object->access_token;
	}

	/**
	 * Determines whether or not we have a valid token object.
	 * Note: This does not check the _expiration_ of the token, just validates that the expected
	 * data is _present_.
	 *
	 * @param object $token_object
	 *
	 * @since 2.11
	 * @return bool
	 */
	private function is_valid( $token_object ) {
		$required_properties = array(
			'created',
			'access_token',
			'expires_in'
		);

		foreach ( $required_properties as $property ) {
			if ( ! isset( $token_object->{$property} ) ) {
				return false;
			}
		}

		return true;
	}

}
