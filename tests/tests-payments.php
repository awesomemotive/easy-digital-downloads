<?php

/**
 * @group edd_payments
 */
class Tests_Payments extends WP_UnitTestCase {

	protected $_payment_id = null;
	protected $_key = null;
	protected $_post = null;
	protected $_payment_key = null;

	public function setUp() {

		global $edd_options;

		parent::setUp();

		$payment_id         = EDD_Helper_Payment::create_simple_payment();
		$purchase_data      = edd_get_payment_meta( $payment_id );
		$this->_payment_key = edd_get_payment_key( $payment_id );

		$this->_payment_id = $payment_id;
		$this->_key = $this->_payment_key;

		$this->_transaction_id = 'FIR3SID3';
		edd_set_payment_transaction_id( $payment_id, $this->_transaction_id );
		edd_insert_payment_note( $payment_id, sprintf( __( 'PayPal Transaction ID: %s', 'easy-digital-downloads' ) , $this->_transaction_id ) );

		// Make sure we're working off a clean object caching in WP Core.
		// Prevents some payment_meta from not being present.
		clean_post_cache( $payment_id );
		update_postmeta_cache( array( $payment_id ) );
	}

	public function tearDown() {

		parent::tearDown();
		EDD_Helper_Payment::delete_payment( $this->_payment_id );
		wp_cache_flush();

	}

	public function test_get_payments() {
		$out = edd_get_payments();
		$this->assertTrue( is_array( (array) $out[0] ) );
		$this->assertArrayHasKey( 'ID', (array) $out[0] );
		$this->assertArrayHasKey( 'post_type', (array) $out[0] );
		$this->assertEquals( 'edd_payment', $out[0]->post_type );
	}

	public function test_payments_query_edd_payments() {
		$payments = new EDD_Payments_Query( array( 'output' => 'edd_payments' ) );
		$out = $payments->get_payments();
		$this->assertTrue( is_object( $out[0] ) );
		$this->assertTrue( property_exists( $out[0], 'ID' ) );
		$this->assertTrue( property_exists( $out[0], 'cart_details' ) );
		$this->assertTrue( property_exists( $out[0], 'user_info' ) );
	}

	public function test_payments_query_payments() {
		$payments = new EDD_Payments_Query( array( 'output' => 'payments' ) );
		$out = $payments->get_payments();
		$this->assertTrue( is_object( $out[0] ) );
		$this->assertTrue( property_exists( $out[0], 'ID' ) );
		$this->assertTrue( property_exists( $out[0], 'cart_details' ) );
		$this->assertTrue( property_exists( $out[0], 'user_info' ) );
	}

	public function test_payments_query_default() {
		$payments = new EDD_Payments_Query;
		$out = $payments->get_payments();
		$this->assertTrue( is_object( $out[0] ) );
		$this->assertTrue( property_exists( $out[0], 'ID' ) );
		$this->assertTrue( property_exists( $out[0], 'cart_details' ) );
		$this->assertTrue( property_exists( $out[0], 'user_info' ) );
	}

	public function test_payments_query_search_discount() {
		$payment_id = EDD_Helper_Payment::create_simple_payment( array( 'discount' => 'ZERO' ) );

		$payments_query = new EDD_Payments_Query( array( 's' => 'discount:ZERO' ) );
		$out = $payments_query->get_payments();
		$this->assertEquals( 1, count( $out ) );
		$this->assertEquals( $payment_id, $out[0]->ID );

		EDD_Helper_Payment::delete_payment( $payment_id );

		$payments_query = new EDD_Payments_Query( array( 's' => 'discount:ZERO' ) );
		$out = $payments_query->get_payments();
		$this->assertEquals( 0, count( $out ) );
	}

	public function test_edd_get_payment_by() {
		$payment = edd_get_payment_by( 'id', $this->_payment_id );
		$this->assertObjectHasAttribute( 'ID', $payment );

		$payment = edd_get_payment_by( 'key', $this->_key );
		$this->assertObjectHasAttribute( 'ID', $payment );
	}

	public function test_fake_insert_payment() {
		$this->assertFalse( edd_insert_payment() );
	}

	public function test_payment_completed_flag_not_exists() {

		$completed_date = edd_get_payment_completed_date( $this->_payment_id );
		$this->assertEmpty( $completed_date );

	}

	public function test_update_payment_status() {
		edd_update_payment_status( $this->_payment_id, 'publish' );

		$out = edd_get_payments();
		$this->assertEquals( 'publish', $out[0]->post_status );
	}

	public function test_update_payment_status_with_invalid_id() {
		$updated = edd_update_payment_status( 1212121212121212121212112, 'publish' );
		$this->assertFalse( $updated );
	}

