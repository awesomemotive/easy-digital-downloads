<?php
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * @group edd_languages
 */
class Tests_Languages extends EDD_UnitTestCase {

	public function test_pot_file_exists() {
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads.pot' ) );
	}

}
