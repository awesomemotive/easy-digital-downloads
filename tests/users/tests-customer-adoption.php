<?php
/**
 * Tests for claiming a guest customer record on registration.
 *
 * @package     EDD\Tests\Users
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.1
 */

namespace EDD\Tests\Users;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Helpers\EDD_Helper_Payment;
use EDD\Tests\Helpers\EDD_Helper_Email;

/**
 * Tests for claiming a guest customer record on registration.
 *
 * @since 3.7.1
 *
 * @group edd_users
 */
class CustomerAdoption extends EDD_UnitTestCase {

	/**
	 * The guest buyer's order.
	 *
	 * @var int
	 */
	protected $order_id;

	/**
	 * The guest buyer's customer record.
	 *
	 * @var \EDD_Customer
	 */
	protected $customer;

	/**
	 * Orders created by a single test, destroyed on tear down.
	 *
	 * @var int[]
	 */
	protected $test_order_ids = array();

	public function setUp(): void {
		parent::setUp();

		$this->order_id = EDD_Helper_Payment::create_simple_payment();

		// The helper's buyer address belongs to an existing user, which would make the
		// registration below fail rather than adopt anything.
		$email = 'guest-buyer@example.test';

		edd_update_order(
			$this->order_id,
			array(
				'user_id' => 0,
				'email'   => $email,
			)
		);

		$order = edd_get_order( $this->order_id );

		$this->customer = edd_get_customer( $order->customer_id );
		$this->customer->update(
			array(
				'user_id' => 0,
				'email'   => $email,
			)
		);
		$this->customer = edd_get_customer( $this->customer->id );
	}

	public function tearDown(): void {
		foreach ( $this->test_order_ids as $order_id ) {
			edd_destroy_order( $order_id );
		}
		$this->test_order_ids = array();

		// `edd_update_option()` also writes the $edd_options global, which no transaction rolls back.
		edd_delete_option( 'logged_in_only' );

		EDD_Helper_Email::stop_capturing_mail();

		edd_destroy_order( $this->order_id );

		parent::tearDown();
	}

	/**
	 * Registering with the address must not take over the customer record.
	 */
	public function test_registration_does_not_take_over_the_customer_record() {
		$this->register_with_the_guest_email();

		$this->assertSame( 0, $this->customer_user_id(), 'The customer must still be unclaimed.' );
	}

	/**
	 * Registering with the address must not take over the orders.
	 */
	public function test_registration_does_not_take_over_the_orders() {
		$this->register_with_the_guest_email();

		$this->assertSame( 0, $this->order_user_id(), 'The order must still be unclaimed.' );
	}

	/**
	 * Registering must still start the verification it already sends.
	 */
	public function test_registration_sets_the_account_pending_verification() {
		$user_id = $this->register_with_the_guest_email();

		$this->assertTrue( edd_user_pending_verification( $user_id ), 'The account must be pending.' );
	}

	/**
	 * Verifying the address claims the customer record and the orders.
	 *
	 * The control for the two tests above: the account which verifies the address still gets
	 * the history the feature exists to hand over.
	 */
	public function test_verifying_the_address_claims_the_customer_and_orders() {
		$user_id = $this->register_with_the_guest_email();

		edd_set_user_to_verified( $user_id );

		$this->assertSame( $user_id, $this->customer_user_id(), 'The customer must be claimed.' );
		$this->assertSame( $user_id, $this->order_user_id(), 'The order must be claimed.' );
	}

