<?php
/**
 * Refund Tests.
 *
 * @group edd_orders
 * @group edd_refunds
 * @group database
 *
 * @coversDefaultClass \EDD\Orders\Order
 */
namespace EDD\Tests\Orders;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Utils\Date;
use EDD\Utils\Exceptions\Invalid_Argument;
use EDD\Orders\Refunds\Validator;
use EDD\Orders\Refunds\Number;
use EDD\Orders\Refund_Validator;

class Refunds extends EDD_UnitTestCase {

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

	public function test_get_refund_number_returns_1() {
		$order = parent::edd()->order->create_and_get();
		$refund_number = Number::generate( $order->id );

		$this->assertEquals( $order->id . '-R-1', $refund_number );
	}

	public function test_get_refund_number_returns_2() {
		$order = parent::edd()->order->create_and_get();
		edd_refund_order( $order->id );

		$refund_number = Number::generate( $order->id );
		$this->assertEquals( $order->id . '-R-2', $refund_number );
	}

	public function test_get_refund_number_with_filtered_suffix() {
		add_filter( 'edd_order_refund_suffix', function( $suffix ) {
			return 'TEST';
		} );

		$order = parent::edd()->order->create_and_get();
		$refund_number = Number::generate( $order->id );

		$this->assertEquals( $order->id . 'TEST1', $refund_number );

		remove_filter( 'edd_order_refund_suffix', function( $suffix ) {
			return 'TEST';
		} );
	}

	/**
	 * @covers ::edd_is_order_refundable
	 */
	public function test_is_order_refundable_should_return_true() {
		$this->assertTrue( edd_is_order_refundable( self::$orders[0] ) );
	}

	/**
	 * @covers ::edd_is_order_refund_window_passed
	 */
	public function test_is_order_refund_window_passed_return_true() {
		$order = parent::edd()->order->create_and_get( array(
			'date_refundable' => '2000-01-01 00:00:00',
		) );

		$this->assertTrue( edd_is_order_refund_window_passed( $order->id ) );
	}

	/**
	 * @covers ::edd_is_order_refundable_by_override
	 */
	public function test_is_order_refundable_by_override_return_true() {
		$order = parent::edd()->order->create_and_get( array(
			'date_refundable' => '2000-01-01 00:00:00',
		) );

		add_filter( 'edd_is_order_refundable_by_override', '__return_true' );

		$this->assertTrue( edd_is_order_refundable_by_override( $order->id ) );
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
		$this->assertNotInstanceOf( 'WP_Error', $refunded_order );
		$this->assertGreaterThan( 0, $refunded_order );

		// Fetch original order.
		$o = edd_get_order( self::$orders[0] );

		// Check a valid Order object was returned.
		$this->assertInstanceOf( 'EDD\Orders\Order', $o );

		// Verify status.
		$this->assertSame( 'refunded', $o->status );

		// Verify type.
		$this->assertSame( 'sale', $o->type );

		// Verify total.
		$this->assertSame( 120.0, floatval( $o->total ) );

		// Fetch refunded order.
		$r = edd_get_order( $refunded_order );

		// Check a valid Order object was returned.
		$this->assertInstanceOf( 'EDD\Orders\Order', $r );

		// Verify status.
		$this->assertSame( 'complete', $r->status );

		// Verify type.
		$this->assertSame( 'refund', $r->type );

		// Verify total.
		$this->assertSame( -120.0, floatval( $r->total ) );
	}

	/**
	 * @covers ::edd_refund_order
	 */
	public function test_refund_order_returns_wp_error_if_refund_amount_exceeds_max() {
		$order = edd_get_order( self::$orders[1] );

		$to_refund = array();

		foreach( $order->items as $order_item ) {
			$to_refund[] = array(
				'order_item_id' => $order_item->id,
				'subtotal'      => $order_item->subtotal * 2,
				'tax'           => $order_item->tax,
				'total'         => $order_item->total * 2
			);
		}

		$refund_id = edd_refund_order( $order->id, $to_refund );

		$this->assertInstanceOf( 'WP_Error', $refund_id );

		$this->assertEquals( 'refund_validation_error', $refund_id->get_error_code() );
		$this->assertStringContainsString( 'The maximum refund subtotal', $refund_id->get_error_message() );
	}

	/**
	 * @covers ::edd_refund_order
	 * @covers ::edd_get_order_total
	 */
	public function test_partially_refund_order() {
		$order = edd_get_order( self::$orders[1] );

		$to_refund = array();

		foreach( $order->items as $order_item ) {
			// Only refund half the subtotal / tax for the order item. This creates a partial refund.
			$to_refund[] = array(
				'order_item_id' => $order_item->id,
				'subtotal'      => ( $order_item->subtotal - $order_item->discount ) / 2,
				'tax'           => $order_item->tax / 2,
				'total'         => $order_item->total / 2
			);
		}

		$refund_id = edd_refund_order( $order->id, $to_refund );

		$this->assertGreaterThan( 0, $refund_id );

		// Fetch original order.
		$o = edd_get_order( $order->id );

		// Check a valid Order object was returned.
		$this->assertInstanceOf( 'EDD\Orders\Order', $o );

		// Verify status.
		$this->assertSame( 'partially_refunded', $o->status );

		// Verify type.
		$this->assertSame( 'sale', $o->type );

		// Verify original total.
		$this->assertEquals( 120.0, floatval( $o->total ) );

		// Verify total minus refunded amount.
		$this->assertEquals( 60.0, edd_get_order_total( $o->id ) );

		// Fetch refunded order.
		$r = edd_get_order( $refund_id );

		// Check a valid Order object was returned.
		$this->assertInstanceOf( 'EDD\Orders\Order', $r );

		// Verify status.
		$this->assertSame( 'complete', $r->status );

		// Verify type.
		$this->assertSame( 'refund', $r->type );

		// Verify total.
		$this->assertEquals( -60.0, floatval( $r->total ) );
	}

