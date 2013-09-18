<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_checkout
 */
class Tests_Checkout extends EDD_UnitTestCase {
	public function setUp() {

		parent::setUp();

		global $wp_rewrite;
		$GLOBALS['wp_rewrite']->init();
		flush_rewrite_rules();

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
     * Test that each of the template actions are added and each hooked in with the right priority
     */
	public function test_checkout_actions() {
		global $wp_filter;
		$this->assertarrayHasKey( 'edd_show_purchase_form',        $wp_filter['edd_purchase_form'][10]                 );
		$this->assertarrayHasKey( 'edd_user_info_fields',          $wp_filter['edd_purchase_form_after_user_info'][10] );
		$this->assertarrayHasKey( 'edd_get_cc_form',               $wp_filter['edd_cc_form'][10]                       );
		$this->assertarrayHasKey( 'edd_default_cc_address_fields', $wp_filter['edd_after_cc_fields'][10]               );
		$this->assertarrayHasKey( 'edd_checkout_tax_fields',       $wp_filter['edd_purchase_form_after_cc_form'][999]  );
		$this->assertarrayHasKey( 'edd_get_register_fields',       $wp_filter['edd_purchase_form_register_fields'][10] );
		$this->assertarrayHasKey( 'edd_get_login_fields',          $wp_filter['edd_purchase_form_login_fields'][10]    );
		$this->assertarrayHasKey( 'edd_payment_mode_select',       $wp_filter['edd_payment_mode_select'][10]           );
		$this->assertarrayHasKey( 'edd_show_payment_icons',        $wp_filter['edd_payment_mode_top'][10]              );
		$this->assertarrayHasKey( 'edd_show_payment_icons',        $wp_filter['edd_checkout_form_top'][10]             );
		$this->assertarrayHasKey( 'edd_discount_field',            $wp_filter['edd_checkout_form_top'][-1]             );
		$this->assertarrayHasKey( 'edd_terms_agreement',           $wp_filter['edd_purchase_form_before_submit'][10]   );
		$this->assertarrayHasKey( 'edd_checkout_final_total',      $wp_filter['edd_purchase_form_before_submit'][999]  );
		$this->assertarrayHasKey( 'edd_checkout_submit',           $wp_filter['edd_purchase_form_after_cc_form'][9999] );
		$this->assertarrayHasKey( 'edd_agree_to_terms_js',         $wp_filter['edd_checkout_form_top'][10]             );
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
		$this->assertInternalType( 'string', edd_checkout_form() );
		// The checkout form should always have this
		$this->assertContains( '<div id="edd_checkout_wrap">', edd_checkout_form() );
		// The checkout form will always have this if there are items in the cart
		$this->assertContains( '<div id="edd_checkout_form_wrap" class="edd_clearfix">', edd_checkout_form() );
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
		$this->assertContains( '<input type="submit" class="edd-submit gray button" id="edd-purchase-button" name="edd-purchase" value="Purchase"/>', edd_checkout_button_purchase() );
	}
}