	/**
	 * Verifying the address claims every order at it, not just the first query page.
	 *
	 * Orders can sit at an address without sitting on the record being claimed, left behind by a
	 * merge or by an address moved between records. `edd_process_customer_updated()` re-parents
	 * only the record's own orders, so those are reached by address alone, and verification is
	 * one click which never comes again.
	 */
	public function test_verifying_the_address_claims_more_than_one_page_of_orders() {
		$other_record_id = edd_add_customer(
			array(
				'email'   => 'other-record@example.test',
				'name'    => 'Other Record',
				'user_id' => 0,
			)
		);

		$order_ids = array();
		for ( $i = 0; $i < 21; $i++ ) {
			$order_ids[] = $this->add_guest_order( $other_record_id, $this->customer->email );
		}

		$this->assertSame(
			21,
			(int) edd_count_orders(
				array(
					'email'       => $this->customer->email,
					'customer_id' => $other_record_id,
					'user_id'     => 0,
					'type'        => 'sale',
				)
			),
			'Fixture: the address must carry more orders off the record than one query page.'
		);

		$user_id = $this->register_with_the_guest_email();

		$this->assertTrue( edd_user_pending_verification( $user_id ), 'Fixture: the account must be pending.' );

		edd_set_user_to_verified( $user_id );

		$unclaimed = array();
		foreach ( $order_ids as $order_id ) {
			if ( (int) edd_get_order( $order_id )->user_id !== $user_id ) {
				$unclaimed[] = $order_id;
			}
		}

		$this->assertSame( array(), $unclaimed, 'Every order at the address must be claimed.' );
	}

	/**
	 * Registering with an address must start verification whatever status its orders are in.
	 *
	 * An admin can cancel an order, and cancelled is not one of `edd_get_payment_status_keys()`.
	 * Registration is the only thing which offers verification on that address, so if it reads a
	 * narrower set of orders than the claim does, the record it declined to claim never gets one.
	 */
	public function test_registration_starts_verification_for_an_address_whose_order_was_cancelled() {
		$email       = 'cancelled-order@example.test';
		$customer_id = edd_add_customer(
			array(
				'email'   => $email,
				'name'    => 'Cancelled Order',
				'user_id' => 0,
			)
		);

		$this->add_guest_order( $customer_id, $email, 'cancelled' );

		$this->assertSame(
			array(),
			edd_get_orders(
				array(
					'email'      => $email,
					'type'       => 'sale',
					'status__in' => edd_get_payment_status_keys(),
					'fields'     => 'ids',
				)
			),
			'Fixture: the cancelled order must sit outside the payment status keys.'
		);

		$user_id = wp_insert_user(
			array(
				'user_login' => 'cancelled-order-buyer',
				'user_pass'  => wp_generate_password(),
				'user_email' => $email,
			)
		);

		$this->assertNotWPError( $user_id, 'Fixture: the account must be created.' );
		$this->assertTrue(
			edd_user_pending_verification( $user_id ),
			'Registering against an address with orders must start verification.'
		);

		edd_set_user_to_verified( $user_id );

		$this->assertSame(
			(int) $user_id,
			(int) edd_get_customer( $customer_id )->user_id,
			'Verifying must claim the record.'
		);
	}

	/**
	 * Checkout auto-registration must not claim a record with earlier orders.
	 */
	public function test_auto_registration_does_not_claim_a_record_with_earlier_orders() {
		edd_update_option( 'logged_in_only', 'auto' );

		$new_order_id = $this->add_guest_order( $this->customer->id, $this->customer->email, 'complete' );

		$this->assertSame(
			1,
			(int) edd_count_orders(
				array(
					'customer_id' => $this->customer->id,
					'type'        => 'sale',
					'id__not_in'  => array( $new_order_id ),
				)
			),
			'Fixture: the record must carry exactly one earlier order.'
		);

		( new \EDD\Checkout\AutoRegister() )->create_user_and_add_to_order( $new_order_id );

		$this->assertSame(
			0,
			$this->customer_user_id(),
			'A record carrying an earlier order must not be claimed at checkout.'
		);
	}

