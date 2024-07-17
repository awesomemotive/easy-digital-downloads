<?php

namespace EDD\Tests\Discounts;

use EDD\Tests\Helpers;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests for Discounts API.
 *
 * @group edd_discounts
 *
 * @coversDefaultClass \EDD_Discount
 */
class UseCounts extends EDD_UnitTestCase {

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
	}

	public function tearDown(): void {
		parent::tearDown();

		Helpers\EDD_Helper_Discount::delete_discount( self::$discount_id );
	}

	/**
	 * @covers ::get_uses()
	 */
	public function test_get_discount_uses_by_property() {
		edd_update_discount( self::$discount_id, array( 'use_count' => 54 ) );
		$this->assertEquals( 54, $this->get_discount()->uses );
		$this->assertNotEmpty( $this->get_discount()->end_date );
	}

	/**
	 * @covers ::get_uses()
	 */
	public function test_get_discount_uses_by_method() {
		edd_update_discount( self::$discount_id, array( 'use_count' => 54 ) );
		$this->assertEquals( 54, $this->get_discount()->get_uses() );
	}

	/**
	 * @covers ::get_max_uses()
	 */
	public function test_get_discount_max_uses_by_property() {
		edd_update_discount( self::$discount_id, array( 'max_uses' => 10 ) );
		$this->assertEquals( 10, $this->get_discount()->max_uses );
	}

	/**
	 * @covers ::get_max_uses()
	 */
	public function test_get_discount_max_uses_by_method() {
		edd_update_discount( self::$discount_id, array( 'max_uses' => 10 ) );
		$this->assertEquals( 10, $this->get_discount()->get_max_uses() );
	}

	/**
	 * @covers \edd_is_discount_maxed_out()
	 */
	public function test_discount_not_maxed_out_no_max_helper_function() {
		$this->assertFalse( edd_is_discount_maxed_out( self::$discount_id ) );
	}

	/**
	 * @covers \EDD_Discount::is_maxed_out()
	 */
	public function test_discount_not_maxed_out_no_max_class_method() {
		$this->assertFalse( $this->get_discount()->is_maxed_out() );
	}

	/**
	 * @covers \edd_is_discount_maxed_out()
	 */
	public function test_discount_not_maxed_out_has_max_helper_function() {
		edd_update_discount( self::$discount_id, array( 'max_uses' => 10 ) );
		$this->assertFalse( edd_is_discount_maxed_out( self::$discount_id ) );
	}

	/**
	 * @covers \EDD_Discount::is_maxed_out()
	 */
	public function test_discount_not_maxed_out_has_max_class_method() {
		edd_update_discount( self::$discount_id, array( 'max_uses' => 10 ) );
		$this->assertFalse( $this->get_discount()->is_maxed_out() );
	}

	/**
	 * @covers \edd_is_discount_maxed_out()
	 */
	public function test_discount_not_maxed_out_has_max_and_uses_helper_function() {
		edd_update_discount( self::$discount_id, array( 'max_uses' => 10, 'use_count' => 1 ) );
		$this->assertFalse( edd_is_discount_maxed_out( self::$discount_id ) );
	}

	/**
	 * @covers \EDD_Discount::is_maxed_out()
	 */
	public function test_discount_not_maxed_out_has_max_and_uses_class_method() {
		edd_update_discount( self::$discount_id, array( 'max_uses' => 10, 'use_count' => 1 ) );
		$this->assertFalse( $this->get_discount()->is_maxed_out() );
	}

	/**
	 * @covers \edd_is_discount_maxed_out()
	 */
	public function test_discount_maxed_out_has_max_and_uses_helper_function() {
		edd_update_discount( self::$discount_id, array( 'max_uses' => 10, 'use_count' => 10 ) );
		$this->assertTrue( edd_is_discount_maxed_out( self::$discount_id ) );
	}

	/**
	 * @covers \EDD_Discount::is_maxed_out()
	 */
	public function test_discount_maxed_out_has_max_and_uses_class_method() {
		edd_update_discount( self::$discount_id, array( 'max_uses' => 10, 'use_count' => 10 ) );

		// Since we're not updating the discount object, we need to re-fetch it.
		$discount = edd_get_discount( self::$discount_id );
		$this->assertTrue( $discount->is_maxed_out() );
	}

	/**
	 * @covers \edd_delete_discount()
	 */
	public function test_deletion_of_discount_should_be_false_because_use_count_not_0() {
		edd_update_discount( self::$discount_id, array( 'use_count' => 1 ) );
		edd_delete_discount( self::$discount_id );

		$this->assertInstanceOf( 'EDD_Discount', $this->get_discount() );
	}


	/**
	 * @covers \edd_delete_discount()
	 */
	public function test_deletion_of_discount_should_be_true_because_use_count_not_0() {
		edd_update_discount( self::$discount_id, array( 'use_count' => 0 ) );
		edd_delete_discount( self::$discount_id );

		$this->assertFalse( $this->get_discount() );
	}

	/**
	 * @covers \edd_decrease_discount_usage()
	 */
	public function test_decrease_discount_usage() {
		// We create this as 0, so we need to set it to 1.
		edd_update_discount( self::$discount_id, array( 'use_count' => 1 ) );
		$uses = edd_get_discount_uses( self::$discount_id );

		$decreased = edd_decrease_discount_usage( $this->get_discount()->code );
		$this->assertSame( $decreased, (int) $uses - 1 );
	}

	/**
	 * @covers \edd_increase_discount_usage()
	 */
	public function test_increase_discount_usage() {
		$uses = edd_get_discount_uses( self::$discount_id );

		$increased = edd_increase_discount_usage( $this->get_discount()->code );
		$this->assertSame( $increased, (int) $uses + 1 );
	}

	public function test_getting_discount_usage_of_invalid_discount_is_false() {
		// Test missing codes
		$this->assertFalse( edd_decrease_discount_usage( 'INVALIDDISCOUNTCODE' ) );
	}

	/**
	 * Just a wrapper to get the discount object.
	 */
	private function get_discount() {
		return edd_get_discount( self::$discount_id );
	}
}
