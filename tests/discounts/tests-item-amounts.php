<?php
namespace EDD\Tests\Discounts;

use EDD\Tests\Helpers;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests for Discounts API.
 * @group edd_discounts
 *
 * @coversDefaultClass \EDD_Discount
 */
class ItemAmounts extends EDD_UnitTestCase {
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
	 * Downloads
	 *
	 * @var array
	 * @static
	 */
	protected static $downloads;

	/**
	 * Runs before each test method, this helps avoid test pollution.
	 */
	public function setUp(): void {
		parent::setUp();

		self::$discount_id = Helpers\EDD_Helper_Discount::create_simple_flat_discount();
		self::$discount = edd_get_discount( self::$discount_id );

		self::$downloads = array(
			Helpers\EDD_Helper_Download::create_simple_download(),
			Helpers\EDD_Helper_Download::create_simple_download(),
			Helpers\EDD_Helper_Download::create_simple_download(),
		);
	}

	public function tearDown(): void {
		parent::tearDown();

		Helpers\EDD_Helper_Discount::delete_discount( self::$discount_id );

		foreach ( self::$downloads as $download ) {
			Helpers\EDD_Helper_Download::delete_download( $download->ID );
		}
	}

	public function test_get_item_discount_amount_single_item() {
		edd_add_to_cart( self::$downloads[0]->ID );

		$cart_contents = edd_get_cart_contents();
		$first_item    = reset( $cart_contents );

		$this->assertEquals( 10, edd_get_item_discount_amount( $first_item, $cart_contents, array( self::$discount->code ) ) );
	}

	public function test_get_item_discount_amount_two_items() {
		edd_add_to_cart( self::$downloads[0]->ID );
		edd_add_to_cart( self::$downloads[1]->ID );

		$cart_contents = edd_get_cart_contents();
		$first_item    = reset( $cart_contents );
		$second_item   = end( $cart_contents );

		$this->assertEquals( 5, edd_get_item_discount_amount( $first_item, $cart_contents, array( self::$discount->code ) ) );
		$this->assertEquals( 5, edd_get_item_discount_amount( $second_item, $cart_contents, array( self::$discount->code ) ) );
	}

	public function test_get_item_discount_amount_three_items() {
		edd_add_to_cart( self::$downloads[0]->ID );
		edd_add_to_cart( self::$downloads[1]->ID );
		edd_add_to_cart( self::$downloads[2]->ID );

		$cart_contents = edd_get_cart_contents();
		$first_item    = $cart_contents[0];
		$second_item   = $cart_contents[1];
		$third_item    = $cart_contents[2];

		$this->assertEquals( 3.33, edd_get_item_discount_amount( $first_item, $cart_contents, array( self::$discount->code ) ) );
		$this->assertEquals( 3.33, edd_get_item_discount_amount( $second_item, $cart_contents, array( self::$discount->code ) ) );
		$this->assertEquals( 3.34, edd_get_item_discount_amount( $third_item, $cart_contents, array( self::$discount->code ) ) );
	}

	public function test_get_item_discount_amount_three_items_with_product_exclusion() {
		edd_update_discount( self::$discount_id, array( 'excluded_products' => array( self::$downloads[1]->ID ) ) );

		edd_add_to_cart( self::$downloads[0]->ID );
		edd_add_to_cart( self::$downloads[1]->ID );
		edd_add_to_cart( self::$downloads[2]->ID );

		$cart_contents = edd_get_cart_contents();
		$first_item    = $cart_contents[0];
		$second_item   = $cart_contents[1];
		$third_item    = $cart_contents[2];

		$this->assertEquals( 5, edd_get_item_discount_amount( $first_item, $cart_contents, array( self::$discount->code ) ) );
		$this->assertEquals( 0, edd_get_item_discount_amount( $second_item, $cart_contents, array( self::$discount->code ) ) );
		$this->assertEquals( 5, edd_get_item_discount_amount( $third_item, $cart_contents, array( self::$discount->code ) ) );
	}

	public function test_get_item_discount_amount_two_variations() {
		$variable_download = Helpers\EDD_Helper_Download::create_variable_download();
		edd_add_to_cart(
			$variable_download->ID,
			array(
				'price_id' => array( 0, 1 ),
			)
		);

		$cart_contents = edd_get_cart_contents();
		$first_item    = $cart_contents[0];
		$second_item   = $cart_contents[1];


		$this->assertEquals( 1.67, edd_get_item_discount_amount( $first_item, $cart_contents, array( self::$discount->code ) ) );
		$this->assertEquals( 8.33, edd_get_item_discount_amount( $second_item, $cart_contents, array( self::$discount->code ) ) );
	}

