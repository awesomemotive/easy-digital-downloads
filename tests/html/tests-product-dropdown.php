<?php

namespace EDD\Tests\HTML;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Helpers\EDD_Helper_Download;

/**
 * EDD HTML Elements Tests
 *
 * @group edd_html
 *
 * @coversDefaultClass EDD_HTML_Elements
 */
class ProductDropdown extends EDD_UnitTestCase {

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		// Create some downloads.
		EDD_Helper_Download::create_simple_download();
		EDD_Helper_Download::create_simple_download();
		EDD_Helper_Download::create_variable_download();
		EDD_Helper_Download::create_variable_download();
	}

	public static function tearDownAfterClass(): void {
		parent::tearDownAfterClass();

		// Remove the downloads.
		EDD_Helper_Download::delete_all_downloads();
	}

	public function test_product_dropdown() {
		$product_dropdown = EDD()->html->product_dropdown();
		$this->assertStringContainsString( 'name="products"', $product_dropdown );
		$this->assertStringContainsString( 'id="products"', $product_dropdown );
		$this->assertStringContainsString( 'class="edd-select"', $product_dropdown );
		$this->assertStringContainsString( 'data-placeholder="Choose a Download"', $product_dropdown );
		$this->assertStringContainsString( 'data-search-type="download"', $product_dropdown );
		$this->assertStringContainsString( 'data-search-placeholder="Search Downloads"', $product_dropdown );
		$this->assertStringContainsString( 'value=""', $product_dropdown );
	}

	public function test_product_dropdown_with_simple_download_selected() {
		$download         = EDD_Helper_Download::create_simple_download();
		$product_dropdown = EDD()->html->product_dropdown( array( 'selected' => $download->ID ) );

		$this->assertStringContainsString( 'value="' . $download->ID . '" selected', $product_dropdown );
	}

	public function test_product_dropdown_with_variable_download_selected() {
		$download         = EDD_Helper_Download::create_variable_download();
		$variation        = "{$download->ID}_1";
		$product_dropdown = EDD()->html->product_dropdown(
			array(
				'variations'           => true,
				'show_variations_only' => true,
			)
		);

		$this->assertStringContainsString( 'value="' . $variation . '"', $product_dropdown );
		$this->assertStringNotContainsString( 'value="' . $download->ID . '"', $product_dropdown );
	}

	public function test_product_dropdown_bundles_included() {
		$bundled_download = EDD_Helper_Download::create_bundled_download();
		$product_dropdown = EDD()->html->product_dropdown();

		$this->assertStringContainsString( 'value="' . $bundled_download->ID . '"', $product_dropdown );
	}

	public function test_product_dropdown_bundles_excluded() {
		$bundled_download = EDD_Helper_Download::create_bundled_download();
		$product_dropdown = EDD()->html->product_dropdown(
			array(
				'bundles' => false,
			)
		);

		$this->assertStringNotContainsString( 'value="' . $bundled_download->ID . '"', $product_dropdown );
	}

	public function test_product_dropdown_selected_is_included() {
		$download         = EDD_Helper_Download::create_simple_download();
		$product_dropdown = EDD()->html->product_dropdown(
			array(
				'number'   => 1,
				'selected' => $download->ID,
			)
		);

		$this->assertStringContainsString( 'value="' . $download->ID . '" selected', $product_dropdown );
	}

	public function test_product_dropdown_multiple_selected_are_included() {
		$download1        = EDD_Helper_Download::create_simple_download();
		$download2        = EDD_Helper_Download::create_variable_download();
		$variation        = "{$download2->ID}_1";
		$product_dropdown = EDD()->html->product_dropdown(
			array(
				'number'   => 1,
				'selected' => array( $download1->ID, $variation ),
				'multiple' => true,
			)
		);

		$this->assertStringContainsString( 'value="' . $download1->ID . '" selected', $product_dropdown );
		$this->assertStringContainsString( 'value="' . $variation . '" selected', $product_dropdown );
		$this->assertStringNotContainsString( 'value="' . $download2->ID . '" selected', $product_dropdown );
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

	public function test_post_type_not_download_is_not_included() {
		$not_download = wp_insert_post(
			array(
				'post_type' => 'page',
				'post_title' => 'Not a Download',
			)
		);
		$product_dropdown = EDD()->html->product_dropdown(
			array(
				'selected' => $not_download,
			)
		);

		$this->assertStringNotContainsString( 'value="' . $not_download . '"', $product_dropdown );
	}

	public function test_product_dropdown_with_show_empty_false_still_has_empty_option() {
		$product_dropdown = EDD()->html->product_dropdown(
			array(
				'show_option_empty' => false,
			)
		);

		$this->assertStringContainsString( '<option value="">', $product_dropdown );
		$this->assertStringContainsString( 'All Downloads', $product_dropdown );
	}
}
