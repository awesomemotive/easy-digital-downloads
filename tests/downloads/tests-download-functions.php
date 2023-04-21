<?php
namespace EDD\Tests\Downloads;

use EDD\Tests\Helpers;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * @group edd_downloads
 * @group edd_functions
 */
class Tests_Download_Functions extends EDD_UnitTestCase {

	protected $simple_download;

	protected $variable_download;

	public function setup(): void {
		parent::setUp();

		$this->simple_download   = Helpers\EDD_Helper_Download::create_simple_download();
		$this->variable_download = Helpers\EDD_Helper_Download::create_variable_download();
	}

	public function tearDown(): void {
		parent::tearDown();

		Helpers\EDD_Helper_Download::delete_download( $this->simple_download->ID );
		Helpers\EDD_Helper_Download::delete_download( $this->variable_download->ID );
	}

	/**
	 * @covers edd_get_download_name
	 */
	public function test_get_download_name_simple_download_returns_name() {
		$name = edd_get_download_name( $this->simple_download->ID );

		$this->assertSame( 'Test Download Product', $name );
	}

	/**
	 * @covers edd_get_download_name
	 */
	public function test_get_download_name_variable_download_returns_name() {
		$name = edd_get_download_name( $this->variable_download->ID, 0 );

		$this->assertSame( 'Variable Test Download Product — Simple', $name );
	}

	/**
	 * @covers edd_get_download_name
	 */
	public function test_get_download_name_invalid_download_returns_false() {
		$this->assertFalse( edd_get_download_name( 54657 ) );
	}

	/**
	 * @covers edd_get_download_name
	 */
	public function test_get_download_name_invalid_download_id_returns_false() {
		$this->assertFalse( edd_get_download_name( 'test' ) );
	}
}
