<?php

namespace EDD\Tests\SiteHealth;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Cron extends EDD_UnitTestCase {

	private static $data;

	public static function setUpBeforeClass(): void {
		add_filter( 'edd_is_gateway_active', function( $retval, $gateway, $gateways ) {
			if ( 'stripe' === $gateway ) {
				return true;
			}
			return $retval;
		}, 10, 3 );
	}

	public function test_label() {
		$this->assertEquals( 'Easy Digital Downloads &mdash; Scheduled Events', $this->get_data()['label'] );
	}

	public function test_fields_has_daily_key() {
		$this->assertArrayHasKey( 'event_daily', $this->get_data()['fields'] );
	}

	public function test_daily_value() {
		$value = $this->get_data()['fields']['event_daily']['value'];

		// Value should be either 'Not Scheduled' or a date string.
		$this->assertTrue(
			$value === 'Not Scheduled' || strpos( $value, '202' ) !== false,
			'Daily value should be "Not Scheduled" or a date string'
		);
	}

	public function test_fields_has_stripe_key() {
		$this->assertArrayHasKey( 'event_stripe', $this->get_data()['fields'] );
	}

	public function test_fields_has_sessions_key() {
		$this->assertArrayHasKey( 'event_sessions', $this->get_data()['fields'] );
	}

	public function test_fields_has_active_scheduler() {
		$this->assertArrayHasKey( 'system_active_scheduler', $this->get_data()['fields'] );
	}

	public function test_active_scheduler_has_value() {
		$scheduler = $this->get_data()['fields']['system_active_scheduler']['value'];

		$this->assertNotEmpty( $scheduler );
		$this->assertIsString( $scheduler );
	}

	public function test_fields_has_registered_events() {
		$this->assertArrayHasKey( 'system_registered_events', $this->get_data()['fields'] );
	}

	public function test_registered_events_is_numeric() {
		$count = $this->get_data()['fields']['system_registered_events']['value'];

		$this->assertIsNumeric( $count );
	}

	public function test_fields_has_registered_components() {
		$this->assertArrayHasKey( 'system_registered_components', $this->get_data()['fields'] );
	}

	public function test_registered_components_is_numeric() {
		$count = $this->get_data()['fields']['system_registered_components']['value'];

		$this->assertIsNumeric( $count );
	}

	private function get_data() {
		if ( is_null( self::$data ) ) {
			$cron       = new \EDD\Admin\SiteHealth\Cron();
			self::$data = $cron->get();
		}

		return self::$data;
	}
}
