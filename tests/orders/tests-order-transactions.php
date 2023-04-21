<?php
/**
 * Order Transaction Tests.
 *
 * @group edd_orders
 * @group database
 *
 * @coversDefaultClass \EDD\Orders\Order_Transaction
 */
namespace EDD\Tests\Orders;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Order_Transaction_Tests extends EDD_UnitTestCase {

	/**
	 * Order transactions fixture.
	 *
	 * @var array
	 * @static
	 */
	protected static $order_transactions = array();

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$order_transactions = parent::edd()->order_transaction->create_many( 5 );
	}

	/**
	 * @covers ::edd_update_order_transaction
	 */
	public function test_update_should_return_true() {
		$success = edd_update_order_transaction( self::$order_transactions[0], array(
			'gateway' => 'stripe',
		) );

		$this->assertSame( 1, $success );
	}

	/**
	 * @covers ::edd_update_order_transaction
	 */
	public function test_order_object_after_update_should_return_true() {
		edd_update_order_transaction( self::$order_transactions[0], array(
			'gateway' => 'stripe',
		) );

		$order_transaction = edd_get_order_transaction( self::$order_transactions[0] );

		$this->assertSame( 'stripe', $order_transaction->gateway );
	}

	/**
	 * @covers ::edd_update_order_transaction
	 */
	public function test_update_without_id_should_fail() {
		$success = edd_update_order_transaction( null, array(
			'gateway' => 'stripe',
		) );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::edd_delete_order_transaction
	 */
	public function test_delete_should_return_true() {
		$success = edd_delete_order_transaction( self::$order_transactions[0] );

		$this->assertSame( 1, $success );
	}

	/**
	 * @covers ::edd_delete_order_transaction
	 */
	public function test_delete_without_id_should_fail() {
		$success = edd_delete_order_transaction( '' );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_number_should_return_true() {
		$orders = edd_get_order_transactions( array(
			'number' => 10,
		) );

		$this->assertCount( 5, $orders );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_offset_should_return_true() {
		$order_transactions = edd_get_order_transactions( array(
			'number' => 10,
			'offset' => 4,
		) );

		$this->assertCount( 1, $order_transactions );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_orderby_id_and_order_asc_should_return_true() {
		$order_transactions = edd_get_order_transactions( array(
			'orderby' => 'id',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_transactions[0]->id < $order_transactions[1]->id );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_orderby_id_and_order_desc_should_return_true() {
		$order_transactions = edd_get_order_transactions( array(
			'orderby' => 'id',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_transactions[0]->id > $order_transactions[1]->id );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_id__not_in_should_return_5() {
		$order_transactions = edd_get_order_transactions( array(
			'id__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_transactions );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_orderby_object_id_and_order_asc_should_return_true() {
		$order_transactions = edd_get_order_transactions( array(
			'orderby' => 'object_id',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_transactions[0]->object_id < $order_transactions[1]->object_id );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_orderby_object_id_and_order_desc_should_return_true() {
		$order_transactions = edd_get_order_transactions( array(
			'orderby' => 'object_id',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_transactions[0]->object_id > $order_transactions[1]->object_id );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_object_id__in_should_return_1() {
		$order_transactions = edd_get_order_transactions( array(
			'object_id__in' => array(
				\WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_transactions );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_object_id__not_in_should_return_5() {
		$order_transactions = edd_get_order_transactions( array(
			'object_id__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_transactions );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_orderby_object_type_and_order_asc_should_return_true() {
		$order_transactions = edd_get_order_transactions( array(
			'orderby' => 'object_type',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_transactions[0]->object_type < $order_transactions[1]->object_type );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_orderby_object_type_and_order_desc_should_return_true() {
		$order_transactions = edd_get_order_transactions( array(
			'orderby' => 'object_type',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_transactions[0]->object_type > $order_transactions[1]->object_type );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_object_type__in_should_return_1() {
		$order_transactions = edd_get_order_transactions( array(
			'object_type__in' => array(
				'order' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_transactions );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_object_type__not_in_should_return_5() {
		$order_transactions = edd_get_order_transactions( array(
			'object_type__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_transactions );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_orderby_transaction_id_and_order_asc_should_return_true() {
		$order_transactions = edd_get_order_transactions( array(
			'orderby' => 'transaction_id',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_transactions[0]->transaction_id < $order_transactions[1]->transaction_id );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_orderby_transaction_id_and_order_desc_should_return_true() {
		$order_transactions = edd_get_order_transactions( array(
			'orderby' => 'transaction_id',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_transactions[0]->transaction_id > $order_transactions[1]->transaction_id );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_transaction_id__in_should_return_1() {
		$order_transactions = edd_get_order_transactions( array(
			'transaction_id__in' => array(
				'transaction' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_transactions );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_transaction_id__not_in_should_return_5() {
		$order_transactions = edd_get_order_transactions( array(
			'transaction_id__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_transactions );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_orderby_gateway_and_order_asc_should_return_true() {
		$order_transactions = edd_get_order_transactions( array(
			'orderby' => 'gateway',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_transactions[0]->gateway < $order_transactions[1]->gateway );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_orderby_gateway_and_order_desc_should_return_true() {
		$order_transactions = edd_get_order_transactions( array(
			'orderby' => 'gateway',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_transactions[0]->gateway > $order_transactions[1]->gateway );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_gateway__in_should_return_1() {
		$order_transactions = edd_get_order_transactions( array(
			'gateway__in' => array(
				'gateway' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_transactions );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_gateway__not_in_should_return_5() {
		$order_transactions = edd_get_order_transactions( array(
			'gateway__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_transactions );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_orderby_status_and_order_asc_should_return_true() {
		$order_transactions = edd_get_order_transactions( array(
			'orderby' => 'status',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_transactions[0]->status < $order_transactions[1]->status );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_orderby_status_and_order_desc_should_return_true() {
		$order_transactions = edd_get_order_transactions( array(
			'orderby' => 'status',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_transactions[0]->status > $order_transactions[1]->status );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_status__in_should_return_1() {
		$order_transactions = edd_get_order_transactions( array(
			'status__in' => array(
				'status' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_transactions );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_status__not_in_should_return_5() {
		$order_transactions = edd_get_order_transactions( array(
			'status__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_transactions );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_orderby_total_and_order_asc_should_return_true() {
		$order_transactions = edd_get_order_transactions( array(
			'orderby' => 'total',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_transactions[0]->total < $order_transactions[1]->total );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_orderby_total_and_order_desc_should_return_true() {
		$order_transactions = edd_get_order_transactions( array(
			'orderby' => 'total',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_transactions[0]->total > $order_transactions[1]->total );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_total__in_should_return_1() {
		$order_transactions = edd_get_order_transactions( array(
			'total__in' => array(
				\WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_transactions );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_total__not_in_should_return_5() {
		$order_transactions = edd_get_order_transactions( array(
			'total__not_in' => array(
				-999,
			),
		) );

		$this->assertCount( 5, $order_transactions );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_invalid_id_should_return_0() {
		$transactions = edd_get_order_transactions( array(
			'id' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_invalid_object_id_should_return_0() {
		$transactions = edd_get_order_transactions( array(
			'object_id' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_invalid_object_type_should_return_0() {
		$transactions = edd_get_order_transactions( array(
			'object_type' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_invalid_transaction_id_should_return_0() {
		$transactions = edd_get_order_transactions( array(
			'transaction_id' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_invalid_gateway_should_return_0() {
		$transactions = edd_get_order_transactions( array(
			'gateway' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_invalid_status_should_return_0() {
		$transactions = edd_get_order_transactions( array(
			'status' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_invalid_total_should_return_0() {
		$transactions = edd_get_order_transactions( array(
			'total' => -999,
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_invalid_date_created_should_return_0() {
		$transactions = edd_get_order_transactions( array(
			'date_created' => '2250-01-01 23:59:59',
		) );

		$this->assertCount( 0, $transactions );
	}

	/**
	 * @covers ::edd_get_order_transactions
	 */
	public function test_get_order_transactions_with_invalid_date_modified_should_return_0() {
		$transactions = edd_get_order_transactions( array(
			'date_modified' => '2250-01-01 23:59:59',
		) );

		$this->assertCount( 0, $transactions );
	}
}
