<?php
namespace EDD\Tests;

use EDD\Tests\Factory;

require_once dirname( __FILE__ ) . '/factory.php';

use Yoast\WPTestUtils\WPIntegration\TestCase as BaseTestCase;

/**
 * Defines a basic fixture to run AJAX tests.
 *
 * Includes utility functions and assertions useful for testing EDD AJAX functions/actions.
 *
 * All EDD AJAX unit tests should inherit from this class.
 */
class Ajax_UnitTestCase extends BaseTestCase {

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		edd_install();

		global $current_user;

		$current_user = new \WP_User( 1 );
		$current_user->set_role( 'administrator' );
		wp_update_user( array( 'ID' => 1, 'first_name' => 'Admin', 'last_name' => 'User' ) );
		add_filter( 'edd_log_email_errors', '__return_false' );
	}

	public static function tearDownAfterClass(): void {
		self::_delete_all_edd_data();

		parent::tearDownAfterClass();
	}

	protected static function edd() {
		static $factory = null;
		if ( ! $factory ) {
			$factory = new Factory();
		}
		return $factory;
	}

	protected static function _delete_all_edd_data() {
		$components = EDD()->components;

		foreach ( $components as $component ) {
			$thing = $component->get_interface( 'table' );

			if ( $thing instanceof \EDD\Database\Table ) {
				$thing->truncate();
			}
		}
	}
}
