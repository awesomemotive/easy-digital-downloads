<?php


/**
 * @group edd_tax
 */
class Tests_Taxes extends EDD_UnitTestCase {

	/**
	 * Order test fixture.
	 *
	 * @var EDD\Orders\Order
	 */
	protected static $order;

	/**
	 * Download test fixture.
	 *
	 * @var EDD_Download
	 */
	protected static $download;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		$post_id = self::factory()->post->create( array(
			'post_title'  => 'Test Download',
			'post_type'   => 'download',
			'post_status' => 'publish',
		) );

		$meta = array(
			'edd_price' => '10.00',
		);
		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		self::$download = edd_get_download( $post_id );

		self::$order = edd_get_order( EDD_Helper_Payment::create_simple_payment_with_tax() );

		edd_update_order_status( self::$order->ID, 'complete' );

		// Setup global tax rate

		edd_update_option( 'enable_taxes', true );
		edd_update_option( 'tax_rate', '3.6' );

		// Setup country / state tax rates
		$tax_rates   = array();
		$tax_rates[] = array( 'country' => 'US', 'state' => 'AL', 'rate' => 15 );
		$tax_rates[] = array( 'country' => 'US', 'state' => 'AZ', 'rate' => .15 );
		$tax_rates[] = array( 'country' => 'US', 'state' => 'TX', 'rate' => .13 );
		$tax_rates[] = array( 'country' => 'US', 'state' => 'AR', 'rate' => .09 );
		$tax_rates[] = array( 'country' => 'US', 'state' => 'HI', 'rate' => .63 );
		$tax_rates[] = array( 'country' => 'US', 'state' => 'LA', 'rate' => .96 );

		update_option( 'edd_tax_rates', $tax_rates );
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

	public function test_get_tax_rate_less_than_one() {
		$this->assertEquals( '0.0015', edd_get_tax_rate( 'US', 'AZ' ) );
		$this->assertEquals( '0.0013', edd_get_tax_rate( 'US', 'TX' ) );
		$this->assertEquals( '0.0009', edd_get_tax_rate( 'US', 'AR' ) );
		$this->assertEquals( '0.0063', edd_get_tax_rate( 'US', 'HI' ) );
		$this->assertEquals( '0.0096', edd_get_tax_rate( 'US', 'LA' ) );
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
		$this->setExpectedIncorrectUsage( 'add_user_meta()/update_user_meta()' );
		$this->setExpectedIncorrectUsage( 'get_user_meta()' );

		global $current_user;

		$current_user = new WP_User( 1 );
		$user_id      = get_current_user_id();

		update_user_meta( $user_id, '_edd_user_address', array(
			'line1'   => 'First address',
			'line2'   => 'Line two',
			'city'    => 'MyCity',
			'zip'     => '12345',
			'country' => 'US',
			'state'   => 'AL',
		) );

		$this->assertEquals( '0.15', edd_get_tax_rate() );
	}

	public function test_get_tax_rate_global() {
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
		$this->assertEquals( '0', edd_calculate_tax( - 1.50 ) );
	}

	public function test_calculate_tax_less_than_one() {
		$this->assertEquals( '0.08', edd_format_amount( edd_calculate_tax( 54, 'US', 'AZ' ) ) );
		$this->assertEquals( '0.07', edd_format_amount( edd_calculate_tax( 54.7, 'US', 'TX' ) ) );
		$this->assertEquals( '0.14', edd_format_amount( edd_calculate_tax( 153.85, 'US', 'AR' ) ) );
		$this->assertEquals( '1.63', edd_format_amount( edd_calculate_tax( 258.31, 'US', 'HI' ) ) );
		$this->assertEquals( '9.98', edd_format_amount( edd_calculate_tax( 1039.32, 'US', 'LA' ) ) );
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
		$this->assertEquals( '11.0', edd_get_sales_tax_for_year( date( 'Y' ) ) );
		$this->assertEquals( '0', edd_get_sales_tax_for_year( date( 'Y' ) - 1 ) );
	}

	public function test_sales_tax_for_year() {
		ob_start();
		edd_sales_tax_for_year( date( 'Y' ) );
		$this_year = ob_get_clean();

		ob_start();
		edd_sales_tax_for_year( date( 'Y' ) - 1 );
		$last_year = ob_get_clean();

		$this->assertEquals( '&#36;11.00', $this_year );
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
		$this->assertFalse( edd_download_is_tax_exclusive( self::$download->ID ) );
	}

	public function test_get_payment_tax() {
		$this->assertEquals( 11.000000000, edd_get_payment_tax( self::$order->ID ), 2 );
	}

	public function test_payment_tax_updates() {
		// Test backwards compat bug in issue/3324
		$this->assertEquals( 11.000000000, self::$order->tax );
		$current_meta = edd_get_payment_meta( self::$order->id );

		edd_update_payment_meta( self::$order->id, '_edd_payment_meta', $current_meta );
		$this->assertEquals( 11.000000000, edd_get_payment_tax( self::$order->id ) );

		// Test that when we update _edd_payment_tax, we update the _edd_payment_meta
		edd_update_payment_meta( self::$order->id, '_edd_payment_tax', 10 );

		$meta_array = edd_get_payment_meta( self::$order->id, '_edd_payment_meta', true );

		$this->assertEquals( 10, $meta_array['tax'] );
		$this->assertEquals( 10, edd_get_payment_tax( self::$order->id ) );

		// Test that when we update the _edd_payment_meta, we update the _edd_payment_tax
		$current_meta        = edd_get_payment_meta( self::$order->id, '_edd_payment_meta', true );
		$current_meta['tax'] = 20;
		edd_update_payment_meta( self::$order->id, '_edd_payment_meta', $current_meta );
		$this->assertEquals( 20, edd_get_payment_tax( self::$order->id ) );
	}
}
