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
	 * Order fixture.
	 *
	 * @var int
	 * @static
	 */
	protected static $order = 0;

	/**
	 * Order item fixture.
	 *
	 * @var int
	 * @static
	 */
	protected static $order_item = 0;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$order = parent::edd()->order->create( array(
			'status'          => 'publish',
			'type'            => 'order',
			'date_refundable' => EDD()->utils->date( 'now' )->addDays( 30 )->toDateTimeString(),
			'email'           => 'guest@edd.local',
			'ip'              => '10.0.0.1',
			'gateway'         => 'stripe',
			'mode'            => 'live',
			'currency'        => 'USD',
			'payment_key'     => md5( uniqid() ),
			'subtotal'        => 100,
			'discount'        => 5,
			'tax'             => 25,
			'total'           => 120,
		) );

		// Prime cache.
		$o = edd_get_order( self::$order );

		// Prime item cache.
		$items = $o->items;

		self::$order_item = $items[0]->id;

		edd_add_order_adjustment( array(
			'object_type' => 'order',
			'object_id'   => self::$order,
			'type'        => 'discount',
			'description' => '5OFF',
			'subtotal'    => 0,
			'total'       => 5,
		) );
	}

	/**
	 * @covers ::edd_is_order_refundable
	 */
	public function test_is_order_refundable_should_return_true() {
		$this->assertTrue( edd_is_order_refundable( self::$order ) );
	}

	/**
	 * @covers ::edd_get_order_total
	 */
	public function test_get_order_total_should_be_120() {
		$this->assertSame( 120.0, edd_get_order_total( self::$order ) );
	}

	/**
	 * @covers ::edd_get_order_item_total
	 */
	public function test_get_order_item_total_should_be_120() {
		$this->assertSame( 120.0, edd_get_order_item_total( array( self::$order ), 1 ) );
	}
}