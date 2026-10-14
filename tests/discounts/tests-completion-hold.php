<?php
/**
 * Completion hold tests.
 *
 * @package     EDD\Tests\Discounts
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.2
 */

namespace EDD\Tests\Discounts;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Discounts\Claims;
use EDD\Discounts\CompletionHold;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests that a paid order whose discount has no use left waits for the store instead of completing.
 *
 * @group edd_discounts
 * @coversDefaultClass \EDD\Discounts\CompletionHold
 */
class CompletionHoldTest extends EDD_UnitTestCase {

	use DiscountFixtures;

	/**
	 * Sets up each test.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();
		$this->forget_cached_claims();
	}

	/**
	 * Tears down each test.
	 *
	 * @return void
	 */
	public function tear_down(): void {
		remove_filter( 'wp_doing_ajax', '__return_true' );

		parent::tear_down();
	}

	public function test_paid_order_without_a_use_left_is_put_on_hold() {
		$order_id = $this->unclaimed_order( $this->create_used_up_discount() );

		edd_update_order_status( $order_id, 'complete' );

		$this->assertSame( 'on_hold', edd_get_order( $order_id )->status );
		$this->assertTrue( CompletionHold::holds( $order_id ) );
	}

	public function test_held_order_does_not_count_the_use() {
		$discount = $this->create_used_up_discount();
		$order_id = $this->unclaimed_order( $discount );

		edd_update_order_status( $order_id, 'complete' );

		$this->assertSame( 1, (int) edd_get_discount( $discount->id )->use_count );
	}

	public function test_held_order_grants_no_files() {
		$order_id = $this->unclaimed_order( $this->create_used_up_discount() );

		edd_update_order_status( $order_id, 'complete' );

		$items = edd_get_order( $order_id )->items;
		$this->assertNotEmpty( $items );
		foreach ( $items as $item ) {
			$this->assertFalse( $item->is_deliverable() );
		}
	}

	public function test_held_order_is_noted() {
		$discount = $this->create_used_up_discount();
		$order_id = $this->unclaimed_order( $discount );

		edd_update_order_status( $order_id, 'complete' );

		$this->assertContains(
			sprintf( 'Discount %s had no uses left, so this paid order is on hold. Complete it to count the use past the discount\'s maximum uses, or refund it.', $discount->code ),
			$this->get_note_contents( $order_id )
		);
	}

	public function test_held_order_announces_the_codes_without_a_use_left() {
		$discount = $this->create_used_up_discount();
		$order_id = $this->unclaimed_order( $discount );
		$codes    = array();
		$listener = function ( $order, $held_codes ) use ( &$codes ) {
			$codes = $held_codes;
		};
		add_action( 'edd/discounts/completion_held', $listener, 10, 2 );

		edd_update_order_status( $order_id, 'complete' );

		remove_action( 'edd/discounts/completion_held', $listener, 10 );
		$this->assertSame( array( $discount->code ), $codes );
	}

	public function test_held_order_never_moves_to_complete() {
		$order_id     = $this->unclaimed_order( $this->create_used_up_discount() );
		$new_statuses = array();
		$listener     = function ( $old_status, $new_status ) use ( &$new_statuses ) {
			$new_statuses[] = $new_status;
		};
		add_action( 'edd_transition_order_status', $listener, 10, 2 );

		edd_update_order_status( $order_id, 'complete' );

		remove_action( 'edd_transition_order_status', $listener, 10 );
		$this->assertSame( array( 'on_hold' ), $new_statuses );
	}

	public function test_gateway_completing_a_held_order_leaves_it_on_hold() {
		$order_id = $this->unclaimed_order( $this->create_used_up_discount() );
		edd_update_order_status( $order_id, 'complete' );

		edd_update_order_status( $order_id, 'complete' );

		$this->assertSame( 'on_hold', edd_get_order( $order_id )->status );
	}