	/**
	 * Checkout auto-registration must still claim a record whose only order is this checkout.
	 *
	 * The control for the test above, and the path every auto-registered buyer takes: the order
	 * being placed is already attached to the record by the time the guard runs.
	 */
	public function test_auto_registration_claims_a_record_whose_only_order_is_this_checkout() {
		edd_update_option( 'logged_in_only', 'auto' );

		$email       = 'first-time-buyer@example.test';
		$customer_id = edd_add_customer(
			array(
				'email'   => $email,
				'name'    => 'First Time Buyer',
				'user_id' => 0,
			)
		);

		$order_id = $this->add_guest_order( $customer_id, $email, 'complete' );

		$this->assertSame(
			1,
			(int) edd_count_orders(
				array(
					'customer_id' => $customer_id,
					'type'        => 'sale',
				)
			),
			'Fixture: the checkout being placed must be the record\'s only order.'
		);

		( new \EDD\Checkout\AutoRegister() )->create_user_and_add_to_order( $order_id );

		$user = get_user_by( 'email', $email );

		$this->assertNotFalse( $user, 'Auto-registration must have created the account.' );
		$this->assertSame(
			(int) $user->ID,
			(int) edd_get_customer( $customer_id )->user_id,
			'A record whose only order is this checkout must be claimed.'
		);
	}

	/**
	 * A record checkout refuses to claim must still be reachable by verifying the address.
	 *
	 * The buyer who abandons a gateway redirect and comes back leaves an order which never
	 * completed. If `maybe_remove_user_registration_actions()` reads a narrower set of orders
	 * than the claim guard, it unhooks the verification path for exactly the records the guard
	 * refuses, and those records have no owner and no way to gain one.
	 */
	public function test_a_record_refused_at_checkout_can_still_be_claimed_by_verifying() {
		edd_update_option( 'logged_in_only', 'auto' );

		$email       = 'came-back@example.test';
		$customer_id = edd_add_customer(
			array(
				'email'   => $email,
				'name'    => 'Came Back',
				'user_id' => 0,
			)
		);

		$this->add_guest_order( $customer_id, $email, 'abandoned' );
		$order_id = $this->add_guest_order( $customer_id, $email, 'complete' );

		$this->assertSame(
			1,
			(int) edd_count_orders(
				array(
					'customer_id' => $customer_id,
					'type'        => 'sale',
					'id__not_in'  => array( $order_id ),
				)
			),
			'Fixture: the record must carry the abandoned order.'
		);
		$this->assertSame(
			0,
			(int) edd_count_orders(
				array(
					'customer_id' => $customer_id,
					'type'        => 'sale',
					'id__not_in'  => array( $order_id ),
					'status__in'  => edd_get_complete_order_statuses(),
				)
			),
			'Fixture: the abandoned order must not count as a completed one.'
		);

		( new \EDD\Checkout\AutoRegister() )->create_user_and_add_to_order( $order_id );

		$user = get_user_by( 'email', $email );

		$this->assertNotFalse( $user, 'Auto-registration must have created the account.' );
		$this->assertSame(
			0,
			(int) edd_get_customer( $customer_id )->user_id,
			'A record carrying an earlier order must not be claimed at checkout.'
		);
		$this->assertTrue(
			edd_user_pending_verification( $user->ID ),
			'The account must be left able to verify the address.'
		);

		edd_set_user_to_verified( $user->ID );

		$this->assertSame(
			(int) $user->ID,
			(int) edd_get_customer( $customer_id )->user_id,
			'Verifying must claim the record checkout refused.'
		);
	}

	/**
	 * A second registration must not consume the claim.
	 *
	 * Registration only starts verification now, so an account which never verifies leaves the
	 * record claimable by the account which does.
	 */
	public function test_an_unverified_registration_does_not_consume_the_claim() {
		$this->register_with_the_guest_email();

		// Free the address by moving it rather than deleting the account: on multisite a user
		// deletion only detaches them from the site and the address stays taken network wide.
		wp_update_user(
			array(
				'ID'         => get_user_by( 'email', $this->customer->email )->ID,
				'user_email' => 'moved-on@example.test',
			)
		);

		$buyer_id = wp_insert_user(
			array(
				'user_login' => 'realbuyer',
				'user_pass'  => wp_generate_password(),
				'user_email' => $this->customer->email,
			)
		);

		$this->assertNotWPError( $buyer_id, 'The address must be free for the buyer to register with.' );

		edd_set_user_to_verified( $buyer_id );

		$this->assertSame( $buyer_id, $this->customer_user_id(), 'The buyer must still be able to claim.' );
		$this->assertSame( $buyer_id, $this->order_user_id(), 'The orders must still be claimable.' );
	}

