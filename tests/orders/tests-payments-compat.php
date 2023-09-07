<?php
/**
 * Payment tests.
 *
 * @group edd_payments
 * @group edd_legacy
 */
namespace EDD\Tests\Orders;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Orders\Order;

class Payment_Compat_Tests extends EDD_UnitTestCase {

	public $payment_id;

	public $payment;

	public function setup(): void {
		parent::setUp();

		$this->payment_id = wp_insert_post(
			array(
				'post_type'   => 'edd_payment',
				'post_status' => 'publish',
			)
		);

		update_post_meta(
			$this->payment_id,
			'_edd_payment_meta',
			array(
				'key'       => 'd8eb04c6fca9a2941a9ed1706a5d48d3',
				'email'     => 'customer@test.com',
				'user_info' => array(
					'id'         => 1234,
					'email'      => 'customer@test.com',
					'first_name' => 'Customer',
				),
				'downloads' => array(
					array(
						'id'       => 1,
						'quantity' => 1,
						'options'  => array(
							'quantity' => 1,
						),
					),
				),
			)
		);
		update_post_meta( $this->payment_id, '_edd_payment_gateway', 'stripe' );
		update_post_meta( $this->payment_id, '_edd_payment_total', 20.00 );
		update_post_meta( $this->payment_id, '_edd_payment_customer_id', 213 );
		update_option( 'edd_v3_migration_pending', 123456, false );

		$this->payment = edd_get_payment( $this->payment_id );
	}

	public function tearDown(): void {
		parent::tearDown();

		wp_delete_post( $this->payment_id );
		delete_option( 'edd_v3_migration_pending' );
	}

	public function test_edd_get_payment_no_order_during_migration_should_return_object() {
		$this->assertTrue( $this->payment instanceof \EDD_Payment );
	}

	public function test_edd_get_payment_no_order_during_migration_should_have_payment_id() {
		$this->assertSame( $this->payment_id, $this->payment->ID );
	}

	public function test_edd_get_payment_no_order_during_migration_should_have_order_object() {
		$this->assertTrue( $this->payment->order instanceof Order );
	}

	public function test_edd_get_payment_no_order_during_migration_payment_id_should_match_order_id() {
		$this->assertSame( $this->payment->order->id, $this->payment_id );
	}

	public function test_edd_get_payment_no_order_during_migration_should_have_payment_meta() {
		$this->assertNotEmpty( $this->payment->payment_meta );
	}

	public function test_edd_get_payment_no_order_during_migration_should_have_download() {
		$this->assertSame( 1, $this->payment->downloads[0]['id'] );
	}

	public function test_edd_get_payment_no_order_during_migration_total_is_20() {
		$this->assertEquals( 20, $this->payment->total );
	}

	public function test_edd_get_payment_no_order_during_migration_gateway_is_stripe() {
		$this->assertEquals( 'stripe', $this->payment->gateway );
	}

	public function test_edd_get_payment_no_order_during_migration_status_is_complete() {
		$this->assertEquals( 'complete', $this->payment->status );
	}

	public function test_edd_get_payment_no_order_during_migration_customer_id_is_213() {
		$this->assertEquals( 213, $this->payment->customer_id );
	}

	public function test_edd_get_order_no_order_during_migration_gets_order() {
		$order = edd_get_order( $this->payment_id );

		$this->assertSame( $this->payment_id, $order->id );
	}

	public function test_edd_get_payment_meta_no_order_during_migration_gets_meta() {
		update_post_meta( $this->payment->ID, '_edd_random_metadata', 'sample_meta' );

		$this->assertSame( 'sample_meta', edd_get_payment_meta( $this->payment->ID, '_edd_random_metadata', true ) );
	}

	public function test_edd_get_payment_no_order_outside_migration_should_return_false() {
		delete_option( 'edd_v3_migration_pending' );
		$payment = edd_get_payment( $this->payment_id );

		$this->assertFalse( $payment );
	}

	public function test_edd_get_order_no_order_outside_migration_should_return_false() {
		delete_option( 'edd_v3_migration_pending' );
		$order = edd_get_order( $this->payment_id );

		$this->assertFalse( $order );
	}
}
