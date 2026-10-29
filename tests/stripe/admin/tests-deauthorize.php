<?php
/**
 * Stripe Connect deauthorization tests.
 *
 * Covers the revocation request itself, and the disconnect handler's rule that local keys are only
 * deleted once Stripe has confirmed the revocation.
 *
 * @package   EDD\Tests\Stripe\Admin
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.7.3
 */

namespace EDD\Tests\Stripe\Admin;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Gateways\Stripe\Admin\Disconnect\Confirmation;
use EDD\Gateways\Stripe\Admin\Disconnect\Deauthorize;
use EDD\Gateways\Stripe\Admin\Disconnect\Notices;

/**
 * Tests for the Stripe Connect deauthorization.
 *
 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Deauthorize
 * @group edd_stripe
 * @group edd_stripe_connect
 */
class Disconnect extends EDD_UnitTestCase {

	/**
	 * The account the store fixture is connected to.
	 */
	const ACCOUNT_ID = 'acct_1TESTCONNECTED';

	/**
	 * The path the revocation is sent to, as owned by the class under test.
	 */
	const ENDPOINT_PATH = '/v2/stripe/deauthorize';

	/**
	 * Administrator fixture.
	 *
	 * @var int
	 */
	protected static $admin_id;

	/**
	 * Shop manager fixture, who can manage the shop but not site options.
	 *
	 * @var int
	 */
	protected static $shop_manager_id;

	/**
	 * Subscriber fixture, who holds none of the capabilities the disconnect needs.
	 *
	 * @var int
	 */
	protected static $subscriber_id;

	/**
	 * Every request the handler made to EDD Connect, in order.
	 *
	 * @var array
	 */
	private $requests = array();

	/**
	 * The response EDD Connect should answer with.
	 *
	 * @var array|\WP_Error
	 */
	private $connect_response;

	/**
	 * Whether Stripe should still accept the store's credential when asked directly.
	 *
	 * @var bool
	 */
	private $stripe_accepts_credential = true;

	/**
	 * The secret keys Stripe should reject, or null to answer for every key alike.
	 *
	 * @var string[]|null
	 */
	private $stripe_rejected_keys = null;

	/**
	 * The original request values the tests overwrite.
	 *
	 * @var array
	 */
	private $original_request = array();

	/**
	 * The request URI the tests overwrite.
	 *
	 * @var string|null
	 */
	private $original_request_uri = null;

	/**
	 * Where the handler sent the merchant.
	 *
	 * @var string
	 */
	private $redirect = '';

	/**
	 * The stored secret keys as they stood when each request to EDD Connect was made.
	 *
	 * @var array
	 */
	private $keys_at_request = array();

	/**
	 * The URL and arguments of every outbound request the test made.
	 *
	 * @var array
	 */
	private $request_args = array();

	/**
	 * The debug log as a test found it, or null when the test left it alone.
	 *
	 * @var array|null
	 */
	private $original_debug_log = null;

	/**
	 * The ignore_user_abort setting the tests overwrite.
	 *
	 * @var bool
	 */
	private $original_ignore_user_abort;

