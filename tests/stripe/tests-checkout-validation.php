<?php
/**
 * Tests for Stripe Checkout Validation.
 *
 * @coversDefaultClass \EDD\Gateways\Stripe\Checkout\Validation
 */

namespace EDD\Tests\Stripe;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Gateways\Stripe\Checkout\Validation;

/**
 * @group edd_stripe
 * @group edd_stripe_validation
 */
class CheckoutValidation extends EDD_UnitTestCase {

	/**
	 * @covers ::intent_status
	 */
	public function test_intent_status_succeeds_for_succeeded() {
		$intent         = new \stdClass();
		$intent->status = 'succeeded';

		// Should not throw.
		Validation::intent_status( $intent );
		// Intentional pointless assertion to satisfy PHPUnit.
		$this->assertTrue( true );
	}

	/**
	 * @covers ::intent_status
	 */
	public function test_intent_status_succeeds_for_requires_capture() {
		$intent         = new \stdClass();
		$intent->status = 'requires_capture';

		Validation::intent_status( $intent );
		// Intentional pointless assertion to satisfy PHPUnit.
		$this->assertTrue( true );
	}

	/**
	 * @covers ::intent_status
	 */
	public function test_intent_status_throws_for_invalid_status() {
		$intent         = new \stdClass();
		$intent->status = 'requires_payment_method';

		$this->expectException( \EDD_Stripe_Gateway_Exception::class );
		Validation::intent_status( $intent );
	}

	/**
	 * @covers ::intent_status
	 */
	public function test_intent_status_accepts_custom_valid_statuses() {
		$intent         = new \stdClass();
		$intent->status = 'processing';

		Validation::intent_status( $intent, array( 'processing' ) );
		// Intentional pointless assertion to satisfy PHPUnit.
		$this->assertTrue( true );
	}

	/**
	 * @covers ::is_intent_paid
	 */
	public function test_is_intent_paid_true_for_succeeded() {
		$intent         = new \stdClass();
		$intent->status = 'succeeded';

		$this->assertTrue( Validation::is_intent_paid( $intent ) );
	}

	/**
	 * @covers ::is_intent_paid
	 */
	public function test_is_intent_paid_true_for_requires_capture() {
		$intent         = new \stdClass();
		$intent->status = 'requires_capture';

		$this->assertTrue( Validation::is_intent_paid( $intent ) );
	}

	/**
	 * An abandoned 3D Secure challenge has taken no money, so the customer must be able to confirm again.
	 *
	 * @covers ::is_intent_paid
	 */
	public function test_is_intent_paid_false_for_requires_action() {
		$intent         = new \stdClass();
		$intent->status = 'requires_action';

		$this->assertFalse( Validation::is_intent_paid( $intent ) );
	}

	/**
	 * A processing intent's funds have not settled yet, so it is not paid.
	 *
	 * @covers ::is_intent_paid
	 */
	public function test_is_intent_paid_false_for_processing() {
		$intent         = new \stdClass();
		$intent->status = 'processing';

		$this->assertFalse( Validation::is_intent_paid( $intent ) );
	}

	/**
	 * @covers ::is_intent_paid
	 */
	public function test_is_intent_paid_false_for_requires_payment_method() {
		$intent         = new \stdClass();
		$intent->status = 'requires_payment_method';

		$this->assertFalse( Validation::is_intent_paid( $intent ) );
	}

	/**
	 * @covers ::is_intent_paid
	 */
	public function test_is_intent_paid_false_for_missing_status() {
		$this->assertFalse( Validation::is_intent_paid( new \stdClass() ) );
	}

	/**
	 * @covers ::intent_amount
	 */
	public function test_intent_amount_passes_for_matching_amount() {
		$intent         = new \stdClass();
		$intent->object = 'payment_intent';
		$intent->amount = 9999;
		$intent->id     = 'pi_test123';

		// $99.99 * 100 = 9999
		Validation::intent_amount( $intent, 99.99 );
		// Intentional pointless assertion to satisfy PHPUnit.
		$this->assertTrue( true );
	}

	/**
	 * @covers ::intent_amount
	 */
	public function test_intent_amount_throws_for_mismatched_amount() {
		$intent         = new \stdClass();
		$intent->object = 'payment_intent';
		$intent->amount = 5000;
		$intent->id     = 'pi_test_mismatch';

		$this->expectException( \EDD_Stripe_Gateway_Exception::class );
		Validation::intent_amount( $intent, 99.99 );
	}

	/**
	 * A paid intent replayed against an inflated cart must be refused rather than completed.
	 *
	 * This is the only thing standing between a recovered payment and the cart manipulation
	 * described in easy-digital-downloads-pro#2287, so every path which can create an order from a
	 * pre-existing intent has to run it.
	 *
	 * @covers ::intent_amount
	 */
	public function test_intent_amount_throws_for_replayed_intent_with_mutated_cart() {
		$intent         = new \stdClass();
		$intent->object = 'payment_intent';
		$intent->status = 'succeeded';
		$intent->amount = 1000;
		$intent->id     = 'pi_test_replay';

		$this->expectException( \EDD_Stripe_Gateway_Exception::class );
		Validation::intent_amount( $intent, 199.99 );
	}

