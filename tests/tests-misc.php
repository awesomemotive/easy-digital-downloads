<?php
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Utils\FileSystem;

/**
 * @group edd_misc
 * @group edd_functions
 */
class Misc extends EDD_UnitTestCase {

	/**
	 * Download fixture.
	 */
	protected static $download;

	/**
	 * Set up fixtures once.
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		self::$download = Helpers\EDD_Helper_Download::create_simple_download();
	}

	public function test_test_mode() {
		$this->assertFalse( edd_is_test_mode() );
	}

	public function test_guest_checkout() {
		$this->assertFalse( edd_no_guest_checkout() );
	}

	public function test_logged_in_only() {
		$this->assertFalse( edd_logged_in_only() );
	}

	public function test_straight_to_checkout() {
		$this->assertFalse( edd_straight_to_checkout() );
	}

	public function test_no_redownload() {
		$this->assertFalse( edd_no_redownload() );
	}

	public function test_is_cc_verify_enabled() {
		$this->assertTrue( edd_is_cc_verify_enabled() );
	}

	public function test_is_odd() {
		$this->assertTrue( edd_is_odd( 3 ) );
		$this->assertFalse( edd_is_odd( 4 ) );
	}

	public function test_get_file_extension() {
		$this->assertEquals( 'php', edd_get_file_extension( 'file.php' ) );
	}

	public function test_get_file_extension_with_query_string() {
		$this->assertEquals( 'pdf', edd_get_file_extension( 'file.pdf?test=1' ) );
	}

	public function test_string_is_image_url() {
		$this->assertTrue( edd_string_is_image_url( 'jpg' ) );
		$this->assertTrue( edd_string_is_image_url( 'webp' ) );
		$this->assertFalse( edd_string_is_image_url( 'php' ) );
	}

	public function test_get_ip() {
		$this->assertEquals( '127.0.0.1', edd_get_ip() );

		$_SERVER['REMOTE_ADDR'] = '172.217.6.46';
		$this->assertEquals( '172.217.6.46', edd_get_ip() );

		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	}

	public function test_get_ip_reverse_proxies() {
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '123.123.123.123, 10.0.0.2';
		$this->assertEquals( '123.123.123.123', edd_get_ip() );
		unset($_SERVER['HTTP_X_FORWARDED_FOR']);
	}

	public function test_get_ip_reverse_proxy() {
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '123.123.123.123';
		$this->assertEquals( '123.123.123.123', edd_get_ip() );
		unset($_SERVER['HTTP_X_FORWARDED_FOR']);
	}


	public function test_get_currencies() {
		$expected = array(
			'USD'  => __( 'US Dollars (&#36;)', 'easy-digital-downloads' ),
			'EUR'  => __( 'Euros (&euro;)', 'easy-digital-downloads' ),
			'GBP'  => __( 'Pound Sterling (&pound;)', 'easy-digital-downloads' ),
			'AUD'  => __( 'Australian Dollars (&#36;)', 'easy-digital-downloads' ),
			'BRL'  => __( 'Brazilian Real (R&#36;)', 'easy-digital-downloads' ),
			'CAD'  => __( 'Canadian Dollars (&#36;)', 'easy-digital-downloads' ),
			'CZK'  => __( 'Czech Koruna', 'easy-digital-downloads' ),
			'DKK'  => __( 'Danish Krone', 'easy-digital-downloads' ),
			'HKD'  => __( 'Hong Kong Dollar (&#36;)', 'easy-digital-downloads' ),
			'HUF'  => __( 'Hungarian Forint', 'easy-digital-downloads' ),
			'ILS'  => __( 'Israeli Shekel (&#8362;)', 'easy-digital-downloads' ),
			'JPY'  => __( 'Japanese Yen (&yen;)', 'easy-digital-downloads' ),
			'MYR'  => __( 'Malaysian Ringgits', 'easy-digital-downloads' ),
			'MXN'  => __( 'Mexican Peso (&#36;)', 'easy-digital-downloads' ),
			'NZD'  => __( 'New Zealand Dollar (&#36;)', 'easy-digital-downloads' ),
			'NOK'  => __( 'Norwegian Krone', 'easy-digital-downloads' ),
			'PHP'  => __( 'Philippine Pesos', 'easy-digital-downloads' ),
			'PLN'  => __( 'Polish Zloty', 'easy-digital-downloads' ),
			'SGD'  => __( 'Singapore Dollar (&#36;)', 'easy-digital-downloads' ),
			'SEK'  => __( 'Swedish Krona', 'easy-digital-downloads' ),
			'CHF'  => __( 'Swiss Franc', 'easy-digital-downloads' ),
			'TWD'  => __( 'Taiwan New Dollars', 'easy-digital-downloads' ),
			'THB'  => __( 'Thai Baht (&#3647;)', 'easy-digital-downloads' ),
			'INR'  => __( 'Indian Rupee (&#8377;)', 'easy-digital-downloads' ),
			'TRY'  => __( 'Turkish Lira (&#8378;)', 'easy-digital-downloads' ),
			'RIAL' => __( 'Iranian Rial (&#65020;)', 'easy-digital-downloads' ),
			'RUB'  => __( 'Russian Rubles', 'easy-digital-downloads' ),
			'AOA'  => __( 'Angolan Kwanza', 'easy-digital-downloads' ),
		);

		$this->assertEquals( $expected, edd_get_currencies() );

	}

	public function test_month_num_to_name() {
		$this->assertEquals( 'Jan', edd_month_num_to_name( 1 ) );
	}

	/**
	 * @covers ::edd_month_num_to_name()
	 */
	public function test_month_num_to_long_name() {
		$this->assertEquals( 'January', edd_month_num_to_name( 1, true ) );
	}

