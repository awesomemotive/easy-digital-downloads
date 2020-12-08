<?php
namespace EDD\Orders;

/**
 * EDD_Payment Tests.
 *
 * @group edd_orders
 * @group edd_payment
 *
 * @coversDefaultClass \EDD_Payment
 */

class EDD_Payment_Tests extends \EDD_UnitTestCase {

	/**
	 * Payment test fixture.
	 *
	 * @var \EDD_Payment
	 */
	protected $payment;

	public function setUp() {
		parent::setUp();

		$payment_id = \EDD_Helper_Payment::create_simple_payment();

		$this->payment = edd_get_payment( $payment_id );
	}

	public function tearDown() {
		parent::tearDown();

		\EDD_Helper_Payment::delete_payment( $this->payment->ID );

		edd_destroy_order( $this->payment->ID );

		$component = edd_get_component_interface( 'order', 'meta' );

		if ( $component instanceof \EDD\Database\Table ) {
			$component->truncate();
		}

		$this->payment = null;
	}

	public function test_IDs() {
		$this->assertSame( $this->payment->_ID, $this->payment->ID );
	}

	public function test_saving_updated_ID() {
		$expected = $this->payment->ID;

		$this->payment->ID = 12121222;
		$this->payment->save();

		$this->assertSame( $expected, $this->payment->ID );
	}

	public function test_EDD_Payment_total() {
		$this->assertEquals( 120.00, $this->payment->total );
	}

	public function test_edd_get_payment_by_transaction_ID_should_be_true() {
		$payment = edd_get_payment( 'EDD_ORDER', true );

		$this->assertSame( $payment->ID, $this->payment->ID );
		$this->assertSame( 'EDD_ORDER', $payment->transaction_id );
	}

	public function test_edd_get_payment_by_transaction_ID_for_guest_payment_should_be_true() {
		$payment_id = \EDD_Helper_Payment::create_simple_guest_payment();

		$payment = edd_get_payment( 'EDD_GUEST_ORDER', true );

		$this->assertSame( 'EDD_GUEST_ORDER', $payment->transaction_id );

		\EDD_Helper_Payment::delete_payment( $payment_id );
	}

	public function test_instantiating_EDD_Payment_with_no_args_should_be_null() {
		$payment = new \EDD_Payment();
		$this->assertEquals( NULL, $payment->ID );
	}

	public function test_edd_get_payment_with_no_args_should_be_false() {
		$payment = edd_get_payment();

		$this->assertFalse( $payment );
	}

	public function test_edd_get_payment_with_invalid_id_should_be_false() {
		$payment = edd_get_payment( 99999999999 );

		$this->assertFalse( $payment );
	}

	public function test_instantiating_EDD_Payment_with_invalid_transaction_id_should_be_null() {
		$payment = new \EDD_Payment( 'false-txn', true );

		$this->assertEquals( NULL, $payment->ID );
	}

	public function test_edd_get_payment_with_invalid_transaction_id_should_be_false() {
		$payment = edd_get_payment( 'false-txn', true );

		$this->assertFalse( $payment );
	}

	public function test_updating_payment_status_to_pending() {
		$this->payment->update_status( 'pending' );
		$this->assertEquals( 'pending', $this->payment->status );
		$this->assertEquals( 'Pending', $this->payment->status_nicename );
	}

	public function test_updating_payment_status_to_publish() {
		// Test backwards compat
		edd_update_payment_status( $this->payment->ID, 'complete' );

		// Need to get the payment again since it's been updated
		$this->payment = edd_get_payment( $this->payment->ID );
		$this->assertEquals( 'complete', $this->payment->status );
		$this->assertEquals( 'Completed', $this->payment->status_nicename );
	}

	public function test_add_download() {

		// Test class vars prior to adding a download.
		$this->assertEquals( 2, count( $this->payment->downloads ) );
		$this->assertEquals( 120.00, $this->payment->total );

		$new_download = \EDD_Helper_Download::create_simple_download();

		$this->payment->add_download( $new_download->ID );
		$this->payment->save();

		$this->assertEquals( 3, count( $this->payment->downloads ) );
		$this->assertEquals( 140.00, $this->payment->total );
	}

