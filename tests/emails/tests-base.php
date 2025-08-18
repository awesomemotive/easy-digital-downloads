<?php

namespace EDD\Tests\Emails;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class BaseEmail extends EDD_UnitTestCase {

	/**
	 * Base email object.
	 *
	 * @var \EDD\Emails\Base
	 */
	private $base_email;

	public function setUp(): void {
		parent::setUp();
		$this->base_email = new \EDD\Emails\Base();
	}

	public function test_get_headers_array_default() {
		$reflection = new \ReflectionClass( $this->base_email );
		$method = $reflection->getMethod( 'get_headers_array' );
		$method->setAccessible( true );
		
		$headers = $method->invoke( $this->base_email );
		
		$this->assertIsArray( $headers );
		$this->assertArrayHasKey( 'From', $headers );
		$this->assertArrayHasKey( 'Reply-To', $headers );
		$this->assertArrayHasKey( 'Content-Type', $headers );
	}

	public function test_get_headers_array_filter_applied() {
		$reflection = new \ReflectionClass( $this->base_email );
		$method = $reflection->getMethod( 'get_headers_array' );
		$method->setAccessible( true );
		
		add_filter( 'edd_email_headers_array', function( $headers ) {
			$headers['Reply-To'] = 'customer@example.com';
			$headers['X-Custom-Header'] = 'Test Value';
			return $headers;
		} );
		
		$headers = $method->invoke( $this->base_email );
		
		$this->assertEquals( 'customer@example.com', $headers['Reply-To'] );
		$this->assertArrayHasKey( 'X-Custom-Header', $headers );
		$this->assertEquals( 'Test Value', $headers['X-Custom-Header'] );
		
		remove_all_filters( 'edd_email_headers_array' );
	}

	public function test_get_headers_array_multiple_filters() {
		$reflection = new \ReflectionClass( $this->base_email );
		$method = $reflection->getMethod( 'get_headers_array' );
		$method->setAccessible( true );
		
		add_filter( 'edd_email_headers_array', function( $headers ) {
			$headers['Reply-To'] = 'customer@example.com';
			return $headers;
		}, 10 );
		
		add_filter( 'edd_email_headers_array', function( $headers ) {
			$headers['X-Priority'] = '1';
			return $headers;
		}, 20 );
		
		$headers = $method->invoke( $this->base_email );
		
		$this->assertEquals( 'customer@example.com', $headers['Reply-To'] );
		$this->assertEquals( '1', $headers['X-Priority'] );
		
		remove_all_filters( 'edd_email_headers_array' );
	}

	public function test_get_headers_string_format() {
		$headers_string = $this->base_email->get_headers();
		
		$this->assertIsString( $headers_string );
		$this->assertStringContainsString( 'From:', $headers_string );
		$this->assertStringContainsString( 'Reply-To:', $headers_string );
		$this->assertStringContainsString( 'Content-Type:', $headers_string );
	}

	public function test_get_headers_with_filter_modification() {
		add_filter( 'edd_email_headers_array', function( $headers ) {
			$headers['Reply-To'] = 'modified@example.com';
			return $headers;
		} );
		
		$headers_string = $this->base_email->get_headers();
		
		$this->assertStringContainsString( 'Reply-To: modified@example.com', $headers_string );
		
		remove_all_filters( 'edd_email_headers_array' );
	}

	public function test_headers_filter_preserves_structure() {
		add_filter( 'edd_email_headers_array', function( $headers ) {
			unset( $headers['From'] );
			$headers['Reply-To'] = 'test@example.com';
			return $headers;
		} );
		
		$reflection = new \ReflectionClass( $this->base_email );
		$method = $reflection->getMethod( 'get_headers_array' );
		$method->setAccessible( true );
		
		$headers = $method->invoke( $this->base_email );
		
		$this->assertArrayNotHasKey( 'From', $headers );
		$this->assertEquals( 'test@example.com', $headers['Reply-To'] );
		
		remove_all_filters( 'edd_email_headers_array' );
	}

	public function test_get_default_reply_to() {
		$reflection = new \ReflectionClass( $this->base_email );
		$method = $reflection->getMethod( 'get_headers_array' );
		$method->setAccessible( true );
		
		$headers = $method->invoke( $this->base_email );
		
		$this->assertEquals( $this->base_email->get_from_address(), $headers['Reply-To'] );
	}

	public function test_content_type_header() {
		$reflection = new \ReflectionClass( $this->base_email );
		$method = $reflection->getMethod( 'get_headers_array' );
		$method->setAccessible( true );
		
		$headers = $method->invoke( $this->base_email );
		
		$this->assertStringContainsString( 'text/html', $headers['Content-Type'] );
		$this->assertStringContainsString( 'charset=utf-8', $headers['Content-Type'] );
	}

	public function test_from_header_format() {
		$reflection = new \ReflectionClass( $this->base_email );
		$method = $reflection->getMethod( 'get_headers_array' );
		$method->setAccessible( true );
		
		$headers = $method->invoke( $this->base_email );
		
		$from_name = $this->base_email->get_from_name();
		$from_email = $this->base_email->get_from_address();
		$expected_from = "{$from_name} <{$from_email}>";
		
		$this->assertEquals( $expected_from, $headers['From'] );
	}
}