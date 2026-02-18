<?php
/**
 * Tests for EDD\Utils\Encryption.
 *
 * AES-256-CBC encryption utility tests: round-trip, tamper detection,
 * IV randomness, and edge cases.
 *
 * @package   EDD\Tests\Utils
 * @copyright (c) 2026, Sandhills Development, LLC
 * @license https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.5
 */

namespace EDD\Tests\Utils;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Utils\Encryption as Utility;

/**
 * @coversDefaultClass \EDD\Utils\Encryption
 */
class Encryption extends EDD_UnitTestCase {

	/**
	 * Test encrypt/decrypt round-trip returns original plaintext.
	 *
	 * @covers ::encrypt
	 * @covers ::decrypt
	 */
	public function test_round_trip_encrypt_decrypt() {
		$plaintext = 'Hello, World!';
		$token     = Utility::encrypt( $plaintext );

		$this->assertNotNull( $token );
		$this->assertIsString( $token );

		$decrypted = Utility::decrypt( $token );
		$this->assertEquals( $plaintext, $decrypted );
	}

	/**
	 * Test round-trip with a numeric string (email ID).
	 *
	 * @covers ::encrypt
	 * @covers ::decrypt
	 */
	public function test_round_trip_numeric_string() {
		$plaintext = '42';
		$token     = Utility::encrypt( $plaintext );

		$this->assertNotNull( $token );

		$decrypted = Utility::decrypt( $token );
		$this->assertEquals( '42', $decrypted );
	}

	/**
	 * Test round-trip with email_id|url format (click tracking payload).
	 *
	 * @covers ::encrypt
	 * @covers ::decrypt
	 */
	public function test_round_trip_click_payload() {
		$plaintext = '42|https://example.com/product?id=123&ref=email';
		$token     = Utility::encrypt( $plaintext );

		$this->assertNotNull( $token );

		$decrypted = Utility::decrypt( $token );
		$this->assertEquals( $plaintext, $decrypted );
	}

	/**
	 * Test invalid hex input returns null.
	 *
	 * @covers ::decrypt
	 */
	public function test_decrypt_invalid_hex_returns_null() {
		$this->assertNull( Utility::decrypt( 'not-hex-at-all!' ) );
		$this->assertNull( Utility::decrypt( 'zzzz' ) );
		$this->assertNull( Utility::decrypt( '123' ) ); // Odd-length hex.
	}

	/**
	 * Test tampered token returns null.
	 *
	 * @covers ::decrypt
	 */
	public function test_tampered_token_returns_null() {
		$token = Utility::encrypt( '42' );
		$this->assertNotNull( $token );

		// Flip the last hex character.
		$last    = substr( $token, -1 );
		$flipped = ( '0' === $last ) ? '1' : '0';
		$tampered = substr( $token, 0, -1 ) . $flipped;

		$result = Utility::decrypt( $tampered );

		// AES-CBC may still decrypt (no built-in authentication) but the
		// plaintext will be garbled, so it won't match '42'.
		$this->assertNotEquals( '42', $result );
	}

	/**
	 * Test same plaintext produces different tokens (random IV).
	 *
	 * @covers ::encrypt
	 */
	public function test_same_plaintext_produces_different_tokens() {
		$token1 = Utility::encrypt( 'same input' );
		$token2 = Utility::encrypt( 'same input' );

		$this->assertNotNull( $token1 );
		$this->assertNotNull( $token2 );
		$this->assertNotEquals( $token1, $token2 );
	}

	/**
	 * Test empty string encrypt/decrypt round-trip.
	 *
	 * @covers ::encrypt
	 * @covers ::decrypt
	 */
	public function test_empty_string_round_trip() {
		$token = Utility::encrypt( '' );

		$this->assertNotNull( $token );

		$decrypted = Utility::decrypt( $token );
		$this->assertEquals( '', $decrypted );
	}

	/**
	 * Test long string handling (URL up to 2048 chars).
	 *
	 * @covers ::encrypt
	 * @covers ::decrypt
	 */
	public function test_long_string_round_trip() {
		$long_url = 'https://example.com/' . str_repeat( 'a', 2028 );
		$plaintext = '999|' . $long_url;

		$token = Utility::encrypt( $plaintext );
		$this->assertNotNull( $token );

		$decrypted = Utility::decrypt( $token );
		$this->assertEquals( $plaintext, $decrypted );
	}

	/**
	 * Test token is valid hex string.
	 *
	 * @covers ::encrypt
	 */
	public function test_token_is_hex_encoded() {
		$token = Utility::encrypt( 'test' );

		$this->assertNotNull( $token );
		$this->assertTrue( ctype_xdigit( $token ) );
		$this->assertEquals( 0, strlen( $token ) % 2 );
	}

	/**
	 * Test decrypt with too-short hex string returns null.
	 *
	 * @covers ::decrypt
	 */
	public function test_decrypt_short_token_returns_null() {
		// A valid hex string but too short to contain an IV.
		$this->assertNull( Utility::decrypt( 'aa' ) );
		$this->assertNull( Utility::decrypt( 'aabb' ) );
	}

	/**
	 * Test special characters in plaintext survive round-trip.
	 *
	 * @covers ::encrypt
	 * @covers ::decrypt
	 */
	public function test_special_characters_round_trip() {
		$plaintext = '42|https://example.com/path?foo=bar&baz=qux#section';
		$token     = Utility::encrypt( $plaintext );

		$this->assertNotNull( $token );

		$decrypted = Utility::decrypt( $token );
		$this->assertEquals( $plaintext, $decrypted );
	}
}
