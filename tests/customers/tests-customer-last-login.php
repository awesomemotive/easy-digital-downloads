<?php
/**
 * Customer last login tests.
 *
 * @package     EDD\Tests\Customers
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

namespace EDD\Tests\Customers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Customers\LastLogin;

/**
 * Customer last login tests.
 *
 * @group edd_customers
 */
class LastLoginTests extends EDD_UnitTestCase {

	/**
	 * The password the test users are created with.
	 *
	 * @var string
	 */
	const PASSWORD = 'last-login-password';

	/**
	 * A user linked to a customer.
	 *
	 * @var \WP_User
	 */
	protected $linked_user;

	/**
	 * The customer linked to the user.
	 *
	 * @var \EDD_Customer
	 */
	protected $customer;

	public function set_up() {
		parent::set_up();

		$this->linked_user = self::factory()->user->create_and_get( array( 'user_pass' => self::PASSWORD ) );
		$this->customer    = parent::edd()->customer->create_and_get(
			array(
				'user_id' => $this->linked_user->ID,
				'email'   => $this->linked_user->user_email,
			)
		);
	}

	public function tear_down() {
		wp_set_current_user( 0 );

		parent::tear_down();
	}

	public function test_wp_login_stores_last_login_in_utc_for_linked_customer() {
		do_action( 'wp_login', $this->linked_user->user_login, $this->linked_user );

		$last_login = LastLogin::get( $this->customer->id )['date'];
		$stored     = \DateTime::createFromFormat( 'Y-m-d H:i:s', $last_login, new \DateTimeZone( 'UTC' ) );

		$this->assertInstanceOf( \DateTime::class, $stored );
		$this->assertSame( $last_login, $stored->format( 'Y-m-d H:i:s' ) );
		$this->assertEqualsWithDelta( time(), $stored->getTimestamp(), 60 );
	}

	public function test_edd_log_user_in_updates_last_login() {
		edd_update_customer_meta( $this->customer->id, 'last_login_date', '2020-01-01 00:00:00' );

		edd_log_user_in( $this->linked_user->ID, $this->linked_user->user_login, self::PASSWORD );

		$this->assertTrue( is_user_logged_in() );

		$last_login = LastLogin::get( $this->customer->id )['date'];
		$stored     = \DateTime::createFromFormat( 'Y-m-d H:i:s', $last_login, new \DateTimeZone( 'UTC' ) );

		$this->assertInstanceOf( \DateTime::class, $stored );
		$this->assertEqualsWithDelta( time(), $stored->getTimestamp(), 60 );
	}

	public function test_login_without_customer_record_creates_no_customer() {
		$user_without_customer = self::factory()->user->create_and_get();
		$customer_count        = edd_count_customers();

		do_action( 'wp_login', $user_without_customer->user_login, $user_without_customer );

		$this->assertSame( $customer_count, edd_count_customers() );
		$this->assertFalse( edd_get_customer_by( 'user_id', $user_without_customer->ID ) );
		$this->assertSame( array(), edd_get_customer_meta( $this->customer->id, 'last_login_date' ) );
		$this->assertSame( array(), edd_get_customer_meta( $this->customer->id, 'last_login_ip' ) );
	}

	public function test_get_returns_empty_strings_when_never_logged_in() {
		$empty = array( 'date' => '', 'ip' => '' );

		$this->assertSame( $empty, LastLogin::get( $this->customer->id ) );
		$this->assertSame( $empty, LastLogin::get( 0 ) );
		$this->assertSame( $empty, LastLogin::get( PHP_INT_MAX ) );
	}

	public function test_get_returns_the_stored_date_and_ip() {
		edd_update_customer_meta( $this->customer->id, 'last_login_date', '2026-03-04 05:06:07' );
		edd_update_customer_meta( $this->customer->id, 'last_login_ip', '203.0.113.7' );

		$this->assertSame( array( 'date' => '2026-03-04 05:06:07', 'ip' => '203.0.113.7' ), LastLogin::get( $this->customer->id ) );
	}

