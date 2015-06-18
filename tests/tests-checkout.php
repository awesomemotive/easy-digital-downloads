<?php


/**
 * @group edd_checkout
 */
class Tests_Checkout extends WP_UnitTestCase {
	public function setUp() {

		parent::setUp();

		global $wp_rewrite;
		$GLOBALS['wp_rewrite']->init();
		flush_rewrite_rules( false );

		edd_add_rewrite_endpoints( $wp_rewrite );

		$this->_rewrite = $wp_rewrite;

		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );

		$meta = array(
			'edd_price' => '10.50',
			'_edd_price_options_mode' => 'on',
			'_edd_product_type' => 'default',
		);
		foreach( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		$this->_post = get_post( $post_id );

		// Add our test product to the cart
		$options = array(
			'name' => 'Simple',
			'amount' => '10.50',
			'quantity' => 1
		);
		edd_add_to_cart( $this->_post->ID, $options );
	}

	/**
     * Test the can checkout function
     */
	public function test_can_checkout() {
		$this->assertTrue( edd_can_checkout() );
	}

	/**
     * Test to make sure the checkout form returns the expected HTML
     */
	public function test_checkout_form() {
		$this->markTestIncomplete('This test produces Travis killing output in https://travis-ci.org/easydigitaldownloads/Easy-Digital-Downloads/builds/11630800 on PHP 5.3 only');
		// $this->assertInternalType( 'string', edd_checkout_form() );
		// The checkout form should always have this
		// $this->assertContains( '<div id="edd_checkout_wrap">', edd_checkout_form() );
		// The checkout form will always have this if there are items in the cart
		// $this->assertContains( '<div id="edd_checkout_form_wrap" class="edd_clearfix">', edd_checkout_form() );
	}

	/**
     * Test to make sure the Next button is returned properly
     */
	public function test_checkout_button_next() {
		$this->assertInternalType( 'string', edd_checkout_button_next() );
		$this->assertContains( '<input type="hidden" name="edd_action" value="gateway_select" />', edd_checkout_button_next() );
	}

	/**
     * Test to make sure the purchase button is returned properly
     */
	public function test_checkout_button_purchase() {
		$this->assertInternalType( 'string', edd_checkout_button_purchase() );
		$this->assertContains( '<input type="submit" class="edd-submit blue button" id="edd-purchase-button" name="edd-purchase" value="Purchase"/>', edd_checkout_button_purchase() );
	}

	/**
	 * Test for retrieving banned emails
	 */
	public function test_edd_get_banned_emails() {
		$this->assertInternalType( 'array', edd_get_banned_emails() );
		$this->assertEmpty( edd_get_banned_emails() );
	}

	/**
	 * Test that a specific email is banned
	 */
	public function test_edd_is_email_banned() {

		$emails = array();
		$emails[] = 'john@test.com';
		$emails[] = 'test2.com';

		edd_update_option( 'banned_emails', $emails );

		$this->assertTrue( edd_is_email_banned( 'john@test.com' ) );
		$this->assertTrue( edd_is_email_banned( 'john@test2.com' ) );
		$this->assertFalse( edd_is_email_banned( 'john2@test.com' ) );
	}

	/**
	 * Test SSL enforced checkout
	 */
	public function test_edd_is_ssl_enforced() {

		$this->assertFalse( edd_is_ssl_enforced() );

		edd_update_option( 'enforce_ssl', true );

		$this->assertTrue( edd_is_ssl_enforced() );
	}

	/**
	 * Test SSL asset filter
	 */
	public function test_edd_enforced_ssl_asset_filter() {

		// Test page URLs. These should not get modified

		$content = 'http://local.dev/';
		$this->assertSame( 'http://local.dev/', edd_enforced_ssl_asset_filter( $content ) );

		$content = array( 'http://local.dev/' );
		$expected = array( 'http://local.dev/' );

		$this->assertSame( $expected, edd_enforced_ssl_asset_filter( $content ) );

		// Test asset URLs.

		$content = 'http://local.dev/assets/file.jpg';
		$this->assertSame( 'https://local.dev/assets/file.jpg', edd_enforced_ssl_asset_filter( $content ) );

		$content = array( 'http://local.dev/assets/js/js_file.js' );
		$expected = array( 'https://local.dev/assets/js/js_file.js' );

		$this->assertSame( $expected, edd_enforced_ssl_asset_filter( $content ) );

	}

	public function test_credit_card_format_methods() {

		// Test Cards, Thanks Stripe!
		$test_cards = array(
			'visa1'              => '4242424242424242',
			'visa2'              => '4012888888881881',
			'visa_debit'         => '4000056655665556',
			'mastercard'         => '5555555555554444',
			'mastercard_debit'   => '5200828282828210',
			'mastercard_prepaid' => '5105105105105100',
			'american_express1'  => '378282246310005',
			'american_express2'  => '371449635398431',
			'discover1'          => '6011111111111117',
			'discover2'          => '6011000990139424',
			'diners_club1'       => '30569309025904',
			'diners_club2'       => '38520000023237',
			'jcb1'               => '3530111333300000',
			'jcb2'               => '3566002020360505',
		);

		foreach ( $test_cards as $type => $card ) {
			$this->assertTrue( edd_validate_card_number_format( $card ), $type . ' failed' );
		}
	}
}
