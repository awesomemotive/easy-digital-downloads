<?php

namespace EDD\Tests\Checkout;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Checkout\Address as CheckoutAddress;
use EDD\Tests\Helpers\EDD_Helper_Download as Download;

/**
 * Checkout tests.
 */
class Address extends EDD_UnitTestCase {

	public function tearDown(): void {
		edd_delete_option( 'stripe_billing_fields');
		edd_delete_option( 'enable_taxes' );
		edd_delete_option( 'checkout_address_fields' );
	}

	public function test_address_fields_default_empty() {
		$address = new CheckoutAddress();

		$this->assertEmpty( $address->get_fields() );
	}

	public function test_address_fields_custom() {
		edd_update_option(
			'checkout_address_fields',
			array(
				'address' => 1,
				'city'    => 1,
				'state'   => 1,
				'zip'     => 1,
				'country' => 1,
			)
		);

		$address = new CheckoutAddress();

		$this->assertCount( 5, $address->get_fields() );
	}

	public function test_address_fields_over_stripe_billing_fields() {
		edd_update_option( 'stripe_billing_fields', 'full' );
		edd_update_option( 'checkout_address_fields', array( 'address' => 1 ) );

		$address = new CheckoutAddress();

		$this->assertCount( 1, $address->get_fields() );
	}

	public function test_address_fields_full_include_address() {
		edd_update_option( 'stripe_billing_fields', 'full' );

		$address = new CheckoutAddress();

		$this->assertTrue( in_array( 'address', $address->get_fields() ) );
	}

	public function test_address_fields_country_has_one_field() {
		edd_update_option( 'checkout_address_fields', array( 'country' => 1 ) );

		$address = new CheckoutAddress();

		$this->assertCount( 1, $address->get_fields() );
	}

	public function test_address_fields_zip_country_has_two_fields() {
		edd_update_option( 'stripe_billing_fields', 'zip_country' );

		$address = new CheckoutAddress();

		$this->assertCount( 2, $address->get_fields() );
	}

	public function test_address_fields_manually_set() {
		edd_update_option( 'stripe_billing_fields', 'full' );

		$address = new CheckoutAddress();
		$this->assertCount( 6, $address->get_fields() );

		$address->fields = array( 'country' );
		$this->assertCount( 1, $address->get_fields() );
	}

	public function test_address_fields_empty_taxes_enabled() {
		edd_update_option( 'enable_taxes', 1 );
		$download = Download::create_simple_download();
		edd_add_to_cart( $download->ID );

		$address = new CheckoutAddress();

		$this->assertNotEmpty( $address->get_fields() );
	}
}
