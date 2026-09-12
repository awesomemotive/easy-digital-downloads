<?php
/**
 * Tests for checkout block attributes.
 *
 * @package EDD\Tests\Blocks\Checkout
 */

namespace EDD\Tests\Blocks\Checkout;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Blocks\Checkout\Attributes as CheckoutAttributes;

/**
 * @group blocks
 */
class Attributes extends EDD_UnitTestCase {

	public function test_not_logged_in_returns_false() {
		$this->assertFalse( CheckoutAttributes::is_customer_info_complete( array( 'logged_in' => false ) ) );
	}

	public function test_logged_in_key_absent_returns_false() {
		$this->assertFalse( CheckoutAttributes::is_customer_info_complete( array() ) );
	}

	public function test_logged_in_no_layout_returns_true() {
		$this->assertTrue( CheckoutAttributes::is_customer_info_complete( array( 'logged_in' => true ) ) );
	}

	public function test_logged_in_with_full_layout_returns_true() {
		$this->assertTrue(
			CheckoutAttributes::is_customer_info_complete(
				array(
					'logged_in' => true,
					'layout'    => 'full',
				)
			)
		);
	}

	public function test_half_bottom_layout_without_address_returns_false() {
		$this->assertFalse(
			CheckoutAttributes::is_customer_info_complete(
				array(
					'logged_in' => true,
					'layout'    => 'half-bottom',
				)
			)
		);
	}

	public function test_half_bottom_layout_with_address_returns_true() {
		$this->assertTrue(
			CheckoutAttributes::is_customer_info_complete(
				array(
					'logged_in'   => true,
					'layout'      => 'half-bottom',
					'has_address' => true,
				)
			)
		);
	}

	public function test_two_thirds_bottom_layout_without_address_returns_false() {
		$this->assertFalse(
			CheckoutAttributes::is_customer_info_complete(
				array(
					'logged_in' => true,
					'layout'    => 'two-thirds-bottom',
				)
			)
		);
	}

	public function test_two_thirds_bottom_layout_with_address_returns_true() {
		$this->assertTrue(
			CheckoutAttributes::is_customer_info_complete(
				array(
					'logged_in'   => true,
					'layout'      => 'two-thirds-bottom',
					'has_address' => true,
				)
			)
		);
	}

	/**
	 * The cart block's show_discount_form should be honored when the checkout block
	 * is a direct, top-level block on the page.
	 */
	public function test_top_level_checkout_uses_cart_discount_form_attribute() {
		$page_id = $this->create_checkout_page(
			'<!-- wp:edd/checkout -->' .
			'<!-- wp:edd/checkout-cart {"show_discount_form":false} /-->' .
			'<!-- /wp:edd/checkout -->'
		);

		$attributes = $this->parse_attributes( $page_id );

		$this->assertFalse( $attributes['show_discount_form'] );
	}

	/**
	 * When the checkout block is nested inside another block (e.g. a group), the cart
	 * block's show_discount_form must still be found. This is the AJAX re-render bug:
	 * a flat, top-level scan never sees the nested checkout and falls back to defaults.
	 */
	public function test_nested_checkout_uses_cart_discount_form_attribute() {
		$page_id = $this->create_checkout_page(
			'<!-- wp:group -->' .
			'<div class="wp-block-group">' .
			'<!-- wp:edd/checkout -->' .
			'<!-- wp:edd/checkout-cart {"show_discount_form":false} /-->' .
			'<!-- /wp:edd/checkout -->' .
			'</div>' .
			'<!-- /wp:group -->'
		);

		$attributes = $this->parse_attributes( $page_id );

		$this->assertFalse( $attributes['show_discount_form'] );
	}

	/**
	 * The overlay should only flip the value when the cart block explicitly sets it;
	 * an unset attribute leaves the default (true) intact.
	 */
	public function test_cart_without_discount_form_attribute_keeps_default() {
		$page_id = $this->create_checkout_page(
			'<!-- wp:edd/checkout -->' .
			'<!-- wp:edd/checkout-cart /-->' .
			'<!-- /wp:edd/checkout -->'
		);

		$attributes = $this->parse_attributes( $page_id );

		$this->assertTrue( $attributes['show_discount_form'] );
	}

	/**
	 * A page with no checkout block falls back to the default attributes.
	 */
	public function test_page_without_checkout_returns_defaults() {
		$page_id = $this->create_checkout_page( '<!-- wp:paragraph --><p>No checkout here.</p><!-- /wp:paragraph -->' );

		$attributes = $this->parse_attributes( $page_id );

		$this->assertTrue( $attributes['show_discount_form'] );
	}

	/**
	 * Creates a page with the given block content.
	 *
	 * @param string $content The post content.
	 * @return int The created page ID.
	 */
	private function create_checkout_page( $content ) {
		return self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => $content,
			)
		);
	}

	/**
	 * Invokes the private parse_attributes method directly to bypass the per-request cache.
	 *
	 * @param int $page_id The page ID to parse.
	 * @return array The parsed attributes.
	 */
	private function parse_attributes( $page_id ) {
		$method = new \ReflectionMethod( CheckoutAttributes::class, 'parse_attributes' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		return $method->invoke( null, $page_id );
	}
}
