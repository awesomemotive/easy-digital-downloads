<?php
/**
 * Tests for completing a Stripe checkout.
 *
 * @package     EDD\Tests\Stripe
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.2
 */

namespace EDD\Tests\Stripe;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Gateways\Stripe\Checkout\Complete;
use EDD\Tests\Discounts\DiscountFixtures;

/**
 * Checkout Complete tests.
 *
 * @since 3.7.2
 *
 * @coversDefaultClass \EDD\Gateways\Stripe\Checkout\Complete
 */
class CheckoutComplete extends EDD_UnitTestCase {

	use DiscountFixtures;

	/**
	 * Pending Stripe order fixture.
	 *
	 * @var int
	 */
	private $pending_order_id;

	/**
	 * Pending transaction belonging to the order fixture.
	 *
	 * @var int
	 */
	private $pending_transaction_id;

	/**
	 * Order transaction lookups by object_id captured from the `query` filter.
	 *
	 * @var string[]
	 */
	private $transaction_lookups = array();

	/**
	 * Creates a pending Stripe order with one pending transaction.
	 */
	public function set_up() {
		parent::set_up();
		$this->forget_cached_claims();

		$this->pending_order_id = parent::edd()->order->create(
			array(
				'status'  => 'pending',
				'gateway' => 'stripe',
			)
		);

		$this->pending_transaction_id = parent::edd()->order_transaction->create(
			array(
				'object_id'      => $this->pending_order_id,
				'object_type'    => 'order',
				'transaction_id' => 'pi_test',
				'gateway'        => 'stripe',
				'status'         => 'pending',
				'total'          => 120,
			)
		);
	}

	/**
	 * Removes the query spy.
	 */
	public function tear_down() {
		remove_filter( 'query', array( $this, 'capture_transaction_lookup' ) );
		$this->transaction_lookups = array();

		parent::tear_down();
	}

	/**
	 * The order's pending transaction is completed with the charge ID.
	 *
	 * @covers ::mark_complete
	 */
	public function test_mark_complete_updates_the_order_transaction() {
		Complete::mark_complete( edd_get_order( $this->pending_order_id ), $this->get_succeeded_intent() );

		$transaction = edd_get_order_transaction( $this->pending_transaction_id );

		$this->assertSame( 'complete', $transaction->status );
		$this->assertSame( 'ch_test', $transaction->transaction_id );
		$this->assertSame( 'complete', edd_get_order( $this->pending_order_id )->status );
	}

	/**
	 * The transaction lookup includes object_type so it can use the object_type_object_id index.
	 *
	 * @covers ::mark_complete
	 */
	public function test_mark_complete_scopes_the_transaction_lookup_by_object_type() {
		add_filter( 'query', array( $this, 'capture_transaction_lookup' ) );

		Complete::mark_complete( edd_get_order( $this->pending_order_id ), $this->get_succeeded_intent() );

		$this->assertNotEmpty( $this->transaction_lookups );
		foreach ( $this->transaction_lookups as $query ) {
			$this->assertStringContainsString( 'object_type', $query );
		}
	}

	/**
	 * @covers ::mark_complete
	 */
	public function test_mark_complete_records_the_charge_on_an_order_held_for_its_discount() {
		$this->hold_pending_order_for_its_discount();

		Complete::mark_complete( edd_get_order( $this->pending_order_id ), $this->get_succeeded_intent() );

		$transaction = edd_get_order_transaction( $this->pending_transaction_id );
		$this->assertSame( 'on_hold', edd_get_order( $this->pending_order_id )->status );
		$this->assertSame( 'ch_test', $transaction->transaction_id );
	}

	/**
	 * @covers ::mark_complete
	 */
	public function test_mark_complete_skips_completion_listeners_on_an_order_held_for_its_discount() {
		$this->hold_pending_order_for_its_discount();
		$completed = did_action( 'edds_order_complete' );

		Complete::mark_complete( edd_get_order( $this->pending_order_id ), $this->get_succeeded_intent() );

		$this->assertSame( $completed, did_action( 'edds_order_complete' ) );
	}

	/**
	 * @covers ::mark_complete_from_charge
	 */
	public function test_mark_complete_from_charge_records_the_charge_on_an_order_held_for_its_discount() {
		$this->hold_pending_order_for_its_discount();
		$completed = did_action( 'edds_order_complete' );

		Complete::mark_complete_from_charge(
			edd_get_order( $this->pending_order_id ),
			\EDD\Vendor\Stripe\Charge::constructFrom(
				array(
					'id'             => 'ch_test',
					'object'         => 'charge',
					'payment_intent' => 'pi_test',
				)
			)
		);

		$this->assertContains( 'Stripe Charge ID: ch_test', $this->get_note_contents() );
		$this->assertSame( $completed, did_action( 'edds_order_complete' ) );
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

	/**
	 * Puts a used-up discount on the pending order without claiming it, as a payment taken before the order was built does.
	 *
	 * @return void
	 */
	private function hold_pending_order_for_its_discount() {
		$this->add_discount_adjustment( $this->pending_order_id, $this->create_used_up_discount() );
	}

	/**
	 * Gets the contents of the pending order's notes.
	 *
	 * @return string[]
	 */
	private function get_note_contents() {
		return wp_list_pluck(
			edd_get_notes(
				array(
					'object_id'   => $this->pending_order_id,
					'object_type' => 'order',
				)
			),
			'content'
		);
	}

	/**
	 * Builds a succeeded PaymentIntent without calling the Stripe API.
	 *
	 * @return \EDD\Vendor\Stripe\PaymentIntent
	 */
	private function get_succeeded_intent() {
		return \EDD\Vendor\Stripe\PaymentIntent::constructFrom(
			array(
				'id'            => 'pi_test',
				'object'        => 'payment_intent',
				'status'        => 'succeeded',
				'latest_charge' => 'ch_test',
			)
		);
	}
}
