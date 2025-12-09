<?php
/**
 * REST Security
 *
 * Handles security validation for REST endpoints.
 *
 * @package     EDD\REST
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.2
 */

namespace EDD\REST;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Utils\Tokenizer;

/**
 * Security class
 *
 * Provides token-based security for EDD REST endpoints.
 *
 * @since 3.6.2
 */
class Security {

	/**
	 * Validate EDD token from request header.
	 *
	 * @since 3.6.2
	 * @param \WP_REST_Request $request Request object.
	 * @return bool|\WP_Error True if valid, WP_Error otherwise.
	 */
	public function validate_token( $request ) {
		// Validate the WordPress nonce.
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new \WP_Error(
				'invalid_nonce',
				__( 'Invalid nonce.', 'easy-digital-downloads' ),
				array( 'status' => 403 )
			);
		}

		// Always validate the custom cart token (primary CSRF protection).
		$token = $request->get_header( 'X-EDD-Cart-Token' );

		if ( empty( $token ) ) {
			return new \WP_Error(
				'missing_token',
				__( 'Cart token missing.', 'easy-digital-downloads' ),
				array( 'status' => 401 )
			);
		}

		$timestamp = $request->get_header( 'X-EDD-Cart-Timestamp' );

		// For security, the timestamp must be within the last hour.
		if ( ! is_numeric( $timestamp ) || $timestamp < time() - HOUR_IN_SECONDS ) {
			return new \WP_Error(
				'invalid_timestamp',
				__( 'Invalid timestamp.', 'easy-digital-downloads' ),
				array( 'status' => 403 )
			);
		}

		// Validate the token.
		if ( ! Tokenizer::is_token_valid( $token, $timestamp ) ) {
			return new \WP_Error(
				'invalid_token',
				__( 'Invalid token.', 'easy-digital-downloads' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Generate a token.
	 *
	 * @since 3.6.2
	 * @param int $timestamp Optional timestamp to tokenize. Defaults to current time.
	 * @return string
	 */
	public static function generate_token( $timestamp = null ) {
		if ( is_null( $timestamp ) ) {
			$timestamp = time();
		}

		return Tokenizer::tokenize( $timestamp );
	}

	/**
	 * Refresh token for response.
	 *
	 * Generates a new token to be used in the next request.
	 *
	 * @since 3.6.2
	 * @return string
	 */
	public function refresh_token() {
		return $this->generate_token();
	}

	/**
	 * Get current timestamp.
	 *
	 * Used to provide the timestamp to the frontend for token generation.
	 *
	 * @since 3.6.2
	 * @return int
	 */
	public static function get_current_timestamp() {
		return time();
	}
}
