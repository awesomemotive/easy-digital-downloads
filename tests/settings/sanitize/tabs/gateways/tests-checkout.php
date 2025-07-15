<?php

namespace EDD\Tests\Settings\Sanitize\Tabs\Gateways;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\Settings\Sanitize\Tabs\Gateways\Checkout;

class CheckoutSection extends EDD_UnitTestCase {

	public function tearDown(): void {
		// Clear any of the settings errors.
		global $wp_settings_errors;
		$wp_settings_errors = array();

		parent::tearDown();
	}

	public function test_banned_emails_empty_input() {
		$this->assertSame(
			array(
				'banned_emails' => ''
			),
			Checkout::sanitize(
				array(
					'banned_emails' => ''
				)
			)
		);
	}

	public function test_banned_emails_single_email() {
		$this->assertSame(
			array(
				'banned_emails' => array( 'user1@example.local' ),
			),
			Checkout::sanitize(
				array(
					'banned_emails' => 'user1@example.local'
				)
			)
		);
	}

	public function test_banned_emails_multiple_emails() {
		$this->assertSame(
			array(
				'banned_emails' => array( 'user1@example.local', 'user2@example.local' ),
			),
			Checkout::sanitize(
				array(
					'banned_emails' => 'user1@example.local' . "\n" . 'user2@example.local',
				)
			)
		);
	}

	public function test_banned_emails_with_invalid_emails() {
		$this->assertSame(
			array(
				'banned_emails' => array(),
			),
			Checkout::sanitize(
				array(
					'banned_emails' => 'not-an-email',
				)
			)
		);
	}

	public function test_banned_emails_with_valid_and_invalid_emails() {
		$this->assertSame(
			array(
				'banned_emails' => array( 'user1@example.local' ),
			),
			Checkout::sanitize(
				array(
					'banned_emails' => 'not-an-email' . "\n" . 'user1@example.local',
				)
			)
		);
	}

	public function test_banned_emails_with_domain() {
		$this->assertSame(
			array(
				'banned_emails' => array( '@example.local' ),
			),
			Checkout::sanitize(
				array(
					'banned_emails' => '@example.local',
				)
			)
		);
	}

	public function test_checkout_address_fields_empty() {
		$this->assertSame(
			array(
				'checkout_address_fields' => array()
			),
			Checkout::sanitize(
				array(
					'checkout_address_fields' => array()
				)
			)
		);
	}

	public function test_checkout_address_fields_country() {
		$this->assertSame(
			array(
				'checkout_address_fields' => array(
					'country' => 1
				)
			),
			Checkout::sanitize(
				array(
					'checkout_address_fields' => array(
						'country' => 1
					)
				)
			)
		);
	}

	public function test_taxes_enabled_checkout_address_fields_country() {
		edd_update_option( 'enable_taxes', true );
		$this->assertSame(
			array(
				'checkout_address_fields' => array(
					'country' => 1
				)
			),
			Checkout::sanitize(
				array(
					'checkout_address_fields' => array(
						'country' => 1
					)
				)
			)
		);
		edd_delete_option( 'enable_taxes' );
	}

	public function test_taxes_enabled_regional_rate_checkout_address_fields_country_is_full() {
		edd_update_option( 'enable_taxes', true );
		$tax_rate = edd_add_tax_rate(
			array(
				'scope'       => 'region',
				'name'        => 'US',
				'description' => 'TN',
				'amount'      => 9.25,
				'status'      => 'active',
			)
		);
		$this->assertSame(
			array(
				'checkout_address_fields' => array(
					'country' => 1,
					'address' => 1,
					'city'    => 1,
					'state'   => 1,
					'zip'     => 1,
				)
			),
			Checkout::sanitize(
				array(
					'checkout_address_fields' => array(
						'country' => 1,
						'address' => 1,
						'city'    => 1,
						'state'   => 1,
						'zip'     => 1,
					)
				)
			)
		);
		edd_delete_option( 'enable_taxes' );
		edd_delete_adjustment( $tax_rate );
	}

	public function test_empty_cart_behavior_empty() {
		$this->assertSame(
			array(
				'empty_cart_behavior' => 'message',
			),
			Checkout::sanitize(
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
			Checkout::sanitize(
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
			Checkout::sanitize(
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
			Checkout::sanitize(
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
			Checkout::sanitize(
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
			Checkout::sanitize(
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
			Checkout::sanitize(
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
			Checkout::sanitize(
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
			Checkout::sanitize(
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
			Checkout::sanitize(
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
			Checkout::sanitize(
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
			Checkout::sanitize(
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
			Checkout::sanitize(
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
			Checkout::sanitize(
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
			Checkout::sanitize(
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
			Checkout::sanitize(
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
			Checkout::sanitize(
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
			Checkout::sanitize(
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
			Checkout::sanitize(
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
			Checkout::sanitize(
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
