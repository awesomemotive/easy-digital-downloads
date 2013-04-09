<?php
/**
 * Test Shortcodes
 */

class Test_Easy_Digital_Downloads_Shortcodes extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function testShortcodesAreRegistered() {
		global $shortcode_tags;
		$this->assertArrayHasKey('purchase_link', $shortcode_tags);
		$this->assertArrayHasKey('download_history', $shortcode_tags);
		$this->assertArrayHasKey('purchase_history', $shortcode_tags);
		$this->assertArrayHasKey('download_checkout', $shortcode_tags);
		$this->assertArrayHasKey('download_cart', $shortcode_tags);
		$this->assertArrayHasKey('edd_login', $shortcode_tags);
		$this->assertArrayHasKey('download_discounts', $shortcode_tags);
		$this->assertArrayHasKey('purchase_collection', $shortcode_tags);
		$this->assertArrayHasKey('downloads', $shortcode_tags);
		$this->assertArrayHasKey('edd_price', $shortcode_tags);
		$this->assertArrayHasKey('edd_receipt', $shortcode_tags);
		$this->assertArrayHasKey('edd_profile_editor', $shortcode_tags);
	}
}