	public function test_two_factor_authentication_stores_the_last_login() {
		do_action( 'two_factor_user_authenticated', $this->linked_user, new \stdClass() );

		$last_login = LastLogin::get( $this->customer->id );
		$stored     = \DateTime::createFromFormat( 'Y-m-d H:i:s', $last_login['date'], new \DateTimeZone( 'UTC' ) );

		$this->assertInstanceOf( \DateTime::class, $stored );
		$this->assertEqualsWithDelta( time(), $stored->getTimestamp(), 60 );
	}

	public function test_two_factor_authentication_without_a_user_stores_nothing() {
		do_action( 'two_factor_user_authenticated', null );

		$this->assertSame( '', LastLogin::get( $this->customer->id )['date'] );
	}

	public function test_a_filtered_ip_with_a_quote_is_stored_intact() {
		$filter = static function () {
			return 'a"b\\c';
		};
		add_filter( 'edd_get_ip', $filter );

		try {
			do_action( 'wp_login', $this->linked_user->user_login, $this->linked_user );
		} finally {
			remove_filter( 'edd_get_ip', $filter );
		}

		$this->assertSame( 'a"b\\c', edd_get_customer_meta( $this->customer->id, 'last_login_ip', true ) );
	}

	public function test_get_returns_empty_strings_for_stored_values_that_are_not_strings() {
		edd_update_customer_meta( $this->customer->id, 'last_login_date', array( '2026-03-04 05:06:07' ) );
		edd_update_customer_meta( $this->customer->id, 'last_login_ip', array( '203.0.113.7' ) );

		$this->assertSame( array( 'date' => '', 'ip' => '' ), LastLogin::get( $this->customer->id ) );
	}

	public function test_subscribes_to_wp_login_last_with_two_arguments() {
		global $wp_filter;

		$registered = null;
		foreach ( $wp_filter['wp_login']->callbacks[ PHP_INT_MAX ] ?? array() as $callback ) {
			if ( is_array( $callback['function'] ) && $callback['function'][0] instanceof LastLogin ) {
				$registered = $callback;
				break;
			}
		}

		$this->assertNotNull( $registered );
		$this->assertSame( 'record', $registered['function'][1] );
		$this->assertSame( 2, $registered['accepted_args'] );
	}

	public function test_wp_login_stores_the_ip_address_of_the_login() {
		$this->log_in_from( '203.0.113.7' );

		$this->assertSame( '203.0.113.7', edd_get_customer_meta( $this->customer->id, 'last_login_ip', true ) );
	}

	public function test_a_later_login_replaces_the_date_and_ip_together() {
		edd_update_customer_meta( $this->customer->id, 'last_login_date', '2020-01-01 00:00:00' );
		edd_update_customer_meta( $this->customer->id, 'last_login_ip', '198.51.100.1' );

		$this->log_in_from( '203.0.113.9' );

		$last_login = LastLogin::get( $this->customer->id );

		$this->assertSame( '203.0.113.9', $last_login['ip'] );
		$this->assertNotSame( '2020-01-01 00:00:00', $last_login['date'] );
	}

	public function test_a_login_whose_ip_write_fails_keeps_no_previous_ip() {
		edd_update_customer_meta( $this->customer->id, 'last_login_date', '2020-01-01 00:00:00' );
		edd_update_customer_meta( $this->customer->id, 'last_login_ip', '198.51.100.1' );
		$this->assertSame( '198.51.100.1', edd_get_customer_meta( $this->customer->id, 'last_login_ip', true ) );

		$fail_ip_write = static function ( $check, $object_id, $meta_key ) {
			return 'last_login_ip' === $meta_key ? false : $check;
		};
		add_filter( 'update_edd_customer_metadata', $fail_ip_write, 10, 3 );

		try {
			$this->log_in_from( '203.0.113.9' );
		} finally {
			remove_filter( 'update_edd_customer_metadata', $fail_ip_write, 10 );
		}

		$this->assertNotSame( '2020-01-01 00:00:00', edd_get_customer_meta( $this->customer->id, 'last_login_date', true ) );
		$this->assertSame( array(), edd_get_customer_meta( $this->customer->id, 'last_login_ip' ) );
	}

