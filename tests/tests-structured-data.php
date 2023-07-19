<?php
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

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
		self::$download = Helpers\EDD_Helper_Download::create_simple_download();
	}

	public function tearDown(): void {
		parent::tearDown();
		EDD()->structured_data->reset();
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

		$product = reset( $data );

		// @type
		$this->assertEquals( 'Product', $product['@type'] );

		// name
		$this->assertEquals( self::$download->post_title, $product['name'] );

		// url
		$this->assertArrayHasKey( 'url', $product );

		// image
		$this->assertEmpty( $product['image'] );

		// brand
		$this->assertArrayHasKey( 'brand', $product );
		$this->assertEquals( 'https://schema.org/Brand', $product['brand']['@type'] );
		$this->assertEquals( get_bloginfo( 'name' ), $product['brand']['name'] );

		// offers
		$this->assertArrayHasKey( 'offers', $product );
	}

	/**
	 * @covers EDD_Structured_Data::generate_download_data()
	 */
	public function test_generate_download_data_for_non_download_should_return_false() {
		$this->assertFalse( EDD()->structured_data->generate_download_data( 2341234 ) );
	}

	/**
	 * @covers EDD_Structured_Data::generate_download_data()
	 */
	public function test_generate_download_data() {
		EDD()->structured_data->generate_download_data( self::$download->ID );

		$all_data = EDD()->structured_data->get_data();
		$data     = reset( $all_data );

		$this->assertEquals( self::$download->post_title, $data['name'] );
	}

	/**
	 * @covers EDD_Structured_Data::output_structured_data()
	 * @covers EDD_Structured_Data::sanitize_data()
	 * @covers EDD_Structured_Data::encoded_data()
	 */
	public function test_output_structured_data() {
		$this->expectOutputRegex( '/<script type="application\/ld\+json">(.*?)<\/script>/' );
		EDD()->structured_data->generate_download_data( self::$download->ID );
		EDD()->structured_data->output_structured_data();
	}

	public function test_variable_product_structured_data() {
		$variable_product = Helpers\EDD_Helper_Download::create_variable_download();

		EDD()->structured_data->generate_structured_data( 'download', $variable_product->ID );

		$data    = EDD()->structured_data->get_data( $variable_product->ID );
		$product = reset( $data );

		// @type
		$this->assertEquals( 'Product', $product['@type'] );

		// assert that product offers is an array
		$this->assertIsArray( $product['offers'] );
		$this->assertEquals( 2, count( $product['offers'] ) );
		$this->assertNotEmpty( $product['offers'][0]['priceValidUntil'] );
	}
}
