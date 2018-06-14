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
}