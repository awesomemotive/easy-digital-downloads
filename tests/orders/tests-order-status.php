<?php
/**
 * Order Status Tests.
 *
 * @group edd_orders
 * @group database
 *
 * @coversDefaultClass \EDD\Orders\Order
 */
namespace EDD\Tests\Orders;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Order_Status_Tests extends EDD_UnitTestCase {

	/**
	 * @covers edd_get_complete_order_statuses()
	 */
	public function test_get_complete_order_statuses() {
		$expected = array( 'publish', 'complete', 'completed', 'partially_refunded', 'revoked', 'refunded' );

		$this->assertEquals( $expected, edd_get_complete_order_statuses() );
	}

	/**
	 * @covers edd_get_complete_order_statuses()
	 */
	public function test_get_complete_order_statuses_label() {
		$statuses = edd_get_complete_order_statuses( true );

		$this->assertEquals( 'Completed', $statuses['complete'] );
	}

	/**
	 * @covers edd_get_incomplete_order_statuses()
	 */
	public function test_get_incomplete_order_statuses() {
		$expected = array( 'pending', 'abandoned', 'processing', 'failed', 'cancelled' );

		$this->assertEquals( $expected, edd_get_incomplete_order_statuses() );
	}

	/**
	 * @covers edd_get_incomplete_order_statuses()
	 */
	public function test_get_incomplete_order_statuses_label() {
		$statuses = edd_get_incomplete_order_statuses( true );

		$this->assertEquals( 'Abandoned', $statuses['abandoned'] );
	}

	/**
	 * @covers edd_recoverable_order_statuses()
	 */
	public function test_recoverable_order_statuses() {
		$expected = array( 'pending', 'abandoned', 'failed' );

		$this->assertEquals( $expected, edd_recoverable_order_statuses() );
	}

	/**
	 * @covers edd_recoverable_order_statuses()
	 */
	public function test_recoverable_order_statuses_label() {
		$statuses = edd_recoverable_order_statuses( true );

		$this->assertEquals( 'Failed', $statuses['failed'] );
	}

	/**
	 * @covers edd_get_complete_order_statuses()
	 */
	public function test_order_status_complete_should_be_in_complete_status_array() {
		$order = parent::edd()->order->create_and_get();

		$this->assertTrue( in_array( $order->status, edd_get_complete_order_statuses(), true ) );
	}

	/**
	 * @covers edd_get_incomplete_order_statuses()
	 */
	public function test_order_status_complete_should_not_be_in_incomplete_status_array() {
		$order = parent::edd()->order->create_and_get();

		$this->assertFalse( in_array( $order->status, edd_get_incomplete_order_statuses(), true ) );
	}

	/**
	 * @covers \EDD\Orders\Order::is_recoverable()
	 */
	public function test_order_status_pending_is_recoverable() {
		$order = parent::edd()->order->create_and_get();
		edd_update_order( $order->id, array( 'status' => 'pending' ) );
		$order = edd_get_order( $order->id );

		$this->assertTrue( $order->is_recoverable() );
	}

	public function test_order_status_on_hold_is_not_complete() {
		$order = parent::edd()->order->create_and_get();
		edd_update_order( $order->id, array( 'status' => 'on_hold' ) );
		$order = edd_get_order( $order->id );

		$this->assertFalse( $order->is_complete() );
	}

	public function test_recording_dispute_sets_status_on_hold() {
		$order = parent::edd()->order->create_and_get();
		edd_record_order_dispute( $order->id, 'dispute_id_1', 'dispute_reason_1' );
		$order = edd_get_order( $order->id );

		$this->assertEquals( 'on_hold', $order->status );
		$this->assertEquals( 'dispute_id_1', edd_get_order_dispute_id( $order->id ) );
		$this->assertEquals( 'dispute_reason_1', edd_get_order_hold_reason( $order->id ) );

		foreach ( $order->get_items() as $item ) {
			$this->assertEquals( 'on_hold', $item->status );
			$this->assertFalse( $item->is_deliverable() );
		}
	}
}
