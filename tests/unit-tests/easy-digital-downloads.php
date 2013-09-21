<?php
namespace EDD_Unit_Tests;

class Tests_EDD extends EDD_UnitTestCase {
	protected $object;

	public function setUp() {
		parent::setUp();
		$this->object = EDD();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_edd_instance() {
		$this->assertClassHasStaticAttribute( 'instance', 'Easy_Digital_Downloads' );
	}

	/**
	 * @covers Easy_Digital_Downloads::setup_constants
	 */
	public function test_constants() {
		// Plugin Folder URL
		$path = str_replace( 'tests/unit-tests/', '', plugin_dir_url( __FILE__ ) );
		$this->assertSame( EDD_PLUGIN_URL, $path );

		// Plugin Folder Path
		$path = str_replace( 'tests/unit-tests/', '', plugin_dir_path( __FILE__ ) );
		$this->assertSame( EDD_PLUGIN_DIR, $path );

		// Plugin Root File
		$path = str_replace( 'tests/unit-tests/', '', plugin_dir_path( __FILE__ ) );
		$this->assertSame( EDD_PLUGIN_FILE, $path.'easy-digital-downloads.php' );
	}


}
