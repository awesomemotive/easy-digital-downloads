<?php
/**
 * Discount claim tests.
 *
 * @package     EDD\Tests\Discounts
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.2
 */

namespace EDD\Tests\Discounts;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Cron\Components\Orders as OrdersCron;
use EDD\Discounts\Claims;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests claiming and giving back discount uses as orders move through checkout.
 *
 * @group edd_discounts
 */
class DiscountClaimsTest extends EDD_UnitTestCase {

	use DiscountFixtures;

	/**
	 * The buyer's email address.
	 *
	 * @var string
	 */
	const EMAIL = 'claims@edd.test';

	/**
	 * The session's resume order before the test.
	 *
	 * @var mixed
	 */
	private $original_resume_order;

	/**
	 * The time claims started being tracked before the test.
	 *
	 * @var mixed
	 */
	private $original_claims_started;

	/**
	 * Captures the session's resume order.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();
		$this->forget_cached_claims();

		$this->original_resume_order   = EDD()->session->get( 'edd_resume_payment' );
		$this->original_claims_started = get_option( Claims::STARTED_OPTION );
		EDD()->session->set( 'edd_resume_payment', null );
	}

	/**
	 * Restores the session's resume order.
	 *
	 * @return void
	 */
	public function tear_down(): void {
		edd_empty_cart();
		EDD()->session->set( 'edd_resume_payment', $this->original_resume_order );

		if ( false === $this->original_claims_started ) {
			delete_option( Claims::STARTED_OPTION );
		} else {
			update_option( Claims::STARTED_OPTION, $this->original_claims_started );
		}

		parent::tear_down();
	}

