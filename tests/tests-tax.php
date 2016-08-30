<?php


/**
 * @group edd_tax
 */
class Tests_Taxes extends WP_UnitTestCase {

	protected $_payment_id = null;

	protected $_post = null;

	public function setUp() {
		parent::setUp();

		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );

		$meta = array(
			'edd_price' => '10.00',
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

		$cart_details = array();
		$cart_details[] = array(
			'name' => 'Test Download',
			'id' => $this->_post->ID,
			'item_number' => array(
				'id' => $this->_post->ID,
				'options' => array(
					'price_id' => 1
				)
			),
			'subtotal' => '10',
			'discount' => '0',
			'tax'      => '0.36',
			'item_price'=> '10',
			'price'    => '10.36',
			'quantity' => 1,
		);

		$purchase_data = array(
			'price' => '10.36',
			'date' => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
			'purchase_key' => strtolower( md5( uniqid() ) ),
			'user_email' => $user_info['email'],
			'user_info' => $user_info,
			'currency' => 'USD',
			'downloads' => $download_details,
			'cart_details' => $cart_details,
			'status' => 'publish'
		);

		$_SERVER['REMOTE_ADDR'] = '10.0.0.0';
		$_SERVER['SERVER_NAME'] = 'edd_virtual';

		$payment_id = edd_insert_payment( $purchase_data );
		edd_update_payment_status( $payment_id, 'publish' );

		$this->_payment_id = $payment_id;

		// Setup global tax rate

		edd_update_option( 'enable_taxes', true );
		edd_update_option( 'tax_rate', '3.6' );

		// Setup country / state tax rates
		$tax_rates   = array();
		$tax_rates[] = array( 'country' => 'US', 'state' => 'AL', 'rate' => 15 );

		update_option( 'edd_tax_rates', $tax_rates );
	}

	public function tearDown() {
		parent::tearDown();
		EDD_Helper_Payment::delete_payment( $this->_payment_id );
	}

	public function test_use_taxes() {
		$this->assertTrue( edd_use_taxes() );
	}

	public function test_get_tax_rates() {
		$this->assertInternalType( 'array', edd_get_tax_rates() );
	}

	public function test_get_tax_rate() {
		$this->assertInternalType( 'float', edd_get_tax_rate( 'US', 'AL' ) );
		// Test the one state that has its own rate
		$this->assertEquals( '0.15', edd_get_tax_rate( 'US', 'AL' ) );

		// Test some other arbitrary states to ensure they fall back to default
		$this->assertEquals( '0.036', edd_get_tax_rate( 'US', 'KS' ) );
		$this->assertEquals( '0.036', edd_get_tax_rate( 'US', 'AK' ) );
		$this->assertEquals( '0.036', edd_get_tax_rate( 'US', 'CA' ) );

		// Test some other countries to ensure they fall back to default
		$this->assertEquals( '0.036', edd_get_tax_rate( 'JP' ) );
		$this->assertEquals( '0.036', edd_get_tax_rate( 'BR' ) );
		$this->assertEquals( '0.036', edd_get_tax_rate( 'CN' ) );
		$this->assertEquals( '0.036', edd_get_tax_rate( 'HK' ) );
	}

	public function test_get_global_tax_rate() {
		$this->assertInternalType( 'float', edd_get_tax_rate( 'CA', 'AB' ) );
		$this->assertEquals( '0.036', edd_get_tax_rate( 'CA', 'AB' ) );

		$this->assertInternalType( 'float', edd_get_tax_rate() );
		$this->assertEquals( '0.036', edd_get_tax_rate() );
	}

	public function test_get_tax_rate_post() {
		$_POST['billing_country'] = 'US';
		$_POST['state']           = 'AL';
		$this->assertEquals( '0.15', edd_get_tax_rate() );

		// Reset to origin
		unset( $_POST['billing_country'] );
		unset( $_POST['state'] );
	}

	public function test_get_tax_rate_user_address() {

		// Prep test (fake is_user_logged_in())
		global $current_user;
		$current_user      = new WP_User(1);
		$user_id           = get_current_user_id();
		$existing_addresss = get_user_meta( $user_id, '_edd_user_address', true );
		update_user_meta( $user_id, '_edd_user_address', array(
			'line1'   => 'First address',
			'line2'   => 'Line two',
			'city'    => 'MyCity',
			'zip'     => '12345',
			'country' => 'US',
			'state'   => 'AL',
		) );

		// Assert
		$this->assertEquals( '0.15', edd_get_tax_rate() );

		// Reset to origin
		update_post_meta( $user_id, '_edd_user_address', $existing_addresss );
	}

	public function test_get_tax_rate_global() {

		// Prepare test
		$existing_tax_rates = get_option( 'edd_tax_rates' );
		$tax_rates[]        = array( 'country' => 'NL', 'global' => '1', 'rate' => 21 );
		update_option( 'edd_tax_rates', $tax_rates );

		// Assert
		$this->assertEquals( '0.21', edd_get_tax_rate( 'NL' ) );

		// Reset to origin
		update_option( 'edd_tax_rates', $existing_tax_rates );
	}