	public function test_partial_refund_with_free_download_remaining() {
		$order_id = self::$orders[2];
		edd_add_order_item( array(
			'order_id'     => $order_id,
			'product_id'   => 17,
			'product_name' => 'Free Download',
			'status'       => 'inherit',
			'amount'       => 0,
			'subtotal'     => 0,
			'discount'     => 0,
			'tax'          => 0,
			'total'        => 0,
			'quantity'     => 1,
		) );

		$to_refund = array();
		$order     = edd_get_order( $order_id );
		foreach ( $order->items as $order_item ) {
			if ( $order_item->total > 0 ) {
				$to_refund[] = array(
					'order_item_id' => $order_item->id,
					'subtotal'      => ( $order_item->subtotal - $order_item->discount ),
					'tax'           => $order_item->tax,
					'total'         => $order_item->total,
				);
			}
		}

		edd_refund_order( $order->id, $to_refund );

		// Fetch original order.
		$o = edd_get_order( $order->id );

		$this->assertSame( 'partially_refunded', $o->status );
	}

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

		$this->assertSame( Date::parse( $date )->addDays( 30 )->toDateTimeString(), edd_get_refund_date( $date ) );
	}

	public function test_refund_validator_all_returns_original_amounts() {
		$order     = edd_get_order( self::$orders[1] );
		$validator = new Validator( $order, 'all', 'all' );
		$validator->validate_and_calculate_totals();

		$this->assertEquals( ( $order->subtotal - $order->discount ), $validator->subtotal );
		$this->assertEquals( $order->tax, $validator->tax );
		$this->assertEquals( $order->total, $validator->total );

		$order_item_ids  = wp_list_pluck( $order->items, 'id' );
		$refund_item_ids = wp_list_pluck( $validator->get_refunded_order_items(), 'parent' );

		sort( $order_item_ids );
		sort( $refund_item_ids );

		$this->assertEquals( $order_item_ids, $refund_item_ids );
	}

	public function test_refund_validator_alias_all_returns_original_amounts() {
		$order     = edd_get_order( self::$orders[1] );
		$validator = new Refund_Validator( $order, 'all', 'all' );
		$validator->validate_and_calculate_totals();

		$this->assertEquals( ( $order->subtotal - $order->discount ), $validator->subtotal );
		$this->assertEquals( $order->tax, $validator->tax );
		$this->assertEquals( $order->total, $validator->total );

		$order_item_ids  = wp_list_pluck( $order->items, 'id' );
		$refund_item_ids = wp_list_pluck( $validator->get_refunded_order_items(), 'parent' );

		sort( $order_item_ids );
		sort( $refund_item_ids );

		$this->assertEquals( $order_item_ids, $refund_item_ids );
	}

	/**
	 * An Invalid_Argument exception is thrown if the `order_item_id` argument is missing.
	 */
	public function test_refund_validator_throws_exception_missing_order_item_id() {
		$order = edd_get_order( self::$orders[1] );

		$this->expectException( Invalid_Argument::class );

		$validator = new Validator( $order, array(
			array(
				'subtotal' => 100,
				'tax'      => 20,
				'total'    => 120
			)
		), 'all' );

		$exception = $this->getexpectException();
		$this->assertStringContainsString( 'order_item_id', $exception->getMessage() );
	}

	/**
	 * An Invalid_Argument exception is thrown if the `subtotal` argument is missing.
	 */
	public function test_refund_validator_throws_exception_missing_subtotal() {
		$order = edd_get_order( self::$orders[1] );

		$this->expectException( Invalid_Argument::class );

		$validator = new Validator( $order, array(
			array(
				'order_item_id' => $order->items[0]->id,
				'tax'           => $order->items[0]->tax,
				'total'         => $order->items[0]->total
			)
		), 'all' );

		$exception = $this->getexpectException();
		$this->assertStringContainsString( 'subtotal', $exception->getMessage() );
	}

	public function test_is_order_refundable_refunded_order_returns_false() {
		$order = parent::edd()->order->create_and_get();
		edd_refund_order( $order->id );

		$this->assertFalse( edd_is_order_refundable( $order->id ) );
	}

	public function test_is_order_refundable_child_order_refunded_returns_true() {
		$order = parent::edd()->order->create_and_get();
		$child = parent::edd()->order->create_and_get( array( 'parent' => $order->id ) );
		edd_refund_order( $child->id );

		$this->assertTrue( edd_is_order_refundable( $order->id ) );
	}
}