	/**
	 * A guest purchase must not claim a record for a still-unverified account.
	 *
	 * Registering leaves the account pending rather than claiming anything, so a second guest
	 * purchase at the same address is what runs `edd_connect_guest_customer_to_existing_user()`.
	 */
	public function test_a_guest_purchase_does_not_claim_an_unverified_registrants_history() {
		$user_id = $this->register_with_the_guest_email();

		$this->assertTrue( edd_user_pending_verification( $user_id ), 'Fixture: the account must be pending.' );

		$new_order_id = $this->add_guest_order( $this->customer->id, $this->customer->email );

		( new \EDD_Customer( $this->customer->id ) )->attach_payment( $new_order_id );

		$this->assertSame( 0, $this->customer_user_id(), 'The customer must still be unclaimed.' );
		$this->assertSame( 0, $this->order_user_id(), 'The earlier order must still be unclaimed.' );
	}

	/**
	 * A guest purchase against a still-pending account must not resend the verification email.
	 *
	 * Registration already sent one.
	 */
	public function test_a_guest_purchase_does_not_resend_the_verification_email() {
		EDD_Helper_Email::install();
		EDD_Helper_Email::start_capturing_mail();

		$user_id = $this->register_with_the_guest_email();

		$this->assertTrue( edd_user_pending_verification( $user_id ), 'Fixture: the account must be pending.' );

		EDD_Helper_Email::start_capturing_mail();

		$new_order_id = $this->add_guest_order( $this->customer->id, $this->customer->email );

		( new \EDD_Customer( $this->customer->id ) )->attach_payment( $new_order_id );

		$this->assertSame( array(), EDD_Helper_Email::captured_mail(), 'No mail may be sent for an already-pending account.' );
	}

	/**
	 * The real buyer must still be able to claim their history once verified, even after the
	 * guest purchase above ran against the same unverified account.
	 *
	 * The control for the test above: without it, refusing every claim would satisfy it too.
	 */
	public function test_verifying_after_a_guest_purchase_still_claims_the_history() {
		$user_id = $this->register_with_the_guest_email();

		$new_order_id = $this->add_guest_order( $this->customer->id, $this->customer->email );

		( new \EDD_Customer( $this->customer->id ) )->attach_payment( $new_order_id );

		edd_set_user_to_verified( $user_id );

		$this->assertSame( $user_id, $this->customer_user_id(), 'The customer must be claimed once verified.' );
		$this->assertSame( $user_id, $this->order_user_id(), 'The earlier order must be claimed too.' );
		$this->assertSame(
			$user_id,
			(int) edd_get_order( $new_order_id )->user_id,
			'The guest purchase that triggered the check must be claimed too.'
		);
	}

	/**
	 * A guest purchase must still claim history for an address that is already verified.
	 *
	 * The case `edd_connect_guest_customer_to_existing_user()` exists for: an already-verified
	 * customer who checks out as a guest by habit.
	 */
	public function test_a_guest_purchase_still_claims_history_for_an_already_verified_account() {
		$buyer_id = wp_insert_user(
			array(
				'user_login' => 'verified-buyer',
				'user_pass'  => wp_generate_password(),
				'user_email' => 'verified-buyer@example.test',
			)
		);
		$this->assertNotWPError( $buyer_id, 'Fixture: the account must exist.' );
		$this->assertFalse( edd_user_pending_verification( $buyer_id ), 'Fixture: the account must not be pending.' );

		$this->customer->update( array( 'email' => 'verified-buyer@example.test' ) );
		$this->customer = edd_get_customer( $this->customer->id );

		$new_order_id = $this->add_guest_order( $this->customer->id, $this->customer->email );

		( new \EDD_Customer( $this->customer->id ) )->attach_payment( $new_order_id );

		$this->assertSame( $buyer_id, $this->customer_user_id(), 'An already-verified buyer must still be claimed.' );
	}

