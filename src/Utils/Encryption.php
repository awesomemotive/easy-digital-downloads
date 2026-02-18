<?php
/**
 * AES-256-CBC Encryption Utility.
 *
 * Provides symmetric encrypt/decrypt using WordPress salts.
 * Used to create opaque URL tokens that pack tracking data
 * without exposing it as query parameters.
 *
 * @package EDD\Utils
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.5
 */

namespace EDD\Utils;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Encryption class.
 *
 * @since 3.6.5
 */
final class Encryption {

	/**
	 * The cipher method.
	 *
	 * @since 3.6.5
	 */
	private const CIPHER_METHOD = 'AES-256-CBC';

	/**
	 * Encrypts a plaintext string and returns a hex-encoded token.
	 *
	 * The token contains the random IV prepended to the ciphertext,
	 * so every call produces a different token for the same input.
	 *
	 * @since 3.6.5
	 * @param string $plaintext The data to encrypt.
	 * @return string|null Hex-encoded token, or null on failure.
	 */
	public static function encrypt( string $plaintext ): ?string {
		if ( ! self::requirements_met() ) {
			return null;
		}

		$key       = self::get_encryption_key();
		$iv_length = openssl_cipher_iv_length( self::CIPHER_METHOD );

		if ( false === $iv_length ) {
			return null;
		}

		$iv = openssl_random_pseudo_bytes( $iv_length );

		$encrypted = openssl_encrypt( $plaintext, self::CIPHER_METHOD, $key, OPENSSL_RAW_DATA, $iv );

		if ( false === $encrypted ) {
			return null;
		}

		return bin2hex( $iv . $encrypted );
	}

	/**
	 * Decrypts a hex-encoded token back to plaintext.
	 *
	 * @since 3.6.5
	 * @param string $token Hex-encoded token from encrypt().
	 * @return string|null The original plaintext, or null on failure.
	 */
	public static function decrypt( string $token ): ?string {
		if ( ! self::requirements_met() ) {
			return null;
		}

		if ( strlen( $token ) % 2 !== 0 || ! ctype_xdigit( $token ) ) {
			return null;
		}

		$raw = hex2bin( $token );
		if ( false === $raw ) {
			return null;
		}

		$key       = self::get_encryption_key();
		$iv_length = openssl_cipher_iv_length( self::CIPHER_METHOD );

		if ( false === $iv_length || strlen( $raw ) < $iv_length ) {
			return null;
		}

		$iv        = substr( $raw, 0, $iv_length );
		$encrypted = substr( $raw, $iv_length );

		$decrypted = openssl_decrypt( $encrypted, self::CIPHER_METHOD, $key, OPENSSL_RAW_DATA, $iv );

		if ( false === $decrypted ) {
			return null;
		}

		return $decrypted;
	}

	/**
	 * Checks if encryption requirements are met.
	 *
	 * @since 3.6.5
	 * @return bool True if all requirements are available.
	 */
	private static function requirements_met(): bool {
		return defined( 'NONCE_KEY' ) && defined( 'NONCE_SALT' );
	}

	/**
	 * Derives the encryption key from WordPress salts.
	 *
	 * @since 3.6.5
	 * @return string Binary encryption key.
	 */
	private static function get_encryption_key(): string {
		return hash( 'sha256', NONCE_KEY . NONCE_SALT, true );
	}
}
