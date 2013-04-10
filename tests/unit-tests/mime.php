<?php
/**
 * Test Mime Types
 */
class Test_Easy_Digital_Downloads_Mime extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function testAllowedMimeTypes() {
		$mime = get_allowed_mime_types();

		$this->assertArrayHasKey('zip', $mime);
		$this->assertArrayHasKey('epub', $mime);
		$this->assertArrayHasKey('mobi', $mime);
		$this->assertArrayHasKey('m4r', $mime);
		$this->assertArrayHasKey('psd', $mime);
		$this->assertArrayHasKey('exe', $mime);
		$this->assertArrayHasKey('apk', $mime);
		$this->assertArrayHasKey('msi', $mime);
	}
}