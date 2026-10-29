<?php
/**
 * PayPal Purchase User - Post-Capture Login Tests
 *
 * Characterizes PurchaseUser's capture-side login re-issue and the session
 * marker that records who a checkout request authenticated. The sink re-issues
 * an auth cookie only for the user recorded at order creation, and only when
 * that user still matches the order.
 *
 * @package   EDD\Tests\Gateways\PayPal
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.7.3
 */

namespace EDD\Tests\Gateways\PayPal;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Checkout\AutoRegister;
use EDD\Gateways\PayPal\V3\PurchaseUser;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests for PurchaseUser::maybe_log_in_after_capture() and the marker the writer records.
 *
 * @group gateways
 * @group paypal
 */
class PurchaseUserLoginTest extends EDD_UnitTestCase {

	/**
	 * Mirrors PurchaseUser's private session key so the tests can read and reset it.
	 *
	 * The production key is a private const, so a test cannot reference it.
	 *
	 * @var string
	 */
	private const MARKER_KEY = 'checkout_authenticated_user';

	/**
	 * User ids passed to every observed set_auth_cookie call, in order.
	 *
	 * @var int[]
	 */
	private $auth_cookie_calls = array();

	/**
	 * Logins passed to every observed wp_login call, in order.
	 *
	 * @var string[]
	 */
	private $wp_login_calls = array();

	/**
	 * The logged_in_only option value captured before a test changes it, so
	 * tear_down can restore it. Null when the option was unset.
	 *
	 * @var mixed
	 */
	private $prior_logged_in_only = null;

	/**
	 * Resets the marker and starts observing the grant hooks.
	 */
	public function set_up(): void {
		parent::set_up();

		wp_set_current_user( 0 );
		EDD()->session->set( self::MARKER_KEY, null );

		$this->prior_logged_in_only = edd_get_option( 'logged_in_only', null );

		$this->auth_cookie_calls = array();
		$this->wp_login_calls    = array();

		add_action( 'set_auth_cookie', array( $this, 'record_auth_cookie' ), 10, 6 );
		add_action( 'wp_login', array( $this, 'record_wp_login' ), 10, 2 );
	}

	/**
	 * Stops observing and clears the marker so it cannot leak into another test.
	 */
	public function tear_down(): void {
		remove_action( 'set_auth_cookie', array( $this, 'record_auth_cookie' ), 10 );
		remove_action( 'wp_login', array( $this, 'record_wp_login' ), 10 );

		EDD()->session->set( self::MARKER_KEY, null );
		EDD()->session->set( 'edd_purchase', null );
		EDD()->session->set( 'edd_resume_payment', null );
		wp_set_current_user( 0 );

		if ( null === $this->prior_logged_in_only ) {
			edd_delete_option( 'logged_in_only' );
		} else {
			edd_update_option( 'logged_in_only', $this->prior_logged_in_only );
		}

		parent::tear_down();
	}

	/**
	 * Records the user id an auth cookie was set for.
	 *
	 * @param string $auth_cookie The cookie value.
	 * @param int    $expire      The expiration time.
	 * @param int    $expiration  The expiration time reference.
	 * @param int    $user_id     The user id the cookie was set for.
	 * @param string $scheme      The cookie scheme.
	 * @param string $token       The session token.
	 */
	public function record_auth_cookie( $auth_cookie, $expire, $expiration, $user_id, $scheme, $token ) {
		$this->auth_cookie_calls[] = (int) $user_id;
	}

	/**
	 * Records the login a wp_login call fired for.
	 *
	 * @param string   $user_login The user's login name.
	 * @param \WP_User $user       The user object.
	 */
	public function record_wp_login( $user_login, $user ) {
		$this->wp_login_calls[] = $user_login;
	}

	/**
	 * A capture with no recorded user must not re-issue a cookie, even when the
	 * order's user_id happens to match a registered account.
	 */
	public function test_capture_without_recorded_user_does_not_log_in() {
		$order_owner      = self::factory()->user->create(
			array(
				'role'       => 'subscriber',
				'user_email' => self::generate_test_email(),
			)
		);
		$order_owner_user = get_userdata( $order_owner );

		$order_id = $this->make_order( $order_owner, $order_owner_user->user_email );

		PurchaseUser::maybe_log_in_after_capture( $order_id );

		$this->assertSame( array(), $this->auth_cookie_calls, 'A capture with no recorded user must not receive an auth cookie.' );
		$this->assertSame( array(), $this->wp_login_calls, 'A capture with no recorded user must not fire wp_login.' );
	}

	/**
	 * A buyer this request recorded, whose order still matches, gets exactly one
	 * cookie re-issued.
	 */
	public function test_recorded_buyer_grants_exactly_one_cookie() {
		$buyer_id = self::factory()->user->create(
			array(
				'role'       => 'subscriber',
				'user_email' => self::generate_test_email(),
			)
		);
		$buyer    = get_userdata( $buyer_id );

		$order_id = $this->make_order( $buyer_id, $buyer->user_email );

		$this->record_marker_for( $buyer_id );

		PurchaseUser::maybe_log_in_after_capture( $order_id );

		$this->assertSame( array( $buyer_id ), $this->auth_cookie_calls, 'A recorded buyer must receive exactly one auth cookie.' );
		$this->assertSame( array( $buyer->user_login ), $this->wp_login_calls, 'wp_login must fire exactly once, for the recorded buyer.' );
	}

