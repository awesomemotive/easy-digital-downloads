<?php
/**
 * General tests for the export system
 *
 * @group exports
 */

namespace EDD\Tests\Exports;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class General extends EDD_UnitTestCase {
	public function test_get_exports_dir() {
		$dir = edd_get_exports_dir();

		$this->assertStringEndsWith( 'exports', $dir );
		$this->assertStringStartsWith( WP_CONTENT_DIR, $dir );
		$this->assertStringContainsString( edd_get_upload_dir(), $dir );
	}
}
