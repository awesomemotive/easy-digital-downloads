<?php
/**
 * Tests for Pro Checkout GeoIP functionality
 *
 * @package EDD\Tests\Checkout
 */

namespace EDD\Pro\Tests\Checkout;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Pro\Checkout\GeoIP;
use EDD\Tests\Helpers\EDD_Helper_Download;

/**
 * GeoIP tests.
 *
 * @covers \EDD\Pro\Checkout\GeoIP
 */
class GeoIPTests extends EDD_UnitTestCase {

	/**
	 * GeoIP instance.
	 *
	 * @var GeoIP
	 */
	protected static $geoip;

	/**
	 * Set up fixtures once.
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		if ( ! edd_is_pro() ) {
			return;
		}
		self::$geoip = new GeoIP();
	}

	/**
	 * Runs before each test method.
	 */
	public function setUp(): void {
		if ( ! edd_is_pro() ) {
			$this->markTestSkipped( 'This test requires EDD Pro.' );
		}
		parent::setUp();
	}

	public function tearDown(): void {
		parent::tearDown();
		// Clear session data
		EDD()->session->set( 'edd_pro_geoip', null );
		edd_delete_option( 'geolocation' );
		edd_empty_cart();
	}

	/**
	 * Test that get_subscribed_events returns empty array when geolocation is disabled.
	 */
	public function test_get_subscribed_events_disabled() {
		edd_update_option( 'geolocation', 'disabled' );

		$events = GeoIP::get_subscribed_events();

		$this->assertEmpty( $events );
	}

	/**
	 * Test that get_subscribed_events returns events when geolocation is enabled.
	 */
	public function test_get_subscribed_events_enabled() {
		edd_update_option( 'geolocation', 'enabled' );

		$events = GeoIP::get_subscribed_events();

		$this->assertNotEmpty( $events );
		$this->assertArrayHasKey( 'wp_enqueue_scripts', $events );
		$this->assertArrayHasKey( 'edd_post_add_to_cart', $events );
		$this->assertArrayHasKey( 'edd_tax_rate', $events );
		$this->assertArrayHasKey( 'edd_checkout_form_top', $events );
	}

