<?php

namespace EDD\Tests\Settings\Sanitize\Tabs\Gateways;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\Settings\Sanitize\Tabs\Gateways\Accounting;
use EDD\Admin\Settings\Sanitize;
use EDD\Settings\Setting;

class AccountingSection extends EDD_UnitTestCase {
	public function test_legacy_admin_sanitize_function() {
		$sanitizer = new Sanitize();
		$this->assertSame(
			array(
				'test_option' => 'test_value'
			),
			$sanitizer->sanitize_sequential_order_numbers(
				array(
					'test_option' => 'test_value'
				)
			)
		);
	}

	public function test_empty_sequential_start() {
		$this->assertSame( array( 'test_option' => 'test_value' ), Accounting::sanitize( array( 'test_option' => 'test_value' ) ) );
	}

	public function test_with_next_order_number_not_set() {
		delete_option( 'edd_next_order_number' );
		$this->assertSame(
			array(
				'test_option'      => 'test_value',
				'sequential_start' => 100
			),
			Accounting::sanitize(
				array(
					'test_option'      => 'test_value',
					'sequential_start' => 100
				)
			)
		);
	}

	public function test_with_next_order_number_same_as_current() {
		Setting::update( 'sequential_start', 100 );
		$this->assertSame(
			array(
				'test_option'      => 'test_value',
				'sequential_start' => 100
			),
			Accounting::sanitize(
				array(
					'test_option'      => 'test_value',
					'sequential_start' => 100
				)
			)
		);
	}

	public function test_with_sequential_start_higher_than_current_start() {
		update_option( 'edd_next_order_number', 105 );

		$this->assertSame(
			array(
				'sequential_start' => 205
			),
			Accounting::sanitize(
				array(
					'sequential_start' => 205
				)
			)
		);

		$this->assertSame( 205, (int) get_option( 'edd_next_order_number' ) );
	}

	public function test_with_sequential_start_lower_than_most_recent_order_number() {
		/**
		 * Due to the way the tests work and the sequential system, this is difficult to write a test for.
		 *
		 * In our attempts, the issue is that the self::get_most_recent_order_number() is returning an empty string
		 * as the orders created in this class aren't getting the order number applied even with the setting enabled.
		 */
		$this->markTestIncomplete( 'This test needs to be written but cannot yet.' );
	}
}
