<?php
/**
 * Order Tests.
 */
namespace EDD\Tests\Orders;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Orders extends EDD_UnitTestCase {

	/**
	 * Orders fixture.
	 *
	 * @var int[]
	 * @static
	 */
	protected static $orders = array();

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$orders = parent::edd()->order->create_many( 5 );
	}

	/**
	 * @covers ::edd_update_order
	 */
	public function test_update_should_return_true() {
		$success = edd_update_order( self::$orders[0], array(
			'gateway' => 'Stripe',
		) );

		$this->assertSame( 1, $success );
	}

	/**
	 * @covers ::edd_update_order
	 */
	public function test_order_object_after_update_should_return_true() {
		edd_update_order( self::$orders[0], array(
			'gateway' => 'Stripe',
		) );

		$order = edd_get_order( self::$orders[0] );

		$this->assertSame( 'Stripe', $order->gateway );
	}

	/**
	 * @covers ::edd_update_order
	 */
	public function test_update_without_id_should_fail() {
		$success = edd_update_order( null, array(
			'gateway' => 'Stripe',
		) );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::edd_delete_order
	 */
	public function test_delete_should_return_true() {
		$success = edd_delete_order( self::$orders[0] );

		$this->assertSame( 1, $success );
	}

	/**
	 * @covers ::edd_delete_order
	 */
	public function test_delete_should_delete_metadata() {
		edd_add_order_meta( self::$orders[1], 'test_meta_key', 'test_meta_value', true );

		// This assertion is added to ensure that metadata was, in fact, added to the order.
		$this->assertEquals( 'test_meta_value', edd_get_order_meta( self::$orders[1], 'test_meta_key', true ) );

		edd_delete_order( self::$orders[1] );
		$this->assertEmpty( edd_get_order_meta( self::$orders[1], 'test_meta_key', true ) );
	}

	/**
	 * @covers ::edd_delete_order
	 */
	public function test_delete_should_delete_metadata_non_unique() {
		edd_add_order_meta( self::$orders[1], 'test_meta_key', '2', false );
		edd_add_order_meta( self::$orders[1], 'test_meta_key', '1', false );

		// This assertion is added to ensure that metadata was, in fact, added to the order.
		$this->assertEquals( array( 2, 1 ), edd_get_order_meta( self::$orders[1], 'test_meta_key', false ) );

		edd_delete_order( self::$orders[1] );
		$this->assertEmpty( edd_get_order_meta( self::$orders[1], 'test_meta_key', false ) );
	}

	/**
	 * @covers ::edd_delete_order
	 */
	public function test_delete_without_id_should_fail() {
		$success = edd_delete_order( '' );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_number_should_return_true() {
		$orders = edd_get_orders( array(
			'number' => 10,
		) );

		$this->assertCount( 5, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_offset_should_return_true() {
		$orders = edd_get_orders( array(
			'number' => 10,
			'offset' => 4,
		) );

		$this->assertCount( 1, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_orderby_id_and_order_asc_should_return_true() {
		$orders = edd_get_orders( array(
			'orderby' => 'id',
			'order'   => 'asc',
		) );

		$this->assertTrue( $orders[0]->id < $orders[1]->id );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_orderby_id_and_order_desc_should_return_true() {
		$orders = edd_get_orders( array(
			'orderby' => 'id',
			'order'   => 'desc',
		) );

		$this->assertTrue( $orders[0]->id > $orders[1]->id );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_orderby_user_id_and_order_asc_should_return_true() {
		$orders = edd_get_orders( array(
			'orderby' => 'user_id',
			'order'   => 'asc',
		) );

		$this->assertTrue( $orders[0]->user_id < $orders[1]->user_id );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_user_id_should_return_1() {
		$orders = edd_get_orders( array(
			'user_id' => \WP_UnitTest_Generator_Sequence::$incr,
		) );

		$this->assertCount( 1, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_user_id__in_should_return_1() {
		$orders = edd_get_orders( array(
			'user_id__in' => array(
				\WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_user_id__not_in_should_return_5() {
		$orders = edd_get_orders( array(
			'user_id__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_orderby_user_id_and_order_desc_should_return_true() {
		$orders = edd_get_orders( array(
			'orderby' => 'user_id',
			'order'   => 'desc',
		) );

		$this->assertTrue( $orders[0]->user_id > $orders[1]->user_id );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_orderby_customer_id_and_order_asc_should_return_true() {
		$orders = edd_get_orders( array(
			'orderby' => 'customer_id',
			'order'   => 'asc',
		) );

		$this->assertTrue( $orders[0]->customer_id < $orders[1]->customer_id );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_orderby_customer_id_and_order_desc_should_return_true() {
		$orders = edd_get_orders( array(
			'orderby' => 'customer_id',
			'order'   => 'desc',
		) );

		$this->assertTrue( $orders[0]->customer_id > $orders[1]->customer_id );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_customer_id__in_should_return_1() {
		$orders = edd_get_orders( array(
			'customer_id__in' => array(
				\WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_customer_id__not_in_should_return_5() {
		$orders = edd_get_orders( array(
			'customer_id__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_orderby_email_and_order_asc_should_return_true() {
		$orders = edd_get_orders( array(
			'orderby' => 'email',
			'order'   => 'asc',
		) );

		$this->assertTrue( $orders[0]->email < $orders[1]->email );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_orderby_email_and_order_desc_should_return_true() {
		$orders = edd_get_orders( array(
			'orderby' => 'email',
			'order'   => 'desc',
		) );

		$this->assertTrue( $orders[0]->email > $orders[1]->email );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_email__in_should_return_1() {
		$orders = edd_get_orders( array(
			'email__in' => array(
				'user' . \WP_UnitTest_Generator_Sequence::$incr . '@edd.test',
			),
		) );

		$this->assertCount( 1, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_email__not_in_should_return_5() {
		$orders = edd_get_orders( array(
			'email__not_in' => array(
				'user999@edd.test',
			),
		) );

		$this->assertCount( 5, $orders );
	}

	/**
	 * @covers \EDD\Database\Queries\Order::query_by_product
	 */
	public function test_get_orders_with_product_price_id_should_return_1() {
		$items = edd_get_order_items( array( 'order_id' => self::$orders[0] ) );
		foreach( $items as $item ) {
			edd_update_order_item( $item->id, array(
				'price_id' => 2
			) );
		}

		$orders = edd_count_orders( array(
			'product_price_id' => 2
		) );

		$this->assertSame( 1, $orders );
	}

	/**
	 * @covers \EDD\Database\Queries\Order::query_by_product
	 */
	public function test_get_orders_with_product_price_id_0_should_return_1() {
		$items = edd_get_order_items( array( 'order_id' => self::$orders[0] ) );
		foreach( $items as $item ) {
			edd_update_order_item( $item->id, array(
				'price_id' => 0
			) );
		}

		$orders = edd_get_orders( array(
			'product_price_id' => 0
		) );

		$this->assertCount( 1, $orders );
	}

	/**
	 * @covers \EDD\Database\Queries\Order::query_by_product
	 */
	public function get_get_orders_with_no_product_price_id_should_return_5() {
		// Make sure all order items have price_id `null`.
		$items = edd_get_order_items( array( 'order_id__in' => self::$orders ) );
		foreach( $items as $item ) {
			edd_update_order_item( $item->id, array(
				'price_id' => null
			) );
		}

		// Now set one to `1`.
		$items = edd_get_order_items( array( 'order_id' => self::$orders[0] ) );
		foreach( $items as $item ) {
			edd_update_order_item( $item->id, array(
				'price_id' => 1
			) );
		}

		// There should be 4 records at `null`, and 1 at `1`.
		$this->assertSame( 4, edd_count_orders( array( 'product_price_id' => null ) ) );
		$this->assertSame( 1, edd_count_orders( array( 'product_price_id' => 1 ) ) );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_invalid_id_should_return_0() {
		$orders = edd_get_orders( array(
			'id' => 999,
		) );

		$this->assertCount( 0, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_invalid_parent_should_return_0() {
		$orders = edd_get_orders( array(
			'parent' => 999,
		) );

		$this->assertCount( 0, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_invalid_status_should_return_0() {
		$orders = edd_get_orders( array(
			'status' => 'invalid_status',
		) );

		$this->assertCount( 0, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_invalid_date_created_should_return_0() {
		$orders = edd_get_orders( array(
			'date_created' => '2250-01-01 23:59:59',
		) );

		$this->assertCount( 0, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_invalid_date_modified_should_return_0() {
		$orders = edd_get_orders( array(
			'date_modified' => '2250-01-01 23:59:59',
		) );

		$this->assertCount( 0, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_invalid_date_completed_should_return_0() {
		$orders = edd_get_orders( array(
			'date_completed' => '2250-01-01 23:59:59',
		) );

		$this->assertCount( 0, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_invalid_date_refundable_should_return_0() {
		$orders = edd_get_orders( array(
			'date_refundable' => '2250-01-01 23:59:59',
		) );

		$this->assertCount( 0, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_invalid_user_id_should_return_0() {
		$orders = edd_get_orders( array(
			'user_id' => 999,
		) );

		$this->assertCount( 0, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_invalid_customer_id_should_return_0() {
		$orders = edd_get_orders( array(
			'customer_id' => 999,
		) );

		$this->assertCount( 0, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_invalid_email_should_return_0() {
		$orders = edd_get_orders( array(
			'email' => 'invalid_email@domain.test',
		) );

		$this->assertCount( 0, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_invalid_ip_should_return_0() {
		$orders = edd_get_orders( array(
			'ip' => '255.255.255.255',
		) );

		$this->assertCount( 0, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_invalid_gateway_should_return_0() {
		$orders = edd_get_orders( array(
			'gateway' => 'invalid',
		) );

		$this->assertCount( 0, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_invalid_mode_should_return_0() {
		$orders = edd_get_orders( array(
			'mode' => 'invalid',
		) );

		$this->assertCount( 0, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_invalid_currency_should_return_0() {
		$orders = edd_get_orders( array(
			'currency' => 'ABC',
		) );

		$this->assertCount( 0, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_invalid_payment_key_should_return_0() {
		$orders = edd_get_orders( array(
			'payment_key' => 'INVALID',
		) );

		$this->assertCount( 0, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_invalid_subtotal_should_return_0() {
		$orders = edd_get_orders( array(
			'subtotal' => -999,
		) );

		$this->assertCount( 0, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_invalid_tax_should_return_0() {
		$orders = edd_get_orders( array(
			'tax' => -999,
		) );

		$this->assertCount( 0, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_invalid_discount_should_return_0() {
		$orders = edd_get_orders( array(
			'discount' => -999,
		) );

		$this->assertCount( 0, $orders );
	}

	/**
	 * @covers ::edd_get_orders
	 */
	public function test_get_orders_with_invalid_total_should_return_0() {
		$orders = edd_get_orders( array(
			'total' => -999,
		) );

		$this->assertCount( 0, $orders );
	}

	/**
	 * @covers ::is_complete
	 */
	public function test_is_order_complete_should_return_true() {
		$this->assertTrue( edd_get_order( self::$orders[0] )->is_complete() );
	}

	/**
	 * @covers ::get_number
	 */
	public function test_order_number_should_be_id_and_return_true() {
		$this->assertSame( self::$orders[0], (int) edd_get_order( self::$orders[0] )->id );
	}

	/**
	 * @covers ::__get
	 */
	public function test_order_object_magic_getter_for_address_should_return_true() {
		foreach ( self::$orders as $order ) {
			$o = edd_get_order( $order );

			$this->assertNotEmpty( $o->address );
			$this->assertInstanceOf( 'EDD\Orders\Order_Address', $o->address );
		}
	}

	/**
	 * @covers ::__get
	 */
	public function test_order_object_magic_getter_for_adjustments_should_return_true() {
		foreach ( self::$orders as $order ) {
			$o = edd_get_order( $order );

			$this->assertNotEmpty( $o->adjustments );

			foreach ( $o->adjustments as $adjustment ) {
				$this->assertInstanceOf( 'EDD\Orders\Order_Adjustment', $adjustment );
			}
		}
	}

	public function test_order_object_magic_getter_for_items_should_return_true() {
		foreach ( self::$orders as $order ) {
			$o = edd_get_order( $order );

			$this->assertNotEmpty( $o->items );

			foreach ( $o->items as $item ) {
				$this->assertInstanceOf( 'EDD\Orders\Order_Item', $item );
			}
		}
	}

	public function test_order_recalculate_returns_same_total() {
		$order = edd_get_order( self::$orders[0] );
		$order->recalculate();

		$updated = edd_get_order( $order->id );

		$this->assertSame( $order->total, $updated->total );
	}

	public function test_order_with_large_negative_fee_total_is_zero() {
		$order = edd_get_order( self::$orders[4] );
		edd_add_order_adjustment(
			array(
				'object_id'   => $order->id,
				'object_type' => 'order',
				'label'       => 'Negative Fee',
				'subtotal'    => -1000,
				'tax'         => 0,
				'total'       => -1000,
				'type'        => 'fee',
			)
		);
		$order->recalculate();
		$updated = edd_get_order( $order->id );

		$this->assertSame( '0.000000000', $updated->total );
	}
}
