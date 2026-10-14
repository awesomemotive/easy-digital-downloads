<?php

namespace EDD\Tests\Stripe;

use \EDD\Tests\PHPUnit\EDD_UnitTestCase;
use \EDD\Gateways\Stripe\StatementDescriptor;

/**
 * Tests for regional support.
 */
class StatementDescriptors extends EDD_UnitTestCase {

	/**
	 * Order transaction lookups by object_id captured from the `query` filter.
	 *
	 * @var string[]
	 */
	private $transaction_lookups = array();

	/**
	 * Sets up each test.
	 */
	public function set_up() {
		parent::set_up();
	}

	/**
	 * Removes the query spy and the email links filter.
	 */
	public function tear_down() {
		remove_filter( 'query', array( $this, 'capture_transaction_lookup' ) );
		remove_filter( 'edd_email_show_links', '__return_true' );
		$this->transaction_lookups = array();

		parent::tear_down();
	}

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

	/**
	 * The transaction lookup includes object_type so it can use the object_type_object_id index.
	 */
	public function test_statement_descriptor_email_tag_scopes_the_transaction_lookup_by_object_type() {
		$order_id = parent::edd()->order->create( array( 'gateway' => 'stripe' ) );

		add_filter( 'edd_email_show_links', '__return_true' );
		add_filter( 'query', array( $this, 'capture_transaction_lookup' ) );

		edd_stripe_statement_descriptor_template_tag( $order_id );

		$this->assertNotEmpty( $this->transaction_lookups );
		foreach ( $this->transaction_lookups as $query ) {
			$this->assertStringContainsString( 'object_type', $query );
		}
	}

	/**
	 * An order paid through another gateway renders nothing and issues no transaction lookup.
	 */
	public function test_statement_descriptor_email_tag_skips_the_lookup_for_a_non_stripe_order() {
		$order_id = parent::edd()->order->create( array( 'gateway' => 'manual' ) );

		add_filter( 'edd_email_show_links', '__return_true' );
		add_filter( 'query', array( $this, 'capture_transaction_lookup' ) );

		$this->assertSame( '', edd_stripe_statement_descriptor_template_tag( $order_id ) );
		$this->assertEmpty( $this->transaction_lookups );
	}

	/**
	 * Collects SELECT queries that look up order transactions by object_id.
	 *
	 * @param string $query SQL query.
	 * @return string
	 */
	public function capture_transaction_lookup( $query ) {
		if (
			0 === stripos( ltrim( $query ), 'SELECT' )
			&& false !== strpos( $query, 'edd_order_transactions' )
			&& false !== strpos( $query, 'object_id' )
		) {
			$this->transaction_lookups[] = $query;
		}

		return $query;
	}
}
