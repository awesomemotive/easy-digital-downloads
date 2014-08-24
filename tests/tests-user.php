<?php


/**
 * @group edd_user
 */
class Tests_User extends WP_UnitTestCase {
	protected $_post_id = null;

	protected $_user_id = null;

	public function setUp() {
		parent::setUp();
	}

	public function test_users_purchases() {
		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );
		$this->_post_id = $post_id;

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

		/** Generate some sales */
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$this->_user_id = $user_id;
		$user = get_userdata( $user_id );

		$user_info = array(
			'id' => $user->ID,
			'email' => $user->user_email,
			'first_name' => $user->first_name,
			'last_name' => $user->last_name,
			'discount' => 'none'
		);

		$download_details = array(
			array(
				'id' => $post_id,
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
				'id' => $this->_post_id,
				'item_number' => array(
					'id' => $this->_post_id,
					'options' => array(
						'price_id' => 1
					)
				),
				'price' =>  100,
				'quantity' => 1,
				'tax' => 0
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
			'status' => 'pending',
			'tax'    => '0.00'
		);

		$_SERVER['REMOTE_ADDR'] = '10.0.0.0';
		$_SERVER['SERVER_NAME'] = 'edd_virtual';

		$payment_id = edd_insert_payment( $purchase_data );

		edd_update_payment_status( $payment_id, 'complete' );

		$out = edd_get_users_purchases( $user_id );

		$this->assertInternalType( 'object', $out[0] );
		$this->assertEquals( 'edd_payment', $out[0]->post_type );
		$this->assertTrue( edd_has_purchases( $user_id ) );
		$this->assertEquals( 1, edd_count_purchases_of_customer( $user_id ) );

		
		$out2 = edd_get_users_purchased_products( $user_id );
		
		$this->assertInternalType( 'array', $out2 );
		$this->assertEquals( 1, count( $out2 ) );
		$this->assertInternalType( 'object', $out2[0] );
		$this->assertEquals( $out2[0]->post_type, 'download' );

		
		$this->assertTrue( edd_has_user_purchased( $user_id, array( $post_id ), 1 ) );
		$this->assertFalse( edd_has_user_purchased( $user_id, array( 99 ), 1 ) );

		
		$purchase_stats = edd_get_purchase_stats_by_user( $user_id );
		
		$this->assertInternalType( 'array', $purchase_stats );
		$this->assertEquals( 2, count( $purchase_stats ) );
		$this->assertTrue( isset( $purchase_stats['purchases'] ) );
		$this->assertTrue( isset( $purchase_stats['total_spent'] ) );

		
		$purchase_total = edd_purchase_total_of_user( $user_id );
		
		$this->assertEquals( 100, $purchase_total );
	}

	public function test_validate_username() {
		$this->assertTrue( edd_validate_username( 'easydigitaldownloads' ) );
		$this->assertFalse( edd_validate_username( 'edd12345$%&+-!@£%^&()(*&^%$£@!' ) );
	}
}
