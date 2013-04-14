<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_shortcode
 */
class Tests_Shortcode extends \WP_UnitTestCase {
	protected $_shortcodes = array( 'purchase_link', 'download_history', 'purchase_history', 'download_checkout', 'download_cart', 'edd_login', 'download_discounts', 'purchase_collection', 'downloads', 'edd_price', 'edd_receipt', 'edd_profile_editor' );

	public function setUp() {
		parent::setUp();

		foreach ( $this->_shortcodes as $shortcode )
			add_shortcode( $shortcode, array( $this, '_shortcode_' . str_replace( '-', '_', $shortcode ) ) );
	}

	public function tearDown() {
		global $shortcode_tags;

		parent::tearDown();

		foreach ( $this->shortcodes as $shortcode )
			unset( $shortcode_tags[ $shortcode ] );
	}

	public function test_shortcodes_are_registered() {
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