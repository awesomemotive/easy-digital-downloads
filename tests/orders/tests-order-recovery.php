<?php
/**
 * Order Reovery Tests.
 *
 * @group edd_orders
 *
 */
namespace EDD\Tests\Orders;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Recovery extends EDD_UnitTestCase {

	protected static $recoverable_payment_id;

	protected static $recovered_payment_id;

	public function setup(): void {
		/**
		 * @internal This call is necessary as we need to flush the meta cache.
		 */
		wp_cache_flush();

		self::$recoverable_payment_id = $this->generate_recoverable_order();
		EDD()->session->set( 'edd_resume_payment', self::$recoverable_payment_id );
		self::$recovered_payment_id = $this->generate_recovered_order();
	}

	public function tearDown(): void {
		parent::tearDown();

		edd_destroy_order( self::$recoverable_payment_id );
		edd_destroy_order( self::$recovered_payment_id );
		wp_cache_flush();
	}

	public function test_recovering_payment_ids_match() {
		$this->assertEquals( self::$recoverable_payment_id, self::$recovered_payment_id );
	}

	public function test_recoverable_payment_matches_session() {
		$this->assertEquals( self::$recoverable_payment_id, EDD()->session->get( 'edd_resume_payment' ) );
	}

	public function test_recovering_payment_guest_to_guest() {

		$payment           = edd_get_payment( self::$recovered_payment_id );
		$payment_customer  = new \EDD_Customer( $payment->customer_id );
		$recovery_customer = new \EDD_Customer( 'batman@thebatcave.co' );

		$this->assertSame( $payment_customer->id, $recovery_customer->id );
	}

	public function test_recovering_payment_gateway_change() {
		$order = edd_get_order( self::$recovered_payment_id );

		$this->assertEquals( 'stripe', $order->gateway );
	}

	public function test_recovering_payment_date_is_today() {
		$order = edd_get_order( self::$recovered_payment_id );

		$this->assertEquals( gmdate( 'Y-m-d' ), gmdate( 'Y-m-d', strtotime( $order->date_created ) ) );
	}

	private function generate_recoverable_order() {
		$initial_purchase_data = array(
			'price'        => 299.0,
			'date'         => '2017-08-15 18:10:37',
			'user_email'   => 'bruce@waynefoundation.org',
			'purchase_key' => '186c2fb5402d756487bd4b6192d59bc2',
			'currency'     => 'USD',
			'downloads'    =>
				array(
					0 =>
						array(
							'id'       => '1906',
							'options'  =>
								array(
									'price_id' => '1',
								),
							'quantity' => 1,
						),
				),
			'user_info'    =>
				array(
					'id'         => 0,
					'email'      => 'bruce@waynefoundation.org',
					'first_name' => 'Bruce',
					'last_name'  => 'Wayne',
					'discount'   => 'none',
					'address'    =>
						array(),
				),
			'cart_details' =>
				array(
					0 =>
						array(
							'name'        => 'Test Product 1',
							'id'          => '1906',
							'item_number' =>
								array(
									'id'       => '1906',
									'options'  =>
										array(
											'price_id' => '1',
										),
									'quantity' => 1,
								),
							'item_price'  => 299.0,
							'quantity'    => 1,
							'discount'    => 0.0,
							'subtotal'    => 299.0,
							'tax'         => 0.0,
							'fees'        =>
								array(),
							'price'       => 299.0,
						),
				),
			'gateway'      => 'paypal',
			'status'       => 'pending',
		);

		return edd_build_order( $initial_purchase_data );
	}

	private function generate_recovered_order() {
		$recovery_purchase_data = array(
			'price'        => 299.0,
			'user_email'   => 'batman@thebatcave.co',
			'purchase_key' => '4f2b5cda76c2a997996f4cf8b68255ed',
			'currency'     => 'USD',
			'downloads'    =>
				array(
					0 =>
						array(
							'id'       => '1906',
							'options'  =>
								array(
									'price_id' => '1',
								),
							'quantity' => 1,
						),
				),
			'user_info'    =>
				array(
					'id'         => 0,
					'email'      => 'batman@thebatcave.co',
					'first_name' => 'Batman',
					'last_name'  => '',
					'discount'   => 'none',
					'address'    =>
						array(),
				),
			'cart_details' =>
				array(
					0 =>
						array(
							'name'        => 'Test Product 1',
							'id'          => '1906',
							'item_number' =>
								array(
									'id'       => '1906',
									'options'  =>
										array(
											'price_id' => '1',
										),
									'quantity' => 1,
								),
							'item_price'  => 299.0,
							'quantity'    => 1,
							'discount'    => 0.0,
							'subtotal'    => 299.0,
							'tax'         => 0.0,
							'fees'        =>
								array(),
							'price'       => 299.0,
						),
				),
			'gateway'      => 'stripe',
			'status'       => 'pending',
		);

		return edd_build_order( $recovery_purchase_data );
	}
}
