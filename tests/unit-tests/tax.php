<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_tax
 */
class Tests_Taxes extends EDD_UnitTestCase {

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

		$total += $item_price;

		$cart_details = array(
			array(
				'name' => 'Test Download',
				'id' => $this->_post->ID,
				'item_number' => array(
					'id' => $this->_post->ID,
					'options' => array()
				),
				'subtotal' => '10',
				'discount' => '0',
				'tax'      => '0.36',
				'price'    => '10.36',
				'quantity' => 1
			)
		);

		$purchase_data = array(
			'price' => '10.36',
			'date' => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'purchase_key' => strtolower( md5( uniqid() ) ),
			'user_email' => $user_info['email'],
			'user_info' => $user_info,
			'currency' => 'USD',
			'downloads' => $download_details,
			'cart_details' => $cart_details,
			'status' => 'complete'
		);

		$_SERVER['REMOTE_ADDR'] = '10.0.0.0';
		$_SERVER['SERVER_NAME'] = 'edd_virtual';

		$payment_id = edd_insert_payment( $purchase_data );

		$this->_payment_id = $payment_id;
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_use_taxes() {
		$this->assertFalse( edd_use_taxes() );
		$options = array();
		$options['edd_use_taxes'] = '1';
		$edd_options = array_merge( $options, $edd_options );
		update_option( 'edd_options', $edd_options );
		$this->assertTrue( edd_use_taxes() );
	}

	public function test_taxes_after_discounts() {
		$this->assertFalse( edd_taxes_after_discounts() );
	}

	public function test_get_tax_rate() {
		global $edd_options;

		// Setup global tax rate
		$options = array();
		$options['tax_rate'] = '3.6';
		$edd_options = array_merge( $options, $edd_options );

		// Setup country / state tax rates
		$tax_rates = array();
		$tax_rates[] = array( 'country' => 'US', 'state' => 'AL', 'rate' => 15 );

		update_option( 'edd_options', $edd_options );
		update_option( 'edd_tax_rates', $tax_rates );

		$this->assertInternalType( 'float', edd_get_tax_rate( 'US', 'AL' ) );
		$this->assertEquals( '0.15', edd_get_tax_rate( 'US', 'AL' ) );
	}

	public function test_get_global_tax_rate() {
		global $edd_options;

		// Setup global tax rate
		$options = array();
		$options['tax_rate'] = '3.6';
		$edd_options = array_merge( $options, $edd_options );

		// Setup country / state tax rates
		$tax_rates = array();
		$tax_rates[] = array( 'country' => 'US', 'state' => 'AL', 'rate' => 15 );

		update_option( 'edd_options', $edd_options );
		update_option( 'edd_tax_rates', $tax_rates );

		$this->assertInternalType( 'float', edd_get_tax_rate( 'CA', 'AB' ) );
		$this->assertEquals( '0.036', edd_get_tax_rate( 'CA', 'AB' ) );

		$this->assertInternalType( 'float', edd_get_tax_rate() );
		$this->assertEquals( '0.036', edd_get_tax_rate() );
	}

	public function test_calculate_tax() {
		global $edd_options;

		// Calculate with taxes disabled
		$this->assertEquals( 0.0, edd_calculate_tax( 54 ) );

		// Enable taxes
		$edd_options['edd_use_taxes'] = '1';

		// Calculate with taxes enabled
		$this->assertEquals( '1.944', edd_calculate_tax( 54 ) );
	}

	public function test_get_sales_tax_for_year() {

		// This needs to test with a payment created

		$this->assertEquals( '0.36', edd_get_sales_tax_for_year( date( 'Y' ) ) );
	}

	public function test_prices_show_tax_on_checkout() {
		$this->assertFalse( edd_prices_show_tax_on_checkout() );
	}

	public function test_prices_include_tax() {
		$this->assertFalse( edd_prices_include_tax() );
	}

	public function test_is_cart_taxed() {
		global $edd_options;

		$this->assertFalse( edd_is_cart_taxed() );

		$edd_options['edd_use_taxes'] = '1';

		$this->assertTrue( edd_is_cart_taxed() );

	}

	public function test_display_tax_rates() {
		global $edd_options;

		$this->assertFalse( edd_display_tax_rate() );

		$edd_options['edd_use_taxes'] = '1';
		$edd_options['display_tax_rate'] = '1';

		$this->assertTrue( edd_is_cart_taxed() );

	}

	public function test_download_is_exclusive_of_tax() {
		$this->assertFalse( edd_download_is_tax_exclusive( $this->_post->ID ) );
	}

}
