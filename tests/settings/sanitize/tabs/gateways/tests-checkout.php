<?php

namespace EDD\Tests\Settings\Sanitize\Tabs\Gateways;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\Settings\Sanitize\Tabs\Gateways\Checkout;

class CheckoutSection extends EDD_UnitTestCase {
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
}
