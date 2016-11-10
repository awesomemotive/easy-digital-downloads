<?php


/**
 * @group edd_downloads
 */
class Tests_Process_Download extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_set_scheme() {
		$home_url = get_home_url();
		$file       = trailingslashit( $home_url ) . 'test-file.jpg';
		$https_file = str_replace( 'http://', 'https://', $file );

		$this->assertEquals( $file, edd_set_requested_file_scheme( $https_file, array(), '' ) );
	}

	public function test_size_in_bytes() {
		$this->assertEquals( 1024, edd_determine_size_in_bytes( '1K' ) );
		$this->assertEquals( 1024, edd_determine_size_in_bytes( '1k' ) );
		$this->assertEquals( 1024 * 2, edd_determine_size_in_bytes( '2k' ) );


		$this->assertEquals( 1048576, edd_determine_size_in_bytes( '1M' ) );
		$this->assertEquals( 1048576, edd_determine_size_in_bytes( '1m' ) );
		$this->assertEquals( 1048576 * 2, edd_determine_size_in_bytes( '2m' ) );

		$this->assertEquals( 1073741824, edd_determine_size_in_bytes( '1G' ) );
		$this->assertEquals( 1073741824, edd_determine_size_in_bytes( '1g' ) );
		$this->assertEquals( 1073741824 * 2, edd_determine_size_in_bytes( '2g' ) );

		$this->assertEquals( 1024, edd_determine_size_in_bytes( '1024' ) );
		$this->assertEquals( 1024, edd_determine_size_in_bytes( 1024 ) );

		$this->assertEquals( false, edd_determine_size_in_bytes( 'test' ) );
		$this->assertEquals( false, edd_determine_size_in_bytes( '' ) );
		$this->assertEquals( 0, edd_determine_size_in_bytes( 0 ) );
	}
}
