<?php
namespace EDD\Tests;

require_once dirname( __FILE__ ) . '/factory.php';

/**
 * Defines a basic fixture to run AJAX tests.
 *
 * Includes utility functions and assertions useful for testing EDD AJAX functions/actions.
 *
 * All EDD AJAX unit tests should inherit from this class.
 */
class Ajax_UnitTestCase extends \WP_Ajax_UnitTestCase {

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		edd_install();

		global $current_user;

		$current_user = new WP_User(1);
		$current_user->set_role('administrator');
		wp_update_user( array( 'ID' => 1, 'first_name' => 'Admin', 'last_name' => 'User' ) );
		add_filter( 'edd_log_email_errors', '__return_false' );
	}

	public static function tearDownAfterClass() {
		self::_delete_all_edd_data();

		parent::tearDownAfterClass();
	}

	protected static function edd() {
		static $factory = null;
		if ( ! $factory ) {
			$factory = new EDD\Tests\Factory();
		}
		return $factory;
	}

	protected static function _delete_all_edd_data() {
		$components = EDD()->components;

		foreach ( $components as $component ) {
			$thing = $component->get_interface( 'table' );

			if ( $thing instanceof EDD_DB_Table ) {
				$thing->delete_all();
				$thing->truncate();
			}
		}
	}
}
