<?php
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * EDD HTML Elements Tests
 *
 * @group edd_html
 *
 * @coversDefaultClass EDD_HTML_Elements
 */
class HTMLElements extends EDD_UnitTestCase {

	/**
	 * @covers ::product_dropdown
	 */
	public function test_product_dropdown() {
		$expected = '<select name="products" id="products" class="edd-select " data-placeholder="Choose a Download" data-search-type="download" data-search-placeholder="Search Downloads">';
		$this->assertStringContainsString( $expected, EDD()->html->product_dropdown() );
	}

	public function test_product_dropdown_value_parse_should_be_123_1() {
		$expected = array(
			'download_id' => '123',
			'price_id'    => '1',
		);

		$this->assertEqualSetsWithIndex( $expected, edd_parse_product_dropdown_value( '123_1' ) );
	}

	public function test_product_dropdown_value_parse_should_be_123() {
		$expected = array(
			'download_id' => '123',
			'price_id'    => false,
		);

		$this->assertEqualSetsWithIndex( $expected, edd_parse_product_dropdown_value( '123' ) );
		$this->assertEqualSetsWithIndex( $expected, edd_parse_product_dropdown_value( 123 ) );
	}

	public function test_product_dropdown_array_parse() {
		$saved_values = array( 123, '155_1', '155_2', 99 );
		$expected     = array(
			array(
				'download_id' => '123',
				'price_id'    => false,
			),
			array(
				'download_id' => '155',
				'price_id'    => '1',
			),
			array(
				'download_id' => '155',
				'price_id'    => '2',
			),
			array(
				'download_id' => '99',
				'price_id'    => false,
			),
		);

		$this->assertEqualSetsWithIndex( $expected, edd_parse_product_dropdown_values( $saved_values ) );
	}

	public function test_product_dropdown_string_parse() {
		$saved_values = '155';
		$expected     = array(
			array(
				'download_id' => '155',
				'price_id'    => false,
			),
		);

		$this->assertEqualSetsWithIndex( $expected, edd_parse_product_dropdown_values( $saved_values ) );

		$saved_values = '155_1';
		$expected     = array(
			array(
				'download_id' => '155',
				'price_id'    => '1',
			),
		);

		$this->assertEqualSetsWithIndex( $expected, edd_parse_product_dropdown_values( $saved_values ) );
	}

	/**
	 * @covers ::customer_dropdown
	 */
	public function test_customer_dropdown() {
		$expected = '<select name="customers" id="customers" class="edd-select  edd-customer-select edd-select-chosen" data-placeholder="Choose a Customer" data-search-type="customer" data-search-placeholder="Search Customers"><option value="0" selected=\'selected\'>No customers found</option></select>';

		$this->assertStringContainsString( $expected, EDD()->html->customer_dropdown() );
	}

	/**
	 * @covers ::discount_dropdown
	 */
	public function test_discount_dropdown() {
		$meta = array(
			'name'              => '50 Percent Off',
			'type'              => 'percent',
			'amount'            => '50',
			'code'              => '50PERCENTOFF',
			'product_condition' => 'all',
		);

		edd_store_discount( $meta );

		$expected = '<select name="edd_discounts" id="discounts" class="edd-select  edd-user-select edd-select-chosen" data-placeholder="Choose a Discount"><option value="all" selected=\'selected\'>All Discounts</option><option value="' . edd_get_discount_id_by_code( '50PERCENTOFF' ) . '">50 Percent Off</option></select>';

		$this->assertSame( $expected, EDD()->html->discount_dropdown() );
	}

	/**
	 * @covers ::category_dropdown
	 */
	public function test_category_dropdown() {
		$expected = '<select name="edd_categories" id="" class="edd-select " data-placeholder=""><option value="all" selected=\'selected\'>All Download Categories</option></select>';
		$this->assertEquals( $expected, EDD()->html->category_dropdown() );
	}

	/**
	 * @covers ::year_dropdown
	 */
	public function test_year_dropdown() {
		$current_year = date( 'Y' );
		$expected     = '<select name="year" id="edd_year_select_year" class="edd-select " data-placeholder="">';
		$i            = 5;

		while ( $i >= 0 ) {
			$selected    = 0 === $i ? ' selected=\'selected\'' : '';
			$option_year = $current_year - $i;
			$expected   .= '<option value="' . $option_year . '"' . $selected . '>' . $option_year . '</option>';
			$i--;
		}

		$expected .= '</select>';

		$this->assertEquals( $expected, EDD()->html->year_dropdown() );
	}

