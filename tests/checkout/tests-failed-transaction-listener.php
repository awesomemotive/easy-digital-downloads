<?php
/**
 * Failed transaction page tests.
 *
 * @package     EDD\Tests\Checkout
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.2
 */

namespace EDD\Tests\Checkout;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Tests\Discounts\DiscountFixtures;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests which order the failed transaction page marks as failed.
 *
 * @group edd_checkout
 * @covers ::edd_listen_for_failed_payments
 */
class FailedTransactionListenerTest extends EDD_UnitTestCase {
	use DiscountFixtures;

	/**
	 * The session's resume order before the test.
	 *
	 * @var mixed
	 */
	private $original_resume_order;

	/**
	 * The payment-id query value before the test.
	 *
	 * @var string|null
	 */
	private $original_payment_id;

	/**
	 * The purchase session before the test.
	 *
	 * @var mixed
	 */
	private $original_purchase_session;

	/**
	 * Puts the request on the failed transaction page.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();

		$this->original_resume_order     = EDD()->session->get( 'edd_resume_payment' );
		$this->original_payment_id       = $_GET['payment-id'] ?? null;
		$this->original_purchase_session = edd_get_purchase_session();
		add_filter( 'edd_is_failure_page', '__return_true' );
		$this->forget_cached_claims();
	}

	/**
	 * Restores the session and query values.
	 *
	 * @return void
	 */
	public function tear_down(): void {
		remove_filter( 'edd_is_failure_page', '__return_true' );
		remove_filter( 'edd/discounts/failed_page_releases', '__return_true' );
		EDD()->session->set( 'edd_resume_payment', $this->original_resume_order );
		edd_set_purchase_session( $this->original_purchase_session );

		if ( is_null( $this->original_payment_id ) ) {
			unset( $_GET['payment-id'] );
		} else {
			$_GET['payment-id'] = $this->original_payment_id;
		}

		parent::tear_down();
	}

	public function test_failed_transaction_page_fails_the_sessions_pending_order() {
		$order_id = static::edd()->order->create(
			array(
				'status'  => 'pending',
				'gateway' => 'paypal_commerce',
			)
		);
		EDD()->session->set( 'edd_resume_payment', $order_id );

		edd_listen_for_failed_payments();

		$this->assertSame( 'failed', edd_get_order( $order_id )->status );
	}

	public function test_failed_transaction_page_ignores_an_order_outside_the_session() {
		$session_order_id = static::edd()->order->create(
			array(
				'status'  => 'pending',
				'gateway' => 'paypal_commerce',
			)
		);
		$other_order_id = static::edd()->order->create(
			array(
				'status'  => 'pending',
				'gateway' => 'paypal_commerce',
			)
		);
		EDD()->session->set( 'edd_resume_payment', $session_order_id );
		$_GET['payment-id'] = (string) $other_order_id;

		edd_listen_for_failed_payments();

		$this->assertSame( 'pending', edd_get_order( $other_order_id )->status );
		$this->assertSame( 'pending', edd_get_order( $session_order_id )->status );
	}

	public function test_failed_transaction_page_skips_a_session_order_that_no_longer_exists() {
		EDD()->session->set( 'edd_resume_payment', PHP_INT_MAX );

		edd_listen_for_failed_payments();

		$this->assertFalse( edd_get_order( PHP_INT_MAX ) );
	}

	public function test_failed_transaction_page_without_a_checkout_session_leaves_orders_alone() {
		$order_id = static::edd()->order->create(
			array(
				'status'  => 'pending',
				'gateway' => 'paypal_commerce',
			)
		);
		EDD()->session->set( 'edd_resume_payment', null );
		$_GET['payment-id'] = (string) $order_id;

		edd_listen_for_failed_payments();

		$this->assertSame( 'pending', edd_get_order( $order_id )->status );
	}

	public function test_failed_transaction_page_fails_the_purchase_sessions_order_once_the_resume_order_is_cleared() {
		$order_id = static::edd()->order->create(
			array(
				'status'      => 'pending',
				'gateway'     => 'paypal_commerce',
				'payment_key' => 'sent-to-gateway',
			)
		);
		edd_set_purchase_session( array( 'purchase_key' => 'sent-to-gateway' ) );
		EDD()->session->set( 'edd_resume_payment', null );
		$_GET['payment-id'] = (string) $order_id;

		edd_listen_for_failed_payments();

		$this->assertSame( 'failed', edd_get_order( $order_id )->status );
	}