	public function test_a_login_whose_date_write_fails_stores_no_ip() {
		edd_update_customer_meta( $this->customer->id, 'last_login_date', '2020-01-01 00:00:00' );
		edd_update_customer_meta( $this->customer->id, 'last_login_ip', '198.51.100.1' );
		$this->assertSame( '198.51.100.1', edd_get_customer_meta( $this->customer->id, 'last_login_ip', true ) );

		$fail_date_write = static function ( $check, $object_id, $meta_key ) {
			return 'last_login_date' === $meta_key ? false : $check;
		};
		add_filter( 'update_edd_customer_metadata', $fail_date_write, 10, 3 );

		try {
			$this->log_in_from( '203.0.113.9' );
		} finally {
			remove_filter( 'update_edd_customer_metadata', $fail_date_write, 10 );
		}

		$this->assertSame( '2020-01-01 00:00:00', edd_get_customer_meta( $this->customer->id, 'last_login_date', true ) );
		$this->assertSame( array(), edd_get_customer_meta( $this->customer->id, 'last_login_ip' ) );
	}

	public function test_a_login_whose_ip_delete_fails_leaves_the_previous_login_unchanged() {
		edd_update_customer_meta( $this->customer->id, 'last_login_date', '2020-01-01 00:00:00' );
		edd_update_customer_meta( $this->customer->id, 'last_login_ip', '198.51.100.1' );
		$this->assertSame( '198.51.100.1', edd_get_customer_meta( $this->customer->id, 'last_login_ip', true ) );

		$fail_ip_delete = static function ( $check, $object_id, $meta_key ) {
			return 'last_login_ip' === $meta_key ? false : $check;
		};
		add_filter( 'delete_edd_customer_metadata', $fail_ip_delete, 10, 3 );

		try {
			$this->log_in_from( '203.0.113.9' );
		} finally {
			remove_filter( 'delete_edd_customer_metadata', $fail_ip_delete, 10 );
		}

		$this->assertSame( '2020-01-01 00:00:00', edd_get_customer_meta( $this->customer->id, 'last_login_date', true ) );
		$this->assertSame( '198.51.100.1', edd_get_customer_meta( $this->customer->id, 'last_login_ip', true ) );
	}

	public function test_a_login_in_the_same_second_as_the_stored_date_still_stores_the_ip() {
		// Store the date but report false, as update_metadata() does when the value is unchanged.
		$store_date_unchanged = static function ( $check, $object_id, $meta_key, $meta_value ) use ( &$store_date_unchanged ) {
			if ( 'last_login_date' !== $meta_key ) {
				return $check;
			}
			remove_filter( 'update_edd_customer_metadata', $store_date_unchanged, 10 );
			update_metadata( 'edd_customer', $object_id, $meta_key, $meta_value );

			return false;
		};
		add_filter( 'update_edd_customer_metadata', $store_date_unchanged, 10, 4 );

		try {
			$this->log_in_from( '203.0.113.9' );
		} finally {
			remove_filter( 'update_edd_customer_metadata', $store_date_unchanged, 10 );
		}

		$this->assertNotSame( '', edd_get_customer_meta( $this->customer->id, 'last_login_date', true ) );
		$this->assertSame( '203.0.113.9', edd_get_customer_meta( $this->customer->id, 'last_login_ip', true ) );
	}

	/**
	 * @dataProvider provider_format_uses_the_offset_in_force_at_the_login
	 */
	public function test_format_uses_the_offset_in_force_at_the_login( $timezone, $utc, $expected ) {
		$original = get_option( 'timezone_string' );
		update_option( 'timezone_string', $timezone );
		EDD()->utils->get_time_zone( true );

		try {
			$this->assertSame( $expected, LastLogin::format( $utc, 'M. d, Y H:i' ) );
		} finally {
			update_option( 'timezone_string', $original );
			EDD()->utils->get_time_zone( true );
		}
	}

