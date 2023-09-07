<?php
/**
 * Payment tests.
 *
 * @group edd_payments
 * @group edd_legacy
 */
namespace EDD\Tests\Orders;

use EDD\Tests\Helpers;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Payment_Tests extends EDD_UnitTestCase {

	/**
	 * Payment test fixture.
	 *
	 * @var \EDD_Payment
	 */
	protected static $payment;

	/**
	 * Order items test fixture.
	 *
	 * @var array
	 */
	protected static $order_items;

	public function setup(): void {
		self::$payment = edd_get_payment( Helpers\EDD_Helper_Payment::create_simple_payment() );
		self::$order_items = edd_get_order_items(
			array(
				'order_id' => self::$payment->ID,
			)
		);
	}

	public function tearDown(): void {
		parent::tearDown();

		Helpers\EDD_Helper_Payment::delete_payment( self::$payment->ID );

		edd_destroy_order( self::$payment->ID );

		$component = edd_get_component_interface( 'order_transaction', 'table' );

		if ( $component instanceof \EDD\Database\Table ) {
			$component->truncate();
		}

		self::$payment = null;
	}

	public function test_get_payments() {
		$payments = edd_get_payments();

		$this->assertTrue( is_array( (array) $payments[0] ) );
		$this->assertArrayHasKey( 'ID', (array) $payments[0] );
		$this->assertEquals( 'edd_payment', $payments[0]->post_type );
	}

	public function test_get_payments_download_array() {
		$payments = edd_get_payments(
			array(
				'download' => array( self::$order_items[0]->product_id ),
			)
		);

		$this->assertFalse( empty( $payments ) );
		$this->assertSame( self::$payment->ID, $payments[0]->ID );
	}

	public function test_get_payments_download_string() {
		$payments = edd_get_payments(
			array(
				'download' => (string) self::$order_items[0]->product_id,
			)
		);

		$this->assertFalse( empty( $payments ) );
		$this->assertSame( self::$payment->ID, $payments[0]->ID );
	}

	public function test_get_payments_download_numeric() {
		$payments = edd_get_payments(
			array(
				'download' => (int) self::$order_items[0]->product_id,
			)
		);

		$this->assertFalse( empty( $payments ) );
		$this->assertSame( self::$payment->ID, $payments[0]->ID );
	}

	public function test_payments_query_edd_payments() {
		$payments = new \EDD_Payments_Query( array( 'output' => 'edd_payments' ) );
		$payments = $payments->get_payments();

		$this->assertTrue( is_object( $payments[0] ) );
		$this->assertTrue( property_exists( $payments[0], 'ID' ) );
		$this->assertTrue( property_exists( $payments[0], 'cart_details' ) );
		$this->assertTrue( property_exists( $payments[0], 'user_info' ) );
	}

	public function test_payments_query_payments() {
		$payments = new \EDD_Payments_Query( array( 'output' => 'payments' ) );
		$out      = $payments->get_payments();
		$this->assertTrue( is_object( $out[0] ) );
		$this->assertTrue( property_exists( $out[0], 'ID' ) );
		$this->assertTrue( property_exists( $out[0], 'cart_details' ) );
		$this->assertTrue( property_exists( $out[0], 'user_info' ) );
	}

	public function test_payments_query_default() {
		$payments = new \EDD_Payments_Query();
		$payments = $payments->get_payments();

		$this->assertTrue( is_object( $payments[0] ) );
		$this->assertTrue( property_exists( $payments[0], 'ID' ) );
		$this->assertTrue( property_exists( $payments[0], 'cart_details' ) );
		$this->assertTrue( property_exists( $payments[0], 'user_info' ) );
	}

	public function test_payments_query_search_discount() {
		$discount = edd_get_discount( Helpers\EDD_Helper_Discount::create_simple_percent_discount() );

		$payment_id = Helpers\EDD_Helper_Payment::create_simple_payment( array( 'discount' => $discount->code ) );

		$payments_query = new \EDD_Payments_Query( array( 's' => 'discount:' . $discount->code ) );
		$out            = $payments_query->get_payments();

		$this->assertEquals( 1, count( $out ) );
		$this->assertEquals( $payment_id, $out[0]->ID );

		Helpers\EDD_Helper_Payment::delete_payment( $payment_id );

		$payments_query = new \EDD_Payments_Query( array( 's' => 'discount:' . $discount->code ) );
		$out            = $payments_query->get_payments();

		$this->assertEquals( 0, count( $out ) );
	}

	public function test_payments_query_count_payments() {
		$payments = new \EDD_Payments_Query( array( 'count' => true ) );
		$count    = $payments->get_payments();

		$this->assertTrue( is_numeric( $count ) );
		$this->assertEquals( 1, $count );
	}

	public function test_edd_get_payment_by() {
		$payment = edd_get_payment_by( 'id', self::$payment->ID );
		$this->assertObjectHasAttribute( 'ID', $payment );

		$payment = edd_get_payment_by( 'key', self::$payment->key );
		$this->assertObjectHasAttribute( 'ID', $payment );
	}

	public function test_fake_insert_payment() {
		$this->assertFalse( edd_insert_payment() );
	}

	public function test_payment_completed_flag_not_exists() {
		$completed_date = edd_get_payment_completed_date( self::$payment->ID );
		$this->assertEmpty( $completed_date );
	}

	public function test_update_payment_status() {
		edd_update_payment_status( self::$payment->ID, 'complete' );

		$out = edd_get_payments();

		$this->assertEquals( 'complete', $out[0]->status );
	}

	public function test_update_payment_status_with_invalid_id() {
		$updated = edd_update_payment_status( 12121212, 'complete' );

		$this->assertFalse( $updated );
	}

	public function test_check_for_existing_payment() {
		edd_update_payment_status( self::$payment->ID, 'complete' );

		$this->assertTrue( edd_check_for_existing_payment( self::$payment->ID ) );
	}

	public function test_get_payment_status() {
		$this->assertEquals( 'pending', edd_get_payment_status( self::$payment->ID ) );

		$this->assertEquals( 'pending', edd_get_payment_status( self::$payment ) );
		$this->assertFalse( edd_get_payment_status( 1212121212121 ) );
	}

	public function test_get_payment_status_label() {
		$this->assertEquals( 'Pending', edd_get_payment_status( self::$payment->ID, true ) );

		$this->assertEquals( 'Pending', edd_get_payment_status( self::$payment, true ) );
	}

	public function test_get_payment_statuses() {
		$out = edd_get_payment_statuses();

		$expected = array(
			'pending'            => 'Pending',
			'complete'           => 'Completed',
			'refunded'           => 'Refunded',
			'partially_refunded' => 'Partially Refunded',
			'failed'             => 'Failed',
			'revoked'            => 'Revoked',
			'abandoned'          => 'Abandoned',
			'processing'         => 'Processing',
			'on_hold'            => 'On Hold',
		);

		$this->assertEquals( $expected, $out );
	}

	public function test_get_payment_status_keys() {
		$out = edd_get_payment_status_keys();

		$expected = array(
			'pending'            => __( 'Pending', 'easy-digital-downloads' ),
			'processing'         => __( 'Processing', 'easy-digital-downloads' ),
			'complete'           => __( 'Completed', 'easy-digital-downloads' ),
			'refunded'           => __( 'Refunded', 'easy-digital-downloads' ),
			'partially_refunded' => __( 'Partially Refunded', 'easy-digital-downloads' ),
			'revoked'            => __( 'Revoked', 'easy-digital-downloads' ),
			'failed'             => __( 'Failed', 'easy-digital-downloads' ),
			'abandoned'          => __( 'Abandoned', 'easy-digital-downloads' ),
			'on_hold'            => __( 'On Hold', 'easy-digital-downloads' ),
		);

		asort( $expected );

		$expected = array_keys( $expected );

		$this->assertIsArray( $out );
		$this->assertEquals( $expected, $out );
	}

	public function test_delete_purchase() {
		edd_delete_purchase( self::$payment->ID );

		$this->assertEmpty( edd_get_payments() );
	}

	public function test_get_payment_completed_date() {
		edd_update_payment_status( self::$payment->ID, 'complete' );
		$payment = new \EDD_Payment( self::$payment->ID );

		$this->assertIsString( $payment->completed_date );
		$this->assertTrue( in_array( date( 'Y-m-d H:i', strtotime( $payment->completed_date ) ), $this->get_expected_date_range() ) );
	}

	public function test_get_payment_completed_date_bc() {
		edd_update_payment_status( self::$payment->ID, 'complete' );
		$completed_date = edd_get_payment_completed_date( self::$payment->ID );

		$this->assertIsString( $completed_date );
		$this->assertTrue( in_array( date( 'Y-m-d H:i', strtotime( $completed_date ) ), $this->get_expected_date_range() ) );
	}

	/**
	 * Gets an array of expected dates (a 20 second range).
	 */
	private function get_expected_date_range() {
		return array(
			date( 'Y-m-d H:i', strtotime( '-10 seconds' ) ),
			date( 'Y-m-d H:i', strtotime( '-9 seconds' ) ),
			date( 'Y-m-d H:i', strtotime( '-8 seconds' ) ),
			date( 'Y-m-d H:i', strtotime( '-7 seconds' ) ),
			date( 'Y-m-d H:i', strtotime( '-6 seconds' ) ),
			date( 'Y-m-d H:i', strtotime( '-5 seconds' ) ),
			date( 'Y-m-d H:i', strtotime( '-4 seconds' ) ),
			date( 'Y-m-d H:i', strtotime( '-3 seconds' ) ),
			date( 'Y-m-d H:i', strtotime( '-2 seconds' ) ),
			date( 'Y-m-d H:i', strtotime( '-1 second' ) ),
			date( 'Y-m-d H:i' ),
			date( 'Y-m-d H:i', strtotime( '+1 second' ) ),
			date( 'Y-m-d H:i', strtotime( '+2 seconds' ) ),
			date( 'Y-m-d H:i', strtotime( '+3 seconds' ) ),
			date( 'Y-m-d H:i', strtotime( '+4 seconds' ) ),
			date( 'Y-m-d H:i', strtotime( '+5 seconds' ) ),
			date( 'Y-m-d H:i', strtotime( '+6 seconds' ) ),
			date( 'Y-m-d H:i', strtotime( '+7 seconds' ) ),
			date( 'Y-m-d H:i', strtotime( '+8 seconds' ) ),
			date( 'Y-m-d H:i', strtotime( '+9 seconds' ) ),
			date( 'Y-m-d H:i', strtotime( '+10 seconds' ) ),
		);
	}

	public function test_get_payment_number() {
		global $edd_options;
		$edd_options['enable_sequential'] = 1;
		$edd_options['sequential_prefix'] = 'EDD-';
		$edd_options['sequential_start']  = 11;

		$payment_id = Helpers\EDD_Helper_Payment::create_simple_payment();

		// Create more payments just because.
		Helpers\EDD_Helper_Payment::create_simple_payment();
		Helpers\EDD_Helper_Payment::create_simple_payment();

		$order_number = new \EDD\Orders\Number();
		$next_number  = $order_number->get_next_payment_number();

		$this->assertIsInt( $next_number );
		$this->assertIsString( $order_number->format( $next_number ) );
		$this->assertEquals( 'EDD-14', $order_number->format( $next_number ) );

		$payment             = edd_get_payment( $payment_id );
		$last_payment_number = $order_number->unformat( $payment->number );
		$this->assertEquals( 11, $last_payment_number );
		$this->assertEquals( 'EDD-11', $payment->number );
		$this->assertEquals( 14, $next_number );

		// Now disable sequential and ensure values come back as expected
		$edd_options['enable_sequential'] = 0;

		$payment = edd_get_payment( $payment_id );
		$this->assertEquals( $payment_id, $payment->number );
	}

	public function test_get_payment_transaction_id_bc() {
		$this->assertEquals( self::$payment->transaction_id, edd_get_payment_transaction_id( self::$payment->ID ) );
	}

	public function test_get_payment_transaction_id_legacy() {
		$this->assertEquals( self::$payment->transaction_id, edd_paypal_get_payment_transaction_id( self::$payment->ID ) );
	}

	public function test_get_payment_meta() {
		// Test by getting the payment key with three different methods
		$this->assertEquals( self::$payment->key, self::$payment->get_meta( '_edd_payment_purchase_key' ) );
		$this->assertEquals( self::$payment->key, edd_get_payment_meta( self::$payment->ID, '_edd_payment_purchase_key', true ) );

		// Try and retrieve the transaction ID
		$this->assertEquals( self::$payment->transaction_id, self::$payment->get_meta( '_edd_payment_transaction_id' ) );

		$this->assertEquals( self::$payment->email, self::$payment->get_meta( '_edd_payment_user_email' ) );
	}

	public function test_get_payment_meta_bc() {
		// Test by getting the payment key with three different methods
		$this->assertEquals( self::$payment->key, edd_get_payment_meta( self::$payment->ID, '_edd_payment_purchase_key' ) );
		$this->assertEquals( self::$payment->key, edd_get_payment_meta( self::$payment->ID, '_edd_payment_purchase_key', true ) );
		$this->assertEquals( self::$payment->key, edd_get_payment_key( self::$payment->ID ) );

		// Try and retrieve the transaction ID
		$this->assertEquals( self::$payment->transaction_id, edd_get_payment_meta( self::$payment->ID, '_edd_payment_transaction_id' ) );

		$user_info = edd_get_payment_meta_user_info( self::$payment->ID );
		$this->assertEquals( $user_info['email'], edd_get_payment_meta( self::$payment->ID, '_edd_payment_user_email' ) );
	}

	public function test_update_payment_meta() {
		$payment = new \EDD_Payment( self::$payment->ID );
		$this->assertEquals( $payment->key, $payment->get_meta( '_edd_payment_purchase_key' ) );

		$new_value = 'test12345';
		$this->assertNotEquals( $payment->key, $new_value );

		$payment->key = $new_value;
		$ret          = $payment->save();

		$this->assertTrue( $ret );
		$this->assertEquals( $new_value, $payment->key );

		$payment->email = 'test@test.com';
		$ret            = $payment->save();

		$this->assertTrue( $ret );

		$this->assertEquals( 'test@test.com', $payment->email );
	}

	public function test_update_payment_meta_bc() {
		$old_value = self::$payment->key;
		$this->assertEquals( $old_value, edd_get_payment_meta( self::$payment->ID, '_edd_payment_purchase_key' ) );

		$new_value = 'test12345';
		$this->assertNotEquals( $old_value, $new_value );

		$ret = edd_update_payment_meta( self::$payment->ID, '_edd_payment_purchase_key', $new_value );

		$this->assertTrue( $ret );

		$this->assertEquals( $new_value, edd_get_payment_meta( self::$payment->ID, '_edd_payment_purchase_key' ) );

		$ret = edd_update_payment_meta( self::$payment->ID, '_edd_payment_user_email', 'test@test.com' );

		$this->assertTrue( $ret );

		$user_info = edd_get_payment_meta_user_info( self::$payment->ID );
		$this->assertEquals( 'test@test.com', edd_get_payment_meta( self::$payment->ID, '_edd_payment_user_email' ) );
	}

	public function test_update_payment_data() {
		self::$payment->date = date( 'Y-m-d H:i:s' );
		self::$payment->save();
		$meta = self::$payment->get_meta();

		$this->assertSame( self::$payment->date, $meta['date'] );
	}

	public function test_get_payment_currency_code() {
		$this->assertEquals( 'USD', self::$payment->currency );
		$this->assertEquals( 'US Dollars (&#36;)', edd_get_payment_currency( self::$payment->ID ) );

		$total1 = edd_currency_filter( edd_format_amount( self::$payment->total ), self::$payment->currency );
		$total2 = edd_currency_filter( edd_format_amount( self::$payment->total ) );

		$this->assertEquals( '&#36;120.00', $total1 );
		$this->assertEquals( '&#36;120.00', $total2 );
	}

	public function test_get_payment_currency_code_bc() {
		$this->assertEquals( 'USD', edd_get_payment_currency_code( self::$payment->ID ) );
		$this->assertEquals( 'US Dollars (&#36;)', edd_get_payment_currency( self::$payment->ID ) );

		$total1 = edd_currency_filter( edd_format_amount( edd_get_payment_amount( self::$payment->ID ) ), edd_get_payment_currency_code( self::$payment->ID ) );
		$total2 = edd_currency_filter( edd_format_amount( edd_get_payment_amount( self::$payment->ID ) ) );

		$this->assertEquals( '&#36;120.00', $total1 );
		$this->assertEquals( '&#36;120.00', $total2 );
	}

	public function test_is_guest_payment() {
		$this->assertFalse( edd_is_guest_payment( self::$payment->ID ) );

		$guest_payment_id = Helpers\EDD_Helper_Payment::create_simple_guest_payment();
		$this->assertTrue( edd_is_guest_payment( $guest_payment_id ) );
	}

	public function test_is_guest_payment_with_order() {
		$order = edd_get_order(  self::$payment->ID );
		$this->assertFalse( edd_is_guest_payment( $order ) );

		$guest_payment_id = Helpers\EDD_Helper_Payment::create_simple_guest_payment();
		$guest_order      = edd_get_order( $guest_payment_id );
		$this->assertTrue( edd_is_guest_payment( $guest_order ) );
	}

	public function test_get_payment() {
		$payment = edd_get_payment( self::$payment->ID );
		$this->assertTrue( property_exists( $payment, 'ID' ) );
		$this->assertTrue( property_exists( $payment, 'cart_details' ) );
		$this->assertTrue( property_exists( $payment, 'user_info' ) );
		$this->assertEquals( $payment->ID, self::$payment->ID );
		$payment->transaction_id = 'a1b2c3d4e5';
		$payment->save();

		$payment_2 = edd_get_payment( 'a1b2c3d4e5', true );
		$this->assertTrue( property_exists( $payment_2, 'ID' ) );
		$this->assertTrue( property_exists( $payment_2, 'cart_details' ) );
		$this->assertTrue( property_exists( $payment_2, 'user_info' ) );
		$this->assertEquals( $payment_2->ID, self::$payment->ID );
	}

	public function test_payments_date_query() {
		/**
		 * @internal There's a caching issue when running the test suite so we have to clear everything at the beginning
		 *           of this test.
		 */
		$component = edd_get_component_interface( 'order', 'table' );

		if ( $component instanceof \EDD\Database\Table ) {
			$component->truncate();
		}

		$payment_id_1 = Helpers\EDD_Helper_Payment::create_simple_payment_with_date( date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ) );
		Helpers\EDD_Helper_Payment::create_simple_payment_with_date( date( 'Y-m-d H:i:s', strtotime( '-4 days' ) ) );
		Helpers\EDD_Helper_Payment::create_simple_payment_with_date( date( 'Y-m-d H:i:s', strtotime( '-5 days' ) ) );
		Helpers\EDD_Helper_Payment::create_simple_payment_with_date( date( 'Y-m-d H:i:s', strtotime( '-1 month' ) ) );

		$payments_query = new \EDD_Payments_Query( array(
			'start_date' => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'end_date'   => date( 'Y-m-d H:i:s' ),
			'output'     => 'orders',
		) );

		$payments = $payments_query->get_payments();

		$this->assertEquals( 1, count( $payments ) );
		$this->assertEquals( $payment_id_1, $payments[0]->ID );

		self::$payment = edd_get_payment( Helpers\EDD_Helper_Payment::create_simple_payment() );
	}
}
