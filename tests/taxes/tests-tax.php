<?php
/**
 * @group edd_tax
 */
namespace EDD\Tests\Taxes;

use EDD\Tests\Helpers;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

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
	 * The array of tax rate IDs that the class adds.
	 *
	 * @var array
	 */
	protected static $rate_ids = array();

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

		self::$order = edd_get_order( Helpers\EDD_Helper_Payment::create_simple_payment_with_tax() );

		edd_update_order_status( self::$order->ID, 'complete' );

		edd_update_option( 'enable_taxes', true );
		$rates_to_create = array(
			array(
				'scope'  => 'global',
				'name'   => '',
				'amount' => 3.6,
			),
			array(
				'scope'       => 'region',
				'name'        => 'US',
				'description' => 'AL',
				'amount'      => 15,
			),
			array(
				'scope'       => 'region',
				'name'        => 'US',
				'description' => 'AZ',
				'amount'      => .15,
			),
			array(
				'scope'       => 'region',
				'name'        => 'US',
				'description' => 'TX',
				'amount'      => .13,
			),
			array(
				'scope'       => 'region',
				'name'        => 'US',
				'description' => 'AR',
				'amount'      => .09,
			),
			array(
				'scope'       => 'region',
				'name'        => 'US',
				'description' => 'HI',
				'amount'      => .63,
			),
			array(
				'scope'       => 'region',
				'name'        => 'US',
				'description' => 'LA',
				'amount'      => .96,
			),
			array(
				'scope'       => 'region',
				'name'        => 'US',
				'description' => 'TN',
				'amount'      => 9.25,
			),
		);
		foreach ( $rates_to_create as $rate ) {
			$rate['amount'] = floatval( $rate['amount'] );

			self::$rate_ids[] = edd_add_tax_rate( $rate );
		}
	}

	// Disable taxes and delete rates.
	public static function wpTearDownAfterClass() {
		edd_update_option( 'enable_taxes', false );

		foreach ( self::$rate_ids as $rate_id ) {
			edd_delete_adjustment( $rate_id );
		}
	}

	public function test_use_taxes() {
		$this->assertTrue( edd_use_taxes() );
	}

	public function test_get_tax_rates() {
		$this->assertIsArray( edd_get_tax_rates() );
	}

	public function test_edd_get_tax_rate_is_float() {
		$this->assertIsFloat( edd_get_tax_rate( 'US', 'AL' ) );
	}

	public function test_edd_get_tax_rate_state_returns_region_rate() {
		$this->assertEquals( '0.15', edd_get_tax_rate( 'US', 'AL' ) );
	}

	public function test_edd_get_tax_rate_other_region_KS_is_fallback() {
		$this->assertEquals( 0.036, round( edd_get_tax_rate( 'US', 'KS' ), 3 ) );
	}

	public function test_edd_get_tax_rate_other_region_AK_is_fallback() {
		$this->assertEquals( 0.036, round( edd_get_tax_rate( 'US', 'AK' ), 3 ) );
	}

	public function test_edd_get_tax_rate_other_region_CA_is_fallback() {
		$this->assertEquals( 0.036, round( edd_get_tax_rate( 'US', 'CA' ), 3 ) );
	}

	public function test_edd_get_tax_rate_other_country_JP_is_fallback() {
		$this->assertEquals( 0.036, round( edd_get_tax_rate( 'JP' ), 3 ) );
	}

	public function test_edd_get_tax_rate_other_country_BR_is_fallback() {
		$this->assertEquals( 0.036, round( edd_get_tax_rate( 'BR' ), 3 ) );
	}

	public function test_edd_get_tax_rate_other_country_CN_is_fallback() {
		$this->assertEquals( 0.036, round( edd_get_tax_rate( 'CN' ), 3 ) );
	}

	public function test_edd_get_tax_rate_other_country_HK_is_fallback() {
		$this->assertEquals( 0.036, round( edd_get_tax_rate( 'HK' ), 3 ) );
	}

	public function test_get_tax_rate_AZ_equals_0015() {
		$this->assertEquals( 0.0015, edd_get_tax_rate( 'US', 'AZ' ) );
	}

	public function test_get_tax_rate_TX_equals_13() {
		$this->assertEquals( 0.0013, edd_get_tax_rate( 'US', 'TX' ) );
	}

	public function test_get_tax_rate_AR_equals_9() {
		$this->assertEquals( 0.0009, edd_get_tax_rate( 'US', 'AR' ) );
	}

	public function test_get_tax_rate_HI_equals_63() {
		$this->assertEquals( 0.0063, edd_get_tax_rate( 'US', 'HI' ) );
	}

	public function test_get_tax_rate_LA_equals_96() {
		$this->assertEquals( 0.0096, edd_get_tax_rate( 'US', 'LA' ) );
	}

	public function test_get_tax_rate_TN_equals_0925() {
		$this->assertEquals( 0.0925, edd_get_tax_rate( 'US', 'TN' ) );
	}

	public function test_get_tax_rate_by_country_returns_global_rate() {
		$this->assertIsFloat( edd_get_tax_rate( 'CA', 'AB' ) );
		$this->assertEquals( 0.036, round( edd_get_tax_rate( 'CA', 'AB' ), 3 ) );
	}

	public function test_get_tax_rate_no_parameters_returns_global_rate() {
		$this->assertIsFloat( edd_get_tax_rate() );
		$this->assertEquals( 0.036, round( edd_get_tax_rate(), 3 ) );
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

		$current_user = new \WP_User( 1 );
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

	public function test_get_tax_rate_country() {
		$country_rate_id = edd_add_tax_rate(
			array(
				'name'   => 'NL',
				'scope'  => 'country',
				'amount' => floatval( 21 ),
			)
		);

		// Assert
		$this->assertEquals( 0.21, edd_get_tax_rate( 'NL' ) );

		// Delete the new adjustment.
		edd_delete_adjustment( $country_rate_id );
	}

	public function test_get_formatted_tax_rate() {
		$this->assertEquals( '3.6%', edd_get_formatted_tax_rate() );
	}

	public function test_calculate_tax() {
		$this->assertEquals( 1.944, round( edd_calculate_tax( 54 ), 3 ) );
		$this->assertEquals( 1.9692, round( edd_calculate_tax( 54.7 ), 4 ) );
		$this->assertEquals( 5.5386, round( edd_calculate_tax( 153.85 ), 4 ) );
		$this->assertEquals( 9.29916, round( edd_calculate_tax( 258.31 ), 5 ) );
		$this->assertEquals( 37.41552, round( edd_calculate_tax( 1039.32 ), 5 ) );
		$this->assertEquals( 361.58724, round( edd_calculate_tax( 10044.09 ), 5 ) );
		$this->assertEquals( 0, edd_calculate_tax( - 1.50 ) );
	}

	public function test_calculate_tax_amount_AZ_equals_810() {
		$this->assertEquals( 0.08, edd_format_amount( edd_calculate_tax( 54, 'US', 'AZ' ) ) );
	}

	public function test_calculate_tax_amount_TX_equals_711() {
		$this->assertEquals( 0.07, edd_format_amount( edd_calculate_tax( 54.7, 'US', 'TX' ) ) );
	}

	public function test_calculate_tax_amount_AR_equals_1385() {
		$this->assertEquals( 0.14, edd_format_amount( edd_calculate_tax( 153.85, 'US', 'AR' ) ) );
	}

	public function test_calculate_tax_amount_HI_equals_16274() {
		$this->assertEquals( 1.63, edd_format_amount( edd_calculate_tax( 258.31, 'US', 'HI' ) ) );
	}

	public function test_calculate_tax_amount_LA_equals_99775() {
		$this->assertEquals( 9.98, edd_format_amount( edd_calculate_tax( 1039.32, 'US', 'LA' ) ) );
	}

	public function test_calculate_tax_amount_TN_equals_277() {
		$this->assertEquals( 2.77, edd_format_amount( edd_calculate_tax( 29.99, 'US', 'TN' ) ) );
	}

	public function test_calculate_tax_amount_price_includes_tax() {

		// Prepare test
		$origin_price_include_tax = edd_get_option( 'prices_include_tax' );
		edd_update_option( 'prices_include_tax', 'yes' );

		// Asserts
		$this->assertEquals( 1.87644787645, round( edd_calculate_tax( 54 ), 11 ) );
		$this->assertEquals( 1.90077220077, round( edd_calculate_tax( 54.7 ), 11 ) );
		$this->assertEquals( 5.34613899614, round( edd_calculate_tax( 153.85 ), 11 ) );
		$this->assertEquals( 8.97602316602, round( edd_calculate_tax( 258.31 ), 11 ) );
		$this->assertEquals( 36.1153667954, round( edd_calculate_tax( 1039.32 ), 10 ) );
		$this->assertEquals( 349.02243243243356118910014629364013671875, round( edd_calculate_tax( 10044.09 ), 38 ) );

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
		$this->assertIsBool( edd_cart_needs_tax_address_fields() );
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

	public function test_update_option_gets_new_rate_amount() {

		$tn_new_rate = edd_add_tax_rate(
			array(
				'scope'       => 'region',
				'name'        => 'US',
				'description' => 'TN',
				'amount'      => 19.25,
			)
		);

		$this->assertEquals( .1925, edd_get_tax_rate( 'US', 'TN' ) );
	}
}