	/**
	 * @covers ::year_dropdown
	 */
	public function test_year_dropdown_variable() {
		$years_before = 5;
		$years_after  = 5;
		$current_year = date( 'Y' );

		$start_year = $current_year - $years_before;
		$end_year   = $current_year + $years_after;

		$expected = '<select name="year" id="edd_year_select_year" class="edd-select " data-placeholder="">';

		while ( $start_year <= $end_year ) {
			$selected = (int) $start_year === (int) $current_year
				? ' selected=\'selected\''
				: '';

			$expected .= '<option value="' . $start_year . '"' . $selected . '>' . $start_year . '</option>';
			$start_year++;
		}
		$expected .= '</select>';

		$this->assertEquals( $expected, EDD()->html->year_dropdown( 'year', 0, $years_before, $years_after ) );

	}

	/**
	 * @covers ::month_dropdown
	 */
	public function test_month_dropdown() {
		$out = EDD()->html->month_dropdown();

		$this->assertStringContainsString( '<select name="month" id="edd_month_select_month" class="edd-select "', $out );
		$this->assertStringContainsString( '<option value="1"', $out );
		$this->assertStringContainsString( '<option value="2"', $out );
		$this->assertStringContainsString( '<option value="3"', $out );
		$this->assertStringContainsString( '<option value="4"', $out );
		$this->assertStringContainsString( '<option value="5"', $out );
		$this->assertStringContainsString( '<option value="6"', $out );
		$this->assertStringContainsString( '<option value="7"', $out );
		$this->assertStringContainsString( '<option value="8"', $out );
		$this->assertStringContainsString( '<option value="9"', $out );
		$this->assertStringContainsString( '<option value="10"', $out );
		$this->assertStringContainsString( '<option value="11"', $out );
		$this->assertStringContainsString( '<option value="12"', $out );
	}

	/**
	 * @covers EDD_HTML_Elements::select
	 */
	public function test_select_is_required() {
		$select = EDD()->html->select(
			array(
				'required' => true,
				'options'  => array(
					1 => '1',
					2 => '2',
					3 => '3',
				),
			)
		);

		$this->assertStringContainsString( 'required', $select );
	}

	/**
	 * @covers EDD_HTML_Elements::select
	 */
	public function test_select_is_not_required() {
		$select = EDD()->html->select(
			array(
				'options' => array(
					1 => '1',
					2 => '2',
					3 => '3',
				)
			)
		);

		$this->assertStringNotContainsString( 'required', $select );
	}

	/**
	 * @covers EDD_HTML_Elements::text
	 */
	public function test_text_is_required() {
		$this->assertStringContainsString( 'required', EDD()->html->text( array( 'required' => true ) ) );
	}

	/**
	 * @covers EDD_HTML_Elements::text
	 */
	public function test_text_is_not_required() {
		$this->assertStringNotContainsString( 'required', EDD()->html->text() );
	}

	public function test_category_select() {
		for ( $i = 0; $i < 5; $i++ ) {
			$category = wp_insert_term( 'Download Category ' . $i, 'download_category' );
			// create a post and assign it to the category
			$post = wp_insert_post(
				array(
					'post_title'   => 'Download ' . $i,
					'post_content' => 'Download ' . $i,
					'post_status'  => 'publish',
					'post_type'    => 'download',
					'post_author'  => 1,
					'post_date'    => date( 'Y-m-d H:i:s' ),
					'post_category' => array( $category['term_id'] ),
				)
			);
			wp_set_object_terms( $post, $category['term_id'], 'download_category' );
		}

		$dropdown = new \EDD\HTML\CategorySelect(
			array(
				'name'             => 'categories[]',
				'id'               => 'edd-categories',
				'selected'         => array(),
				'multiple'         => true,
				'chosen'           => true,
				'show_option_all'  => false,
				'show_option_none' => false,
				'number'           => 30,
			)
		);
		$expected = '<select name="categories[]" id="edd_categories" class="edd-select  edd-select-chosen"';

		$this->assertStringContainsString( $expected, $dropdown->get() );
	}
}