	public function test_get_php_arg_separator_output() {
		$this->assertEquals( '&', edd_get_php_arg_separator_output() );
	}

	public function test_let_to_num() {
		$this->assertEquals( 0, edd_let_to_num( WP_MEMORY_LIMIT ) / ( 1024*1024 ) );
	}

	/**
	 * @covers ::edd_get_symlink_dir
	 */
	public function test_get_symlink_url() {
		$this->assertEquals( 'http://example.org/wp-content/uploads/edd/symlinks', edd_get_symlink_url() );
	}

	public function test_use_skus() {
		$this->assertFalse( edd_use_skus() );
	}

	public function test_edd_is_host() {
		$this->assertFalse( edd_is_host( 'wpengine' ) );
		$this->assertFalse( edd_is_host( 'wp engine' ) );
		$this->assertFalse( edd_is_host( 'WP Engine' ) );
		$this->assertFalse( edd_is_host( 'WPEngine' ) );

		define( 'WPE_APIKEY', 'testkey' );

		$this->assertTrue( edd_is_host( 'wpengine' ) );
		$this->assertTrue( edd_is_host( 'wp engine' ) );
		$this->assertTrue( edd_is_host( 'WP Engine' ) );
		$this->assertTrue( edd_is_host( 'WPEngine' ) );
	}

	public function test_edd_update_option(){
		$key   = 'some-setting';
		$value = 'some-value';
		$isset = edd_get_option( $key, false );

		// The option shouldn't exist
		$this->assertFalse( $isset );

		$updated = edd_update_option( $key, $value );

		// The option should have successfully updated
		$this->assertTrue( $updated );

		// The option retrieve should be equal to the one we set
		$this->assertEquals( $value, edd_get_option( $key, false ) );

		$key   = 'some-setting2';
		$value = null;
		$isset = edd_get_option( $key, false );

		// The option shouldn't exist
		$this->assertFalse( $isset );

		$updated = edd_update_option( $key, $value );

		// The option should return false due to the null value
		$this->assertFalse( $updated );

		// The option retrieve should be false since it doesn't exist
		$this->assertFalse( edd_get_option( $key, false ) );

	}

	public function test_add_cache_busting() {
		add_filter( 'edd_is_caching_plugin_active', '__return_true' );
		$this->assertEquals( 'http://example.org/?nocache=true', edd_add_cache_busting( home_url( '/') ) );
		remove_filter( 'edd_is_caching_plugin_active', '__return_true' );
		$this->assertEquals( 'http://example.org/', edd_add_cache_busting( home_url( '/' ) ) );
	}

	/**
	 * @covers ::edd_get_current_page_url()
	 */
	public function test_get_current_page_url_if_home_should_return_home_url() {
		$this->go_to( home_url( '/' ) );
		$this->assertEquals( 'http://example.org/', edd_get_current_page_url() );
	}

	/**
	 * @covers ::edd_get_current_page_url()
	 */
	public function test_get_current_page_url_if_a_download_page_should_return_that_url() {
		$this->go_to( get_permalink( self::$download->ID ) );
		$this->assertEquals( 'http://' . WP_TESTS_DOMAIN . '/?download=test-download-product', edd_get_current_page_url() );
	}

