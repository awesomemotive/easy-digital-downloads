<?php

namespace EDD\Tests\Discounts;

use EDD\Tests\Helpers;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Categories extends EDD_UnitTestCase {

	/**
	 * The discount object.
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
	 * Category
	 *
	 * @var int
	 * @static
	 */
	protected static $category;

	/**
	 * Child Category
	 *
	 * @var int
	 * @static
	 */
	protected static $child_category;

	public static function wpSetUpBeforeClass() {
		self::$downloads = array(
			Helpers\EDD_Helper_Download::create_simple_download(),
			Helpers\EDD_Helper_Download::create_simple_download(),
			Helpers\EDD_Helper_Download::create_simple_download(),
		);

		$category       = wp_insert_term( 'Test Category', 'download_category' );
		self::$category = $category['term_id'];

		$second_download = self::$downloads[1];
		wp_set_object_terms( $second_download->ID, self::$category, 'download_category' );

		$child_category       = wp_insert_term(
			'Test Child Category',
			'download_category',
			array(
				'parent' => self::$category,
			)
		);
		self::$child_category = $child_category['term_id'];

		$third_download = self::$downloads[2];
		wp_set_object_terms( $third_download->ID, self::$child_category, 'download_category' );
	}

	public function setUp(): void {
		parent::setUp();

		$discount       = Helpers\EDD_Helper_Discount::create_simple_percent_discount();
		self::$discount = edd_get_discount( $discount );
	}

	public function tearDown(): void {
		edd_empty_cart();
		Helpers\EDD_Helper_Discount::delete_discount( self::$discount->id );
		parent::tearDown();
	}

	public function test_update_discount_category_included() {
		edd_update_discount(
			self::$discount->id,
			array(
				'categories' => array( self::$category ),
			)
		);
		$discount = edd_get_discount( self::$discount->id );

		$this->assertSame( array( self::$category ), $discount->get_categories() );
		$this->assertEmpty( $discount->get_term_condition() );
	}

	public function test_update_discount_category_excluded() {
		edd_update_discount(
			self::$discount->id,
			array(
				'categories'     => array( self::$category ),
				'term_condition' => 'exclude',
			)
		);
		$discount = edd_get_discount( self::$discount->id );

		$this->assertSame( array( self::$category ), $discount->get_categories() );
		$this->assertEquals( 'exclude', $discount->get_term_condition() );
	}

	public function test_update_discount_no_category_no_term() {
		edd_update_discount(
			self::$discount->id,
			array(
				'term_condition' => 'exclude',
			)
		);
		$discount = edd_get_discount( self::$discount->id );

		$this->assertEmpty( $discount->get_term_condition() );
	}

	public function test_item_in_cart_no_category_discount_is_valid_for_categories_is_false() {
		edd_update_discount(
			self::$discount->id,
			array(
				'categories' => array( self::$category ),
			)
		);

		$download = reset( self::$downloads );
		edd_add_to_cart( $download->ID );

		$this->assertFalse( self::$discount->is_valid_for_categories() );
	}

	public function test_edd_validate_discount_no_category_is_false() {
		edd_update_discount(
			self::$discount->id,
			array(
				'categories' => array( self::$category ),
			)
		);

		$download = reset( self::$downloads );

		$this->assertFalse( edd_validate_discount( self::$discount->id, $download->ID ) );
	}

	public function test_item_in_cart_category_discount_is_valid_for_categories_is_true() {
		edd_update_discount(
			self::$discount->id,
			array(
				'categories' => array( self::$category ),
			)
		);

		$download = self::$downloads[1];
		edd_add_to_cart( $download->ID );

		$this->assertTrue( self::$discount->is_valid_for_categories() );
	}

	public function test_item_in_cart_category_discount_is_valid_for_categories_is_false() {
		edd_update_discount(
			self::$discount->id,
			array(
				'categories'     => array( self::$category ),
				'term_condition' => 'exclude',
			)
		);

		$download = self::$downloads[1];
		edd_add_to_cart( $download->ID );

		$this->assertFalse( self::$discount->is_valid_for_categories() );
	}

	public function test_item_in_cart_category_discount_is_valid_for_categories_child_category_is_true() {
		edd_update_discount(
			self::$discount->id,
			array(
				'categories' => array( self::$category ),
			)
		);

		$download = self::$downloads[2];
		edd_add_to_cart( $download->ID );

		$this->assertTrue( self::$discount->is_valid_for_categories() );
	}

	public function test_item_in_cart_category_discount_is_valid_for_categories_child_category_is_false() {
		edd_update_discount(
			self::$discount->id,
			array(
				'categories'     => array( self::$category ),
				'term_condition' => 'exclude',
			)
		);

		$download = self::$downloads[2];
		edd_add_to_cart( $download->ID );

		$this->assertFalse( self::$discount->is_valid_for_categories() );
	}

	public function test_edd_validate_discount_category_is_true() {
		edd_update_discount(
			self::$discount->id,
			array(
				'categories' => array( self::$category ),
			)
		);

		$download = self::$downloads[1];

		$this->assertTrue( edd_validate_discount( self::$discount->id, $download->ID ) );
	}

	public function test_get_cart_item_discount_amount_not_in_category_is_0() {
		edd_update_discount(
			self::$discount->id,
			array(
				'min_charge_amount' => 0,
				'categories'        => array( self::$category ),
			)
		);

		$download = self::$downloads[0];
		edd_add_to_cart( $download->ID );
		$cart_contents = edd_get_cart_contents();

		$this->assertEquals( 0, edd_get_item_discount_amount( reset( $cart_contents ), $cart_contents, array( self::$discount->code ) ) );
	}

	public function test_get_cart_item_discount_amount_in_category_is_4() {
		edd_update_discount(
			self::$discount->id,
			array(
				'min_charge_amount' => 0,
				'categories'        => array( self::$category ),
			)
		);

		$download = self::$downloads[1];
		edd_add_to_cart( $download->ID );
		$cart_contents = edd_get_cart_contents();

		$this->assertEquals( 4, edd_get_item_discount_amount( reset( $cart_contents ), $cart_contents, array( self::$discount->code ) ) );
	}

	public function test_get_cart_item_discount_amount_not_in_excluded_category_is_4() {
		edd_update_discount(
			self::$discount->id,
			array(
				'min_charge_amount' => 0,
				'categories'        => array( self::$category ),
				'term_condition'    => 'exclude',
			)
		);

		$download = self::$downloads[0];
		edd_add_to_cart( $download->ID );
		$cart_contents = edd_get_cart_contents();

		$this->assertEquals( 4, edd_get_item_discount_amount( reset( $cart_contents ), $cart_contents, array( self::$discount->code ) ) );
	}

	public function test_get_cart_item_discount_amount_in_excluded_category_is_0() {
		edd_update_discount(
			self::$discount->id,
			array(
				'min_charge_amount' => 0,
				'categories'        => array( self::$category ),
				'term_condition'    => 'exclude',
			)
		);

		$download = self::$downloads[1];
		edd_add_to_cart( $download->ID );
		$cart_contents = edd_get_cart_contents();

		$this->assertEquals( 0, edd_get_item_discount_amount( reset( $cart_contents ), $cart_contents, array( self::$discount->code ) ) );
	}

	/**
	 * Test a discount that requires a product which is excluded by its category.
	 *
	 * @return void
	 */
	public function test_edd_validate_discount_product_requirements_but_excluded_category() {
		$download = self::$downloads[1];
		$args     = array(
			'product_reqs'      => array( $download->ID ),
			'product_condition' => 'all',
			'max_uses'          => 10000,
			'categories'        => array( self::$category ),
			'term_condition'    => 'exclude',
			'min_charge_amount' => 0,
			'scope'             => 'global',
		);
		edd_update_discount( self::$discount->id, $args );
		edd_add_to_cart( $download->ID );

		$discount      = edd_get_discount( self::$discount->id );
		$cart_contents = edd_get_cart_contents();
		$this->assertTrue( $discount->is_product_requirements_met( false ) );
		$this->assertFalse( $discount->is_valid_for_categories( false ) );
		$this->assertEquals( 0, edd_get_item_discount_amount( reset( $cart_contents ), $cart_contents, array( self::$discount->code ) ) );
	}

	/**
	 * Test a discount for a product requirement that allows other items in the cart to be discounted.
	 *
	 * @return void
	 */
	public function test_edd_validate_discount_product_requirements_but_excluded_category_two_products() {
		$download = self::$downloads[1];
		$args     = array(
			'product_reqs'      => array( $download->ID ),
			'product_condition' => 'all',
			'max_uses'          => 10000,
			'categories'        => array( self::$category ),
			'term_condition'    => 'exclude',
			'min_charge_amount' => 0,
			'scope'             => 'global',
		);
		edd_update_discount( self::$discount->id, $args );
		edd_add_to_cart( $download->ID );

		$download_2 = self::$downloads[0];
		edd_add_to_cart( $download_2->ID );

		$discount      = edd_get_discount( self::$discount->id );
		$cart_contents = edd_get_cart_contents();
		$this->assertTrue( $discount->is_product_requirements_met( false ) );
		$this->assertTrue( $discount->is_valid_for_categories( false ) );
		$this->assertEquals( 0, edd_get_item_discount_amount( reset( $cart_contents ), $cart_contents, array( self::$discount->code ) ) );
		$this->assertEquals( 4.0, edd_get_item_discount_amount( $cart_contents[1], $cart_contents, array( self::$discount->code ) ) );
	}
}
