<?php
namespace EDD\Tests\Discounts;

use EDD\Tests\Helpers;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests for Discounts API.
 *
 * @group edd_discounts
 */
class Statuses extends EDD_UnitTestCase {
	/**
	 * Discount ID.
	 *
	 * @var int
	 * @static
	 */
	protected static $discount_id;

	/**
	 * Discount object test fixture.
	 *
	 * @var \EDD_Discount
	 * @static
	 */
	protected static $discount;

	/**
	 * Runs before each test method, this helps avoid test pollution.
	 */
	public function setUp(): void {
		parent::setUp();

		self::$discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount();
		self::$discount = edd_get_discount( self::$discount_id );
	}

	public function tearDown(): void {
		parent::tearDown();

		Helpers\EDD_Helper_Discount::delete_discount( self::$discount_id );
	}

	/**
	 * @covers \EDD_Discount::is_active()
	 */
	public function test_discount_is_active_no_update() {
		$this->assertTrue( self::$discount->is_active( false, false ) );
	}

	/**
	 * @covers \EDD_Discount::is_active()
	 */
	public function test_discount_is_active_with_update() {
		$this->assertTrue( self::$discount->is_active( true, false ) );
	}

	/**
	 * @covers \EDD_Discount::is_active()
	 */
	public function test_discount_is_not_active_with_update_expired() {
		// Directly update the end_date with a date in the past, to avoid update logic from triggering.
		self::$discount->__set( 'end_date', date( 'Y-m-d', time() - DAY_IN_SECONDS ) );

		$this->assertFalse( self::$discount->is_active( true, false ) );
	}

	/**
	 * @covers \EDD_Discount::is_active()
	 */
	public function test_discount_is_not_active_with_update_not_started() {
		// Directly update the start_date with a date in the future, to avoid update logic from triggering.
		self::$discount->__set( 'start_date', date( 'Y-m-d', time() + DAY_IN_SECONDS ) );

		$this->assertFalse( self::$discount->is_active( true, false ) );
	}

	/**
	 * @covers \EDD_Discount::is_active()
	 */
	public function test_discount_is_not_active_when_archived() {
		// Directly update the status to archived.
		self::$discount->__set( 'status', 'archived' );

		$this->assertFalse( self::$discount->is_active( true, false ) );
	}

	/**
	 * @covers \EDD_Discount::is_archived()
	 */
	public function test_discount_is_archived() {
		// Directly update the status to archived.
		self::$discount->__set( 'status', 'archived' );

		$this->assertTrue( self::$discount->is_archived() );
	}

	/**
	 * @covers \EDD_Discount::is_archived()
	 */
	public function test_discount_is_not_archived() {
		$this->assertFalse( self::$discount->is_archived() );
	}

}
