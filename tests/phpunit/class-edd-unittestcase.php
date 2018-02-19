<?php

require_once dirname( __FILE__ ) . '/factory.php';

/**
 * Defines a basic fixture to run multiple tests.
 *
 * Resets the state of the WordPress installation before and after every test.
 *
 * Includes utility functions and assertions useful for testing WordPress.
 *
 * All WordPress unit tests should inherit from this class.
 */
class EDD_UnitTestCase extends WP_UnitTestCase {

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
		global $wpdb;

		foreach ( array(
			EDD()->customers->table_name,
			EDD()->customer_meta->table_name,
			EDD()->discounts->table_name,
			EDD()->discount_meta->table_name,
		) as $table ) {
			$wpdb->query( "DELETE FROM {$table}" );
		}
	}
}