	public function test_add_download_with_an_item_price_of_0() {

		// Test class vars prior to adding a download.
		$this->assertEquals( 2, count( $this->payment->downloads ) );
		$this->assertEquals( 120.00, $this->payment->total );

		$new_download = \EDD_Helper_Download::create_simple_download();

		$args = array(
			'item_price' => 0,
		);

		$this->payment->add_download( $new_download->ID, $args );
		$this->payment->save();

		$this->assertEquals( 3, count( $this->payment->downloads ) );
		$this->assertEquals( 120.00, $this->payment->total );
	}

	public function test_add_download_with_fee() {
		$args = array(
			'fees' => array(
				array(
					'amount' => 5,
					'label'  => 'Test Fee',
				),
			),
		);

		$new_download = \EDD_Helper_Download::create_simple_download();

		$this->payment->add_download( $new_download->ID, $args );
		$this->payment->save();

		$this->assertFalse( empty( $this->payment->cart_details[2]['fees'] ) );
	}

	public function test_remove_download() {
		$download_id = $this->payment->cart_details[0]['id'];
		$amount      = $this->payment->cart_details[0]['price'];
		$quantity    = $this->payment->cart_details[0]['quantity'];

		$remove_args = array(
			'amount'   => $amount,
			'quantity' => $quantity,
		);

		$this->payment->remove_download( $download_id, $remove_args );
		$this->payment->save();

		$this->assertEquals( 1, count( $this->payment->downloads ) );
		$this->assertEquals( 100.00, $this->payment->total );
	}

	public function test_remove_download_by_index() {
		$download_id = $this->payment->cart_details[1]['id'];

		$remove_args = array(
			'cart_index' => 1,
		);

		$this->payment->remove_download( $download_id, $remove_args );
		$this->payment->save();

		$this->assertEquals( 1, count( $this->payment->downloads ) );
		$this->assertEquals( 20.00, $this->payment->total );
	}

	public function test_remove_download_with_quantity() {
		global $edd_options;

		$edd_options['item_quantities'] = true;

		$payment_id = \EDD_Helper_Payment::create_simple_payment_with_quantity_tax();

		$payment = edd_get_payment( $payment_id );

		$testing_index = 1;
		$download_id   = $payment->cart_details[ $testing_index ]['id'];

		$remove_args = array(
			'quantity' => 1,
		);

		$payment->remove_download( $download_id, $remove_args );
		$payment->save();

		$payment = edd_get_payment( $payment_id );

		$this->assertEquals( 2, count( $payment->downloads ) );
		$this->assertEquals( 1, $payment->cart_details[ $testing_index ]['quantity'] );
		$this->assertEquals( 140.00, $payment->subtotal );
		$this->assertEquals( 12, $payment->tax );
		$this->assertEquals( 152.00, $payment->total );

		\EDD_Helper_Payment::delete_payment( $payment_id );
		unset( $edd_options['item_quantities'] );
	}

	public function test_payment_add_fee() {
		$this->payment->add_fee( array(
			'amount' => 5,
			'label'  => 'Test Fee 1',
		) );

		$this->assertEquals( 1, count( $this->payment->fees ) );
		$this->assertEquals( 125, $this->payment->total );

		$this->payment->save();

		$this->payment = edd_get_payment( $this->payment->ID );

		$this->assertEquals( 5, $this->payment->fees_total );
		$this->assertEquals( 125, $this->payment->total );

		// Test backwards compatibility with _edd_payment_meta.
		$payment_meta = edd_get_payment_meta( $this->payment->ID, '_edd_payment_meta', true );
		$this->assertArrayHasKey( 'fees', $payment_meta );

		$fees = $payment_meta['fees'];
		$this->assertEquals( 1, count( $fees ) );
	}

	public function test_user_info() {
		$this->assertSame( 'Admin', $this->payment->first_name );
		$this->assertSame( 'User', $this->payment->last_name );
	}