	public function test_gateway_completing_a_held_order_adds_no_second_note() {
		$order_id = $this->unclaimed_order( $this->create_used_up_discount() );
		edd_update_order_status( $order_id, 'complete' );
		$notes = count( $this->get_note_contents( $order_id ) );

		edd_update_order_status( $order_id, 'complete' );

		$this->assertCount( $notes, $this->get_note_contents( $order_id ) );
	}

	public function test_admin_ajax_completion_of_an_order_without_a_use_left_is_held() {
		$order_id = $this->unclaimed_order( $this->create_used_up_discount() );
		add_filter( 'wp_doing_ajax', '__return_true' );

		$this->complete_as_store_admin( $order_id );

		$this->assertSame( 'on_hold', edd_get_order( $order_id )->status );
	}

	public function test_store_completing_a_held_order_completes_it() {
		$order_id = $this->unclaimed_order( $this->create_used_up_discount() );
		edd_update_order_status( $order_id, 'complete' );

		$this->complete_as_store_admin( $order_id );

		$this->assertSame( 'complete', edd_get_order( $order_id )->status );
	}

	public function test_store_completing_a_held_order_counts_the_use_past_the_limit() {
		$discount = $this->create_used_up_discount();
		$order_id = $this->unclaimed_order( $discount );
		edd_update_order_status( $order_id, 'complete' );

		$this->complete_as_store_admin( $order_id );

		$this->assertSame( 2, (int) edd_get_discount( $discount->id )->use_count );
		$this->assertTrue( Claims::holds( $order_id, $discount->id ) );
	}

	public function test_order_with_a_use_left_completes() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order_id = $this->unclaimed_order( $discount );

		edd_update_order_status( $order_id, 'complete' );

		$this->assertSame( 'complete', edd_get_order( $order_id )->status );
		$this->assertTrue( Claims::holds( $order_id, $discount->id ) );
	}

	public function test_failed_order_paid_later_with_a_use_left_completes() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order_id = $this->unclaimed_order( $discount, 'failed' );

		edd_update_order_status( $order_id, 'complete' );

		$this->assertSame( 'complete', edd_get_order( $order_id )->status );
		$this->assertSame( 1, (int) edd_get_discount( $discount->id )->use_count );
	}

	public function test_failed_order_paid_later_without_a_use_left_is_put_on_hold() {
		$order_id = $this->unclaimed_order( $this->create_used_up_discount(), 'failed' );

		edd_update_order_status( $order_id, 'complete' );

		$this->assertSame( 'on_hold', edd_get_order( $order_id )->status );
	}

	public function test_order_without_a_discount_completes() {
		$order_id = static::edd()->order->create( array( 'status' => 'pending' ) );

		edd_update_order_status( $order_id, 'complete' );

		$this->assertSame( 'complete', edd_get_order( $order_id )->status );
	}

	/**
	 * @covers ::holds
	 */
	public function test_order_on_hold_for_another_reason_is_not_held_for_its_discount() {
		$order_id = static::edd()->order->create( array( 'status' => 'pending' ) );

		edd_update_order_status( $order_id, 'on_hold' );

		$this->assertFalse( CompletionHold::holds( $order_id ) );
	}

	/**
	 * Creates an order carrying a discount that it holds no use of, as a gateway that charged first leaves it.
	 *
	 * @param \EDD_Discount $discount The discount.
	 * @param string        $status   The order status.
	 * @return int The order ID.
	 */
	private function unclaimed_order( $discount, $status = 'pending' ) {
		return $this->create_order_with_discount( $discount, array( 'status' => $status ) )->id;
	}

	/**
	 * Gets the contents of an order's notes.
	 *
	 * @param int $order_id The order ID.
	 * @return string[]
	 */
	private function get_note_contents( $order_id ) {
		return wp_list_pluck(
			edd_get_notes(
				array(
					'object_id'   => $order_id,
					'object_type' => 'order',
				)
			),
			'content'
		);
	}
}
