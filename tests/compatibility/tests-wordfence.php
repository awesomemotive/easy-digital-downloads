<?php
/**
 * Handles tests for Wordfence compatibility.
 *
 * @group compatibility
 * @group plugins
 */

namespace EDD\Tests\Compatibility;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Compatibility\Loader;

class Wordfence extends EDD_UnitTestCase {
	/**
	 * Test if the Wordfence compatibility class is exists.
	 *
	 * @since 3.2.8
	 *
	 * @return void
	 */
	public function test_wordfence_compatibility_class_loaded() {
		$this->assertTrue( class_exists( 'EDD\Compatibility\Plugins\Wordfence' ) );
	}

	/**
	 * Test if the Wordfence compatibility class is a subclass of the base compatibility class.
	 *
	 * @since 3.2.8
	 *
	 * @return void
	 */
	public function test_wordfence_compatibility_class_is_subclass() {
		$this->assertTrue( is_subclass_of( 'EDD\Compatibility\Plugins\Wordfence', 'EDD\Compatibility\Plugins\Plugin' ) );
	}

	/**
	 * Test that without a Wordfence class, the compatibility class is not loaded.
	 *
	 * @since 3.2.8
	 */
	public function test_wordfence_compatibility_class_not_loaded() {
		Loader::load_plugin_compatibility();

		$this->assertTrue( array_key_exists( 'wordfence', Loader::get_loaded()['plugins'] ) );
		$this->assertFalse( Loader::get_loaded()['plugins']['wordfence'] );
	}

	public function test_wordfence_compatibility_class_is_loaded() {
		// Include the stub for the Wordfence class.
		require_once EDD_PLUGIN_DIR . 'tests/helpers/stubs/wordfence.php';

		Loader::load_plugin_compatibility();

		$this->assertTrue( array_key_exists( 'wordfence', Loader::get_loaded()['plugins'] ) );
		$this->assertTrue( Loader::get_loaded()['plugins']['wordfence'] );
	}
}
