<?php


/**
 * @group edd_meta
 */
class Tests_Register_Meta extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
		$this->payment_id  = EDD_Helper_Payment::create_simple_payment();
		$variable_download = EDD_Helper_Download::create_variable_download();

		$this->download_id = $variable_download->ID;
	}

	public function tearDown() {
		parent::tearDown();
		EDD_Helper_Payment::delete_payment( $this->payment_id );
		EDD_Helper_Download::delete_download( $this->download_id );
	}

	public function test_download_meta() {
		global $wp_filter;

		// Uses standalone function callbacks
		$this->assertarrayHasKey( 'edd_sanitize_amount', $wp_filter['sanitize_post_meta__edd_download_earnings'][10] );
		$this->assertarrayHasKey( 'sanitize_text_field', $wp_filter['sanitize_post_meta__edd_button_behavior'][10] );

		// Callbacks are part of the object, so just make sure it got registered
		$this->assertNotEmpty( $wp_filter['sanitize_post_meta__edd_default_price_id'][10] );
		$this->assertNotEmpty( $wp_filter['sanitize_post_meta__edd_download_sales'][10] );
		$this->assertNotEmpty( $wp_filter['sanitize_post_meta_edd_variable_prices'][10] );
		$this->assertNotEmpty( $wp_filter['sanitize_post_meta_edd_download_files'][10] );
		$this->assertNotEmpty( $wp_filter['sanitize_post_meta__edd_bundled_products'][10] );
		$this->assertNotEmpty( $wp_filter['sanitize_post_meta_edd_price'][10] );

	}

	public function test_purchase_meta() {
		global $wp_filter;

		// Uses standalone function callbacks
		$this->assertarrayHasKey( 'sanitize_email',      $wp_filter['sanitize_post_meta__edd_payment_user_email'][10] );
		$this->assertarrayHasKey( 'sanitize_text_field', $wp_filter['sanitize_post_meta__edd_payment_user_ip'][10] );
		$this->assertarrayHasKey( 'sanitize_text_field', $wp_filter['sanitize_post_meta__edd_payment_purchase_key'][10] );
		$this->assertarrayHasKey( 'edd_sanitize_amount', $wp_filter['sanitize_post_meta__edd_payment_total'][10] );
		$this->assertarrayHasKey( 'sanitize_text_field', $wp_filter['sanitize_post_meta__edd_payment_mode'][10] );
		$this->assertarrayHasKey( 'sanitize_text_field', $wp_filter['sanitize_post_meta__edd_payment_gateway'][10] );
		$this->assertarrayHasKey( 'edd_sanitize_amount', $wp_filter['sanitize_post_meta__edd_payment_tax'][10] );
		$this->assertarrayHasKey( 'sanitize_text_field', $wp_filter['sanitize_post_meta__edd_completed_date'][10] );

		// Callbacks are part of the object, so just make sure it got registered
		$this->assertNotEmpty( $wp_filter['sanitize_post_meta__edd_payment_customer_id'][10] );
		$this->assertNotEmpty( $wp_filter['sanitize_post_meta__edd_payment_user_id'][10] );
		$this->assertNotEmpty( $wp_filter['sanitize_post_meta__edd_payment_meta'][10] );

	}

	public function test_intval_wrapper() {
		update_post_meta( $this->payment_id, '_edd_payment_customer_id', '90.4' );
		$this->assertEquals( '90', get_post_meta( $this->payment_id, '_edd_payment_customer_id', true ) );

		update_post_meta( $this->payment_id, '_edd_payment_customer_id', '-1.43' );
		$this->assertEquals( '-1', get_post_meta( $this->payment_id, '_edd_payment_customer_id', true ) );
	}

	public function test_sanitize_array() {
		$object = new StdClass;
		$object->one = 1;
		$object->two = 2;

		update_post_meta( $this->payment_id, '_edd_payment_meta', $object );
		$this->assertInternalType( 'array', get_post_meta( $this->payment_id, '_edd_payment_meta', true ) );

		$serialized = serialize( array(
			1, 2, 3,
		) );

		update_post_meta( $this->payment_id, '_edd_payment_meta', $serialized );
		$this->assertInternalType( 'array', get_post_meta( $this->payment_id, '_edd_payment_meta', true ) );
		$this->assertFalse( is_serialized( get_post_meta( $this->payment_id, '_edd_payment_meta', true ) ) );
	}

	public function test_sanitize_price() {

		// Test saving a normal postitive value
		$price = '9';
		update_post_meta( $this->download_id, 'edd_price', $price );
		$saved_price = get_post_meta( $this->download_id, 'edd_price', true );
		$this->assertEquals( 9, $saved_price );

		// Test saving a negative value
		$price = -1;
		update_post_meta( $this->download_id, 'edd_price', $price );
		$saved_price = get_post_meta( $this->download_id, 'edd_price', true );
		$this->assertEquals( 0, $saved_price );

		// Test saving a zero value
		$price = 0;
		update_post_meta( $this->download_id, 'edd_price', $price );
		$saved_price = get_post_meta( $this->download_id, 'edd_price', true );
		$this->assertEquals( 0, $saved_price );

		// Test negative values with the filter now
		add_filter( 'edd_allow_negative_prices', '__return_true' );
		$price = -1;
		update_post_meta( $this->download_id, 'edd_price', $price );
		$saved_price = get_post_meta( $this->download_id, 'edd_price', true );
		$this->assertEquals( -1, $saved_price );
		remove_filter( 'edd_allow_negative_prices', '__return_true' );

	}

	public function test_sanitize_variable_prices() {
		$variable_prices = array(
			array( 'name'   => 'First Option' ),
			array( 'amount' => 5, 'name' => 'Second Option' ),
			array( 'foo'    => 'bar', 'bar' => 'baz' ),
		);

		update_post_meta( $this->download_id, 'edd_variable_prices', $variable_prices );
		$saved_variable_prices = get_post_meta( $this->download_id, 'edd_variable_prices', true );
		$this->assertEquals( 2, count( $saved_variable_prices ) );
		$this->assertEquals( 0, $saved_variable_prices[0]['amount'] );
	}

	public function test_sanitize_files() {
		$files = array(
			array(
				'file' => '',
				'name' => '',
			),
			array(
				'file' => '  file2.zip  ',
				'name' => 'File 2',
			),
			array(
				'file' => 'file3.zip',
				'name' => '   File 3   ',
			),
		);

		update_post_meta( $this->download_id, 'edd_download_files', $files );
		$saved_files = get_post_meta( $this->download_id, 'edd_download_files', true );
		$this->assertEquals( 2, count( $saved_files ) );
		$this->assertEquals( 'file2.zip', $saved_files[1]['file'] );
		$this->assertEquals( 'File 3', $saved_files[2]['name'] );
	}


}