	public function test_for_serialized_user_info() {

		// Issue #4248
		$this->payment->user_info = serialize( array(
			'first_name' => 'John',
			'last_name' => 'Doe',
		) );

		$this->payment->save();

		$this->assertInternalType( 'array', $this->payment->user_info );

		foreach ( $this->payment->user_info as $key => $value ) {
			$this->assertFalse( is_serialized( $value ), $key . ' returned a searlized value' );
		}
	}

	public function test_modify_amount() {
		$args = array(
			'item_price' => '1,001.95',
		);

		$this->payment->modify_cart_item( 0, $args );
		$this->payment->save();

		$this->assertEquals( 1001.95, $this->payment->cart_details[0]['price'] );
	}

	public function test_payment_remove_fee() {
		for ( $i = 0; $i <= 2; $i++ ) {
			$this->payment->add_fee( array(
				'amount' => 5,
				'label'  => 'Test Fee ' . $i,
				'type'   => 'fee',
			) );
		}

		$this->payment->save();

		$this->assertEquals( 3, count( $this->payment->fees ) );
		$this->assertEquals( 'Test Fee 1', $this->payment->fees[1]['label'] );
		$this->assertEquals( 135, $this->payment->total );

		$this->payment->remove_fee( 1 );
		$this->payment->save();

		$this->assertEquals( 2, count( $this->payment->fees ) );
		$this->assertEquals( 130, $this->payment->total );
		$this->assertEquals( 'Test Fee 2', $this->payment->fees[1]['label'] );

		// Test that it saves to the DB
		$payment_meta = edd_get_payment_meta( $this->payment->ID, '_edd_payment_meta', true );

		$this->assertArrayHasKey( 'fees', $payment_meta );

		$fees = $payment_meta['fees'];

		$this->assertEquals( 2, count( $fees ) );
		$this->assertEquals( 'Test Fee 2', $fees[2]['label'] );
	}

	public function test_payment_remove_fee_by_index() {
		for ( $i = 0; $i <= 2; $i++ ) {
			$this->payment->add_fee( array(
				'amount' => 5,
				'label'  => 'Test Fee ' . $i,
				'type'   => 'fee',
			) );
		}

		$this->payment->save();

		$this->assertEquals( 3, count( $this->payment->fees ) );
		$this->assertEquals( 'Test Fee 1', $this->payment->fees[1]['label'] );
		$this->assertEquals( 135, $this->payment->total );

		$this->payment->remove_fee_by( 'index', 1, true );
		$this->payment->save();

		$this->assertEquals( 2, count( $this->payment->fees ) );
		$this->assertEquals( 130, $this->payment->total );
		$this->assertEquals( 'Test Fee 2', $this->payment->fees[1]['label'] );

		// Test that it saves to the DB
		$payment_meta = edd_get_payment_meta( $this->payment->ID, '_edd_payment_meta', true );

		$this->assertArrayHasKey( 'fees', $payment_meta );

		$fees = $payment_meta['fees'];

		$this->assertEquals( 2, count( $fees ) );
		$this->assertEquals( 'Test Fee 2', $fees[2]['label'] );
	}

	public function test_payment_remove_fee_by_label_should_be_empty() {
		$this->payment->add_fee( array(
			'amount' => 5,
			'label'  => 'Test Fee',
			'type'   => 'fee',
		) );

		$this->assertEquals( 1, count( $this->payment->fees ) );
		$this->assertEquals( 'Test Fee', $this->payment->fees[0]['label'] );
		$this->payment->save();

		$this->payment->remove_fee_by( 'label', 'Test Fee' );
		$this->assertEmpty( $this->payment->fees );
		$this->assertEquals( 120, $this->payment->total );
		$this->payment->save();

		// Test that it saves to the DB
		$payment_meta = edd_get_payment_meta( $this->payment->ID, '_edd_payment_meta', true );
		$this->assertArrayHasKey( 'fees', $payment_meta );

		$fees = $payment_meta['fees'];
		$this->assertEmpty( $fees );
	}

