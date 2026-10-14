<?php
/**
 * Order discount validation tests.
 *
 * @package     EDD\Tests\Discounts
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.2
 */

namespace EDD\Tests\Discounts;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Discounts\OrderValidation;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests redeeming the discounts stored on an order.
 *
 * @group edd_discounts
 * @coversDefaultClass \EDD\Discounts\OrderValidation
 */
class OrderValidationTest extends EDD_UnitTestCase {

	use DiscountFixtures;

	/**
	 * The cart's discounts before the test.
	 *
	 * @var mixed
	 */
	private $original_cart_discounts;

	/**
	 * Captures the cart's discounts.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();
		$this->forget_cached_claims();

		$this->original_cart_discounts = EDD()->session->get( 'cart_discounts' );
	}

	/**
	 * Tears down each test.
	 *
	 * @return void
	 */
	public function tear_down(): void {
		EDD()->session->set( 'cart_discounts', $this->original_cart_discounts );
		edd_clear_errors();

		parent::tear_down();
	}

	/**
	 * @covers ::redeem_discounts
	 */
	public function test_order_with_an_available_discount_is_redeemable() {
		$order = $this->pending_order_with( $this->create_discount() );

		$this->assertCount( 1, $order->get_discounts() );
		$this->assertSame( array(), OrderValidation::redeem_discounts( $order ) );
	}

	/**
	 * @covers ::redeem_discounts
	 */
	public function test_redeemable_order_claims_a_use_it_did_not_hold() {
		$discount = $this->create_discount();
		$order    = $this->pending_order_with( $discount );
		$this->assertFalse( \EDD\Discounts\Claims::holds( $order->id, $discount->id ) );

		$this->assertSame( array(), OrderValidation::redeem_discounts( $order ) );

		$this->assertTrue( \EDD\Discounts\Claims::holds( $order->id, $discount->id ) );
		$this->assertSame( 1, (int) edd_get_discount( $discount->id )->use_count );
	}

	/**
	 * @covers ::redeem_discounts
	 */
	public function test_second_order_cannot_redeem_the_last_use_the_first_order_claimed() {
		$discount     = $this->create_discount( array( 'max_uses' => 1 ) );
		$first_order  = $this->pending_order_with( $discount );
		$second_order = $this->pending_order_with( $discount );

		$this->assertSame( array(), OrderValidation::redeem_discounts( $first_order ) );

		$this->assertSame( array( $discount->code ), OrderValidation::redeem_discounts( $second_order ) );
		$this->assertSame( 1, (int) edd_get_discount( $discount->id )->use_count );
	}

	/**
	 * @covers ::get_unavailable_codes
	 */
	public function test_checking_an_order_does_not_claim_its_discount() {
		$discount = $this->create_discount();
		$order    = $this->pending_order_with( $discount );

		$this->assertSame( array(), OrderValidation::get_unavailable_codes( $order ) );

		$this->assertFalse( \EDD\Discounts\Claims::holds( $order->id, $discount->id ) );
		$this->assertSame( 0, (int) edd_get_discount( $discount->id )->use_count );
	}

	/**
	 * @covers ::redeem_discounts
	 */
	public function test_order_that_loses_the_last_use_to_another_order_is_not_redeemed() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order    = $this->pending_order_with( $discount );
		add_filter(
			'edd_is_discount_active',
			function ( $is_active ) use ( $discount ) {
				global $wpdb;
				// Another order takes the last use between this order's check and its claim.
				$wpdb->update( $wpdb->edd_adjustments, array( 'use_count' => 1 ), array( 'id' => $discount->id ) );

				return $is_active;
			}
		);

