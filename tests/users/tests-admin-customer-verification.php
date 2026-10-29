<?php
/**
 * Tests for the admin Customer screen's verify and resend controls.
 *
 * @package     EDD\Tests\Users
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

namespace EDD\Tests\Users;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Helpers\EDD_Helper_Email;
use EDD\Users\Verification as UserVerification;

/**
 * Tests for the admin Customer screen's verify and resend controls.
 *
 * @since 3.7.3
 *
 * @group edd_users
 * @group edd_customers
 */
class AdminCustomerVerification extends EDD_UnitTestCase {

	/**
	 * Original $_GET['id'], or null if it was absent.
	 *
	 * @var mixed
	 */
	private $original_get_id;

	/**
	 * Original $_GET['_wpnonce'], or null if it was absent.
	 *
	 * @var mixed
	 */
	private $original_get_nonce;

	/**
	 * Original $_GET['user_id'], or null if it was absent.
	 *
	 * @var mixed
	 */
	private $original_get_user_id;

	/**
	 * Original current screen, restored after each test.
	 *
	 * @var \WP_Screen|null
	 */
	private $original_current_screen;

	public static function wpSetUpBeforeClass() {
		require_once EDD_PLUGIN_DIR . 'includes/admin/customers/customers.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/customers/customer-actions.php';
	}

	public function set_up() {
		parent::set_up();

		$this->original_get_id         = $_GET['id'] ?? null;
		$this->original_get_nonce      = $_GET['_wpnonce'] ?? null;
		$this->original_get_user_id    = $_GET['user_id'] ?? null;
		$this->original_current_screen = $GLOBALS['current_screen'] ?? null;
	}

	public function tear_down() {
		foreach ( array(
			'id'       => $this->original_get_id,
			'_wpnonce' => $this->original_get_nonce,
			'user_id'  => $this->original_get_user_id,
		) as $key => $value ) {
			if ( null === $value ) {
				unset( $_GET[ $key ] );
			} else {
				$_GET[ $key ] = $value;
			}
		}

		$GLOBALS['current_screen'] = $this->original_current_screen;

		EDD_Helper_Email::stop_capturing_mail();

		parent::tear_down();
	}

	public function test_unclaimed_customer_resolves_to_the_account_registered_with_its_email() {
		list( $customer, $user_id ) = $this->create_unclaimed_customer_with_pending_account( 'unclaimed-resolver@example.com' );

		$this->assertSame( $user_id, UserVerification::resolve_customer_user_id( $customer ) );
	}

	public function test_resolver_returns_the_customers_own_user_id_when_already_set() {
		$owning_user_id = self::factory()->user->create( array( 'user_email' => 'owner@example.com' ) );
		self::factory()->user->create( array( 'user_email' => 'claimed-customer@example.com' ) );

		$customer = self::edd()->customer->create_and_get(
			array(
				'email'   => 'claimed-customer@example.com',
				'user_id' => $owning_user_id,
			)
		);

		$this->assertSame( $owning_user_id, UserVerification::resolve_customer_user_id( $customer ) );
	}

	public function test_resolver_returns_zero_when_no_account_is_registered_with_the_email() {
		$customer = self::edd()->customer->create_and_get(
			array(
				'email'   => 'no-account-resolver@example.com',
				'user_id' => 0,
			)
		);

		$this->assertSame( 0, UserVerification::resolve_customer_user_id( $customer ) );
	}

