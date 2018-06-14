<?php
namespace EDD\Orders;

/**
 * Order Item Tests.
 *
 * @group edd_orders
 * @group database
 *
 * @coversDefaultClass \EDD\Orders\Order_Item
 */
class Order_Item_Tests extends \EDD_UnitTestCase {

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
}