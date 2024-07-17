<?php

namespace EDD\Sessions\Traits;

defined( 'ABSPATH' ) || exit;

trait Legacy {

	/**
	 * Set the expiration time for the session cookie.
	 * Legacy method.
	 *
	 * @return int
	 */
	public function set_expiration_variant_time() {
		return HOUR_IN_SECONDS * 23;
	}

	/**
	 * Set the expiration time for the session cookie.
	 * Legacy method.
	 *
	 * @return int
	 */
	public function set_expiration_time() {
		return HOUR_IN_SECONDS * 24;
	}

	/**
	 * Whether to start the session.
	 * Legacy method.
	 *
	 * @deprecated 3.3.0 Left for backwards compatibility.
	 * @return bool
	 */
	public function should_start_session() {
		if ( ! $this->is_request_valid_for_session() ) {
			return false;
		}
		if ( isset( $_COOKIE['edd_items_in_cart'] ) ) {
			return true;
		}
		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$blacklist = $this->get_blacklist();
			$uri       = ltrim( $_SERVER['REQUEST_URI'], '/' );
			$uri       = untrailingslashit( $uri );

			if ( in_array( $uri, $blacklist, true ) ) {
				return false;
			}

			if ( false !== strpos( $uri, 'feed=' ) ) {
				return false;
			}

			// We do not want to start sessions in the admin unless we're processing an ajax request.
			if ( is_admin() && false === strpos( $uri, 'wp-admin/admin-ajax.php' ) ) {
				return false;
			}

			// Starting sessions while saving the file editor can break the save process, so don't start.
			if ( false !== strpos( $uri, 'wp_scrape_key' ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Retrieve the URI blacklist.
	 *
	 * These are the URIs where we never start sessions.
	 * Legacy method.
	 *
	 * @since 2.5.11
	 *
	 * @return array URI blacklist.
	 */
	public function get_blacklist() {
		$blacklist = apply_filters(
			'edd_session_start_uri_blacklist',
			array(
				'feed',
				'feed/rss',
				'feed/rss2',
				'feed/rdf',
				'feed/atom',
				'comments/feed',
			)
		);

		// Look to see if WordPress is in a sub folder or this is a network site that uses sub folders.
		$folder = str_replace( network_home_url(), '', get_site_url() );

		if ( ! empty( $folder ) ) {
			foreach ( $blacklist as $path ) {
				$blacklist[] = $folder . '/' . $path;
			}
		}

		return $blacklist;
	}

	/**
	 * Whether to use PHP sessions.
	 * Legacy method.
	 *
	 * @return bool
	 */
	public function use_php_sessions() {
		$session_handling = get_option( 'edd_session_handling', false );
		if ( ! empty( $session_handling ) ) {
			return 'php' === $session_handling;
		}

		$use_php_sessions = (bool) get_option( 'edd_use_php_sessions', false );

		if ( ! $use_php_sessions && function_exists( 'session_start' ) ) {
			if ( ! headers_sent() ) {
				session_start();
			}
			// Attempt to detect if the server supports PHP sessions.
			$_SESSION['edd_use_php_sessions'] = true;
			if ( ! empty( $_SESSION['edd_use_php_sessions'] ) ) {
				$use_php_sessions = true;
			}
		}

		// Enable or disable PHP Sessions based on the EDD_USE_PHP_SESSIONS constant.
		if ( defined( 'EDD_USE_PHP_SESSIONS' ) ) {
			$use_php_sessions = (bool) EDD_USE_PHP_SESSIONS;
		}

		$use_php_sessions = apply_filters( 'edd_use_php_sessions', $use_php_sessions );

		update_option( 'edd_session_handling', $use_php_sessions ? 'php' : 'db' );
		delete_option( 'edd_use_php_sessions' );

		return $use_php_sessions;
	}

	/**
	 * Whether to use the cart cookie.
	 * Legacy method.
	 *
	 * @deprecated 3.3.0 Left for backwards compatibility.
	 * @return bool
	 */
	public function use_cart_cookie() {
		$use_cart_cookie = true;
		if ( defined( 'EDD_USE_CART_COOKIE' ) && ! EDD_USE_CART_COOKIE ) {
			$use_cart_cookie = false;
		}

		return (bool) apply_filters( 'edd_use_cart_cookie', $use_cart_cookie );
	}

	/**
	 * Initialize the session handler.
	 * Legacy method.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public function init() {}
}
