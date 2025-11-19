<?php
/**
 * Tests for Stripe Line Items.
 *
 * @group edd_stripe
 * @group edd_stripe_line_items
 */

namespace EDD\Tests\Stripe;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Gateways\Stripe\PaymentIntents\LineItems\Builder as LineItemsBuilder;
use EDD\Gateways\Stripe\PaymentIntents\LineItems\Item;
use EDD\Gateways\Stripe\PaymentIntents\LineItems\Formatter;
use EDD\Gateways\Stripe\PaymentIntents\AmountDetails;

class LineItems extends EDD_UnitTestCase {

	/**
	 * Purchase data for testing.
	 *
	 * @var array
	 */
	protected $purchase_data;

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
			'tax'          => 5.00,
			'tax_rate'     => 0.05,
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
					'subtotal'    => 89.99, // Pre-tax amount (same as item_price when no tax included in price)
					'quantity'    => 1,
					'discount'    => 10.00,
					'tax'         => 5.00,
					'fees'        => array(), // Item-specific fees (if any)
					'item_number' => array(
						'id'      => 1,
						'options' => array(),
					),
				),
			),
		);
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\LineItems\Formatter::format_amount
	 */
	public function test_formatter_converts_dollars_to_cents() {
		$cents = Formatter::format_amount( 99.99 );
		$this->assertEquals( 9999, $cents );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\LineItems\Formatter::format_amount
	 */
	public function test_formatter_handles_zero_amount() {
		$cents = Formatter::format_amount( 0 );
		$this->assertEquals( 0, $cents );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\LineItems\Formatter::cents_to_dollars
	 */
	public function test_formatter_converts_cents_to_dollars() {
		$dollars = Formatter::cents_to_dollars( 9999 );
		$this->assertEquals( 99.99, $dollars );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\LineItems\Formatter::sanitize_product_code
	 */
	public function test_formatter_sanitizes_product_code() {
		$code = Formatter::sanitize_product_code( '<script>alert("xss")</script>TEST-SKU-123' );
		$this->assertStringNotContainsString( '<script>', $code );
		$this->assertStringContainsString( 'TEST-SKU-123', $code );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\LineItems\Formatter::sanitize_product_name
	 */
	public function test_formatter_sanitizes_product_name() {
		$name = Formatter::sanitize_product_name( 'Test &amp; Product &#8220;Name&#8221;' );
		$this->assertEquals( 'Test & Product "Name"', $name );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\LineItems\Item::to_array
	 */
	public function test_item_creates_basic_line_item() {
		$cart_item = $this->purchase_data['cart_details'][0];
		$item      = new Item( $cart_item, $this->purchase_data );
		$line_item = $item->to_array();

		$this->assertIsArray( $line_item );
		$this->assertArrayHasKey( 'product_code', $line_item );
		$this->assertArrayHasKey( 'product_name', $line_item );
		$this->assertArrayHasKey( 'unit_cost', $line_item );
		$this->assertArrayHasKey( 'quantity', $line_item );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\LineItems\Item::to_array
	 */
	public function test_item_uses_download_id_as_product_code_when_no_sku() {
		$cart_item = $this->purchase_data['cart_details'][0];
		$item      = new Item( $cart_item, $this->purchase_data );
		$line_item = $item->to_array();

		$this->assertEquals( '1', $line_item['product_code'] );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\LineItems\Item::to_array
	 */
	public function test_item_includes_price_id_in_product_code_for_variable_pricing() {
		$cart_item                                = $this->purchase_data['cart_details'][0];
		$cart_item['item_number']['options']['price_id'] = 2;

		$item      = new Item( $cart_item, $this->purchase_data );
		$line_item = $item->to_array();

		$this->assertEquals( '1_2', $line_item['product_code'] );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\LineItems\Item::to_array
	 */
	public function test_item_includes_discount_when_present() {
		$cart_item = $this->purchase_data['cart_details'][0];
		$item      = new Item( $cart_item, $this->purchase_data );
		$line_item = $item->to_array();

		$this->assertArrayHasKey( 'discount_amount', $line_item );
		$this->assertEquals( 1000, $line_item['discount_amount'] ); // $10 in cents
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\LineItems\Item::to_array
	 */
	public function test_item_includes_tax_when_present() {
		$cart_item = $this->purchase_data['cart_details'][0];
		$item      = new Item( $cart_item, $this->purchase_data );
		$line_item = $item->to_array();

		// EDD provides separate subtotal and tax for both tax-inclusive and tax-exclusive pricing.
		$this->assertArrayHasKey( 'tax', $line_item );
		$this->assertArrayHasKey( 'total_tax_amount', $line_item['tax'] );
		$this->assertEquals( 500, $line_item['tax']['total_tax_amount'] ); // $5 in cents
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\LineItems\Item::to_array
	 */
	public function test_item_uses_subtotal_for_unit_cost() {
		// Set up cart item with subtotal to test tax-inclusive scenario.
		$cart_item              = $this->purchase_data['cart_details'][0];
		$cart_item['subtotal']  = 9.09;  // Pre-tax amount
		$cart_item['tax']       = 1.91;  // Tax amount
		$cart_item['item_price'] = 11.00; // Total price

		$item      = new Item( $cart_item, $this->purchase_data );
		$line_item = $item->to_array();

		// unit_cost should use subtotal (pre-tax amount).
		$this->assertEquals( 909, $line_item['unit_cost'] ); // $9.09 in cents

		// Tax is sent separately.
		$this->assertArrayHasKey( 'tax', $line_item );
		$this->assertEquals( 191, $line_item['tax']['total_tax_amount'] ); // $1.91 in cents

		// Stripe will calculate: 909 + 191 = 1100 ($11.00)
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\LineItems\Item::to_array
	 */
	public function test_item_excludes_discount_when_zero() {
		$cart_item            = $this->purchase_data['cart_details'][0];
		$cart_item['discount'] = 0;

		$item      = new Item( $cart_item, $this->purchase_data );
		$line_item = $item->to_array();

		$this->assertArrayNotHasKey( 'discount_amount', $line_item );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\LineItems\Item::to_array
	 */
	public function test_item_excludes_tax_when_zero() {
		$cart_item         = $this->purchase_data['cart_details'][0];
		$cart_item['tax']  = 0;
		$cart_item['fees'] = array(); // Ensure no fees with tax

		$item      = new Item( $cart_item, $this->purchase_data );
		$line_item = $item->to_array();

		$this->assertArrayNotHasKey( 'tax', $line_item );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\LineItems\Builder::is_enabled
	 */
	public function test_line_items_disabled_by_default_for_card_payments() {
		$builder = new LineItemsBuilder( $this->purchase_data, 'card' );
		$this->assertFalse( $builder->is_enabled() );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\LineItems\Builder::is_enabled
	 */
	public function test_line_items_enabled_when_setting_is_on() {
		edd_update_option( 'stripe_line_items_enabled', true );

		$builder = new LineItemsBuilder( $this->purchase_data, 'card' );
		$this->assertTrue( $builder->is_enabled() );

		edd_delete_option( 'stripe_line_items_enabled' );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\LineItems\Builder::is_enabled
	 */
	public function test_line_items_always_enabled_for_klarna() {
		edd_delete_option( 'stripe_line_items_enabled' ); // Ensure setting is off

		$builder = new LineItemsBuilder( $this->purchase_data, 'klarna' );
		$this->assertTrue( $builder->is_enabled() );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\LineItems\Builder::is_enabled
	 */
	public function test_line_items_always_enabled_for_paypal() {
		edd_delete_option( 'stripe_line_items_enabled' ); // Ensure setting is off

		$builder = new LineItemsBuilder( $this->purchase_data, 'paypal' );
		$this->assertTrue( $builder->is_enabled() );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\LineItems\Builder::build
	 */
	public function test_builder_returns_empty_array_when_disabled() {
		edd_delete_option( 'stripe_line_items_enabled' );

		$builder    = new LineItemsBuilder( $this->purchase_data, 'card' );
		$line_items = $builder->build();

		$this->assertIsArray( $line_items );
		$this->assertEmpty( $line_items );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\LineItems\Builder::build
	 */
	public function test_builder_creates_line_items_when_enabled() {
		edd_update_option( 'stripe_line_items_enabled', true );

		$builder    = new LineItemsBuilder( $this->purchase_data, 'card' );
		$line_items = $builder->build();

		$this->assertIsArray( $line_items );
		$this->assertNotEmpty( $line_items );
		$this->assertCount( 1, $line_items );

		edd_delete_option( 'stripe_line_items_enabled' );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\LineItems\Builder::build
	 */
	public function test_builder_includes_all_cart_items() {
		edd_update_option( 'stripe_line_items_enabled', true );

		// Add a second cart item.
		$this->purchase_data['cart_details'][] = array(
			'id'          => 2,
			'name'        => 'Second Download',
			'item_price'  => 49.99,
			'quantity'    => 2,
			'discount'    => 0,
			'tax'         => 0,
			'item_number' => array(
				'id'      => 2,
				'options' => array(),
			),
		);

		$builder    = new LineItemsBuilder( $this->purchase_data, 'card' );
		$line_items = $builder->build();

		$this->assertCount( 2, $line_items );

		edd_delete_option( 'stripe_line_items_enabled' );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\AmountDetails::build
	 */
	public function test_amount_details_returns_empty_when_line_items_disabled() {
		edd_delete_option( 'stripe_line_items_enabled' );

		$amount_details = new AmountDetails( $this->purchase_data, 'card' );
		$details        = $amount_details->build();

		$this->assertIsArray( $details );
		$this->assertEmpty( $details );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\AmountDetails::build
	 */
	public function test_amount_details_includes_line_items_when_enabled() {
		edd_update_option( 'stripe_line_items_enabled', true );

		$amount_details = new AmountDetails( $this->purchase_data, 'card' );
		$details        = $amount_details->build();

		$this->assertIsArray( $details );
		$this->assertArrayHasKey( 'line_items', $details );
		$this->assertNotEmpty( $details['line_items'] );

		edd_delete_option( 'stripe_line_items_enabled' );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\AmountDetails::build
	 */
	public function test_amount_details_excludes_payment_level_tax() {
		edd_update_option( 'stripe_line_items_enabled', true );

		$amount_details = new AmountDetails( $this->purchase_data, 'card' );
		$details        = $amount_details->build();

		// Tax should NOT be at payment level (it's in line items instead).
		$this->assertArrayNotHasKey( 'tax', $details );

		// But tax should be in the line items.
		$this->assertArrayHasKey( 'line_items', $details );
		$this->assertNotEmpty( $details['line_items'] );
		$this->assertArrayHasKey( 'tax', $details['line_items'][0] );

		edd_delete_option( 'stripe_line_items_enabled' );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\AmountDetails::build
	 */
	public function test_amount_details_excludes_payment_level_discount() {
		edd_update_option( 'stripe_line_items_enabled', true );

		$amount_details = new AmountDetails( $this->purchase_data, 'card' );
		$details        = $amount_details->build();

		// Payment-level discount is NOT included (item-level discounts are in line items).
		// Stripe doesn't allow both payment-level and item-level discounts simultaneously.
		$this->assertArrayNotHasKey( 'discount_amount', $details );

		edd_delete_option( 'stripe_line_items_enabled' );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\PaymentIntents\AmountDetails::build
	 */
	public function test_amount_details_filter_is_applied() {
		edd_update_option( 'stripe_line_items_enabled', true );

		add_filter( 'edds_amount_details', function( $details ) {
			$details['custom_field'] = 'test_value';
			return $details;
		} );

		$amount_details = new AmountDetails( $this->purchase_data, 'card' );
		$details        = $amount_details->build();

		$this->assertArrayHasKey( 'custom_field', $details );
		$this->assertEquals( 'test_value', $details['custom_field'] );

		remove_all_filters( 'edds_amount_details' );
		edd_delete_option( 'stripe_line_items_enabled' );
	}
}