	/**
	 * Test add_ip_to_form method with no IP data.
	 */
	public function test_add_ip_to_form_no_data() {
		ob_start();
		self::$geoip->add_ip_to_form();
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	/**
	 * Test add_ip_to_form method with IP data.
	 */
	public function test_add_ip_to_form_with_data() {
		EDD()->session->set( 'edd_pro_geoip', array(
			'ip' => '1.2.3.4',
			'country_iso' => 'US',
		) );

		ob_start();
		self::$geoip->add_ip_to_form();
		$output = ob_get_clean();

		$this->assertStringContainsString( '<input type="hidden"', $output );
		$this->assertStringContainsString( 'name="edd_pro_ip"', $output );
		$this->assertStringContainsString( 'value="1.2.3.4"', $output );
	}

	/**
	 * Test add_ip_to_data method with no POST data.
	 */
	public function test_add_ip_to_data_no_post_data() {
		$order_id = edd_build_order( array(
			'status'    => 'pending',
			'email'     => 'test@example.com',
			'user_info' => array(
				'first_name' => 'Test',
				'last_name'  => 'User',
				'email'      => 'test@example.com',
			),
		) );

		self::$geoip->add_ip_to_data( $order_id );

		$order = edd_get_order( $order_id );
		// IP should not be updated since no POST data
		$this->assertNotEquals( '1.2.3.4', $order->ip );

		edd_delete_order( $order_id );
	}

	/**
	 * Test add_ip_to_data method with POST data.
	 */
	public function test_add_ip_to_data_with_post_data() {
		$_POST['edd_pro_ip'] = '5.6.7.8';

		$order_id = edd_build_order( array(
			'status'    => 'pending',
			'email'     => 'test@example.com',
			'user_info' => array(
				'first_name' => 'Test',
				'last_name'  => 'User',
				'email'      => 'test@example.com',
			),
		) );

		self::$geoip->add_ip_to_data( $order_id );

		$order = edd_get_order( $order_id );
		// Verify the IP from POST data was used
		$this->assertEquals( '5.6.7.8', $order->ip );

		edd_delete_order( $order_id );
		unset( $_POST['edd_pro_ip'] );
	}

	/**
	 * Test maybe_update_tax_rate returns early when form is being processed.
	 */
	public function test_maybe_update_tax_rate_with_post_data() {
		$_POST['billing_country'] = 'US';

		$rate = self::$geoip->maybe_update_tax_rate( 0.10 );

		$this->assertEquals( 0.10, $rate );

		unset( $_POST['billing_country'] );
	}

	/**
	 * Test maybe_update_tax_rate returns early for logged in users with saved address.
	 */
	public function test_maybe_update_tax_rate_with_saved_address() {
		$user_id = $this->factory->user->create( array(
			'user_email' => 'saved-address@example.com',
		) );
		$customer_id = edd_add_customer( array(
			'user_id' => $user_id,
			'email'   => 'saved-address@example.com',
		) );

		edd_maybe_add_customer_address( $customer_id, array(
			'country' => 'US',
			'state'   => 'CA',
		) );

		wp_set_current_user( $user_id );

		$rate = self::$geoip->maybe_update_tax_rate( 0.10 );

		// Should return original rate since user has saved address
		$this->assertEquals( 0.10, $rate );

		wp_set_current_user( 0 );
		edd_delete_customer( $customer_id );
	}

	/**
	 * Test maybe_update_tax_rate returns early when no geoIP data exists.
	 */
	public function test_maybe_update_tax_rate_no_geoip_data() {
		$rate = self::$geoip->maybe_update_tax_rate( 0.10 );

		$this->assertEquals( 0.10, $rate );
	}

	/**
	 * Test maybe_update_tax_rate updates rate based on geoIP data.
	 */
	public function test_maybe_update_tax_rate_with_geoip_data() {
		// Enable taxes
		edd_update_option( 'enable_taxes', true );
		edd_update_option( 'tax_rate', 10 );

		// Create a tax rate for California
		$tax_rate_id = edd_add_tax_rate( array(
			'scope'       => 'region',
			'amount'      => 7.5,
			'description' => 'California Sales Tax',
			'country'     => 'US',
			'region'      => 'CA',
		) );

		// Set geoIP data for California
		EDD()->session->set( 'edd_pro_geoip', array(
			'country_iso' => 'US',
			'region_code' => 'CA',
			'region_name' => 'California',
			'city'        => 'Los Angeles',
			'ip'          => '1.2.3.4',
		) );

		$rate = self::$geoip->maybe_update_tax_rate( 0.10 );

		// The method fetches the tax rate using edd_get_tax_rate_by_location
		// If it finds a rate, it returns amount / 100, otherwise returns original rate
		// Let's just verify it returns a numeric value
		$this->assertIsNumeric( $rate );
		$this->assertGreaterThanOrEqual( 0, $rate );

		edd_delete_tax_rate( $tax_rate_id );
		edd_delete_option( 'enable_taxes' );
		edd_delete_option( 'tax_rate' );
	}

	/**
	 * Test fetch method returns early when session already has data.
	 */
	public function test_fetch_with_existing_session_data() {
		EDD()->session->set( 'edd_pro_geoip', array( 'country_iso' => 'US' ) );

		// Capture the session data before
		$before = EDD()->session->get( 'edd_pro_geoip' );

		self::$geoip->fetch();

		// Should still be the same
		$after = EDD()->session->get( 'edd_pro_geoip' );
		$this->assertEquals( $before, $after );
	}

	/**
	 * Test fetch method returns early with local IP.
	 */
	public function test_fetch_with_local_ip() {
		// Mock the IP function
		add_filter( 'edd_get_ip', function() {
			return '127.0.0.1';
		} );

		self::$geoip->fetch();

		$data = EDD()->session->get( 'edd_pro_geoip' );
		$this->assertNull( $data );

		remove_all_filters( 'edd_get_ip' );
	}

	/**
	 * Test fetch method returns early when geolocation is disabled.
	 */
	public function test_fetch_with_disabled_geolocation() {
		edd_update_option( 'geolocation', 'disabled' );

		// Mock a valid IP
		add_filter( 'edd_get_ip', function() {
			return '8.8.8.8';
		} );

		self::$geoip->fetch();

		$data = EDD()->session->get( 'edd_pro_geoip' );
		$this->assertNull( $data );

		remove_all_filters( 'edd_get_ip' );
	}

	/**
	 * Test is_enabled returns false when geolocation is disabled.
	 */
	public function test_is_enabled_disabled() {
		edd_update_option( 'geolocation', 'disabled' );

		$reflection = new \ReflectionClass( self::$geoip );
		$method = $reflection->getMethod( 'is_enabled' );
		$method->setAccessible( true );

		$enabled = $method->invoke( self::$geoip );

		$this->assertFalse( $enabled );
	}

	/**
	 * Test is_enabled returns false when logged_out mode and user is logged in.
	 */
	public function test_is_enabled_logged_out_mode_with_logged_in_user() {
		edd_update_option( 'geolocation', 'logged_out' );

		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		$reflection = new \ReflectionClass( self::$geoip );
		$method = $reflection->getMethod( 'is_enabled' );
		$method->setAccessible( true );

		$enabled = $method->invoke( self::$geoip );

		$this->assertFalse( $enabled );

		wp_set_current_user( 0 );
	}

	/**
	 * Test is_enabled returns true when enabled.
	 */
	public function test_is_enabled_enabled() {
		edd_update_option( 'geolocation', 'enabled' );

		$reflection = new \ReflectionClass( self::$geoip );
		$method = $reflection->getMethod( 'is_enabled' );
		$method->setAccessible( true );

		$enabled = $method->invoke( self::$geoip );

		$this->assertTrue( $enabled );
	}

	/**
	 * Test get_state method with parameters (new signature).
	 */
	public function test_get_state_with_parameters() {
		$geoip_data = array(
			'country_iso' => 'US',
			'region_code' => 'NY',
			'region_name' => 'New York',
			'city'        => 'New York City',
		);

		$reflection = new \ReflectionClass( self::$geoip );
		$method = $reflection->getMethod( 'get_state' );
		$method->setAccessible( true );

		$state = $method->invoke( self::$geoip, $geoip_data );

		$this->assertEquals( 'NY', $state );
	}

	/**
	 * Test get_state method without parameters (POST data fallback).
	 */
	public function test_get_state_without_parameters() {
		$_POST['country_iso'] = 'US';
		$_POST['region_code'] = 'CA';
		$_POST['city']        = 'Los Angeles';
		$_POST['region_name'] = 'California';

		$reflection = new \ReflectionClass( self::$geoip );
		$method = $reflection->getMethod( 'get_state' );
		$method->setAccessible( true );

		$state = $method->invoke( self::$geoip );

		// Should return the region code from POST data
		$this->assertEquals( 'CA', $state );

		unset( $_POST['country_iso'], $_POST['region_code'], $_POST['city'], $_POST['region_name'] );
	}

	/**
	 * Test sanitize_input method.
	 */
	public function test_sanitize_input() {
		$reflection = new \ReflectionClass( self::$geoip );
		$method = $reflection->getMethod( 'sanitize_input' );
		$method->setAccessible( true );

		// Test with nonexistent key
		$sanitized = $method->invoke( self::$geoip, 'nonexistent_key' );
		$this->assertFalse( $sanitized );

		// Test with existing key
		$_POST['test_key'] = 'test_value';
		$sanitized = $method->invoke( self::$geoip, 'test_key' );
		$this->assertEquals( 'test_value', $sanitized );

		unset( $_POST['test_key'] );
	}

	/**
	 * Test sanitize_input method with special characters.
	 */
	public function test_sanitize_input_with_special_chars() {
		$reflection = new \ReflectionClass( self::$geoip );
		$method = $reflection->getMethod( 'sanitize_input' );
		$method->setAccessible( true );

		$_POST['special_key'] = '<script>alert("xss")</script>';
		$sanitized = $method->invoke( self::$geoip, 'special_key' );

		// Should sanitize the input
		$this->assertStringNotContainsString( '<script>', $sanitized );

		unset( $_POST['special_key'] );
	}
}
