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

}