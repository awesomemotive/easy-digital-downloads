<?php

namespace EDD\Tests\Settings\Sanitize\Tabs\Gateways;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\Settings\Sanitize\Tabs\General\Currency;

class CurrencySection extends EDD_UnitTestCase {
	public function test_empty_currency() {
		$this->assertSame(
			array(
				'currency' => '',
			),
			Currency::sanitize(
				array(
					'currency' => '',
				)
			)
		);
	}

	public function test_non_registered_currency() {
		$this->assertSame(
			array(
				'currency' => 'USD',
			),
			Currency::sanitize(
				array(
					'currency' => 'non-registered-currency',
				)
			)
		);
	}

	public function test_registered_currency() {
		$this->assertSame(
			array(
				'currency' => 'EUR',
			),
			Currency::sanitize(
				array(
					'currency' => 'EUR',
				)
			)
		);
	}
}