	public function test_failed_transaction_page_ignores_a_requested_order_whose_key_does_not_match_the_purchase_session() {
		$other_order_id = static::edd()->order->create(
			array(
				'status'      => 'pending',
				'gateway'     => 'paypal_commerce',
				'payment_key' => 'other-visitor',
			)
		);
		edd_set_purchase_session( array( 'purchase_key' => 'sent-to-gateway' ) );
		EDD()->session->set( 'edd_resume_payment', null );
		$_GET['payment-id'] = (string) $other_order_id;

		edd_listen_for_failed_payments();

		$this->assertSame( 'pending', edd_get_order( $other_order_id )->status );
	}

	public function test_failed_transaction_page_leaves_a_paypal_standard_order_pending_and_holding_its_use() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order    = $this->create_order_with_discount(
			$discount,
			array(
				'status'  => 'pending',
				'gateway' => 'paypal',
			)
		);
		\EDD\Discounts\Claims::claim( $order );
		EDD()->session->set( 'edd_resume_payment', $order->id );

		edd_listen_for_failed_payments();

		// PayPal Standard can still complete the order through IPN, so giving the use back would let it be redeemed twice.
		$this->assertSame( 'pending', edd_get_order( $order->id )->status );
		$this->assertSame( 1, edd_get_discount( $discount->id )->get_uses() );
		$this->assertSame( absint( $order->id ), absint( EDD()->session->get( 'edd_resume_payment' ) ) );
	}

	public function test_failed_transaction_page_fails_a_paypal_standard_order_without_a_discount() {
		$order_id = static::edd()->order->create(
			array(
				'status'  => 'pending',
				'gateway' => 'paypal',
			)
		);
		EDD()->session->set( 'edd_resume_payment', $order_id );

		edd_listen_for_failed_payments();

		// With no use to give back, a canceled checkout fails as it always has, so failed-order listeners still fire.
		$this->assertSame( 'failed', edd_get_order( $order_id )->status );
		$this->assertEmpty( EDD()->session->get( 'edd_resume_payment' ) );
	}

	public function test_failed_transaction_page_fails_a_paypal_commerce_order_created_at_paypal_and_gives_back_its_use() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order    = $this->create_order_with_discount(
			$discount,
			array(
				'status'  => 'pending',
				'gateway' => 'paypal_commerce',
			)
		);
		\EDD\Discounts\Claims::claim( $order );
		edd_update_order_meta( $order->id, 'paypal_order_id', 'PAYPAL-' . $order->id );
		EDD()->session->set( 'edd_resume_payment', $order->id );

		edd_listen_for_failed_payments();

		$this->assertSame( 'failed', edd_get_order( $order->id )->status );
		$this->assertSame( 0, edd_get_discount( $discount->id )->get_uses() );
	}

	public function test_failed_transaction_page_leaves_a_paypal_commerce_order_never_created_at_paypal_pending_and_holding_its_use() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order    = $this->create_order_with_discount(
			$discount,
			array(
				'status'  => 'pending',
				'gateway' => 'paypal_commerce',
			)
		);
		\EDD\Discounts\Claims::claim( $order );
		EDD()->session->set( 'edd_resume_payment', $order->id );

		edd_listen_for_failed_payments();

		// Recurring builds these orders without PayPal's capture, which would recheck the discount, so it can complete them with the use given back.
		$this->assertSame( 'pending', edd_get_order( $order->id )->status );
		$this->assertSame( 1, edd_get_discount( $discount->id )->get_uses() );
	}

	public function test_failed_transaction_page_fails_an_order_holding_a_use_when_its_gateway_opts_in() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order    = $this->create_order_with_discount(
			$discount,
			array(
				'status'  => 'pending',
				'gateway' => 'paypal',
			)
		);
		\EDD\Discounts\Claims::claim( $order );
		EDD()->session->set( 'edd_resume_payment', $order->id );
		add_filter( 'edd/discounts/failed_page_releases', '__return_true' );

		edd_listen_for_failed_payments();

		$this->assertSame( 'failed', edd_get_order( $order->id )->status );
		$this->assertSame( 0, edd_get_discount( $discount->id )->get_uses() );
	}
}