	/**
	 * @covers ::edd_get_current_page_url()
	 */
	public function test_get_current_page_url_if_no_caching_should_return_url_with_nocache_true() {
		add_filter( 'edd_is_caching_plugin_active', '__return_true' );

			$this->go_to( get_permalink( self::$download->ID ) );

			$this->assertEquals( 'http://' . WP_TESTS_DOMAIN . '/?download=test-download-product&nocache=true', edd_get_current_page_url( true ) );

		remove_filter( 'edd_is_caching_plugin_active', '__return_true' );
	}

	/**
	 * @covers ::edd_get_current_page_url()
	 */
	public function test_get_current_page_url_if_no_cache_checkout_then_current_url_should_match() {
		global $edd_options;

		add_filter( 'edd_is_caching_plugin_active', '__return_true' );

			$edd_options['no_cache_checkout'] = true;

			$this->go_to( get_permalink( $edd_options['purchase_page'] ) );

			$this->assertEquals( edd_get_checkout_uri(), edd_get_current_page_url( true ) );

		remove_filter( 'edd_is_caching_plugin_active', '__return_true' );
	}

	public function test_cart_url_formats() {
		global $edd_options;
		$post = Helpers\EDD_Helper_Download::create_simple_download();

		edd_add_to_cart( $post->ID );

		$this->assertTrue( edd_item_in_cart( $post->ID ) );

		$item_position = edd_get_item_position_in_cart( $post->ID );

		// Go to checkout
		$this->go_to( edd_get_checkout_uri() );

		add_filter( 'edd_is_caching_plugin_active', '__return_true' );

		$remove_url = edd_remove_item_url( $item_position );

		$this->assertStringContainsString( 'page_id=' . $edd_options['purchase_page'], $remove_url );
		$this->assertStringContainsString( 'edd_action=remove', $remove_url );
		$this->assertStringContainsString( 'nocache=true', $remove_url );
		$this->assertStringContainsString( 'cart_item=' . $item_position, $remove_url );

		remove_filter( 'edd_is_caching_plugin_active', '__return_true' );
		unset( $edd_options['no_cache_checkout'] );
		$remove_url = edd_remove_item_url( $item_position );

		$this->assertStringContainsString( 'page_id=' . $edd_options['purchase_page'], $remove_url );
		$this->assertStringContainsString( 'edd_action=remove', $remove_url );
		$this->assertStringContainsString( 'cart_item=' . $item_position, $remove_url );
		$this->assertStringNotContainsString( 'nocache=true', $remove_url );

		// Go home and test again
		$this->go_to( home_url( '/' ) );

		add_filter( 'edd_is_caching_plugin_active', '__return_true' );

		$expected_url = 'http://example.org/?cart_item=' . $item_position . '&edd_action=remove&nocache=true';
		$remove_url   = edd_remove_item_url( $item_position );

		$this->assertStringNotContainsString( 'page_id=', $remove_url );
		$this->assertStringContainsString( 'edd_action=remove', $remove_url );
		$this->assertStringContainsString( 'cart_item=' . $item_position, $remove_url );
		$this->assertStringContainsString( 'nocache=true', $remove_url );

		remove_filter( 'edd_is_caching_plugin_active', '__return_true' );

		$remove_url = edd_remove_item_url( $item_position );

		$this->assertStringNotContainsString( 'page_id=', $remove_url );
		$this->assertStringContainsString( 'edd_action=remove', $remove_url );
		$this->assertStringContainsString( 'cart_item=' . $item_position, $remove_url );
		$this->assertStringNotContainsString( 'nocache=true', $remove_url );

		// Go home and test again
		$this->go_to( home_url( '/' ) );

		add_filter( 'edd_is_caching_plugin_active', '__return_true' );

		$expected_url  = 'http://example.org/?cart_item=' . $item_position . '&edd_action=remove&nocache=true';
		$remove_url    = edd_remove_item_url( $item_position );

		$this->assertEquals( $expected_url, $remove_url );
		remove_filter( 'edd_is_caching_plugin_active', '__return_true' );

		$remove_url    = edd_remove_item_url( $item_position );
		$expected_url  = 'http://example.org/?cart_item=' . $item_position . '&edd_action=remove';

		Helpers\EDD_Helper_Download::delete_download( $post->ID );
	}

