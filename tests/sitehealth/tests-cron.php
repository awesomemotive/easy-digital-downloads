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
		$this->assertEquals( 'Easy Digital Downloads &mdash; Cron Events', $this->get_data()['label'] );
	}

	public function test_fields_has_daily_key() {
		$this->assertArrayHasKey( 'daily', $this->get_data()['fields'] );
	}

	public function test_daily_value() {
		$this->assertEquals( 'Not Scheduled', $this->get_data()['fields']['daily']['value'] );
	}

	public function test_fields_has_stripe_key() {
		$this->assertArrayHasKey( 'stripe', $this->get_data()['fields'] );
	}

	public function test_fields_has_sessions_key() {
		$this->assertArrayHasKey( 'sessions', $this->get_data()['fields'] );
	}

	private function get_data() {
		if ( is_null( self::$data ) ) {
			$cron       = new \EDD\Admin\SiteHealth\Cron();
			self::$data = $cron->get();
		}

		return self::$data;
	}
}
