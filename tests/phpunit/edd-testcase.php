<?php
namespace EDD\Tests;

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
class UnitTestCase extends \WP_UnitTestCase {

	function __get( $name ) {
		if ( 'factory' === $name ) {
			return self::edd();
		}
	}

	protected static function edd() {
		static $factory = null;
		if ( ! $factory ) {
			$factory = new Factory();
		}
		return $factory;
	}

	public static function tearDownAfterClass() {
		self::_delete_all_data();

		return parent::tearDownAfterClass();
	}

	protected static function _delete_all_data() {
		global $wpdb;

		foreach ( array(
			EDD()->customers->table_name,
			EDD()->customer_meta->table_name
		) as $table ) {
			$wpdb->query( "DELETE FROM {$table}" );
		}
	}

	/**
	 * Helper to flush the $wp_roles global.
	 */
	public static function _flush_roles() {
		/*
		 * We want to make sure we're testing against the db, not just in-memory data
		 * this will flush everything and reload it from the db
		 */
		unset( $GLOBALS['wp_user_roles'] );
		global $wp_roles;
		if ( is_object( $wp_roles ) ) {
			$wp_roles->_init();
		}
	}
}