	/**
	 * The marker is single-use: a second call for the same order must not
	 * re-issue the cookie once the first call has consumed it.
	 */
	public function test_recorded_marker_is_single_use() {
		$buyer_id = self::factory()->user->create(
			array(
				'role'       => 'subscriber',
				'user_email' => self::generate_test_email(),
			)
		);
		$buyer    = get_userdata( $buyer_id );

		$order_id = $this->make_order( $buyer_id, $buyer->user_email );

		$this->record_marker_for( $buyer_id );

		PurchaseUser::maybe_log_in_after_capture( $order_id );

		// Assert the fixture: the first call must have actually granted a cookie,
		// or a second no-op call would prove nothing about a repeated capture.
		$this->assertSame( array( $buyer_id ), $this->auth_cookie_calls, 'Fixture: the first call must grant the cookie.' );

		PurchaseUser::maybe_log_in_after_capture( $order_id );

		$this->assertSame( array( $buyer_id ), $this->auth_cookie_calls, 'A second call must not re-issue the cookie once the marker is consumed.' );
	}

	/**
	 * A recorded user that names one account while the order belongs to another
	 * must not grant either account a cookie.
	 */
	public function test_recorded_user_must_match_order_user() {
		$recorded_user  = self::factory()->user->create(
			array(
				'role'       => 'subscriber',
				'user_email' => self::generate_test_email(),
			)
		);
		$order_owner      = self::factory()->user->create(
			array(
				'role'       => 'subscriber',
				'user_email' => self::generate_test_email(),
			)
		);
		$order_owner_user = get_userdata( $order_owner );

		$order_id = $this->make_order( $order_owner, $order_owner_user->user_email );

		$this->record_marker_for( $recorded_user );

		PurchaseUser::maybe_log_in_after_capture( $order_id );

		$this->assertSame( array(), $this->auth_cookie_calls, 'A recorded-user/order mismatch must not grant a cookie to either account.' );
		$this->assertSame( array(), $this->wp_login_calls, 'A recorded-user/order mismatch must not fire wp_login.' );
	}

	/**
	 * resolve() records nothing on its own; the writer run after the order is
	 * built is what records the user the login branch authenticated.
	 */
	public function test_resolve_login_branch_records_nothing_until_writer_runs() {
		$password = 'correct-' . wp_generate_password( 12, false );
		$user_id  = self::factory()->user->create(
			array(
				'role'       => 'subscriber',
				'user_pass'  => $password,
				'user_email' => self::generate_test_email(),
			)
		);
		$user     = get_userdata( $user_id );

		$form_data = array(
			'edd-purchase-var' => 'needs-to-login',
			'edd_user_login'   => $user->user_login,
			'edd_user_pass'    => $password,
		);

		$result = PurchaseUser::resolve( $form_data, $user->user_email, 'Test', 'Buyer', array() );

		// Assert the fixture: a failed login would leave the marker unset for an
		// uninteresting reason (the branch never ran to completion).
		$this->assertIsArray( $result, 'Fixture: a valid login must resolve successfully, not return a WP_Error.' );
		$this->assertTrue( is_user_logged_in(), 'Fixture: the login branch must sign the buyer in.' );

		$this->assertEmpty(
			EDD()->session->get( self::MARKER_KEY ),
			'resolve() alone must not record the authenticated user.'
		);

		PurchaseUser::remember_authenticated_user();

		$this->assertSame(
			$user_id,
			(int) EDD()->session->get( self::MARKER_KEY ),
			'The writer must record the user the login branch authenticated.'
		);
	}

	/**
	 * A guest checkout under a registered account's email records no user: the
	 * request never authenticated as that account.
	 */
	public function test_guest_with_registered_email_records_no_user() {
		$email          = self::generate_test_email();
		$registered     = self::factory()->user->create(
			array(
				'role'       => 'subscriber',
				'user_email' => $email,
			)
		);

		// Assert the fixture: the account the email belongs to must exist, or the
		// scenario this guards against cannot arise.
		$this->assertNotEmpty( $registered, 'Fixture: the registered account must exist.' );
		$this->assertInstanceOf( \WP_User::class, get_user_by( 'email', $email ), 'Fixture: the email must belong to a registered account.' );

		wp_set_current_user( 0 );

		PurchaseUser::resolve(
			array( 'edd-purchase-var' => '' ),
			$email,
			'Guest',
			'Buyer',
			array()
		);

		$this->assertFalse( is_user_logged_in(), 'A guest checkout must not authenticate as the registered account.' );
		$this->assertEmpty(
			EDD()->session->get( self::MARKER_KEY ),
			'A guest checkout under a registered email must record no user.'
		);
	}