	/**
	 * @covers ::intent_amount
	 */
	public function test_intent_amount_skips_for_setup_intent() {
		$intent         = new \stdClass();
		$intent->object = 'setup_intent';
		$intent->id     = 'seti_test123';

		// Should not throw — SetupIntents have no amount.
		Validation::intent_amount( $intent, 99.99 );
		// Intentional pointless assertion to satisfy PHPUnit.
		$this->assertTrue( true );
	}

	/**
	 * @covers ::intent_amount
	 */
	public function test_intent_amount_allows_one_unit_rounding_tolerance() {
		$intent         = new \stdClass();
		$intent->object = 'payment_intent';
		$intent->amount = 10000;
		$intent->id     = 'pi_test_rounding';

		// Expected = 99.99 * 100 = 9999, actual = 10000, diff = 1 (within tolerance).
		Validation::intent_amount( $intent, 99.99 );
		// Intentional pointless assertion to satisfy PHPUnit.
		$this->assertTrue( true );
	}

	/**
	 * @covers ::intent_amount
	 */
	public function test_intent_amount_fails_beyond_rounding_tolerance() {
		$intent         = new \stdClass();
		$intent->object = 'payment_intent';
		$intent->amount = 10001;
		$intent->id     = 'pi_test_beyond';

		// Expected = 99.99 * 100 = 9999, actual = 10001, diff = 2 (exceeds tolerance).
		$this->expectException( \EDD_Stripe_Gateway_Exception::class );
		Validation::intent_amount( $intent, 99.99 );
	}

	/**
	 * @covers ::intent_amount
	 */
	public function test_intent_amount_with_zero_decimal_currency() {
		$intent         = new \stdClass();
		$intent->object = 'payment_intent';
		$intent->amount = 500;
		$intent->id     = 'pi_test_jpy';

		// Simulate zero-decimal currency (e.g. JPY) by setting the EDD currency.
		$original_currency = edd_get_option( 'currency', 'USD' );
		edd_update_option( 'currency', 'JPY' );

		Validation::intent_amount( $intent, 500 );
		// Intentional pointless assertion to satisfy PHPUnit.
		$this->assertTrue( true );

		edd_update_option( 'currency', $original_currency );
	}

	/**
	 * A free-trial order total is zeroed after the intent is created, so the completion
	 * check must validate the fixed intent amount against the immutable creation-time
	 * total stored in the metadata (a string such as "40.68"). See easy-digital-downloads-pro#2673.
	 *
	 * Pass condition: no exception thrown for a matching string metadata total.
	 *
	 * @covers ::intent_amount
	 */
	public function test_intent_amount_passes_for_string_metadata_total() {
		$this->expectNotToPerformAssertions();

		$intent         = new \stdClass();
		$intent->object = 'payment_intent';
		$intent->amount = 4068;
		$intent->id     = 'pi_test_trial';

		// Metadata total arrives from Stripe as a string. 40.68 * 100 = 4068.
		Validation::intent_amount( $intent, '40.68' );
	}

	/**
	 * The string-metadata path must still enforce the amount. A mismatched string total
	 * has to throw, so the guard fails loudly if it is ever removed. See easy-digital-downloads-pro#2673.
	 *
	 * @covers ::intent_amount
	 */
	public function test_intent_amount_throws_for_mismatched_string_metadata_total() {
		$intent         = new \stdClass();
		$intent->object = 'payment_intent';
		$intent->amount = 4068;
		$intent->id     = 'pi_test_trial_mismatch';

		// 99.99 * 100 = 9999, actual 4068 (far beyond tolerance) must throw.
		$this->expectException( \EDD_Stripe_Gateway_Exception::class );
		Validation::intent_amount( $intent, '99.99' );
	}

	/**
	 * @covers ::charge_amount
	 */
	public function test_charge_amount_passes_for_matching_amount() {
		$charge         = new \stdClass();
		$charge->amount = 2500;
		$charge->id     = 'ch_test123';

		// $25.00 * 100 = 2500
		Validation::charge_amount( $charge, 25.00 );
		// Intentional pointless assertion to satisfy PHPUnit.
		$this->assertTrue( true );
	}

	/**
	 * @covers ::charge_amount
	 */
	public function test_charge_amount_throws_for_mismatched_amount() {
		$charge         = new \stdClass();
		$charge->amount = 5000;
		$charge->id     = 'ch_test_mismatch';

		$this->expectException( \EDD_Stripe_Gateway_Exception::class );
		Validation::charge_amount( $charge, 25.00 );
	}