	public function test_payment_remove_fee_by_label_w_multi_no_global() {
		$this->payment->add_fee( array(
			'amount' => 5,
			'label'  => 'Test Fee',
			'type'   => 'fee',
		) );

		$this->payment->add_fee( array(
			'amount' => 5,
			'label'  => 'Test Fee',
			'type'   => 'fee',
		) );

		$this->assertEquals( 2, count( $this->payment->fees ) );
		$this->assertEquals( 'Test Fee', $this->payment->fees[0]['label'] );
		$this->payment->save();

		$this->payment->remove_fee_by( 'label', 'Test Fee' );
		$this->assertEquals( 1, count( $this->payment->fees ) );
		$this->assertEquals( 125, $this->payment->total );
		$this->payment->save();

		$payment_meta = edd_get_payment_meta( $this->payment->ID, '_edd_payment_meta', true );
		$this->assertArrayHasKey( 'fees', $payment_meta );

		$fees = $payment_meta['fees'];
		$this->assertEquals( 1, count( $fees ) );
	}

	public function test_payment_remove_fee_by_label_w_multi_w_global() {
		$this->payment->add_fee( array(
			'amount' => 5,
			'label'  => 'Test Fee',
			'type'   => 'fee',
		) );

		$this->payment->add_fee( array(
			'amount' => 5,
			'label'  => 'Test Fee',
			'type'   => 'fee',
		) );

		$this->assertEquals( 2, count( $this->payment->fees ) );
		$this->assertEquals( 'Test Fee', $this->payment->fees[0]['label'] );
		$this->payment->save();

		$this->payment->remove_fee_by( 'label', 'Test Fee', true );
		$this->assertEmpty( $this->payment->fees );
		$this->assertEquals( 120, $this->payment->total );
		$this->payment->save();

		$payment_meta = edd_get_payment_meta( $this->payment->ID, '_edd_payment_meta', true );
		$this->assertArrayHasKey( 'fees', $payment_meta );
		$this->assertEmpty( $payment_meta['fees'] );
	}

	public function test_payment_with_initial_fee() {
		$payment_id = \EDD_Helper_Payment::create_simple_payment_with_fee();

		$payment = edd_get_payment( $payment_id );

		$this->assertFalse( empty( $payment->fees ) );
		$this->assertEquals( 47, $payment->total );
	}

	public function test_update_date_future() {
		$current_date = $this->payment->date;

		$new_date = strtotime( $this->payment->date ) + DAY_IN_SECONDS;
		$this->payment->date = date( 'Y-m-d H:i:s', $new_date );
		$this->payment->save();

		$date2 = strtotime( $this->payment->date );
		$this->assertEquals( $new_date, $date2 );
	}

	public function test_update_date_past() {
		$new_date = strtotime( $this->payment->date ) - DAY_IN_SECONDS;

		$this->payment->date = date( 'Y-m-d H:i:s', $new_date );
		$this->payment->save();

		$date2 = strtotime( $this->payment->date );
		$this->assertEquals( $new_date, $date2 );
	}

	public function test_refund_payment() {
		$this->payment->status = 'complete';
		$this->payment->save();

		$download = new \EDD_Download( $this->payment->downloads[0]['id'] );
		$earnings = $download->earnings;
		$sales    = $download->sales;

		$store_earnings = edd_get_total_earnings();
		$store_sales    = edd_get_total_sales();

		$this->payment->refund();

		wp_cache_flush();

		$this->assertEquals( 'refunded', $this->payment->status );

		$download2 = new \EDD_Download( $download->ID );

		$this->assertEquals( $earnings - $download->price, $download2->earnings );
		$this->assertEquals( $sales - 1, $download2->sales );

		$this->assertEquals( $store_earnings - $this->payment->total, edd_get_total_earnings() );
		$this->assertEquals( $store_sales - 1, edd_get_total_sales() );
	}

	/**
	 * @expectEDDeprecated edd_undo_purchase_on_refund
	 */
	public function test_refund_payment_legacy() {
		$this->payment->status = 'complete';
		$this->payment->save();

		$download = new \EDD_Download( $this->payment->downloads[0]['id'] );
		$earnings = $download->earnings;
		$sales    = $download->sales;

		edd_undo_purchase_on_refund( $this->payment->ID, 'refunded', 'complete' );

		wp_cache_flush();

		$payment = edd_get_payment( $this->payment->ID );
		$this->assertEquals( 'refunded', $payment->status );

		$download2 = new \EDD_Download( $download->ID );

		$this->assertEquals( $earnings - $download->price, $download2->earnings );
		$this->assertEquals( $sales - 1, $download2->sales );
	}

