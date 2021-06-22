<?php
/**
 * tests-tokenizer.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.11
 */

namespace EDD\Tests;

use EDD\Utils\Tokenizer;

/**
 * Class TestsTokenizer
 *
 * @coversDefaultClass \EDD\Utils\Tokenizer
 *
 * @package EDD\Tests
 */
class TestsTokenizer extends \EDD_UnitTestCase {

	/**
	 * When a valid token is passed through using the current timestamp, it should be valid.
	 *
	 * @covers \EDD\Utils\Tokenizer::is_token_valid
	 */
	public function test_token_valid_for_timestamp() {
		$timestamp = time();
		$token     = Tokenizer::tokenize( $timestamp );

		$this->assertTrue( Tokenizer::is_token_valid( $token, $timestamp ) );
	}

	/**
	 * When a valid token is passed through using a timestamp from 1 day ago, it should be valid.
	 *
	 * @covers \EDD\Utils\Tokenizer::is_token_valid
	 */
	public function test_token_valid_for_yesterday_timestamp() {
		$timestamp = strtotime( '-1 day' );

		$token = Tokenizer::tokenize( $timestamp );

		$this->assertTrue( Tokenizer::is_token_valid( $token, $timestamp ) );
	}

	/**
	 * Token is generated from current timestamp, but then validated using a timestamp from 1 day ago.
	 * Because the data being tokenized is different, this should fail.
	 *
	 * @covers \EDD\Utils\Tokenizer::is_token_valid
	 */
	public function test_token_invalid_when_wrong_token_provided() {
		$token = Tokenizer::tokenize( time() );

		$this->assertFalse( Tokenizer::is_token_valid( $token, strtotime( '-1 day' ) ) );
	}

}
