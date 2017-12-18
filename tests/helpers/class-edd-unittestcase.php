<?php

class EDD_UnitTestCase extends WP_UnitTestCase {

	public static function wpSetUpBeforeClass() {
		edd_install();
	}

	public static function tearDownAfterClass() {
		
		self::_delete_all_data();

		return parent::tearDownAfterClass();
	}

	protected static function _delete_all_data() {
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