	public function test_format_follows_the_site_date_and_time_settings() {
		$original_timezone = get_option( 'timezone_string' );
		$original_date     = get_option( 'date_format' );
		$original_time     = get_option( 'time_format' );
		update_option( 'timezone_string', 'Asia/Tokyo' );
		update_option( 'date_format', 'd/m/Y' );
		update_option( 'time_format', 'H:i' );
		EDD()->utils->get_time_zone( true );
		EDD()->utils->get_date_format( true );
		EDD()->utils->get_time_format( true );

		try {
			$this->assertSame( '15/01/2026 14:06', LastLogin::format( '2026-01-15 05:06:00' ) );
			$this->assertSame( '2026-01-15 14:06:00', LastLogin::format( '2026-01-15 05:06:00', 'mysql' ) );
		} finally {
			update_option( 'timezone_string', $original_timezone );
			update_option( 'date_format', $original_date );
			update_option( 'time_format', $original_time );
			EDD()->utils->get_time_zone( true );
			EDD()->utils->get_date_format( true );
			EDD()->utils->get_time_format( true );
		}
	}

	public function test_format_returns_empty_string_for_an_unreadable_value() {
		$this->assertSame( '', LastLogin::format( 'not a date' ) );
	}

	public function test_login_does_not_write_to_a_customer_relinked_to_another_user() {
		$other_user = self::factory()->user->create_and_get();

		// Prime the user_id lookup so a cache could still answer it after the relink.
		edd_get_customer_by( 'user_id', $this->linked_user->ID );
		edd_update_customer( $this->customer->id, array( 'user_id' => $other_user->ID ) );

		do_action( 'wp_login', $this->linked_user->user_login, $this->linked_user );

		$this->assertSame( '', LastLogin::get( $this->customer->id )['date'] );
		$this->assertSame( '', LastLogin::get( $this->customer->id )['ip'] );
	}

	public function test_linking_a_customer_to_another_user_keeps_the_login() {
		do_action( 'wp_login', $this->linked_user->user_login, $this->linked_user );
		$recorded = LastLogin::get( $this->customer->id );
		$this->assertNotSame( '', $recorded['date'] );

		$other_user = self::factory()->user->create_and_get();
		edd_update_customer( $this->customer->id, array( 'user_id' => $other_user->ID ) );

		$this->assertSame( $recorded, LastLogin::get( $this->customer->id ) );
	}

	public function test_unlinking_a_customer_keeps_the_login() {
		do_action( 'wp_login', $this->linked_user->user_login, $this->linked_user );

		edd_update_customer( $this->customer->id, array( 'user_id' => 0 ) );

		$this->assertNotSame( '', LastLogin::get( $this->customer->id )['date'] );
	}

	public function test_unlinking_and_linking_the_same_user_again_keeps_the_login() {
		do_action( 'wp_login', $this->linked_user->user_login, $this->linked_user );
		$recorded = LastLogin::get( $this->customer->id );

		edd_update_customer( $this->customer->id, array( 'user_id' => 0 ) );
		edd_update_customer( $this->customer->id, array( 'user_id' => $this->linked_user->ID ) );

		$this->assertSame( $recorded, LastLogin::get( $this->customer->id ) );
	}

	public function test_get_timezone_abbr_uses_the_offset_in_force_at_the_login() {
		$original = get_option( 'timezone_string' );
		update_option( 'timezone_string', 'America/New_York' );
		EDD()->utils->get_time_zone( true );

		try {
			$this->assertSame( 'EST', LastLogin::get_timezone_abbr( '2026-01-15 12:00:00' ) );
			$this->assertSame( 'EDT', LastLogin::get_timezone_abbr( '2026-07-15 12:00:00' ) );
			$this->assertSame( '', LastLogin::get_timezone_abbr( 'not a date' ) );
		} finally {
			update_option( 'timezone_string', $original );
			EDD()->utils->get_time_zone( true );
		}
	}

	public function test_updating_a_customer_without_changing_the_user_keeps_the_login() {
		do_action( 'wp_login', $this->linked_user->user_login, $this->linked_user );

		edd_update_customer( $this->customer->id, array( 'name' => 'Renamed Customer' ) );

		$this->assertNotSame( '', LastLogin::get( $this->customer->id )['date'] );
	}

