<?php


/**
 * @group edd_meta
 */
class Tests_Register_Meta extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_download_meta() {
		global $wp_filter;

		// Uses standalone function callbacks
		$this->assertarrayHasKey( 'edd_sanitize_amount', $wp_filter['sanitize_post_meta__edd_download_earnings'][10] );
		$this->assertarrayHasKey( 'edd_sanitize_amount', $wp_filter['sanitize_post_meta_edd_price'][10] );
		$this->assertarrayHasKey( 'sanitize_text_field', $wp_filter['sanitize_post_meta__edd_button_behavior'][10] );

		// Callbacks are part of the object, so just make sure it got registered
		$this->assertNotEmpty( $wp_filter['sanitize_post_meta__edd_default_price_id'][10] );
		$this->assertNotEmpty( $wp_filter['sanitize_post_meta__edd_download_sales'][10] );
		$this->assertNotEmpty( $wp_filter['sanitize_post_meta_edd_variable_prices'][10] );
		$this->assertNotEmpty( $wp_filter['sanitize_post_meta_edd_download_files'][10] );
		$this->assertNotEmpty( $wp_filter['sanitize_post_meta__edd_bundled_products'][10] );

	}

	public function test_purchase_meta() {
		global $wp_filter;

		// Uses standalone function callbacks
		$this->assertarrayHasKey( 'sanitize_email',      $wp_filter['sanitize_post_meta__edd_payment_user_email'][10] );
		$this->assertarrayHasKey( 'sanitize_text_field', $wp_filter['sanitize_post_meta__edd_payment_user_ip'][10] );
		$this->assertarrayHasKey( 'sanitize_text_field', $wp_filter['sanitize_post_meta__edd_payment_purchase_key'][10] );
		$this->assertarrayHasKey( 'edd_sanitize_amount', $wp_filter['sanitize_post_meta__edd_payment_total'][10] );
		$this->assertarrayHasKey( 'sanitize_text_field', $wp_filter['sanitize_post_meta__edd_payment_mode'][10] );
		$this->assertarrayHasKey( 'sanitize_text_field', $wp_filter['sanitize_post_meta__edd_payment_gateway'][10] );
		$this->assertarrayHasKey( 'edd_sanitize_amount', $wp_filter['sanitize_post_meta__edd_payment_tax'][10] );
		$this->assertarrayHasKey( 'sanitize_text_field', $wp_filter['sanitize_post_meta__edd_completed_date'][10] );

		// Callbacks are part of the object, so just make sure it got registered
		$this->assertNotEmpty( $wp_filter['sanitize_post_meta__edd_payment_customer_id'][10] );
		$this->assertNotEmpty( $wp_filter['sanitize_post_meta__edd_payment_user_id'][10] );
		$this->assertNotEmpty( $wp_filter['sanitize_post_meta__edd_payment_meta'][10] );

	}


}