	public function test_building_an_order_claims_a_use_of_its_discount() {
		$discount = $this->create_discount( array( 'max_uses' => 5 ) );

		$order_id = $this->build_order( $discount );

		$this->assertCount( 1, edd_get_order( $order_id )->get_discounts() );
		$this->assertTrue( Claims::holds( $order_id, $discount->id ) );
		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_building_an_order_at_the_cap_does_not_hold_the_discount() {
		$discount = $this->create_discount(
			array(
				'max_uses'  => 1,
				'use_count' => 1,
			)
		);

		$order_id = $this->build_order( $discount );

		$this->assertCount( 1, edd_get_order( $order_id )->get_discounts() );
		$this->assertNotEmpty( Claims::get_unclaimed( edd_get_order( $order_id ) ) );
		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_claiming_an_order_again_does_not_count_another_use() {
		$discount = $this->create_discount();
		$order_id = $this->build_order( $discount );

		$this->assertTrue( Claims::claim( edd_get_order( $order_id ) ) );

		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_releasing_an_order_again_does_not_give_back_another_use() {
		$discount = $this->create_discount( array( 'use_count' => 3 ) );
		$order_id = $this->build_order( $discount );
		$this->assertSame( 4, $this->get_use_count( $discount ) );

		$this->assertSame( 1, Claims::release( $order_id ) );
		$this->assertSame( 0, Claims::release( $order_id ) );

		$this->assertSame( 3, $this->get_use_count( $discount ) );
	}

	/**
	 * @dataProvider provider_release_statuses
	 */
	public function test_pending_order_ending_without_payment_gives_back_its_use( $status ) {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order_id = $this->build_order( $discount );
		$this->assertSame( 'inactive', edd_get_discount( $discount->id )->status );

		edd_update_order_status( $order_id, $status );

		$discount = edd_get_discount( $discount->id );
		$this->assertSame( $status, edd_get_order( $order_id )->status );
		$this->assertSame( 0, (int) $discount->use_count );
		$this->assertSame( 'active', $discount->status );
		$this->assertFalse( Claims::holds( $order_id, $discount->id ) );
	}

	public function test_failing_a_pending_order_that_holds_no_use_leaves_the_count() {
		$discount = $this->create_discount(
			array(
				'max_uses'  => 5,
				'use_count' => 3,
			)
		);
		$order_id = $this->order_without_claim( $discount );
		$this->assertFalse( Claims::holds( $order_id, $discount->id ) );

		edd_update_order_status( $order_id, 'failed' );

		$this->assertSame( 'failed', edd_get_order( $order_id )->status );
		$this->assertSame( 3, $this->get_use_count( $discount ) );
	}

	public function test_completing_an_order_that_holds_its_use_counts_it_once() {
		$discount = $this->create_discount();
		$order_id = $this->build_order( $discount );

		edd_update_order_status( $order_id, 'complete' );

		$this->assertSame( 'complete', edd_get_order( $order_id )->status );
		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_completing_an_order_a_second_time_counts_one_use() {
		$discount = $this->create_discount();
		$order_id = $this->build_order( $discount );
		edd_update_order_status( $order_id, 'complete' );
		edd_update_order_status( $order_id, 'revoked' );

		edd_update_order_status( $order_id, 'complete' );

		$this->assertSame( 'complete', edd_get_order( $order_id )->status );
		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_store_completing_an_order_without_a_use_left_counts_it_past_the_limit() {
		$discount = $this->create_used_up_discount();
		$order_id = $this->order_without_claim( $discount );

		$this->complete_as_store_admin( $order_id );

		$this->assertSame( 'complete', edd_get_order( $order_id )->status );
		$this->assertSame( 2, $this->get_use_count( $discount ) );
		$this->assertTrue( Claims::holds( $order_id, $discount->id ) );
	}

	public function test_store_completing_an_order_without_a_use_left_notes_it() {
		$discount = $this->create_used_up_discount();
		$order_id = $this->order_without_claim( $discount );

		$this->complete_as_store_admin( $order_id );

		$this->assertContains(
			sprintf( 'Discount %s was redeemed past its maximum uses (2/1).', $discount->code ),
			wp_list_pluck( edd_get_notes( array( 'object_id' => $order_id, 'object_type' => 'order' ) ), 'content' )
		);
	}

	public function test_store_completing_an_order_without_a_use_left_announces_the_discount_past_its_limit() {
		$discount   = $this->create_used_up_discount();
		$order_id   = $this->order_without_claim( $discount );
		$past_limit = array();
		$listener   = function ( $order, $discounts ) use ( &$past_limit ) {
			$past_limit = wp_list_pluck( $discounts, 'id' );
		};
		add_action( 'edd/discounts/redeemed_past_limit', $listener, 10, 2 );

		$this->complete_as_store_admin( $order_id );

		remove_action( 'edd/discounts/redeemed_past_limit', $listener, 10 );
		$this->assertSame( array( $discount->id ), $past_limit );
	}

	public function test_recompleting_an_order_counted_past_the_limit_notes_it_once() {
		$discount = $this->create_used_up_discount();
		$order_id = $this->order_without_claim( $discount );
		$this->complete_as_store_admin( $order_id );
		edd_update_order_status( $order_id, 'revoked' );

		edd_update_order_status( $order_id, 'complete' );

		$notes = wp_list_pluck( edd_get_notes( array( 'object_id' => $order_id, 'object_type' => 'order' ) ), 'content' );
		$this->assertCount( 1, array_keys( $notes, sprintf( 'Discount %s was redeemed past its maximum uses (2/1).', $discount->code ), true ) );
	}

	public function test_recompleting_an_order_counted_past_the_limit_counts_it_once() {
		$discount = $this->create_used_up_discount();
		$order_id = $this->order_without_claim( $discount );
		$this->complete_as_store_admin( $order_id );
		edd_update_order_status( $order_id, 'revoked' );

		edd_update_order_status( $order_id, 'complete' );

		$this->assertSame( 2, $this->get_use_count( $discount ) );
	}

	public function test_completing_an_order_that_holds_its_use_announces_nothing_past_the_limit() {
		$discount  = $this->create_discount( array( 'max_uses' => 1 ) );
		$order_id  = $this->order_without_claim( $discount );
		$announced = did_action( 'edd/discounts/redeemed_past_limit' );
		Claims::claim( edd_get_order( $order_id ) );

		edd_update_order_status( $order_id, 'complete' );

		$this->assertSame( $announced, did_action( 'edd/discounts/redeemed_past_limit' ) );
	}

	public function test_deleting_an_order_counted_past_the_limit_gives_back_only_its_use() {
		$discount = $this->create_used_up_discount();
		$order_id = $this->order_without_claim( $discount );
		$this->complete_as_store_admin( $order_id );

		$this->assertSame( 2, $this->get_use_count( $discount ) );

		edd_delete_order( $order_id );

		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_deleting_an_order_counted_past_the_limit_keeps_the_code_inactive() {
		$discount = $this->create_used_up_discount();
		$order_id = $this->order_without_claim( $discount );
		$this->complete_as_store_admin( $order_id );

		$this->assertSame( 2, $this->get_use_count( $discount ) );

		edd_delete_order( $order_id );

		$this->assertSame( 'inactive', edd_get_discount( $discount->id )->status );
	}

	public function test_deleting_a_pending_order_gives_back_its_use() {
		$discount = $this->create_discount();
		$order_id = $this->build_order( $discount );

		edd_destroy_order( $order_id );

		$this->assertSame( 0, $this->get_use_count( $discount ) );
	}

	public function test_deleting_an_order_that_holds_no_use_leaves_the_count() {
		$discount = $this->create_discount( array( 'use_count' => 2 ) );
		$order_id = $this->order_without_claim( $discount );

		edd_destroy_order( $order_id );

		$this->assertSame( 2, $this->get_use_count( $discount ) );
	}

	public function test_failed_delete_of_an_order_that_held_no_use_leaves_the_count() {
		global $wpdb;

		$discount = $this->create_discount();
		$order_id = $this->build_order( $discount );
		edd_trash_order( $order_id );

		$fail_order_delete = function ( $query ) use ( $wpdb ) {
			return 0 === strpos( $query, "DELETE FROM `{$wpdb->edd_orders}`" ) ? $query . ' AND 1 = 0' : $query;
		};
		add_filter( 'query', $fail_order_delete );
		$deleted = edd_delete_order( $order_id );
		remove_filter( 'query', $fail_order_delete );

		$this->assertFalse( $deleted );
		$this->assertSame( 0, $this->get_use_count( $discount ) );
		$this->assertFalse( Claims::holds( $order_id, $discount->id ) );
	}

	public function test_trashing_a_pending_order_gives_back_its_use() {
		$discount = $this->create_discount();
		$order_id = $this->build_order( $discount );

		edd_trash_order( $order_id );

		$this->assertSame( 'trash', edd_get_order( $order_id )->status );
		$this->assertSame( 0, $this->get_use_count( $discount ) );
	}

	public function test_trashing_a_completed_order_keeps_its_use() {
		$discount = $this->create_discount();
		$order_id = $this->build_order( $discount );
		edd_update_order_status( $order_id, 'complete' );

		edd_trash_order( $order_id );

		$this->assertSame( 'trash', edd_get_order( $order_id )->status );
		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_resuming_an_order_with_the_same_discount_keeps_one_use() {
		$discount = $this->create_discount();
		$order_id = $this->build_order( $discount );
		EDD()->session->set( 'edd_resume_payment', $order_id );

		$resumed_id = $this->build_order( $discount );

		$this->assertSame( $order_id, $resumed_id );
		$this->assertSame( 1, $this->get_use_count( $discount ) );
		$this->assertTrue( Claims::holds( $order_id, $discount->id ) );
	}

	public function test_resuming_an_order_without_its_discount_gives_back_the_use() {
		$discount = $this->create_discount();
		$order_id = $this->build_order( $discount );
		EDD()->session->set( 'edd_resume_payment', $order_id );

		$resumed_id = $this->build_order( null );

		$this->assertSame( $order_id, $resumed_id );
		$this->assertEmpty( edd_get_order( $order_id )->get_discounts() );
		$this->assertSame( 0, $this->get_use_count( $discount ) );
	}

	public function test_giving_back_a_use_keeps_a_code_switched_off_below_its_cap_inactive() {
		$discount = $this->create_discount( array( 'max_uses' => 5 ) );
		$order_id = $this->build_order( $discount );
		edd_update_discount( $discount->id, array( 'status' => 'inactive' ) );

		edd_update_order_status( $order_id, 'failed' );

		$discount = edd_get_discount( $discount->id );
		$this->assertSame( 0, (int) $discount->use_count );
		$this->assertSame( 'inactive', $discount->status );
	}

	public function test_giving_back_a_use_keeps_a_code_over_its_cap_inactive() {
		$discount = $this->create_discount(
			array(
				'max_uses'  => 1,
				'use_count' => 3,
				'status'    => 'inactive',
			)
		);
		$this->assertSame( 'inactive', edd_get_discount( $discount->id )->status );

		edd_get_discount( $discount->id )->decrease_usage();

		$discount = edd_get_discount( $discount->id );
		$this->assertSame( 2, (int) $discount->use_count );
		$this->assertSame( 'inactive', $discount->status );
	}

	public function test_giving_back_the_use_at_the_cap_reactivates_the_code() {
		$discount = $this->create_discount(
			array(
				'max_uses'  => 1,
				'use_count' => 1,
				'status'    => 'inactive',
			)
		);
		$this->assertSame( 'inactive', edd_get_discount( $discount->id )->status );

		edd_get_discount( $discount->id )->decrease_usage();

		$discount = edd_get_discount( $discount->id );
		$this->assertSame( 0, (int) $discount->use_count );
		$this->assertSame( 'active', $discount->status );
	}

	public function test_giving_back_the_use_at_the_cap_keeps_a_code_switched_off_on_purpose_inactive() {
		$discount = $this->create_discount( array( 'max_uses' => 2 ) );
		$order_id = $this->build_order( $discount );
		$this->switch_off_at_its_cap( $discount );

		edd_update_order_status( $order_id, 'failed' );

		$discount = edd_get_discount( $discount->id );
		$this->assertSame( 0, (int) $discount->use_count );
		$this->assertSame( 'inactive', $discount->status );
	}

	public function test_order_holding_the_last_use_of_a_code_switched_off_on_purpose_can_still_apply_it() {
		$discount = $this->create_discount( array( 'max_uses' => 2 ) );
		$order_id = $this->build_order( $discount );
		$this->switch_off_at_its_cap( $discount );

		$this->assertTrue( edd_get_discount( $discount->id )->is_available_to_order( $order_id, false ) );
	}

	public function test_code_switched_off_on_purpose_keeps_the_use_held_by_a_pending_order_over_an_hour_old() {
		$discount  = $this->create_discount( array( 'max_uses' => 2 ) );
		$abandoned = $this->build_abandoned_order( $discount );
		$this->switch_off_at_its_cap( $discount );

		$this->assertFalse( $this->apply_code( $discount ) );

		$this->assertTrue( Claims::holds( $abandoned, $discount->id ) );
		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_sweep_gives_back_uses_of_paypal_orders_pending_over_an_hour() {
		$discount = $this->create_discount();
		$order_id = $this->build_order( $discount );
		$this->create_at_paypal( $order_id );
		$this->age_order( $order_id );

		( new OrdersCron() )->release_expired_discount_claims();

		$this->assertSame( 'pending', edd_get_order( $order_id )->status );
		$this->assertSame( 0, $this->get_use_count( $discount ) );
	}

	public function test_sweep_keeps_uses_of_recent_paypal_orders() {
		$discount = $this->create_discount();
		$this->create_at_paypal( $this->build_order( $discount ) );

		( new OrdersCron() )->release_expired_discount_claims();

		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_sweep_keeps_uses_of_paypal_orders_already_paid() {
		$discount = $this->create_discount();
		$order_id = $this->build_order( $discount );
		$this->create_at_paypal( $order_id );
		edd_add_order_transaction(
			array(
				'object_id'      => $order_id,
				'object_type'    => 'order',
				'transaction_id' => 'CAPTURE-1',
				'gateway'        => 'paypal_commerce',
				'status'         => 'complete',
				'total'          => 20,
			)
		);
		$this->age_order( $order_id );

		( new OrdersCron() )->release_expired_discount_claims();

		$this->assertSame( 1, $this->get_use_count( $discount ) );
		$this->assertTrue( Claims::holds( $order_id, $discount->id ) );
	}

	public function test_sweep_keeps_uses_of_paypal_orders_never_created_at_paypal() {
		$discount = $this->create_discount();
		$order_id = $this->build_order( $discount );
		$this->age_order( $order_id );

		( new OrdersCron() )->release_expired_discount_claims();

		$this->assertSame( 1, $this->get_use_count( $discount ) );
		$this->assertTrue( Claims::holds( $order_id, $discount->id ) );
	}

	public function test_sweep_keeps_uses_of_orders_on_other_gateways() {
		$discount = $this->create_discount();
		$order_id = $this->build_order( $discount, 'paypal' );
		$this->create_at_paypal( $order_id );
		$this->age_order( $order_id );

		( new OrdersCron() )->release_expired_discount_claims();

		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_full_code_is_available_when_its_use_is_held_by_a_pending_order_over_an_hour_old() {
		$discount  = $this->create_discount( array( 'max_uses' => 1 ) );
		$abandoned = $this->build_abandoned_order( $discount );
		$discount  = edd_get_discount( $discount->id );
		$this->assertSame( 'inactive', $discount->status );

		$this->assertTrue( $this->apply_code( $discount ) );

		$this->assertFalse( Claims::holds( $abandoned, $discount->id ) );
		$this->assertSame( 'active', edd_get_discount( $discount->id )->status );
		$this->assertSame( 0, $this->get_use_count( $discount ) );
	}

	public function test_full_code_stays_unavailable_when_its_use_is_held_by_a_pending_order_under_an_hour_old() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order_id = $this->build_order( $discount );
		EDD()->session->set( 'edd_resume_payment', null );

		$this->assertFalse( $this->apply_code( $discount ) );

		$this->assertTrue( Claims::holds( $order_id, $discount->id ) );
		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_full_code_stays_unavailable_when_its_use_is_held_by_a_pending_order_another_gateway_can_still_complete() {
		$discount  = $this->create_discount( array( 'max_uses' => 1 ) );
		$abandoned = $this->build_abandoned_order( $discount, 'abandoned', 'paypal' );

		$this->assertFalse( $this->apply_code( $discount ) );

		$this->assertTrue( Claims::holds( $abandoned, $discount->id ) );
		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_full_code_stays_unavailable_when_its_use_is_held_by_a_pending_order_paypal_already_captured() {
		$discount  = $this->create_discount( array( 'max_uses' => 1 ) );
		$abandoned = $this->build_abandoned_order( $discount );
		edd_add_order_transaction(
			array(
				'object_id'      => $abandoned,
				'object_type'    => 'order',
				'transaction_id' => 'CAPTURE-1',
				'gateway'        => 'paypal_commerce',
				'status'         => 'complete',
				'total'          => 20,
			)
		);

		$this->assertFalse( $this->apply_code( $discount ) );

		$this->assertTrue( Claims::holds( $abandoned, $discount->id ) );
		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_full_code_stays_unavailable_when_its_use_is_held_by_a_completed_order() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order_id = $this->build_abandoned_order( $discount );
		edd_update_order_status( $order_id, 'complete' );

		$this->assertFalse( $this->apply_code( $discount ) );

		$this->assertTrue( Claims::holds( $order_id, $discount->id ) );
		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_expired_full_code_keeps_the_use_held_by_a_pending_order_over_an_hour_old() {
		$discount  = $this->create_discount( array( 'max_uses' => 1 ) );
		$abandoned = $this->build_abandoned_order( $discount );
		$this->set_discount_dates( $discount, array( 'end_date' => '2020-01-01 00:00:00' ) );

		$this->assertFalse( $this->apply_code( $discount ) );

		$this->assertTrue( Claims::holds( $abandoned, $discount->id ) );
		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_expired_code_under_the_minimum_is_marked_expired() {
		$discount = $this->expired_code_with_unmet_minimum();

		$this->assertFalse( $this->apply_code( $discount ) );

		$this->assertSame( 'expired', edd_get_discount( $discount->id )->status );
	}

	public function test_expired_code_under_the_minimum_is_told_it_expired() {
		add_filter( 'wp_doing_ajax', '__return_true' );
		$discount = $this->expired_code_with_unmet_minimum();
		edd_add_to_cart( \EDD\Tests\Helpers\EDD_Helper_Download::create_simple_download()->ID );
		edd_clear_errors();

		$this->assertFalse( edd_is_discount_valid( $discount->code, '', true ) );
		$this->assertSame( 'This discount is expired.', edd_get_errors()['edd-discount-error'] ?? '' );

		edd_clear_errors();
		remove_filter( 'wp_doing_ajax', '__return_true' );
	}

	public function test_full_code_not_yet_started_keeps_the_use_held_by_a_pending_order_over_an_hour_old() {
		$discount  = $this->create_discount( array( 'max_uses' => 1 ) );
		$abandoned = $this->build_abandoned_order( $discount );
		$this->set_discount_dates( $discount, array( 'start_date' => '2049-01-01 00:00:00' ) );

		$this->assertFalse( $this->apply_code( $discount ) );

		$this->assertTrue( Claims::holds( $abandoned, $discount->id ) );
		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_full_code_gives_back_only_the_one_stale_use_it_needs() {
		$discount     = $this->create_discount( array( 'max_uses' => 2 ) );
		$first_order  = $this->build_abandoned_order( $discount, 'first' );
		$second_order = $this->build_abandoned_order( $discount, 'second' );
		$this->assertSame( 2, $this->get_use_count( $discount ) );

		$this->assertTrue( $this->apply_code( $discount ) );

		$this->assertSame( 1, $this->get_use_count( $discount ) );
		$this->assertCount( 1, Claims::get_holders( $discount->id, array( $first_order, $second_order ) ) );
	}

	public function test_checking_whether_a_full_code_is_available_gives_back_no_stale_use() {
		$discount  = $this->create_discount( array( 'max_uses' => 1 ) );
		$abandoned = $this->build_abandoned_order( $discount );

		$this->assertFalse( edd_get_discount( $discount->id )->is_available_to_order( 0, false ) );

		$this->assertTrue( Claims::holds( $abandoned, $discount->id ) );
		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_shopper_under_the_minimum_gives_back_no_stale_use() {
		$discount  = $this->create_discount(
			array(
				'max_uses'          => 1,
				'min_charge_amount' => 1000,
			)
		);
		$abandoned = $this->build_abandoned_order( $discount );

		$this->assertFalse( $this->apply_code( $discount ) );

		$this->assertTrue( Claims::holds( $abandoned, $discount->id ) );
		$this->assertSame( 'inactive', edd_get_discount( $discount->id )->status );
	}

	public function test_code_over_its_cap_gives_back_no_stale_use() {
		$discount     = $this->create_discount( array( 'max_uses' => 2 ) );
		$first_order  = $this->build_abandoned_order( $discount, 'first' );
		$second_order = $this->build_abandoned_order( $discount, 'second' );
		edd_update_discount( $discount->id, array( 'max_uses' => 1 ) );

		$this->assertFalse( $this->apply_code( $discount ) );

		$this->assertSame( 2, $this->get_use_count( $discount ) );
		$this->assertCount( 2, Claims::get_holders( $discount->id, array( $first_order, $second_order ) ) );
	}

	public function test_building_an_order_for_an_expired_full_code_keeps_the_stale_use() {
		$discount  = $this->create_discount( array( 'max_uses' => 1 ) );
		$abandoned = $this->build_abandoned_order( $discount );
		$this->set_discount_dates( $discount, array( 'end_date' => '2020-01-01 00:00:00' ) );

		$order_id = $this->build_order( $discount, 'stripe', 'second' );

		$this->assertFalse( Claims::holds( $order_id, $discount->id ) );
		$this->assertTrue( Claims::holds( $abandoned, $discount->id ) );
		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_building_an_order_for_a_code_switched_off_on_purpose_keeps_the_stale_use() {
		$discount  = $this->create_discount( array( 'max_uses' => 2 ) );
		$abandoned = $this->build_abandoned_order( $discount );
		$this->switch_off_at_its_cap( $discount );

		$order_id = $this->build_order( $discount, 'stripe', 'second' );

		$this->assertFalse( Claims::holds( $order_id, $discount->id ) );
		$this->assertTrue( Claims::holds( $abandoned, $discount->id ) );
		$this->assertSame( 'inactive', edd_get_discount( $discount->id )->status );
	}

	public function test_building_an_order_over_a_full_cap_takes_a_stale_use() {
		$discount  = $this->create_discount( array( 'max_uses' => 1 ) );
		$abandoned = $this->build_abandoned_order( $discount );

		$order_id = $this->build_order( $discount, 'stripe', 'second' );

		$this->assertTrue( Claims::holds( $order_id, $discount->id ) );
		$this->assertFalse( Claims::holds( $abandoned, $discount->id ) );
		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_completing_an_order_over_a_full_cap_takes_a_stale_use_instead_of_counting_past_the_limit() {
		$discount  = $this->create_discount( array( 'max_uses' => 1 ) );
		$abandoned = $this->build_abandoned_order( $discount );
		$paid      = $this->order_without_claim( $discount );
		$announced = did_action( 'edd/discounts/redeemed_past_limit' );

		edd_update_order_status( $paid, 'complete' );

		$this->assertTrue( Claims::holds( $paid, $discount->id ) );
		$this->assertFalse( Claims::holds( $abandoned, $discount->id ) );
		$this->assertSame( 1, $this->get_use_count( $discount ) );
		$this->assertSame( $announced, did_action( 'edd/discounts/redeemed_past_limit' ) );
	}

	public function test_order_holding_the_last_use_can_still_apply_the_discount() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order_id = $this->build_order( $discount );
		$discount = edd_get_discount( $discount->id );
		$this->assertSame( 'inactive', $discount->status );

		$this->assertTrue( $discount->is_available_to_order( $order_id, false ) );
		$this->assertFalse( $discount->is_available_to_order( 0, false ) );
	}

	public function test_order_holding_the_last_use_of_an_expired_discount_is_told_it_expired() {
		add_filter( 'wp_doing_ajax', '__return_true' );
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order_id = $this->build_order( $discount );
		edd_update_discount( $discount->id, array( 'end_date' => '2020-01-01 00:00:00' ) );
		$discount = edd_get_discount( $discount->id );
		$this->assertSame( 'inactive', $discount->status );
		$this->assertTrue( edd_doing_ajax() );
		edd_clear_errors();

		$this->assertFalse( $discount->is_available_to_order( $order_id ) );
		$this->assertSame( 'This discount is expired.', edd_get_errors()['edd-discount-error'] ?? '' );
		edd_clear_errors();
	}

	public function test_order_holding_the_last_use_is_refused_when_filtered_inactive() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order_id = $this->build_order( $discount );
		$discount = edd_get_discount( $discount->id );
		$this->assertTrue( $discount->is_available_to_order( $order_id, false ) );

		add_filter( 'edd_is_discount_active', '__return_false' );

		$this->assertFalse( $discount->is_available_to_order( $order_id, false ) );
	}

	public function test_resumed_checkout_holding_the_last_use_keeps_the_discount_valid() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order_id = $this->build_order( $discount );
		edd_add_to_cart( \EDD\Tests\Helpers\EDD_Helper_Download::create_simple_download()->ID );

		$this->assertFalse( edd_is_discount_valid( $discount->code, self::EMAIL, false ) );

		EDD()->session->set( 'edd_resume_payment', $order_id );

		$this->assertTrue( edd_is_discount_valid( $discount->code, self::EMAIL, false ) );
	}

	public function test_resumed_checkout_in_a_filtered_recoverable_status_keeps_the_discount_valid() {
		add_filter(
			'edd_recoverable_payment_statuses',
			function ( $statuses ) {
				return array_merge( $statuses, array( 'processing' ) );
			}
		);
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order_id = $this->build_order( $discount );
		edd_update_order( $order_id, array( 'status' => 'processing' ) );
		edd_add_to_cart( \EDD\Tests\Helpers\EDD_Helper_Download::create_simple_download()->ID );
		$this->assertSame( 'processing', edd_get_order( $order_id )->status );
		$this->assertTrue( Claims::holds( $order_id, $discount->id ) );

		EDD()->session->set( 'edd_resume_payment', $order_id );

		$this->assertTrue( edd_is_discount_valid( $discount->code, self::EMAIL, false ) );
	}

	public function test_once_per_customer_code_held_by_a_pending_order_counts_as_used() {
		$discount = $this->create_discount( array( 'once_per_customer' => 1 ) );
		$this->build_order( $discount );

		$this->assertTrue( $discount->is_used( self::EMAIL, false ) );
	}

	public function test_once_per_customer_code_held_by_the_order_being_checked_is_not_used() {
		$discount = $this->create_discount( array( 'once_per_customer' => 1 ) );
		$order_id = $this->build_order( $discount );

		$this->assertFalse( $discount->is_used( self::EMAIL, false, $order_id ) );
	}

	public function test_once_per_customer_code_held_by_the_resumed_checkout_is_not_used() {
		$discount = $this->create_discount( array( 'once_per_customer' => 1 ) );
		$order_id = $this->build_order( $discount );
		EDD()->session->set( 'edd_resume_payment', $order_id );

		$this->assertFalse( $discount->is_used( self::EMAIL, false ) );
	}

	public function test_once_per_customer_code_counts_only_the_earlier_of_two_holders() {
		$discount       = $this->create_discount( array( 'once_per_customer' => 1 ) );
		$earlier_order  = $this->build_order( $discount );
		$later_order    = $this->build_order( $discount, 'paypal_commerce', 'second' );

		$this->assertTrue( Claims::holds( $later_order, $discount->id ) );
		$this->assertFalse( $discount->is_used( self::EMAIL, false, $earlier_order ) );
		$this->assertTrue( $discount->is_used( self::EMAIL, false, $later_order ) );
	}

	public function test_trashing_an_order_on_hold_keeps_its_use() {
		$discount = $this->create_discount();
		$order_id = $this->build_order( $discount );
		edd_update_order_status( $order_id, 'on_hold' );

		edd_trash_order( $order_id );

		$this->assertSame( 'trash', edd_get_order( $order_id )->status );
		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_deleting_an_order_completed_before_claims_gives_back_its_use() {
		$discount = $this->create_discount( array( 'use_count' => 1 ) );
		$order_id = $this->order_completed_before_claims( $discount );

		edd_destroy_order( $order_id );

		$this->assertSame( 0, $this->get_use_count( $discount ) );
	}

	public function test_trashing_then_deleting_an_order_completed_before_claims_gives_back_its_use_once() {
		$discount = $this->create_discount(
			array(
				'max_uses'  => 1,
				'use_count' => 1,
				'status'    => 'inactive',
			)
		);
		$order_id = $this->order_completed_before_claims( $discount );
		edd_trash_order( $order_id );
		$this->assertSame( 'trash', edd_get_order( $order_id )->status );
		$this->assertSame( 1, $this->get_use_count( $discount ) );

		edd_destroy_order( $order_id );

		$this->assertSame( 0, $this->get_use_count( $discount ) );
		$this->assertSame( 'active', edd_get_discount( $discount->id )->status );
	}

	public function test_deleting_an_order_completed_before_claims_without_a_completed_date_gives_back_its_use() {
		$discount = $this->create_discount( array( 'use_count' => 1 ) );
		$order_id = $this->order_completed_before_claims( $discount );
		global $wpdb;
		$wpdb->update(
			$wpdb->edd_orders,
			array(
				'date_created'   => '2020-01-01 00:00:00',
				'date_completed' => null,
			),
			array( 'id' => $order_id )
		);
		( new \EDD\Database\Queries\Order() )->refresh_item_cache( $order_id );
		$this->assertEmpty( edd_get_order( $order_id )->date_completed );
		$this->assertSame( 1, $this->get_use_count( $discount ) );

		edd_destroy_order( $order_id );

		$this->assertSame( 0, $this->get_use_count( $discount ) );
	}

	/**
	 * @dataProvider provider_counted_statuses_after_complete
	 */
	public function test_recompleting_an_order_imported_complete_before_claims_counts_one_use( $status ) {
		$discount = $this->create_discount( array( 'use_count' => 1 ) );
		$order_id = $this->order_imported_complete_before_claims( $discount );
		edd_update_order_status( $order_id, $status );
		$this->assertSame( $status, edd_get_order( $order_id )->status );
		$this->assertSame( 1, $this->get_use_count( $discount ) );

		edd_update_order_status( $order_id, 'complete' );

		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_failing_an_order_imported_complete_before_claims_after_revoking_gives_back_its_use() {
		$discount = $this->create_discount( array( 'use_count' => 3 ) );
		$order_id = $this->order_imported_complete_before_claims( $discount );
		edd_update_order_status( $order_id, 'revoked' );
		$this->assertSame( 'revoked', edd_get_order( $order_id )->status );
		$this->assertSame( 3, $this->get_use_count( $discount ) );

		edd_update_order_status( $order_id, 'failed' );

		$this->assertSame( 2, $this->get_use_count( $discount ) );
	}

	public function test_failing_an_order_completed_before_claims_gives_back_its_use_once() {
		$discount = $this->create_discount( array( 'use_count' => 1 ) );
		$order_id = $this->order_completed_before_claims( $discount );

		edd_update_order_status( $order_id, 'failed' );
		edd_update_order_status( $order_id, 'cancelled' );

		$this->assertSame( 0, $this->get_use_count( $discount ) );
	}

	public function test_recompleting_an_order_completed_before_claims_counts_one_use() {
		$discount = $this->create_discount( array( 'use_count' => 1 ) );
		$order_id = $this->order_completed_before_claims( $discount );
		edd_update_order_status( $order_id, 'failed' );

		edd_update_order_status( $order_id, 'complete' );

		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_completing_an_order_completed_before_claims_again_counts_no_new_use() {
		$discount = $this->create_discount( array( 'use_count' => 1 ) );
		$order_id = $this->order_completed_before_claims( $discount );
		edd_update_order_status( $order_id, 'revoked' );

		edd_update_order_status( $order_id, 'complete' );

		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_restoring_an_order_completed_before_claims_from_the_trash_counts_no_new_use() {
		$discount = $this->create_discount( array( 'use_count' => 1 ) );
		$order_id = $this->order_completed_before_claims( $discount );
		edd_trash_order( $order_id );
		$this->assertSame( 'trash', edd_get_order( $order_id )->status );

		edd_restore_order( $order_id );

		$this->assertSame( 'complete', edd_get_order( $order_id )->status );
		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_completing_an_order_left_pending_before_claims_counts_its_use() {
		$discount = $this->create_discount( array( 'max_uses' => 5 ) );
		$order_id = $this->pending_order_placed_before_claims( $discount );

		edd_update_order_status( $order_id, 'complete' );

		$this->assertSame( 1, $this->get_use_count( $discount ) );
		$this->assertTrue( Claims::holds( $order_id, $discount->id ) );
	}

	public function test_store_completing_an_order_left_pending_before_claims_without_a_use_left_notes_it() {
		$discount = $this->create_discount(
			array(
				'max_uses'  => 1,
				'use_count' => 1,
			)
		);
		$order_id = $this->pending_order_placed_before_claims( $discount );

		$this->complete_as_store_admin( $order_id );

		$this->assertContains(
			sprintf( 'Discount %s was redeemed past its maximum uses (2/1).', $discount->code ),
			wp_list_pluck( edd_get_notes( array( 'object_id' => $order_id, 'object_type' => 'order' ) ), 'content' )
		);
	}

	public function test_failing_an_order_put_on_hold_before_claims_without_completing_leaves_the_count() {
		$discount = $this->create_discount(
			array(
				'max_uses'  => 1,
				'use_count' => 1,
				'status'    => 'inactive',
			)
		);
		$order_id = $this->on_hold_order_placed_before_claims( $discount );

		edd_update_order_status( $order_id, 'failed' );

		$discount = edd_get_discount( $discount->id );
		$this->assertSame( 1, (int) $discount->use_count );
		$this->assertSame( 'inactive', $discount->status );
	}

	public function test_completing_an_order_put_on_hold_before_claims_counts_its_use() {
		$discount = $this->create_discount( array( 'max_uses' => 5 ) );
		$order_id = $this->on_hold_order_placed_before_claims( $discount );

		edd_update_order_status( $order_id, 'complete' );

		$this->assertSame( 1, $this->get_use_count( $discount ) );
		$this->assertTrue( Claims::holds( $order_id, $discount->id ) );
	}

	public function test_deleting_an_order_that_failed_before_claims_leaves_the_count() {
		global $wpdb;

		$discount = $this->create_discount( array( 'use_count' => 1 ) );
		$order_id = $this->order_completed_before_claims( $discount );
		// Fail the order as it was failed before claims, without the status change running today's release.
		$wpdb->update( $wpdb->edd_orders, array( 'status' => 'failed' ), array( 'id' => $order_id ) );
		( new \EDD\Database\Queries\Order() )->refresh_item_cache( $order_id );
		$this->assertSame( 'failed', edd_get_order( $order_id )->status );

		edd_destroy_order( $order_id );

		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_deleting_an_order_that_completed_without_a_use_leaves_the_count() {
		$discount = $this->create_discount(
			array(
				'max_uses'  => 1,
				'use_count' => 1,
			)
		);
		$order_id = $this->order_without_claim( $discount );
		edd_update_order_status( $order_id, 'complete' );

		edd_destroy_order( $order_id );

		$this->assertSame( 1, $this->get_use_count( $discount ) );
	}

	public function test_each_test_starts_with_no_cached_claims() {
		$held = new \ReflectionProperty( Claims::class, 'held' );
		$held->setAccessible( true );

		$this->assertSame( array(), $held->getValue() );
	}

	/**
	 * Statuses that end a pending order without payment.
	 *
	 * @return array
	 */
	public function provider_release_statuses() {
		return array(
			'failed'    => array( 'failed' ),
			'abandoned' => array( 'abandoned' ),
			'cancelled' => array( 'cancelled' ),
		);
	}

	/**
	 * Counted statuses an order can move to from complete.
	 *
	 * @return array
	 */
	public function provider_counted_statuses_after_complete() {
		return array(
			'revoked'            => array( 'revoked' ),
			'refunded'           => array( 'refunded' ),
			'partially_refunded' => array( 'partially_refunded' ),
			'on_hold'            => array( 'on_hold' ),
		);
	}

	/**
	 * Builds a pending order through the checkout's order builder.
	 *
	 * @param \EDD_Discount|null $discount The discount to apply, if any.
	 * @param string             $gateway  The gateway.
	 * @param string             $key      Distinguishes the purchase key.
	 * @return int The order ID.
	 */
	private function build_order( $discount, $gateway = 'paypal_commerce', $key = 'first' ) {
		$order_id = edd_build_order(
			array(
				'price'        => 10.0,
				'user_email'   => self::EMAIL,
				'purchase_key' => md5( $key . wp_rand() ),
				'currency'     => 'USD',
				'downloads'    => array(
					array(
						'id'       => 1,
						'quantity' => 1,
					),
				),
				'user_info'    => array(
					'id'         => 0,
					'email'      => self::EMAIL,
					'first_name' => 'Claim',
					'last_name'  => 'Tester',
					'discount'   => $discount ? $discount->code : 'none',
					'address'    => array(),
				),
				'cart_details' => array(
					array(
						'name'        => 'Test Download',
						'id'          => 1,
						'item_number' => array(
							'id'       => 1,
							'quantity' => 1,
						),
						'item_price'  => 20.0,
						'quantity'    => 1,
						'discount'    => 10.0,
						'subtotal'    => 20.0,
						'tax'         => 0.0,
						'fees'        => array(),
						'price'       => 10.0,
					),
				),
				'gateway'      => $gateway,
				'status'       => 'pending',
			)
		);

		$this->assertNotEmpty( $order_id );

		return (int) $order_id;
	}

	/**
	 * Creates a pending order carrying a discount without claiming a use, as orders placed before claims did.
	 *
	 * @param \EDD_Discount $discount The discount.
	 * @return int The order ID.
	 */
	private function order_without_claim( $discount ) {
		return $this->create_order_with_discount(
			$discount,
			array(
				'status' => 'pending',
				'email'  => self::EMAIL,
			)
		)->id;
	}

	/**
	 * Records the PayPal order ID that PayPal returns when an order is created there.
	 *
	 * @param int $order_id The order ID.
	 * @return void
	 */
	private function create_at_paypal( $order_id ) {
		edd_update_order_meta( $order_id, 'paypal_order_id', 'PAYPAL-' . $order_id );
	}

	/**
	 * Moves an order's creation date back past the claim window.
	 *
	 * @param int $order_id The order ID.
	 * @return void
	 */
	private function age_order( $order_id ) {
		edd_update_order( $order_id, array( 'date_created' => gmdate( 'Y-m-d H:i:s', time() - 2 * HOUR_IN_SECONDS ) ) );

		$this->assertLessThan( time() - HOUR_IN_SECONDS, strtotime( edd_get_order( $order_id )->date_created ) );
	}

	/**
	 * Sets a discount inactive from the admin, then lowers its cap to the uses already counted.
	 *
	 * @param \EDD_Discount $discount The discount.
	 * @return void
	 */
	private function switch_off_at_its_cap( $discount ) {
		edd_update_discount_status( $discount->id, 'inactive' );
		edd_update_discount( $discount->id, array( 'max_uses' => $this->get_use_count( $discount ) ) );

		$discount = edd_get_discount( $discount->id );
		$this->assertSame( 'inactive', $discount->status );
		$this->assertSame( (int) $discount->use_count, (int) $discount->max_uses );
		$this->assertTrue( \EDD\Discounts\ManualDeactivation::is_manual( $discount->id ) );
	}

	/**
	 * Changes a discount's dates without touching its use count or status.
	 *
	 * @param \EDD_Discount $discount The discount.
	 * @param array         $dates    The start_date and/or end_date to set.
	 * @return void
	 */
	private function set_discount_dates( $discount, $dates ) {
		global $wpdb;

		$wpdb->update( $wpdb->edd_adjustments, $dates, array( 'id' => $discount->id ) );
		( new \EDD\Database\Queries\Adjustment() )->refresh_item_cache( $discount->id );

		$this->assertSame( 'inactive', edd_get_discount( $discount->id )->status );
	}

	/**
	 * Creates a code still stored as active after its end date, with a minimum no test cart meets.
	 *
	 * @return \EDD_Discount
	 */
	private function expired_code_with_unmet_minimum() {
		global $wpdb;

		$discount = $this->create_discount( array( 'min_charge_amount' => 1000 ) );
		$wpdb->update( $wpdb->edd_adjustments, array( 'end_date' => '2020-01-01 00:00:00' ), array( 'id' => $discount->id ) );
		( new \EDD\Database\Queries\Adjustment() )->refresh_item_cache( $discount->id );

		$this->assertSame( 'active', edd_get_discount( $discount->id )->status );

		return $discount;
	}

	/**
	 * Builds an order created at PayPal that claims a use and is left unpaid past the claim window, outside the shopper's session.
	 *
	 * @param \EDD_Discount $discount The discount.
	 * @param string        $key      Distinguishes the purchase key.
	 * @param string        $gateway  The gateway.
	 * @return int The order ID.
	 */
	private function build_abandoned_order( $discount, $key = 'abandoned', $gateway = 'paypal_commerce' ) {
		$order_id = $this->build_order( $discount, $gateway, $key );
		$this->create_at_paypal( $order_id );
		$this->age_order( $order_id );
		// Another shopper's checkout, so the next build starts a new order instead of resuming this one.
		EDD()->session->set( 'edd_resume_payment', null );

		$this->assertTrue( Claims::holds( $order_id, $discount->id ) );

		return $order_id;
	}

	/**
	 * Creates an order completed and counted before claims were tracked.
	 *
	 * @param \EDD_Discount $discount The discount.
	 * @return int The order ID.
	 */
	private function order_completed_before_claims( $discount ) {
		global $wpdb;

		update_option( Claims::STARTED_OPTION, time() - DAY_IN_SECONDS );

		$order_id = $this->order_without_claim( $discount );
		// Complete the order as it was completed before claims, without the status change claiming a use today.
		$wpdb->update(
			$wpdb->edd_orders,
			array(
				'status'         => 'complete',
				'date_completed' => '2020-01-01 00:00:00',
			),
			array( 'id' => $order_id )
		);
		( new \EDD\Database\Queries\Order() )->refresh_item_cache( $order_id );

		$this->assertSame( 'complete', edd_get_order( $order_id )->status );
		$this->assertFalse( Claims::holds( $order_id, $discount->id ) );

		return $order_id;
	}

	/**
	 * Creates an order imported as complete before claims were tracked, counted but with no completed date.
	 *
	 * @param \EDD_Discount $discount The discount.
	 * @return int The order ID.
	 */
	private function order_imported_complete_before_claims( $discount ) {
		global $wpdb;

		$order_id = $this->order_completed_before_claims( $discount );
		$wpdb->update(
			$wpdb->edd_orders,
			array(
				'date_created'   => '2020-01-01 00:00:00',
				'date_completed' => null,
			),
			array( 'id' => $order_id )
		);
		( new \EDD\Database\Queries\Order() )->refresh_item_cache( $order_id );

		$this->assertEmpty( edd_get_order( $order_id )->date_completed );

		return $order_id;
	}

	/**
	 * Creates an order placed before claims were tracked and still unpaid, so it was never counted.
	 *
	 * @param \EDD_Discount $discount The discount.
	 * @return int The order ID.
	 */
	private function pending_order_placed_before_claims( $discount ) {
		global $wpdb;

		update_option( Claims::STARTED_OPTION, time() - DAY_IN_SECONDS );

		$order_id = $this->order_without_claim( $discount );
		$wpdb->update(
			$wpdb->edd_orders,
			array(
				'date_created'   => '2020-01-01 00:00:00',
				'date_completed' => null,
			),
			array( 'id' => $order_id )
		);
		( new \EDD\Database\Queries\Order() )->refresh_item_cache( $order_id );

		$order = edd_get_order( $order_id );
		$this->assertSame( 'pending', $order->status );
		$this->assertEmpty( $order->date_completed );
		$this->assertFalse( Claims::holds( $order_id, $discount->id ) );

		return $order_id;
	}

	/**
	 * Creates an order placed before claims were tracked and put on hold without ever completing.
	 *
	 * @param \EDD_Discount $discount The discount.
	 * @return int The order ID.
	 */
	private function on_hold_order_placed_before_claims( $discount ) {
		global $wpdb;

		$order_id = $this->pending_order_placed_before_claims( $discount );
		$wpdb->update( $wpdb->edd_orders, array( 'status' => 'on_hold' ), array( 'id' => $order_id ) );
		( new \EDD\Database\Queries\Order() )->refresh_item_cache( $order_id );

		$this->assertSame( 'on_hold', edd_get_order( $order_id )->status );

		return $order_id;
	}

	/**
	 * Applies a discount at checkout as another shopper with a download in the cart.
	 *
	 * @param \EDD_Discount $discount The discount.
	 * @return bool Whether the discount was accepted.
	 */
	private function apply_code( $discount ) {
		edd_add_to_cart( \EDD\Tests\Helpers\EDD_Helper_Download::create_simple_download()->ID );

		return edd_is_discount_valid( $discount->code, '', false );
	}

	/**
	 * Reads a discount's use count from the database.
	 *
	 * @param \EDD_Discount $discount The discount.
	 * @return int
	 */
	private function get_use_count( $discount ) {
		return (int) edd_get_discount( $discount->id )->use_count;
	}
}
