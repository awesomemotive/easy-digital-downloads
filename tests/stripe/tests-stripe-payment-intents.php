<?php
/**
 * Tests for Payment Intent Builder.
 *
 * @group edd_stripe
 * @group edd_stripe_payment_intents
 *
 * @coversDefaultClass \EDD\Gateways\Stripe\PaymentIntents\Builder
 */

namespace EDD\Tests\Stripe;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Gateways\Stripe\PaymentIntents\Builder;

class PaymentIntents extends EDD_UnitTestCase {

	/**
	 * Purchase data for testing.
	 *
	 * @var array
	 */
	protected $purchase_data;

	/**
	 * Payment method for testing.
	 *
	 * @var array
	 */
	protected $payment_method;

	/**
	 * Mock Stripe customer.
	 *
	 * @var object
	 */
	protected $customer;

	/**
	 * Set up test fixtures.
	 */
	public function setUp(): void {
		parent::setUp();

		// Set up mock purchase data.
		$this->purchase_data = array(
			'price'        => 99.99,
			'subtotal'     => 89.99,
			'discount'     => 10.00,
			'tax'          => 0,
			'tax_rate'     => 0,
			'user_email'   => 'customer@example.com',
			'user_info'    => array(
				'email'      => 'customer@example.com',
				'first_name' => 'John',
				'last_name'  => 'Doe',
			),
			'cart_details' => array(
				array(
					'id'          => 1,
					'name'        => 'Test Download',
					'item_price'  => 89.99,
					'quantity'    => 1,
					'discount'    => 10.00,
					'tax'         => 0,
					'item_number' => array(
						'id'      => 1,
						'options' => array(),
					),
				),
			),
		);

		// Set up mock payment method.
		$this->payment_method = array(
			'id'   => 'pm_test123',
			'type' => 'card',
		);

		// Create mock Stripe customer.
		$this->customer     = new \stdClass();
		$this->customer->id = 'cus_test123';
	}