	public function test_get_item_discount_amount_two_identical_items() {

		edd_add_to_cart( self::$downloads[0]->ID );
		edd_add_to_cart( self::$downloads[0]->ID );

		$cart_contents = edd_get_cart_contents();
		$first_item    = $cart_contents[0];
		$second_item   = $cart_contents[1];

		$this->assertEquals( 5, edd_get_item_discount_amount( $first_item, $cart_contents, array( self::$discount->code ) ) );
		$this->assertEquals( 5, edd_get_item_discount_amount( $second_item, $cart_contents, array( self::$discount->code ) ) );
	}

	public function test_get_item_discount_amount_percentage_discount() {
		$percentage_discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses();
		$percentage_discount    = edd_get_discount( $percentage_discount_id );

		edd_add_to_cart( self::$downloads[0]->ID );

		$cart_contents = edd_get_cart_contents();
		$first_item    = $cart_contents[0];

		$this->assertEquals( 4.0, edd_get_item_discount_amount( $first_item, $cart_contents, array( $percentage_discount->code ) ) );
	}

	public function test_get_item_discount_amount_empty_item() {

		edd_add_to_cart( self::$downloads[0]->ID );

		$cart_contents = edd_get_cart_contents();

		$this->assertEquals( 0, edd_get_item_discount_amount( array(), $cart_contents, array( self::$discount->code ) ) );
	}

	public function test_get_item_discount_amount_no_discounts() {

		edd_add_to_cart( self::$downloads[0]->ID );

		$cart_contents = edd_get_cart_contents();
		$first_item    = $cart_contents[0];

		$this->assertEquals( 0, edd_get_item_discount_amount( $first_item, $cart_contents, array() ) );
	}

	public function test_percentage_discount_product_requirements() {
		$percentage_discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount_nodates_nouses(
			array(
				'product_reqs' => array( self::$downloads[0]->ID ),
				'scope'        => 'not_global',
			)
		);
		$percentage_discount    = edd_get_discount( $percentage_discount_id );

		edd_add_to_cart( self::$downloads[0]->ID );
		edd_add_to_cart( self::$downloads[1]->ID );

		$cart_contents = edd_get_cart_contents();
		$first_item    = $cart_contents[0];
		$second_item   = $cart_contents[1];

		$this->assertEquals( 4.0, edd_get_item_discount_amount( $first_item, $cart_contents, array( $percentage_discount->code ) ) );
		$this->assertEquals( 0, edd_get_item_discount_amount( $second_item, $cart_contents, array( $percentage_discount->code ) ) );
	}

	public function test_get_item_discount_amount_single_item_discount_more_than_item() {

		$discount_id = Helpers\EDD_Helper_Discount::create_simple_flat_discount(
			array(
				'amount' => 25,
			)
		);
		$discount = edd_get_discount( $discount_id );

		edd_add_to_cart( self::$downloads[0]->ID );

		$cart_contents = edd_get_cart_contents();
		$first_item    = reset( $cart_contents );

		$this->assertEquals( 20, edd_get_item_discount_amount( $first_item, $cart_contents, array( $discount ) ) );
	}

	public function test_get_item_discount_amount_single_item_invalid_discount() {
		edd_add_to_cart( self::$downloads[0]->ID );

		$cart_contents = edd_get_cart_contents();
		$first_item    = reset( $cart_contents );

		$this->assertEquals( 0, edd_get_item_discount_amount( $first_item, $cart_contents, array( 'not_a_valid_code' ) ) );
	}

	public function test_get_item_discount_price_id_requirements() {
		$variable_download = Helpers\EDD_Helper_Download::create_variable_download();

		edd_update_discount( self::$discount_id, array( 'product_reqs' => array( $variable_download->ID . '_' . 0 ) ) );

		edd_add_to_cart( $variable_download->ID, array( 'price_id' => array( 0, 1 ), ) );

		$cart_contents = edd_get_cart_contents();
		$first_item    = reset( $cart_contents );
		$second_item   = end( $cart_contents );

		$this->assertEquals( 10, edd_get_item_discount_amount( $first_item, $cart_contents, array( self::$discount->code ) ) );
		$this->assertEquals( 0, edd_get_item_discount_amount( $second_item, $cart_contents, array( self::$discount->code ) ) );
	}

	public function test_get_item_discount_price_id_requirements_all_variations() {
		$variable_download = Helpers\EDD_Helper_Download::create_variable_download();

		edd_update_discount( self::$discount_id, array( 'product_reqs' => array( $variable_download->ID ) ) );

		edd_add_to_cart( $variable_download->ID, array( 'price_id' => 0 ) );

		$cart_contents = edd_get_cart_contents();
		$first_item    = reset( $cart_contents );

		$this->assertEquals( 10, edd_get_item_discount_amount( $first_item, $cart_contents, array( self::$discount->code ) ) );
	}
}