	/**
	 * @covers ::purchase_data
	 */
	public function test_purchase_data_passes_for_valid_data() {
		$purchase_data = array(
			'price'      => 99.99,
			'user_email' => 'test@example.com',
		);

		Validation::purchase_data( $purchase_data );
		// Intentional pointless assertion to satisfy PHPUnit.
		$this->assertTrue( true );
	}

	/**
	 * @covers ::purchase_data
	 */
	public function test_purchase_data_throws_for_empty_data() {
		$this->expectException( \EDD_Stripe_Gateway_Exception::class );
		Validation::purchase_data( array() );
	}

	/**
	 * @covers ::purchase_data
	 */
	public function test_purchase_data_throws_for_null() {
		$this->expectException( \EDD_Stripe_Gateway_Exception::class );
		Validation::purchase_data( null );
	}

	/**
	 * @covers ::intent_exists
	 */
	public function test_intent_exists_passes_for_array_with_id() {
		$intent = array( 'id' => 'pi_test123' );

		Validation::intent_exists( $intent );
		// Intentional pointless assertion to satisfy PHPUnit.
		$this->assertTrue( true );
	}

	/**
	 * @covers ::intent_exists
	 */
	public function test_intent_exists_passes_for_object_with_id() {
		$intent     = new \stdClass();
		$intent->id = 'pi_test123';

		Validation::intent_exists( $intent );
		// Intentional pointless assertion to satisfy PHPUnit.
		$this->assertTrue( true );
	}

	/**
	 * @covers ::intent_exists
	 */
	public function test_intent_exists_throws_for_empty_array() {
		$this->expectException( \EDD_Stripe_Gateway_Exception::class );
		Validation::intent_exists( array() );
	}

	/**
	 * @covers ::intent_exists
	 */
	public function test_intent_exists_throws_for_array_without_id() {
		$this->expectException( \EDD_Stripe_Gateway_Exception::class );
		Validation::intent_exists( array( 'object' => 'payment_intent' ) );
	}

	/**
	 * @covers ::intent_exists
	 */
	public function test_intent_exists_throws_for_object_without_id() {
		$intent         = new \stdClass();
		$intent->object = 'payment_intent';

		$this->expectException( \EDD_Stripe_Gateway_Exception::class );
		Validation::intent_exists( $intent );
	}

	/**
	 * A paid intent replayed against an inflated cart must be caught by comparing against
	 * the live order total, not the creation-time metadata total. If the metadata total
	 * ever wins again while the order total is positive, this reopens
	 * easy-digital-downloads-pro#2287.
	 *
	 * @covers ::get_expected_price
	 */
	public function test_get_expected_price_prefers_positive_order_total_over_metadata() {
		$order_id = edd_add_order(
			array(
				'status'   => 'complete',
				'currency' => 'USD',
				'gateway'  => 'stripe',
				'subtotal' => 99.99,
				'total'    => 99.99,
			)
		);

		$intent           = new \stdClass();
		$intent->metadata = (object) array(
			'edd_payment_id'    => $order_id,
			'edd_payment_total' => '40.68',
		);

		$this->assertEquals( 99.99, Validation::get_expected_price( $intent ) );
	}

	/**
	 * A free-trial order total is zeroed after the intent is created, so the expected
	 * price must fall back to the immutable creation-time metadata total. Without that
	 * fallback the live order total (0) is compared against the fixed intent amount and
	 * the completion check throws. See easy-digital-downloads-pro#2673.
	 *
	 * @covers ::get_expected_price
	 * @covers ::intent_amount
	 */
	public function test_intent_amount_does_not_throw_for_zeroed_order_with_metadata_total() {
		$order_id = edd_add_order(
			array(
				'status'   => 'pending',
				'currency' => 'USD',
				'gateway'  => 'stripe',
				'subtotal' => 0,
				'total'    => 0,
			)
		);

		$intent           = new \stdClass();
		$intent->object   = 'payment_intent';
		$intent->amount   = 4068;
		$intent->id       = 'pi_test_trial_zeroed';
		$intent->metadata = (object) array(
			'edd_payment_id'    => $order_id,
			'edd_payment_total' => '40.68',
		);

		$expected_price = Validation::get_expected_price( $intent );

		$this->assertEquals( 40.68, $expected_price );

		// Should not throw.
		Validation::intent_amount( $intent, $expected_price );
	}

	/**
	 * Intents created before the fix carry no metadata total, so the expected price
	 * falls back to the live order total.
	 *
	 * @covers ::get_expected_price
	 */
	public function test_get_expected_price_falls_back_to_order_total_without_metadata_total() {
		$order_id = edd_add_order(
			array(
				'status'   => 'complete',
				'currency' => 'USD',
				'gateway'  => 'stripe',
				'subtotal' => 25.00,
				'total'    => 25.00,
			)
		);

		$intent           = new \stdClass();
		$intent->metadata = (object) array(
			'edd_payment_id' => $order_id,
		);

		$this->assertEquals( 25.00, Validation::get_expected_price( $intent ) );
	}
}