	public function test_customers_who_last_logged_in_from_one_ip_are_found_by_a_meta_query() {
		$shared_ip_customer = parent::edd()->customer->create_and_get();
		$other_ip_customer  = parent::edd()->customer->create_and_get();
		edd_update_customer_meta( $this->customer->id, LastLogin::IP_META_KEY, '203.0.113.7' );
		edd_update_customer_meta( $shared_ip_customer->id, LastLogin::IP_META_KEY, '203.0.113.7' );
		edd_update_customer_meta( $other_ip_customer->id, LastLogin::IP_META_KEY, '198.51.100.1' );

		$found = edd_get_customers(
			array(
				'fields'     => 'id',
				'meta_query' => array(
					array(
						'key'   => LastLogin::IP_META_KEY,
						'value' => '203.0.113.7',
					),
				),
			)
		);

		$this->assertEqualsCanonicalizing( array( (int) $this->customer->id, (int) $shared_ip_customer->id ), array_values( array_map( 'intval', $found ) ) );
	}

	public function test_customers_who_last_logged_in_before_a_date_are_found_by_a_meta_query() {
		$recent_customer = parent::edd()->customer->create_and_get();
		edd_update_customer_meta( $this->customer->id, LastLogin::DATE_META_KEY, '2026-01-15 05:06:07' );
		edd_update_customer_meta( $recent_customer->id, LastLogin::DATE_META_KEY, '2026-09-30 23:59:59' );

		$found = edd_get_customers(
			array(
				'fields'     => 'id',
				'meta_query' => array(
					array(
						'key'     => LastLogin::DATE_META_KEY,
						'value'   => '2026-06-01 00:00:00',
						'compare' => '<',
						'type'    => 'DATETIME',
					),
				),
			)
		);

		$this->assertSame( array( (int) $this->customer->id ), array_values( array_map( 'intval', $found ) ) );
	}

	public function test_orders_placed_after_the_last_login_can_be_counted() {
		edd_update_customer_meta( $this->customer->id, LastLogin::DATE_META_KEY, '2026-03-04 05:06:07' );
		$order_before_login = parent::edd()->order->create_and_get(
			array(
				'customer_id'  => $this->customer->id,
				'date_created' => '2026-03-01 00:00:00',
			)
		);
		$order_after_login  = parent::edd()->order->create_and_get(
			array(
				'customer_id'  => $this->customer->id,
				'date_created' => '2026-03-10 00:00:00',
			)
		);
		$this->assertSame( 2, edd_count_orders( array( 'customer_id' => $this->customer->id ) ) );

		$orders_after_login = edd_get_orders(
			array(
				'fields'             => 'id',
				'customer_id'        => $this->customer->id,
				'date_created_query' => array(
					'after' => LastLogin::get( $this->customer->id )['date'],
				),
			)
		);

		$this->assertSame( array( (int) $order_after_login->id ), array_values( array_map( 'intval', $orders_after_login ) ) );
	}

	public function provider_format_uses_the_offset_in_force_at_the_login() {
		return array(
			'Tokyo'                    => array( 'Asia/Tokyo', '2026-01-15 05:06:00', 'Jan. 15, 2026 14:06' ),
			'New York in winter'       => array( 'America/New_York', '2026-01-15 12:00:00', 'Jan. 15, 2026 07:00' ),
			'New York in summer (DST)' => array( 'America/New_York', '2026-07-15 12:00:00', 'Jul. 15, 2026 08:00' ),
		);
	}

	/**
	 * Fires `wp_login` for the linked user from an IP address, restoring the previous one afterwards.
	 *
	 * @param string $ip The IP address to log in from.
	 */
	private function log_in_from( $ip ) {
		$original_ip            = $_SERVER['REMOTE_ADDR'] ?? null;
		$_SERVER['REMOTE_ADDR'] = $ip;

		try {
			do_action( 'wp_login', $this->linked_user->user_login, $this->linked_user );
		} finally {
			if ( null === $original_ip ) {
				unset( $_SERVER['REMOTE_ADDR'] );
			} else {
				$_SERVER['REMOTE_ADDR'] = $original_ip;
			}
		}
	}
}
