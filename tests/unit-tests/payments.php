<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_payments
 */
class Tests_Payments extends EDD_UnitTestCase {
	protected $_payment_id = null;

	protected $_post = null;

	public function setUp() {
		parent::setUp();

		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );

		$_variable_pricing = array(
			array(
				'name' => 'Simple',
				'amount' => 20
			),
			array(
				'name' => 'Advanced',
				'amount' => 100
			)
		);

		$_download_files = array(
			array(
				'name' => 'File 1',
				'file' => 'http://localhost/file1.jpg',
				'condition' => 0
			),
			array(
				'name' => 'File 2',
				'file' => 'http://localhost/file2.jpg',
				'condition' => 'all'
			)
		);

		$meta = array(
			'edd_price' => '0.00',
			'_variable_pricing' => 1,
			'_edd_price_options_mode' => 'on',
			'edd_variable_prices' => array_values( $_variable_pricing ), 
			'edd_download_files' => array_values( $_download_files ),
			'_edd_download_limit' => 20,
			'_edd_hide_purchase_link' => 1,
			'edd_product_notes' => 'Purchase Notes',
			'_edd_product_type' => 'default',
			'_edd_download_earnings' => 129.43,
			'_edd_download_sales' => 59,
			'_edd_download_limit_override_1' => 1
		);
		foreach( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		$this->_post = get_post( $post_id );

		/** Generate some sales */
		$user = get_userdata(1);

		$user_info = array(
			'id' => $user->ID,
			'email' => $user->user_email,
			'first_name' => $user->first_name,
			'last_name' => $user->last_name,
			'discount' => 'none'
		);

		$download_details = array(
			array(
				'id' => $this->_post->ID,
				'options' => array(
					'price_id' => 1
				)
			)
		);

		$price = '100.00';

		$total = 0;

		$prices = get_post_meta($download_details[0]['id'], 'edd_variable_prices', true);
		$item_price = $prices[1]['amount'];

		$total += $item_price;

		$cart_details = array(
			array(
				'name' => 'Test Download',
				'id' => $this->_post->ID,
				'item_number' => array(
					'id' => $this->_post->ID,
					'options' => array(
						'price_id' => 1
					)
				),
				'price' =>  100,
				'quantity' => 1
			)
		);

		$purchase_data = array(
			'price' => number_format( (float) $total, 2 ),
			'date' => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'purchase_key' => strtolower( md5( uniqid() ) ),
			'user_email' => $user_info['email'],
			'user_info' => $user_info,
			'currency' => 'USD',
			'downloads' => $download_details,
			'cart_details' => $cart_details,
			'status' => 'pending'
		);

		$_SERVER['REMOTE_ADDR'] = '10.0.0.0';
		$_SERVER['SERVER_NAME'] = 'edd_virtual';

		$payment_id = edd_insert_payment( $purchase_data );
		$this->_payment_id = $payment_id;
	}

	public function test_get_payments() {
		$out = edd_get_payments();
		$this->assertTrue( is_array( (array) $out[0] ) );
		$this->assertArrayHasKey( 'ID', (array) $out[0] );
		$this->assertArrayHasKey( 'post_type', (array) $out[0] );
		$this->assertEquals( 'edd_payment', $out[0]->post_type );
	}

	public function test_fake_insert_payment() {
		$this->assertFalse( edd_insert_payment() );
	}

	public function test_update_payment_status() {
		edd_update_payment_status( $this->_payment_id );

		$out = edd_get_payments();
		$this->assertEquals( 'publish', $out[0]->post_status );
	}

	public function test_check_for_existing_payment() {
		$this->assertTrue( edd_check_for_existing_payment( $this->_post->ID ) );
	}

	public function test_get_payment_statuses() {
		$out = edd_get_payment_statuses();

		$expected = array(
			'pending' => 'Pending',
			'publish' => 'Complete',
			'refunded' => 'Refunded',
			'failed' => 'Failed',
			'revoked' => 'Revoked',
			'abandoned' => 'Abandoned'
		);

		$this->assertEquals( $expected, $out );
	}

	public function test_get_earnings_by_date() {
		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Payment', 'post_type' => 'edd_payment', 'post_status' => 'publish' ) );
		$meta = array(
			'_edd_payment_total' => '120.00',
			'_edd_payment_mode' => 'live'
		);
		foreach( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}
		$this->assertEquals( 120, edd_get_earnings_by_date( null, date( 'n' ), date( 'Y' ) ) );
	}

	public function test_undo_purchase() {
		edd_undo_purchase( $this->_post->ID, $this->_payment_id );
		$this->assertEquals( 0, edd_get_total_earnings() );
	}

	public function test_delete_purchase() {
		edd_delete_purchase( $this->_payment_id );
		$this->assertFalse( edd_get_payments() );
	}
}