	public function test_check_for_existing_payment() {
		edd_update_payment_status( $this->_payment_id, 'publish' );
		$this->assertTrue( edd_check_for_existing_payment( $this->_payment_id ) );
	}

	public function test_get_payment_status() {
		$this->assertEquals( 'pending', edd_get_payment_status( $this->_payment_id ) );
		$this->assertEquals( 'pending', edd_get_payment_status( get_post( $this->_payment_id ) ) );
		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEquals( 'pending', edd_get_payment_status( $payment ) );
		$this->assertFalse( edd_get_payment_status( 1212121212121 ) );
	}

	public function test_get_payment_status_label() {
		$this->assertEquals( 'Pending', edd_get_payment_status( $this->_payment_id, true ) );
		$this->assertEquals( 'Pending', edd_get_payment_status( get_post( $this->_payment_id ), true ) );
		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEquals( 'Pending', edd_get_payment_status( $payment, true ) );
	}

	public function test_get_payment_statuses() {
		$out = edd_get_payment_statuses();

		$expected = array(
			'pending'   => 'Pending',
			'publish'   => 'Complete',
			'refunded'  => 'Refunded',
			'failed'    => 'Failed',
			'revoked'   => 'Revoked',
			'abandoned' => 'Abandoned'
		);

		$this->assertEquals( $expected, $out );
	}

	public function test_get_payment_status_keys() {
		$out = edd_get_payment_status_keys();

		$expected = array(
			'abandoned',
			'failed',
			'pending',
			'publish',
			'refunded',
			'revoked'
		);

		$this->assertInternalType( 'array', $out );
		$this->assertEquals( $expected, $out );
	}

	public function test_delete_purchase() {
		edd_delete_purchase( $this->_payment_id );
		// This returns an empty array(), so empty makes it false
		$cart = edd_get_payments();
		$this->assertTrue( empty( $cart ) );
	}

	public function test_get_payment_completed_date() {

		edd_update_payment_status( $this->_payment_id, 'publish' );
		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertInternalType( 'string', $payment->completed_date );
		$this->assertEquals( date( 'Y-m-d' ), date( 'Y-m-d', strtotime( $payment->completed_date ) ) );

	}

	public function test_get_payment_completed_date_bc() {

		edd_update_payment_status( $this->_payment_id, 'publish' );
		$completed_date = edd_get_payment_completed_date( $this->_payment_id );
		$this->assertInternalType( 'string', $completed_date );
		$this->assertEquals( date( 'Y-m-d' ), date( 'Y-m-d', strtotime( $completed_date ) ) );

	}

	public function test_get_payment_number() {
		// Reset all items and start from scratch
		EDD_Helper_Payment::delete_payment( $this->_payment_id );
		wp_cache_flush();

		global $edd_options;
		$edd_options['enable_sequential'] = 1;

		$payment_id = EDD_Helper_Payment::create_simple_payment();

		$this->assertInternalType( 'int', edd_get_next_payment_number() );
		$this->assertInternalType( 'string', edd_format_payment_number( edd_get_next_payment_number() ) );
		$this->assertEquals( 'EDD-2', edd_format_payment_number( edd_get_next_payment_number() ) );

		$payment             = new EDD_Payment( $payment_id );
		$last_payment_number = edd_remove_payment_prefix_postfix( $payment->number );
		$this->assertEquals( 1, $last_payment_number );
		$this->assertEquals( 'EDD-1', $payment->number );
		$this->assertEquals( 2, edd_get_next_payment_number() );

		// Now disable sequential and ensure values come back as expected
		$edd_options['enable_sequential'] = 0;

		$payment = new EDD_Payment( $payment_id );
		$this->assertEquals( $payment_id, $payment->number );
	}

	public function test_get_payment_transaction_id() {
		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEquals( $this->_transaction_id, $payment->transaction_id );
	}

	public function test_get_payment_transaction_id_bc() {
		$this->assertEquals( $this->_transaction_id, edd_get_payment_transaction_id( $this->_payment_id ) );
	}

	public function test_get_payment_transaction_id_legacy() {
		$this->assertEquals( $this->_transaction_id, edd_paypal_get_payment_transaction_id( $this->_payment_id ) );
	}

	public function test_get_payment_meta() {

		$payment = new EDD_Payment( $this->_payment_id );

		// Test by getting the payment key with three different methods
		$this->assertEquals( $this->_payment_key, $payment->get_meta( '_edd_payment_purchase_key' ) );
		$this->assertEquals( $this->_payment_key, get_post_meta( $this->_payment_id, '_edd_payment_purchase_key', true ) );
		$this->assertEquals( $this->_payment_key, $payment->key );

		// Try and retrieve the transaction ID
		$this->assertEquals( $this->_transaction_id, $payment->get_meta( '_edd_payment_transaction_id' ) );

		$this->assertEquals( $payment->email, $payment->get_meta( '_edd_payment_user_email' ) );

	}

