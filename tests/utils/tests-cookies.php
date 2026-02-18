<?php

namespace EDD\Tests\Utils;

use EDD\Utils\Cookies as Utility;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Cookies extends EDD_UnitTestCase {

	/**
	 * Stores filter data for testing.
	 *
	 * @since 3.3.0
	 * @var array
	 */
	private $filter_data = array();

	/**
	 * Set up before each test.
	 *
	 * @since 3.3.0
	 */
	public function setUp(): void {
		parent::setUp();
		// Clear any existing cookies for testing.
		$_COOKIE           = array();
		$this->filter_data = array();
	}

	/**
	 * Tear down after each test.
	 *
	 * @since 3.3.0
	 */
	public function tearDown(): void {
		parent::tearDown();
		// Remove any filters added during tests.
		remove_all_filters( 'edd_cookie_options' );
		$this->filter_data = array();
	}

	/**
	 * Test that get() returns false when cookie is not set.
	 *
	 * @since 3.3.0
	 */
	public function test_get_returns_false_when_cookie_not_set() {
		$result = Utility::get( 'test_cookie' );

		$this->assertFalse( $result );
	}

	/**
	 * Test that get() returns cookie value when cookie is set.
	 *
	 * @since 3.3.0
	 */
	public function test_get_returns_cookie_value() {
		$_COOKIE['test_cookie'] = 'test_value';

		$result = Utility::get( 'test_cookie' );

		$this->assertSame( 'test_value', $result );
	}

	/**
	 * Test that set() returns false when trying to delete non-existent cookie.
	 *
	 * @since 3.3.0
	 */
	public function test_set_returns_false_when_deleting_nonexistent_cookie() {
		$result = Utility::set( 'test_cookie', '', null );

		$this->assertFalse( $result );
	}

	/**
	 * Test that edd_cookie_options filter is applied with default values.
	 *
	 * @since 3.6.5
	 */
	public function test_edd_cookie_options_filter_default_values() {
		add_filter( 'edd_cookie_options', array( $this, 'capture_filter_args' ), 10, 3 );

		$expiration = time() + 3600;
		$options    = $this->invoke_get_options( $expiration, 'test_cookie' );

		$this->assertTrue( $this->filter_data['called'], 'The edd_cookie_options filter should be called' );
		$this->assertSame( $expiration, $this->filter_data['expiration'] );
		$this->assertSame( 'test_cookie', $this->filter_data['cookie'] );
		$this->assertIsArray( $options );
		$this->assertArrayHasKey( 'expires', $options );
		$this->assertArrayHasKey( 'path', $options );
		$this->assertArrayHasKey( 'domain', $options );
		$this->assertArrayHasKey( 'secure', $options );
		$this->assertArrayHasKey( 'httponly', $options );
		$this->assertArrayHasKey( 'samesite', $options );
	}

	/**
	 * Test that edd_cookie_options filter can modify cookie options.
	 *
	 * @since 3.6.5
	 */
	public function test_edd_cookie_options_filter_can_modify_options() {
		add_filter( 'edd_cookie_options', array( $this, 'modify_cookie_options' ), 10, 3 );

		$expiration = time() + 3600;
		$options    = $this->invoke_get_options( $expiration, 'test_cookie' );

		$this->assertSame( 'Strict', $options['samesite'] );
		$this->assertFalse( $options['httponly'] );
		$this->assertSame( 'value', $options['custom'] );
	}

	/**
	 * Test that edd_cookie_options filter receives correct cookie name.
	 *
	 * @since 3.6.5
	 */
	public function test_edd_cookie_options_filter_receives_correct_cookie_name() {
		add_filter( 'edd_cookie_options', array( $this, 'capture_cookie_name' ), 10, 3 );

		$this->invoke_get_options( time() + 3600, 'my_custom_cookie' );

		$this->assertSame( 'my_custom_cookie', $this->filter_data['cookie_name'] );
	}

	/**
	 * Test that edd_cookie_options filter receives null expiration when deleting.
	 *
	 * @since 3.6.5
	 */
	public function test_edd_cookie_options_filter_receives_null_expiration_when_deleting() {
		add_filter( 'edd_cookie_options', array( $this, 'capture_expiration' ), 10, 3 );

		$this->invoke_get_options( null, 'test_cookie' );

		$this->assertNull( $this->filter_data['expiration_value'] );
	}

	/**
	 * Test that edd_cookie_options filter can set different options per cookie.
	 *
	 * @since 3.6.5
	 */
	public function test_edd_cookie_options_filter_can_customize_per_cookie() {
		add_filter( 'edd_cookie_options', array( $this, 'customize_per_cookie' ), 10, 3 );

		$expiration = time() + 3600;
		$options1   = $this->invoke_get_options( $expiration, 'secure_cookie' );
		$options2   = $this->invoke_get_options( $expiration, 'tracking_cookie' );

		$this->assertSame( 'Strict', $options1['samesite'] );
		$this->assertTrue( $options1['secure'] );
		$this->assertSame( 'None', $options2['samesite'] );
		$this->assertTrue( $options2['secure'] );
	}

	/**
	 * Test that default cookie options include httponly set to true.
	 *
	 * @since 3.6.5
	 */
	public function test_default_cookie_options_include_httponly() {
		$options = $this->invoke_get_options( time() + 3600, 'test_cookie' );

		$this->assertSame( true, $options['httponly'] );
	}

	/**
	 * Test that default cookie options include samesite set to Lax.
	 *
	 * @since 3.6.5
	 */
	public function test_default_cookie_options_include_samesite_lax() {
		$options = $this->invoke_get_options( time() + 3600, 'test_cookie' );

		$this->assertSame( 'Lax', $options['samesite'] );
	}

	/**
	 * Test that default cookie options include secure based on is_ssl().
	 *
	 * @since 3.6.5
	 */
	public function test_default_cookie_options_include_secure_based_on_ssl() {
		$options = $this->invoke_get_options( time() + 3600, 'test_cookie' );

		$this->assertSame( is_ssl(), $options['secure'] );
	}

	/**
	 * Test that get_expiration returns past time when null is passed.
	 *
	 * @since 3.3.0
	 */
	public function test_expiration_calculation_for_null() {
		$options = $this->invoke_get_options( null, 'test_cookie' );

		// When expiration is null, it should be set to time() - 3600 (past time).
		$this->assertLessThan( time(), $options['expires'] );
	}

	/**
	 * Test that get_expiration preserves provided timestamp.
	 *
	 * @since 3.3.0
	 */
	public function test_expiration_calculation_preserves_timestamp() {
		$expiration = time() + 7200;
		$options    = $this->invoke_get_options( $expiration, 'test_cookie' );

		$this->assertSame( $expiration, $options['expires'] );
	}

	/**
	 * Test that cookie path defaults to COOKIEPATH.
	 *
	 * @since 3.6.5
	 */
	public function test_default_cookie_path_is_cookiepath() {
		$options = $this->invoke_get_options( time() + 3600, 'test_cookie' );

		$this->assertSame( COOKIEPATH, $options['path'] );
	}

	/**
	 * Test that cookie domain defaults to COOKIE_DOMAIN.
	 *
	 * @since 3.6.5
	 */
	public function test_default_cookie_domain_is_cookie_domain() {
		$options = $this->invoke_get_options( time() + 3600, 'test_cookie' );

		$this->assertSame( COOKIE_DOMAIN, $options['domain'] );
	}

	/**
	 * Test that filter can override path for specific cookies.
	 *
	 * @since 3.6.5
	 */
	public function test_filter_can_override_path_for_specific_cookie() {
		add_filter(
			'edd_cookie_options',
			function ( $options, $expiration, $cookie ) {
				if ( 'special_cookie' === $cookie ) {
					$options['path'] = '/custom-path/';
				}
				return $options;
			},
			10,
			3
		);

		$options = $this->invoke_get_options( time() + 3600, 'special_cookie' );

		$this->assertSame( '/custom-path/', $options['path'] );
	}

	/**
	 * Test that filter can override domain for specific cookies.
	 *
	 * @since 3.6.5
	 */
	public function test_filter_can_override_domain_for_specific_cookie() {
		add_filter(
			'edd_cookie_options',
			function ( $options, $expiration, $cookie ) {
				if ( 'subdomain_cookie' === $cookie ) {
					$options['domain'] = '.example.com';
				}
				return $options;
			},
			10,
			3
		);

		$options = $this->invoke_get_options( time() + 3600, 'subdomain_cookie' );

		$this->assertSame( '.example.com', $options['domain'] );
	}

	/**
	 * Test that filter receives correct expiration timestamp.
	 *
	 * @since 3.6.5
	 */
	public function test_filter_receives_correct_expiration_timestamp() {
		$test_expiration = time() + 86400; // 24 hours from now

		add_filter( 'edd_cookie_options', array( $this, 'capture_filter_args' ), 10, 3 );

		$this->invoke_get_options( $test_expiration, 'test_cookie' );

		$this->assertSame( $test_expiration, $this->filter_data['expiration'] );
	}

	/**
	 * Helper method to invoke the private get_options method.
	 *
	 * @since 3.6.5
	 * @param int|null $expiration The expiration timestamp.
	 * @param string   $cookie     The cookie name.
	 * @return array The cookie options.
	 */
	private function invoke_get_options( $expiration, $cookie ) {
		$reflection = new \ReflectionClass( Utility::class );
		$method     = $reflection->getMethod( 'get_options' );
		$method->setAccessible( true );

		return $method->invokeArgs( null, array( $expiration, $cookie ) );
	}

	/**
	 * Helper method to capture all filter arguments.
	 *
	 * @since 3.6.5
	 * @param array    $options    The cookie options.
	 * @param int|null $expiration The expiration timestamp.
	 * @param string   $cookie     The cookie name.
	 * @return array
	 */
	public function capture_filter_args( $options, $expiration, $cookie ) {
		$this->filter_data['called']     = true;
		$this->filter_data['options']    = $options;
		$this->filter_data['expiration'] = $expiration;
		$this->filter_data['cookie']     = $cookie;

		return $options;
	}

	/**
	 * Helper method to modify cookie options in filter.
	 *
	 * @since 3.6.5
	 * @param array    $options    The cookie options.
	 * @param int|null $expiration The expiration timestamp.
	 * @param string   $cookie     The cookie name.
	 * @return array
	 */
	public function modify_cookie_options( $options, $expiration, $cookie ) {
		$options['samesite'] = 'Strict';
		$options['httponly'] = false;
		$options['custom']   = 'value';

		return $options;
	}

	/**
	 * Helper method to capture cookie name.
	 *
	 * @since 3.6.5
	 * @param array    $options    The cookie options.
	 * @param int|null $expiration The expiration timestamp.
	 * @param string   $cookie     The cookie name.
	 * @return array
	 */
	public function capture_cookie_name( $options, $expiration, $cookie ) {
		$this->filter_data['cookie_name'] = $cookie;

		return $options;
	}

	/**
	 * Helper method to capture expiration value.
	 *
	 * @since 3.6.5
	 * @param array    $options    The cookie options.
	 * @param int|null $expiration The expiration timestamp.
	 * @param string   $cookie     The cookie name.
	 * @return array
	 */
	public function capture_expiration( $options, $expiration, $cookie ) {
		$this->filter_data['expiration_value'] = $expiration;

		return $options;
	}

	/**
	 * Helper method to customize options per cookie.
	 *
	 * @since 3.6.5
	 * @param array    $options    The cookie options.
	 * @param int|null $expiration The expiration timestamp.
	 * @param string   $cookie     The cookie name.
	 * @return array
	 */
	public function customize_per_cookie( $options, $expiration, $cookie ) {
		// Customize based on cookie name.
		if ( 'secure_cookie' === $cookie ) {
			$options['samesite'] = 'Strict';
			$options['secure']   = true;
		}

		if ( 'tracking_cookie' === $cookie ) {
			$options['samesite'] = 'None';
			$options['secure']   = true;
		}

		return $options;
	}
}
