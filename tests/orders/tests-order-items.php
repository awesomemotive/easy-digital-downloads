<?php
/**
 * Order Item Tests.
 *
 * @group edd_orders
 * @group database
 */
namespace EDD\Tests\Orders;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Helpers\EDD_Helper_Payment;

class OrderItem extends EDD_UnitTestCase {

	/**
	 * Order items fixture.
	 *
	 * @var array
	 * @static
	 */
	protected static $order_items = array();

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$order_items = parent::edd()->order_item->create_many( 5 );
	}

	/**
	 * @covers ::edd_update_order_item
	 */
	public function test_update_should_return_true() {
		$success = edd_update_order_item( self::$order_items[0], array(
			'product_name' => 'Stripe',
		) );

		$this->assertSame( 1, $success );
	}

	/**
	 * @covers ::edd_update_order_item
	 */
	public function test_order_object_after_update_should_return_true() {
		edd_update_order_item( self::$order_items[0], array(
			'product_name' => 'Stripe',
		) );

		$order_item = edd_get_order_item( self::$order_items[0] );

		$this->assertSame( 'Stripe', $order_item->product_name );
	}

	/**
	 * @covers ::edd_update_order_item
	 */
	public function test_update_without_id_should_fail() {
		$success = edd_update_order_item( null, array(
			'product_name' => 'Stripe',
		) );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::edd_delete_order_item
	 */
	public function test_delete_should_return_true() {
		$success = edd_delete_order_item( self::$order_items[0] );

		$this->assertSame( 1, $success );
	}

	/**
	 * @covers ::edd_delete_order_item
	 */
	public function test_delete_without_id_should_fail() {
		$success = edd_delete_order_item( '' );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_number_should_return_true() {
		$orders = edd_get_order_items( array(
			'number' => 10,
		) );

		$this->assertCount( 5, $orders );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_offset_should_return_true() {
		$orders = edd_get_order_items( array(
			'number' => 10,
			'offset' => 4,
		) );

		$this->assertCount( 1, $orders );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_orderby_id_and_order_asc_should_return_true() {
		$order_items = edd_get_order_items( array(
			'orderby' => 'id',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_items[0]->id < $order_items[1]->id );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_orderby_id_and_order_desc_should_return_true() {
		$order_items = edd_get_order_items( array(
			'orderby' => 'id',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_items[0]->id > $order_items[1]->id );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_orderby_order_id_and_order_asc_should_return_true() {
		$order_items = edd_get_order_items( array(
			'orderby' => 'order_id',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_items[0]->order_id < $order_items[1]->order_id );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_orderby_order_id_and_order_desc_should_return_true() {
		$order_items = edd_get_order_items( array(
			'orderby' => 'order_id',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_items[0]->order_id > $order_items[1]->order_id );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_order_id_should_return_1() {
		$order_items = edd_get_order_items( array(
			'order_id' => \WP_UnitTest_Generator_Sequence::$incr,
		) );

		$this->assertCount( 1, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_order_id__in_should_return_1() {
		$order_items = edd_get_order_items( array(
			'order_id__in' => array(
				\WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_order_id__not_in_should_return_5() {
		$order_items = edd_get_order_items( array(
			'order_id__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_orderby_product_id_and_order_asc_should_return_true() {
		$order_items = edd_get_order_items( array(
			'orderby' => 'product_id',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_items[0]->product_id < $order_items[1]->product_id );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_orderby_product_id_and_order_desc_should_return_true() {
		$order_items = edd_get_order_items( array(
			'orderby' => 'product_id',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_items[0]->product_id > $order_items[1]->product_id );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_product_id_should_return_1() {
		$order_items = edd_get_order_items( array(
			'product_id' => \WP_UnitTest_Generator_Sequence::$incr,
		) );

		$this->assertCount( 1, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_product_id__in_should_return_1() {
		$order_items = edd_get_order_items( array(
			'product_id__in' => array(
				\WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_product_id__not_in_should_return_5() {
		$order_items = edd_get_order_items( array(
			'product_id__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_product_name_should_return_5() {
		$order_items = edd_get_order_items( array(
			'product_name' => 'Test Product',
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_orderby_price_id_and_order_asc_should_return_true() {
		$order_items = edd_get_order_items( array(
			'orderby' => 'price_id',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_items[0]->price_id < $order_items[1]->price_id );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_orderby_price_id_and_order_desc_should_return_true() {
		$order_items = edd_get_order_items( array(
			'orderby' => 'price_id',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_items[0]->price_id > $order_items[1]->price_id );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_price_id_should_return_1() {
		$order_items = edd_get_order_items( array(
			'price_id' => \WP_UnitTest_Generator_Sequence::$incr,
		) );

		$this->assertCount( 1, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_price_id__in_should_return_1() {
		$order_items = edd_get_order_items( array(
			'price_id__in' => array(
				\WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_price_id__not_in_should_return_5() {
		$order_items = edd_get_order_items( array(
			'price_id__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_orderby_cart_index_and_order_asc_should_return_true() {
		$order_items = edd_get_order_items( array(
			'orderby' => 'cart_index',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_items[0]->cart_index < $order_items[1]->cart_index );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_orderby_cart_index_and_order_desc_should_return_true() {
		$order_items = edd_get_order_items( array(
			'orderby' => 'cart_index',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_items[0]->cart_index > $order_items[1]->cart_index );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_cart_index_should_return_1() {
		$order_items = edd_get_order_items( array(
			'cart_index' => \WP_UnitTest_Generator_Sequence::$incr,
		) );

		$this->assertCount( 1, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_cart_index__in_should_return_1() {
		$order_items = edd_get_order_items( array(
			'cart_index__in' => array(
				\WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_cart_index__not_in_should_return_5() {
		$order_items = edd_get_order_items( array(
			'cart_index__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_type_should_return_5() {
		$order_items = edd_get_order_items( array(
			'type' => 'download',
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_status_should_return_5() {
		$order_items = edd_get_order_items( array(
			'status' => 'inherit',
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_quantity_should_return_5() {
		$order_items = edd_get_order_items( array(
			'quantity' => 1,
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_quantity__in_should_return_5() {
		$order_items = edd_get_order_items( array(
			'quantity__in' => array(
				1,
			),
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_quantity__not_in_should_return_5() {
		$order_items = edd_get_order_items( array(
			'quantity__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_amount_should_return_5() {
		$order_items = edd_get_order_items( array(
			'amount' => 20,
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_amount__in_should_return_5() {
		$order_items = edd_get_order_items( array(
			'amount__in' => array(
				20,
			),
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_amount__not_in_should_return_5() {
		$order_items = edd_get_order_items( array(
			'amount__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_subtotal_should_return_5() {
		$order_items = edd_get_order_items( array(
			'subtotal' => 20,
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_subtotal__in_should_return_5() {
		$order_items = edd_get_order_items( array(
			'subtotal__in' => array(
				20,
			),
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_subtotal__not_in_should_return_5() {
		$order_items = edd_get_order_items( array(
			'subtotal__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_tax_should_return_5() {
		$order_items = edd_get_order_items( array(
			'tax' => 5,
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_tax__in_should_return_5() {
		$order_items = edd_get_order_items( array(
			'tax__in' => array(
				5,
			),
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_tax__not_in_should_return_5() {
		$order_items = edd_get_order_items( array(
			'tax__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_discount_should_return_5() {
		$order_items = edd_get_order_items( array(
			'discount' => 5,
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_discount__in_should_return_5() {
		$order_items = edd_get_order_items( array(
			'discount__in' => array(
				5,
			),
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_discount__not_in_should_return_5() {
		$order_items = edd_get_order_items( array(
			'discount__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_total_should_return_5() {
		$order_items = edd_get_order_items( array(
			'total' => 20,
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_total__in_should_return_5() {
		$order_items = edd_get_order_items( array(
			'total__in' => array(
				20,
			),
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_total__not_in_should_return_5() {
		$order_items = edd_get_order_items( array(
			'total__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_invalid_id_should_return_0() {
		$order_items = edd_get_order_items( array(
			'id' => 999,
		) );

		$this->assertCount( 0, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_invalid_order_id_should_return_0() {
		$order_items = edd_get_order_items( array(
			'order_id' => 999,
		) );

		$this->assertCount( 0, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_invalid_product_id_should_return_0() {
		$order_items = edd_get_order_items( array(
			'product_id' => 999,
		) );

		$this->assertCount( 0, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_invalid_product_name_should_return_0() {
		$order_items = edd_get_order_items( array(
			'product_name' => 'Invalid Product Name',
		) );

		$this->assertCount( 0, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_invalid_price_id_should_return_0() {
		$order_items = edd_get_order_items( array(
			'price_id' => 999,
		) );

		$this->assertCount( 0, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_invalid_cart_index_should_return_0() {
		$order_items = edd_get_order_items( array(
			'cart_index' => 999,
		) );

		$this->assertCount( 0, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_invalid_type_should_return_0() {
		$order_items = edd_get_order_items( array(
			'type' => 'invalid',
		) );

		$this->assertCount( 0, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_invalid_status_should_return_0() {
		$order_items = edd_get_order_items( array(
			'status' => 'invalid',
		) );

		$this->assertCount( 0, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_invalid_quantity_should_return_0() {
		$order_items = edd_get_order_items( array(
			'quantity' => 999,
		) );

		$this->assertCount( 0, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_invalid_amount_should_return_0() {
		$order_items = edd_get_order_items( array(
			'amount' => 999,
		) );

		$this->assertCount( 0, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_invalid_subtotal_should_return_0() {
		$order_items = edd_get_order_items( array(
			'subtotal' => -999,
		) );

		$this->assertCount( 0, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_invalid_discount_should_return_0() {
		$order_items = edd_get_order_items( array(
			'discount' => -999,
		) );

		$this->assertCount( 0, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_invalid_tax_should_return_0() {
		$order_items = edd_get_order_items( array(
			'tax' => -999,
		) );

		$this->assertCount( 0, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_invalid_total_should_return_0() {
		$order_items = edd_get_order_items( array(
			'total' => -999,
		) );

		$this->assertCount( 0, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_invalid_date_created_should_return_0() {
		$order_items = edd_get_order_items( array(
			'date_created' => '2250-01-01 23:59:59',
		) );

		$this->assertCount( 0, $order_items );
	}

	/**
	 * @covers ::edd_get_order_items
	 */
	public function test_get_order_items_with_invalid_date_modified_should_return_0() {
		$order_items = edd_get_order_items( array(
			'date_modified' => '2250-01-01 23:59:59',
		) );

		$this->assertCount( 0, $order_items );
	}

	/**
	 * @covers ::get_fees
	 */
	public function test_get_fees_should_be_empty() {
		$this->assertEmpty( edd_get_order_item( self::$order_items[0] )->get_fees() );
	}

	/**
	 * @covers ::is_deliverable
	 */
	public function test_order_item_marked_complete_is_deliverable_returns_true() {
		edd_update_order_item(
			self::$order_items[4],
			array(
				'status' => 'complete',
			)
		);

		$order_item = edd_get_order_item( self::$order_items[4] );

		$this->assertTrue( $order_item->is_deliverable() );
	}

	/**
	 * @covers ::is_deliverable
	 */
	public function test_order_item_marked_refunded_is_deliverable_returns_false() {
		edd_update_order_item(
			self::$order_items[4],
			array(
				'status' => 'refunded',
			)
		);

		$order_item = edd_get_order_item( self::$order_items[4] );

		$this->assertFalse( $order_item->is_deliverable() );
	}

	/**
	 * @covers edd_get_deliverable_order_item_statuses
	 */
	public function test_order_item_deliverable_statuses_includes_partially_refunded() {
		$this->assertTrue( in_array( 'partially_refunded', edd_get_deliverable_order_item_statuses(), true ) );
	}

	/**
	 * @covers ::is_deliverable
	 */
	public function test_order_item_marked_on_hold_is_deliverable_returns_false() {
		edd_update_order_item(
			self::$order_items[3],
			array(
				'status' => 'on_hold',
			)
		);

		$order_item = edd_get_order_item( self::$order_items[3] );

		$this->assertFalse( $order_item->is_deliverable() );
	}

	/**
	 * @covers ::get_net_total
	 */
	public function test_order_item_net_total_equal_to_total_before_refund() {
		$order_item     = edd_get_order_item( self::$order_items[0] );
		$expected_total = $order_item->total - $order_item->tax;

		$this->assertEquals( $expected_total, $order_item->get_net_total() );
	}

	/**
	 * @covers ::get_net_total
	 */
	public function test_order_item_net_total_not_equal_to_total_after_tax() {
		$order_id = EDD_Helper_Payment::create_simple_payment_with_tax();
		edd_update_order_status( $order_id, 'complete' );
		$order       = edd_get_order( $order_id );
		$order_items = $order->get_items();
		$order_item  = reset( $order_items );

		$this->assertNotEquals( $order_item->total, $order_item->get_net_total() );
	}

	/**
	 * @covers ::get_net_total
	 */
	public function test_order_item_net_total_equal_to_total_after_refund() {
		$order_id = EDD_Helper_Payment::create_simple_payment();
		edd_update_order_status( $order_id, 'complete' );
		$order       = edd_get_order( $order_id );
		$order_items = $order->get_items();
		$order_item  = reset( $order_items );

		$refund = edd_refund_order(
			$order_id,
			array(
				array(
					'order_item_id' => $order_item->id,
					'subtotal'      => $order_item->subtotal / 2,
					'tax'           => $order_item->tax,
					'total'         => $order_item->total / 2
				)
			)
		);

		$this->assertIsInt( $refund );
		$this->assertNotEquals( $order_item->total, $order_item->get_net_total() );
		$this->assertEquals( 10.00, $order_item->get_net_total() );
	}

	/**
	 * Tests that an order item in a refund, which will have a status of `complete` but a negative quantity, is not deliverable.
	 *
	 * @return void
	 */
	public function test_order_item_marked_complete_in_refund_is_deliverable_returns_false() {
		edd_update_order_item(
			self::$order_items[3],
			array(
				'status'   => 'complete',
				'quantity' => -1,
			)
		);

		$order_item = edd_get_order_item( self::$order_items[3] );

		$this->assertFalse( $order_item->is_deliverable() );
	}

	/**
	 */
	public function test_order_items_in_bundle_are_deliverable() {
		$order_id = EDD_Helper_Payment::create_simple_payment();
		edd_update_order_status( $order_id, 'complete' );
		$bundle = \EDD\Tests\Helpers\EDD_Helper_Download::create_bundled_download();
		edd_add_order_item(
			array(
				'order_id'    => $order_id,
				'product_id'  => $bundle->ID,
				'quantity'    => 1,
				'total'       => 9.99,
				'status'      => 'complete',
			)
		);
		$order = edd_get_order( $order_id );
		$items = $order->get_items();
		$this->assertCount( 3, $items );
		$items_with_bundles = $order->get_items_with_bundles();
		$this->assertCount( 4, $items_with_bundles );
		foreach ( $items_with_bundles as $item ) {
			$this->assertTrue( $item->is_deliverable() );
		}

	}
}