		$this->assertSame( array( $discount->code ), OrderValidation::redeem_discounts( $order ) );
		$this->assertFalse( \EDD\Discounts\Claims::holds( $order->id, $discount->id ) );
	}

	/**
	 * @covers ::refuse_unclaimed
	 */
	public function test_order_is_not_refused_when_the_last_use_is_held_by_an_order_left_unpaid_over_an_hour() {
		$discount        = $this->create_discount( array( 'max_uses' => 1 ) );
		$abandoned_order = $this->pending_order_with( $discount, 'abandoned@edd.test' );
		$this->assertTrue( \EDD\Discounts\Claims::claim( $abandoned_order ) );
		$this->leave_unpaid_at_paypal_for_two_hours( $abandoned_order->id );
		$order = $this->pending_order_with( $discount );

		$this->assertSame( '', $this->refuse_unclaimed_in_the_building_session( $order ) );

		$this->assertTrue( \EDD\Discounts\Claims::holds( $order->id, $discount->id ) );
		$this->assertFalse( \EDD\Discounts\Claims::holds( $abandoned_order->id, $discount->id ) );
		$this->assertSame( 1, (int) edd_get_discount( $discount->id )->use_count );
	}

	/**
	 * @covers ::refuse
	 */
	public function test_refusing_an_order_removes_only_the_refused_code_from_the_cart() {
		$refused_discount = $this->create_discount();
		$kept_discount    = $this->create_discount();
		$order            = $this->pending_order_with( $refused_discount );
		EDD()->session->set( 'cart_discounts', $refused_discount->code . '|' . $kept_discount->code );
		$this->assertCount( 2, edd_get_cart_discounts() );

		$message = OrderValidation::refuse( $order->id, array( $refused_discount->code ) );

		$this->assertSame( 'failed', edd_get_order( $order->id )->status );
		$this->assertSame( array( $kept_discount->code ), array_values( edd_get_cart_discounts() ) );
		$this->assertStringContainsString( $refused_discount->code, $message );
	}

	/**
	 * @covers ::refuse
	 */
	public function test_refusing_an_order_records_the_message_for_the_buyer() {
		$discount = $this->create_discount();
		$order    = $this->pending_order_with( $discount );

		$message = OrderValidation::refuse( $order->id, array( $discount->code ) );

		$this->assertSame( $message, edd_get_errors()['edd-discount-error'] );
	}

	/**
	 * @covers ::redeem_discounts
	 */
	public function test_order_without_discounts_is_redeemable() {
		$order = static::edd()->order->create_and_get( array( 'status' => 'pending' ) );

		$this->assertEmpty( $order->get_discounts() );
		$this->assertSame( array(), OrderValidation::redeem_discounts( $order ) );
	}

	/**
	 * @covers ::redeem_discounts
	 */
	public function test_discount_at_its_cap_is_not_redeemable() {
		$discount = $this->create_discount(
			array(
				'max_uses'  => 1,
				'use_count' => 1,
			)
		);
		$order    = $this->pending_order_with( $discount );

		$this->assertSame( 'active', edd_get_discount( $discount->id )->status );
		$this->assertSame( array( $discount->code ), OrderValidation::redeem_discounts( $order ) );
	}

	/**
	 * @covers ::redeem_discounts
	 */
	public function test_inactive_discount_is_not_redeemable() {
		$discount = $this->create_discount();
		$order    = $this->pending_order_with( $discount );

		edd_update_discount( $discount->id, array( 'status' => 'inactive' ) );

		$this->assertSame( 'inactive', edd_get_discount( $discount->id )->status );
		$this->assertSame( array( $discount->code ), OrderValidation::redeem_discounts( $order ) );
	}

	/**
	 * @covers ::redeem_discounts
	 */
	public function test_expired_discount_is_not_redeemable() {
		$discount = $this->create_discount();
		$order    = $this->pending_order_with( $discount );

		edd_update_discount( $discount->id, array( 'end_date' => '2020-01-01 00:00:00' ) );

		$this->assertSame( 'active', edd_get_discount( $discount->id )->status );
		$this->assertSame( array( $discount->code ), OrderValidation::redeem_discounts( $order ) );
	}

	/**
	 * @covers ::redeem_discounts
	 */
	public function test_deleted_discount_is_not_redeemable() {
		$discount = $this->create_discount();
		$order    = $this->pending_order_with( $discount );

		edd_delete_discount( $discount->id );

		$this->assertFalse( edd_get_discount( $discount->id ) );
		$this->assertSame( array( $discount->code ), OrderValidation::redeem_discounts( $order ) );
	}

	/**
	 * Software Licensing adds its renewal discount this way, so refusing it blocks every discounted renewal.
	 *
	 * @covers ::refuse_unclaimed
	 */
	public function test_order_with_a_discount_adjustment_without_a_discount_id_is_not_refused() {
		$order = static::edd()->order->create_and_get( array( 'status' => 'pending' ) );
		$this->add_adjustment_without_discount_id( $order->id );

		$this->assertCount( 1, edd_get_order( $order->id )->get_discounts() );
		$this->assertSame( '', $this->refuse_unclaimed_in_the_building_session( $order ) );
		$this->assertSame( 'pending', edd_get_order( $order->id )->status );
	}

	/**
	 * @covers ::redeem_discounts
	 */
	public function test_discount_at_its_cap_beside_an_adjustment_without_a_discount_id_is_not_redeemable() {
		$discount = $this->create_discount(
			array(
				'max_uses'  => 1,
				'use_count' => 1,
			)
		);
		$order    = $this->pending_order_with( $discount );
		$this->add_adjustment_without_discount_id( $order->id );

		$this->assertSame( array( $discount->code ), OrderValidation::redeem_discounts( edd_get_order( $order->id ) ) );
	}

	/**
	 * @covers ::redeem_discounts
	 */
	public function test_single_use_discount_held_by_the_order_itself_is_redeemable() {
		$discount = $this->create_discount( array( 'once_per_customer' => 1 ) );
		$email    = 'holder@edd.test';
		$customer = edd_add_customer( array( 'email' => $email ) );
		$order    = $this->create_order_with_discount(
			$discount,
			array(
				'status'      => 'pending',
				'customer_id' => $customer,
				'email'       => $email,
			)
		);
		$this->assertTrue( \EDD\Discounts\Claims::claim( $order ) );
		$this->assertTrue( edd_get_discount( $discount->id )->is_used( $email, false ) );

		$this->assertSame( array(), OrderValidation::redeem_discounts( $order ) );
	}

	/**
	 * @covers ::redeem_discounts
	 */
	public function test_single_use_discount_already_used_by_the_customer_is_not_redeemable() {
		$discount = $this->create_discount( array( 'once_per_customer' => 1 ) );
		$email    = 'returning@edd.test';
		$customer = edd_add_customer( array( 'email' => $email ) );

		$this->complete_order_with( $discount, $customer, $email );
		$order = $this->pending_order_with( $discount, $email );

		$this->assertTrue( edd_get_discount( $discount->id )->is_single_use );
		$this->assertSame( array( $discount->code ), OrderValidation::redeem_discounts( $order ) );
	}

	/**
	 * @covers ::redeem_discounts
	 */
	public function test_single_use_discount_on_an_order_without_an_email_is_redeemable() {
		$discount = $this->create_discount( array( 'once_per_customer' => 1 ) );
		$customer = edd_add_customer( array( 'email' => 'earlier@edd.test' ) );

		$this->complete_order_with( $discount, $customer, 'earlier@edd.test' );
		$order = $this->pending_order_with( $discount, '' );

		$this->assertSame( '', $order->email );
		$this->assertSame( array(), OrderValidation::redeem_discounts( $order ) );
	}

	/**
	 * @covers ::refuse_unclaimed
	 */
	public function test_order_built_while_the_customer_holds_a_single_use_discount_is_refused() {
		$discount      = $this->create_discount( array( 'once_per_customer' => 1 ) );
		$email         = 'concurrent@edd.test';
		$customer      = static::edd()->customer->create( array( 'email' => $email ) );
		$holding_order = $this->customer_order_with( $discount, $customer, $email );
		$this->assertTrue( \EDD\Discounts\Claims::claim( $holding_order ) );
		$building_order = $this->customer_order_with( $discount, $customer, $email );
		$this->assertTrue( \EDD\Discounts\Claims::claim( $building_order ) );

		$refusal = $this->refuse_unclaimed_in_the_building_session( $building_order );

		$this->assertNotSame( '', $refusal );
		$this->assertSame( 'failed', edd_get_order( $building_order->id )->status );
		$this->assertSame( 'pending', edd_get_order( $holding_order->id )->status );
		$this->assertTrue( \EDD\Discounts\Claims::holds( $holding_order->id, $discount->id ) );
	}

	/**
	 * @covers ::refuse_unclaimed
	 */
	public function test_only_order_holding_a_single_use_discount_is_not_refused() {
		$discount = $this->create_discount( array( 'once_per_customer' => 1 ) );
		$email    = 'first-time@edd.test';
		$customer = static::edd()->customer->create( array( 'email' => $email ) );
		$order    = $this->customer_order_with( $discount, $customer, $email );
		$this->assertTrue( \EDD\Discounts\Claims::claim( $order ) );

		$this->assertSame( '', $this->refuse_unclaimed_in_the_building_session( $order ) );
		$this->assertSame( 'pending', edd_get_order( $order->id )->status );
	}

	/**
	 * @covers ::start_capture
	 */
	public function test_holding_an_order_for_capture_fires_its_status_transition() {
		$order       = $this->pending_order_with( $this->create_discount() );
		$transitions = array();
		add_action(
			'edd_transition_order_status',
			function ( $old_status, $new_status, $order_id ) use ( &$transitions ) {
				$transitions[] = array( $old_status, $new_status, $order_id );
			},
			10,
			3
		);

		$this->assertTrue( OrderValidation::start_capture( $order->id ) );

		$this->assertSame( 'processing', edd_get_order( $order->id )->status );
		$this->assertSame( array( array( 'pending', 'processing', $order->id ) ), $transitions );
	}

	/**
	 * @covers ::start_capture
	 */
	public function test_order_that_is_not_pending_is_not_held_for_capture() {
		$order       = $this->create_order_with_discount( $this->create_discount(), array( 'status' => 'processing' ) );
		$transitions = 0;
		add_action(
			'edd_transition_order_status',
			function () use ( &$transitions ) {
				++$transitions;
			}
		);

		$this->assertFalse( OrderValidation::start_capture( $order->id ) );
		$this->assertSame( 0, $transitions );
	}

	/**
	 * @covers ::start_capture
	 */
	public function test_order_is_put_back_to_pending_when_its_capture_transition_errors() {
		$order = $this->pending_order_with( $this->create_discount() );
		add_action(
			'edd_transition_order_status',
			function ( $old_status, $new_status ) {
				if ( 'processing' === $new_status ) {
					throw new \RuntimeException( 'Listener error.' );
				}
			},
			10,
			2
		);
		$error = null;

		try {
			OrderValidation::start_capture( $order->id );
		} catch ( \RuntimeException $e ) {
			$error = $e;
		}

		$this->assertInstanceOf( \RuntimeException::class, $error );
		$this->assertSame( 'pending', edd_get_order( $order->id )->status );
	}

	/**
	 * @covers ::reopen_failed
	 */
	public function test_failed_order_is_reopened_with_its_status_transition() {
		$order       = $this->create_order_with_discount( $this->create_discount(), array( 'status' => 'failed' ) );
		$transitions = array();
		add_action(
			'edd_transition_order_status',
			function ( $old_status, $new_status, $order_id ) use ( &$transitions ) {
				$transitions[] = array( $old_status, $new_status, $order_id );
			},
			10,
			3
		);

		$this->assertTrue( OrderValidation::reopen_failed( $order->id ) );

		$this->assertSame( 'pending', edd_get_order( $order->id )->status );
		$this->assertSame( array( array( 'failed', 'pending', $order->id ) ), $transitions );
	}

	/**
	 * A reopened order's items move with it, so reports reading item statuses agree with the order.
	 *
	 * @covers ::reopen_failed
	 */
	public function test_reopened_order_items_are_pending() {
		$order = $this->create_order_with_discount( $this->create_discount(), array( 'status' => 'failed' ) );
		$item  = static::edd()->order_item->create_and_get(
			array(
				'order_id' => $order->id,
				'status'   => 'failed',
			)
		);

		$this->assertTrue( OrderValidation::reopen_failed( $order->id ) );

		$this->assertSame( 'pending', edd_get_order_item( $item->id )->status );
	}

	/**
	 * @covers ::reopen_failed
	 */
	public function test_order_held_for_capture_is_not_reopened_by_a_second_submit() {
		$order = $this->create_order_with_discount( $this->create_discount(), array( 'status' => 'failed' ) );
		$this->assertTrue( OrderValidation::reopen_failed( $order->id ) );
		$this->assertTrue( OrderValidation::start_capture( $order->id ) );

		$this->assertFalse( OrderValidation::reopen_failed( $order->id ) );
		$this->assertFalse( OrderValidation::start_capture( $order->id ) );
		$this->assertSame( 'processing', edd_get_order( $order->id )->status );
	}

	/**
	 * A webhook can complete the order while the capture request is still ending its hold.
	 *
	 * @covers ::end_capture
	 */
	public function test_order_completed_while_its_capture_ends_stays_complete() {
		global $wpdb;

		$order                     = $this->create_order_with_discount( $this->create_discount(), array( 'status' => 'processing' ) );
		$is_completed              = false;
		$complete_before_the_write = function ( $query ) use ( $wpdb, $order, &$is_completed ) {
			if ( ! $is_completed && 0 === stripos( $query, 'UPDATE' ) && false !== strpos( $query, $wpdb->edd_orders ) && false !== strpos( $query, "'pending'" ) ) {
				$is_completed = true;
				$wpdb->update( $wpdb->edd_orders, array( 'status' => 'complete' ), array( 'id' => $order->id ) );
			}

			return $query;
		};
		add_filter( 'query', $complete_before_the_write );

		OrderValidation::end_capture( $order->id, 'pending' );

		remove_filter( 'query', $complete_before_the_write );
		$this->assertTrue( $is_completed );
		$this->assertSame( 'complete', edd_get_order( $order->id )->status );
	}

	/**
	 * Creates a pending order carrying a discount adjustment, as checkout stores it.
	 *
	 * @param \EDD_Discount $discount The discount.
	 * @param string        $email    The order email.
	 * @return \EDD\Orders\Order
	 */
	private function pending_order_with( $discount, $email = 'buyer@edd.test' ) {
		return $this->create_order_with_discount(
			$discount,
			array(
				'status' => 'pending',
				'email'  => $email,
			)
		);
	}

	/**
	 * Creates a pending order for a customer carrying a discount adjustment.
	 *
	 * @param \EDD_Discount $discount    The discount.
	 * @param int           $customer_id The customer ID.
	 * @param string        $email       The order email.
	 * @return \EDD\Orders\Order
	 */
	private function customer_order_with( $discount, $customer_id, $email ) {
		return $this->create_order_with_discount(
			$discount,
			array(
				'status'      => 'pending',
				'customer_id' => $customer_id,
				'email'       => $email,
			)
		);
	}

	/**
	 * Makes a pending order look like a PayPal Commerce checkout created at PayPal and left unpaid two hours ago.
	 *
	 * @param int $order_id The order ID.
	 * @return void
	 */
	private function leave_unpaid_at_paypal_for_two_hours( $order_id ) {
		global $wpdb;

		// The order factory always sets a completed date, which no unpaid order has.
		$wpdb->update(
			$wpdb->edd_orders,
			array(
				'gateway'        => 'paypal_commerce',
				'date_created'   => gmdate( 'Y-m-d H:i:s', time() - 2 * HOUR_IN_SECONDS ),
				'date_completed' => null,
			),
			array( 'id' => $order_id )
		);
		( new \EDD\Database\Queries\Order() )->refresh_item_cache( $order_id );
		edd_update_order_meta( $order_id, 'paypal_order_id', 'PAYPAL-' . $order_id );

		$this->assertEmpty( edd_get_order( $order_id )->date_completed );
	}

	/**
	 * Adds a discount adjustment with no discount ID, as Software Licensing stores its renewal discount.
	 *
	 * @param int $order_id The order ID.
	 * @return void
	 */
	private function add_adjustment_without_discount_id( $order_id ) {
		// The order adjustment factory always generates a type_id.
		edd_add_order_adjustment(
			array(
				'object_id'   => $order_id,
				'object_type' => 'order',
				'type'        => 'discount',
				'type_key'    => 'renewal_discount',
				'description' => 'Renewal Discount',
				'subtotal'    => 5,
				'tax'         => 0,
				'total'       => 5,
			)
		);
	}

	/**
	 * Refuses an order from the session that built it, as edd_build_order() leaves the session.
	 *
	 * @param \EDD\Orders\Order $order The order just built.
	 * @return string The refusal message.
	 */
	private function refuse_unclaimed_in_the_building_session( $order ) {
		$original_order = EDD()->session->get( 'edd_resume_payment' );
		EDD()->session->set( 'edd_resume_payment', $order->id );

		try {
			return OrderValidation::refuse_unclaimed( $order->id );
		} finally {
			EDD()->session->set( 'edd_resume_payment', $original_order );
		}
	}

	/**
	 * Creates a complete order for a customer carrying a discount adjustment.
	 *
	 * @param \EDD_Discount $discount    The discount.
	 * @param int           $customer_id The customer ID.
	 * @param string        $email       The order email.
	 * @return void
	 */
	private function complete_order_with( $discount, $customer_id, $email ) {
		$this->create_order_with_discount(
			$discount,
			array(
				'status'      => 'complete',
				'customer_id' => $customer_id,
				'email'       => $email,
			)
		);
	}

}