	/**
	 * An account registered with an address of the customer's that has no orders is not held pending,
	 * so a guest purchase must not hand it the customer's history.
	 */
	public function test_a_guest_purchase_does_not_hand_the_customers_history_to_an_orderless_secondary_address() {
		$primary_email = $this->customer->email;

		$this->customer->add_email( 'orderless-secondary@example.test' );
		$this->customer = edd_get_customer( $this->customer->id );
		$this->assertContains( 'orderless-secondary@example.test', $this->customer->emails, 'Fixture: the secondary address is on the customer.' );
		$this->assertEmpty(
			edd_get_orders(
				array(
					'email'  => 'orderless-secondary@example.test',
					'number' => 1,
				)
			),
			'Fixture: no order carries the secondary address.'
		);
		$this->assertSame( 0, $this->order_user_id(), 'Fixture: the earlier order is not owned by anyone.' );

		$stranger_id = wp_insert_user(
			array(
				'user_login' => 'secondary-stranger',
				'user_pass'  => wp_generate_password(),
				'user_email' => 'orderless-secondary@example.test',
			)
		);
		$this->assertNotWPError( $stranger_id, 'Fixture: the account must exist.' );
		$this->assertFalse( edd_user_pending_verification( $stranger_id ), 'Fixture: nothing held the account pending.' );

		$new_order_id = $this->add_guest_order( $this->customer->id, $primary_email );

		( new \EDD_Customer( $this->customer->id ) )->attach_payment( $new_order_id );

		$customer = edd_get_customer( $this->customer->id );

		$this->assertSame( 0, (int) $customer->user_id, 'The customer is not linked to the account.' );
		$this->assertSame( 0, $this->order_user_id(), 'The earlier order did not move.' );
		$this->assertSame( 0, (int) edd_get_order( $new_order_id )->user_id, 'The new order did not move.' );
		$this->assertSame( $primary_email, $customer->email, 'The primary address is unchanged.' );
		$this->assertFalse( edd_user_pending_verification( $stranger_id ), 'The account is not locked out by someone else\'s purchase.' );
	}

	/**
	 * Registers a user with the guest buyer's email address.
	 *
	 * @return int
	 */
	private function register_with_the_guest_email() {
		return wp_insert_user(
			array(
				'user_login' => 'claimant',
				'user_pass'  => wp_generate_password(),
				'user_email' => $this->customer->email,
			)
		);
	}

	/**
	 * Adds an unattached order for a customer record and tracks it for tear down.
	 *
	 * @param int    $customer_id The customer record the order belongs to.
	 * @param string $email       The address the order was placed with.
	 * @param string $status      The order status.
	 * @return int
	 */
	private function add_guest_order( $customer_id, $email, $status = 'pending' ) {
		$order_id = edd_add_order(
			array(
				'customer_id' => $customer_id,
				'email'       => $email,
				'user_id'     => 0,
				'type'        => 'sale',
				'status'      => $status,
				'currency'    => 'USD',
				'total'       => 20.00,
			)
		);

		$this->test_order_ids[] = $order_id;

		return $order_id;
	}

	/**
	 * The customer record's current owner.
	 *
	 * @return int
	 */
	private function customer_user_id() {
		return (int) ( new \EDD_Customer( $this->customer->id ) )->user_id;
	}

	/**
	 * The order's current owner.
	 *
	 * @return int
	 */
	private function order_user_id() {
		return (int) edd_get_order( $this->order_id )->user_id;
	}
}