	public function test_modifying_address() {
		$new_address = array(
			'line1'   => '123 Main St',
			'line2'   => '',
			'city'    => 'New York City',
			'state'   => 'New York',
			'zip'     => '10010',
			'country' => 'US',
		);
		$this->payment->address = $new_address;
		$this->payment->save();

		$this->assertEquals( $new_address, $this->payment->address );
	}

	public function test_modify_cart_item_price() {
		$this->payment->status = 'complete';
		$this->payment->save();

		$this->payment->modify_cart_item( 0, array( 'item_price' => 1 ) );
		$this->payment->save();

		$this->assertEquals( 1, $this->payment->cart_details[0]['item_price'] );

		$download = new \EDD_Download( $this->payment->cart_details[0]['id'] );
		$this->assertEquals( 41, $download->get_earnings() );
	}

	public function test_modify_cart_item_quantity() {
		$this->payment->status = 'complete';
		$this->payment->save();

		$this->payment->modify_cart_item( 0, array(
			'quantity'   => 3,
			'item_price' => 1,
		) );
		$this->payment->save();

		$this->assertEquals( 3, $this->payment->cart_details[0]['quantity'] );
		$this->assertEquals( 3, $this->payment->cart_details[0]['price'] );

		$download = new \EDD_Download( $this->payment->cart_details[0]['id'] );
		$this->assertEquals( 43, $download->get_earnings() );
	}

	public function test_modify_cart_item_tax() {
		$this->payment->status = 'complete';
		$this->payment->save();

		$this->payment->modify_cart_item( 0, array( 'tax' => 2 ) );
		$this->payment->save();

		$this->assertEquals( 2, $this->payment->cart_details[0]['tax'] );
		$this->assertEquals( 2, $this->payment->tax );
	}

	public function test_modify_cart_item_discount() {
		$this->payment->status = 'complete';
		$this->payment->save();

		$original_total = $this->payment->cart_details[0]['price'];
		$this->payment->modify_cart_item( 0, array( 'discount' => 1 ) );
		$this->payment->save();

		$this->assertEquals( 1, $this->payment->cart_details[0]['discount'] );
		$this->assertEquals( $original_total - 1, $this->payment->cart_details[0]['price'] );
		$this->assertEquals( 1, $this->payment->discounted_amount );
	}

	public function test_modify_cart_item_with_disallowed_changes_should_return_false() {
		$this->payment->status = 'complete';
		$this->payment->save();

		$change_permitted = $this->payment->modify_cart_item( 0, array(
			'quantity'   => $this->payment->cart_details[0]['quantity'],
			'item_price' => $this->payment->cart_details[0]['price'],
		) );

		$this->assertFalse( $change_permitted );
	}

	public function test_filtering_payment_meta() {
		add_filter( 'edd_payment_meta', array( $this, 'alter_payment_meta' ), 10, 2 );

		$payment_id = \EDD_Helper_Payment::create_simple_payment();

		remove_filter( 'edd_payment_meta', array( $this, 'alter_payment_meta' ), 10, 2 );

		$payment = edd_get_payment( $payment_id );

		$this->assertEquals( 'PL', $payment->payment_meta['user_info']['address']['country'] );
	}

	/**
	 * @see https://github.com/easydigitaldownloads/easy-digital-downloads/issues/5228
	 */
	public function test_issue_5228_data() {
		$this->setExpectedIncorrectUsage( 'get_post_meta()' );
		$this->setExpectedIncorrectUsage( 'add_post_meta()/update_post_meta()' );

		$meta = $this->payment->get_meta();

		$meta[0]         = array();
		$meta[0]['test'] = 'Test Value';

		update_post_meta( $this->payment->ID, '_edd_payment_meta', $meta );

		$direct_meta = get_post_meta( $this->payment->ID, '_edd_payment_meta', $meta );
		$this->assertTrue( isset( $direct_meta ) );

		$payment = edd_get_payment( $this->payment->ID );
		$meta    = $payment->get_meta();

		$this->assertFalse( isset( $meta[0] ) );
		$this->assertTrue( isset( $meta['test'] ) );
		$this->assertEquals( 'Test Value', $meta['test'] );
	}

