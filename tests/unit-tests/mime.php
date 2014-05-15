<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_mime
 */
class Tests_Mime extends EDD_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function testAllowedMimeTypes() {
		$mime = get_allowed_mime_types();

		$this->assertArrayHasKey( 'zip', $mime );
		$this->assertArrayHasKey( 'epub', $mime );
		$this->assertArrayHasKey( 'mobi', $mime );
		$this->assertArrayHasKey( 'aiff', $mime );
		$this->assertArrayHasKey( 'aif', $mime );
		$this->assertArrayHasKey( 'psd', $mime );
		$this->assertArrayHasKey( 'exe', $mime );
		$this->assertArrayHasKey( 'apk', $mime );
		$this->assertArrayHasKey( 'msi', $mime );
	}
}