	public function test_array_convert() {
		$customer1_id = edd_add_customer( array( 'email' => 'test10@example.com' ) );

		// Test sending a single object in
		$customer_object = new \EDD_Customer( $customer1_id );
		$customer_array  = edd_object_to_array( $customer_object );
		$this->assertIsArray( $customer_array );
		$this->assertEquals( $customer_object->id, $customer_array['id'] );
		$this->assertEquals( $customer_object->email, $customer_array['email'] );
		$this->assertEquals( $customer_object->purchase_count, $customer_array['purchase_count'] );

		// Negative tests (no alterations should occur)
		$this->assertEquals( 'string', edd_object_to_array( 'string' ) );
		$this->assertEquals( array( 'foo', 'bar', 'baz' ), edd_object_to_array( array( 'foo', 'bar', 'baz' ) ) );

		// Test sending in an array of objects
		$customers = edd_get_customers();
		$converted = edd_object_to_array( $customers );
		$this->assertIsArray( $converted[0] );

		// Test payments
		$payment_1 = Helpers\EDD_Helper_Payment::create_simple_payment();
		$payment_2 = Helpers\EDD_Helper_Payment::create_simple_payment();

		$payment_1_obj = new \EDD_Payment( $payment_1 );
		$payment_2_obj = new \EDD_Payment( $payment_2 );

		// Test a single convert
		$payment_1_array = edd_object_to_array( $payment_1_obj );
		$this->assertIsArray(  $payment_1_array );
		$this->assertEquals( $payment_1_obj->ID, $payment_1_array['ID'] );

		$payments = array(
			$payment_1_obj,
			$payment_2_obj,
		);

		$payments_array = edd_object_to_array( $payments );
		$this->assertIsArray( $payments_array[0] );
		$this->assertEquals( 2, count( $payments_array ) );
	}

	// Test getting currency symols:
	function test_gbp_symbol() {
		$this->assertEquals( '&pound;', edd_currency_symbol( 'GBP' ) );
	}

	function test_brl_symbol() {
		$this->assertEquals( 'R&#36;', edd_currency_symbol( 'BRL' ) );
	}

	function test_us_dollar_symbol() {
		$this->assertEquals( '&#36;', edd_currency_symbol( 'USD' ) );
	}

	function test_au_dollar_symbol() {
		$this->assertEquals( '&#36;', edd_currency_symbol( 'AUD' ) );
	}

	function test_nz_dollar_symbol() {
		$this->assertEquals( '&#36;', edd_currency_symbol( 'NZD' ) );
	}

	function test_ca_dollar_symbol() {
		$this->assertEquals( '&#36;', edd_currency_symbol( 'CAD' ) );
	}

	function test_hk_dollar_symbol() {
		$this->assertEquals( '&#36;', edd_currency_symbol( 'HKD' ) );
	}

	function test_mx_dollar_symbol() {
		$this->assertEquals( '&#36;', edd_currency_symbol( 'MXN' ) );
	}

	function test_sg_dollar_symbol() {
		$this->assertEquals( '&#36;', edd_currency_symbol( 'SGD' ) );
	}

	function test_yen_symbol() {
		$this->assertEquals( '&yen;', edd_currency_symbol( 'JPY' ) );
	}

	function test_aoa_symbol() {
		$this->assertEquals( 'Kz', edd_currency_symbol( 'AOA' ) );
	}

	function test_default_symbol() {
		$this->assertEquals( 'CZK', edd_currency_symbol( 'CZK' ) );
	}

	function test_edd_delete_option() {
		edd_update_option( 'test_setting', 'testing' );
		edd_delete_option( 'test_setting' );

		$this->assertFalse( edd_get_option( 'test_setting' ) );
	}

	function test_should_allow_file_download_edd_uploaded_file_url() {
		$file_details   = array ( 'scheme' => 'https', 'host' => site_url(), 'path' => '/wp-content/uploads/edd/2019/04/test-file.jpg' );
		$schemas        = array ( 0 => 'http', 1 => 'https' );
		$requested_file = trailingslashit( site_url() ) . 'wp-content/uploads/edd/2019/04/test-file.jpg';

		$this->assertTrue( edd_local_file_location_is_allowed( $file_details, $schemas, $requested_file ) );
	}

	function test_should_allow_file_download_uploaded_file_in_content_url() {
		$file_details   = array ( 'scheme' => 'https', 'host' => site_url(), 'path' => '/wp-content/my-files/test-file.jpg' );
		$schemas        = array ( 0 => 'http', 1 => 'https' );
		$requested_file = trailingslashit( site_url() ) . '/wp-content/my-files/test-file.jpg';

		$this->assertTrue( edd_local_file_location_is_allowed( $file_details, $schemas, $requested_file ) );
	}

