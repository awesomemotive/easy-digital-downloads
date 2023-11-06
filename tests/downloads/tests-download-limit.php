<?php

namespace EDD\Tests\Downloads;

use EDD\Tests\Helpers;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * @group edd_downloads
 * @group edd_functions
 */
class DownloadLimit extends EDD_UnitTestCase {

	/**
	 * @var WP_Post
	 */
	protected static $simple_download;

	protected static $download_no_limit;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		edd_update_option( 'file_download_limit', 2 );
		$simple_download = Helpers\EDD_Helper_Download::create_simple_download();
		self::$simple_download = edd_get_download( $simple_download->ID );

		$download_no_limit = Helpers\EDD_Helper_Download::create_simple_download();
		delete_post_meta( $download_no_limit->ID, '_edd_download_limit' );
		self::$download_no_limit = edd_get_download( $download_no_limit->ID );
	}

	public function test_download_limit_is_20() {
		$this->assertEquals( 20, self::$simple_download->file_download_limit );
	}

	public function test_get_download_limit_is_20() {
		$this->assertEquals( 20, self::$simple_download->get_file_download_limit() );
	}

	public function test_get_file_download_limit() {
		$this->assertEquals( 20, edd_get_file_download_limit( self::$simple_download->ID ) );
	}

	public function test_get_file_download_limit_override() {
		$this->assertEquals( 1, edd_get_file_download_limit_override( self::$simple_download->ID, 1 ) );
	}

	public function test_is_file_at_download_limit() {
		$this->assertFalse( edd_is_file_at_download_limit( self::$simple_download->ID, 1, 1 ) );
	}

	public function test_get_file_download_limit_setting_is_2() {
		$this->assertEquals( 2, self::$download_no_limit->get_file_download_limit() );
	}

	public function test_get_file_download_limit_setting_is_0() {
		edd_delete_option( 'file_download_limit' );
		$download = edd_get_download( self::$download_no_limit->ID );
		$this->assertEquals( 0, $download->get_file_download_limit() );
	}
}
