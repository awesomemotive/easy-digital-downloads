<?php
namespace EDD\Orders;

use Carbon\Carbon;

/**
 * Refund Tests.
 *
 * @group edd_orders
 * @group edd_refunds
 * @group database
 *
 * @coversDefaultClass \EDD\Orders\Order
 */
class Refunds_Tests extends \EDD_UnitTestCase {

	/**
	 * Orders fixture.
	 *
	 * @var array
	 * @static
	 */
	protected static $orders = array();

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$orders = parent::edd()->order->create_many( 5 );

		foreach ( self::$orders as $order ) {
			edd_add_order_adjustment( array(
				'object_type' => 'order',
				'object_id'   => $order,
				'type'        => 'discount',
				'description' => '5OFF',
				'subtotal'    => 0,
				'total'       => 5,
			) );
		}
	}

	/**
	 * @covers ::edd_is_order_refundable
	 */
	public function test_is_order_refundable_should_return_true() {
		$this->assertTrue( edd_is_order_refundable( self::$orders[0] ) );
	}

	/**
	 * @covers ::edd_get_order_total
	 */
	public function test_get_order_total_should_be_120() {
		$this->assertSame( 120.0, edd_get_order_total( self::$orders[0] ) );
	}

	/**
	 * @covers ::edd_get_order_item_total
	 */
	public function test_get_order_item_total_should_be_120() {
		$this->assertSame( 120.0, edd_get_order_item_total( array( self::$orders[0] ), 1 ) );
	}

	/**
	 * @covers ::edd_refund_order
	 */
	public function test_refund_order() {

		// Refund order entirely.
		$refunded_order = edd_refund_order( self::$orders[0] );

		// Check that a new order ID was returned.
		$this->assertGreaterThan( 0, $refunded_order );

		// Fetch refunded order.
		$o = edd_get_order( $refunded_order );

		// Check a valid Order object was returned.
		$this->assertInstanceOf( 'EDD\Orders\Order', $o );

		// Verify status.
		$this->assertSame( 'refunded', $o->status );

		// Verify type.
		$this->assertSame( 'refund', $o->type );

		// Verify total.
		$this->assertSame( -120.0, floatval( $o->total ) );
	}

	/**
	 * @covers ::edd_refund_order_item
	 */
	public function test_refund_order_item() {
		$order_items = edd_get_order( self::$orders[1] )->items;

		// Refund order item entirely.
		$refunded_order = edd_refund_order_item( $order_items[0]->id );

		// Fetch refunded order.
		$o = edd_get_order( $refunded_order );

		// Check a valid Order object was returned.
		$this->assertInstanceOf( 'EDD\Orders\Order', $o );

		// Verify status.
		$this->assertSame( 'partially_refunded', $o->status );

		// Verify type.
		$this->assertSame( 'refund', $o->type );

		// Verify total.
		$this->assertSame( -120.0, floatval( $o->total ) );
	}

	/**
	 * @covers ::edd_apply_order_credit
	 */
	public function test_apply_order_credit() {
		$refunded_order = edd_apply_order_credit( self::$orders[2], array(
			'subtotal' => 20,
			'total'    => 20,
		) );

		// Fetch refunded order.
		$o = edd_get_order( $refunded_order );

		// Check a valid Order object was returned.
		$this->assertInstanceOf( 'EDD\Orders\Order', $o );

		// Verify status.
		$this->assertSame( 'partially_refunded', $o->status );

		// Verify type.
		$this->assertSame( 'refund', $o->type );

		// Verify total.
		$this->assertSame( -20.0, floatval( $o->total ) );

		// Verify adjustments
		$this->assertCount( 1, $o->adjustments );

		$a = $o->adjustments;
		$a = $a[0];

		// Verify type.
		$this->assertSame( 'credit', $a->type );

		// Verify subtotal.
		$this->assertSame( 20.0, floatval( $a->subtotal ) );

		// Verify total.
		$this->assertSame( 20.0, floatval( $a->total ) );

		// Verify order total.
		$this->assertSame( 100.0, edd_get_order_total( self::$orders[2] ) );
	}

	/**
	 * @covers ::edd_apply_order_item_credit
	 */
	public function test_apply_order_item_credit() {
		$o = edd_get_order( self::$orders[2] );
		$i = $o->items;

		$refunded_order = edd_apply_order_item_credit( $i[0]->id, array(
			'subtotal' => 20,
			'total'    => 20,
		) );

		// Fetch refunded order.
		$o = edd_get_order( $refunded_order );

		// Check a valid Order object was returned.
		$this->assertInstanceOf( 'EDD\Orders\Order', $o );

		// Verify status.
		$this->assertSame( 'partially_refunded', $o->status );

		// Verify type.
		$this->assertSame( 'refund', $o->type );

		// Verify total.
		$this->assertSame( -20.0, floatval( $o->total ) );

		// Verify adjustments
		$this->assertCount( 1, $o->adjustments );

		$a = $o->adjustments;
		$a = $a[0];

		// Verify type.
		$this->assertSame( 'credit', $a->type );

		// Verify subtotal.
		$this->assertSame( 20.0, floatval( $a->subtotal ) );

		// Verify total.
		$this->assertSame( 20.0, floatval( $a->total ) );

		// Verify order total.
		$this->assertSame( 100.0, edd_get_order_total( self::$orders[2] ) );
	}

	/**
	 * @covers ::edd_apply_order_discount
	 */
	public function test_apply_order_discount() {
		$discount_id = self::edd()->discount->create( array(
			'name'   => '$5 Off',
			'code'   => '5OFF',
			'status' => 'active',
			'type'   => 'flat',
			'scope'  => 'global',
			'amount' => 5,
		) );

		$refunded_order = edd_apply_order_discount( self::$orders[3], $discount_id );

		$o = edd_get_order( $refunded_order );
	}

	/**
	 * @covers ::edd_get_refundability_types
	 */
	public function test_get_refundability_types() {
		$expected = array(
			'refundable'    => __( 'Refundable', 'easy-digital-downloads' ),
			'nonrefundable' => __( 'Non-Refundable', 'easy-digital-downloads' ),
		);

		$this->assertEqualSetsWithIndex( $expected, edd_get_refundability_types() );
	}

	/**
	 * @covers ::edd_get_refund_date()
	 */
	public function test_get_refund_date() {

		// Static date to ensure unit tests don't fail if this test runs for longer than 1 second.
		$date = '2010-01-01 00:00:00';

		$this->assertSame( Carbon::parse( $date )->addDays( 30 )->toDateTimeString(), edd_get_refund_date( $date ) );
	}
}