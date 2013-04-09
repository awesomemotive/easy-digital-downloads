<?php
/**
 * Test Formatting
 */

class Test_Easy_Digital_Downloads_Formatting extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function testSanitizeAmount() {
		$this->assertEquals('20,000.20', edd_sanitize_amount('20,000.20'));
	}

	public function testFormatAmount() {
		$this->assertEquals('20,000.20', edd_format_amount('20,000.20'));
	}
}