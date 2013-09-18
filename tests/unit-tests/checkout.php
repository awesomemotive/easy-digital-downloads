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
}