	/**
	 * The time limit the tests overwrite.
	 *
	 * @var string
	 */
	private $original_time_limit;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$admin_id        = $factory->user->create( array( 'role' => 'administrator' ) );
		self::$shop_manager_id = $factory->user->create( array( 'role' => 'shop_manager' ) );
		self::$subscriber_id = $factory->user->create( array( 'role' => 'subscriber' ) );
	}

	public function set_up() {
		parent::set_up();

		/*
		 * WP_Roles::add_cap() writes the roles option but leaves the WP_Role objects already built in
		 * this process untouched, so without this an administrator looks like it holds none of EDD's
		 * capabilities and the notice gate never opens.
		 */
		wp_roles()->for_site();

		// Admin-only files are not loaded in the test context.
		require_once EDDS_PLUGIN_DIR . '/includes/admin/settings/stripe-connect.php';

		$this->requests                  = array();
		$this->keys_at_request           = array();
		$this->request_args              = array();
		$this->original_ignore_user_abort = (bool) ignore_user_abort();
		$this->original_time_limit        = ini_get( 'max_execution_time' );
		$this->connect_response          = $this->success_response();
		$this->stripe_accepts_credential = true;
		$this->stripe_rejected_keys      = null;

		foreach ( array( 'page', 'edds-stripe-disconnect', 'edds-revoke-disconnect', '_wpnonce' ) as $key ) {
			$this->original_request[ $key ] = $_GET[ $key ] ?? null;
		}

		/*
		 * The handler builds its redirect by stripping arguments from the current request, and
		 * REQUEST_URI has no default in the test bootstrap.
		 */
		$this->original_request_uri = $_SERVER['REQUEST_URI'] ?? null;
		$_SERVER['REQUEST_URI']     = '/wp-admin/edit.php?post_type=download&page=edd-settings&tab=gateways&section=edd-stripe&edds-stripe-disconnect=1&edd-action=disconnect_stripe_connect&_wpnonce=abc123';

		$this->redirect = '';

		// Test mode is the active mode, so the live mode is revoked first.
		add_filter( 'edd_is_test_mode', '__return_true' );
		add_filter( 'edd_url_is_production', '__return_true' );
		add_filter( 'pre_http_request', array( $this, 'answer_as_connect' ), 10, 3 );
		add_filter( 'wp_redirect', array( $this, 'capture_redirect' ) );

		$this->connect_store();
	}

	public function tear_down() {
		remove_filter( 'edd_url_is_production', '__return_true' );
		remove_filter( 'edd_url_is_production', '__return_false' );
		remove_filter( 'pre_http_request', array( $this, 'answer_as_connect' ), 10 );
		remove_filter( 'wp_redirect', array( $this, 'capture_redirect' ) );

		if ( is_null( $this->original_request_uri ) ) {
			unset( $_SERVER['REQUEST_URI'] );
		} else {
			$_SERVER['REQUEST_URI'] = $this->original_request_uri;
		}

		foreach ( $this->original_request as $key => $value ) {
			if ( is_null( $value ) ) {
				unset( $_GET[ $key ] );
			} else {
				$_GET[ $key ] = $value;
			}
		}

		foreach ( array( 'stripe_connect_account_id', 'live_secret_key', 'live_publishable_key', 'test_secret_key', 'test_publishable_key', 'gateways', Deauthorize::MODES_SETTING ) as $option ) {
			edd_delete_option( $option );
		}

		ignore_user_abort( $this->original_ignore_user_abort );
		set_time_limit( (int) $this->original_time_limit );

		if ( ! is_null( $this->original_debug_log ) ) {
			$log_file = EDD()->debug_log->get_log_file_path();

			if ( $this->original_debug_log['existed'] ) {
				file_put_contents( $log_file, $this->original_debug_log['contents'] );
			} elseif ( file_exists( $log_file ) ) {
				unlink( $log_file );
			}

			$this->original_debug_log = null;
		}

		Deauthorize::clear_error();
		Deauthorize::clear_unconfirmed();
		wp_set_current_user( 0 );

		parent::tear_down();
	}

	public function test_store_fixture_is_connected_with_both_modes() {
		$this->assertSame( self::ACCOUNT_ID, edd_stripe()->connect()->get_connect_id() );
		$this->assertNotEmpty( edd_get_option( 'live_secret_key' ) );
		$this->assertNotEmpty( edd_get_option( 'test_secret_key' ) );
		$this->assertTrue( ( new Deauthorize() )->is_revocable() );
	}

	public function test_revoke_sends_the_stored_credential_for_each_mode() {
		$this->assertTrue( ( new Deauthorize() )->revoke() );

		$this->assertCount( 2, $this->requests, 'A store connected in both modes has two authorizations to revoke.' );

		$by_mode = array();
		foreach ( $this->requests as $request ) {
			$by_mode[ $request['live_mode'] ] = $request;
		}

		$this->assertSame( self::ACCOUNT_ID, $by_mode[1]['stripe_user_id'] );
		$this->assertSame( 'sk_live_fixture', $by_mode[1]['secret_key'] );
		$this->assertSame( 'sk_test_fixture', $by_mode[0]['secret_key'] );
		$this->assertSame( home_url(), $by_mode[0]['site_url'] );
	}

	public function test_revoke_requires_a_connect_account() {
		edd_delete_option( 'stripe_connect_account_id' );

		$revoked = ( new Deauthorize() )->revoke();

		$this->assertWPError( $revoked );
		$this->assertSame( 'edd_stripe_deauthorize_not_connected', $revoked->get_error_code() );
		$this->assertEmpty( $this->requests, 'A store without a brokered connection has nothing to revoke.' );
	}

	public function test_manual_api_keys_are_not_revocable() {
		edd_delete_option( 'stripe_connect_account_id' );

		$this->assertFalse( ( new Deauthorize() )->is_revocable() );
	}

	public function test_revoke_fails_when_the_credential_belongs_to_another_account() {
		$this->connect_response = array(
			'response' => array(
				'code'    => 403,
				'message' => 'Forbidden',
			),
			'body'     => wp_json_encode(
				array(
					'error' => array(
						'code'    => 'credential_mismatch',
						'message' => 'The supplied key does not belong to this account.',
					),
				)
			),
		);

		$revoked = ( new Deauthorize() )->revoke();

		$this->assertWPError( $revoked );
		$this->assertSame( 'edd_stripe_deauthorize_failed', $revoked->get_error_code() );
		$this->assertStringContainsString( 'does not belong to this account', $revoked->get_error_message() );
	}

	/**
	 * A lost response leaves the outcome unknown, and Stripe still accepting the credential is what
	 * makes it a failure rather than a revocation nobody heard back about.
	 */
	public function test_revoke_reports_an_unconfirmed_revocation_when_connect_cannot_be_reached() {
		$this->connect_response = new \WP_Error( 'http_request_failed', 'Operation timed out' );

		$revoked = ( new Deauthorize() )->revoke();

		$this->assertWPError( $revoked );
		$this->assertSame( 'edd_stripe_deauthorize_unreachable', $revoked->get_error_code() );
		$this->assertStringContainsString( 'Operation timed out', $revoked->get_error_message() );
		$this->assertSame( 'sk_test_fixture', edd_get_option( 'test_secret_key' ), 'A credential Stripe still accepts is kept.' );
	}

	/**
	 * A lost EDD Connect response counts as revoked when Stripe has stopped accepting the key, and
	 * the disconnect goes ahead rather than reporting a failure for a credential that is already dead.
	 */
	public function test_a_lost_response_is_a_revocation_when_stripe_has_stopped_accepting_the_key() {
		$this->connect_response          = new \WP_Error( 'http_request_failed', 'Operation timed out' );
		$this->stripe_accepts_credential = false;

		$this->assertTrue( ( new Deauthorize() )->revoke() );
		$this->assertFalse( edd_get_option( 'test_secret_key' ), 'A dead credential is not kept.' );
		$this->assertFalse( edd_get_option( 'live_secret_key' ) );
	}

	/**
	 * EDD Connect answers a retry with an already-revoked key as a success, but only when it recorded
	 * the key before revoking it. Where it did not, or the record is gone, it answers that the key is
	 * not valid for the account. The key is dead either way, which is what the disconnect was for.
	 */
	public function test_a_rejected_credential_that_stripe_has_killed_is_a_revocation() {
		$this->connect_response          = array(
			'response' => array(
				'code'    => 403,
				'message' => 'Forbidden',
			),
			'body'     => wp_json_encode(
				array(
					'error' => array(
						'code'    => 'credential_mismatch',
						'message' => 'The supplied secret_key is not valid for this Stripe account.',
					),
				)
			),
		);
		$this->stripe_accepts_credential = false;

		$this->assertTrue( ( new Deauthorize() )->revoke() );
		$this->assertFalse( edd_get_option( 'test_secret_key' ) );
	}

	/**
	 * An error raised at the edge is not JSON at all, which is what a merchant gets if the endpoint
	 * is unreachable. It may not reach them as a PHP warning.
	 */
	public function test_revoke_reports_a_response_that_is_not_json() {
		$this->connect_response = array(
			'response' => array(
				'code'    => 502,
				'message' => 'Bad Gateway',
			),
			'body'     => '<html><body><h1>502 Bad Gateway</h1></body></html>',
		);

		$revoked = ( new Deauthorize() )->revoke();

		$this->assertWPError( $revoked );
		$this->assertSame( 'edd_stripe_deauthorize_unavailable', $revoked->get_error_code() );
		$this->assertStringContainsString( "Stripe didn't confirm.", $revoked->get_error_message() );
	}

	public function test_revoke_reports_an_error_body_with_no_code() {
		$this->connect_response = array(
			'response' => array(
				'code'    => 404,
				'message' => 'Not Found',
			),
			'body'     => wp_json_encode(
				array(
					'success' => false,
					'error'   => array(
						'message' => 'Not Found',
						'status'  => 404,
					),
				)
			),
		);

		$revoked = ( new Deauthorize() )->revoke();

		$this->assertWPError( $revoked );
		$this->assertStringContainsString( 'Not Found', $revoked->get_error_message() );
	}

	public function test_revoke_reports_a_throttle_that_names_no_wait() {
		$this->connect_response = $this->throttled_response( 0 );

		$revoked = ( new Deauthorize() )->revoke();

		$this->assertWPError( $revoked );
		$this->assertSame( 'edd_stripe_deauthorize_rate_limited', $revoked->get_error_code() );
		$this->assertCount( 2, $this->requests, 'Each mode asks once.' );
	}

	public function test_revoke_reports_a_short_throttle_without_retrying_it() {
		$this->connect_response = $this->throttled_response( 3 );

		$this->assertNotEmpty( $this->connect_response['body'], 'Fixture: EDD Connect names a three second wait.' );

		$revoked = ( new Deauthorize() )->revoke();

		$this->assertWPError( $revoked );
		$this->assertSame( 'edd_stripe_deauthorize_rate_limited', $revoked->get_error_code() );
		$this->assertCount( 2, $this->requests, 'Each mode asks once and is not retried.' );
	}

	public function test_revoke_reports_a_long_throttle_with_its_wait() {
		$this->connect_response = $this->throttled_response( 900 );

		$revoked = ( new Deauthorize() )->revoke();

		$this->assertWPError( $revoked );
		$this->assertSame( 'edd_stripe_deauthorize_rate_limited', $revoked->get_error_code() );
		$this->assertStringContainsString( 'Too many attempts to turn off the live keys.', $revoked->get_error_message() );
		$this->assertStringNotContainsString( 'try again', $revoked->get_error_message(), 'The wait is data, not message.' );
		$this->assertSame( 'Please try again in 15 minutes.', $revoked->get_error_data()['retry'] ?? '' );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_disconnect_deletes_the_keys_once_stripe_confirms_the_revocation() {
		$this->request_disconnect();

		$this->assertNotEmpty( $this->requests, 'The revocation should have been attempted.' );
		$this->assertFalse( edd_get_option( 'live_secret_key' ) );
		$this->assertFalse( edd_get_option( 'test_secret_key' ) );
		$this->assertFalse( edd_get_option( 'stripe_connect_account_id' ) );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_disconnect_keeps_the_keys_when_the_revocation_fails() {
		$this->connect_response = new \WP_Error( 'http_request_failed', 'Operation timed out' );

		$this->request_disconnect();

		$this->assertSame( 'sk_live_fixture', edd_get_option( 'live_secret_key' ) );
		$this->assertSame( self::ACCOUNT_ID, edd_get_option( 'stripe_connect_account_id' ) );
		$this->assertStringContainsString( 'section=edd-stripe', $this->redirect, 'The failure goes back to the Stripe settings screen.' );
		$this->assertStringNotContainsString( '_wpnonce', $this->redirect );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_failed_revocation_is_reported_to_the_merchant() {
		$this->connect_response = new \WP_Error( 'http_request_failed', 'Operation timed out' );

		$this->request_disconnect();

		$error = Deauthorize::get_error();

		$this->assertSame( 'edd_stripe_deauthorize_unreachable', $error['code'] );
		$this->assertStringContainsString( 'Operation timed out', $error['message'] );
	}

	/**
	 * A store connected in both modes can have one revoked and the other fail. Keeping a key Stripe
	 * has already stopped accepting would leave the gateway enabled on a credential that cannot take
	 * a payment.
	 *
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_revoked_mode_loses_its_keys_even_when_another_mode_fails() {
		$this->connect_response = array(
			$this->success_response(),
			new \WP_Error( 'http_request_failed', 'Operation timed out' ),
		);

		$this->request_disconnect();

		$this->assertFalse( edd_get_option( 'live_secret_key' ), 'The revoked credential is dead, so it is not kept.' );
		$this->assertFalse( edd_get_option( 'live_publishable_key' ) );
		$this->assertSame( 'sk_test_fixture', edd_get_option( 'test_secret_key' ), 'The mode that was not revoked keeps its key.' );
		$this->assertSame( self::ACCOUNT_ID, edd_get_option( 'stripe_connect_account_id' ) );
	}

	/**
	 * The notice is the only thing that tells a merchant their store is still connected, and the
	 * only place the manual fallback and the escape hatch appear.
	 *
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_failed_revocation_renders_a_notice_with_both_ways_out() {
		$this->connect_response = new \WP_Error( 'http_request_failed', 'Operation timed out' );

		$this->request_disconnect();

		$rendered = $this->render_notices();

		$this->assertArrayHasKey( 'edd-stripe-deauthorize-failed', $rendered, 'The failure has to be reported somewhere the merchant will see it.' );

		$notice = $rendered['edd-stripe-deauthorize-failed'];

		$this->assertStringContainsString( 'Operation timed out', $notice, 'The notice names why the revocation failed.' );
		$this->assertStringContainsString( 'so your current keys are still in place', $notice, 'Nothing was removed, so the keys are all still in place.' );
		$this->assertStringContainsString( 'remove the keys from this site only', $notice );
		$this->assertStringContainsString( 'dashboard.stripe.com/settings/apps', $notice, 'The manual fallback is the merchant\'s way to revoke it themselves.' );
		$this->assertStringContainsString( 'edds-stripe-disconnect=1', $notice, 'The way to remove the keys from this site appears nowhere else in the admin.' );
	}

	/**
	 * The notice carries the only link to remove the keys from this site, so it has to outlast the
	 * first screen the merchant lands on.
	 *
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_failed_revocation_notice_is_still_there_on_the_next_load() {
		$this->connect_response = new \WP_Error( 'http_request_failed', 'Operation timed out' );

		$this->request_disconnect();

		$first = $this->render_notices();
		$this->assertArrayHasKey( 'edd-stripe-deauthorize-failed', $first, 'Fixture: the notice renders once.' );

		$second = $this->render_notices();
		$this->assertArrayHasKey( 'edd-stripe-deauthorize-failed', $second, 'Rendering must not consume the record.' );
		$this->assertStringContainsString( 'edds-stripe-disconnect=1', $second['edd-stripe-deauthorize-failed'] );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_local_disconnect_clears_a_recorded_failure() {
		$this->connect_response = new \WP_Error( 'http_request_failed', 'Operation timed out' );
		$this->request_disconnect();
		$this->assertNotFalse( Deauthorize::get_error(), 'Fixture: a failure is recorded.' );

		$this->request_disconnect( array( 'revoke' => false ) );

		$this->assertFalse( edd_get_option( 'stripe_connect_account_id' ), 'Fixture: the local disconnect ran.' );
		$this->assertFalse( Deauthorize::get_error() );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_confirmed_disconnect_clears_a_recorded_failure() {
		$this->connect_response = new \WP_Error( 'http_request_failed', 'Operation timed out' );
		$this->request_disconnect();
		$this->assertNotFalse( Deauthorize::get_error(), 'Fixture: a failure is recorded.' );

		$this->connect_response = $this->success_response();
		$this->request_disconnect();

		$this->assertFalse( edd_get_option( 'stripe_connect_account_id' ), 'Fixture: the disconnect ran.' );
		$this->assertFalse( Deauthorize::get_error() );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_local_disconnect_deletes_the_keys_without_contacting_connect() {
		$this->request_disconnect( array( 'revoke' => false ) );

		$this->assertEmpty( $this->requests, 'A local disconnect does not contact EDD Connect at all.' );
		$this->assertFalse( edd_get_option( 'live_secret_key' ) );
		$this->assertFalse( edd_get_option( 'stripe_connect_account_id' ) );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_disconnect_with_manual_keys_deletes_the_keys_without_contacting_connect() {
		edd_delete_option( 'stripe_connect_account_id' );

		$this->request_disconnect( array( 'revoke' => false ) );

		$this->assertEmpty( $this->requests, 'Keys the merchant entered themselves are not sent for revocation.' );
		$this->assertFalse( edd_get_option( 'live_secret_key' ) );
	}

	/**
	 * A request killed between two modes must leave the store connected. Deleting the first mode's
	 * keys before the second has answered would leave a half-finished disconnect behind.
	 *
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_no_keys_are_deleted_until_every_mode_has_answered() {
		$this->request_disconnect();

		$this->assertCount( 2, $this->keys_at_request, 'Each mode should have been revoked.' );
		foreach ( $this->keys_at_request as $keys ) {
			$this->assertSame( 'sk_live_fixture', $keys['live'], 'The live key is still stored while any request is in flight.' );
			$this->assertSame( 'sk_test_fixture', $keys['test'], 'The test key is still stored while any request is in flight.' );
		}
		$this->assertFalse( edd_get_option( 'live_secret_key' ), 'Both are deleted once every mode has answered.' );
		$this->assertFalse( edd_get_option( 'test_secret_key' ) );
	}

	public function test_revoke_keeps_running_if_the_merchant_closes_the_tab() {
		ignore_user_abort( false );

		( new Deauthorize() )->revoke();

		$this->assertTrue( (bool) ignore_user_abort() );
	}

	public function test_revoke_raises_the_time_limit_for_the_request() {
		set_time_limit( 30 );

		( new Deauthorize() )->revoke();

		$this->assertSame( (string) Deauthorize::TIME_LIMIT, (string) ini_get( 'max_execution_time' ) );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_rejected_key_without_a_confirmed_revocation_is_reported_as_unconfirmed() {
		$this->connect_response          = new \WP_Error( 'http_request_failed', 'Operation timed out' );
		$this->stripe_accepts_credential = false;

		$this->request_disconnect();

		$this->assertFalse( edd_get_option( 'live_secret_key' ), 'The dead keys are still removed.' );
		$this->assertFalse( edd_get_option( 'stripe_connect_account_id' ) );
		$this->assertTrue( Deauthorize::has_unconfirmed() );
		$this->assertFalse( Deauthorize::get_error(), 'It is not reported as a failure.' );
		$this->assertStringContainsString( 'section=edd-stripe', $this->redirect, 'The notice lands on the Stripe settings screen.' );
		$this->assertStringNotContainsString( '_wpnonce', $this->redirect );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_confirmed_revocation_is_not_reported_as_unconfirmed() {
		$this->request_disconnect();

		$this->assertFalse( Deauthorize::has_unconfirmed() );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_an_unconfirmed_revocation_renders_its_own_notice() {
		$this->connect_response          = new \WP_Error( 'http_request_failed', 'Operation timed out' );
		$this->stripe_accepts_credential = false;

		$this->request_disconnect();

		$rendered = $this->render_notices();

		$this->assertArrayNotHasKey( 'edd-stripe-deauthorize-failed', $rendered, 'The keys are gone, so this is not the failure notice.' );
		$this->assertArrayHasKey( 'edd-stripe-deauthorize-unconfirmed', $rendered );
		$this->assertStringContainsString( "We couldn't confirm the old keys were turned off", $rendered['edd-stripe-deauthorize-unconfirmed'] );
		$this->assertStringContainsString( 'dashboard.stripe.com/settings/apps', $rendered['edd-stripe-deauthorize-unconfirmed'] );
		$this->assertFalse( Deauthorize::has_unconfirmed(), 'The record is consumed once it has been rendered.' );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_failed_revocation_redirects_without_the_request_arguments_that_triggered_it() {
		$this->connect_response = new \WP_Error( 'http_request_failed', 'Operation timed out' );

		$this->request_disconnect();

		$this->assertStringContainsString( 'section=edd-stripe', $this->redirect, 'Fixture: the redirect was captured.' );
		$this->assertStringNotContainsString( 'edd-action', $this->redirect );
		$this->assertStringNotContainsString( 'edds-revoke-disconnect', $this->redirect );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_revoked_disconnect_redirects_without_the_request_arguments_that_triggered_it() {
		$this->request_disconnect();

		$this->assertStringContainsString( 'section=edd-stripe', $this->redirect, 'Fixture: the redirect was captured.' );
		$this->assertStringNotContainsString( 'edd-action', $this->redirect );
		$this->assertStringNotContainsString( 'edds-revoke-disconnect', $this->redirect );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_local_disconnect_redirects_without_the_edd_action_that_triggered_it() {
		$this->request_disconnect( array( 'revoke' => false ) );

		$this->assertStringContainsString( 'section=edd-stripe', $this->redirect, 'Fixture: the redirect was captured.' );
		$this->assertStringNotContainsString( 'edd-action', $this->redirect );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Notices::add_notices
	 */
	public function test_the_disconnect_notices_do_not_show_outside_an_edd_admin_page() {
		wp_set_current_user( self::$admin_id );
		Deauthorize::record_unconfirmed();
		$this->connect_response = new \WP_Error( 'http_request_failed', 'Operation timed out' );
		Deauthorize::record_error( $this->connect_response );

		add_filter( 'edd_is_admin_page', '__return_false', 20 );
		$rendered = $this->render_notices();
		remove_filter( 'edd_is_admin_page', '__return_false', 20 );

		$this->assertArrayNotHasKey( 'edd-stripe-deauthorize-failed', $rendered );
		$this->assertArrayNotHasKey( 'edd-stripe-deauthorize-unconfirmed', $rendered );
		$this->assertTrue( Deauthorize::has_unconfirmed(), 'Not rendering did not consume the record.' );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_request_without_a_nonce_leaves_the_store_connected() {
		edd_update_option( 'gateways', array( 'stripe' => true ) );
		$this->assert_store_still_connected();

		$this->request_disconnect( array( 'nonce' => null ) );

		$this->assert_store_still_connected();
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_request_with_a_mismatched_nonce_leaves_the_store_connected() {
		edd_update_option( 'gateways', array( 'stripe' => true ) );
		$this->assert_store_still_connected();

		$this->request_disconnect( array( 'nonce' => wp_create_nonce( 'edds-some-other-action' ) ) );

		$this->assert_store_still_connected();
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_user_without_the_capability_leaves_the_store_connected() {
		edd_update_option( 'gateways', array( 'stripe' => true ) );
		$this->assert_store_still_connected();

		// The nonce is minted for the user making the request, so it is valid and only the capability is missing.
		$this->request_disconnect( array( 'user_id' => self::$subscriber_id ) );

		$this->assert_store_still_connected();
	}

	/**
	 * Ending the request is what keeps the notice from being consumed by this request's own
	 * `admin_init`, so the handler has to stop after each of its redirects.
	 *
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_failed_disconnect_ends_the_request() {
		$this->connect_response = new \WP_Error( 'http_request_failed', 'Operation timed out' );

		$this->assertInstanceOf( \WPDieException::class, $this->request_disconnect() );
		$this->assertNotFalse( Deauthorize::get_error(), 'The failure is still recorded for the next page.' );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_an_unconfirmed_disconnect_ends_the_request_with_its_notice_intact() {
		$this->connect_response          = new \WP_Error( 'http_request_failed', 'Operation timed out' );
		$this->stripe_accepts_credential = false;

		$this->assertInstanceOf( \WPDieException::class, $this->request_disconnect() );
		$this->assertTrue( Deauthorize::has_unconfirmed(), 'The notice is waiting for the next page.' );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_confirmed_disconnect_ends_the_request() {
		$this->assertInstanceOf( \WPDieException::class, $this->request_disconnect() );
		$this->assertFalse( edd_get_option( 'stripe_connect_account_id' ), 'Fixture check: the disconnect ran.' );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_plain_disconnect_removes_the_keys_and_sends_nothing_to_connect() {
		edd_update_option( 'gateways', array( 'stripe' => true ) );
		$this->assertArrayHasKey( 'stripe', edd_get_option( 'gateways', array() ), 'Fixture: Stripe is enabled.' );

		$this->request_disconnect( array( 'revoke' => false ) );

		$this->assertEmpty( $this->requests, 'Disconnect is local and sends nothing to EDD Connect.' );
		$this->assertFalse( edd_get_option( 'live_secret_key' ) );
		$this->assertFalse( edd_get_option( 'test_secret_key' ) );
		$this->assertFalse( edd_get_option( 'stripe_connect_account_id' ) );
		$this->assertFalse( edd_get_option( Deauthorize::MODES_SETTING ) );
		$this->assertArrayNotHasKey( 'stripe', edd_get_option( 'gateways', array() ) );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_revoke_request_contacts_connect_for_each_mode() {
		$this->request_disconnect();

		$this->assertCount( 2, $this->requests, 'The revoke action still revokes.' );
		$this->assertFalse( edd_get_option( 'stripe_connect_account_id' ), 'Fixture: the revoke ran.' );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_revoke_request_on_a_non_production_url_leaves_the_store_connected() {
		edd_update_option( 'gateways', array( 'stripe' => true ) );
		$this->use_non_production_url();

		$result = $this->request_disconnect();

		$this->assertInstanceOf( \WPDieException::class, $result, 'The request ends with a redirect.' );
		$this->assertStringContainsString( 'section=edd-stripe', $this->redirect );
		$this->assertStringNotContainsString( 'edds-revoke-disconnect', $this->redirect );
		$this->assert_store_still_connected();
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_revoke_request_with_nothing_to_revoke_leaves_the_store_connected() {
		$this->make_store_not_revocable();

		$result = $this->request_disconnect();

		$this->assertInstanceOf( \WPDieException::class, $result, 'The request ends with a redirect.' );
		$this->assertStringNotContainsString( 'edds-revoke-disconnect', $this->redirect );
		$this->assertSame( self::ACCOUNT_ID, edd_get_option( 'stripe_connect_account_id' ), 'The store was not disconnected locally.' );
		$this->assertFalse( Deauthorize::get_error(), 'No revocation was attempted, so no failure was recorded.' );
		$this->assertEmpty( $this->requests, 'No revocation was requested.' );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_the_local_nonce_is_rejected_on_the_revoke_action() {
		edd_update_option( 'gateways', array( 'stripe' => true ) );

		// A nonce is bound to the user, so it has to be minted for the one making the request.
		wp_set_current_user( self::$admin_id );
		$this->request_disconnect( array( 'nonce' => wp_create_nonce( 'edds-stripe-connect-disconnect' ) ) );

		$this->assert_store_still_connected();
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_the_revoke_nonce_is_rejected_on_the_local_action() {
		edd_update_option( 'gateways', array( 'stripe' => true ) );

		wp_set_current_user( self::$admin_id );
		$this->request_disconnect(
			array(
				'revoke' => false,
				'nonce'  => wp_create_nonce( 'edds-stripe-connect-revoke' ),
			)
		);

		$this->assert_store_still_connected();
	}

	/**
	 * One mode whose old keys are gone without confirmation, and another that failed outright: the
	 * merchant still has to hear about the first.
	 *
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_failure_still_reports_a_mode_that_was_removed_unconfirmed() {
		$this->connect_response     = new \WP_Error( 'http_request_failed', 'Operation timed out' );
		$this->stripe_rejected_keys = array( 'Bearer sk_live_fixture' );

		$this->request_disconnect();

		$this->assertFalse( edd_get_option( 'live_secret_key' ), 'Fixture: the rejected mode lost its key.' );
		$this->assertSame( 'sk_test_fixture', edd_get_option( 'test_secret_key' ), 'Fixture: the failed mode kept its key.' );

		$rendered = $this->render_notices();

		$this->assertArrayHasKey( 'edd-stripe-deauthorize-failed', $rendered );
		$this->assertArrayNotHasKey( 'edd-stripe-deauthorize-unconfirmed', $rendered, 'One notice tells the whole story.' );
		$this->assertStringContainsString( "We couldn't confirm the old live keys were turned off", $rendered['edd-stripe-deauthorize-failed'] );
		$this->assertStringContainsString( 'Your live Stripe keys were turned off and removed from this site', $rendered['edd-stripe-deauthorize-failed'] );
		$this->assertFalse( Deauthorize::has_unconfirmed() );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Notices::add_notices
	 */
	public function test_the_unconfirmed_notice_says_removing_the_app_turns_stripe_off_for_every_site() {
		$this->connect_response          = new \WP_Error( 'http_request_failed', 'Operation timed out' );
		$this->stripe_accepts_credential = false;

		$this->request_disconnect();

		$notice = $this->render_notices()['edd-stripe-deauthorize-unconfirmed'] ?? '';

		$this->assertStringContainsString( 'You can check in Settings &gt; Installed Apps in your', $notice );
		$this->assertStringContainsString( 'Removing Easy Digital Downloads there turns Stripe off for every site using this account.', $notice );
		$this->assertStringNotContainsString( 'reconnect', str_replace( 'reconnect to get new ones', '', $notice ) );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Notices::add_notices
	 */
	public function test_the_mixed_outcome_notice_says_removing_the_app_turns_stripe_off_for_every_site() {
		$this->connect_response     = new \WP_Error( 'http_request_failed', 'Operation timed out' );
		$this->stripe_rejected_keys = array( 'Bearer sk_live_fixture' );

		$this->request_disconnect();

		$notice = $this->render_notices()['edd-stripe-deauthorize-failed'] ?? '';

		$this->assertStringContainsString( "We couldn't confirm the old live keys were turned off", $notice, 'Fixture: the notice carries the unconfirmed mode.' );
		$this->assertStringContainsString( 'Removing Easy Digital Downloads there turns Stripe off for every site using this account.', $notice );
		$this->assertStringNotContainsString( 'Before you reconnect', $notice );
		$this->assertStringNotContainsString( 'once you have reconnected', $notice );
	}

	/**
	 * Live revoked and confirmed, test failed: the live keys are gone, so the notice may not claim
	 * the current keys are all still in place.
	 *
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_failure_after_a_confirmed_mode_names_the_mode_that_was_disconnected() {
		$this->connect_response = array(
			$this->success_response(),
			new \WP_Error( 'http_request_failed', 'Operation timed out' ),
		);

		$this->assertSame( 'sk_live_fixture', edd_get_option( 'live_secret_key' ), 'Fixture: both modes are connected.' );
		$this->assertSame( 'sk_test_fixture', edd_get_option( 'test_secret_key' ), 'Fixture: both modes are connected.' );

		$this->request_disconnect();

		$this->assertFalse( edd_get_option( 'live_secret_key' ), 'Fixture: the confirmed mode lost its keys.' );
		$this->assertSame( 'sk_test_fixture', edd_get_option( 'test_secret_key' ), 'Fixture: the failed mode kept its keys.' );

		$rendered = $this->render_notices();

		$this->assertArrayHasKey( 'edd-stripe-deauthorize-failed', $rendered );

		$notice = $rendered['edd-stripe-deauthorize-failed'];

		$this->assertStringContainsString( 'Your live Stripe keys were turned off and removed from this site, so reconnect to get new ones.', $notice );
		$this->assertStringContainsString( "We couldn't revoke access for your test keys right now, so they are still in place.", $notice );
		$this->assertStringNotContainsString( 'so your current keys are still in place', $notice );
		$this->assertStringNotContainsString( "couldn't confirm", $notice, 'A confirmed mode needs no dashboard check.' );
		$this->assertStringContainsString( 'Operation timed out', $notice, 'The reason still follows.' );
		$this->assertStringContainsString( 'edds-stripe-disconnect=1', $notice );
	}

	/**
	 * @covers ::edds_stripe_connect_account_info_ajax_response
	 */
	public function test_the_wizard_account_info_offers_no_disconnect_link() {
		$settings = $this->account_info_response( false );
		$wizard   = $this->account_info_response( true );

		$this->assertStringContainsString( 'edds-stripe-disconnect', $settings, 'Fixture: the settings screen offers Disconnect.' );
		$this->assertStringContainsString( 'Reconnect', $wizard, 'The wizard still offers Reconnect.' );
		$this->assertStringNotContainsString( 'edds-stripe-disconnect', $wizard );
		$this->assertStringNotContainsString( 'isconnect', $wizard );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_disconnect_requested_from_the_onboarding_wizard_leaves_the_store_connected() {
		edd_update_option( 'gateways', array( 'stripe' => true ) );
		$this->assert_store_still_connected();

		$this->request_disconnect( array( 'page' => 'edd-onboarding-wizard' ) );

		$this->assert_store_still_connected();
	}

	/**
	 * The live keys are what a live store charges with, so once they are gone Stripe cannot stay
	 * on offer at checkout. The test keys that remain are for the other mode.
	 *
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_failure_that_removed_the_active_modes_keys_disables_stripe() {
		add_filter( 'edd_is_test_mode', '__return_false' );
		edd_update_option( 'gateways', array( 'stripe' => true ) );
		// The active mode is revoked last, so live answers second.
		$this->connect_response = array(
			$this->throttled_response( 900 ),
			$this->success_response(),
		);

		$this->assertFalse( edd_is_test_mode(), 'Fixture: the store is live.' );
		$this->assertArrayHasKey( 'stripe', edd_get_option( 'gateways', array() ), 'Fixture: Stripe is enabled.' );

		$this->request_disconnect();
		remove_filter( 'edd_is_test_mode', '__return_false' );

		$this->assertFalse( edd_get_option( 'live_secret_key' ), 'Fixture: the live keys were removed.' );
		$this->assertSame( 'sk_test_fixture', edd_get_option( 'test_secret_key' ), 'Fixture: the test keys were kept.' );
		$this->assertArrayNotHasKey( 'stripe', edd_get_option( 'gateways', array() ) );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_failure_that_removed_only_the_inactive_modes_keys_leaves_stripe_enabled() {
		add_filter( 'edd_is_test_mode', '__return_false' );
		edd_update_option( 'gateways', array( 'stripe' => true ) );
		// The active mode is revoked last, so test answers first.
		$this->connect_response = array(
			$this->success_response(),
			$this->throttled_response( 900 ),
		);

		$this->assertFalse( edd_is_test_mode(), 'Fixture: the store is live.' );

		$this->request_disconnect();
		remove_filter( 'edd_is_test_mode', '__return_false' );

		$this->assertFalse( edd_get_option( 'test_secret_key' ), 'Fixture: the test keys were removed.' );
		$this->assertSame( 'sk_live_fixture', edd_get_option( 'live_secret_key' ), 'Fixture: the live keys were kept.' );
		$this->assertArrayHasKey( 'stripe', edd_get_option( 'gateways', array() ) );
	}

	public function test_a_recorded_mode_is_the_only_one_sent_for_revocation() {
		edd_update_option( Deauthorize::MODES_SETTING, array( 'live' => self::ACCOUNT_ID ) );

		$this->assertSame( array( 'live' => self::ACCOUNT_ID ), edd_get_option( Deauthorize::MODES_SETTING ), 'Fixture: only live is recorded.' );
		$this->assertSame( 'sk_test_fixture', edd_get_option( 'test_secret_key' ), 'Fixture: a test key is stored anyway.' );

		$this->assertTrue( ( new Deauthorize() )->revoke() );

		$this->assertCount( 1, $this->requests );
		$this->assertSame( 1, $this->requests[0]['live_mode'] );
		$this->assertSame( 'sk_live_fixture', $this->requests[0]['secret_key'] );
	}

	public function test_a_legacy_store_that_reconnects_one_mode_still_revokes_both() {
		$this->assertFalse( edd_get_option( Deauthorize::MODES_SETTING ), 'Fixture: nothing is recorded.' );
		$this->assertNotEmpty( edd_get_option( 'test_secret_key' ), 'Fixture: the test mode holds a key already.' );

		Deauthorize::record_connected_mode( 'live', 'acct_NEW' );

		$this->assertSame(
			array(
				'live' => 'acct_NEW',
				'test' => self::ACCOUNT_ID,
			),
			edd_get_option( Deauthorize::MODES_SETTING ),
			'The mode that was already stored keeps the account stored before this connect.'
		);

		$this->assertTrue( ( new Deauthorize() )->revoke() );
		$this->assertCount( 2, $this->requests, 'Both stored keys are sent.' );
	}

	public function test_a_first_connection_does_not_record_the_modes_entered_by_hand() {
		edd_delete_option( 'stripe_connect_account_id' );
		edd_delete_option( 'live_secret_key' );
		edd_delete_option( 'live_publishable_key' );
		$this->assertSame( 'sk_test_fixture', edd_get_option( 'test_secret_key' ), 'Fixture: a test key was entered by hand.' );
		$this->assertFalse( edd_get_option( Deauthorize::MODES_SETTING ), 'Fixture: nothing is recorded.' );

		Deauthorize::record_connected_mode( 'live', self::ACCOUNT_ID );

		$this->assertSame( array( 'live' => self::ACCOUNT_ID ), edd_get_option( Deauthorize::MODES_SETTING ) );

		// The completion handler now saves the live keys and the account ID.
		edd_update_option( 'live_secret_key', 'sk_live_fixture' );
		edd_update_option( 'stripe_connect_account_id', self::ACCOUNT_ID );

		$this->assertTrue( ( new Deauthorize() )->revoke() );
		$this->assertCount( 1, $this->requests );
		$this->assertSame( 1, $this->requests[0]['live_mode'], 'The key entered by hand is not sent.' );
	}

	public function test_an_existing_record_gains_only_the_issued_mode() {
		edd_update_option( Deauthorize::MODES_SETTING, array( 'live' => self::ACCOUNT_ID ) );
		$this->assertNotEmpty( edd_get_option( 'test_secret_key' ), 'Fixture: a test key is stored.' );

		Deauthorize::record_connected_mode( 'live', self::ACCOUNT_ID );
		$this->assertSame( array( 'live' => self::ACCOUNT_ID ), edd_get_option( Deauthorize::MODES_SETTING ) );

		edd_delete_option( 'live_secret_key' );
		Deauthorize::record_connected_mode( 'test', 'acct_B' );
		$this->assertSame(
			array(
				'live' => self::ACCOUNT_ID,
				'test' => 'acct_B',
			),
			edd_get_option( Deauthorize::MODES_SETTING )
		);
	}

	public function test_each_mode_is_revoked_against_the_account_it_was_connected_to() {
		edd_update_option( Deauthorize::MODES_SETTING, array( 'live' => 'acct_A' ) );
		Deauthorize::record_connected_mode( 'test', 'acct_B' );
		edd_update_option( 'stripe_connect_account_id', 'acct_B' );

		$this->assertSame(
			array(
				'live' => 'acct_A',
				'test' => 'acct_B',
			),
			edd_get_option( Deauthorize::MODES_SETTING ),
			'Fixture: each mode records its own account.'
		);

		$this->assertTrue( ( new Deauthorize() )->revoke() );

		$by_mode = array_column( $this->requests, 'stripe_user_id', 'live_mode' );
		$this->assertSame( 'acct_A', $by_mode[1], 'The live key goes with the account it was issued for.' );
		$this->assertSame( 'acct_B', $by_mode[0] );
	}

	public function test_a_failed_revocation_is_logged_against_the_account_of_its_mode() {
		edd_update_option(
			Deauthorize::MODES_SETTING,
			array(
				'live' => 'acct_A',
				'test' => 'acct_B',
			)
		);
		edd_update_option( 'stripe_connect_account_id', 'acct_B' );
		$this->connect_response = array(
			array(
				'response' => array(
					'code'    => 403,
					'message' => 'Forbidden',
				),
				'body'     => wp_json_encode( array( 'error' => array( 'message' => 'Rejected.' ) ) ),
			),
			$this->success_response(),
		);

		$log_file                 = EDD()->debug_log->get_log_file_path();
		$this->original_debug_log = array(
			'existed'  => file_exists( $log_file ),
			'contents' => file_exists( $log_file ) ? file_get_contents( $log_file ) : '',
		);

		EDD()->debug_log->clear_log_file();
		$this->assertEmpty( EDD()->debug_log->get_file_contents(), 'Fixture: the log is empty.' );

		$revoked = ( new Deauthorize() )->revoke();

		$this->assertWPError( $revoked );
		$this->assertCount( 2, $this->requests, 'Fixture: live failed and test was revoked.' );
		$this->assertStringContainsString( 'revoking the live authorization for acct_A failed', EDD()->debug_log->get_file_contents() );
	}

	public function test_a_mode_that_was_removed_is_no_longer_recorded() {
		$this->connect_response = array(
			$this->success_response(),
			new \WP_Error( 'http_request_failed', 'Operation timed out' ),
		);

		$this->assertFalse( edd_get_option( Deauthorize::MODES_SETTING ), 'Fixture: nothing is recorded.' );

		$revoked = ( new Deauthorize() )->revoke();

		$this->assertWPError( $revoked );
		$this->assertFalse( edd_get_option( 'live_secret_key' ), 'Fixture: the live keys were removed.' );
		$this->assertSame( array( 'test' => self::ACCOUNT_ID ), edd_get_option( Deauthorize::MODES_SETTING ) );
	}

	public function test_a_key_entered_for_a_removed_mode_is_not_sent_on_the_next_disconnect() {
		$this->connect_response = array(
			$this->success_response(),
			new \WP_Error( 'http_request_failed', 'Operation timed out' ),
		);
		( new Deauthorize() )->revoke();

		edd_update_option( 'live_secret_key', 'sk_live_BYHAND' );
		$this->requests         = array();
		$this->connect_response = $this->success_response();

		$this->assertSame( 'sk_live_BYHAND', edd_get_option( 'live_secret_key' ), 'Fixture: a live key was entered by hand.' );
		$this->assertNotEmpty( edd_get_option( 'test_secret_key' ), 'Fixture: the failed mode still holds its key.' );

		$this->assertTrue( ( new Deauthorize() )->revoke() );

		$this->assertCount( 1, $this->requests );
		$this->assertSame( 0, $this->requests[0]['live_mode'], 'Only the mode that failed is sent again.' );
	}

	public function test_the_active_mode_is_revoked_last_in_test_mode() {
		$this->assertTrue( edd_is_test_mode(), 'Fixture: the store is in test mode.' );

		( new Deauthorize() )->revoke();

		$this->assertCount( 2, $this->requests, 'Fixture: both modes were sent.' );
		$this->assertSame( array( 1, 0 ), array_column( $this->requests, 'live_mode' ) );
	}

	public function test_the_active_mode_is_revoked_last_in_live_mode() {
		add_filter( 'edd_is_test_mode', '__return_false' );
		$this->assertFalse( edd_is_test_mode(), 'Fixture: the store is live.' );

		( new Deauthorize() )->revoke();
		remove_filter( 'edd_is_test_mode', '__return_false' );

		$this->assertCount( 2, $this->requests, 'Fixture: both modes were sent.' );
		$this->assertSame( array( 0, 1 ), array_column( $this->requests, 'live_mode' ) );
	}

	public function test_a_success_that_is_not_the_boolean_true_is_not_a_revocation() {
		$this->connect_response = array(
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'body'     => wp_json_encode( array( 'success' => 'false' ) ),
		);

		$this->assertTrue( $this->stripe_accepts_credential, 'Fixture: Stripe still accepts the credential.' );

		$revoked = ( new Deauthorize() )->revoke();

		$this->assertWPError( $revoked );
		$this->assertSame( 'sk_live_fixture', edd_get_option( 'live_secret_key' ) );
	}

	public function test_a_remote_error_message_that_is_not_a_string_is_not_printed() {
		$this->connect_response = array(
			'response' => array(
				'code'    => 403,
				'message' => 'Forbidden',
			),
			'body'     => wp_json_encode( array( 'error' => array( 'message' => array( 'nested' ) ) ) ),
		);

		$revoked = ( new Deauthorize() )->revoke();

		$this->assertWPError( $revoked );
		$this->assertStringContainsString( "Stripe didn't confirm.", $revoked->get_error_message() );
		$this->assertStringNotContainsString( 'Array', $revoked->get_error_message() );
	}

	public function test_requests_carrying_a_secret_key_do_not_follow_redirects() {
		$this->connect_response          = new \WP_Error( 'http_request_failed', 'Operation timed out' );
		$this->stripe_accepts_credential = false;

		( new Deauthorize() )->revoke();

		$urls = array_column( $this->request_args, 'url' );
		$this->assertContains( 'https://api.stripe.com/v1/account', $urls, 'Fixture: Stripe was asked directly.' );
		$this->assertNotEmpty( array_filter( $urls, fn( $url ) => false !== strpos( $url, self::ENDPOINT_PATH ) ), 'Fixture: EDD Connect was asked.' );

		foreach ( $this->request_args as $request ) {
			$this->assertSame( 0, $request['args']['redirection'] ?? null, $request['url'] );
		}
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Notices::add_notices
	 */
	public function test_the_failure_notice_links_to_the_plain_disconnect_url() {
		$this->connect_response = new \WP_Error( 'http_request_failed', 'Operation timed out' );

		$this->request_disconnect();

		$notice = $this->render_notices()['edd-stripe-deauthorize-failed'] ?? '';

		$this->assertSame( 1, preg_match( '/Or <a href="([^"]*)">remove the keys from this site only/', $notice, $matches ), 'Fixture: the notice has the link.' );
		$this->assertSame( html_entity_decode( edds_stripe_connect_disconnect_url(), ENT_QUOTES ), html_entity_decode( $matches[1], ENT_QUOTES ) );

		wp_parse_str( (string) wp_parse_url( html_entity_decode( $matches[1], ENT_QUOTES ), PHP_URL_QUERY ), $query );

		$this->assertNotFalse( wp_verify_nonce( $query['_wpnonce'] ?? '', 'edds-stripe-connect-disconnect' ), 'The link runs the local disconnect directly.' );
		$this->assertArrayNotHasKey( 'edds-revoke-disconnect', $query, 'It never revokes.' );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Notices::add_notices
	 */
	public function test_the_failure_notice_warns_that_removing_the_app_in_stripe_turns_it_off_for_every_site() {
		$this->connect_response = new \WP_Error( 'http_request_failed', 'Operation timed out' );

		$this->request_disconnect();

		$notice = $this->render_notices()['edd-stripe-deauthorize-failed'] ?? '';

		$this->assertStringContainsString( 'remove the keys from this site only', $notice, 'Fixture: the sentence is on the notice.' );
		$this->assertStringContainsString( 'turns Stripe off for every site using this account', $notice );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Notices::add_notices
	 */
	public function test_the_failure_notice_says_access_could_not_be_revoked_not_that_the_site_could_not_be_disconnected() {
		$this->connect_response = new \WP_Error( 'http_request_failed', 'Operation timed out' );

		$this->request_disconnect();

		$notice = $this->render_notices()['edd-stripe-deauthorize-failed'] ?? '';

		$this->assertStringContainsString( "We couldn't revoke access to your Stripe account right now", $notice );
		$this->assertStringNotContainsString( 'disconnected right now', $notice );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Notices::add_notices
	 */
	public function test_the_failure_notice_asks_for_a_retry_on_a_production_url() {
		$this->connect_response = new \WP_Error( 'http_request_failed', 'Operation timed out' );
		$this->request_disconnect();
		$this->assertTrue( \EDD\Utils\URL::is_production_url( home_url() ), 'Fixture: this is a production URL.' );
		$this->assertTrue( Deauthorize::get_error()['retryable'] ?? false, 'Fixture: the failure is retryable.' );

		$notice = $this->render_notices()['edd-stripe-deauthorize-failed'] ?? '';

		$this->assertStringContainsString( 'Please try again in a few minutes.', $notice );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Notices::add_notices
	 */
	public function test_the_failure_notice_for_a_store_with_nothing_to_revoke_leaves_out_the_retry_sentence() {
		$this->connect_response = new \WP_Error( 'http_request_failed', 'Operation timed out' );
		$this->request_disconnect();
		$this->assertTrue( \EDD\Utils\URL::is_production_url( home_url() ), 'Fixture: this is a production URL.' );
		$this->assertTrue( Deauthorize::get_error()['retryable'] ?? false, 'Fixture: the failure is retryable.' );
		$this->make_store_not_revocable();

		$notice = $this->render_notices()['edd-stripe-deauthorize-failed'] ?? '';

		$this->assertStringContainsString( "We couldn't revoke access to your Stripe account right now", $notice, 'The notice itself stays.' );
		$this->assertStringNotContainsString( 'Please try again in a few minutes', $notice );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Notices::add_notices
	 */
	public function test_the_failure_notice_on_a_non_production_url_leaves_out_the_retry_sentence() {
		$this->connect_response = new \WP_Error( 'http_request_failed', 'Operation timed out' );
		$this->request_disconnect();
		$this->assertTrue( Deauthorize::get_error()['retryable'] ?? false, 'Fixture: the failure is retryable.' );
		$this->use_non_production_url();

		$notice = $this->render_notices()['edd-stripe-deauthorize-failed'] ?? '';

		$this->assertStringContainsString( "We couldn't revoke access to your Stripe account right now", $notice, 'The notice itself stays.' );
		$this->assertStringNotContainsString( 'Please try again', $notice );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Notices::add_notices
	 */
	public function test_the_unconfirmed_notice_links_to_the_installed_apps_url() {
		wp_set_current_user( self::$admin_id );
		Deauthorize::record_unconfirmed();

		$notice = $this->render_notices()['edd-stripe-deauthorize-unconfirmed'] ?? '';

		$this->assertStringContainsString( 'href="' . Deauthorize::INSTALLED_APPS_URL . '"', $notice );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Notices::get_subscribed_events
	 */
	public function test_the_disconnect_notices_are_registered_after_the_notice_registry_is_ready() {
		$events = Notices::get_subscribed_events();

		$this->assertSame( 'add_notices', $events['admin_init'][0] ?? null );
		$this->assertGreaterThan( 20, $events['admin_init'][1] ?? 0, 'EDD_Notices::add_notices() runs at priority 20.' );

		$registered = false;
		foreach ( $GLOBALS['wp_filter']['admin_init']->callbacks[ $events['admin_init'][1] ] ?? array() as $callback ) {
			if ( is_array( $callback['function'] ) && $callback['function'][0] instanceof Notices ) {
				$registered = true;
			}
		}

		$this->assertTrue( $registered, 'Core registers the subscriber.' );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_user_who_cannot_manage_options_sees_no_unconfirmed_notice_and_leaves_it_in_place() {
		wp_set_current_user( self::$shop_manager_id );
		Deauthorize::record_unconfirmed();

		$this->assertTrue( Deauthorize::has_unconfirmed(), 'Fixture: the shop manager has a record waiting.' );
		$this->assertFalse( current_user_can( 'manage_options' ), 'Fixture: a shop manager cannot manage options.' );

		$this->assertArrayNotHasKey( 'edd-stripe-deauthorize-unconfirmed', $this->render_notices() );
		$this->assertTrue( Deauthorize::has_unconfirmed(), 'Rendering did not consume it.' );

		Deauthorize::clear_unconfirmed();
	}

	public function test_a_store_with_no_record_revokes_both_modes_against_the_single_account() {
		$this->assertFalse( edd_get_option( Deauthorize::MODES_SETTING ), 'Fixture: nothing is recorded.' );

		$this->assertTrue( ( new Deauthorize() )->revoke() );

		$this->assertSame( array( self::ACCOUNT_ID, self::ACCOUNT_ID ), array_column( $this->requests, 'stripe_user_id' ) );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_throttle_on_either_mode_leaves_the_wait_to_the_detail_line() {
		$this->connect_response = array(
			new \WP_Error( 'http_request_failed', 'Operation timed out' ),
			$this->throttled_response( 900 ),
		);

		$this->request_disconnect();

		$notice = $this->render_notices()['edd-stripe-deauthorize-failed'] ?? '';

		$this->assertStringContainsString( 'Operation timed out', $notice, 'Fixture: the first mode failed for another reason.' );
		$this->assertStringContainsString( '15 minutes', $notice, 'Fixture: the second mode was throttled.' );
		$this->assertStringNotContainsString( 'a few minutes', $notice );
	}

	public function test_a_store_with_no_recorded_modes_sends_every_stored_key() {
		$this->assertFalse( edd_get_option( Deauthorize::MODES_SETTING ), 'Fixture: nothing is recorded.' );

		$this->assertTrue( ( new Deauthorize() )->revoke() );

		$modes = array_column( $this->requests, 'live_mode' );
		sort( $modes );
		$this->assertSame( array( 0, 1 ), $modes );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_disconnect_clears_the_recorded_modes() {
		edd_update_option( Deauthorize::MODES_SETTING, array( 'live' ) );
		$this->assertNotFalse( edd_get_option( Deauthorize::MODES_SETTING ), 'Fixture: a mode is recorded.' );

		$this->request_disconnect( array( 'revoke' => false ) );

		$this->assertFalse( edd_get_option( 'stripe_connect_account_id' ), 'Fixture: the disconnect ran.' );
		$this->assertFalse( edd_get_option( Deauthorize::MODES_SETTING ) );
	}

	/**
	 * The throttle's own message gives the wait, so the notice may not also say a few minutes.
	 *
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_throttle_notice_leaves_the_wait_to_the_detail_line() {
		$this->connect_response = $this->throttled_response( 900 );

		$this->request_disconnect();

		$notice = $this->render_notices()['edd-stripe-deauthorize-failed'] ?? '';

		$this->assertStringContainsString( '15 minutes', $notice, 'Fixture: the detail line names the wait.' );
		$this->assertStringNotContainsString( 'a few minutes', $notice );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Notices::add_notices
	 */
	public function test_a_throttle_notice_on_a_non_production_url_gives_the_reason_without_a_wait() {
		$this->connect_response = $this->throttled_response( 900 );
		$this->request_disconnect();
		$this->assertTrue( Deauthorize::get_error()['throttled'] ?? false, 'Fixture: the failure is a throttle.' );
		$this->assertSame( 'Please try again in 15 minutes.', Deauthorize::get_error()['retry'] ?? '', 'Fixture: the wait is recorded.' );
		$this->use_non_production_url();

		$notice = $this->render_notices()['edd-stripe-deauthorize-failed'] ?? '';

		$this->assertStringContainsString( 'Too many attempts to turn off the live keys.', $notice );
		$this->assertStringNotContainsString( 'try again', $notice );
		$this->assertStringNotContainsString( '15 minutes', $notice );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Notices::add_notices
	 */
	public function test_a_throttle_notice_on_a_production_url_keeps_its_wait_in_minutes() {
		$this->connect_response = $this->throttled_response( 900 );
		$this->request_disconnect();
		$this->assertTrue( \EDD\Utils\URL::is_production_url( home_url() ), 'Fixture: this is a production URL.' );

		$notice = $this->render_notices()['edd-stripe-deauthorize-failed'] ?? '';

		$this->assertStringContainsString( 'Too many attempts to turn off the test keys. Please try again in 15 minutes.', $notice );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Notices::add_notices
	 */
	public function test_a_short_throttle_notice_on_a_production_url_says_to_try_again_shortly() {
		$this->connect_response = $this->throttled_response( 3 );
		$this->request_disconnect();
		$this->assertTrue( \EDD\Utils\URL::is_production_url( home_url() ), 'Fixture: this is a production URL.' );
		$this->assertSame( 'Please try again shortly.', Deauthorize::get_error()['retry'] ?? '', 'Fixture: the wait is recorded.' );

		$notice = $this->render_notices()['edd-stripe-deauthorize-failed'] ?? '';

		$this->assertStringContainsString( 'Too many attempts to turn off the test keys. Please try again shortly.', $notice );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_throttle_notice_after_a_removed_mode_leaves_the_wait_to_the_detail_line() {
		$this->connect_response = array(
			$this->success_response(),
			$this->throttled_response( 900 ),
		);

		$this->request_disconnect();

		$notice = $this->render_notices()['edd-stripe-deauthorize-failed'] ?? '';

		$this->assertStringContainsString( 'Your live Stripe keys were turned off and removed from this site', $notice, 'Fixture: this is the mixed notice.' );
		$this->assertStringContainsString( '15 minutes', $notice, 'Fixture: the detail line names the wait.' );
		$this->assertStringNotContainsString( 'a few minutes', $notice );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_an_unreachable_connect_suggests_trying_again() {
		$this->connect_response = new \WP_Error( 'http_request_failed', 'Operation timed out' );

		$this->request_disconnect();

		$this->assertStringContainsString( 'a few minutes', $this->render_notices()['edd-stripe-deauthorize-failed'] ?? '' );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_server_error_from_connect_suggests_trying_again() {
		$this->connect_response = array(
			'response' => array(
				'code'    => 502,
				'message' => 'Bad Gateway',
			),
			'body'     => '<html><body><h1>502 Bad Gateway</h1></body></html>',
		);

		$this->request_disconnect();

		$this->assertSame( 'edd_stripe_deauthorize_unavailable', Deauthorize::get_error()['code'], 'Fixture: the failure is the server error.' );
		$this->assertStringContainsString( 'a few minutes', $this->render_notices()['edd-stripe-deauthorize-failed'] ?? '' );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_rejection_a_retry_cannot_change_does_not_suggest_trying_again() {
		$this->connect_response = array(
			'response' => array(
				'code'    => 403,
				'message' => 'Forbidden',
			),
			'body'     => wp_json_encode(
				array(
					'error' => array(
						'code'    => 'credential_mismatch',
						'message' => 'The supplied key does not belong to this account.',
					),
				)
			),
		);

		$this->request_disconnect();

		$this->assertSame( 'edd_stripe_deauthorize_failed', Deauthorize::get_error()['code'], 'Fixture: the failure is a rejection.' );

		$notice = $this->render_notices()['edd-stripe-deauthorize-failed'] ?? '';

		$this->assertStringContainsString( 'does not belong to this account', $notice, 'Fixture: the notice rendered.' );
		$this->assertStringNotContainsString( 'a few minutes', $notice );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_user_who_cannot_manage_options_sees_no_failure_notice() {
		$this->connect_response = new \WP_Error( 'http_request_failed', 'Operation timed out' );

		$this->request_disconnect();

		$this->assertArrayHasKey( 'edd-stripe-deauthorize-failed', $this->render_notices(), 'Fixture: an administrator sees the notice.' );

		wp_set_current_user( self::$shop_manager_id );

		$this->assertFalse( current_user_can( 'manage_options' ), 'Fixture: a shop manager cannot manage options.' );
		$this->assertNotFalse( Deauthorize::get_error(), 'Fixture: the failure is still recorded.' );
		$this->assertArrayNotHasKey( 'edd-stripe-deauthorize-failed', $this->render_notices() );
	}

	/**
	 * Callers of the method from 3.1.1 keep working, and the wizard still offers no Disconnect.
	 *
	 * @covers \EDD\Admin\Onboarding\Ajax::disconnect_url
	 */
	public function test_the_wizard_disconnect_url_method_is_deprecated_and_adds_no_filter() {
		$this->setExpectedDeprecated( 'EDD\Admin\Onboarding\Ajax::disconnect_url' );
		add_filter( 'edd_deprecated_function_trigger_error', '__return_false' );

		$this->assertFalse( has_filter( 'edds_stripe_connect_disconnect_url' ), 'Fixture: nothing filters the URL yet.' );

		( new \EDD\Admin\Onboarding\Ajax() )->disconnect_url();

		remove_filter( 'edd_deprecated_function_trigger_error', '__return_false' );

		$this->assertFalse( has_filter( 'edds_stripe_connect_disconnect_url' ) );
		$this->assertArrayNotHasKey( 'wp_ajax_edds_stripe_connect_account_info', \EDD\Admin\Onboarding\Ajax::get_subscribed_events() );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_local_disconnect_on_a_revocable_store_removes_the_keys_without_contacting_edd_connect() {
		edd_update_option( 'gateways', array( 'stripe' => true ) );

		$this->assertTrue( ( new Deauthorize() )->is_revocable(), 'Fixture: the store holds an authorization to revoke.' );

		$this->request_disconnect( array( 'revoke' => false ) );

		$this->assertEmpty( $this->requests, 'A local disconnect does not contact EDD Connect.' );
		$this->assertFalse( edd_get_option( 'stripe_connect_account_id' ) );
		$this->assertArrayNotHasKey( 'stripe', edd_get_option( 'gateways', array() ) );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_revoke_request_sends_both_modes_to_edd_connect() {
		$this->assertTrue( ( new Deauthorize() )->is_revocable(), 'Fixture: the store holds an authorization to revoke.' );

		$this->request_disconnect();

		$this->assertCount( 2, $this->requests, 'Both modes were sent for revocation on the first request.' );
	}

	/**
	 * @covers ::edds_stripe_connect_process_disconnect
	 */
	public function test_a_revoke_request_removes_the_connection_once_stripe_confirms() {
		$this->assertNotEmpty( edd_get_option( 'stripe_connect_account_id' ), 'Fixture: the store is connected.' );

		$this->request_disconnect();

		$this->assertFalse( edd_get_option( 'stripe_connect_account_id' ) );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Confirmation::render
	 */
	public function test_the_dialog_is_printed_for_a_revocable_store() {
		$this->assertTrue( ( new Deauthorize() )->is_revocable(), 'Fixture: the store holds an authorization to revoke.' );

		$html = $this->render_dialog();

		$this->assertStringContainsString( '<dialog', $html );
		$this->assertStringContainsString( 'id="edds-stripe-disconnect-dialog"', $html );
		$this->assertStringContainsString( 'class="edd-modal edd-modal--confirm"', $html );
		$this->assertStringContainsString( 'other sites using this Stripe account are unaffected.', $html );
		$this->assertStringContainsString( 'Each of them stops taking Stripe payments right away until it reconnects.', $html );
		$this->assertStringContainsString( 'edd-modal__revoke', $html, 'A revocable store is offered the revoke link.' );
		$this->assertStringContainsString( '>Revoke for every site</a>', $html );
		$this->assertStringNotContainsString( 'edd-modal__cancel', $html, 'The close button already dismisses the dialog.' );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Confirmation::render
	 */
	public function test_the_dialog_for_a_store_with_nothing_to_revoke_offers_disconnect_only() {
		$this->make_store_not_revocable();

		$html = $this->render_dialog();

		$this->assertStringContainsString( 'id="edds-stripe-disconnect-dialog"', $html );
		$this->assertStringContainsString( '>Disconnect this site</a>', $html );
		$this->assertStringNotContainsString( 'edd-modal__revoke', $html );
		$this->assertStringNotContainsString( 'edds-revoke-disconnect', $html );
		$this->assertStringNotContainsString( 'Each of them stops taking Stripe payments', $html, 'The revoke explanation has nothing to explain.' );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Confirmation::render
	 */
	public function test_the_dialog_on_a_non_production_url_offers_disconnect_only() {
		$this->assertTrue( ( new Deauthorize() )->is_revocable(), 'Fixture: the store holds an authorization to revoke.' );
		$this->use_non_production_url();

		$html = $this->render_dialog();

		$this->assertStringContainsString( '>Disconnect this site</a>', $html );
		$this->assertStringContainsString( 'Installed Apps in your Stripe dashboard', $html, 'The Installed Apps paragraph stays.' );
		$this->assertStringContainsString( 'Revoking is available from the production site.', $html );
		$this->assertStringNotContainsString( 'edd-modal__revoke', $html );
		$this->assertStringNotContainsString( 'edds-revoke-disconnect', $html );
		$this->assertStringNotContainsString( 'Each of them stops taking Stripe payments', $html );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Confirmation::render
	 */
	public function test_the_dialog_for_a_non_revocable_store_on_a_non_production_url_offers_no_revoke_copy() {
		$this->make_store_not_revocable();
		$this->use_non_production_url();

		$html = $this->render_dialog();

		$this->assertStringContainsString( '>Disconnect this site</a>', $html );
		$this->assertStringNotContainsString( 'Revoking is available from the production site.', $html );
		$this->assertStringNotContainsString( 'Each of them stops taking Stripe payments', $html );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Confirmation::render
	 */
	public function test_the_revoke_copy_names_every_other_connected_site() {
		$this->assertTrue( \EDD\Utils\URL::is_production_url( home_url() ), 'Fixture: this is a production URL.' );

		$html = $this->render_dialog();

		$this->assertStringContainsString( 'this site and every other site connected to this Stripe account, such as a staging copy', $html );
		$this->assertStringNotContainsString( 'copied from', $html );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Confirmation::render
	 */
	public function test_the_dialogs_disconnect_link_carries_the_disconnect_nonce_only() {
		$query = $this->get_dialog_link_query( 'class="button button-primary" href="([^"]*)"' );

		$this->assertSame( '1', $query['edds-stripe-disconnect'] ?? null );
		$this->assertSame( 'disconnect_stripe_connect', $query['edd-action'] ?? null );
		$this->assertNotFalse( wp_verify_nonce( $query['_wpnonce'] ?? '', 'edds-stripe-connect-disconnect' ) );
		$this->assertArrayNotHasKey( 'edds-revoke-disconnect', $query, 'The primary link does not revoke.' );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Confirmation::render
	 */
	public function test_the_dialogs_revoke_link_carries_its_own_nonce() {
		$query = $this->get_dialog_link_query( 'class="button edd-modal__revoke" href="([^"]*)"' );

		$this->assertSame( '1', $query['edds-revoke-disconnect'] ?? null, 'The link revokes.' );
		$this->assertSame( '1', $query['edds-stripe-disconnect'] ?? null );
		$this->assertSame( 'disconnect_stripe_connect', $query['edd-action'] ?? null );
		$this->assertNotFalse( wp_verify_nonce( $query['_wpnonce'] ?? '', 'edds-stripe-connect-revoke' ) );
		$this->assertFalse( wp_verify_nonce( $query['_wpnonce'] ?? '', 'edds-stripe-connect-disconnect' ), 'The local nonce is a different one.' );
	}

	/**
	 * @covers ::edds_stripe_connect_account_info_ajax_response
	 */
	public function test_the_account_info_disconnect_button_opens_the_dialog_for_a_revocable_store() {
		$message = json_decode( $this->account_info_response( false ), true )['data']['message'] ?? '';

		$this->assertStringContainsString( '<button type="button" class="button-link" data-dialog-id="edds-stripe-disconnect-dialog">', $message, 'The button survives the allowlist.' );
	}

	/**
	 * @covers ::edds_stripe_connect_account_info_ajax_response
	 */
	public function test_the_account_info_disconnect_button_opens_the_dialog_for_a_store_with_nothing_to_revoke() {
		$this->make_store_not_revocable();

		$message = json_decode( $this->account_info_response( false ), true )['data']['message'] ?? '';

		$this->assertStringContainsString( '<button type="button" class="button-link" data-dialog-id="edds-stripe-disconnect-dialog">', $message, 'Every Disconnect button has a dialog to open.' );
		$this->assertStringNotContainsString( 'edds-stripe-disconnect=1', $message, 'The disconnect URL is only in the dialog.' );
	}

	/**
	 * @covers ::edds_stripe_connect_account_info_ajax_response
	 */
	public function test_the_connected_account_actions_disconnect_button_keeps_its_dialog_trigger() {
		$response = json_decode( $this->account_info_response( false, 'actions' ), true );
		$markup   = $response['data']['actions'] ?? '';

		$this->assertStringContainsString( '<button type="button" class="button-link" data-dialog-id="edds-stripe-disconnect-dialog">', $markup );
	}

	/**
	 * @covers ::edds_stripe_connect_account_info_ajax_response
	 */
	public function test_the_temporary_account_warning_disconnect_button_keeps_its_dialog_trigger() {
		$response = json_decode( $this->account_info_response( false, 'dev' ), true );
		$markup   = $response['data']['message'] ?? '';

		$this->assertStringContainsString( '<button type="button" class="button-link" data-dialog-id="edds-stripe-disconnect-dialog">', $markup );
	}

	/**
	 * @covers ::edds_stripe_connect_account_info_ajax_response
	 */
	public function test_the_rejected_key_message_disconnect_button_keeps_its_dialog_trigger() {
		$response = json_decode( $this->account_info_response( false, 'auth' ), true );
		$markup   = $response['data']['message'] ?? '';

		$this->assertStringContainsString( '<button type="button" class="button-link" data-dialog-id="edds-stripe-disconnect-dialog">', $markup );
	}

	/**
	 * @covers ::edds_stripe_connect_account_info_ajax_response
	 */
	public function test_the_general_error_message_disconnect_button_keeps_its_dialog_trigger() {
		$response = json_decode( $this->account_info_response( false, 'general' ), true );
		$markup   = $response['data']['message'] ?? '';

		$this->assertStringContainsString( '<button type="button" class="button-link" data-dialog-id="edds-stripe-disconnect-dialog">', $markup );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Disconnect\Notices::add_notices
	 */
	public function test_a_user_with_manage_options_alone_sees_the_failure_notice() {
		$this->connect_response = new \WP_Error( 'http_request_failed', 'Operation timed out' );

		$this->request_disconnect();

		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		( new \WP_User( $user_id ) )->add_cap( 'manage_options' );
		wp_set_current_user( $user_id );

		$this->assertTrue( current_user_can( 'manage_options' ), 'Fixture: the user can manage options.' );
		$this->assertFalse( current_user_can( 'manage_shop_settings' ), 'Fixture: the user holds no shop capability.' );
		$this->assertNotFalse( Deauthorize::get_error(), 'Fixture: the failure is recorded.' );

		$this->assertArrayHasKey( 'edd-stripe-deauthorize-failed', $this->render_notices() );
	}

	/**
	 * Record where the handler redirected, and stop the redirect itself.
	 *
	 * Returning false keeps `wp_redirect()` away from `header()`, which a test process has already
	 * sent output past. The request's end is `edd_die()`, which becomes a `WPDieException` in tests.
	 *
	 * @param string $location Where the handler is sending the merchant.
	 * @return false
	 */
	public function capture_redirect( $location ) {
		$this->redirect = $location;

		return false;
	}

	/**
	 * Stand in for EDD Connect.
	 *
	 * @param false|array|\WP_Error $preempt The preempted response.
	 * @param array                 $args    The request arguments.
	 * @param string                $url     The request URL.
	 * @return array|\WP_Error
	 */
	public function answer_as_connect( $preempt, $args, $url ) {
		$this->request_args[] = array(
			'url'  => $url,
			'args' => $args,
		);

		// Stripe, asked directly whether it still accepts the credential.
		if ( false !== strpos( $url, 'api.stripe.com/v1/account' ) ) {
			$accepts = is_null( $this->stripe_rejected_keys )
				? $this->stripe_accepts_credential
				: ! in_array( $args['headers']['Authorization'] ?? '', $this->stripe_rejected_keys, true );

			return array(
				'response' => array(
					'code'    => $accepts ? 200 : 401,
					'message' => $accepts ? 'OK' : 'Unauthorized',
				),
				'body'     => wp_json_encode(
					$accepts
						? array( 'id' => self::ACCOUNT_ID )
						: array( 'error' => array( 'message' => 'Expired API Key provided.' ) )
				),
			);
		}

		// Nothing else may leave the test process.
		if ( false === strpos( $url, self::ENDPOINT_PATH ) ) {
			return new \WP_Error( 'edd_test_unexpected_request', 'Unexpected outbound request to ' . $url );
		}

		$this->requests[]        = json_decode( $args['body'], true );
		$this->keys_at_request[] = array(
			'live' => edd_get_option( 'live_secret_key' ),
			'test' => edd_get_option( 'test_secret_key' ),
		);

		// A test may queue a sequence of answers; the last one stands for every request after it.
		if ( is_array( $this->connect_response ) && isset( $this->connect_response[0] ) ) {
			return count( $this->connect_response ) > 1
				? array_shift( $this->connect_response )
				: $this->connect_response[0];
		}

		return $this->connect_response;
	}

	/**
	 * Connect the store fixture in both modes, the way the completion handler would have.
	 */
	private function connect_store() {
		edd_update_option( 'stripe_connect_account_id', self::ACCOUNT_ID );
		edd_update_option( 'live_secret_key', 'sk_live_fixture' );
		edd_update_option( 'live_publishable_key', 'pk_live_fixture' );
		edd_update_option( 'test_secret_key', 'sk_test_fixture' );
		edd_update_option( 'test_publishable_key', 'pk_test_fixture' );
	}

	/**
	 * Request the disconnect the way a link on the settings screen does.
	 *
	 * Returns the exception that ended the request, or null if the handler returned.
	 *
	 * @param array $args {
	 *     Optional. What to change about the request.
	 *
	 *     @type int         $user_id   The user making the request.
	 *     @type string|null $nonce     The nonce to send, 'valid' for the real one, or null to send none.
	 *     @type string      $page      The admin page the request arrives on.
	 * }
	 */
	private function request_disconnect( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'user_id'   => self::$admin_id,
				'nonce'     => 'valid',
				'revoke'    => true,
				'page'      => 'edd-settings',
			)
		);

		wp_set_current_user( $args['user_id'] );

		$_GET['page']                   = $args['page'];
		$_GET['edds-stripe-disconnect'] = '1';

		if ( is_null( $args['nonce'] ) ) {
			unset( $_GET['_wpnonce'] );
		} else {
			$nonce_action     = $args['revoke'] ? 'edds-stripe-connect-revoke' : 'edds-stripe-connect-disconnect';
			$_GET['_wpnonce'] = 'valid' === $args['nonce'] ? wp_create_nonce( $nonce_action ) : $args['nonce'];

			// The handler builds its links from the request URI, which carries the nonce the link did.
			$_SERVER['REQUEST_URI'] = '/wp-admin/edit.php?post_type=download&page=edd-settings&tab=gateways&section=edd-stripe&edds-stripe-disconnect=1&edd-action=disconnect_stripe_connect&_wpnonce=' . $_GET['_wpnonce'] . ( $args['revoke'] ? '&edds-revoke-disconnect=1' : '' );
		}

		if ( $args['revoke'] ) {
			$_GET['edds-revoke-disconnect'] = '1';
		} else {
			unset( $_GET['edds-revoke-disconnect'] );
		}

		try {
			edds_stripe_connect_process_disconnect();
		} catch ( \WPDieException $e ) {
			return $e;
		}

		return null;
	}

	/**
	 * Makes the site read as a staging copy, and asserts it does.
	 */
	private function use_non_production_url() {
		remove_filter( 'edd_url_is_production', '__return_true' );
		add_filter( 'edd_url_is_production', '__return_false' );

		$this->assertFalse( \EDD\Utils\URL::is_production_url( home_url() ), 'Fixture: this is not a production URL.' );
	}

	/**
	 * Leaves the store connected, but with no stored secret key to revoke.
	 */
	private function make_store_not_revocable() {
		edd_delete_option( 'live_secret_key' );
		edd_delete_option( 'test_secret_key' );

		$this->assertNotEmpty( edd_get_option( 'stripe_connect_account_id' ), 'Fixture: the store is still connected.' );
		$this->assertFalse( ( new Deauthorize() )->is_revocable(), 'Fixture: nothing is revocable.' );
	}

	/**
	 * Prints the confirmation dialog as an administrator and returns the markup.
	 *
	 * @return string
	 */
	private function render_dialog() {
		wp_set_current_user( self::$admin_id );

		ob_start();
		Confirmation::render();

		return ob_get_clean();
	}

	/**
	 * Gets the query arguments of a link in the confirmation dialog.
	 *
	 * @param string $pattern The regex, without delimiters, with the href as its first group.
	 * @return array
	 */
	private function get_dialog_link_query( $pattern ) {
		$this->assertSame( 1, preg_match( '/' . $pattern . '/', $this->render_dialog(), $matches ), 'Fixture: the dialog has the link.' );

		wp_parse_str( (string) wp_parse_url( html_entity_decode( $matches[1], ENT_QUOTES ), PHP_URL_QUERY ), $query );

		return $query;
	}

	/**
	 * Runs the Stripe account information AJAX handler and returns its response.
	 *
	 * The scenario picks which branch of the handler answers: a rejected key (`auth`), any other
	 * failure (`general`), an account with no email or display name (`dev`), or one with both (`actions`).
	 *
	 * @param bool   $in_wizard Whether the request comes from the onboarding wizard.
	 * @param string $scenario  One of auth, general, dev or actions.
	 * @return string
	 */
	private function account_info_response( $in_wizard, $scenario = 'auth' ) {
		require_once EDDS_PLUGIN_DIR . '/includes/admin/settings/stripe-connect.php';

		wp_set_current_user( self::$admin_id );

		$original_post = array(
			'nonce'            => $_POST['nonce'] ?? null,
			'accountId'        => $_POST['accountId'] ?? null,
			'onboardingWizard' => $_POST['onboardingWizard'] ?? null,
		);

		$_POST['nonce']     = wp_create_nonce( 'edds-stripe-connect-account-information' );
		$_POST['accountId'] = self::ACCOUNT_ID;

		if ( $in_wizard ) {
			$_POST['onboardingWizard'] = '1';
		} else {
			unset( $_POST['onboardingWizard'] );
		}

		$reject_the_key = function () use ( $scenario ) {
			if ( 'general' === $scenario ) {
				throw new \Exception( 'Stripe is unreachable.' );
			}

			throw new \EDD\Vendor\Stripe\Exception\AuthenticationException( 'Invalid API Key provided.' );
		};

		// The dev and actions scenarios let the retrieve through and answer it with an account.
		$account = 'actions' === $scenario
			? array(
				'id'    => self::ACCOUNT_ID,
				'email' => 'owner@example.com',
			)
			: array( 'id' => self::ACCOUNT_ID );

		if ( in_array( $scenario, array( 'dev', 'actions' ), true ) ) {
			\EDD\Vendor\Stripe\ApiRequestor::setHttpClient(
				new class( $account ) implements \EDD\Vendor\Stripe\HttpClient\ClientInterface {
					private $account;

					public function __construct( array $account ) {
						$this->account = $account;
					}

					public function request( $method, $abs_url, $headers, $params, $has_file ) {
						return array( wp_json_encode( $this->account ), 200, array() );
					}
				}
			);
		} else {
			add_action( 'edds_pre_stripe_api_request', $reject_the_key );
		}
		add_filter( 'wp_doing_ajax', '__return_true' );

		// An Error, so the handler's own catch of \Exception cannot swallow the end of the response.
		$end_the_request = function () {
			return function () {
				throw new class( 'ajax response sent' ) extends \Error {};
			};
		};
		add_filter( 'wp_die_ajax_handler', $end_the_request );

		ob_start();
		try {
			edds_stripe_connect_account_info_ajax_response();
		} catch ( \Error $e ) {
			// The response has been written.
			unset( $e );
		}
		$output = ob_get_clean();

		remove_action( 'edds_pre_stripe_api_request', $reject_the_key );
		\EDD\Vendor\Stripe\ApiRequestor::setHttpClient( null );
		remove_filter( 'wp_doing_ajax', '__return_true' );
		remove_filter( 'wp_die_ajax_handler', $end_the_request );

		foreach ( $original_post as $key => $value ) {
			if ( is_null( $value ) ) {
				unset( $_POST[ $key ] );
			} else {
				$_POST[ $key ] = $value;
			}
		}

		return $output;
	}

	/**
	 * Assert the store is as connected as the fixture left it.
	 */
	private function assert_store_still_connected() {
		$this->assertSame( 'sk_live_fixture', edd_get_option( 'live_secret_key' ) );
		$this->assertSame( 'sk_test_fixture', edd_get_option( 'test_secret_key' ) );
		$this->assertSame( self::ACCOUNT_ID, edd_get_option( 'stripe_connect_account_id' ) );
		$this->assertArrayHasKey( 'stripe', edd_get_option( 'gateways', array() ), 'Stripe stays an enabled gateway.' );
		$this->assertEmpty( $this->requests, 'No revocation was requested.' );
	}

	/**
	 * Render the admin notices the way an EDD admin screen does, and return them.
	 *
	 * The notice gate is an admin-screen check, which a test process is not on. Only the
	 * unqualified call is forced: the gate also asks whether this is the dashboard, and
	 * answering yes to that would close it again.
	 *
	 * @return array
	 */
	private function render_notices() {
		require_once EDD_PLUGIN_DIR . 'includes/admin/class-edd-notices.php';

		$on_an_edd_page = function ( $result, $page, $view, $passed_page ) {
			return empty( $passed_page ) ? true : $result;
		};
		add_filter( 'edd_is_admin_page', $on_an_edd_page, 10, 4 );

		$notices        = new \EDD_Notices();
		$original_owner = EDD()->notices;
		EDD()->notices  = $notices;

		$notices->add_notices();
		( new Notices() )->add_notices();

		EDD()->notices = $original_owner;
		remove_filter( 'edd_is_admin_page', $on_an_edd_page, 10 );

		$property = new \ReflectionProperty( \EDD_Notices::class, 'notices' );
		$property->setAccessible( true );

		return $property->getValue( $notices );
	}

	/**
	 * The response EDD Connect gives when the account's revocations are throttled.
	 *
	 * @param int $retry_after Seconds until the next attempt is allowed.
	 * @return array
	 */
	private function throttled_response( $retry_after ) {
		return array(
			'response' => array(
				'code'    => 429,
				'message' => 'Too Many Requests',
			),
			'body'     => wp_json_encode(
				array(
					'error' => array(
						'code'        => 'rate_limited',
						'message'     => 'Too many revocation attempts for this account.',
						'retry_after' => $retry_after,
					),
				)
			),
		);
	}

	/**
	 * The response EDD Connect gives for a completed revocation.
	 *
	 * @param bool $already_revoked Whether the authorization was already gone.
	 * @return array
	 */
	private function success_response( $already_revoked = false ) {
		return array(
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'body'     => wp_json_encode(
				array(
					'success'         => true,
					'stripe_user_id'  => self::ACCOUNT_ID,
					'already_revoked' => $already_revoked,
				)
			),
		);
	}
}
