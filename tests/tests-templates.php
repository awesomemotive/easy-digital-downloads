<?php
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * @group edd_mime
 * @group edd_functions
 */
class Tests_Templates extends EDD_UnitTestCase {

	protected $_post;

	public function setup(): void {
		parent::setUp();

		$post_id = $this->factory->post->create( array( 'post_title' => 'A Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );

		$_variable_pricing = array(
			array(
				'name' => 'Simple',
				'amount' => 20
			),
			array(
				'name' => 'Advanced',
				'amount' => 100
			)
		);

		$_download_files = array(
			array(
				'name' => 'File 1',
				'file' => 'http://localhost/file1.jpg',
				'condition' => 0
			),
			array(
				'name' => 'File 2',
				'file' => 'http://localhost/file2.jpg',
				'condition' => 'all'
			)
		);

		$meta = array(
			'edd_price' => '0.00',
			'_variable_pricing' => 1,
			'_edd_price_options_mode' => 'on',
			'edd_variable_prices' => array_values( $_variable_pricing ),
			'edd_download_files' => array_values( $_download_files ),
			'_edd_download_limit' => 20,
			'_edd_hide_purchase_link' => 1,
			'edd_product_notes' => 'Purchase Notes',
			'_edd_product_type' => 'default',
			'_edd_download_earnings' => 129.43,
			'_edd_download_sales' => 59,
			'_edd_download_limit_override_1' => 1,
			'edd_sku' => 'sku1234567'
		);
		foreach( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		$this->_post = get_post( $post_id );

	}

	public function tearDown(): void {
		parent::tearDown();
	}

	public function test_get_purchase_link() {
		$link = edd_get_purchase_link( array( 'download_id' => $this->_post->ID ) );
		$this->assertIsString( $link );
		$this->assertStringContainsString( '<form id="edd_purchase_', $link );
		$this->assertStringContainsString( 'class="edd_download_purchase_form', $link );
		$this->assertStringContainsString( 'method="post">', $link );
		$this->assertStringContainsString( '<input type="hidden" name="download_id" value="' . $this->_post->ID . '">', $link );

		// The product we created has variable pricing, so ensure the price options render
		$this->assertStringContainsString( '<div class="edd_price_options', $link );
		$this->assertStringContainsString( '<span class="edd_price_option_name">', $link );

		add_filter( 'edd_item_quantities_enabled', '__return_true' );
		$link = edd_get_purchase_link( array( 'download_id' => $this->_post->ID ) );
		$this->assertIsString( $link );
		remove_filter( 'edd_item_quantities_enabled', '__return_true' );

		// Test a single price point as well
		$single_id = $this->factory->post->create( array( 'post_title' => 'A Test Single Price Download', 'post_type' => 'download', 'post_status' => 'publish' ) );
		$meta = array(
			'edd_price' => '10.00',
			'_edd_download_limit' => 20,
			'_edd_product_type' => 'default',
			'_edd_download_earnings' => 0,
			'_edd_download_sales' => 0
		);

		foreach( $meta as $key => $value ) {
			update_post_meta( $single_id, $key, $value );
		}

		$single_link_default = edd_get_purchase_link( array( 'download_id' => $single_id ) );
		$this->assertStringContainsString( 'data-price="10.00"', $single_link_default );
		$this->assertStringContainsString( '<span class="edd-add-to-cart-label">&#36;10.00&nbsp;&ndash;&nbsp;Purchase</span>', $single_link_default );

		// Verify the purchase link works with price = 0
		$single_link_no_price = edd_get_purchase_link( array( 'download_id' => $single_id, 'price' => 0 ) );
		// Price should NOT show on button
		$this->assertStringContainsString( '<span class="edd-add-to-cart-label">Purchase</span>', $single_link_no_price );
		// data-price should still contain the price
		$this->assertStringContainsString( 'data-price="10.00"', $single_link_no_price );
	}

	// For issue #4755
	public function test_get_purchase_link_invalid_sku() {
		$link = edd_get_purchase_link( array( 'sku' => 'SKU' ) );
		$this->assertTrue( empty( $link ) );
	}

	public function test_button_colors() {
		$colors = edd_get_button_colors();
		$this->assertIsArray( $colors );
		$this->assertarrayHasKey( 'white', $colors );
		$this->assertarrayHasKey( 'gray', $colors );
		$this->assertarrayHasKey( 'blue', $colors );
		$this->assertarrayHasKey( 'red', $colors );
		$this->assertarrayHasKey( 'green', $colors );
		$this->assertarrayHasKey( 'yellow', $colors );
		$this->assertarrayHasKey( 'orange', $colors );
		$this->assertarrayHasKey( 'dark-gray', $colors );
		$this->assertarrayHasKey( 'inherit', $colors );
		$this->assertIsArray( $colors['white'] );
		$this->assertEquals( 'White', $colors['white']['label'] );
	}

	public function test_button_styles() {
		$styles = edd_get_button_styles();
		$this->assertIsArray( $styles );
		$this->assertarrayHasKey( 'button', $styles );
		$this->assertarrayHasKey( 'plain', $styles );
		$this->assertEquals( 'Button', $styles['button'] );
		$this->assertEquals( 'Plain Text', $styles['plain'] );
	}

	public function test_locate_template() {
		// Test that a file path is found
		$this->assertIsString( edd_locate_template( 'history-purchases.php' ) );
	}

	public function test_get_theme_template_paths() {
		$paths = edd_get_theme_template_paths();
		$this->assertIsArray( $paths );
		$this->assertarrayHasKey( 1, $paths );
		$this->assertarrayHasKey( 10, $paths );
		$this->assertarrayHasKey( 100, $paths );
		$this->assertIsString( $paths[1] );
		$this->assertIsString( $paths[10] );
		$this->assertIsString( $paths[100] );
	}

	public function test_get_templates_dir_name() {
		$this->assertEquals( 'edd_templates/', edd_get_theme_template_dir_name() );
	}
}
