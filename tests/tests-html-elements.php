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
		$expected = '<select name="products" id="products" class="edd-select " data-placeholder="Select a Download">';
		$this->assertContains( $expected, EDD()->html->product_dropdown() );
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

		$expected = '<select name="edd_discounts" id="" class="edd-select " data-placeholder=""><option value="'. edd_get_discount_id_by_code( '50PERCENTOFF' ) .'">50 Percent Off</option></select>';
		$this->assertEquals( $expected, EDD()->html->discount_dropdown() );
	}

	public function test_category_dropdown() {
		$expected = '<select name="edd_categories" id="" class="edd-select " data-placeholder=""></select>';
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
