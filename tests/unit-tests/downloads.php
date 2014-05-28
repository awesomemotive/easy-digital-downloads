<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_cpt
 */
class Tests_Downloads extends EDD_UnitTestCase {
	protected $_post = null;

	protected $_variable_pricing = null;

	protected $_download_files = null;

	public function setUp() {
		parent::setUp();

		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Download', 'post_name' => 'test-download', 'post_type' => 'download', 'post_status' => 'draft' ) );

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
			'edd_sku' => 'sku_001'
		);
		foreach( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		$this->_post = get_post( $post_id );
	}

	public function test_get_download() {
		$out = edd_get_download( $this->_post->ID );

		$this->assertObjectHasAttribute( 'ID', $out );
		$this->assertObjectHasAttribute( 'post_title', $out );
		$this->assertObjectHasAttribute( 'post_type', $out );

		$this->assertEquals( $out->post_type, $this->_post->post_type );
	}

	public function test_edd_get_download_by() {
		
		$download = edd_get_download_by( 'id', $this->_post->ID );
		$this->assertSame( $this->_post->ID, $download->ID );
		
		$download = edd_get_download_by( 'sku', 'sku_001' );
		$this->assertSame( $this->_post->ID, $download->ID );
		
		$download = edd_get_download_by( 'name', 'test-download' );
		$this->assertSame( $this->_post->ID, $download->ID );

	}

	public function test_download_price() {
		// This is correct and should equal 0.00 because this download uses variable pricing
		$this->assertEquals( 0.00, edd_get_download_price( $this->_post->ID ) );
	}

	public function test_variable_pricing() {
		$out = edd_get_variable_prices( $this->_post->ID );
		$this->assertNotEmpty( $out );
		foreach ( $out as $var ) {
			$this->assertArrayHasKey( 'name', $var );
			$this->assertArrayHasKey( 'amount', $var );

			if ( $var['name'] == 'Simple' ) {
				$this->assertEquals( 20, $var['amount'] );
			}

			if ( $var['name'] == 'Advanced' ) {
				$this->assertEquals( 100, $var['amount'] );
			}
		}
	}

	public function test_has_variable_prices() {
		$this->assertTrue( edd_has_variable_prices( $this->_post->ID ) );
	}

	public function test_get_price_option_name() {
		$this->assertEquals( 'Simple', edd_get_price_option_name( $this->_post->ID, 0 ) );
		$this->assertEquals( 'Advanced', edd_get_price_option_name( $this->_post->ID, 1 ) );
	}

	public function test_get_lowest_price_option() {
		$this->assertEquals( 20, edd_get_lowest_price_option( $this->_post->ID ) );
	}

	public function test_get_highest_price_option() {
		$this->assertEquals( 100, edd_get_highest_price_option( $this->_post->ID ) );
	}

	public function test_price_range() {
		$range = edd_price_range( $this->_post->ID );
		$expected = '<span class="edd_price_range_low">&#36;20.00</span><span class="edd_price_range_sep">&nbsp;&ndash;&nbsp;</span><span class="edd_price_range_high">&#36;100.00</span>';
		$this->assertInternalType( 'string', $range );
		$this->assertEquals( $expected, $range );
	}

	public function test_single_price_option_mode() {
		$this->assertTrue( edd_single_price_option_mode( $this->_post->ID ) );
	}

	public function test_download_type() {
		$this->assertEquals( 'default', edd_get_download_type( $this->_post->ID ) );
	}

	public function test_download_earnings() {
		$this->assertEquals( 129.43, edd_get_download_earnings_stats( $this->_post->ID ) );
	}

	public function test_download_sales() {
		$this->assertEquals( 59, edd_get_download_sales_stats( $this->_post->ID ) );
	}

	public function test_increase_purchase_count() {
		$this->assertEquals( 60, edd_increase_purchase_count( $this->_post->ID ) );
	}

	public function test_decrease_purchase_count() {
		$this->assertEquals( 58, edd_decrease_purchase_count( $this->_post->ID ) );
	}

	public function test_earnings_increase() {
		$this->assertEquals( 149.43, edd_increase_earnings( $this->_post->ID, 20 ) );
	}

	public function test_decrease_earnings() {
		$this->assertEquals( 109.43, edd_decrease_earnings( $this->_post->ID, 20 ) );
	}

	public function test_get_download_files() {
		$out = edd_get_download_files( $this->_post->ID );

		foreach ( $out as $file ) {
			$this->assertArrayHasKey( 'name', $file );
			$this->assertArrayHasKey( 'file', $file );
			$this->assertArrayHasKey( 'condition', $file );

			if ( $file['name'] == 'File 1' ) {
				$this->assertEquals( 'http://localhost/file1.jpg', $file['file'] );
				$this->assertEquals( 0, $file['condition'] );
			}

			if ( $file['name'] == 'File 2' ) {
				$this->assertEquals( 'http://localhost/file2.jpg', $file['file'] );
				$this->assertEquals( 'all', $file['condition'] );
			}
		}
	}

	public function test_get_file_download_limit() {
		$this->assertEquals( 20, edd_get_file_download_limit( $this->_post->ID ) );
	}

	public function test_get_file_download_limit_override() {
		$this->assertEquals( 1, edd_get_file_download_limit_override( $this->_post->ID, 1 ) );
	}

	public function test_is_file_at_download_limit() {
		$this->assertFalse( edd_is_file_at_download_limit( $this->_post->ID, 1, 1 ) );
	}

	public function test_get_file_price_condition() {
		$this->assertEquals( 0, edd_get_file_price_condition( $this->_post->ID, 0 ) );
		$this->assertEquals( 'all', edd_get_file_price_condition( $this->_post->ID, 1 ) );
	}

	public function test_get_product_notes() {
		$this->assertEquals( 'Purchase Notes', edd_get_product_notes( $this->_post->ID ) );
	}

	public function test_get_download_type() {
		$this->assertEquals( 'default', edd_get_download_type( $this->_post->ID ) );
	}

	public function test_get_download_is_bundle() {
		$this->assertFalse( edd_is_bundled_product( $this->_post->ID ) );
	}
}
