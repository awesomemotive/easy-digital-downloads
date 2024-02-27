<?php

namespace EDD\Tests\Stripe;

use \EDD\Tests\PHPUnit\EDD_UnitTestCase;
use \EDD\Gateways\Stripe\StatementDescriptor;

/**
 * Tests for regional support.
 */
class StatementDescriptors extends EDD_UnitTestCase {

	public function test_default_statement_descriptor() {
		$this->assertEmpty( StatementDescriptor::sanitize_suffix() );
	}

	public function test_purchase_summary_enabled_length_valid() {
		edd_update_option( 'stripe_statement_descriptor_prefix', 'EDDTEST' );
		edd_update_option( 'stripe_include_purchase_summary_in_statement_descriptor', '1' );

		$purchase_summary = 'Product Name';
		$suffix = StatementDescriptor::sanitize_suffix( $purchase_summary );

		$this->assertEquals( 'PRODUCTNAME', $suffix );
	}

	public function test_purchase_summary_enabled_length_invalid() {
		edd_update_option( 'stripe_statement_descriptor_prefix', 'EDDTEST' );
		edd_update_option( 'stripe_include_purchase_summary_in_statement_descriptor', '1' );

		$purchase_summary = 'Product Name That Is Too Long';
		$suffix = StatementDescriptor::sanitize_suffix( $purchase_summary );

		$this->assertEquals( 'PRODUCTNAMETH', $suffix );
	}

	public function test_suffix_with_no_latin_character() {
		edd_update_option( 'stripe_statement_descriptor_prefix', '12345' );
		edd_update_option( 'stripe_include_purchase_summary_in_statement_descriptor', '1' );

		$purchase_summary = '987';
		$suffix = StatementDescriptor::sanitize_suffix( $purchase_summary );

		$this->assertEquals( 'E-987', $suffix );
	}

	public function test_suffix_prefix_has_latin_character() {
		edd_update_option( 'stripe_statement_descriptor_prefix', 'EDDTEST' );
		edd_update_option( 'stripe_include_purchase_summary_in_statement_descriptor', '1' );

		$purchase_summary = '987';
		$suffix = StatementDescriptor::sanitize_suffix( $purchase_summary );

		$this->assertEquals( '987', $suffix );
	}

	public function test_suffix_with_unsupported_characters() {
		edd_update_option( 'stripe_statement_descriptor_prefix', 'EDDTEST' );
		edd_update_option( 'stripe_include_purchase_summary_in_statement_descriptor', '1' );

		$purchase_summary = 'N<a>\\m*"e Test';
		$suffix = StatementDescriptor::sanitize_suffix( $purchase_summary );

		$this->assertEquals( 'NAMETEST', $suffix );
	}
}
