<?php
/**
 * Handles tests for User Switching compatibility.
 *
 * @group compatibility
 * @group plugins
 */

namespace EDD\Tests\Compatibility;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Compatibility\Loader;

class UserSwitching extends EDD_UnitTestCase {

	/**
	 * Test if the UserSwitching compatibility class exists.
	 *
	 * @since 3.5.0
	 *
	 * @return void
	 */
	public function test_user_switching_compatibility_class_loaded() {
		$this->assertTrue( class_exists( 'EDD\Compatibility\Plugins\UserSwitching' ) );
	}

	/**
	 * Test if the UserSwitching compatibility class is a subclass of the base compatibility class.
	 *
	 * @since 3.5.0
	 *
	 * @return void
	 */
	public function test_user_switching_compatibility_class_is_subclass() {
		$this->assertTrue( is_subclass_of( 'EDD\Compatibility\Plugins\UserSwitching', 'EDD\Compatibility\Plugins\Plugin' ) );
	}

	/**
	 * Test that without the User Switching plugin functions, the compatibility class is not loaded.
	 *
	 * @since 3.5.0
	 */
	public function test_user_switching_compatibility_class_not_loaded() {
		Loader::load_compatibility_layers();

		$this->assertTrue( array_key_exists( 'user-switching', Loader::get_loaded()['plugins'] ) );
		$this->assertFalse( Loader::get_loaded()['plugins']['user-switching'] );
	}

	/**
	 * Test that with the User Switching plugin functions, the compatibility class is loaded.
	 *
	 * @since 3.5.0
	 */
	public function test_user_switching_compatibility_class_is_loaded() {
		// Include the stub for the User Switching plugin functions.
		require_once EDD_PLUGIN_DIR . 'tests/helpers/stubs/user-switching.php';

		Loader::load_compatibility_layers();

		$this->assertTrue( array_key_exists( 'user-switching', Loader::get_loaded()['plugins'] ) );
		$this->assertTrue( Loader::get_loaded()['plugins']['user-switching'] );
	}

	/**
	 * Test that the session is cleared when user switching hooks are fired.
	 *
	 * @since 3.5.0
	 */
	public function test_session_cleared_on_user_switch() {
		// Include the stub for the User Switching plugin functions.
		require_once EDD_PLUGIN_DIR . 'tests/helpers/stubs/user-switching.php';

		// Load compatibility layers to ensure UserSwitching class is loaded.
		Loader::load_compatibility_layers();

		// Set some session data.
		EDD()->session->set( 'test_key', 'test_value' );
		EDD()->session->set( 'customer', array( 'id' => 123 ) );

		$this->assertEquals( 'test_value', EDD()->session->get( 'test_key' ) );
		$this->assertNotEmpty( EDD()->session->get( 'customer' ) );

		// Trigger the user switch action.
		do_action( 'switch_to_user', 2, 1, '', '' );

		// Session should be cleared.
		$this->assertNull( EDD()->session->get( 'test_key' ) );
		$this->assertNull( EDD()->session->get( 'customer' ) );
	}

	/**
	 * Test that the session is cleared when switching back to a user.
	 *
	 * @since 3.5.0
	 */
	public function test_session_cleared_on_switch_back() {
		// Include the stub for the User Switching plugin functions.
		require_once EDD_PLUGIN_DIR . 'tests/helpers/stubs/user-switching.php';

		// Load compatibility layers.
		Loader::load_compatibility_layers();

		// Set some session data.
		EDD()->session->set( 'test_key', 'test_value' );

		$this->assertEquals( 'test_value', EDD()->session->get( 'test_key' ) );

		// Trigger the switch back action.
		do_action( 'switch_back_user', 1, 2, '', '' );

		// Session should be cleared.
		$this->assertNull( EDD()->session->get( 'test_key' ) );
	}

	/**
	 * Test that the session is cleared when switching off.
	 *
	 * @since 3.5.0
	 */
	public function test_session_cleared_on_switch_off() {
		// Include the stub for the User Switching plugin functions.
		require_once EDD_PLUGIN_DIR . 'tests/helpers/stubs/user-switching.php';

		// Load compatibility layers.
		Loader::load_compatibility_layers();

		// Set some session data.
		EDD()->session->set( 'test_key', 'test_value' );

		$this->assertEquals( 'test_value', EDD()->session->get( 'test_key' ) );

		// Trigger the switch off action.
		do_action( 'switch_off_user', 1, '' );

		// Session should be cleared.
		$this->assertNull( EDD()->session->get( 'test_key' ) );
	}
}
