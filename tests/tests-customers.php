<?php

/**
 * @group edd_customers
 */
class Tests_Customers extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_get_columns() {
		$columns = array(
			'id'             => '%d',
			'user_id'        => '%d',
			'name'           => '%s',
			'email'          => '%s',
			'payment_ids'    => '%s',
			'purchase_value' => '%s',
			'purchase_count' => '%d',
			'notes'          => '%s',
			'date_created'   => '%s',
		);

		$this->assertEquals( $columns, EDD()->customers->get_columns() );
	}
}