	public function test_user_id_mismatch() {
		$this->setExpectedIncorrectUsage( 'add_post_meta()/update_post_meta()' );

		update_post_meta( $this->payment->ID, '_edd_payment_user_id', 99999 );
		$payment  = edd_get_payment( $this->payment->ID );
		$customer = edd_get_customer( $payment->customer_id );

		$this->assertEquals( $payment->user_id, $customer->user_id );
	}

	public function test_pending_affecting_stats() {
		$this->payment->status = 'complete';
		$this->payment->save();

		$customer = new \EDD_Customer( $this->payment->customer_id );
		$download = new \EDD_Download( $this->payment->downloads[0]['id'] );

		$customer_sales    = $customer->purchase_count;
		$customer_earnings = $customer->purchase_value;

		$download_sales    = $download->sales;
		$download_earnings = $download->earnings;

		$store_earnings = edd_get_total_earnings();
		$store_sales    = edd_get_total_sales();

		$this->payment->status = 'pending';
		$this->payment->save();
		wp_cache_flush();

		$this->assertEmpty( $this->payment->completed_date );

		$customer = new \EDD_Customer( $this->payment->customer_id );
		$download = new \EDD_Download( $this->payment->downloads[0]['id'] );

		$this->assertEquals( $customer_earnings - $this->payment->total, $customer->purchase_value );
		$this->assertEquals( $customer_sales - 1, $customer->purchase_count );

		$this->assertEquals( $download_earnings - $this->payment->cart_details[0]['price'], $download->earnings );
		$this->assertEquals( $download_sales - $this->payment->downloads[0]['quantity'], $download->sales );

		$this->assertEquals( $store_earnings - $this->payment->total, edd_get_total_earnings() );
		$this->assertEquals( $store_sales - 1, edd_get_total_sales() );
	}

	public function test_refund_affecting_stats() {

		$this->payment->status = 'complete';
		$this->payment->save();

		$customer          = new \EDD_Customer( $this->payment->customer_id );
		$customer_sales    = $customer->purchase_count;
		$customer_earnings = $customer->purchase_value;

		$download = new \EDD_Download( $this->payment->downloads[0]['id'] );

		$download_sales    = $download->sales;
		$download_earnings = $download->earnings;

		$store_earnings = edd_get_total_earnings();
		$store_sales    = edd_get_total_sales();

		$this->payment->refund();
		$customer->recalculate_stats();
		wp_cache_flush();

		$download = new \EDD_Download( $this->payment->downloads[0]['id'] );

		$this->assertEquals( $customer_earnings - $this->payment->total, $customer->purchase_value );
		$this->assertEquals( $customer_sales - 1, $customer->purchase_count );

		$this->assertEquals( $download_earnings - $this->payment->cart_details[0]['price'], $download->earnings );
		$this->assertEquals( $download_sales - $this->payment->downloads[0]['quantity'], $download->sales );

		$this->assertEquals( $store_earnings - $this->payment->total, edd_get_total_earnings() );
		$this->assertEquals( $store_sales - 1, edd_get_total_sales() );
	}

