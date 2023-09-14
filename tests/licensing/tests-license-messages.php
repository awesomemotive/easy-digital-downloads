<?php
/**
 * Tests for license messaging.
 */
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Tests_License_Messages extends EDD_UnitTestCase {

	public function test_license_expired() {
		$yesterday = strtotime( '-1 day', current_time( 'timestamp' ) );
		$args      = $this->get_args(
			array(
				'status'  => 'expired',
				'expires' => date( 'Y-m-d 23:59:59', $yesterday ),
			)
		);
		$expected  = sprintf( 'Your license key expired on %s.', edd_date_i18n( $yesterday ) );
		$message   = $this->get_message( $args );

		$this->assertStringContainsString( $expected, $message );
		$this->assertStringContainsString( $args['license_key'], $message );
	}

	public function test_license_lifetime() {
		$expected = 'License key never expires.';

		$this->assertStringContainsString( $expected, $this->get_message( $this->get_args() ) );
	}

	public function test_license_revoked() {
		$args     = $this->get_args(
			array(
				'status' => 'revoked',
			)
		);
		$expected = 'Your license key has been disabled.';

		$this->assertStringContainsString( $expected, $this->get_message( $args ) );
	}

	public function test_license_invalid_item_id() {
		$args     = $this->get_args(
			array(
				'status' => 'invalid_item_id',
			)
		);
		$expected = sprintf( 'This appears to be an invalid license key for %s.', $args['name'] );

		$this->assertStringContainsString( $expected, $this->get_message( $args ) );
	}

	public function test_license_no_activations_left() {
		$args     = $this->get_args(
			array(
				'status' => 'no_activations_left',
			)
		);
		$expected = 'Your license key has reached its activation limit.';

		$this->assertStringContainsString( $expected, $this->get_message( $args ) );
	}

	public function test_license_missing() {
		$args     = $this->get_args(
			array(
				'status' => 'missing',
			)
		);
		$expected = 'Invalid license.';

		$this->assertStringContainsString( $expected, $this->get_message( $args ) );
	}

	public function test_license_expires_tomorrow() {
		$tomorrow = strtotime( '+1 day', current_time( 'timestamp' ) );
		$args     = $this->get_args(
			array(
				'expires' => date( 'Y-m-d 23:59:59', $tomorrow ),
			)
		);
		$expected = sprintf( 'Your license key expires soon! It expires on %s.', edd_date_i18n( $tomorrow ) );

		$this->assertStringContainsString( $expected, $this->get_message( $args ) );
	}

	public function test_license_expires_next_month() {
		$next_month = strtotime( '+32 days', current_time( 'timestamp' ) );
		$args       = $this->get_args(
			array(
				'expires' => date( 'Y-m-d 23:59:59', $next_month ),
			)
		);
		$expected   = sprintf( 'Your license key expires on %s.', edd_date_i18n( $next_month ) );

		$this->assertStringContainsString( $expected, $this->get_message( $args ) );
	}

	public function test_license_third_party_missing() {
		$args = $this->get_args(
			array(
				'status'  => 'missing',
				'api_url' => 'https://example.com',
			)
		);

		$this->assertStringContainsString( 'Please verify it.', $this->get_message( $args ) );
	}

	public function test_license_third_party_custom_uri() {
		$args = $this->get_args(
			array(
				'status'  => 'site_inactive',
				'api_url' => 'https://example.com',
				'uri'     => 'https://example.com',
			)
		);

		$this->assertStringContainsString( 'https://example.com', $this->get_message( $args ) );
	}

	private function get_args( $args = array() ) {
		return wp_parse_args(
			$args,
			array(
				'status'      => 'valid',
				'license_key' => 'bgvear89p7ty4qbrjkc4',
				'expires'     => 'lifetime',
				'name'        => 'Stripe Pro Payment Gateway',
			)
		);
	}

	private function get_message( $args ) {
		$messages = new \EDD\Licensing\Messages( $args );

		return $messages->get_message();
	}
}