	/**
	 * A guest checkout clears a user recorded by an earlier attempt, so a stale
	 * value cannot survive into a later request.
	 */
	public function test_guest_checkout_clears_a_stale_recorded_user() {
		$earlier_buyer = self::factory()->user->create(
			array(
				'role'       => 'subscriber',
				'user_email' => self::generate_test_email(),
			)
		);

		$this->record_marker_for( $earlier_buyer );

		// Assert the fixture: the stale value must be present, or clearing it
		// proves nothing.
		$this->assertSame(
			$earlier_buyer,
			(int) EDD()->session->get( self::MARKER_KEY ),
			'Fixture: the earlier attempt must have recorded a user.'
		);

		wp_set_current_user( 0 );
		PurchaseUser::remember_authenticated_user();

		$this->assertEmpty(
			EDD()->session->get( self::MARKER_KEY ),
			'A guest checkout must clear a user recorded by an earlier attempt.'
		);
	}

	/**
	 * Registering a new account at checkout records that account as the
	 * authenticated user.
	 */
	public function test_registration_at_checkout_records_the_new_user() {
		$email    = self::generate_test_email();
		$login    = 'buyer_' . wp_generate_password( 8, false, false );
		$password = wp_generate_password( 16, false );

		$new_user_id = edd_register_and_login_new_user(
			array(
				'user_id'    => 0,
				'user_login' => $login,
				'user_email' => $email,
				'user_pass'  => $password,
				'user_first' => 'New',
				'user_last'  => 'Buyer',
			)
		);

		// Assert the fixture: registration must have created and signed in the
		// account, or the writer would record nothing for an unrelated reason.
		$this->assertIsInt( $new_user_id, 'Fixture: registration must return a user id.' );
		$this->assertGreaterThan( 0, $new_user_id, 'Fixture: registration must create an account.' );
		$this->assertSame( $new_user_id, get_current_user_id(), 'Fixture: the new account must be signed in.' );

		PurchaseUser::remember_authenticated_user();

		$this->assertSame(
			$new_user_id,
			(int) EDD()->session->get( self::MARKER_KEY ),
			'The writer must record the account registered at checkout.'
		);
	}

	/**
	 * Auto Register signs in a new account during order building, and the writer
	 * records that account.
	 */
	public function test_auto_registration_records_the_new_user() {
		edd_update_option( 'logged_in_only', 'auto' );

		$email       = self::generate_test_email();
		$customer_id = edd_add_customer(
			array(
				'email' => $email,
				'name'  => 'Auto Buyer',
			)
		);

		$order_id = $this->make_order( 0, $email, $customer_id );

		// Auto Register logs in a visitor who has been through a checkout of their
		// own this session.
		edd_set_purchase_session( array( 'purchase_key' => 'auto-register-' . wp_generate_password( 12, false ) ) );

		// Assert the fixtures the handler branches on.
		$this->assertTrue( AutoRegister::is_enabled(), 'Fixture: Auto Register must be enabled.' );
		$this->assertFalse( is_user_logged_in(), 'Fixture: the buyer must start as a guest.' );
		$this->assertFalse( get_user_by( 'email', $email ), 'Fixture: no account may exist for the email yet.' );
		$this->assertNotEmpty( edd_get_purchase_session(), 'Fixture: the session must carry a purchase.' );

		$subscriber = new AutoRegister();
		$subscriber->create_user_after_order_built( $order_id, array( 'user_info' => array( 'email' => $email ) ) );

		$registered_id = get_current_user_id();

		// Assert the fixture: Auto Register must have created and signed in the
		// account, or there is nothing for the writer to record.
		$this->assertGreaterThan( 0, $registered_id, 'Fixture: Auto Register must sign the new account in.' );
		$this->assertSame(
			$registered_id,
			(int) edd_get_order( $order_id )->user_id,
			'Fixture: Auto Register must assign the new account to the order.'
		);

		PurchaseUser::remember_authenticated_user();

		$this->assertSame(
			$registered_id,
			(int) EDD()->session->get( self::MARKER_KEY ),
			'The writer must record the account Auto Register signed in.'
		);
	}

	/**
	 * Records a user through the production writer, the way an entry point does.
	 *
	 * @param int $user_id The user to record.
	 */
	private function record_marker_for( int $user_id ) {
		wp_set_current_user( $user_id );
		PurchaseUser::remember_authenticated_user();
		wp_set_current_user( 0 );
	}

	/**
	 * Builds a minimal paid PayPal order carrying a given user id and email.
	 *
	 * @param int    $user_id     The order's user_id column.
	 * @param string $email       The order's email column.
	 * @param int    $customer_id Optional customer id to attach.
	 * @return int
	 */
	private function make_order( int $user_id, string $email, int $customer_id = 0 ): int {
		$args = array(
			'gateway' => 'paypal_commerce',
			'user_id' => $user_id,
			'email'   => $email,
		);

		if ( $customer_id ) {
			$args['customer_id'] = $customer_id;
		}

		return parent::edd()->order->create( $args );
	}
}
