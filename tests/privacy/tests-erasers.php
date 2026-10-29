<?php
namespace EDD\Tests\Privacy;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Unit tests for the privacy data erasers in includes/privacy-functions.php.
 */
class Erasers extends EDD_UnitTestCase {

	/**
	 * An email address with no EDD customer.
	 *
	 * @var string
	 */
	private $non_customer_email = 'not-a-customer@edd.test';

	/**
	 * A complete order left with no customer after its customer was deleted.
	 *
	 * @var \EDD\Orders\Order
	 */
	private $detached_order;

	public function set_up() {
		parent::set_up();

		$this->detached_order = $this->create_order_with_address( 0, 'complete' );
	}

	public function tear_down() {
		edd_privacy_remove_customer_id( $this->non_customer_email );

		parent::tear_down();
	}

	public function test_payment_eraser_leaves_detached_orders_untouched_for_email_without_customer() {
		$result = $this->run_eraser( 'edd_privacy_payment_eraser', $this->non_customer_email );

		$order = edd_get_order( $this->detached_order->id );

		$this->assertTrue( $result['done'] );
		$this->assertFalse( $result['items_removed'] );
		$this->assertSame( 'Order Buyer', $order->address->name );
		$this->assertSame( '1 Main Street', $order->address->address );
		$this->assertSame( '10.1.1.1', $order->ip );
	}

	public function test_payment_eraser_keeps_detached_failed_order_for_email_without_customer() {
		$failed_order = $this->create_order_with_address( 0, 'failed' );

		$this->run_eraser( 'edd_privacy_payment_eraser', $this->non_customer_email );

		$this->assertNotEmpty( edd_get_order( $failed_order->id ) );
	}

	public function test_file_download_logs_eraser_leaves_detached_logs_untouched_for_email_without_customer() {
		$detached_log = parent::edd()->file_download_log->create_and_get(
			array(
				'customer_id' => 0,
				'ip'          => '10.1.1.1',
			)
		);

		$result = $this->run_eraser( 'edd_privacy_file_download_logs_eraser', $this->non_customer_email );

		$this->assertTrue( $result['done'] );
		$this->assertFalse( $result['items_removed'] );
		$this->assertSame( '10.1.1.1', edd_get_file_download_log( $detached_log->id )->ip );
	}

	public function test_payment_eraser_anonymizes_orders_of_requested_customer() {
		$customer       = parent::edd()->customer->create_and_get();
		$customer_order = $this->create_order_with_address( $customer->id, 'complete' );

		$this->run_eraser( 'edd_privacy_payment_eraser', $customer->email );
		edd_privacy_remove_customer_id( $customer->email );

		$order = edd_get_order( $customer_order->id );

		$this->assertSame( '', $order->address->name );
		$this->assertSame( '10.1.1.0', $order->ip );
		$this->assertSame( 'Order Buyer', edd_get_order( $this->detached_order->id )->address->name );
	}

	/**
	 * Creates an order with a billing name and street address.
	 *
	 * @param int    $customer_id The customer ID, 0 for a detached order.
	 * @param string $status      The order status.
	 * @return \EDD\Orders\Order
	 */
	private function create_order_with_address( $customer_id, $status ) {
		$order = parent::edd()->order->create_and_get(
			array(
				'customer_id' => $customer_id,
				'status'      => $status,
			)
		);

		edd_update_order_address(
			$order->address->id,
			array(
				'name'    => 'Order Buyer',
				'address' => '1 Main Street',
			)
		);

		return edd_get_order( $order->id );
	}

	/**
	 * Runs the customer ID prefetch, then the first page of an eraser, as WordPress does.
	 *
	 * @param callable $eraser        The eraser callback.
	 * @param string   $email_address The email address being erased.
	 * @return array
	 */
	private function run_eraser( $eraser, $email_address ) {
		edd_privacy_prefetch_customer_id( $email_address );

		return call_user_func( $eraser, $email_address, 1 );
	}
}
