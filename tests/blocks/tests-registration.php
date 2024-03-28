<?php
namespace EDD\Tests\Blocks;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests for EDD core block functions.
 */

class Registration extends EDD_UnitTestCase {

	private static $registry;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		self::$registry = \WP_Block_Type_Registry::get_instance();
	}

	public function test_edd_has_core_blocks_is_true() {
		$this->assertTrue( edd_has_core_blocks() );
	}

	public function test_block_categories_includes_edd() {
		$block_editor_context = new \WP_Block_Editor_Context( array( 'name' => 'core/edit-post' ) );
		$block_categories     = get_block_categories( $block_editor_context );
		$categories           = wp_list_pluck( $block_categories, 'slug' );

		$this->assertTrue( in_array( 'easy-digital-downloads', $categories, true ) );
	}

	public function test_button_colors_update_button_class() {
		$button_colors = edd_update_option( 'button_colors', array( 'background' => '#333', 'text' => '#fff' ) );

		$this->assertStringContainsString( 'has-edd-button-background-color', edd_get_button_color_class() );

		edd_delete_option( 'button_colors' );
	}

	public function test_checkout_block_is_registered() {
		$block = self::$registry->get_registered( 'edd/checkout' );

		$this->assertEquals( 'edd/checkout', $block->name );
	}

	public function test_cart_block_is_registered() {
		$block = self::$registry->get_registered( 'edd/cart' );

		$this->assertEquals( 'edd/cart', $block->name );
	}

	public function test_downloads_block_is_registered() {
		$block = self::$registry->get_registered( 'edd/downloads' );

		$this->assertEquals( 'edd/downloads', $block->name );
	}

	public function test_buy_button_block_is_registered() {
		$block = self::$registry->get_registered( 'edd/buy-button' );

		$this->assertEquals( 'edd/buy-button', $block->name );
	}

	public function test_login_block_is_registered() {
		$block = self::$registry->get_registered( 'edd/login' );

		$this->assertEquals( 'edd/login', $block->name );
	}

	public function test_register_block_is_registered() {
		$block = self::$registry->get_registered( 'edd/register' );

		$this->assertEquals( 'edd/register', $block->name );
	}

	public function test_order_history_block_is_registered() {
		$block = self::$registry->get_registered( 'edd/order-history' );

		$this->assertEquals( 'edd/order-history', $block->name );
	}

	// test confirmation is registered
	public function test_confirmation_block_is_registered() {
		$block = self::$registry->get_registered( 'edd/confirmation' );

		$this->assertEquals( 'edd/confirmation', $block->name );
	}

	public function test_receipt_block_is_registered() {
		$block = self::$registry->get_registered( 'edd/receipt' );

		$this->assertEquals( 'edd/receipt', $block->name );
	}

	public function test_user_downloads_block_is_registered() {
		$block = self::$registry->get_registered( 'edd/user-downloads' );

		$this->assertEquals( 'edd/user-downloads', $block->name );
	}

	public function test_terms_block_is_registered() {
		$block = self::$registry->get_registered( 'edd/terms' );

		$this->assertEquals( 'edd/terms', $block->name );
	}

	public function test_checkout_has_blocks_is_true() {
		$this->assertTrue( \EDD\Blocks\Checkout\Functions\checkout_has_blocks() );
	}

	public function test_shortcode_checkout_has_blocks_is_false() {
		$post_id = $this->factory->post->create( array(
			'post_title'   => 'Test Page',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => '[download_checkout]',
		) );

		// The main checkout has blocks.
		$this->assertTrue( \EDD\Blocks\Checkout\Functions\checkout_has_blocks() );
		$this->go_to( get_permalink( $post_id ) );
		do_action( 'template_redirect' ); // Necessary to trigger correct actions
		// This secondary checkout does not.
		$this->assertFalse( \EDD\Blocks\Checkout\Functions\checkout_has_blocks() );
	}

	public function test_edd_get_blocks_includes_core_blocks() {
		$blocks = \EDD\Blocks\Styles\get_edd_blocks();

		$this->assertTrue( in_array( 'edd/checkout', $blocks, true ) );
		$this->assertTrue( in_array( 'edd/cart', $blocks, true ) );
		$this->assertTrue( in_array( 'edd/login', $blocks, true ) );
		$this->assertTrue( in_array( 'edd/downloads', $blocks, true ) );
		$this->assertTrue( in_array( 'edd/receipt', $blocks, true ) );
	}
}
