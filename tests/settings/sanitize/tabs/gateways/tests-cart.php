<?php

namespace EDD\Tests\Settings\Sanitize\Tabs\Gateways;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\Settings\Sanitize\Tabs\Gateways\Cart;

class CartSection extends EDD_UnitTestCase {

	public function tearDown(): void {
		// Clear any of the settings errors.
		global $wp_settings_errors;
		$wp_settings_errors = array();

		parent::tearDown();
	}

	public function test_empty_cart_behavior_empty() {
		$this->assertSame(
			array(
				'empty_cart_behavior' => 'message',
			),
			Cart::sanitize(
				array(
					'empty_cart_behavior' => '',
				)
			)
		);
	}

	public function test_empty_cart_behavior_invalid() {
		$this->assertSame(
			array(
				'empty_cart_behavior' => 'message',
			),
			Cart::sanitize(
				array(
					'empty_cart_behavior' => 'invalid',
				)
			)
		);
	}

	public function test_empty_cart_behavior_message() {
		$this->assertSame(
			array(
				'empty_cart_behavior' => 'message',
			),
			Cart::sanitize(
				array(
					'empty_cart_behavior' => 'message',
				)
			)
		);
	}

	public function test_empty_cart_behavior_redirect_page() {
		$this->assertSame(
			array(
				'empty_cart_behavior' => 'redirect_page',
			),
			Cart::sanitize(
				array(
					'empty_cart_behavior' => 'redirect_page',
				)
			)
		);
	}

	public function test_empty_cart_behavior_redirect_url() {
		$this->assertSame(
			array(
				'empty_cart_behavior' => 'redirect_url',
			),
			Cart::sanitize(
				array(
					'empty_cart_behavior' => 'redirect_url',
				)
			)
		);
	}

	public function test_empty_cart_message_empty() {
		$this->assertSame(
			array(
				'empty_cart_message' => '',
			),
			Cart::sanitize(
				array(
					'empty_cart_message' => '',
				)
			)
		);
	}

	public function test_empty_cart_message_invalid() {
		$this->assertSame(
			array(
				'empty_cart_message' => '',
			),
			Cart::sanitize(
				array(
					'empty_cart_message' => array( 'invalid' => 'invalid' ),
				)
			)
		);
	}

	public function test_empty_cart_message_valid() {
		$this->assertSame(
			array(
				'empty_cart_message' => 'Hello, world!',
			),
			Cart::sanitize(
				array(
					'empty_cart_message' => 'Hello, world!',
				)
			)
		);
	}

	public function test_empty_cart_message_kses() {
		$this->assertSame(
			array(
				'empty_cart_message' => 'alert("Hello, world!");',
			),
			Cart::sanitize(
				array(
					'empty_cart_message' => '<script>alert("Hello, world!");</script>',
				)
			)
		);
	}

	public function test_empty_cart_redirect_page_empty() {
		$this->assertSame(
			array(
				'empty_cart_redirect_page' => 0,
			),
			Cart::sanitize(
				array(
					'empty_cart_redirect_page' => '',
				)
			)
		);
	}

	public function test_empty_cart_redirect_page_invalid() {
		$this->assertSame(
			array(
				'empty_cart_redirect_page' => 0,
			),
			Cart::sanitize(
				array(
					'empty_cart_redirect_page' => 'invalid',
				)
			)
		);

		$this->assertSame(
			'Please provide the ID of a published page for the redirect target.',
			$this->get_error( 'edd-empty-cart-redirect-page-not-numeric' )
		);
	}

	public function test_empty_cart_redirect_page_valid() {
		$page_id = $this->factory->post->create( array( 'post_type' => 'page' ) );

		$this->assertSame(
			array(
				'empty_cart_redirect_page' => $page_id,
			),
			Cart::sanitize(
				array(
					'empty_cart_redirect_page' => $page_id,
				)
			)
		);

		wp_delete_post( $page_id );
	}

	public function test_empty_cart_redirect_page_invalid_page_not_published() {
		$page_id = $this->factory->post->create( array( 'post_type' => 'page' ) );

		// Update the post ID to not be published.
		wp_update_post( array( 'ID' => $page_id, 'post_status' => 'draft' ) );

		$this->assertSame(
			array(
				'empty_cart_redirect_page' => 0,
			),
			Cart::sanitize(
				array(
					'empty_cart_redirect_page' => $page_id,
				)
			)
		);

		$this->assertSame(
			'The selected redirect page is not valid. Please select a valid published page.',
			$this->get_error( 'edd-empty-cart-redirect-page' )
		);

		wp_delete_post( $page_id );
	}

	public function test_empty_cart_redirect_page_invalid_post_type() {
		$page_id = $this->factory->post->create( array( 'post_type' => 'custom-post-type' ) );

		$this->assertSame(
			array(
				'empty_cart_redirect_page' => 0,
			),
			Cart::sanitize(
				array(
					'empty_cart_redirect_page' => $page_id,
				)
			)
		);

		$this->assertSame(
			'The selected redirect page is not valid. Please select a valid published page.',
			$this->get_error( 'edd-empty-cart-redirect-page' )
		);

		wp_delete_post( $page_id );
	}

	public function test_empty_cart_redirect_page_invalid_checkout_page() {
		$checkout_page_id = edd_get_option( 'purchase_page', 0 );
		$this->assertSame(
			array(
				'empty_cart_redirect_page' => 0,
			),
			Cart::sanitize(
				array(
					'empty_cart_redirect_page' => $checkout_page_id,
				)
			)
		);
	}

	public function test_empty_cart_redirect_page_invalid_checkout_page_has_checkout_shortcode() {
		$checkout_page_id = $this->factory->post->create( array( 'post_type' => 'page', 'post_content' => '[download_checkout]' ) );
		$this->assertSame(
			array(
				'empty_cart_redirect_page' => 0,
			),
			Cart::sanitize(
				array(
					'empty_cart_redirect_page' => $checkout_page_id,
				)
			)
		);
	}

	public function test_empty_cart_redirect_url_empty() {
		$this->assertSame(
			array(
				'empty_cart_redirect_url' => '',
			),
			Cart::sanitize(
				array(
					'empty_cart_redirect_url' => '',
				)
			)
		);
	}

	public function test_empty_cart_redirect_url_invalid() {
		$this->assertSame(
			array(
				'empty_cart_redirect_url' => 'invalid-url',
			),
			Cart::sanitize(
				array(
					'empty_cart_redirect_url' => 'invalid-url',
				)
			)
		);

		$this->assertSame(
			'The provided redirect URL is not valid. Please enter a valid URL.',
			$this->get_error( 'edd-empty-cart-redirect-url' )
		);
	}

	public function test_empty_cart_redirect_url_valid() {
		$this->assertSame(
			array(
				'empty_cart_redirect_url' => 'https://example.com',
			),
			Cart::sanitize(
				array(
					'empty_cart_redirect_url' => 'https://example.com',
				)
			)
		);
	}

	public function test_empty_cart_redirect_url_invalid_checkout_page() {
		$checkout_url = edd_get_checkout_uri();
		$this->assertSame(
			array(
				'empty_cart_redirect_url' => '',
			),
			Cart::sanitize(
				array(
					'empty_cart_redirect_url' => $checkout_url,
				)
			)
		);
	}

	private function get_error( $error_id ) {
		$errors = get_settings_errors();
		foreach ( $errors as $error ) {
			if ( $error_id === $error['code'] ) {
				return $error['message'];
			}
		}

		return null;
	}
}