	public function test_notice_renders_for_a_customer_whose_account_is_not_yet_claimed() {
		list( $customer ) = $this->create_unclaimed_customer_with_pending_account( 'unclaimed-notice@example.com' );

		ob_start();
		\edd_verify_customer_notice( $customer );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'pending verification', $output );
		$this->assertStringContainsString( 'edd-action=verify_user_admin', $output );
		$this->assertStringContainsString( 'edd-action=resend_verification_email_admin', $output );
	}

	public function test_notice_marks_verify_for_confirmation_when_the_account_is_not_yet_claimed() {
		list( $customer ) = $this->create_unclaimed_customer_with_pending_account( 'unclaimed-confirm@example.com' );

		ob_start();
		\edd_verify_customer_notice( $customer );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'data-edd-verify-unclaimed', $output );
		$this->assertStringNotContainsString( 'onclick', $output );
	}

	public function test_notice_says_the_unclaimed_account_is_not_linked_and_not_confirmed() {
		list( $customer ) = $this->create_unclaimed_customer_with_pending_account( 'unclaimed-copy@example.com' );

		ob_start();
		\edd_verify_customer_notice( $customer );
		$output = wp_specialchars_decode( ob_get_clean(), ENT_QUOTES );

		$this->assertStringContainsString( 'A user account with this customer\'s email address is pending verification. It isn\'t linked to this customer yet, and its owner hasn\'t confirmed the address.', $output );
		$this->assertStringNotContainsString( 'This customer\'s user account is pending verification.', $output );
	}

	public function test_notice_keeps_its_wording_for_a_claimed_customer() {
		$user_id = self::factory()->user->create( array( 'user_email' => 'claimed-copy@example.com' ) );
		edd_set_user_to_pending( $user_id );
		$customer = self::edd()->customer->create_and_get(
			array(
				'email'   => 'claimed-copy@example.com',
				'user_id' => $user_id,
			)
		);

		ob_start();
		\edd_verify_customer_notice( $customer );
		$output = wp_specialchars_decode( ob_get_clean(), ENT_QUOTES );

		$this->assertStringContainsString( 'This customer\'s user account is pending verification.', $output );
	}

	public function test_admin_script_variables_carry_the_unclaimed_verify_confirmation() {
		$method = new \ReflectionMethod( \EDD\Admin\Assets\Localization::class, 'get_variables' );
		$method->setAccessible( true );

		$variables = $method->invoke( null );

		$this->assertArrayHasKey( 'verify_unclaimed_user', $variables );
		$this->assertStringContainsString( 'its owner hasn\'t confirmed they own it', $variables['verify_unclaimed_user'] );
		$this->assertStringContainsString( 'use Resend verification email', $variables['verify_unclaimed_user'] );
		$this->assertStringContainsString( 'makes its address the customer\'s email', $variables['verify_unclaimed_user'] );
		$this->assertStringContainsString( 'moves the customer\'s orders', $variables['verify_unclaimed_user'] );
		$this->assertStringContainsString( 'licenses', $variables['verify_unclaimed_user'] );
	}

	public function test_notice_does_not_ask_for_confirmation_for_a_claimed_customer() {
		$user_id = self::factory()->user->create( array( 'user_email' => 'claimed-confirm@example.com' ) );
		edd_set_user_to_pending( $user_id );
		$customer = self::edd()->customer->create_and_get(
			array(
				'email'   => 'claimed-confirm@example.com',
				'user_id' => $user_id,
			)
		);

		ob_start();
		\edd_verify_customer_notice( $customer );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'edd-action=verify_user_admin', $output );
		$this->assertStringNotContainsString( 'data-edd-verify-unclaimed', $output );
	}

	public function test_admin_verification_activates_an_unclaimed_customers_account() {
		list( $customer, $user_id ) = $this->create_unclaimed_customer_with_pending_account( 'unclaimed-verify@example.com' );

		$this->run_admin_verification( $customer );

		$this->assertFalse( edd_user_pending_verification( $user_id ) );
	}

	public function test_admin_verification_claims_an_unclaimed_customer_record() {
		list( $customer, $user_id ) = $this->create_unclaimed_customer_with_pending_account( 'unclaimed-claim@example.com' );

		$this->run_admin_verification( $customer );

		$this->assertSame( $user_id, (int) edd_get_customer( $customer->id )->user_id );
	}

	public function test_resolver_skips_a_verified_account_when_only_pending_is_wanted() {
		$user_id  = self::factory()->user->create( array( 'user_email' => 'verified-pending-only@example.com' ) );
		$customer = self::edd()->customer->create_and_get(
			array(
				'email'   => 'verified-pending-only@example.com',
				'user_id' => 0,
			)
		);

		$this->assertSame( 0, UserVerification::resolve_customer_user_id( $customer, true ) );
		$this->assertSame( $user_id, UserVerification::resolve_customer_user_id( $customer ) );
	}

	public function test_resolver_skips_a_verified_account_for_a_claimed_customer_when_only_pending_is_wanted() {
		$user_id  = self::factory()->user->create( array( 'user_email' => 'claimed-verified@example.com' ) );
		$customer = self::edd()->customer->create_and_get(
			array(
				'email'   => 'claimed-verified@example.com',
				'user_id' => $user_id,
			)
		);
		$this->assertFalse( edd_user_pending_verification( $user_id ), 'Fixture: the account is verified.' );

		$this->assertSame( 0, UserVerification::resolve_customer_user_id( $customer, true ) );
		$this->assertSame( $user_id, UserVerification::resolve_customer_user_id( $customer ) );
	}

	public function test_resolver_ignores_an_account_already_linked_to_another_customer() {
		list( $guest ) = $this->create_guest_sharing_an_account_with_another_customer( 'shared-email-resolver@example.com' );

		$this->assertSame( 0, UserVerification::resolve_customer_user_id( $guest ) );
	}

	public function test_resolver_finds_an_account_registered_with_a_secondary_address() {
		$customer = self::edd()->customer->create_and_get(
			array(
				'email'   => 'primary-resolver@example.com',
				'user_id' => 0,
			)
		);
		$customer->add_email( 'secondary-resolver@example.com' );
		$user_id = self::factory()->user->create( array( 'user_email' => 'secondary-resolver@example.com' ) );
		edd_set_user_to_pending( $user_id );

		$this->assertContains( 'secondary-resolver@example.com', edd_get_customer( $customer->id )->emails, 'Fixture: the secondary address is on the customer.' );
		$this->assertFalse( get_user_by( 'email', 'primary-resolver@example.com' ), 'Fixture: only the secondary address has an account.' );

		$this->assertSame( $user_id, UserVerification::resolve_customer_user_id( edd_get_customer( $customer->id ) ) );
	}

	public function test_resolver_returns_zero_when_the_addresses_belong_to_more_than_one_account() {
		list( $customer, $first_user_id ) = $this->create_unclaimed_customer_with_pending_account( 'first-account@example.com' );
		$customer->add_email( 'second-account@example.com' );
		$second_user_id = self::factory()->user->create( array( 'user_email' => 'second-account@example.com' ) );

		$customer = edd_get_customer( $customer->id );
		$this->assertNotSame( $first_user_id, $second_user_id, 'Fixture: two distinct accounts.' );
		$this->assertContains( 'second-account@example.com', $customer->emails, 'Fixture: both addresses are on the customer.' );

		$this->assertSame( 0, UserVerification::resolve_customer_user_id( $customer ) );
	}

	public function test_resolver_ignores_a_network_user_who_is_not_a_member_of_this_site() {
		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Site membership only exists on multisite.' );
		}

		list( $customer, $user_id ) = $this->create_unclaimed_customer_with_pending_account( 'network-user@example.com' );
		$this->assertTrue( is_user_member_of_blog( $user_id ), 'Fixture: the account is a member before it is removed.' );
		$this->assertSame( $user_id, UserVerification::resolve_customer_user_id( $customer ), 'Fixture: the resolver finds the member.' );

		remove_user_from_blog( $user_id, get_current_blog_id() );

		$this->assertInstanceOf( \WP_User::class, get_user_by( 'email', 'network-user@example.com' ), 'Fixture: the account still exists on the network.' );
		$this->assertFalse( is_user_member_of_blog( $user_id ) );
		$this->assertSame( 0, UserVerification::resolve_customer_user_id( $customer ) );
	}

	public function test_notice_does_not_render_for_an_account_linked_to_another_customer() {
		list( $guest ) = $this->create_guest_sharing_an_account_with_another_customer( 'shared-email-notice@example.com' );

		ob_start();
		\edd_verify_customer_notice( $guest );
		$this->assertStringNotContainsString( 'pending verification', ob_get_clean() );
	}

	public function test_admin_verification_leaves_an_account_linked_to_another_customer_pending() {
		list( $guest, $user_id ) = $this->create_guest_sharing_an_account_with_another_customer( 'shared-email-verify@example.com' );

		$order_id = self::edd()->order->create(
			array(
				'customer_id' => $guest->id,
				'email'       => 'shared-email-verify@example.com',
				'user_id'     => 0,
			)
		);

		$this->run_admin_verification( $guest );

		$this->assertTrue( edd_user_pending_verification( $user_id ) );
		$this->assertSame( 0, (int) edd_get_order( $order_id )->user_id );
	}

	public function test_verify_link_names_the_account_it_acts_on() {
		list( $customer, $user_id ) = $this->create_unclaimed_customer_with_pending_account( 'link-names-account@example.com' );

		ob_start();
		\edd_verify_customer_notice( $customer );
		$output = html_entity_decode( ob_get_clean() );

		$this->assertStringContainsString( 'user_id=' . $user_id, $output );
	}

	public function test_admin_verification_refuses_when_the_matched_account_has_changed_since_the_notice() {
		list( $customer, $notice_user_id ) = $this->create_unclaimed_customer_with_pending_account( 'moved-match@example.com' );

		// The account the notice showed gives up the address and a different account takes it.
		wp_update_user(
			array(
				'ID'         => $notice_user_id,
				'user_email' => 'moved-away@example.com',
			)
		);
		$new_user_id = self::factory()->user->create( array( 'user_email' => 'moved-match@example.com' ) );
		edd_set_user_to_pending( $new_user_id );

		$this->assertSame( $new_user_id, UserVerification::resolve_customer_user_id( $customer ), 'Fixture: the match now points at the other account.' );

		try {
			$this->run_admin_verification( $customer, $notice_user_id );
			$this->fail( 'The verification should have been refused.' );
		} catch ( \WPDieException $e ) {
			$this->assertStringContainsString( 'has changed', $e->getMessage() );
		}

		$this->assertTrue( edd_user_pending_verification( $new_user_id ) );
		$this->assertEmpty( edd_get_customer( $customer->id )->user_id );
	}

	public function test_admin_verification_refuses_a_nonce_issued_for_a_different_account() {
		list( $customer, $matched_user_id ) = $this->create_unclaimed_customer_with_pending_account( 'mismatched-nonce@example.com' );
		$other_user_id                      = self::factory()->user->create( array( 'user_email' => 'mismatched-nonce-other@example.com' ) );

		$this->assertNotSame( $matched_user_id, $other_user_id, 'Fixture: two distinct accounts.' );
		$this->assertSame( $matched_user_id, UserVerification::resolve_customer_user_id( $customer ), 'Fixture: the link names the account the customer really resolves to.' );

		$this->login_as_administrator();
		set_current_screen( 'dashboard' );

		// A nonce minted for the other account, on a link naming the matched one.
		$_GET['id']       = $customer->id;
		$_GET['user_id']  = $matched_user_id;
		$_GET['_wpnonce'] = wp_create_nonce( 'edd-verify-user-' . $other_user_id );

		try {
			\edd_process_admin_user_verification();
			$this->fail( 'The verification should have been refused.' );
		} catch ( \WPDieException $e ) {
			$this->assertStringContainsString( 'Nonce verification failed.', $e->getMessage() );
		}

		$this->assertTrue( edd_user_pending_verification( $matched_user_id ) );
	}

	public function test_the_verify_link_in_the_notice_is_accepted_by_the_handler() {
		list( $customer, $user_id ) = $this->create_unclaimed_customer_with_pending_account( 'rendered-link@example.com' );

		$this->login_as_administrator();
		set_current_screen( 'dashboard' );

		ob_start();
		\edd_verify_customer_notice( $customer );
		$output = ob_get_clean();

		$this->assertSame( 1, preg_match( '/href="([^"]*verify_user_admin[^"]*)"/', $output, $matches ), 'Fixture: the notice renders a Verify link.' );

		wp_parse_str( (string) wp_parse_url( html_entity_decode( $matches[1] ), PHP_URL_QUERY ), $query );
		$this->assertSame( (string) $user_id, $query['user_id'], 'Fixture: the link names the account.' );
		$this->assertArrayHasKey( '_wpnonce', $query, 'Fixture: the link carries a nonce.' );
		$this->assertTrue( edd_user_pending_verification( $user_id ), 'Fixture: the account is pending before the click.' );

		$_GET['id']       = $query['id'];
		$_GET['user_id']  = $query['user_id'];
		$_GET['_wpnonce'] = $query['_wpnonce'];

		\edd_process_admin_user_verification();

		$this->assertFalse( edd_user_pending_verification( $user_id ) );
	}

	public function test_admin_verification_dies_when_the_customer_does_not_exist() {
		$this->expectException( \WPDieException::class );
		$this->expectExceptionMessage( 'Customer not found.' );

		$missing = new \stdClass();
		$missing->id = 999999;
		$this->run_admin_verification( $missing, 1 );
	}

	public function test_admin_verification_moves_the_customers_orders_to_the_account() {
		list( $customer, $user_id ) = $this->create_unclaimed_customer_with_pending_account( 'unclaimed-orders@example.com' );

		$order_id = self::edd()->order->create(
			array(
				'customer_id' => $customer->id,
				'email'       => 'unclaimed-orders@example.com',
				'user_id'     => 0,
			)
		);

		$this->run_admin_verification( $customer );

		$this->assertSame( $user_id, (int) edd_get_order( $order_id )->user_id );
	}

	public function test_verifying_a_secondary_address_match_does_what_the_confirmation_says() {
		$customer = self::edd()->customer->create_and_get(
			array(
				'email'   => 'primary-effects@example.com',
				'user_id' => 0,
			)
		);
		$customer->add_email( 'secondary-effects@example.com' );
		$user_id = self::factory()->user->create( array( 'user_email' => 'secondary-effects@example.com' ) );
		edd_set_user_to_pending( $user_id );

		$order_ids = array();
		foreach ( array( 'primary-effects@example.com', 'secondary-effects@example.com' ) as $email ) {
			$order_ids[] = self::edd()->order->create(
				array(
					'customer_id' => $customer->id,
					'email'       => $email,
					'user_id'     => 0,
				)
			);
		}

		$this->run_admin_verification( edd_get_customer( $customer->id ) );

		$customer = edd_get_customer( $customer->id );
		$this->assertSame( $user_id, (int) $customer->user_id, 'The record is linked to the account.' );
		$this->assertSame( 'secondary-effects@example.com', $customer->email, 'The account\'s address becomes the customer\'s email.' );
		foreach ( $order_ids as $order_id ) {
			$this->assertSame( $user_id, (int) edd_get_order( $order_id )->user_id, 'All of the customer\'s orders move, not only those on the account\'s address.' );
		}
	}

	public function test_admin_resend_sends_for_a_customer_whose_account_is_not_yet_claimed() {
		list( $customer, $user_id ) = $this->create_unclaimed_customer_with_pending_account( 'unclaimed-resend@example.com' );

		$this->login_as_administrator();

		$_GET['id']       = $customer->id;
		$_GET['_wpnonce'] = wp_create_nonce( 'edd-resend-verification' );

		EDD_Helper_Email::install();
		EDD_Helper_Email::start_capturing_mail();

		$verification = new UserVerification();
		$verification->process_admin_verification_resend();

		$sent = EDD_Helper_Email::captured_mail();
		$this->assertCount( 1, $sent );
		$this->assertSame( get_userdata( $user_id )->user_email, $sent[0]['to'] );
	}

	public function test_admin_resend_dies_when_the_addresses_belong_to_more_than_one_account() {
		list( $customer ) = $this->create_unclaimed_customer_with_pending_account( 'ambiguous-resend-a@example.com' );
		$customer->add_email( 'ambiguous-resend-b@example.com' );
		self::factory()->user->create( array( 'user_email' => 'ambiguous-resend-b@example.com' ) );

		$this->login_as_administrator();

		$_GET['id']       = $customer->id;
		$_GET['_wpnonce'] = wp_create_nonce( 'edd-resend-verification' );

		EDD_Helper_Email::install();
		EDD_Helper_Email::start_capturing_mail();

		try {
			( new UserVerification() )->process_admin_verification_resend();
			$this->fail( 'Resend should have been refused.' );
		} catch ( \WPDieException $e ) {
			$this->assertStringContainsString( 'does not have a user account', $e->getMessage() );
		}

		$this->assertCount( 0, EDD_Helper_Email::captured_mail() );
	}

	public function test_admin_resend_dies_when_customer_has_no_user_account() {
		$customer = self::edd()->customer->create_and_get(
			array(
				'email'   => 'no-account-resend@example.com',
				'user_id' => 0,
			)
		);

		$this->login_as_administrator();

		$_GET['id']       = $customer->id;
		$_GET['_wpnonce'] = wp_create_nonce( 'edd-resend-verification' );

		$verification = new UserVerification();

		$this->expectException( \WPDieException::class );
		$verification->process_admin_verification_resend();
	}

	/**
	 * Creates a customer record with no user_id, and the WordPress account pending
	 * verification at the customer's email address.
	 *
	 * @since 3.7.3
	 *
	 * @param string $email Email shared by the customer and the pending account.
	 * @return array{0: \EDD_Customer, 1: int} The customer and the pending account's user ID.
	 */
	private function create_unclaimed_customer_with_pending_account( $email ) {
		$user_id = self::factory()->user->create( array( 'user_email' => $email ) );
		edd_set_user_to_pending( $user_id );

		$customer = self::edd()->customer->create_and_get(
			array(
				'email'   => $email,
				'user_id' => 0,
			)
		);

		return array( $customer, $user_id );
	}

	/**
	 * Creates a pending account linked to one customer, and a guest customer sharing its email.
	 *
	 * @since 3.7.3
	 *
	 * @param string $email Email shared by the guest customer and the pending account.
	 * @return array{0: \EDD_Customer, 1: int} The guest customer and the pending account's user ID.
	 */
	private function create_guest_sharing_an_account_with_another_customer( $email ) {
		$user_id = self::factory()->user->create( array( 'user_email' => $email ) );
		edd_set_user_to_pending( $user_id );

		self::edd()->customer->create(
			array(
				'email'   => 'other-' . $email,
				'user_id' => $user_id,
			)
		);
		$guest = self::edd()->customer->create_and_get(
			array(
				'email'   => $email,
				'user_id' => 0,
			)
		);

		return array( $guest, $user_id );
	}

	/**
	 * Runs the admin Verify handler for a customer as an administrator.
	 *
	 * @since 3.7.3
	 *
	 * @param \EDD_Customer $customer The customer whose Verify link is followed.
	 * @param int|null     $user_id  The account the link names. Defaults to the account the customer resolves to.
	 * @return void
	 */
	private function run_admin_verification( $customer, $user_id = null ) {
		if ( is_null( $user_id ) ) {
			$user_id = UserVerification::resolve_customer_user_id( $customer );
		}

		$this->login_as_administrator();
		set_current_screen( 'dashboard' );

		$_GET['id']       = $customer->id;
		$_GET['user_id']  = $user_id;
		$_GET['_wpnonce'] = wp_create_nonce( 'edd-verify-user-' . $user_id );

		\edd_process_admin_user_verification();
	}

	/**
	 * Logs in an administrator, who holds edit_shop_payments by default.
	 *
	 * @since 3.7.3
	 *
	 * @return int The administrator's user ID.
	 */
	private function login_as_administrator() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		return $admin_id;
	}
}