	function test_should_allow_file_download_uploaded_file_in_content_absolute_in_content() {
		$this->write_test_file( trailingslashit( WP_CONTENT_DIR ) . 'test-file.jpg' );
		$file_details   = array ( 'path' => trailingslashit( WP_CONTENT_DIR ) . 'test-file.jpg' );
		$schemas        = array ( 0 => 'http', 1 => 'https' );
		$requested_file =  trailingslashit( WP_CONTENT_DIR ) . 'test-file.jpg';

		$this->assertTrue( edd_local_file_location_is_allowed( $file_details, $schemas, $requested_file ) );
		$this->delete_test_file( trailingslashit( WP_CONTENT_DIR ) . 'test-file.jpg' );
	}

	function test_should_allow_file_download_uploaded_file_in_content_absolute_outside_of_content() {
		$this->write_test_file( trailingslashit( ABSPATH ) . 'test-file.jpg' );
		$file_details   = array ( 'path' => trailingslashit( ABSPATH ) . 'test-file.jpg' );
		$schemas        = array ( 0 => 'http', 1 => 'https' );
		$requested_file =  trailingslashit( ABSPATH ) . 'test-file.jpg';

		$this->assertFalse( edd_local_file_location_is_allowed( $file_details, $schemas, $requested_file ) );
		$this->delete_test_file( trailingslashit( ABSPATH ) . 'test-file.jpg' );
	}

	function test_should_allow_file_download_uploaded_file_in_content_url_on_windows_WAMP() {
		$file_details   = array ( 'scheme' => 'https', 'host' => site_url(), 'path' => 'E:\wamp\www\site\wp/wp-content/my-files/test-file.jpg' );
		$schemas        = array ( 0 => 'http', 1 => 'https' );
		$requested_file = trailingslashit( site_url() ) . '/wp-content/my-files/test-file.jpg';

		$this->assertTrue( edd_local_file_location_is_allowed( $file_details, $schemas, $requested_file ) );
	}

	function test_should_allow_file_download_uploaded_file_in_content_absolute_outside_of_content_on_windows_WAMP() {
		$file_details   = array ( 'path' => 'E:\wamp\www\site\wp/test-file.jpg' );
		$schemas        = array ( 0 => 'http', 1 => 'https' );
		$requested_file = 'E:\wamp\www\site\wp/test-file.jpg';

		$this->assertFalse( edd_local_file_location_is_allowed( $file_details, $schemas, $requested_file ) );
	}

	function test_should_allow_file_download_uploaded_file_in_content_url_on_windows_IIS() {
		$file_details   = array ( 'scheme' => 'https', 'host' => site_url(), 'path' => 'C:\inetpub\wwwroot\mysite/wp-content/my-files/test-file.jpg' );
		$schemas        = array ( 0 => 'http', 1 => 'https' );
		$requested_file = trailingslashit( site_url() ) . '/wp-content/my-files/test-file.jpg';

		$this->assertTrue( edd_local_file_location_is_allowed( $file_details, $schemas, $requested_file ) );
	}

	function test_should_allow_file_download_uploaded_file_in_content_absolute_outside_of_content_on_windows_IIS() {
		$file_details   = array ( 'path' => 'C:\inetpub\wwwroot\mysite/test-file.jpg' );
		$schemas        = array ( 0 => 'http', 1 => 'https' );
		$requested_file = 'C:\inetpub\wwwroot\mysite/test-file.jpg';

		$this->assertFalse( edd_local_file_location_is_allowed( $file_details, $schemas, $requested_file ) );
	}

	function test_is_countable_defined() {
		$this->assertTrue( function_exists( 'is_countable' ) );
	}

	function test_is_iterable_defined() {
		$this->assertTrue( function_exists( 'is_iterable' ) );
	}

	function test_postal_codes_SE_leading_s() {
		$this->assertTrue( edd_purchase_form_validate_cc_zip( 's-12345', 'SE' ) );
	}

	function test_postal_codes_SE_leading_capital_s() {
		$this->assertTrue( edd_purchase_form_validate_cc_zip( 'S-12345', 'SE' ) );
	}

	function test_postal_codes_SE_numeric() {
		$this->assertTrue( edd_purchase_form_validate_cc_zip( '12345', 'SE' ) );
	}

	private function write_test_file( $full_file_path ) {
		$file = FileSystem::fopen( $full_file_path, "w" );
		fwrite( $file,"" );
		fclose( $file );
	}

	private function delete_test_file( $full_file_path ) {
		unlink( $full_file_path );
	}
}
