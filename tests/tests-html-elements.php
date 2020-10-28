<?php

/**
 * EDD HTML Elements Tests
 *
 * @group edd_html
 *
 * @coversDefaultClass EDD_HTML_Elements
 */
class Test_HTML_Elements extends EDD_UnitTestCase {

	/**
	 * @covers ::product_dropdown
	 */
	public function test_product_dropdown() {
		$expected = '<select name="products" id="products" class="edd-select " data-placeholder="Choose a Download" data-search-type="download" data-search-placeholder="Search Downloads">';
		$this->assertContains( $expected, EDD()->html->product_dropdown() );
	}

	/**
	 * @covers ::edd_parse_product_dropdown_value
	 */
	public function test_product_dropdown_value_parse_should_be_123_1() {
		$expected = array(
			'download_id' => '123',
			'price_id'    => '1',
		);

		$this->assertEqualSetsWithIndex( $expected, edd_parse_product_dropdown_value( '123_1' ) );
	}

	/**
	 * @covers ::edd_parse_product_dropdown_value
	 */
	public function test_product_dropdown_value_parse_should_be_123() {
		$expected = array(
			'download_id' => '123',
			'price_id'    => false,
		);

		$this->assertEqualSetsWithIndex( $expected, edd_parse_product_dropdown_value( '123' ) );
		$this->assertEqualSetsWithIndex( $expected, edd_parse_product_dropdown_value( 123 ) );
	}

	/**
	 * @covers ::edd_parse_product_dropdown_value
	 */
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

	/**
	 * @covers ::edd_parse_product_dropdown_value
	 */
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

		$this->assertContains( $expected, EDD()->html->customer_dropdown() );
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
		$expected     = '<select name="year" id="edd-year-dropdown_year" class="edd-select " data-placeholder="">';
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

		$expected = '<select name="year" id="edd-year-dropdown_year" class="edd-select " data-placeholder="">';

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

		$this->assertContains( '<select name="month" id="edd-month-dropdown_month" class="edd-select "', $out );
		$this->assertContains( '<option value="1"', $out );
		$this->assertContains( '<option value="2"', $out );
		$this->assertContains( '<option value="3"', $out );
		$this->assertContains( '<option value="4"', $out );
		$this->assertContains( '<option value="5"', $out );
		$this->assertContains( '<option value="6"', $out );
		$this->assertContains( '<option value="7"', $out );
		$this->assertContains( '<option value="8"', $out );
		$this->assertContains( '<option value="9"', $out );
		$this->assertContains( '<option value="10"', $out );
		$this->assertContains( '<option value="11"', $out );
		$this->assertContains( '<option value="12"', $out );
	}
}
