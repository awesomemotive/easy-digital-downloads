<?php

namespace EDD\Tests\Settings\Sanitize\Tabs\Gateways;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\Settings\Sanitize\Tabs\Gateways\Main;

class MainSection extends EDD_UnitTestCase {
	public function test_empty_default_gateway() {
		$this->assertSame(
			array(
				'default_gateway' => '',
			),
			Main::sanitize(
				array(
					'default_gateway' => '',
				)
			)
		);
	}

	public function test_no_gateways() {
		$this->assertSame(
			array(
				'gateways' => '',
			),
			Main::sanitize(
				array(
					'gateways'        => '',
					'default_gateway' => 'paypal',
				)
			)
		);
	}

	public function test_negative_one_gateways() {
		$this->assertSame(
			array(
				'gateways' => '-1',
			),
			Main::sanitize(
				array(
					'default_gateway' => 'paypal',
					'gateways'        => '-1',
				)
			)
		);
	}

	public function test_default_gateway_not_enabled() {
		$this->assertSame(
			array(
				'default_gateway' => 'paypal',
				'gateways'        => array(
					'paypal' => '1',
					'manual' => '1',
				),
			),
			Main::sanitize(
				array(
					'default_gateway' => 'stripe',
					'gateways'        => array(
						'paypal' => '1',
						'manual' => '1',
					),
				)
			)
		);
	}

	public function test_default_gateway_is_enabled_is_first() {
		$this->assertSame(
			array(
				'default_gateway' => 'stripe',
				'gateways'        => array(
					'stripe' => '1',
					'paypal' => '1',
				),
			),
			Main::sanitize(
				array(
					'default_gateway' => 'stripe',
					'gateways'        => array(
						'stripe' => '1',
						'paypal' => '1',
					),
				)
			)
		);
	}

	public function test_default_gateway_is_enabled_is_not_first() {
		$this->assertSame(
			array(
				'default_gateway' => 'paypal',
				'gateways'        => array(
					'stripe' => '1',
					'paypal' => '1',
				),
			),
			Main::sanitize(
				array(
					'default_gateway' => 'paypal',
					'gateways'        => array(
						'stripe' => '1',
						'paypal' => '1',
					),
				)
			)
		);
	}
}
