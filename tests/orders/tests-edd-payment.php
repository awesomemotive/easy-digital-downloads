<?php
namespace EDD\Orders;

/**
 * EDD_Payment Tests.
 *
 * @group edd_orders
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

		// Make sure we're working off a clean object caching in WP Core.
		// Prevents some payment_meta from not being present.
		clean_post_cache( $payment_id );
		update_postmeta_cache( array( $payment_id ) );
	}

	public function tearDown() {
		parent::tearDown();

		\EDD_Helper_Payment::delete_payment( $this->payment->ID );

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
		$payment = edd_get_payment( 'FIR3SID3', true );

		$this->assertEquals( $payment->ID, $this->payment->ID );
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
		edd_update_payment_status( $this->payment->ID, 'publish' );

		// Need to get the payment again since it's been updated
		$this->payment = edd_get_payment( $this->payment->ID );
		$this->assertEquals( 'publish', $this->payment->status );
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

		EDD_Helper_Payment::delete_payment( $payment_id );
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

	public function test_modify_amount() {
		$args = array(
			'item_price' => '1,001.95',
		);

		$this->payment->modify_cart_item( 0, $args );
		$this->payment->save();

		$this->assertEquals( 1001.95, $this->payment->cart_details[0]['price'] );
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
