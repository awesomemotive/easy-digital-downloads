<?php

/**
 * Payment tests.
 *
 * @group edd_payments
 */
class Payment_Compat_Tests extends \EDD_UnitTestCase {

	public $payment_id;

	public function setUp() {
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
		update_option( 'edd_v3_migration_pending', true, false );
	}

	public function tearDown() {
		parent::tearDown();

		wp_delete_post( $this->payment_id );
		delete_option( 'edd_v3_migration_pending' );
	}

	public function test_edd_get_payment_no_order_during_migration_should_return_object() {
		$payment = edd_get_payment( $this->payment_id );

		$this->assertTrue( $payment instanceof EDD_Payment );
	}

	public function test_edd_get_payment_no_order_during_migration_should_have_payment_id() {
		$payment = edd_get_payment( $this->payment_id );

		$this->assertSame( $this->payment_id, $payment->ID );
	}

	public function test_edd_get_payment_no_order_during_migration_total_is_20() {
		$payment = edd_get_payment( $this->payment_id );

		$this->assertEquals( 20, $payment->total );
	}

	public function test_edd_get_payment_no_order_during_migration_gateway_is_stripe() {
		$payment = edd_get_payment( $this->payment_id );

		$this->assertEquals( 'stripe', $payment->gateway );
	}

	public function test_edd_get_payment_no_order_outside_migration_should_return_false() {
		delete_option( 'edd_v3_migration_pending' );
		$payment = edd_get_payment( $this->payment_id );

		$this->assertFalse( $payment );
	}
}
