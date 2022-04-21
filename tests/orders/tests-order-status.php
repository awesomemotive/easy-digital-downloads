<?php
namespace EDD\Orders;

/**
 * Order Status Tests.
 *
 * @group edd_orders
 * @group database
 *
 * @coversDefaultClass \EDD\Orders\Order
 */
class Order_Status_Tests extends \EDD_UnitTestCase {

	public function test_get_complete_order_statuses() {
		$expected = array( 'publish', 'complete', 'completed', 'partially_refunded', 'revoked', 'refunded' );

		$this->assertEquals( $expected, edd_get_complete_order_statuses() );
	}

	public function test_get_complete_order_statuses_label() {
		$statuses = edd_get_complete_order_statuses( true );

		$this->assertEquals( 'Completed', $statuses['complete'] );
	}

	public function test_get_incomplete_order_statuses() {
		$expected = array( 'pending', 'abandoned', 'processing', 'failed' );

		$this->assertEquals( $expected, edd_get_incomplete_order_statuses() );
	}

	public function test_get_incomplete_order_statuses_label() {
		$statuses = edd_get_complete_order_statuses( true );

		$this->assertEquals( 'Abandoned', $statuses['abandoned'] );
	}

	public function test_recoverable_order_statuses() {
		$expected = array( 'pending', 'abandoned', 'failed' );

		$this->assertEquals( $expected, edd_recoverable_order_statuses() );
	}

	public function test_recoverable_order_statuses_label() {
		$statuses = edd_recoverable_order_statuses( true );

		$this->assertEquals( 'Failed', $statuses['failed'] );
	}

	public function test_order_status_complete_should_be_in_complete_status_array() {
		$order = parent::edd()->order->create_and_get();

		$this->assertTrue( in_array( $order->status, edd_get_complete_order_statuses(), true ) );
	}

	public function test_order_status_complete_should_not_be_in_incomplete_status_array() {
		$order = parent::edd()->order->create_and_get();

		$this->assertFalse( in_array( $order->status, edd_get_incomplete_order_statuses(), true ) );
	}

	public function test_order_status_pending_is_recoverable() {
		$order = parent::edd()->order->create_and_get();
		edd_update_order( $order->id, array( 'status' => 'pending' ) );

		$this->assertTrue( $order->is_recoverable() );
	}
}
