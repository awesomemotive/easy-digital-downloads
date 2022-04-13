<?php


/**
 * @group edd_languages
 */
class Tests_Languages extends EDD_UnitTestCase {

	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_pot_file_exists() {
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads.pot' ) );
	}

}
