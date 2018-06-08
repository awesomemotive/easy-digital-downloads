<?php
/**
 * EDD_Payment Tests.
 *
 * @group edd_payments
 *
 * @coversDefaultClass \EDD_Payment
 */
namespace EDD\Orders;

class Tests_EDD_Payment extends \EDD_UnitTestCase {

	/**
	 * Payment test fixture.
	 *
	 * @var \EDD_Payment
	 */
	protected static $payment;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		$payment_id = \EDD_Helper_Payment::create_simple_payment();

		self::$payment = edd_get_payment( $payment_id );
	}

	public function setUp() {
		parent::setUp();

		self::$payment->update_status( 'publish' );
	}

	public function test_IDs_should_be_same() {
		$this->assertSame( self::$payment->ID, self::$payment->_ID );
	}

	public function test_IDs_are_the_same_after_saving_should_be_same() {
		$payment = edd_get_payment( self::$payment->ID );

		$payment->ID = 12121222;
		$payment->save();

		$this->assertSame( $payment->ID, self::$payment->ID );
	}

	public function test_get_payment_by_transaction_ID_should_return_true() {
		$payment = edd_get_payment( 'FIR3SID3', true );

		$this->assertEquals( self::$payment->ID, $payment->ID );
	}

	public function test_instantiating_EDD_Payment_with_no_args_should_be_null() {
		$payment = new \EDD_Payment();

		$this->assertEquals( null, $payment->ID );
		$this->assertSame( 0, $payment->ID );
	}

	public function test_edd_get_payment_with_no_args_should_be_false() {
		$payment = edd_get_payment();

		$this->assertFalse( $payment );
	}

	public function test_edd_get_payment_with_invalid_id_shuould_be_false() {
		$payment = edd_get_payment( 99999999999 );

		$this->assertFalse( $payment );
	}

	public function test_instantiating_EDD_Payment_with_invalid_transaction_id_should_be_null() {
		$payment = new \EDD_Payment( 'false-txn', true );

		$this->assertEquals( NULL, $payment->ID );
		$this->assertSame( 0, $payment->ID );
	}

	public function test_edd_get_payment_with_invalid_transaction_id_shuould_be_false() {
		$payment = edd_get_payment( 'false-txn', true );

		$this->assertFalse( $payment );
	}

	public function test_payment_update_status_should_be_pending() {
		self::$payment->update_status( 'pending' );

		$this->assertSame( 'pending', self::$payment->status );
		$this->assertSame( 'Pending', self::$payment->status_nicename );
	}

	public function test_edd_update_payment_status() {
		edd_update_payment_status( self::$payment->ID, 'publish' );

		self::$payment = edd_get_payment( self::$payment->ID );

		$this->assertSame( 'publish', self::$payment->status );
		$this->assertSame( 'Completed', self::$payment->status_nicename );
	}

	public function test_EDD_Payment_class_vars() {
		$this->assertCount( 2, self::$payment->downloads );
		$this->assertEquals( 120.00, self::$payment->total );
		$this->assertSame( '120.000000000', self::$payment->total ); // Total is stored as BIGINT in the database.
	}

	public function test_add_download() {
		$new_download = \EDD_Helper_Download::create_simple_download();

		self::$payment->add_download( $new_download->ID );
		self::$payment->save();

		$this->assertCount( 3, self::$payment->downloads );
		$this->assertEquals( 140.00, self::$payment->total );
	}

	public function test_add_download_with_item_price_of_0() {
		$new_download = \EDD_Helper_Download::create_simple_download();

		$args = array(
			'item_price' => 0,
		);

		self::$payment->add_download( $new_download->ID, $args );
		self::$payment->save();

		$this->assertCount( 3, self::$payment->downloads );
		$this->assertEquals( 140.00, self::$payment->total );
	}

	public function test_add_download_with_fee() {
		$args = array(
			'fees' => array(
				'test_fee' => array(
					'amount' => 5,
					'label'  => 'Test Fee',
				),
			),
		);

		$new_download = \EDD_Helper_Download::create_simple_download();

		self::$payment->add_download( $new_download->ID, $args );
		self::$payment->save();

		$this->assertFalse( empty( self::$payment->cart_details[4]['fees'] ) );
	}

	public function test_remove_download() {

	}

	public function test_remove_download_by_index() {

	}

	public function test_remove_download_with_quantity() {

	}

	public function test_payment_add_fee() {

	}

	public function test_payment_remove_fee() {

	}

	public function test_payment_remove_fee_by_label() {

	}

	public function test_payment_remove_fee_by_label_w_multi_no_global() {

	}

	public function test_payment_remove_fee_by_label_w_multi_w_global() {

	}

	public function test_payment_remove_fee_by_index() {

	}

	public function test_user_info() {
		$this->markTestIncomplete();

		$this->assertSame( 'Admin', self::$payment->first_name );
		$this->assertSame( 'User', self::$payment->last_name );
	}

	public function test_for_serialized_user_info() {
		// Issue #4248
		self::$payment->user_info = serialize( array( 'first_name' => 'John', 'last_name' => 'Doe' ) );
		self::$payment->save();

		$this->assertInternalType( 'array', self::$payment->user_info );

		foreach ( self::$payment->user_info as $key => $value ) {
			$this->assertFalse( is_serialized( $value ), $key . ' returned a searlized value' );
		}
	}

	public function test_payment_with_initial_fee() {
		add_filter( 'edd_cart_contents', '__return_true' );
		add_filter( 'edd_item_quantities_enabled', '__return_true' );

		$payment_id = \EDD_Helper_Payment::create_simple_payment_with_fee();

		$payment = edd_get_payment( $payment_id );

		$this->assertFalse( empty( $payment->fees ) );
		$this->assertEquals( 47, $payment->total );

		remove_filter( 'edd_cart_contents', '__return_true' );
		remove_filter( 'edd_item_quantities_enabled', '__return_true' );
	}

	public function test_update_date_future() {
		$new_date = strtotime( self::$payment->date ) + DAY_IN_SECONDS;

		self::$payment->date = date( 'Y-m-d H:i:s', $new_date );
		self::$payment->save();

		$date2 = strtotime( self::$payment->date );

		$this->assertEquals( $new_date, $date2 );
	}

	public function test_update_date_past() {
		$new_date = strtotime( self::$payment->date ) - DAY_IN_SECONDS;

		self::$payment->date = date( 'Y-m-d H:i:s', $new_date );
		self::$payment->save();

		$date2 = strtotime( self::$payment->date );

		$this->assertEquals( $new_date, $date2 );
	}

	public function test_refund_payment() {
		$download = new \EDD_Download( self::$payment->downloads[0]['id'] );
		$earnings = $download->earnings;
		$sales    = $download->sales;

		$store_earnings = edd_get_total_earnings();
		$store_sales    = edd_get_total_sales();

		self::$payment->refund();

		wp_cache_flush();

		$this->assertEquals( 'refunded', self::$payment->status );

		$download2 = new \EDD_Download( $download->ID );

		$this->assertEquals( $earnings - $download->price, $download2->earnings );
		$this->assertEquals( $sales - 1, $download2->sales );

		$this->assertEquals( $store_earnings - self::$payment->total, edd_get_total_earnings() );
		$this->assertEquals( $store_sales - 1, edd_get_total_sales() );
	}
}
