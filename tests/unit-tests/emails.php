<?php

/**
 * Test Emails
 */
class Tests_Emails extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test_admin_notice_emails() {
		$expected = array( 'admin@example.org' );
		$this->assertEquals( $expected, edd_get_admin_notice_emails() );
	}

	public function test_email_templates() {
		$expected = array(
			'default' => 'Default Template',
			'none' => 'No template, plain text only'
		);

		$this->assertEquals( $expected, edd_get_email_templates() );
	}
}