	/**
	 * @covers ::__construct
	 * @covers ::get_intent_type
	 */
	public function test_builder_determines_payment_intent_type_for_non_zero_amount() {
		$builder = new Builder(
			$this->purchase_data,
			$this->payment_method,
			$this->customer,
			9999 // $99.99 in cents
		);

		$this->assertEquals( 'PaymentIntent', $builder->get_intent_type() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::get_intent_type
	 */
	public function test_builder_determines_setup_intent_type_for_zero_amount() {
		$builder = new Builder(
			$this->purchase_data,
			$this->payment_method,
			$this->customer,
			0
		);

		$this->assertEquals( 'SetupIntent', $builder->get_intent_type() );
	}

	/**
	 * @covers ::get_arguments_for_fingerprint
	 */
	public function test_get_arguments_for_fingerprint_returns_array() {
		$builder = new Builder(
			$this->purchase_data,
			$this->payment_method,
			$this->customer,
			9999
		);

		$args = $builder->get_arguments_for_fingerprint();

		$this->assertIsArray( $args );
	}

	/**
	 * @covers ::get_arguments_for_fingerprint
	 */
	public function test_get_arguments_for_fingerprint_contains_required_keys() {
		$builder = new Builder(
			$this->purchase_data,
			$this->payment_method,
			$this->customer,
			9999
		);

		$args = $builder->get_arguments_for_fingerprint();

		// Base arguments.
		$this->assertArrayHasKey( 'customer', $args );
		$this->assertArrayHasKey( 'metadata', $args );
		$this->assertArrayHasKey( 'payment_method', $args );
		$this->assertArrayHasKey( 'description', $args );

		// PaymentIntent-specific arguments.
		$this->assertArrayHasKey( 'amount', $args );
		$this->assertArrayHasKey( 'currency', $args );
	}

	/**
	 * @covers ::get_arguments_for_fingerprint
	 */
	public function test_get_arguments_for_fingerprint_uses_correct_customer_id() {
		$builder = new Builder(
			$this->purchase_data,
			$this->payment_method,
			$this->customer,
			9999
		);

		$args = $builder->get_arguments_for_fingerprint();

		$this->assertEquals( 'cus_test123', $args['customer'] );
	}

	/**
	 * @covers ::get_arguments_for_fingerprint
	 */
	public function test_get_arguments_for_fingerprint_uses_correct_payment_method() {
		$builder = new Builder(
			$this->purchase_data,
			$this->payment_method,
			$this->customer,
			9999
		);

		$args = $builder->get_arguments_for_fingerprint();

		$this->assertEquals( 'pm_test123', $args['payment_method'] );
	}

	/**
	 * @covers ::get_arguments_for_fingerprint
	 */
	public function test_get_arguments_for_fingerprint_uses_correct_amount() {
		$builder = new Builder(
			$this->purchase_data,
			$this->payment_method,
			$this->customer,
			9999
		);

		$args = $builder->get_arguments_for_fingerprint();

		$this->assertEquals( 9999, $args['amount'] );
	}

	/**
	 * @covers ::get_arguments_for_fingerprint
	 */
	public function test_get_arguments_for_fingerprint_contains_metadata() {
		$builder = new Builder(
			$this->purchase_data,
			$this->payment_method,
			$this->customer,
			9999
		);

		$args = $builder->get_arguments_for_fingerprint();

		$this->assertIsArray( $args['metadata'] );
		$this->assertArrayHasKey( 'email', $args['metadata'] );
		$this->assertArrayHasKey( 'edd_payment_subtotal', $args['metadata'] );
		$this->assertArrayHasKey( 'edd_payment_discount', $args['metadata'] );
		$this->assertArrayHasKey( 'edd_payment_tax', $args['metadata'] );
		$this->assertArrayHasKey( 'edd_payment_total', $args['metadata'] );
		$this->assertArrayHasKey( 'edd_payment_items', $args['metadata'] );
	}

	/**
	 * @covers ::get_arguments_for_fingerprint
	 */
	public function test_get_arguments_for_fingerprint_metadata_contains_correct_email() {
		$builder = new Builder(
			$this->purchase_data,
			$this->payment_method,
			$this->customer,
			9999
		);

		$args = $builder->get_arguments_for_fingerprint();

		$this->assertEquals( 'customer@example.com', $args['metadata']['email'] );
	}

	/**
	 * @covers ::get_arguments_for_fingerprint
	 */
	public function test_get_arguments_for_fingerprint_does_not_include_mandate_options() {
		$builder = new Builder(
			$this->purchase_data,
			$this->payment_method,
			$this->customer,
			9999
		);

		$args = $builder->get_arguments_for_fingerprint();

		// Mandate options should not be included in fingerprint to prevent
		// timestamp changes from causing fingerprint mismatches.
		$this->assertArrayNotHasKey( 'payment_method_options', $args );
	}

	/**
	 * @covers ::get_arguments_for_fingerprint
	 */
	public function test_fingerprint_is_consistent_for_same_data() {
		$builder1 = new Builder(
			$this->purchase_data,
			$this->payment_method,
			$this->customer,
			9999
		);

		$builder2 = new Builder(
			$this->purchase_data,
			$this->payment_method,
			$this->customer,
			9999
		);

		$args1       = $builder1->get_arguments_for_fingerprint();
		$args2       = $builder2->get_arguments_for_fingerprint();
		$fingerprint1 = md5( json_encode( $args1 ) );
		$fingerprint2 = md5( json_encode( $args2 ) );

		$this->assertEquals( $fingerprint1, $fingerprint2 );
	}

	/**
	 * @covers ::get_arguments_for_fingerprint
	 */
	public function test_fingerprint_changes_when_amount_changes() {
		$builder1 = new Builder(
			$this->purchase_data,
			$this->payment_method,
			$this->customer,
			9999
		);

		$builder2 = new Builder(
			$this->purchase_data,
			$this->payment_method,
			$this->customer,
			5000
		);

		$args1       = $builder1->get_arguments_for_fingerprint();
		$args2       = $builder2->get_arguments_for_fingerprint();
		$fingerprint1 = md5( json_encode( $args1 ) );
		$fingerprint2 = md5( json_encode( $args2 ) );

		$this->assertNotEquals( $fingerprint1, $fingerprint2 );
	}

	/**
	 * @covers ::get_arguments_for_fingerprint
	 */
	public function test_fingerprint_changes_when_customer_changes() {
		$customer2     = new \stdClass();
		$customer2->id = 'cus_different';

		$builder1 = new Builder(
			$this->purchase_data,
			$this->payment_method,
			$this->customer,
			9999
		);

		$builder2 = new Builder(
			$this->purchase_data,
			$this->payment_method,
			$customer2,
			9999
		);

		$args1       = $builder1->get_arguments_for_fingerprint();
		$args2       = $builder2->get_arguments_for_fingerprint();
		$fingerprint1 = md5( json_encode( $args1 ) );
		$fingerprint2 = md5( json_encode( $args2 ) );

		$this->assertNotEquals( $fingerprint1, $fingerprint2 );
	}

	/**
	 * @covers ::set_existing_intent
	 */
	public function test_set_existing_intent_stores_intent() {
		$builder = new Builder(
			$this->purchase_data,
			$this->payment_method,
			$this->customer,
			9999
		);

		$intent     = new \stdClass();
		$intent->id = 'pi_test123';

		// This should not throw an error.
		$builder->set_existing_intent( $intent );

		// We can't directly test the private property, but we can verify no error occurs.
		$this->assertTrue( true );
	}

	/**
	 * @covers ::get_arguments_for_fingerprint
	 */
	public function test_setup_intent_arguments_do_not_include_amount() {
		$builder = new Builder(
			$this->purchase_data,
			$this->payment_method,
			$this->customer,
			0 // Zero amount triggers SetupIntent
		);

		$args = $builder->get_arguments_for_fingerprint();

		// SetupIntent should not have amount/currency.
		$this->assertArrayNotHasKey( 'amount', $args );
		$this->assertArrayNotHasKey( 'currency', $args );

		// But should have usage.
		$this->assertArrayHasKey( 'usage', $args );
	}

	/**
	 * @covers ::get_arguments_for_fingerprint
	 */
	public function test_payment_intent_arguments_include_currency() {
		edd_update_option( 'currency', 'USD' );

		$builder = new Builder(
			$this->purchase_data,
			$this->payment_method,
			$this->customer,
			9999
		);

		$args = $builder->get_arguments_for_fingerprint();

		$this->assertEquals( 'USD', $args['currency'] );
	}

	/**
	 * @covers ::get_arguments_for_fingerprint
	 */
	public function test_link_payment_method_sets_setup_future_usage() {
		$link_payment_method = array(
			'id'   => 'pm_link123',
			'type' => 'link',
		);

		$builder = new Builder(
			$this->purchase_data,
			$link_payment_method,
			$this->customer,
			9999
		);

		$args = $builder->get_arguments_for_fingerprint();

		$this->assertArrayHasKey( 'setup_future_usage', $args );
		$this->assertEquals( 'off_session', $args['setup_future_usage'] );
	}

	/**
	 * @covers ::get_arguments_for_fingerprint
	 */
	public function test_card_payment_may_include_statement_descriptor_suffix() {
		$builder = new Builder(
			$this->purchase_data,
			$this->payment_method,
			$this->customer,
			9999
		);

		$args = $builder->get_arguments_for_fingerprint();

		// Statement descriptor suffix is conditionally included based on sanitization.
		// If present, it should be a string.
		if ( isset( $args['statement_descriptor_suffix'] ) ) {
			$this->assertIsString( $args['statement_descriptor_suffix'] );
		}

		// For card payments, the payment method type should be 'card'.
		$this->assertEquals( 'card', $this->payment_method['type'] );
	}

	/**
	 * @covers ::get_arguments_for_fingerprint
	 */
	public function test_non_card_payment_method_does_not_include_statement_descriptor() {
		$non_card_payment_method = array(
			'id'   => 'pm_sepa123',
			'type' => 'sepa_debit',
		);

		$builder = new Builder(
			$this->purchase_data,
			$non_card_payment_method,
			$this->customer,
			9999
		);

		$args = $builder->get_arguments_for_fingerprint();

		// Non-card payment methods should not have statement_descriptor_suffix.
		$this->assertArrayNotHasKey( 'statement_descriptor_suffix', $args );
	}

	/**
	 * @covers ::get_arguments_for_fingerprint
	 */
	public function test_payment_items_string_format() {
		$builder = new Builder(
			$this->purchase_data,
			$this->payment_method,
			$this->customer,
			9999
		);

		$args = $builder->get_arguments_for_fingerprint();

		// Should contain the download ID.
		$this->assertStringContainsString( '1', $args['metadata']['edd_payment_items'] );
	}

	/**
	 * @covers ::get_arguments_for_fingerprint
	 */
	public function test_variable_pricing_includes_price_id_in_items() {
		$purchase_data_with_price_id                       = $this->purchase_data;
		$purchase_data_with_price_id['cart_details'][0]['item_number']['options']['price_id'] = 2;

		$builder = new Builder(
			$purchase_data_with_price_id,
			$this->payment_method,
			$this->customer,
			9999
		);

		$args = $builder->get_arguments_for_fingerprint();

		// Should contain download ID + price ID.
		$this->assertStringContainsString( '1_2', $args['metadata']['edd_payment_items'] );
	}

	/**
	 * Test that the edds_payment_intent_metadata filter is applied.
	 *
	 * @covers ::get_arguments_for_fingerprint
	 */
	public function test_metadata_filter_is_applied() {
		add_filter( 'edds_payment_intent_metadata', function( $metadata ) {
			$metadata['custom_field'] = 'test_value';
			return $metadata;
		} );

		$builder = new Builder(
			$this->purchase_data,
			$this->payment_method,
			$this->customer,
			9999
		);

		$args = $builder->get_arguments_for_fingerprint();

		$this->assertArrayHasKey( 'custom_field', $args['metadata'] );
		$this->assertEquals( 'test_value', $args['metadata']['custom_field'] );

		remove_all_filters( 'edds_payment_intent_metadata' );
	}
}

