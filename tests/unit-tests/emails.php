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
}