	public function test_remove_with_multi_price_points_by_price_id() {
		$download = \EDD_Helper_Download::create_variable_download_with_multi_price_purchase();
		$payment  = new \EDD_Payment();

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
		$download = \EDD_Helper_Download::create_variable_download_with_multi_price_purchase();
		$payment  = new \EDD_Payment();

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
		$download = \EDD_Helper_Download::create_variable_download_with_multi_price_purchase();
		$payment  = new \EDD_Payment();

		$payment->add_download( $download->ID, array(
			'price_id'   => 0,
			'item_price' => 10,
		) );

		$payment->add_download( $download->ID, array(
			'price_id'   => 0,
			'item_price' => 20,
		) );

		$payment->add_download( $download->ID, array(
			'price_id'   => 0,
			'item_price' => 30,
		) );

		$this->assertEquals( 3, count( $payment->downloads ) );
		$this->assertEquals( 60, $payment->total );

		$payment->status = 'complete';
		$payment->save();

		$payment->remove_download( $download->ID, array(
			'price_id'   => 0,
			'item_price' => 20,
		) );

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
		$download = \EDD_Helper_Download::create_variable_download_with_multi_price_purchase();
		$payment  = new \EDD_Payment();

		$payment->add_download( $download->ID, array(
			'price_id'   => 0,
			'item_price' => 10,
		) );

		$payment->add_download( $download->ID, array(
			'price_id'   => 0,
			'item_price' => 10,
		) );

		$payment->add_download( $download->ID, array(
			'price_id'   => 0,
			'item_price' => 10,
		) );

		$this->assertEquals( 3, count( $payment->downloads ) );
		$this->assertEquals( 30, $payment->total );

		$payment->status = 'complete';
		$payment->save();

		$payment->remove_download( $download->ID, array(
			'price_id'   => 0,
			'item_price' => 10,
		) );

		$payment->save();

		$this->assertEquals( 2, count( $payment->downloads ) );

		$this->assertEquals( 0, $payment->downloads[0]['options']['price_id'] );
		$this->assertEquals( 0, $payment->cart_details[1]['item_number']['options']['price_id'] );
		$this->assertEquals( 10, $payment->cart_details[1]['item_price'] );

		$this->assertEquals( 0, $payment->downloads[1]['options']['price_id'] );
		$this->assertEquals( 0, $payment->cart_details[2]['item_number']['options']['price_id'] );
		$this->assertEquals( 10, $payment->cart_details[2]['item_price'] );

	}

	/**
	 * Ensures that setting a dynamic tax rate to a payment saves that in order meta and can be retrieved.
	 *
	 * This is testing backwards compatibility, when in 2.x you could set a tax rate without a referencing
	 * ID. We need to ensure this is accessible in 3.x when using an order object.
	 */
	public function test_setting_dynamic_tax_rate_saves_rate_in_order_meta() {
		$payment = $this->payment;
		$payment->tax_rate = 0.2;

		$tax_amount = $payment->total * 0.2;
		$payment->increase_tax( $tax_amount );
		$payment->save();

		// Now fetch the order equivalent.
		$order = edd_get_order( $payment->ID );

		$this->assertEquals( 20, $order->get_tax_rate() );
		$this->assertEquals( $tax_amount, $order->tax );

		$this->assertEquals( 20, edd_get_order_meta( $payment->ID, 'tax_rate', true ) );
	}

	/**
	 * In 2.x, tax rates are stored as decimals. In 3.x they are stored as percentages. When using
	 * EDD_Payment you should always get a decimal back.
	 */
	public function test_get_tax_rate_returns_decimal() {
		$payment = new \EDD_Payment();
		$payment->tax_rate = 0.2;
		$payment->total    = 10;

		$tax_amount = $payment->total * 0.2;
		$payment->increase_tax( $tax_amount );
		$payment->save();

		// Fetch a new payment object.
		$payment = edd_get_payment( $payment->ID );
		$this->assertEquals( 0.2, $payment->tax_rate );
	}

	/* Helpers ***************************************************************/

	public function alter_payment_meta( $meta, $payment_data ) {
		$meta['user_info']['address']['country'] = 'PL';

		return $meta;
	}

	public function add_meta() {
		$this->assertTrue( $this->payment->add_meta( '_test_add_payment_meta', 'test' ) );
	}

	public function add_meta_false_empty_key() {
		$this->assertFalse( $this->payment->add_meta( '', 'test' ) );
	}

	public function add_meta_unique_false() {
		$this->assertFalse( $this->payment->add_meta( '_edd_payment_key', 'test', true ) );
	}

	public function delete_meta() {
		$this->assertTrue( $this->payment->delete_meta( '_edd_payment_key' ) );
	}

	public function delete_meta_no_key() {
		$this->assertFalse( $this->payment->delete_meta( '' ) );
	}

	public function delete_meta_missing_key() {
		$this->assertFalse( $this->payment->delete_meta( '_edd_nonexistant_key' ) );
	}
}
