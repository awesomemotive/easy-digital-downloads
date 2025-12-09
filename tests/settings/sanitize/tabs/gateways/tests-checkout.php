<?php

namespace EDD\Tests\Settings\Sanitize\Tabs\Gateways;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\Settings\Sanitize\Tabs\Gateways\Checkout;

class CheckoutSection extends EDD_UnitTestCase {

	public function tearDown(): void {
		// Clear any of the settings errors.
		global $wp_settings_errors;
		$wp_settings_errors = array();

		parent::tearDown();
	}

	public function test_banned_emails_empty_input() {
		$this->assertSame(
			array(
				'banned_emails' => ''
			),
			Checkout::sanitize(
				array(
					'banned_emails' => ''
				)
			)
		);
	}

	public function test_banned_emails_single_email() {
		$this->assertSame(
			array(
				'banned_emails' => array( 'user1@example.local' ),
			),
			Checkout::sanitize(
				array(
					'banned_emails' => 'user1@example.local'
				)
			)
		);
	}

	public function test_banned_emails_multiple_emails() {
		$this->assertSame(
			array(
				'banned_emails' => array( 'user1@example.local', 'user2@example.local' ),
			),
			Checkout::sanitize(
				array(
					'banned_emails' => 'user1@example.local' . "\n" . 'user2@example.local',
				)
			)
		);
	}

	public function test_banned_emails_with_invalid_emails() {
		$this->assertSame(
			array(
				'banned_emails' => array(),
			),
			Checkout::sanitize(
				array(
					'banned_emails' => 'not-an-email',
				)
			)
		);
	}

	public function test_banned_emails_with_valid_and_invalid_emails() {
		$this->assertSame(
			array(
				'banned_emails' => array( 'user1@example.local' ),
			),
			Checkout::sanitize(
				array(
					'banned_emails' => 'not-an-email' . "\n" . 'user1@example.local',
				)
			)
		);
	}

	public function test_banned_emails_with_domain() {
		$this->assertSame(
			array(
				'banned_emails' => array( '@example.local' ),
			),
			Checkout::sanitize(
				array(
					'banned_emails' => '@example.local',
				)
			)
		);
	}

	public function test_checkout_address_fields_empty() {
		$this->assertSame(
			array(
				'checkout_address_fields' => array()
			),
			Checkout::sanitize(
				array(
					'checkout_address_fields' => array()
				)
			)
		);
	}

	public function test_checkout_address_fields_country() {
		$this->assertSame(
			array(
				'checkout_address_fields' => array(
					'country' => 1
				)
			),
			Checkout::sanitize(
				array(
					'checkout_address_fields' => array(
						'country' => 1
					)
				)
			)
		);
	}

	public function test_taxes_enabled_checkout_address_fields_country() {
		edd_update_option( 'enable_taxes', true );
		$this->assertSame(
			array(
				'checkout_address_fields' => array(
					'country' => 1
				)
			),
			Checkout::sanitize(
				array(
					'checkout_address_fields' => array(
						'country' => 1
					)
				)
			)
		);
		edd_delete_option( 'enable_taxes' );
	}

	public function test_taxes_enabled_regional_rate_checkout_address_fields_country_is_full() {
		edd_update_option( 'enable_taxes', true );
		$tax_rate = edd_add_tax_rate(
			array(
				'scope'       => 'region',
				'name'        => 'US',
				'description' => 'TN',
				'amount'      => 9.25,
				'status'      => 'active',
			)
		);
		$this->assertSame(
			array(
				'checkout_address_fields' => array(
					'country' => 1,
					'address' => 1,
					'city'    => 1,
					'state'   => 1,
					'zip'     => 1,
				)
			),
			Checkout::sanitize(
				array(
					'checkout_address_fields' => array(
						'country' => 1,
						'address' => 1,
						'city'    => 1,
						'state'   => 1,
						'zip'     => 1,
					)
				)
			)
		);
		edd_delete_option( 'enable_taxes' );
		edd_delete_adjustment( $tax_rate );
	}
}
