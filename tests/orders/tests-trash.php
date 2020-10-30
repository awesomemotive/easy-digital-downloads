<?php
namespace EDD\Orders;

use Carbon\Carbon;

/**
 * Trash Tests.
 *
 * @group edd_orders
 * @group edd_trash
 * @group database
 *
 * @coversDefaultClass \EDD\Orders\Order
 */
class Trash_Tests extends \EDD_UnitTestCase {

	/**
	 * Orders fixture.
	 *
	 * @var array
	 * @static
	 */
	protected static $orders = array();

	protected static $order_queries = null;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$order_queries = new \EDD\Database\Queries\Order();
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
	 * @covers ::edd_is_order_trashable
	 */
	public function test_is_order_trashable_should_return_true() {
		$this->assertTrue( edd_is_order_trashable( self::$orders[0] ) );
	}

	/**
	 * @covers ::edd_is_order_trashable
	 */
	public function test_is_order_trashable_should_return_false() {
		self::$order_queries->update_item( self::$orders[1], array( 'status' => 'trash' ) );
		$this->assertFalse( edd_is_order_trashable( self::$orders[1] ) );

		// Reset this order ID's status
		self::$order_queries->update_item( self::$orders[1], array( 'status' => 'complete' ) );
	}

	/**
	 * @covers ::edd_trash_order
	 */
	public function test_trash_order_should_return_true() {
		$order = edd_get_order( self::$orders[0] );
		$previous_status = $order->status;

		$this->assertTrue( edd_trash_order( self::$orders[0] ) );

		$order = edd_get_order( self::$orders[0] );
		$this->assertSame( 'trash', $order->status );

		// Update the status of any order to 'trashed'.
		$order_items = edd_get_order_items( array(
			'order_id'      => self::$orders[0],
			'no_found_rows' => true,
		) );

		foreach ( $order_items as $order_item ) {
			$this->assertSame( 'trash', $order_item->status );
		}

		$this->assertSame( $previous_status, edd_get_order_meta( self::$orders[0], '_pre_trash_status', true ) );
	}

	public function test_is_order_restorable_should_return_false() {
		$this->assertFalse( edd_is_order_restorable( self::$orders[0] ) );
	}

	public function test_is_order_restorable_should_return_true() {
		edd_trash_order( self::$orders[0] );
		$this->assertTrue( edd_is_order_restorable( self::$orders[0] ) );
	}

	public function test_restore_order() {
		$order = edd_get_order( self::$orders[0] );
		$previous_status = $order->status;

		edd_trash_order( self::$orders[0] );

		$this->assertTrue( edd_is_order_restorable( self::$orders[0] ) );

		$this->assertTrue( edd_restore_order( self::$orders[0] ) );
		$order = edd_get_order( self::$orders[0] );
		$this->assertSame( $previous_status, $order->status );
		$this->assertEmpty( edd_get_order_meta( self::$orders[0], '_pre_trash_status', true ) );
	}

	public function test_trash_order_with_refunds() {
		edd_refund_order( self::$orders[0] );
		$refunds = edd_get_orders( array( 'parent' => self::$orders[0] ) );

		$this->assertTrue( edd_is_order_trashable( self::$orders[0] ) );
		$this->assertTrue( edd_is_order_trashable( self::$orders[0] ) );

		edd_trash_order( self::$orders[0] );
		$order   = edd_get_order( self::$orders[0] );
		$refunds = edd_get_orders( array( 'parent' => self::$orders[0] ) );
		$this->assertSame( 'trash', $order->status );

		$this->assertFalse( edd_is_order_trashable( $refunds[0]->id ) );
		$this->assertSame( 'trash', $refunds[0]->status );
	}

}
