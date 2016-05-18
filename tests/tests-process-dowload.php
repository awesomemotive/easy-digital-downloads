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
}
