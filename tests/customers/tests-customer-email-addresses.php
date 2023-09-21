<?php
namespace EDD\Tests\Customers;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Customer Email Address Tests.
 *
 * @group edd_customers
 * @group database
 */
class Customer_Email_Address extends EDD_UnitTestCase {

	/**
	 * Customer email addresses fixture.
	 *
	 * @var array
	 * @static
	 */
	protected static $customer_email_addresses = array();

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$customer_email_addresses = parent::edd()->customer_email_address->create_many( 5 );
	}

	/**
	 * @covers ::edd_update_customer_email_address
	 */
	public function test_update_should_return_true() {
		$success = edd_update_customer_email_address( self::$customer_email_addresses[0], array(
			'email' => 'eddtest@edd.test',
		) );

		$this->assertSame( 1, $success );
	}

	/**
	 * @covers ::edd_update_customer_email_address
	 */
	public function test_email_address_object_after_update_should_return_true() {
		edd_update_customer_email_address( self::$customer_email_addresses[0], array(
			'email' => 'eddtest@edd.test',
		) );

		$customer_email_address = edd_get_customer_email_address( self::$customer_email_addresses[0] );

		$this->assertSame( 'eddtest@edd.test', $customer_email_address->email );
	}

	/**
	 * @covers ::edd_update_customer_email_address
	 */
	public function test_update_without_id_should_fail() {
		$success = edd_update_customer_email_address( null, array(
			'email' => 'eddtest@edd.test',
		) );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::edd_delete_order_transaction
	 */
	public function test_delete_should_return_true() {
		$success = edd_delete_customer_email_address( self::$customer_email_addresses[0] );

		$this->assertSame( 1, $success );
	}

	/**
	 * @covers ::edd_delete_order_transaction
	 */
	public function test_delete_without_id_should_fail() {
		$success = edd_delete_customer_email_address( '' );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_number_should_return_true() {
		$orders = edd_get_customer_email_addresses( array(
			'number' => 10,
		) );

		$this->assertCount( 5, $orders );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_offset_should_return_true() {
		$customer_email_addresses = edd_get_customer_email_addresses( array(
			'number' => 10,
			'offset' => 4,
		) );

		$this->assertCount( 1, $customer_email_addresses );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_orderby_id_and_order_asc_should_return_true() {
		$customer_email_addresses = edd_get_customer_email_addresses( array(
			'orderby' => 'id',
			'order'   => 'asc',
		) );

		$this->assertTrue( $customer_email_addresses[0]->id < $customer_email_addresses[1]->id );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_orderby_id_and_order_desc_should_return_true() {
		$customer_email_addresses = edd_get_customer_email_addresses( array(
			'orderby' => 'id',
			'order'   => 'desc',
		) );

		$this->assertTrue( $customer_email_addresses[0]->id > $customer_email_addresses[1]->id );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_id__not_in_should_return_5() {
		$customer_email_addresses = edd_get_customer_email_addresses( array(
			'id__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $customer_email_addresses );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_orderby_customer_id_and_order_asc_should_return_true() {
		$customer_email_addresses = edd_get_customer_email_addresses( array(
			'orderby' => 'customer_id',
			'order'   => 'asc',
		) );

		$this->assertTrue( $customer_email_addresses[0]->customer_id < $customer_email_addresses[1]->customer_id );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_orderby_customer_id_and_order_desc_should_return_true() {
		$customer_email_addresses = edd_get_customer_email_addresses( array(
			'orderby' => 'customer_id',
			'order'   => 'desc',
		) );

		$this->assertTrue( $customer_email_addresses[0]->customer_id > $customer_email_addresses[1]->customer_id );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_customer_id__in_should_return_1() {
		$customer_email_addresses = edd_get_customer_email_addresses( array(
			'customer_id__in' => array(
				\WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $customer_email_addresses );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_customer_id__not_in_should_return_5() {
		$customer_email_addresses = edd_get_customer_email_addresses( array(
			'customer_id__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $customer_email_addresses );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_orderby_type_and_order_asc_should_return_true() {
		$customer_email_addresses = edd_get_customer_email_addresses( array(
			'orderby' => 'type',
			'order'   => 'asc',
		) );

		$this->assertTrue( $customer_email_addresses[0]->type < $customer_email_addresses[1]->type );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_orderby_type_and_order_desc_should_return_true() {
		$customer_email_addresses = edd_get_customer_email_addresses( array(
			'orderby' => 'type',
			'order'   => 'desc',
		) );

		$this->assertTrue( $customer_email_addresses[0]->type > $customer_email_addresses[1]->type );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_type__in_should_return_1() {
		$customer_email_addresses = edd_get_customer_email_addresses( array(
			'type__in' => array(
				'type' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $customer_email_addresses );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_type__not_in_should_return_5() {
		$customer_email_addresses = edd_get_customer_email_addresses( array(
			'type__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $customer_email_addresses );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_orderby_status_and_order_asc_should_return_true() {
		$customer_email_addresses = edd_get_customer_email_addresses( array(
			'orderby' => 'status',
			'order'   => 'asc',
		) );

		$this->assertTrue( $customer_email_addresses[0]->status < $customer_email_addresses[1]->status );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_orderby_status_and_order_desc_should_return_true() {
		$customer_email_addresses = edd_get_customer_email_addresses( array(
			'orderby' => 'status',
			'order'   => 'desc',
		) );

		$this->assertTrue( $customer_email_addresses[0]->status > $customer_email_addresses[1]->status );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_status__in_should_return_1() {
		$customer_email_addresses = edd_get_customer_email_addresses( array(
			'status__in' => array(
				'status' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $customer_email_addresses );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_status__not_in_should_return_5() {
		$customer_email_addresses = edd_get_customer_email_addresses( array(
			'status__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $customer_email_addresses );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_orderby_email_and_order_asc_should_return_true() {
		$customer_email_addresses = edd_get_customer_email_addresses( array(
			'orderby' => 'email',
			'order'   => 'asc',
		) );

		$this->assertTrue( $customer_email_addresses[0]->email < $customer_email_addresses[1]->email );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_orderby_email_and_order_desc_should_return_true() {
		$customer_email_addresses = edd_get_customer_email_addresses( array(
			'orderby' => 'email',
			'order'   => 'desc',
		) );

		$this->assertTrue( $customer_email_addresses[0]->email > $customer_email_addresses[1]->email );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_email__in_should_return_1() {
		$customer_email_addresses = edd_get_customer_email_addresses( array(
			'email__in' => array(
				'user' . \WP_UnitTest_Generator_Sequence::$incr . '@edd.test',
			),
		) );

		$this->assertCount( 1, $customer_email_addresses );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_email__not_in_should_return_5() {
		$customer_email_addresses = edd_get_customer_email_addresses( array(
			'email__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $customer_email_addresses );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_invalid_id_should_return_0() {
		$transactions = edd_get_customer_email_addresses( array(
			'id' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_invalid_customer_id_should_return_0() {
		$transactions = edd_get_customer_email_addresses( array(
			'customer_id' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_invalid_type_should_return_0() {
		$transactions = edd_get_customer_email_addresses( array(
			'type' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_invalid_status_should_return_0() {
		$transactions = edd_get_customer_email_addresses( array(
			'status' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_invalid_email_should_return_0() {
		$transactions = edd_get_customer_email_addresses( array(
			'email' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_invalid_date_created_should_return_0() {
		$transactions = edd_get_customer_email_addresses( array(
			'date_created' => '2250-01-01 23:59:59',
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_customer_email_addresses
	 */
	public function test_get_customer_email_addresses_with_invalid_date_modified_should_return_0() {
		$transactions = edd_get_customer_email_addresses( array(
			'date_modified' => '2250-01-01 23:59:59',
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * This test covers a scenario where a customer exists but does not have an email address assigned.
	 * This could have happened during the migration, if a customer had no orders which could be migrated.
	 */
	public function test_get_customer_by_email_missing_email_returns_customer() {
		$customer_id = edd_add_customer(
			array(
				'name'  => 'Test Customer',
				'email' => 'missing@edd.test',
			)
		);

		// EDD does add the email, so we need to delete it to shim the missing email experience.
		$emails = edd_get_customer_email_addresses(
			array(
			'customer_id' => $customer_id,
			)
		);
		foreach ( $emails as $email ) {
			edd_delete_customer_email_address( $email->id );
		}
		$this->assertEmpty( edd_get_customer_email_addresses( array(
			'customer_id' => $customer_id,
		) ) );

		$customer = edd_get_customer_by( 'email', 'missing@edd.test' );
		$this->assertEquals( $customer_id, $customer->id );
		$this->assertSame( 'missing@edd.test', $customer->email );
	}
}
