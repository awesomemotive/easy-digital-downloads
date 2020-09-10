<?php

/**
 * Structured Data Tests
 *
 * @covers EDD_Structured_Data
 * @group edd_structured_data
 */
class Tests_Structured_Data extends EDD_UnitTestCase {

	/**
	 * Download test fixture.
	 *
	 * @var WP_Post
	 */
	protected static $download = null;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$download = EDD_Helper_Download::create_simple_download();
	}

	/**
	 * @covers EDD_Structured_Data::get_data()
	 */
	public function test_get_data_with_no_data() {
		$this->assertEmpty( EDD()->structured_data->get_data() );
	}

	/**
	 * @covers EDD_Structured_Data::generate_structured_data()
	 * @covers EDD_Structured_Data::get_data()
	 */
	public function test_generate_structured_data_for_download() {
		EDD()->structured_data->generate_structured_data( 'download', self::$download->ID );

		$data = EDD()->structured_data->get_data();

		$product = $data[0];

		// @type
		$this->assertEquals( 'Product', $product['@type'] );

		// name
		$this->assertEquals( self::$download->post_title, $product['name'] );

		// url
		$this->assertArrayHasKey( 'url', $product );

		// image
		$this->assertArrayNotHasKey( 'image', $product );

		// brand
		$this->assertArrayHasKey( 'brand', $product );
		$this->assertEquals( 'Thing', $product['brand']['@type'] );
		$this->assertEquals( get_bloginfo( 'name' ), $product['brand']['name'] );

		// offers
		$this->assertArrayHasKey( 'offers', $product );
	}

	/**
	 * @covers EDD_Structured_Data::generate_download_data()
	 */
	public function test_generate_download_data() {
		EDD()->structured_data->generate_download_data( self::$download->ID );

		$data = EDD()->structured_data->get_data();

		$this->assertEquals( self::$download->post_title, $data[1]['name'] );
	}

	/**
	 * @covers EDD_Structured_Data::output_structured_data()
	 * @covers EDD_Structured_Data::sanitize_data()
	 * @covers EDD_Structured_Data::encoded_data()
	 */
	public function test_output_structured_data() {
		$this->expectOutputRegex( '/<script type="application\/ld\+json">(.*?)<\/script>/' );
		EDD()->structured_data->output_structured_data();
	}
}