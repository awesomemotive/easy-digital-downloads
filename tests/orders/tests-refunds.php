<?php
namespace EDD\Orders;

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
}