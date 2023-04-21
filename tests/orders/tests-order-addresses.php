<?php
/**
 * Order Address Tests.
 *
 * @group edd_orders
 * @group database
 *
 * @coversDefaultClass \EDD\Orders\Order_Address
 */
namespace EDD\Tests\Orders;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Order_Address_Tests extends EDD_UnitTestCase {

	/**
	 * Customer addresses fixture.
	 *
	 * @var array
	 * @static
	 */
	protected static $order_addresses = array();

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$order_addresses = parent::edd()->order_address->create_many( 5 );
	}

	/**
	 * @covers ::edd_update_order_address
	 */
	public function test_update_should_return_true() {
		$success = edd_update_order_address( self::$order_addresses[0], array(
			'address' => 'Address Line 1',
		) );

		$this->assertSame( 1, $success );
	}

	/**
	 * @covers ::edd_update_order_address
	 */
	public function test_address_object_after_update_should_return_true() {
		edd_update_order_address( self::$order_addresses[0], array(
			'address' => 'Address Line 1',
		) );

		$order_address = edd_get_order_address( self::$order_addresses[0] );

		$this->assertSame( 'Address Line 1', $order_address->address );
	}

	/**
	 * @covers ::edd_update_order_address
	 */
	public function test_update_without_id_should_fail() {
		$success = edd_update_order_address( null, array(
			'email' => 'eddtest@edd.test',
		) );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::edd_delete_order_transaction
	 */
	public function test_delete_should_return_true() {
		$success = edd_delete_order_address( self::$order_addresses[0] );

		$this->assertSame( 1, $success );
	}

	/**
	 * @covers ::edd_delete_order_transaction
	 */
	public function test_delete_without_id_should_fail() {
		$success = edd_delete_order_address( '' );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_number_should_return_true() {
		$orders = edd_get_order_addresses( array(
			'number' => 10,
		) );

		$this->assertCount( 5, $orders );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_offset_should_return_true() {
		$order_addresses = edd_get_order_addresses( array(
			'number' => 10,
			'offset' => 4,
		) );

		$this->assertCount( 1, $order_addresses );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_orderby_id_and_order_asc_should_return_true() {
		$order_addresses = edd_get_order_addresses( array(
			'orderby' => 'id',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_addresses[0]->id < $order_addresses[1]->id );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_orderby_id_and_order_desc_should_return_true() {
		$order_addresses = edd_get_order_addresses( array(
			'orderby' => 'id',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_addresses[0]->id > $order_addresses[1]->id );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_id__not_in_should_return_5() {
		$order_addresses = edd_get_order_addresses( array(
			'id__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_addresses );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_orderby_order_id_and_order_asc_should_return_true() {
		$order_addresses = edd_get_order_addresses( array(
			'orderby' => 'order_id',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_addresses[0]->order_id < $order_addresses[1]->order_id );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_orderby_order_id_and_order_desc_should_return_true() {
		$order_addresses = edd_get_order_addresses( array(
			'orderby' => 'order_id',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_addresses[0]->order_id > $order_addresses[1]->order_id );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_order_id__in_should_return_1() {
		$order_addresses = edd_get_order_addresses( array(
			'order_id__in' => array(
				\WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_addresses );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_order_id__not_in_should_return_5() {
		$order_addresses = edd_get_order_addresses( array(
			'order_id__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_addresses );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_orderby_name_and_order_asc_should_return_true() {
		$order_addresses = edd_get_order_addresses( array(
			'orderby' => 'name',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_addresses[0]->name < $order_addresses[1]->name );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_orderby_name_and_order_desc_should_return_true() {
		$order_addresses = edd_get_order_addresses( array(
			'orderby' => 'name',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_addresses[0]->name > $order_addresses[1]->name );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_name__in_should_return_1() {
		$order_addresses = edd_get_order_addresses( array(
			'name__in' => array(
				'name' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_addresses );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_name__not_in_should_return_5() {
		$order_addresses = edd_get_order_addresses( array(
			'name__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_addresses );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_orderby_address_and_order_asc_should_return_true() {
		$order_addresses = edd_get_order_addresses( array(
			'orderby' => 'address',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_addresses[0]->address < $order_addresses[1]->address );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_orderby_address_and_order_desc_should_return_true() {
		$order_addresses = edd_get_order_addresses( array(
			'orderby' => 'address',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_addresses[0]->address > $order_addresses[1]->address );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_address__in_should_return_1() {
		$order_addresses = edd_get_order_addresses( array(
			'address__in' => array(
				'address' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_addresses );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_address__not_in_should_return_5() {
		$order_addresses = edd_get_order_addresses( array(
			'address__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_addresses );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_orderby_address2_and_order_asc_should_return_true() {
		$order_addresses = edd_get_order_addresses( array(
			'orderby' => 'address2',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_addresses[0]->address2 < $order_addresses[1]->address2 );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_orderby_address2_and_order_desc_should_return_true() {
		$order_addresses = edd_get_order_addresses( array(
			'orderby' => 'address2',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_addresses[0]->address2 > $order_addresses[1]->address2 );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_address2__in_should_return_1() {
		$order_addresses = edd_get_order_addresses( array(
			'address2__in' => array(
				'address2' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_addresses );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_address2__not_in_should_return_5() {
		$order_addresses = edd_get_order_addresses( array(
			'address2__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_addresses );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_orderby_city_and_order_asc_should_return_true() {
		$order_addresses = edd_get_order_addresses( array(
			'orderby' => 'city',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_addresses[0]->city < $order_addresses[1]->city );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_orderby_city_and_order_desc_should_return_true() {
		$order_addresses = edd_get_order_addresses( array(
			'orderby' => 'city',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_addresses[0]->city > $order_addresses[1]->city );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_city__in_should_return_1() {
		$order_addresses = edd_get_order_addresses( array(
			'city__in' => array(
				'city' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_addresses );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_city__not_in_should_return_5() {
		$order_addresses = edd_get_order_addresses( array(
			'city__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_addresses );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_orderby_region_and_order_asc_should_return_true() {
		$order_addresses = edd_get_order_addresses( array(
			'orderby' => 'region',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_addresses[0]->region < $order_addresses[1]->region );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_orderby_region_and_order_desc_should_return_true() {
		$order_addresses = edd_get_order_addresses( array(
			'orderby' => 'region',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_addresses[0]->region > $order_addresses[1]->region );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_region__in_should_return_1() {
		$order_addresses = edd_get_order_addresses( array(
			'region__in' => array(
				'region' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_addresses );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_region__not_in_should_return_5() {
		$order_addresses = edd_get_order_addresses( array(
			'region__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_addresses );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_orderby_postal_code_and_order_asc_should_return_true() {
		$order_addresses = edd_get_order_addresses( array(
			'orderby' => 'postal_code',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_addresses[0]->postal_code < $order_addresses[1]->postal_code );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_orderby_postal_code_and_order_desc_should_return_true() {
		$order_addresses = edd_get_order_addresses( array(
			'orderby' => 'postal_code',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_addresses[0]->postal_code > $order_addresses[1]->postal_code );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_postal_code__in_should_return_1() {
		$order_addresses = edd_get_order_addresses( array(
			'postal_code__in' => array(
				'postal_code' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_addresses );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_postal_code__not_in_should_return_5() {
		$order_addresses = edd_get_order_addresses( array(
			'postal_code__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_addresses );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_orderby_country_and_order_asc_should_return_true() {
		$order_addresses = edd_get_order_addresses( array(
			'orderby' => 'country',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_addresses[0]->country < $order_addresses[1]->country );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_orderby_country_and_order_desc_should_return_true() {
		$order_addresses = edd_get_order_addresses( array(
			'orderby' => 'country',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_addresses[0]->country > $order_addresses[1]->country );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_country__in_should_return_1() {
		$order_addresses = edd_get_order_addresses( array(
			'country__in' => array(
				'country' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_addresses );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_country__not_in_should_return_5() {
		$order_addresses = edd_get_order_addresses( array(
			'country__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_addresses );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_invalid_id_should_return_0() {
		$transactions = edd_get_order_addresses( array(
			'id' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_invalid_order_id_should_return_0() {
		$transactions = edd_get_order_addresses( array(
			'order_id' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_invalid_name_should_return_0() {
		$transactions = edd_get_order_addresses( array(
			'name' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_invalid_address_should_return_0() {
		$transactions = edd_get_order_addresses( array(
			'address' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_invalid_address2_should_return_0() {
		$transactions = edd_get_order_addresses( array(
			'address2' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_invalid_city_should_return_0() {
		$transactions = edd_get_order_addresses( array(
			'city' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_invalid_region_should_return_0() {
		$transactions = edd_get_order_addresses( array(
			'region' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_invalid_postal_code_should_return_0() {
		$transactions = edd_get_order_addresses( array(
			'postal_code' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_invalid_country_should_return_0() {
		$transactions = edd_get_order_addresses( array(
			'country' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_invalid_date_created_should_return_0() {
		$transactions = edd_get_order_addresses( array(
			'date_created' => '2250-01-01 23:59:59',
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_order_addresses
	 */
	public function test_get_order_addresses_with_invalid_date_modified_should_return_0() {
		$transactions = edd_get_order_addresses( array(
			'date_modified' => '2250-01-01 23:59:59',
		) );

		$this->assertCount( 0, $transactions );
	}

	public function test_add_order_address_identical_should_return_false() {
		$order_address = edd_get_order_address( self::$order_addresses[0] );
		$data          = (array) $order_address;
		unset( $data['id'] );

		$this->assertFalse( edd_add_order_address( $data ) );
	}

	public function test_add_order_address_different_should_update_address() {
		$order_address = edd_get_order_address( self::$order_addresses[0] );
		$data          = array(
			'order_id' => $order_address->order_id,
			'city'     => 'A Totally New City',
		);

		$new_order_address_id = edd_add_order_address( $data );
		$this->assertFalse( $new_order_address_id );

		$new_order_address = edd_get_order_address( self::$order_addresses[0] );
		$this->assertSame( 'A Totally New City', $new_order_address->city );
	}
}
