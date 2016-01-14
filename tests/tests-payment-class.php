<?php

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

	public function test_IDs() {
		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEquals( $this->_payment_id, $payment->ID );
		$this->assertEquals( $payment->_ID, $payment->ID );
	}

	public function test_ID_save_block() {
		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEquals( $this->_payment_id, $payment->ID );
		$payment->ID = 12121222;
		$payment->save();
		$this->assertEquals( $this->_payment_id, $payment->ID );
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
		$this->assertEquals( 'Pending', $payment->status_nicename );

		// Test backwards compat
		edd_update_payment_status( $this->_payment_id, 'publish' );

		// Need to get the payment again since it's been updated
		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEquals( 'publish', $payment->status );
		$this->assertEquals( 'Complete', $payment->status_nicename );
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

	public function test_add_download_with_fee() {
		$payment = new EDD_Payment( $this->_payment_id );
		$args = array(
			'fees' => array(
				array(
					'amount' => 5,
					'label'  => 'Test Fee',
				),
			),
		);

		$new_download   = EDD_Helper_Download::create_simple_download();

		$payment->add_download( $new_download->ID, $args );
		$payment->save();

		$this->assertFalse( empty( $payment->cart_details[2]['fees'] ) );
	}

	public function test_remove_download() {
		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEquals( 2, count( $payment->downloads ) );
		$this->assertEquals( 120.00, $payment->total );

		$download_id = $payment->cart_details[0]['id'];
		$amount      = $payment->cart_details[0]['price'];
		$quantity    = $payment->cart_details[0]['quantity'];

		$remove_args = array( 'amount' => $amount, 'quantity' => $quantity );
		$payment->remove_download( $download_id, $remove_args );
		$payment->save();

		$this->assertEquals( 1, count( $payment->downloads ) );
		$this->assertEquals( 100.00, $payment->total );
	}

	public function test_remove_download_by_index() {
		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEquals( 2, count( $payment->downloads ) );
		$this->assertEquals( 120.00, $payment->total );

		$download_id = $payment->cart_details[1]['id'];

		$remove_args = array( 'cart_index' => 1 );
		$payment->remove_download( $download_id, $remove_args );
		$payment->save();

		$this->assertEquals( 1, count( $payment->downloads ) );
		$this->assertEquals( 20.00, $payment->total );
	}

	public function test_remove_download_with_quantity() {
		global $edd_options;
		EDD_Helper_Payment::delete_payment( $this->_payment_id );

		$edd_options['item_quantities'] = true;
		$payment_id = EDD_Helper_Payment::create_simple_payment_with_quantity_tax();

		$payment = new EDD_Payment( $payment_id );
		$this->assertEquals( 2, count( $payment->downloads ) );
		$this->assertEquals( 240.00, $payment->subtotal );
		$this->assertEquals( 22, $payment->tax );
		$this->assertEquals( 262.00, $payment->total );

		$testing_index = 1;
		$download_id   = $payment->cart_details[ $testing_index ]['id'];

		$remove_args = array( 'quantity' => 1 );
		$payment->remove_download( $download_id, $remove_args );
		$payment->save();

		$payment = new EDD_Payment( $payment_id );
		$this->assertEquals( 2, count( $payment->downloads ) );
		$this->assertEquals( 1, $payment->cart_details[ $testing_index ]['quantity'] );
		$this->assertEquals( 140.00, $payment->subtotal );
		$this->assertEquals( 12, $payment->tax );
		$this->assertEquals( 152.00, $payment->total );

		EDD_Helper_Payment::delete_payment( $payment_id );
		unset( $edd_options['item_quantities'] );
	}

	public function test_payment_add_fee() {
		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEmpty( $payment->fees );
		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee 1' ) );
		$this->assertEquals( 1, count( $payment->fees ) );
		$this->assertEquals( 125, $payment->total );
		$payment->save();

		// Test that it saves to the DB
		$payment_meta = get_post_meta( $this->_payment_id, '_edd_payment_meta', true );
		$this->assertArrayHasKey( 'fees', $payment_meta );
		$fees = $payment_meta['fees'];
		$this->assertEquals( 1, count( $fees ) );
	}

	public function test_payment_remove_fee() {
		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEmpty( $payment->fees );

		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee 1', 'type' => 'fee' ) );
		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee 2', 'type' => 'fee' ) );
		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee 3', 'type' => 'fee' ) );
		$payment->save();
		$this->assertEquals( 3, count( $payment->fees ) );
		$this->assertEquals( 'Test Fee 2', $payment->fees[1]['label'] );
		$this->assertEquals( 135, $payment->total );

		$payment->remove_fee( 1 );
		$payment->save();
		$this->assertEquals( 2, count( $payment->fees ) );
		$this->assertEquals( 130, $payment->total );
		$this->assertEquals( 'Test Fee 3', $payment->fees[1]['label'] );

		// Test that it saves to the DB
		$payment_meta = get_post_meta( $this->_payment_id, '_edd_payment_meta', true );
		$this->assertArrayHasKey( 'fees', $payment_meta );
		$fees = $payment_meta['fees'];
		$this->assertEquals( 2, count( $fees ) );
		$this->assertEquals( 'Test Fee 3', $fees[1]['label'] );
	}

	public function test_payment_remove_fee_by_label() {
		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEmpty( $payment->fees );

		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee', 'type' => 'fee' ) );
		$this->assertEquals( 1, count( $payment->fees ) );
		$this->assertEquals( 'Test Fee', $payment->fees[0]['label'] );
		$payment->save();

		$payment->remove_fee_by( 'label', 'Test Fee' );
		$this->assertEmpty( $payment->fees );
		$this->assertEquals( 120, $payment->total );
		$payment->save();

		// Test that it saves to the DB
		$payment_meta = get_post_meta( $this->_payment_id, '_edd_payment_meta', true );
		$this->assertArrayHasKey( 'fees', $payment_meta );
		$fees = $payment_meta['fees'];
		$this->assertEmpty( $fees );
	}

	public function test_payment_remove_fee_by_label_w_multi_no_global() {
		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEmpty( $payment->fees );

		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee', 'type' => 'fee' ) );
		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee', 'type' => 'fee' ) );
		$this->assertEquals( 2, count( $payment->fees ) );
		$this->assertEquals( 'Test Fee', $payment->fees[0]['label'] );
		$payment->save();

		$payment->remove_fee_by( 'label', 'Test Fee' );
		$this->assertEquals( 1, count( $payment->fees ) );
		$this->assertEquals( 125, $payment->total );
		$payment->save();

		// Test that it saves to the DB
		$payment_meta = get_post_meta( $this->_payment_id, '_edd_payment_meta', true );
		$this->assertArrayHasKey( 'fees', $payment_meta );
		$fees = $payment_meta['fees'];
		$this->assertEquals( 1, count( $fees ) );
	}

	public function test_payment_remove_fee_by_label_w_multi_w_global() {
		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEmpty( $payment->fees );

		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee', 'type' => 'fee' ) );
		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee', 'type' => 'fee' ) );
		$this->assertEquals( 2, count( $payment->fees ) );
		$this->assertEquals( 'Test Fee', $payment->fees[0]['label'] );
		$payment->save();

		$payment->remove_fee_by( 'label', 'Test Fee', true );
		$this->assertEmpty( $payment->fees );
		$this->assertEquals( 120, $payment->total );
		$payment->save();

		// Test that it saves to the DB
		$payment_meta = get_post_meta( $this->_payment_id, '_edd_payment_meta', true );
		$this->assertArrayHasKey( 'fees', $payment_meta );
		$fees = $payment_meta['fees'];
		$this->assertEmpty( $fees );
	}

	public function test_payment_remove_fee_by_index() {
		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEmpty( $payment->fees );

		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee 1', 'type' => 'fee' ) );
		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee 2', 'type' => 'fee' ) );
		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee 3', 'type' => 'fee' ) );
		$this->assertEquals( 3, count( $payment->fees ) );
		$this->assertEquals( 'Test Fee 2', $payment->fees[1]['label'] );
		$payment->save();

		$payment->remove_fee_by( 'index', 1, true );
		$this->assertEquals( 2, count( $payment->fees ) );
		$this->assertEquals( 130, $payment->total );
		$this->assertEquals( 'Test Fee 3', $payment->fees[1]['label'] );
		$payment->save();

		// Test that it saves to the DB
		$payment_meta = get_post_meta( $this->_payment_id, '_edd_payment_meta', true );
		$this->assertArrayHasKey( 'fees', $payment_meta );
		$fees = $payment_meta['fees'];
		$this->assertEquals( 2, count( $fees ) );
		$this->assertEquals( 'Test Fee 3', $fees[1]['label'] );
	}

	public function test_user_info() {
		$payment = new EDD_Payment( $this->_payment_id );

		$this->assertEquals( 'Admin', $payment->first_name );
		$this->assertEquals( 'User', $payment->last_name );
	}

	public function test_payment_with_initial_fee() {
		EDD_Helper_Payment::delete_payment( $this->_payment_id );

		add_filter( 'edd_cart_contents', '__return_true' );
		add_filter( 'edd_item_quantities_enabled', '__return_true' );
		$payment_id = EDD_Helper_Payment::create_simple_payment_with_fee();

		$payment = new EDD_Payment( $payment_id );
		$this->assertFalse( empty( $payment->fees ) );
		$this->assertEquals( 47, $payment->total );

		remove_filter( 'edd_cart_contents', '__return_true' );
		remove_filter( 'edd_item_quantities_enabled', '__return_true' );
	}

	public function test_update_date_future() {
		$payment      = new EDD_Payment( $this->_payment_id );
		$current_date = $payment->date;

		$new_date = strtotime( $payment->date ) + DAY_IN_SECONDS;
		$payment->date = date( 'Y-m-d H:i:s', $new_date );
		$payment->save();

		$date2    = strtotime( $payment->date );
		$this->assertEquals( $new_date, $date2 );
	}

	public function test_update_date_past() {
		$payment      = new EDD_Payment( $this->_payment_id );
		$current_date = $payment->date;

		$new_date = strtotime( $payment->date ) - DAY_IN_SECONDS;
		$payment->date = date( 'Y-m-d H:i:s', $new_date );
		$payment->save();

		$date2    = strtotime( $payment->date );
		$this->assertEquals( $new_date, $date2 );
	}
}
