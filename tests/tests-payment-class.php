<?php

use \EDD_Payments_Query;
/**
 * @group edd_payments
 */
class Tests_Payment_Class extends WP_UnitTestCase {

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
		edd_insert_payment_note( $payment_id, sprintf( __( 'PayPal Transaction ID: %s', 'edd' ) , $this->_transaction_id ) );

		// Make sure we're working off a clean object caching in WP Core.
		// Prevents some payment_meta from not being present.
		clean_post_cache( $payment_id );
		update_postmeta_cache( array( $payment_id ) );
	}

	public function tearDown() {

		parent::tearDown();

		EDD_Helper_Payment::delete_payment( $this->_payment_id );

	}

	public function test_get_existing_payment() {
		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEquals( $this->_payment_id, $payment->ID );
		$this->assertEquals( 120.00, $payment->total );
	}

	public function test_getting_no_payment() {
		$payment = new EDD_Payment();
		$this->assertEquals( NULL, $payment->ID );

		$payment = new EDD_Payment( 99999999999 );
		$this->assertEquals( NULL, $payment->ID );
	}

	public function test_payment_status_update() {
		$payment = new EDD_Payment( $this->_payment_id );
		$payment->update_status( 'pending' );
		$this->assertEquals( 'pending', $payment->status );

		// Test backwards compat
		edd_update_payment_status( $this->_payment_id, 'publish' );

		// Need to get the payment again since it's been updated
		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEquals( 'publish', $payment->status );
	}

	public function test_add_download() {
		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEquals( 2, count( $payment->downloads ) );
		$this->assertEquals( 120.00, $payment->total );

		$new_download   = EDD_Helper_Download::create_simple_download();

		$payment->add_download( $new_download->ID );
		$payment->save();

		$this->assertEquals( 3, count( $payment->downloads ) );
		$this->assertEquals( 140.00, $payment->total );
	}

	public function test_remove_download() {
		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEquals( 2, count( $payment->downloads ) );
		$this->assertEquals( 120.00, $payment->total );

		$download_id = $payment->cart_details[0]['id'];
		$amount      = $payment->cart_details[0]['price'];
		$quantity    = $payment->cart_details[0]['quantity'];

		$remove_args = array( 'amount' => $amount, 'quantity' => $quantity );
		$payment->remove_download( $download_id, array( 'amount' => $amount, 'quantity' => $quantity ) );
		$payment->save();

		$this->assertEquals( 1, count( $payment->downloads ) );
		$this->assertEquals( 100.00, $payment->total );
	}

}
