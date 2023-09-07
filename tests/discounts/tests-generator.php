<?php

namespace EDD\Tests\Discounts;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Test for the discount generator
 *
 * @covers \EDD\Pro\Discounts\Generator
 */
class Generator extends EDD_UnitTestCase {

	/**
	 * @var \EDD\Pro\Discounts\Generator
	 */
	private static $generator;

	/**
	 * Runs before all tests in this class are executed.
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		if ( ! edd_is_pro() ) {
			return;
		}
		self::$generator = new \EDD\Pro\Discounts\Generator();
	}

	/**
	 * Runs before each test method, this helps avoid test pollution.
	 */
	public function setUp(): void {
		if ( ! edd_is_pro() ) {
			$this->markTestSkipped( 'This test requires EDD Pro.' );
		}
		parent::setUp();
	}

	/**
	 * Runs after each test method.
	 */
	public function tearDown(): void {
		parent::tearDown();

		// Remove all adjustments after each test.
		edd_get_component_interface( 'adjustment', 'table' )->truncate();
	}

	/**
	 * Test that the generator can generate a valid code.
	 */
	public function test_generate_valid_code() {
		$code = self::$generator->generate();

		$this->assertNotEmpty( $code );
		$this->assertIsString( $code );
		$this->assertLessThanOrEqual( 50, strlen( $code ) );
		$this->assertGreaterThanOrEqual( 6, strlen( $code ) );
	}

	/**
	 * Test that the generator can generate a valid code with a prefix.
	 */
	public function test_generate_code_with_prefix() {
		$code = self::$generator->generate( 'TestPrefix-' );
		$this->assertStringStartsWith( 'TestPrefix-', $code );
	}

	/**
	 * Test that the generator can generate a valid code with a prefix and length.
	 */
	public function test_generate_code_with_prefix_and_length() {
		$code = self::$generator->generate( 'Test-', 'hash', 10 );
		$this->assertEquals( 15, strlen( $code ) );
	}

	/**
	 * Test that the generator can generate a valid code with a prefix and length.
	 */
	public function test_generate_code_with_prefix_and_length_too_long() {
		$code = self::$generator->generate( 'Test-', 'hash', 100 );

		$this->assertEquals( 50, strlen( $code ) );
	}

	public function test_generate_code_only_letters() {
		$code = self::$generator->generate( '', 'letters', 10 );

		$this->assertRegExp( '/^[A-Z]+$/', $code );
		$this->assertEquals( 10, strlen( $code ) );
	}

	public function test_generate_code_only_numbers() {
		$code = self::$generator->generate( '', 'numbers', 10 );

		$this->assertRegExp( '/^[0-9]+$/', $code );
		$this->assertEquals( 10, strlen( $code ) );
	}

	public function test_generate_code_too_short() {
		$code = self::$generator->generate( '', 'hash', 1 );

		$this->assertEquals( 6, strlen( $code ) );
	}

	public function test_generate_code_too_long() {
		$code = self::$generator->generate( '', 'hash', 1000 );

		$this->assertEquals( 50, strlen( $code ) );
	}
}
