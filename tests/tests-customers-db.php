<?php

/**
 * @group edd_customers
 */
class Tests_Customers_DB extends EDD_UnitTestCase {

	/**
	 * Download fixture.
	 *
	 * @var int
	 */
	protected static $post_id;

	/**
	 * User fixture.
	 *
	 * @var int
	 */
	protected static $user_id;

	protected $_customer_id = null;

	public function setUp() {
		parent::setUp();

		self::$post_id = self::factory()->post->create( array(
			'post_title'  => 'Test Download',
			'post_type'   => 'download',
			'post_status' => 'publish'
		) );

		$_variable_pricing = array(
			array(
				'name'   => 'Simple',
				'amount' => 20
			),
			array(
				'name'   => 'Advanced',
				'amount' => 100
			)
		);

		$_download_files = array(
			array(
				'name'      => 'File 1',
				'file'      => 'http://localhost/file1.jpg',
				'condition' => 0
			),
			array(
				'name'      => 'File 2',
				'file'      => 'http://localhost/file2.jpg',
				'condition' => 'all'
			)
		);

		$meta = array(
			'edd_price'                      => '0.00',
			'_variable_pricing'              => 1,
			'_edd_price_options_mode'        => 'on',
			'edd_variable_prices'            => array_values( $_variable_pricing ),
			'edd_download_files'             => array_values( $_download_files ),
			'_edd_download_limit'            => 20,
			'_edd_hide_purchase_link'        => 1,
			'edd_product_notes'              => 'Purchase Notes',
			'_edd_product_type'              => 'default',
			'_edd_download_earnings'         => 129.43,
			'_edd_download_sales'            => 59,
			'_edd_download_limit_override_1' => 1
		);
		foreach( $meta as $key => $value ) {
			update_post_meta( self::$post_id, $key, $value );
		}

		/** Generate some sales */
		self::$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$user = get_userdata( self::$user_id );

		$user_info = array(
			'id'         => $user->ID,
			'email'      => 'testadmin@domain.com',
			'first_name' => $user->first_name,
			'last_name'  => $user->last_name,
			'discount'   => 'none',
		);

		$download_details = array(
			array(
				'id' => self::$post_id,
				'options' => array(
					'price_id' => 1
				)
			)
		);

		$total = 0;

		$prices = get_post_meta($download_details[0]['id'], 'edd_variable_prices', true);
		$item_price = $prices[1]['amount'];

		$total += $item_price;

		$cart_details = array(
			array(
				'name' => 'Test Download',
				'id' => self::$post_id,
				'item_number' => array(
					'id' => self::$post_id,
					'options' => array(
						'price_id' => 1
					)
				),
				'price'    => 100.0,
				'quantity' => 1,
				'tax'      => 0.0
			)
		);

		$purchase_data = array(
			'price'        => number_format( (float) $total, 2 ),
			'date'         => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'purchase_key' => strtolower( md5( uniqid() ) ),
			'user_email'   => $user_info['email'],
			'user_info'    => $user_info,
			'currency'     => 'USD',
			'downloads'    => $download_details,
			'cart_details' => $cart_details,
			'status'       => 'pending',
			'tax'          => '0.00'
		);

		$_SERVER['REMOTE_ADDR'] = '10.0.0.0';
		$_SERVER['SERVER_NAME'] = 'edd-virtual.local';

		$payment_id = edd_insert_payment( $purchase_data );

		edd_update_payment_status( $payment_id, 'complete' );
	}

	public function tearDown() {
		parent::tearDown();

		edd_get_component_interface( 'customer', 'table' )->delete_all();
		edd_get_component_interface( 'customer', 'meta'  )->delete_all();
	}

	public function test_installed() {
		$this->assertTrue( edd_get_component_interface( 'customer', 'table' )->exists() );
	}

	public function test_get_by() {
		$customer = edd_get_customer_by( 'email', 'testadmin@domain.com' );

		$this->assertGreaterThan( 0, $customer->id );
	}

	public function test_legacy_attach_payment() {
		$payment_id = EDD_Helper_Payment::create_simple_payment();
		$customer   = new EDD_Customer( 'testadmin@domain.com' );

		EDD()->customers->attach_payment( $customer->id, $payment_id );

		$updated_customer = new EDD_Customer( 'testadmin@domain.com' );
		$payment_ids      = array_map( 'absint', explode( ',', $updated_customer->payment_ids ) );

		$this->assertTrue( in_array( $payment_id, $payment_ids ) );

		EDD_Helper_Payment::delete_payment( $payment_id );
	}

	public function test_legacy_remove_payment() {
		$payment_id = EDD_Helper_Payment::create_simple_payment();

		$customer = new EDD_Customer( 'testadmin@domain.com' );
		EDD()->customers->attach_payment( $customer->id, $payment_id );

		$updated_customer = new EDD_Customer( 'testadmin@domain.com' );
		$payment_ids      = array_map( 'absint', explode( ',', $updated_customer->payment_ids ) );
		$this->assertTrue( in_array( $payment_id, $payment_ids ) );

		EDD()->customers->remove_payment( $updated_customer->id, $payment_id );
		$updated_customer = new EDD_Customer( 'testadmin@domain.com' );
		$payment_ids      = array_map( 'absint', explode( ',', $updated_customer->payment_ids ) );

		$this->assertFalse( in_array( $payment_id, $payment_ids ) );

		EDD_Helper_Payment::delete_payment( $payment_id );
	}

	public function test_legacy_increment_stats() {
		$customer = new EDD_Customer( 'testadmin@domain.com' );

		$this->assertEquals( '100', $customer->purchase_value );
		$this->assertEquals( '1',   $customer->purchase_count );

		EDD()->customers->increment_stats( $customer->id, 10 );

		$updated_customer = new EDD_Customer( 'testadmin@domain.com' );

		$this->assertEquals( '110', $updated_customer->purchase_value );
		$this->assertEquals( '2',   $updated_customer->purchase_count );
	}

	public function test_legacy_decrement_stats() {
		$customer = new EDD_Customer( 'testadmin@domain.com' );

		$this->assertEquals( '100', $customer->purchase_value );
		$this->assertEquals( '1',   $customer->purchase_count );

		EDD()->customers->decrement_stats( $customer->id, 10 );

		$updated_customer = new EDD_Customer( 'testadmin@domain.com' );

		$this->assertEquals( '90', $updated_customer->purchase_value );
		$this->assertEquals( '0',  $updated_customer->purchase_count );
	}

	public function test_get_customers() {
		$customers = edd_get_customers();

		$this->assertEquals( 1, count( $customers ) );
	}

	public function test_count_customers() {
		$this->assertEquals( 1, edd_get_customer_count() );
	}

	public function test_update_customer_email_on_user_update() {

		$user_id = wp_insert_user( array(
			'user_login' => 'john12345',
			'user_email' => 'john1234@test.com',
			'user_pass'  => wp_generate_password()
		) );

		$customer = new EDD_Customer;
		$customer->create( array(
			'email' => 'john1234@test.com',
			'user_id' => $user_id
		) );

		wp_update_user( array(
			'ID' => $user_id,
			'user_email' => 'john12345@test.com'
		) );

		$updated_customer = new EDD_Customer( 'john12345@test.com' );

		$this->assertEquals( $customer->id, $updated_customer->id );
	}
}
