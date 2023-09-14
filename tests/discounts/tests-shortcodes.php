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
class Shortcodes extends EDD_UnitTestCase {
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

	public function test_discounts_shortcode() {
		Helpers\EDD_Helper_Discount::create_simple_percent_discount();

		$actual = edd_discounts_shortcode( array() );

		$this->assertIsString( $actual );
		$this->assertEquals( '<ul id="edd_discounts_list"><li class="edd_discount"><span class="edd_discount_name">20OFF</span><span class="edd_discount_separator"> - </span><span class="edd_discount_amount">20.00%</span></li></ul>', $actual );
	}

	public function test_discounts_shortcode_no_active_discounts() {
		$discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		edd_update_discount_status( $discount_id, 'inactive' );

		$actual = edd_discounts_shortcode( array() );

		$this->assertIsString( $actual );
		$this->assertEquals( '<ul id="edd_discounts_list"><li class="edd_discount">No discounts found</li></ul>', $actual );
	}

}
