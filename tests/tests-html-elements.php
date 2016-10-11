<?php


/**
 * @group edd_html
 */
class Test_HTML_Elements extends WP_UnitTestCase {
	protected $_post_id = null;

	public function setUp() {
		parent::setUp();

		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );
		$this->_post_id = $post_id;
	}

	public function test_product_dropdown() {
		$expected = '<select name="products" id="products" class="edd-select " data-placeholder="Choose a Download" data-search-type="download">';
		$this->assertContains( $expected, EDD()->html->product_dropdown() );
	}

	public function test_product_dropdown_value_parse() {
		$expected = array( 'download_id' => '123', 'price_id' => '1' );
		$this->assertEquals( $expected, edd_parse_product_dropdown_value( '123_1' ) );

		$expected = array( 'download_id' => '123', 'price_id' => false );
		$this->assertEquals( $expected, edd_parse_product_dropdown_value( '123' ) );

		$expected = array( 'download_id' => '123', 'price_id' => false );
		$this->assertEquals( $expected, edd_parse_product_dropdown_value( 123 ) );
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

		$this->assertEquals( $expected, edd_parse_product_dropdown_values( $saved_values ) );
	}

	public function test_product_dropdown_string_parse() {
		$saved_values = '155';
		$expected     = array(
			array(
				'download_id' => '155',
				'price_id'    => false,
			),
		);

		$this->assertEquals( $expected, edd_parse_product_dropdown_values( $saved_values ) );

		$saved_values = '155_1';
		$expected     = array(
			array(
				'download_id' => '155',
				'price_id'    => '1',
			),
		);

		$this->assertEquals( $expected, edd_parse_product_dropdown_values( $saved_values ) );
	}

	public function test_customer_dropdown() {
		$expected = '<select name="customers" id="customers" class="edd-select  edd-customer-select edd-select-chosen" data-placeholder="Select a Customer" data-search-type="customer">';
		$this->assertContains( $expected, EDD()->html->customer_dropdown() );
	}

	public function test_discount_dropdown() {
		$meta = array(
			'name' => '50 Percent Off',
			'type' => 'percent',
			'amount' => '50',
			'code' => '50PERCENTOFF',
			'product_condition' => 'all'
		);

		edd_store_discount( $meta );

		$expected = '<select name="edd_discounts" id="" class="edd-select " data-placeholder=""><option value="-1">Select a discount</option><option value="'. edd_get_discount_id_by_code( '50PERCENTOFF' ) .'">50 Percent Off</option></select>';
		$this->assertEquals( $expected, EDD()->html->discount_dropdown() );
	}

	public function test_category_dropdown() {
		$expected = '<select name="edd_categories" id="" class="edd-select " data-placeholder=""><option value="all" selected=\'selected\'>All Download Categories</option></select>';
		$this->assertEquals( $expected, EDD()->html->category_dropdown() );
	}

	public function test_year_dropdown() {
		$current_year = date( 'Y' );
		$expected = '<select name="year" id="" class="edd-select " data-placeholder="">';
		$i = 5;
		while ( $i >= 0 ) {
			$selected  = 0 === $i ? ' selected=\'selected\'' : '';
			$option_year = $current_year - $i;
			$expected .= '<option value="' . $option_year . '"' . $selected . '>' . $option_year . '</option>';
			$i--;
		}
		$expected .= '</select>';
		$this->assertEquals( $expected, EDD()->html->year_dropdown() );
	}

	public function test_year_dropdown_variable() {
		$years_before = 5;
		$years_after  = 5;
		$current_year = date( 'Y' );

		$start_year    = $current_year - $years_before;
		$end_year      = $current_year + $years_after;

		$expected = '<select name="year" id="" class="edd-select " data-placeholder="">';
		while ( $start_year <= $end_year ) {
			$selected  = $start_year == $current_year ? ' selected=\'selected\'' : '';
			$expected .= '<option value="' . $start_year . '"' . $selected . '>' . $start_year . '</option>';
			$start_year++;
		}
		$expected .= '</select>';
		$this->assertEquals( $expected, EDD()->html->year_dropdown( 'year', 0, $years_before, $years_after ) );

	}

	public function test_month_dropdown() {
		$out = EDD()->html->month_dropdown();
		$this->assertContains( '<select name="month" id="" class="edd-select "', $out );
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
