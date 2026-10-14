<?php

namespace EDD\Tests\Users;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Users\LoginLink\Send\Validate;
use EDD\Database\Queries\LogEmail;

class LoginLinkSendValidate extends EDD_UnitTestCase {

	/**
	 * @var int
	 */
	private static $user_id;

	/**
	 * @var LogEmail
	 */
	private $log_query;

	/**
	 * @var array IDs of email log entries created during a test.
	 */
	private $created_log_ids = array();

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
	}

	public function setUp(): void {
		parent::setUp();
		$this->log_query = new LogEmail();

		// Clear IP transients.
		$ip = edd_get_ip();
		if ( ! empty( $ip ) ) {
			delete_transient( 'edd_login_link_ip_' . md5( $ip ) );
		}
	}

	public function tearDown(): void {
		// Clean up email log entries created during the test.
		foreach ( $this->created_log_ids as $log_id ) {
			$this->log_query->delete_item( $log_id );
		}
		$this->created_log_ids = array();

		// Clean up IP transients.
		$ip = edd_get_ip();
		if ( ! empty( $ip ) ) {
			delete_transient( 'edd_login_link_ip_' . md5( $ip ) );
		}

		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function test_check_allows_when_no_logs_exist() {
		$result = Validate::check_user( self::$user_id );

		$this->assertTrue( $result );
	}

	/**
	 * @return void
	 */
	public function test_check_blocks_during_cooldown() {
		// Create a log entry with the current timestamp.
		$this->create_login_link_log( self::$user_id );

		$result = Validate::check_user( self::$user_id );

		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertSame( 'rate_limited', $result->get_error_code() );
	}

	/**
	 * @return void
	 */
	public function test_check_allows_after_cooldown_expires() {
		// Create a log entry older than the cooldown period.
		$this->create_login_link_log(
			self::$user_id,
			edd_get_utc_date_string( '@' . ( time() - Validate::COOLDOWN_SECONDS - 1 ) )
		);

		$result = Validate::check_user( self::$user_id );

		$this->assertTrue( $result );
	}

	/**
	 * @return void
	 */
	public function test_check_blocks_when_max_emails_reached() {
		// Create entries older than cooldown but within the window.
		$timestamp = edd_get_utc_date_string( '@' . ( time() - Validate::COOLDOWN_SECONDS - 10 ) );

		for ( $i = 0; $i < Validate::MAX_EMAILS; $i++ ) {
			$this->create_login_link_log( self::$user_id, $timestamp );
		}

		$result = Validate::check_user( self::$user_id );

		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertSame( 'max_emails_reached', $result->get_error_code() );
	}

	/**
	 * @return void
	 */
	public function test_check_allows_below_max_emails() {
		// Create entries below the limit, older than cooldown.
		$timestamp = edd_get_utc_date_string( '@' . ( time() - Validate::COOLDOWN_SECONDS - 10 ) );

		for ( $i = 0; $i < Validate::MAX_EMAILS - 1; $i++ ) {
			$this->create_login_link_log( self::$user_id, $timestamp );
		}

		$result = Validate::check_user( self::$user_id );

		$this->assertTrue( $result );
	}

	/**
	 * @return void
	 */
	public function test_check_ignores_logs_outside_window() {
		// Create entries older than the user window — should not be counted.
		$timestamp = edd_get_utc_date_string( '@' . ( time() - Validate::USER_WINDOW - 60 ) );

		for ( $i = 0; $i < Validate::MAX_EMAILS + 2; $i++ ) {
			$this->create_login_link_log( self::$user_id, $timestamp );
		}

		$result = Validate::check_user( self::$user_id );

		$this->assertTrue( $result );
	}

	/**
	 * @return void
	 */
	public function test_check_only_counts_login_link_emails() {
		// Create log entries with a different email_id — should not be counted.
		$timestamp = edd_get_utc_date_string( '@' . ( time() - 10 ) );

		for ( $i = 0; $i < Validate::MAX_EMAILS + 2; $i++ ) {
			$log_id = $this->log_query->add_item(
				array(
					'email_id'     => 'order_receipt',
					'object_id'    => self::$user_id,
					'object_type'  => 'user',
					'subject'      => 'Order receipt',
					'email'        => 'test@edd.local',
					'date_created' => $timestamp,
				)
			);
			if ( $log_id ) {
				$this->created_log_ids[] = $log_id;
			}
		}

		$result = Validate::check_user( self::$user_id );

		$this->assertTrue( $result );
	}

	/**
	 * @return void
	 */
	public function test_check_ip_does_not_check_per_user_limits() {
		// Even with logs that would block a per-user check, check_ip should pass.
		$this->create_login_link_log( self::$user_id );

		$result = Validate::check_ip();

		$this->assertTrue( $result );
	}

	/**
	 * @return void
	 */
	public function test_check_does_not_count_other_users_logs() {
		$other_user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		// Fill up the other user's logs — should not affect our user.
		for ( $i = 0; $i < Validate::MAX_EMAILS; $i++ ) {
			$this->create_login_link_log( $other_user_id );
		}

		// Our user should still be allowed (cooldown aside, no logs for them).
		$result = Validate::check_user( self::$user_id );

		$this->assertTrue( $result );
	}

	/**
	 * @return void
	 */
	public function test_cooldown_boundary_exactly_at_expiry_allows() {
		// Log created exactly COOLDOWN_SECONDS ago — cooldown has just expired.
		$this->create_login_link_log(
			self::$user_id,
			edd_get_utc_date_string( '@' . ( time() - Validate::COOLDOWN_SECONDS ) )
		);

		$result = Validate::check_user( self::$user_id );

		$this->assertTrue( $result );
	}

	/**
	 * @return void
	 */
	public function test_cooldown_boundary_one_second_before_expiry_blocks() {
		// The log is only one second inside the cooldown if the check reads the clock in the same second it was written.
		for ( $attempt = 1; $attempt <= 5; $attempt++ ) {
			// A Berlin query reuses its last_changed stamp, so a reused instance leaves the check reading the previous attempt's cache.
			$this->log_query = new LogEmail();

			$seeded_at = time();
			$log_id    = $this->create_login_link_log(
				self::$user_id,
				edd_get_utc_date_string( '@' . ( $seeded_at - Validate::COOLDOWN_SECONDS + 1 ) )
			);

			$result              = Validate::check_user( self::$user_id );
			$read_in_same_second = time() === $seeded_at;

			if ( $read_in_same_second ) {
				break;
			}

			$this->log_query->delete_item( $log_id );
		}

		$this->assertTrue( $read_in_same_second, 'The clock ticked between writing the log and checking it on every attempt.' );
		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertSame( 'rate_limited', $result->get_error_code() );
	}

	/**
	 * @return void
	 */
	public function test_window_boundary_log_at_edge_is_counted() {
		// Create MAX_EMAILS entries just inside the user window boundary.
		$just_inside = edd_get_utc_date_string( '@' . ( time() - Validate::USER_WINDOW + 60 ) );

		for ( $i = 0; $i < Validate::MAX_EMAILS; $i++ ) {
			$this->create_login_link_log( self::$user_id, $just_inside );
		}

		$result = Validate::check_user( self::$user_id );

		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertSame( 'max_emails_reached', $result->get_error_code() );
	}

	/**
	 * @return void
	 */
	public function test_window_boundary_log_just_outside_is_not_counted() {
		// Create MAX_EMAILS entries just outside the user window boundary.
		$just_outside = edd_get_utc_date_string( '@' . ( time() - Validate::USER_WINDOW - 1 ) );

		for ( $i = 0; $i < Validate::MAX_EMAILS; $i++ ) {
			$this->create_login_link_log( self::$user_id, $just_outside );
		}

		$result = Validate::check_user( self::$user_id );

		$this->assertTrue( $result );
	}

	/**
	 * @return void
	 */
	public function test_date_query_uses_utc() {
		// Create a log entry using edd_get_utc_date_string to match
		// the same UTC conversion the production code uses.
		$utc_now = edd_get_utc_date_string( 'now' );

		$this->create_login_link_log( self::$user_id, $utc_now );

		// Should be within cooldown since it was just created.
		$result = Validate::check_user( self::$user_id );

		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertSame( 'rate_limited', $result->get_error_code() );
	}

	/**
	 * @return void
	 */
	public function test_mixed_window_logs_only_recent_counted() {
		// 2 entries outside the user window — should not count.
		$old = edd_get_utc_date_string( '@' . ( time() - Validate::USER_WINDOW - 120 ) );
		for ( $i = 0; $i < 2; $i++ ) {
			$this->create_login_link_log( self::$user_id, $old );
		}

		// 2 entries inside the window but past cooldown — should count.
		$recent = edd_get_utc_date_string( '@' . ( time() - Validate::COOLDOWN_SECONDS - 10 ) );
		for ( $i = 0; $i < 2; $i++ ) {
			$this->create_login_link_log( self::$user_id, $recent );
		}

		// Total in window = 2, below MAX_EMAILS (3). Should be allowed.
		$result = Validate::check_user( self::$user_id );

		$this->assertTrue( $result );
	}

	/**
	 * @return void
	 */
	public function test_check_user_bypasses_ip_check() {
		$ip  = edd_get_ip();
		$key = 'edd_login_link_ip_' . md5( $ip );

		// Fill the IP limit so check_ip() would fail.
		$timestamps = array();
		for ( $i = 0; $i < Validate::IP_LIMIT; $i++ ) {
			$timestamps[] = time();
		}
		set_transient( $key, $timestamps, Validate::IP_WINDOW );

		// check_user() skips IP — should pass.
		$result = Validate::check_user( self::$user_id );
		$this->assertTrue( $result );

		// check_ip() should fail on IP.
		$result = Validate::check_ip();
		$this->assertInstanceOf( 'WP_Error', $result );

		delete_transient( $key );
	}

	/**
	 * @return void
	 */
	public function test_check_user_still_enforces_cooldown() {
		// Create a recent log entry within cooldown.
		$this->create_login_link_log( self::$user_id );

		// check_user() should still enforce per-user cooldown.
		$result = Validate::check_user( self::$user_id );
		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertSame( 'rate_limited', $result->get_error_code() );
	}

	/**
	 * @return void
	 */
	public function test_user_window_constant_exists_and_is_independent_from_ip_window() {
		// USER_WINDOW must be defined on the class.
		$this->assertTrue( defined( 'EDD\Users\LoginLink\Send\Validate::USER_WINDOW' ), 'USER_WINDOW constant must be defined.' );

		// The constants may share the same value, but they are separate and can diverge.
		// Verify both are positive integers so they are usable as time windows.
		$this->assertGreaterThan( 0, Validate::USER_WINDOW );
		$this->assertGreaterThan( 0, Validate::IP_WINDOW );
	}

	/**
	 * @return void
	 */
	public function test_record_appends_timestamp_to_transient() {
		$ip  = edd_get_ip();
		$key = 'edd_login_link_ip_' . md5( $ip );

		Validate::record();

		$requests = get_transient( $key );
		$this->assertIsArray( $requests );
		$this->assertCount( 1, $requests );
		$this->assertEqualsWithDelta( time(), $requests[0], 2 );
	}

	/**
	 * @return void
	 */
	public function test_record_prunes_stale_timestamps_before_appending() {
		$ip  = edd_get_ip();
		$key = 'edd_login_link_ip_' . md5( $ip );

		// Seed the transient with one timestamp older than IP_WINDOW.
		$stale = time() - Validate::IP_WINDOW - 60;
		set_transient( $key, array( $stale ), Validate::IP_WINDOW );

		Validate::record();

		$requests = get_transient( $key );
		$this->assertIsArray( $requests );
		// Stale entry must be pruned; only the new timestamp remains.
		$this->assertCount( 1, $requests );
		$this->assertNotContains( $stale, $requests );
	}

	/**
	 * @return void
	 */
	public function test_record_preserves_fresh_timestamps() {
		$ip  = edd_get_ip();
		$key = 'edd_login_link_ip_' . md5( $ip );

		// Seed with a timestamp within the window.
		$fresh = time() - 60;
		set_transient( $key, array( $fresh ), Validate::IP_WINDOW );

		Validate::record();

		$requests = get_transient( $key );
		$this->assertIsArray( $requests );
		// Fresh entry must be kept; new timestamp appended.
		$this->assertCount( 2, $requests );
		$this->assertContains( $fresh, $requests );
	}

	/**
	 * Creates a login link email log entry for testing.
	 *
	 * @param int         $user_id      User ID.
	 * @param string|null $date_created Optional. GMT date string. Defaults to now.
	 * @return int|false Log entry ID or false on failure.
	 */
	private function create_login_link_log( int $user_id, ?string $date_created = null ) {
		$args = array(
			'email_id'    => 'login_link',
			'object_id'   => $user_id,
			'object_type' => 'user',
			'subject'     => 'Your login link',
			'email'       => 'test@edd.local',
		);

		if ( null !== $date_created ) {
			$args['date_created'] = $date_created;
		}

		$log_id = $this->log_query->add_item( $args );
		if ( $log_id ) {
			$this->created_log_ids[] = $log_id;
		}

		return $log_id;
	}
}
