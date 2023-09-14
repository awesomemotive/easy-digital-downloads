<?php
namespace EDD\Tests\Discounts;

use EDD\Tests\Helpers;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests for Discounts API.
 * @covers edd_has_active_discounts
 * @group edd_discounts
 *
 * @coversDefaultClass \EDD_Discount
 */
class HasActiveDiscounts extends EDD_UnitTestCase {
	/**
	 * Runs before each test method, this helps avoid test pollution.
	 */
	public function setUp(): void {
		parent::setUp();
	}

	public function tearDown(): void {
		parent::tearDown();

		// Remove all adjustments after each test.
		edd_get_component_interface( 'adjustment', 'table' )->truncate();
	}

	/**
	 * Test with a single active discount, with no start or end date.
	 */
	public function test_single_active_discount_no_dates() {
		$discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		edd_update_discount( $discount_id, array( 'start_date' => null, 'end_date' => null ) );

		$this->assertTrue( edd_has_active_discounts() );
	}

	/**
	 * Test with a single inactive discount.
	 */
	public function test_single_inactive_discount() {
		$discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		edd_update_discount( $discount_id, array( 'status' => 'inactive' ) );

		$this->assertFalse( edd_has_active_discounts() );
	}

	/**
	 * Test with a single archived discount.
	 */
	public function test_single_archived_discount() {
		$discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		edd_update_discount( $discount_id, array( 'status' => 'archived' ) );

		$this->assertFalse( edd_has_active_discounts() );
	}

	/**
	 * Test with a single active discount, with a start date in the future.
	 */
	public function test_single_active_discount_future_start_date() {
		$discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		edd_update_discount( $discount_id, array( 'start_date' => date( 'Y-m-d H:i:s', strtotime( '+1 day' ) ) ) );

		$this->assertFalse( edd_has_active_discounts() );
	}

	/**
	 * Test with a single active discount, with an end date in the past.
	 */
	public function test_single_active_discount_past_end_date() {
		$discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		edd_update_discount( $discount_id, array( 'end_date' => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ) ) );

		$this->assertFalse( edd_has_active_discounts() );
	}

	/**
	 * Test with a single active discount, with a start date in the past and an end date in the future.
	 */
	public function test_single_active_discount_past_start_date_future_end_date() {
		$discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		edd_update_discount( $discount_id, array(
			'start_date' => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'end_date'   => date( 'Y-m-d H:i:s', strtotime( '+1 day' ) ),
		) );

		$this->assertTrue( edd_has_active_discounts() );
	}

	/**
	 * Test with a single active discount, with a start date in the past and an end date in the future, but the discount has hit it's max uses.
	 */
	public function test_single_active_discount_past_start_date_future_end_date_max_uses() {
		$discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		edd_update_discount( $discount_id, array(
			'start_date' => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'end_date'   => date( 'Y-m-d H:i:s', strtotime( '+1 day' ) ),
			'use_count'  => 10,
			'max_uses'   => 10,
		) );

		$this->assertFalse( edd_has_active_discounts() );
	}

	/**
	 * Test with a single active discount with a start date in the past and no end date.
	 */
	public function test_single_active_discount_past_start_date_no_end_date() {
		$discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		edd_update_discount( $discount_id, array(
			'start_date' => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'end_date'   => null,
		) );

		$this->assertTrue( edd_has_active_discounts() );
	}

	/**
	 * Test with a single active discount with no start date and an end date in the past.
	 */
	public function test_single_active_discount_no_start_date_past_end_date() {
		$discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		edd_update_discount( $discount_id, array(
			'start_date' => null,
			'end_date'   => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
		) );

		$this->assertFalse( edd_has_active_discounts() );
	}

	/**
	 * Test with a single active discount with no start date and an end date in the future.
	 */
	public function test_single_active_discount_no_start_date_future_end_date() {
		$discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		edd_update_discount( $discount_id, array(
			'start_date' => null,
			'end_date'   => date( 'Y-m-d H:i:s', strtotime( '+1 day' ) ),
		) );

		$this->assertTrue( edd_has_active_discounts() );
	}

	/**
	 * Test with two discounts, one active, one inactive.
	 */
	public function test_two_discounts_one_active_one_inactive() {
		$discount_id_1 = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		$discount_id_2 = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		edd_update_discount( $discount_id_2, array( 'status' => 'inactive' ) );

		$this->assertTrue( edd_has_active_discounts() );
	}

	/**
	 * Test with two discounts, one active, one archived.
	 */
	public function test_two_discounts_one_active_one_archived() {
		$discount_id_1 = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		$discount_id_2 = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		edd_update_discount( $discount_id_2, array( 'status' => 'archived' ) );

		$this->assertTrue( edd_has_active_discounts() );
	}

	/**
	 * Test with two discounts, one active, one expired.
	 */
	public function test_two_discounts_one_active_one_expired() {
		$discount_id_1 = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		$discount_id_2 = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		edd_update_discount( $discount_id_2, array(
			'end_date' => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'status'   => 'expired',
		) );

		$this->assertTrue( edd_has_active_discounts() );
	}

	/**
	 * Test with two discounts, one active and one with a start date in the future.
	 */
	public function test_two_discounts_one_active_one_future_start_date() {
		$discount_id_1 = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		$discount_id_2 = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		edd_update_discount( $discount_id_2, array(
			'start_date' => date( 'Y-m-d H:i:s', strtotime( '+1 day' ) ),
		) );

		$this->assertTrue( edd_has_active_discounts() );
	}

	/**
	 * Test with two discounts one active and one with an end date in the past.
	 */
	public function test_two_discounts_one_active_one_past_end_date() {
		$discount_id_1 = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		$discount_id_2 = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		edd_update_discount( $discount_id_2, array(
			'end_date' => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
		) );

		$this->assertTrue( edd_has_active_discounts() );
	}

	/**
	 * Test with two discounts, both with an end date in the past.
	*/
	public function test_two_discounts_both_past_end_date() {
		$discount_id_1 = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		$discount_id_2 = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();

		$past_date = date( 'Y-m-d H:i:s', strtotime( '-1 day' ) );
		edd_update_discount( $discount_id_1, array( 'end_date' => $past_date ) );
		edd_update_discount( $discount_id_2, array( 'end_date' => $past_date ) );

		$this->assertFalse( edd_has_active_discounts() );
	}

	/**
	 * Test with two discounts, both with a start date in the future.
	 */
	public function test_two_discounts_both_future_start_date() {
		$discount_id_1 = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		$discount_id_2 = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();

		$future_date = date( 'Y-m-d H:i:s', strtotime( '+1 day' ) );
		edd_update_discount( $discount_id_1, array( 'start_date' => $future_date ) );
		edd_update_discount( $discount_id_2, array( 'start_date' => $future_date ) );

		$this->assertFalse( edd_has_active_discounts() );
	}
}
