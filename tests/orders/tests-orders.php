<?php
namespace EDD\Orders;

/**
 * Order Tests.
 *
 * @group edd_orders
 * @group database
 *
 * @coversDefaultClass \EDD\Orders\Order
 */
class Orders_Tests extends \EDD_UnitTestCase {

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
	}

	public function test_update_should_return_true() {
		$success = edd_update_order( self::$orders[0], array(
			'gateway' => 'Stripe',
		) );

		$this->assertSame( 1, $success );
	}

	public function test_order_object_after_update_should_return_true() {
		edd_update_order( self::$orders[0], array(
			'gateway' => 'Stripe',
		) );

		$order = edd_get_order( self::$orders[0] );

		$this->assertSame( 'Stripe', $order->gateway );
	}

	public function test_update_without_id_should_fail() {
		$success = edd_update_order( null, array(
			'gateway' => 'Stripe',
		) );

		$this->assertFalse( $success );
	}

	public function test_delete_should_return_true() {
		$success = edd_delete_order( self::$orders[0] );

		$this->assertSame( 1, $success );
	}

	public function test_delete_without_id_should_fail() {
		$success = edd_delete_order( '' );

		$this->assertFalse( $success );
	}

	public function test_get_orders_with_number_should_return_true() {
		$orders = edd_get_orders( array(
			'number' => 10,
		) );

		$this->assertCount( 5, $orders );
	}

	public function test_get_orders_with_offset_should_return_true() {
		$orders = edd_get_orders( array(
			'number' => 10,
			'offset' => 4,
		) );

		$this->assertCount( 1, $orders );
	}
}