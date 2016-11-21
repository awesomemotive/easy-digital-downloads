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
		edd_insert_payment_note( $payment_id, sprintf( __( 'PayPal Transaction ID: %s', 'easy-digital-downloads' ) , $this->_transaction_id ) );

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

	public function test_add_download_zero_item_price() {

		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEquals( 2, count( $payment->downloads ) );
		$this->assertEquals( 120.00, $payment->total );

		$new_download = EDD_Helper_Download::create_simple_download();

		$args = array(
			'item_price' => 0,
		);

		$payment->add_download( $new_download->ID, $args );
		$payment->save();

		$this->assertEquals( 3, count( $payment->downloads ) );
		$this->assertEquals( 120.00, $payment->total );

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

		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEquals( 5, $payment->fees_total );
		$this->assertEquals( 125, $payment->total );

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

	public function test_for_searlized_user_info() {
		// Issue #4248
		$payment = new EDD_Payment( $this->_payment_id );
		$payment->user_info = serialize( array( 'first_name' => 'John', 'last_name' => 'Doe' ) );
		// Save re-runs the setup process
		$payment->save();

		$this->assertInternalType( 'array', $payment->user_info );
		foreach ( $payment->user_info as $key => $value ) {
			$this->assertFalse( is_serialized( $value ), $key . ' returned a searlized value' );
		}
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

	public function test_refund_payment() {
		$payment  = new EDD_Payment( $this->_payment_id );
		$payment->status = 'complete';
		$payment->save();

		$download = new EDD_Download( $payment->downloads[0]['id'] );
		$earnings = $download->earnings;
		$sales    = $download->sales;

		$store_earnings = edd_get_total_earnings();
		$store_sales    = edd_get_total_sales();

		$payment->refund();

		wp_cache_flush();

		$status = get_post_status( $payment->ID );
		$this->assertEquals( 'refunded', $status );
		$this->assertEquals( 'refunded', $payment->status );

		$download2 = new EDD_Download( $download->ID );

		$this->assertEquals( $earnings - $download->price, $download2->earnings );
		$this->assertEquals( $sales - 1, $download2->sales );

		$this->assertEquals( $store_earnings - $payment->total, edd_get_total_earnings() );
		$this->assertEquals( $store_sales - 1, edd_get_total_sales() );
	}

	public function test_refund_payment_legacy() {
		$payment  = new EDD_Payment( $this->_payment_id );
		$payment->status = 'complete';
		$payment->save();

		$download = new EDD_Download( $payment->downloads[0]['id'] );
		$earnings = $download->earnings;
		$sales    = $download->sales;

		edd_undo_purchase_on_refund( $payment->ID, 'refunded', 'publish' );

		wp_cache_flush();

		$payment = new EDD_Payment( $this->_payment_id );
		$status  = get_post_status( $payment->ID );
		$this->assertEquals( 'refunded', $status );
		$this->assertEquals( 'refunded', $payment->status );

		$download2 = new EDD_Download( $download->ID );

		$this->assertEquals( $earnings - $download->price, $download2->earnings );
		$this->assertEquals( $sales - 1, $download2->sales );

	}

	public function test_remove_with_multi_price_points_by_price_id() {
		EDD_Helper_Payment::delete_payment( $this->_payment_id );

		$download = EDD_Helper_Download::create_variable_download_with_multi_price_purchase();
		$payment  = new EDD_Payment();

		$payment->add_download( $download->ID, array( 'price_id' => 0 ) );
		$payment->add_download( $download->ID, array( 'price_id' => 1 ) );
		$payment->add_download( $download->ID, array( 'price_id' => 2 ) );
		$payment->add_download( $download->ID, array( 'price_id' => 3 ) );

		$this->assertEquals( 4, count( $payment->downloads ) );
		$this->assertEquals( 620, $payment->total );

		$payment->status = 'complete';
		$payment->save();

		$payment->remove_download( $download->ID, array( 'price_id' => 1 ) );
		$payment->save();

		$this->assertEquals( 3, count( $payment->downloads ) );

		$this->assertEquals( 0, $payment->downloads[0]['options']['price_id'] );
		$this->assertEquals( 0, $payment->cart_details[0]['item_number']['options']['price_id'] );

		$this->assertEquals( 2, $payment->downloads[1]['options']['price_id'] );
		$this->assertEquals( 2, $payment->cart_details[2]['item_number']['options']['price_id'] );

		$this->assertEquals( 3, $payment->downloads[2]['options']['price_id'] );
		$this->assertEquals( 3, $payment->cart_details[3]['item_number']['options']['price_id'] );
	}

	public function test_remove_with_multi_price_points_by_cart_index() {
		EDD_Helper_Payment::delete_payment( $this->_payment_id );

		$download = EDD_Helper_Download::create_variable_download_with_multi_price_purchase();
		$payment  = new EDD_Payment();

		$payment->add_download( $download->ID, array( 'price_id' => 0 ) );
		$payment->add_download( $download->ID, array( 'price_id' => 1 ) );
		$payment->add_download( $download->ID, array( 'price_id' => 2 ) );
		$payment->add_download( $download->ID, array( 'price_id' => 3 ) );

		$this->assertEquals( 4, count( $payment->downloads ) );
		$this->assertEquals( 620, $payment->total );

		$payment->status = 'complete';
		$payment->save();

		$payment->remove_download( $download->ID, array( 'cart_index' => 1 ) );
		$payment->remove_download( $download->ID, array( 'cart_index' => 2 ) );
		$payment->save();

		$this->assertEquals( 2, count( $payment->downloads ) );

		$this->assertEquals( 0, $payment->downloads[0]['options']['price_id'] );
		$this->assertEquals( 0, $payment->cart_details[0]['item_number']['options']['price_id'] );

		$this->assertEquals( 3, $payment->downloads[1]['options']['price_id'] );
		$this->assertEquals( 3, $payment->cart_details[3]['item_number']['options']['price_id'] );

	}

	public function test_remove_with_multiple_same_price_by_price_id_different_prices() {
		EDD_Helper_Payment::delete_payment( $this->_payment_id );

		$download = EDD_Helper_Download::create_variable_download_with_multi_price_purchase();
		$payment  = new EDD_Payment();

		$payment->add_download( $download->ID, array( 'price_id' => 0, 'item_price' => 10 ) );
		$payment->add_download( $download->ID, array( 'price_id' => 0, 'item_price' => 20 ) );
		$payment->add_download( $download->ID, array( 'price_id' => 0, 'item_price' => 30 ) );

		$this->assertEquals( 3, count( $payment->downloads ) );
		$this->assertEquals( 60, $payment->total );

		$payment->status = 'complete';
		$payment->save();

		$payment->remove_download( $download->ID, array( 'price_id' => 0, 'item_price' => 20 ) );
		$payment->save();

		$this->assertEquals( 2, count( $payment->downloads ) );

		$this->assertEquals( 0, $payment->downloads[0]['options']['price_id'] );
		$this->assertEquals( 0, $payment->cart_details[0]['item_number']['options']['price_id'] );
		$this->assertEquals( 10, $payment->cart_details[0]['item_price'] );

		$this->assertEquals( 0, $payment->downloads[1]['options']['price_id'] );
		$this->assertEquals( 0, $payment->cart_details[2]['item_number']['options']['price_id'] );
		$this->assertEquals( 30, $payment->cart_details[2]['item_price'] );

	}

	public function test_remove_with_multiple_same_price_by_price_id_same_prices() {
		EDD_Helper_Payment::delete_payment( $this->_payment_id );

		$download = EDD_Helper_Download::create_variable_download_with_multi_price_purchase();
		$payment  = new EDD_Payment();

		$payment->add_download( $download->ID, array( 'price_id' => 0, 'item_price' => 10 ) );
		$payment->add_download( $download->ID, array( 'price_id' => 0, 'item_price' => 10 ) );
		$payment->add_download( $download->ID, array( 'price_id' => 0, 'item_price' => 10 ) );

		$this->assertEquals( 3, count( $payment->downloads ) );
		$this->assertEquals( 30, $payment->total );

		$payment->status = 'complete';
		$payment->save();

		$payment->remove_download( $download->ID, array( 'price_id' => 0, 'item_price' => 10 ) );
		$payment->save();

		$this->assertEquals( 2, count( $payment->downloads ) );

		$this->assertEquals( 0, $payment->downloads[0]['options']['price_id'] );
		$this->assertEquals( 0, $payment->cart_details[1]['item_number']['options']['price_id'] );
		$this->assertEquals( 10, $payment->cart_details[1]['item_price'] );

		$this->assertEquals( 0, $payment->downloads[1]['options']['price_id'] );
		$this->assertEquals( 0, $payment->cart_details[2]['item_number']['options']['price_id'] );
		$this->assertEquals( 10, $payment->cart_details[2]['item_price'] );

	}

	public function test_refund_affecting_stats() {
		$payment         = new EDD_Payment( $this->_payment_id );
		$payment->status = 'complete';
		$payment->save();

		$customer = new EDD_Customer( $payment->customer_id );
		$download = new EDD_Download( $payment->downloads[0]['id'] );

		$customer_sales    = $customer->purchase_count;
		$customer_earnings = $customer->purchase_value;

		$download_sales    = $download->sales;
		$download_earnings = $download->earnings;

		$store_earnings    = edd_get_total_earnings();
		$store_sales       = edd_get_total_sales();

		$payment->refund();
		wp_cache_flush();

		$customer = new EDD_Customer( $payment->customer_id );
		$download = new EDD_Download( $payment->downloads[0]['id'] );

		$this->assertEquals( $customer_earnings - $payment->total, $customer->purchase_value );
		$this->assertEquals( $customer_sales - 1, $customer->purchase_count );

		$this->assertEquals( $download_earnings - $payment->cart_details[0]['price'], $download->earnings );
		$this->assertEquals( $download_sales - $payment->downloads[0]['quantity'], $download->sales );

		$this->assertEquals( $store_earnings - $payment->total, edd_get_total_earnings() );
		$this->assertEquals( $store_sales - 1, edd_get_total_sales() );
	}

	public function test_refund_without_affecting_stats() {
		add_filter( 'edd_decrease_earnings_on_undo', '__return_false' );
		add_filter( 'edd_decrease_sales_on_undo', '__return_false' );
		add_filter( 'edd_decrease_customer_value_on_refund', '__return_false' );
		add_filter( 'edd_decrease_customer_purchase_count_on_refund', '__return_false' );
		add_filter( 'edd_decrease_store_earnings_on_refund', '__return_false' );

		$payment         = new EDD_Payment( $this->_payment_id );
		$payment->status = 'complete';
		$payment->save();

		$customer = new EDD_Customer( $payment->customer_id );
		$download = new EDD_Download( $payment->downloads[0]['id'] );

		$customer_sales    = $customer->purchase_count;
		$customer_earnings = $customer->purchase_value;

		$download_sales    = $download->sales;
		$download_earnings = $download->earnings;

		$store_earnings    = edd_get_total_earnings();
		$store_sales       = edd_get_total_sales();

		$payment->refund();
		wp_cache_flush();

		$customer = new EDD_Customer( $payment->customer_id );
		$download = new EDD_Download( $payment->downloads[0]['id'] );

		$this->assertEquals( $customer_earnings, $customer->purchase_value );
		$this->assertEquals( $customer_sales, $customer->purchase_count );

		$this->assertEquals( $download_earnings, $download->earnings );
		$this->assertEquals( $download_sales, $download->sales );

		$this->assertEquals( $store_earnings, edd_get_total_earnings() );
		// Store sales are based off 'publish' & 'revoked' status. So it reduces this count
		$this->assertEquals( $store_sales - 1, edd_get_total_sales() );

		remove_filter( 'edd_decrease_earnings_on_undo', '__return_false' );
		remove_filter( 'edd_decrease_sales_on_undo', '__return_false' );
		remove_filter( 'edd_decrease_customer_value_on_refund', '__return_false' );
		remove_filter( 'edd_decrease_customer_purchase_count_on_refund', '__return_false' );
		remove_filter( 'edd_decrease_store_earnings_on_refund', '__return_false ' );
	}

	public function test_pending_affecting_stats() {
		$payment         = new EDD_Payment( $this->_payment_id );
		$payment->status = 'complete';
		$payment->save();

		$customer = new EDD_Customer( $payment->customer_id );
		$download = new EDD_Download( $payment->downloads[0]['id'] );

		$customer_sales    = $customer->purchase_count;
		$customer_earnings = $customer->purchase_value;

		$download_sales    = $download->sales;
		$download_earnings = $download->earnings;

		$store_earnings    = edd_get_total_earnings();
		$store_sales       = edd_get_total_sales();

		$payment->status = 'pending';
		$payment->save();
		wp_cache_flush();

		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEmpty( $payment->completed_date );

		$customer = new EDD_Customer( $payment->customer_id );
		$download = new EDD_Download( $payment->downloads[0]['id'] );

		$this->assertEquals( $customer_earnings - $payment->total, $customer->purchase_value );
		$this->assertEquals( $customer_sales - 1, $customer->purchase_count );

		$this->assertEquals( $download_earnings - $payment->cart_details[0]['price'], $download->earnings );
		$this->assertEquals( $download_sales - $payment->downloads[0]['quantity'], $download->sales );

		$this->assertEquals( $store_earnings - $payment->total, edd_get_total_earnings() );
		$this->assertEquals( $store_sales - 1, edd_get_total_sales() );
	}

	public function test_pending_without_affecting_stats() {
		add_filter( 'edd_decrease_earnings_on_undo', '__return_false' );
		add_filter( 'edd_decrease_sales_on_undo', '__return_false' );
		add_filter( 'edd_decrease_customer_value_on_pending', '__return_false' );
		add_filter( 'edd_decrease_customer_purchase_count_on_pending', '__return_false' );
		add_filter( 'edd_decrease_store_earnings_on_pending', '__return_false' );

		$payment         = new EDD_Payment( $this->_payment_id );
		$payment->status = 'complete';
		$payment->save();

		$customer = new EDD_Customer( $payment->customer_id );
		$download = new EDD_Download( $payment->downloads[0]['id'] );

		$customer_sales    = $customer->purchase_count;
		$customer_earnings = $customer->purchase_value;

		$download_sales    = $download->sales;
		$download_earnings = $download->earnings;

		$store_earnings    = edd_get_total_earnings();
		$store_sales       = edd_get_total_sales();

		$payment->status = 'pending';
		$payment->save();
		wp_cache_flush();

		$payment = new EDD_Payment( $this->_payment_id );
		$this->assertEmpty( $payment->completed_date );

		$customer = new EDD_Customer( $payment->customer_id );
		$download = new EDD_Download( $payment->downloads[0]['id'] );

		$this->assertEquals( $customer_earnings, $customer->purchase_value );
		$this->assertEquals( $customer_sales, $customer->purchase_count );

		$this->assertEquals( $download_earnings, $download->earnings );
		$this->assertEquals( $download_sales, $download->sales );

		$this->assertEquals( $store_earnings, edd_get_total_earnings() );
		// Store sales are based off 'publish' & 'revoked' status. So it reduces this count
		$this->assertEquals( $store_sales - 1, edd_get_total_sales() );

		remove_filter( 'edd_decrease_earnings_on_undo', '__return_false' );
		remove_filter( 'edd_decrease_sales_on_undo', '__return_false' );
		remove_filter( 'edd_decrease_customer_value_on_pending', '__return_false' );
		remove_filter( 'edd_decrease_customer_purchase_count_on_pending', '__return_false' );
		remove_filter( 'edd_decrease_store_earnings_on_pending', '__return_false ' );
	}

	public function test_failed_payment_discount() {

		$id   = EDD_Helper_Discount::create_simple_percent_discount();
		$uses = edd_get_discount_uses( $id );

		$payment = new EDD_Payment( $this->_payment_id );
		$payment->discounts = array( '20OFF' );
		$payment->save();

		$payment->status = 'complete';
		$payment->save();

		$new_complete = edd_get_discount_uses( $id );

		$this->assertEquals( $uses + 1, $new_complete );

		$payment->status = 'failed';
		$payment->save();

		$new_failed = edd_get_discount_uses( $id );
		$this->assertEquals( $uses, $new_failed );

	}

	public function test_user_id_mismatch() {
		update_post_meta( $this->_payment_id, '_edd_payment_user_id', 99999 );
		$payment  = new EDD_Payment( $this->_payment_id );
		$customer = new EDD_Customer( $payment->customer_id );

		$this->assertEquals( $payment->user_id, $customer->user_id );
	}

	public function test_filtering_payment_meta() {
		add_filter( 'edd_payment_meta', array( $this, 'alter_payment_meta' ), 10, 2 );
		$payment_id         = EDD_Helper_Payment::create_simple_payment();
		remove_filter( 'edd_payment_meta', array( $this, 'alter_payment_meta' ), 10, 2 );

		$payment = new EDD_Payment( $payment_id );
		$this->assertEquals( 'PL', $payment->payment_meta['user_info']['address']['country'] );
	}

	public function test_modifying_address() {
		$payment_id = EDD_Helper_Payment::create_simple_payment();
		$payment    = new EDD_Payment( $payment_id );
		$payment->address = array(
			'line1'   => '123 Main St',
			'line2'   => '',
			'city'    => 'New York City',
			'state'   => 'New York',
			'zip'     => '10010',
			'country' => 'US',
		);
		$payment->save();

		$payment_2 = new EDD_Payment( $payment_id );
		$this->assertEquals( $payment_2->address, $payment_2->user_info['address'] );
	}

	// https://github.com/easydigitaldownloads/easy-digital-downloads/issues/5228
	public function test_issue_5228_data() {
		$payment         = new EDD_Payment( $this->_payment_id );
		$meta            = $payment->get_meta();
		$meta[0]['test'] = 'Test Value';
		update_post_meta( $payment->ID, '_edd_payment_meta', $meta );

		$direct_meta = get_post_meta( $payment->ID, '_edd_payment_meta', $meta );
		$this->assertTrue( isset( $direct_meta[0] ) );

		$payment = new EDD_Payment( $payment->ID );
		$meta    = $payment->get_meta();
		$this->assertFalse( isset( $meta[0] ) );
		$this->assertTrue( isset( $meta['test'] ) );
		$this->assertEquals( 'Test Value', $meta['test'] );

	}

	/** Helpers **/
	public function alter_payment_meta( $meta, $payment_data ) {
		$meta['user_info']['address']['country'] = 'PL';

		return $meta;
	}
}
