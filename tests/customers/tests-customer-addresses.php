<?php
namespace EDD\Customers;

/**
 * Customer Address Tests.
 *
 * @group edd_customers
 * @group database
 *
 * @coversDefaultClass \EDD\Customers\Customer_Address
 */
class Customer_Address_Tests extends \EDD_UnitTestCase {

	/**
	 * Customer addresses fixture.
	 *
	 * @var array
	 * @static
	 */
	protected static $customer_addresses = array();

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$customer_addresses = parent::edd()->customer_address->create_many( 5 );
	}

	/**
	 * @covers ::edd_update_customer_address
	 */
	public function test_update_should_return_true() {
		$success = edd_update_customer_address( self::$customer_addresses[0], array(
			'address' => 'Address Line 1',
		) );

		$this->assertSame( 1, $success );
	}

	/**
	 * @covers ::edd_update_customer_address
	 */
	public function test_address_object_after_update_should_return_true() {
		edd_update_customer_address( self::$customer_addresses[0], array(
			'address' => 'Address Line 1',
		) );

		$customer_address = edd_fetch_customer_address( self::$customer_addresses[0] );

		$this->assertSame( 'Address Line 1', $customer_address->address );
	}

	/**
	 * @covers ::edd_update_customer_address
	 */
	public function test_update_without_id_should_fail() {
		$success = edd_update_customer_address( null, array(
			'email' => 'eddtest@edd.test',
		) );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::edd_delete_order_transaction
	 */
	public function test_delete_should_return_true() {
		$success = edd_delete_customer_address( self::$customer_addresses[0] );

		$this->assertSame( 1, $success );
	}

	/**
	 * @covers ::edd_delete_order_transaction
	 */
	public function test_delete_without_id_should_fail() {
		$success = edd_delete_customer_address( '' );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_number_should_return_true() {
		$orders = edd_get_customer_addresses( array(
			'number' => 10,
		) );

		$this->assertCount( 5, $orders );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_offset_should_return_true() {
		$customer_addresses = edd_get_customer_addresses( array(
			'number' => 10,
			'offset' => 4,
		) );

		$this->assertCount( 1, $customer_addresses );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_orderby_id_and_order_asc_should_return_true() {
		$customer_addresses = edd_get_customer_addresses( array(
			'orderby' => 'id',
			'order'   => 'asc',
		) );

		$this->assertTrue( $customer_addresses[0]->id < $customer_addresses[1]->id );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_orderby_id_and_order_desc_should_return_true() {
		$customer_addresses = edd_get_customer_addresses( array(
			'orderby' => 'id',
			'order'   => 'desc',
		) );

		$this->assertTrue( $customer_addresses[0]->id > $customer_addresses[1]->id );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_id__not_in_should_return_5() {
		$customer_addresses = edd_get_customer_addresses( array(
			'id__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $customer_addresses );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_orderby_customer_id_and_order_asc_should_return_true() {
		$customer_addresses = edd_get_customer_addresses( array(
			'orderby' => 'customer_id',
			'order'   => 'asc',
		) );

		$this->assertTrue( $customer_addresses[0]->customer_id < $customer_addresses[1]->customer_id );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_orderby_customer_id_and_order_desc_should_return_true() {
		$customer_addresses = edd_get_customer_addresses( array(
			'orderby' => 'customer_id',
			'order'   => 'desc',
		) );

		$this->assertTrue( $customer_addresses[0]->customer_id > $customer_addresses[1]->customer_id );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_customer_id__in_should_return_1() {
		$customer_addresses = edd_get_customer_addresses( array(
			'customer_id__in' => array(
				\WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $customer_addresses );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_customer_id__not_in_should_return_5() {
		$customer_addresses = edd_get_customer_addresses( array(
			'customer_id__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $customer_addresses );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_orderby_type_and_order_asc_should_return_true() {
		$customer_addresses = edd_get_customer_addresses( array(
			'orderby' => 'type',
			'order'   => 'asc',
		) );

		$this->assertTrue( $customer_addresses[0]->type < $customer_addresses[1]->type );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_orderby_type_and_order_desc_should_return_true() {
		$customer_addresses = edd_get_customer_addresses( array(
			'orderby' => 'type',
			'order'   => 'desc',
		) );

		$this->assertTrue( $customer_addresses[0]->type > $customer_addresses[1]->type );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_type__in_should_return_1() {
		$customer_addresses = edd_get_customer_addresses( array(
			'type__in' => array(
				'type' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $customer_addresses );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_type__not_in_should_return_5() {
		$customer_addresses = edd_get_customer_addresses( array(
			'type__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $customer_addresses );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_orderby_status_and_order_asc_should_return_true() {
		$customer_addresses = edd_get_customer_addresses( array(
			'orderby' => 'status',
			'order'   => 'asc',
		) );

		$this->assertTrue( $customer_addresses[0]->status < $customer_addresses[1]->status );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_orderby_status_and_order_desc_should_return_true() {
		$customer_addresses = edd_get_customer_addresses( array(
			'orderby' => 'status',
			'order'   => 'desc',
		) );

		$this->assertTrue( $customer_addresses[0]->status > $customer_addresses[1]->status );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_status__in_should_return_1() {
		$customer_addresses = edd_get_customer_addresses( array(
			'status__in' => array(
				'status' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $customer_addresses );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_status__not_in_should_return_5() {
		$customer_addresses = edd_get_customer_addresses( array(
			'status__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $customer_addresses );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_orderby_address_and_order_asc_should_return_true() {
		$customer_addresses = edd_get_customer_addresses( array(
			'orderby' => 'address',
			'order'   => 'asc',
		) );

		$this->assertTrue( $customer_addresses[0]->address < $customer_addresses[1]->address );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_orderby_address_and_order_desc_should_return_true() {
		$customer_addresses = edd_get_customer_addresses( array(
			'orderby' => 'address',
			'order'   => 'desc',
		) );

		$this->assertTrue( $customer_addresses[0]->address > $customer_addresses[1]->address );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_address__in_should_return_1() {
		$customer_addresses = edd_get_customer_addresses( array(
			'address__in' => array(
				'address' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $customer_addresses );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_address__not_in_should_return_5() {
		$customer_addresses = edd_get_customer_addresses( array(
			'address__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $customer_addresses );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_orderby_address2_and_order_asc_should_return_true() {
		$customer_addresses = edd_get_customer_addresses( array(
			'orderby' => 'address2',
			'order'   => 'asc',
		) );

		$this->assertTrue( $customer_addresses[0]->address2 < $customer_addresses[1]->address2 );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_orderby_address2_and_order_desc_should_return_true() {
		$customer_addresses = edd_get_customer_addresses( array(
			'orderby' => 'address2',
			'order'   => 'desc',
		) );

		$this->assertTrue( $customer_addresses[0]->address2 > $customer_addresses[1]->address2 );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_address2__in_should_return_1() {
		$customer_addresses = edd_get_customer_addresses( array(
			'address2__in' => array(
				'address2' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $customer_addresses );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_address2__not_in_should_return_5() {
		$customer_addresses = edd_get_customer_addresses( array(
			'address2__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $customer_addresses );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_orderby_city_and_order_asc_should_return_true() {
		$customer_addresses = edd_get_customer_addresses( array(
			'orderby' => 'city',
			'order'   => 'asc',
		) );

		$this->assertTrue( $customer_addresses[0]->city < $customer_addresses[1]->city );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_orderby_city_and_order_desc_should_return_true() {
		$customer_addresses = edd_get_customer_addresses( array(
			'orderby' => 'city',
			'order'   => 'desc',
		) );

		$this->assertTrue( $customer_addresses[0]->city > $customer_addresses[1]->city );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_city__in_should_return_1() {
		$customer_addresses = edd_get_customer_addresses( array(
			'city__in' => array(
				'city' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $customer_addresses );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_city__not_in_should_return_5() {
		$customer_addresses = edd_get_customer_addresses( array(
			'city__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $customer_addresses );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_orderby_region_and_order_asc_should_return_true() {
		$customer_addresses = edd_get_customer_addresses( array(
			'orderby' => 'region',
			'order'   => 'asc',
		) );

		$this->assertTrue( $customer_addresses[0]->region < $customer_addresses[1]->region );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_orderby_region_and_order_desc_should_return_true() {
		$customer_addresses = edd_get_customer_addresses( array(
			'orderby' => 'region',
			'order'   => 'desc',
		) );

		$this->assertTrue( $customer_addresses[0]->region > $customer_addresses[1]->region );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_region__in_should_return_1() {
		$customer_addresses = edd_get_customer_addresses( array(
			'region__in' => array(
				'region' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $customer_addresses );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_region__not_in_should_return_5() {
		$customer_addresses = edd_get_customer_addresses( array(
			'region__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $customer_addresses );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_orderby_postal_code_and_order_asc_should_return_true() {
		$customer_addresses = edd_get_customer_addresses( array(
			'orderby' => 'postal_code',
			'order'   => 'asc',
		) );

		$this->assertTrue( $customer_addresses[0]->postal_code < $customer_addresses[1]->postal_code );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_orderby_postal_code_and_order_desc_should_return_true() {
		$customer_addresses = edd_get_customer_addresses( array(
			'orderby' => 'postal_code',
			'order'   => 'desc',
		) );

		$this->assertTrue( $customer_addresses[0]->postal_code > $customer_addresses[1]->postal_code );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_postal_code__in_should_return_1() {
		$customer_addresses = edd_get_customer_addresses( array(
			'postal_code__in' => array(
				'postal_code' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $customer_addresses );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_postal_code__not_in_should_return_5() {
		$customer_addresses = edd_get_customer_addresses( array(
			'postal_code__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $customer_addresses );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_orderby_country_and_order_asc_should_return_true() {
		$customer_addresses = edd_get_customer_addresses( array(
			'orderby' => 'country',
			'order'   => 'asc',
		) );

		$this->assertTrue( $customer_addresses[0]->country < $customer_addresses[1]->country );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_orderby_country_and_order_desc_should_return_true() {
		$customer_addresses = edd_get_customer_addresses( array(
			'orderby' => 'country',
			'order'   => 'desc',
		) );

		$this->assertTrue( $customer_addresses[0]->country > $customer_addresses[1]->country );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_country__in_should_return_1() {
		$customer_addresses = edd_get_customer_addresses( array(
			'country__in' => array(
				'country' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $customer_addresses );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_country__not_in_should_return_5() {
		$customer_addresses = edd_get_customer_addresses( array(
			'country__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $customer_addresses );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_invalid_id_should_return_0() {
		$transactions = edd_get_customer_addresses( array(
			'id' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_invalid_customer_id_should_return_0() {
		$transactions = edd_get_customer_addresses( array(
			'customer_id' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_invalid_type_should_return_0() {
		$transactions = edd_get_customer_addresses( array(
			'type' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_invalid_status_should_return_0() {
		$transactions = edd_get_customer_addresses( array(
			'status' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_invalid_address_should_return_0() {
		$transactions = edd_get_customer_addresses( array(
			'address' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_invalid_address2_should_return_0() {
		$transactions = edd_get_customer_addresses( array(
			'address2' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_invalid_city_should_return_0() {
		$transactions = edd_get_customer_addresses( array(
			'city' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_invalid_region_should_return_0() {
		$transactions = edd_get_customer_addresses( array(
			'region' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_invalid_postal_code_should_return_0() {
		$transactions = edd_get_customer_addresses( array(
			'postal_code' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_invalid_country_should_return_0() {
		$transactions = edd_get_customer_addresses( array(
			'country' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_invalid_date_created_should_return_0() {
		$transactions = edd_get_customer_addresses( array(
			'date_created' => '2250-01-01 23:59:59',
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_customer_addresses
	 */
	public function test_get_customer_addresses_with_invalid_date_modified_should_return_0() {
		$transactions = edd_get_customer_addresses( array(
			'date_modified' => '2250-01-01 23:59:59',
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_demote_customer_primary_addresses
	 */
	public function test_adding_new_primary_customer_address_demotes_previous_primary() {
		$old_primary_address = parent::edd()->customer_address->create_and_get( array(
			'is_primary' => true
		) );

		$this->assertEquals( '1', $old_primary_address->is_primary );

		$number_primary_addresses = edd_count_customer_addresses( array(
			'is_primary'  => true,
			'customer_id' => $old_primary_address->customer_id
		) );

		$this->assertEquals( 1, $number_primary_addresses );

		// Add another primary address. Old one should be demoted.
		$new_primary_address = parent::edd()->customer_address->create_and_get( array(
			'is_primary'  => true,
			'customer_id' => $old_primary_address->customer_id
		) );

		$this->assertEquals( '1', $new_primary_address->is_primary );

		$number_primary_addresses = edd_count_customer_addresses( array(
			'is_primary'  => true,
			'customer_id' => $new_primary_address->customer_id
		) );

		$this->assertEquals( 1, $number_primary_addresses );

		// Re-fetch old address to ensure it's been demoted.
		$old_primary_address = parent::edd()->customer_address->get_object_by_id( $old_primary_address->id );
		$this->assertEquals( '0', $old_primary_address->is_primary );
	}

	/**
	 * @covers ::edd_demote_customer_primary_addresses
	 */
	public function test_adding_new_non_primary_customer_address_doesnt_demote_primary() {
		$primary_address = parent::edd()->customer_address->create_and_get( array(
			'is_primary' => true
		) );

		$this->assertEquals( '1', $primary_address->is_primary );

		// Add another, non-primary address.
		$non_primary_address = parent::edd()->customer_address->create_and_get( array(
			'is_primary'  => false,
			'customer_id' => $primary_address->customer_id
		) );

		$this->assertEquals( '0', $non_primary_address->is_primary );

		// Ensure primary address is still a primary.
		$primary_address = parent::edd()->customer_address->get_object_by_id( $primary_address->id );
		$this->assertEquals( '1', $primary_address->is_primary );
	}
}