	public function test_get_payment_meta_bc() {

		// Test by getting the payment key with three different methods
		$this->assertEquals( $this->_payment_key, edd_get_payment_meta( $this->_payment_id, '_edd_payment_purchase_key' ) );
		$this->assertEquals( $this->_payment_key, get_post_meta( $this->_payment_id, '_edd_payment_purchase_key', true ) );
		$this->assertEquals( $this->_payment_key, edd_get_payment_key( $this->_payment_id ) );

		// Try and retrieve the transaction ID
		$this->assertEquals( $this->_transaction_id, edd_get_payment_meta( $this->_payment_id, '_edd_payment_transaction_id' ) );

		$user_info = edd_get_payment_meta_user_info( $this->_payment_id );
		$this->assertEquals( $user_info['email'], edd_get_payment_meta( $this->_payment_id, '_edd_payment_user_email' ) );

	}

	public function test_update_payment_meta() {

		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEquals( $payment->key, $payment->get_meta( '_edd_payment_purchase_key' ) );

		$new_value = 'test12345';
		$this->assertNotEquals( $payment->key, $new_value );

		$payment->key = $new_value;
		$ret = $payment->save();

		$this->assertTrue( $ret );
		$this->assertEquals( $new_value, $payment->key );

		$payment->email = 'test@test.com';
		$ret = $payment->save();

		$this->assertTrue( $ret );

		$this->assertEquals( 'test@test.com', $payment->email );

	}

	public function test_update_payment_meta_bc() {

		$old_value = $this->_payment_key;
		$this->assertEquals( $old_value, edd_get_payment_meta( $this->_payment_id, '_edd_payment_purchase_key' ) );

		$new_value = 'test12345';
		$this->assertNotEquals( $old_value, $new_value );

		$ret = edd_update_payment_meta( $this->_payment_id, '_edd_payment_purchase_key', $new_value );

		$this->assertTrue( $ret );

		$this->assertEquals( $new_value, edd_get_payment_meta( $this->_payment_id, '_edd_payment_purchase_key' ) );

		$ret = edd_update_payment_meta( $this->_payment_id, '_edd_payment_user_email', 'test@test.com' );

		$this->assertTrue( $ret );

		$user_info = edd_get_payment_meta_user_info( $this->_payment_id );
		$this->assertEquals( 'test@test.com', edd_get_payment_meta( $this->_payment_id, '_edd_payment_user_email' ) );

	}

	public function test_update_payment_data() {

		$payment = new EDD_Payment( $this->_payment_id );
		$payment->date = date( 'Y-n-d H:i:s' );
		$payment->save();
		$meta = $payment->get_meta();

		$this->assertSame( $payment->date, $meta['date'] );


	}

	public function test_get_payment_currency_code() {

		$payment = new EDD_Payment( $this->_payment_id );

		$this->assertEquals( 'USD', $payment->currency );
		$this->assertEquals( 'US Dollars (&#36;)', edd_get_payment_currency( $payment->ID ) );

		$total1 = edd_currency_filter( edd_format_amount( $payment->total ), $payment->currency );
		$total2 = edd_currency_filter( edd_format_amount( $payment->total ) );

		$this->assertEquals( '&#36;120.00', $total1 );
		$this->assertEquals( '&#36;120.00', $total2 );

	}

	public function test_get_payment_currency_code_bc() {

		$this->assertEquals( 'USD', edd_get_payment_currency_code( $this->_payment_id ) );
		$this->assertEquals( 'US Dollars (&#36;)', edd_get_payment_currency( $this->_payment_id ) );

		$total1 = edd_currency_filter( edd_format_amount( edd_get_payment_amount( $this->_payment_id ) ), edd_get_payment_currency_code( $this->_payment_id ) );
		$total2 = edd_currency_filter( edd_format_amount( edd_get_payment_amount( $this->_payment_id ) ) );

		$this->assertEquals( '&#36;120.00', $total1 );
		$this->assertEquals( '&#36;120.00', $total2 );

	}

	public function test_is_guest_payment() {
		// setUp defines a payment with a known user, use this
		$this->assertFalse( edd_is_guest_payment( $this->_payment_id ) );

		// Create a guest payment
		$guest_payment_id   = EDD_Helper_Payment::create_simple_guest_payment();
		$this->assertTrue( edd_is_guest_payment( $guest_payment_id ) );
	}

}
