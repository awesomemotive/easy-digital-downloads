<?php
/**
 * Records when a customer last logged in.
 *
 * @package     EDD\Customers
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

namespace EDD\Customers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;

/**
 * Stores the last login time and IP address for the customer linked to a user.
 *
 * @since 3.7.3
 */
class LastLogin implements SubscriberInterface {

	/**
	 * The customer meta key the last login time is stored under, in UTC as 'Y-m-d H:i:s'.
	 *
	 * @since 3.7.3
	 * @var string
	 */
	const DATE_META_KEY = 'last_login_date';

	/**
	 * The customer meta key the IP address of the last login is stored under.
	 *
	 * @since 3.7.3
	 * @var string
	 */
	const IP_META_KEY = 'last_login_ip';

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.7.3
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'wp_login'                      => array( 'record', PHP_INT_MAX, 2 ),
			'two_factor_user_authenticated' => array( 'record_two_factor', PHP_INT_MAX, 1 ),
		);
	}

	/**
	 * Gets the stored last login for a customer.
	 *
	 * Recorded on `wp_login`, and on `two_factor_user_authenticated` once a Two Factor user passes
	 * the second step. Logins at purchase time are recorded too: registering at checkout, and
	 * PayPal's login after capture. A remember-me cookie, an application password, REST
	 * authentication, or a login through another network site's `wp-login.php` records nothing.
	 *
	 * @since 3.7.3
	 *
	 * @param int $customer_id The customer ID.
	 * @return array{date: string, ip: string} The login time in UTC as 'Y-m-d H:i:s' and the IP address. Each is an empty string if none is stored.
	 */
	public static function get( $customer_id ) {
		$customer_id = absint( $customer_id );
		$date        = edd_get_customer_meta( $customer_id, self::DATE_META_KEY, true );
		$ip          = edd_get_customer_meta( $customer_id, self::IP_META_KEY, true );

		return array(
			'date' => is_string( $date ) ? $date : '',
			'ip'   => is_string( $ip ) ? $ip : '',
		);
	}

	/**
	 * Formats a stored UTC login time in the site timezone.
	 *
	 * The offset is the one in force at that moment, not today's, so a login either side of a
	 * daylight saving change reads correctly.
	 *
	 * @since 3.7.3
	 *
	 * @param string $last_login The stored login time in UTC, as 'Y-m-d H:i:s'.
	 * @param string $format     The date format shorthand or format string, as `edd_get_date_format()` accepts. Default 'datetime'.
	 * @return string The formatted time, or an empty string if the value cannot be read.
	 */
	public static function format( $last_login, $format = 'datetime' ) {
		if ( ! strtotime( $last_login ) ) {
			return '';
		}

		$date = self::to_site_date( $last_login );

		return date_i18n( edd_get_date_format( $format ), $date->getTimestamp() + $date->getOffset() );
	}

	/**
	 * Gets the timezone abbreviation in force in the site timezone at a stored UTC login time.
	 *
	 * Not today's abbreviation: a winter login reads EST when viewed in July.
	 *
	 * @since 3.7.3
	 *
	 * @param string $last_login The stored login time in UTC, as 'Y-m-d H:i:s'.
	 * @return string The abbreviation, or an empty string if the value cannot be read.
	 */
	public static function get_timezone_abbr( $last_login ) {
		if ( ! strtotime( $last_login ) ) {
			return '';
		}

		return self::to_site_date( $last_login )->format( 'T' );
	}

	/**
	 * Converts a stored UTC login time to a date object in the site timezone.
	 *
	 * @since 3.7.3
	 *
	 * @param string $last_login The stored login time in UTC, as 'Y-m-d H:i:s'.
	 * @return \EDD\Utils\Date
	 */
	private static function to_site_date( $last_login ) {
		return edd_get_edd_timezone_equivalent_date_from_utc( EDD()->utils->date( $last_login, 'UTC' ) );
	}

	/**
	 * Records the login time and IP address for the customer linked to the user who logged in.
	 *
	 * Never creates a customer: a user without one is ignored.
	 *
	 * @since 3.7.3
	 *
	 * @param string   $user_login The user's login name.
	 * @param \WP_User $user       The user who logged in.
	 * @return void
	 */
	public function record( $user_login, $user = null ) {
		$this->write( $user );
	}

	/**
	 * Records the login of a user who has passed the second step of Two Factor authentication.
	 *
	 * The hook passes the user first, and a provider second that is not needed here.
	 *
	 * @since 3.7.3
	 *
	 * @param \WP_User $user The user who authenticated.
	 * @return void
	 */
	public function record_two_factor( $user = null ) {
		$this->write( $user );
	}

	/**
	 * Writes the login time and IP address for the customer linked to a user.
	 *
	 * @since 3.7.3
	 *
	 * @param \WP_User $user The user who logged in.
	 * @return void
	 */
	private function write( $user ) {
		if ( ! $user instanceof \WP_User || empty( $user->ID ) ) {
			return;
		}

		$customer = edd_get_customer_by( 'user_id', $user->ID );
		if ( ! $customer instanceof \EDD_Customer ) {
			return;
		}

		// Read the customer again by ID: the user_id lookup can come from a cache that missed a relink or erasure.
		$current_customer = edd_get_customer( $customer->id );
		if ( ! $current_customer instanceof \EDD_Customer || (int) $current_customer->user_id !== (int) $user->ID ) {
			return;
		}

		// Drop the previous IP first, so a failed write can never pair a new date with it.
		edd_delete_customer_meta( $customer->id, self::IP_META_KEY );
		// The delete also returns false when there was no IP, so check the key instead.
		if ( metadata_exists( 'edd_customer', $customer->id, self::IP_META_KEY ) ) {
			return;
		}

		$date = gmdate( 'Y-m-d H:i:s' );
		// update_metadata() also returns false for an unchanged value, so confirm the stored date before writing the IP.
		if ( ! edd_update_customer_meta( $customer->id, self::DATE_META_KEY, $date ) && edd_get_customer_meta( $customer->id, self::DATE_META_KEY, true ) !== $date ) {
			return;
		}

		// update_metadata() unslashes the value, so slash it or a backslash in a filtered IP is lost.
		edd_update_customer_meta( $customer->id, self::IP_META_KEY, wp_slash( edd_get_ip() ) );
	}
}