	public function test_get_formatted_tax_rate() {
		$this->assertEquals( '3.6%', edd_get_formatted_tax_rate() );
	}

	public function test_calculate_tax() {
		$this->assertEquals( '1.944', edd_calculate_tax( 54 ) );
		$this->assertEquals( '1.9692', edd_calculate_tax( 54.7 ) );
		$this->assertEquals( '5.5386', edd_calculate_tax( 153.85 ) );
		$this->assertEquals( '9.29916', edd_calculate_tax( 258.31 ) );
		$this->assertEquals( '37.41552', edd_calculate_tax( 1039.32 ) );
		$this->assertEquals( '361.58724', edd_calculate_tax( 10044.09 ) );
		$this->assertEquals( '0', edd_calculate_tax( -1.50 ) );
	}

	public function test_calculate_tax_price_includes_tax() {

		// Prepare test
		$origin_price_include_tax = edd_get_option( 'prices_include_tax' );
		edd_update_option( 'prices_include_tax', 'yes' );

		// Asserts
		$this->assertEquals( '1.87644787645', edd_calculate_tax( 54 ) );
		$this->assertEquals( '1.90077220077', edd_calculate_tax( 54.7 ) );
		$this->assertEquals( '5.34613899614', edd_calculate_tax( 153.85 ) );
		$this->assertEquals( '8.97602316602', edd_calculate_tax( 258.31 ) );
		$this->assertEquals( '36.1153667954', edd_calculate_tax( 1039.32 ) );
		$this->assertEquals( '349.02243243243356118910014629364013671875', edd_calculate_tax( 10044.09 ) );

		// Reset to origin
		edd_update_option( 'prices_include_tax', $origin_price_include_tax );
	}

	public function test_get_sales_tax_for_year() {
		$this->assertEquals( '0.36', edd_get_sales_tax_for_year( date( 'Y' ) ) );
		$this->assertEquals( '0', edd_get_sales_tax_for_year( date( 'Y' ) - 1 ) );
	}

	public function test_sales_tax_for_year() {
		ob_start();
		edd_sales_tax_for_year( date( 'Y' ) );
		$this_year = ob_get_clean();

		ob_start();
		edd_sales_tax_for_year( date( 'Y' ) - 1 );
		$last_year = ob_get_clean();

		$this->assertEquals( '&#36;0.36', $this_year );
		$this->assertEquals( '&#36;0.00', $last_year );
	}

	public function test_prices_show_tax_on_checkout() {
		$this->assertFalse( edd_prices_show_tax_on_checkout() );
	}

	public function test_prices_include_tax() {
		$this->assertFalse( edd_prices_include_tax() );
	}

	public function test_is_cart_taxed() {
		$this->assertTrue( edd_is_cart_taxed() );
	}

	public function test_display_tax_rates() {
		$this->assertFalse( edd_display_tax_rate() );
	}

	public function test_cart_needs_tax_address_fields() {
		$this->assertInternalType( 'bool', edd_cart_needs_tax_address_fields() );
		$this->assertTrue( edd_cart_needs_tax_address_fields() );
	}

	public function test_cart_needs_tax_address_fields_false() {

		// Prepare test
		$existing_enable_taxes = edd_get_option( 'enable_taxes' );
		edd_update_option( 'enable_taxes', false );

		// Assert
		$this->assertFalse( edd_cart_needs_tax_address_fields() );

		// Reset to origin
		edd_update_option( 'enable_taxes', $existing_enable_taxes );
	}

	public function test_download_is_exclusive_of_tax() {
		$this->assertFalse( edd_download_is_tax_exclusive( $this->_post->ID ) );
	}

	public function test_get_payment_tax() {
		$this->assertEquals( '0.36', edd_get_payment_tax( $this->_payment_id ) );
	}

	public function test_payment_tax_updates() {
		// Test backwards compat bug in issue/3324
		$this->assertEquals( '0.36', edd_get_payment_tax( $this->_payment_id ) );
		$current_meta = edd_get_payment_meta( $this->_payment_id );
		edd_update_payment_meta( $this->_payment_id, '_edd_payment_meta', $current_meta );
		$this->assertEquals( '0.36', edd_get_payment_tax( $this->_payment_id ) );

		// Test that when we update _edd_payment_tax, we update the _edd_payment_meta
		edd_update_payment_meta( $this->_payment_id, '_edd_payment_tax', 10 );
		$meta_array = edd_get_payment_meta( $this->_payment_id, '_edd_payment_meta', true );
		$this->assertEquals( 10, $meta_array['tax'] );
		$this->assertEquals( 10, edd_get_payment_tax( $this->_payment_id ) );

		// Test that when we update the _edd_payment_meta, we update the _edd_payment_tax
		$current_meta = edd_get_payment_meta( $this->_payment_id, true );
		$current_meta['tax'] = 20;
		edd_update_payment_meta( $this->_payment_id, '_edd_payment_meta', $current_meta );
		$this->assertEquals( 20, edd_get_payment_tax( $this->_payment_id ) );

	}
}
