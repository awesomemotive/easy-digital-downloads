<?php
/**
 * EDD Session
 *
 * This is a wrapper class for WP_Session / PHP $_SESSION and handles the storage of cart items, purchase sessions, etc
 *
 * @package     EDD
 * @subpackage  Classes/Session
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Session Class
 *
 * @since 1.5
 */
class EDD_Session {

	/**
	 * Holds our session data
	 *
	 * @var array
	 * @access private
	 * @since 1.5
	 */
	private $session;


	/**
	 * Whether to use PHP $_SESSION or WP_Session
	 *
	 * @var bool
	 * @access private
	 * @since 1.5,1
	 */
	private $use_php_sessions = false;

	/**
	 * Session index prefix
	 *
	 * @var string
	 * @access private
	 * @since 2.3
	 */
	private $prefix = '';


	/**
	 * Get things started
	 *
	 * Defines our WP_Session constants, includes the necessary libraries and
	 * retrieves the WP Session instance
	 *
	 * @since 1.5
	 */
	public function __construct() {

		$this->use_php_sessions = $this->use_php_sessions();

		if( $this->use_php_sessions ) {

			if( is_multisite() ) {

				$this->prefix = '_' . get_current_blog_id();

			}

			// Use PHP SESSION (must be enabled via the EDD_USE_PHP_SESSIONS constant)
			add_action( 'init', array( $this, 'maybe_start_session' ), -2 );

		} else {

			if( ! $this->should_start_session() ) {
				return;
			}

			// Use WP_Session (default)

			if ( ! defined( 'WP_SESSION_COOKIE' ) ) {
				define( 'WP_SESSION_COOKIE', 'edd_wp_session' );
			}

			if ( ! class_exists( 'Recursive_ArrayAccess' ) ) {
				require_once EDD_PLUGIN_DIR . 'includes/libraries/class-recursive-arrayaccess.php';
			}

			if ( ! class_exists( 'WP_Session' ) ) {
				require_once EDD_PLUGIN_DIR . 'includes/libraries/class-wp-session.php';
				require_once EDD_PLUGIN_DIR . 'includes/libraries/wp-session.php';
			}

			add_filter( 'wp_session_expiration_variant', array( $this, 'set_expiration_variant_time' ), 99999 );
			add_filter( 'wp_session_expiration', array( $this, 'set_expiration_time' ), 99999 );

		}

		if ( empty( $this->session ) && ! $this->use_php_sessions ) {
			add_action( 'plugins_loaded', array( $this, 'init' ), -1 );
		} else {
			add_action( 'init', array( $this, 'init' ), -1 );
		}

	}

	/**
	 * Setup the WP_Session instance
	 *
	 * @since 1.5
	 * @return void
	 */
	public function init() {

		if( $this->use_php_sessions ) {
			$this->session = isset( $_SESSION['edd' . $this->prefix ] ) && is_array( $_SESSION['edd' . $this->prefix ] ) ? $_SESSION['edd' . $this->prefix ] : array();
		} else {
			$this->session = WP_Session::get_instance();
		}

		$use_cookie = $this->use_cart_cookie();
		$cart       = $this->get( 'edd_cart' );
		$purchase   = $this->get( 'edd_purchase' );

		if ( $use_cookie ) {
			if( ! empty( $cart ) || ! empty( $purchase ) ) {
				$this->set_cart_cookie();
			} else {
				$this->set_cart_cookie( false );
			}
		}

		return $this->session;
	}


	/**
	 * Retrieve session ID
	 *
	 * @since 1.6
	 * @return string Session ID
	 */
	public function get_id() {
		return $this->session->session_id;
	}


	/**
	 * Retrieve a session variable
	 *
	 * @since 1.5
	 * @param string $key Session key
	 * @return mixed Session variable
	 */
	public function get( $key ) {

		$key    = sanitize_key( $key );
		$return = false;

		if ( isset( $this->session[ $key ] ) && ! empty( $this->session[ $key ] ) ) {

			preg_match( '/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $this->session[ $key ], $matches );
			if ( ! empty( $matches ) ) {
				$this->set( $key, null );
				return false;
			}

			if ( is_numeric( $this->session[ $key ] ) ) {
				$return = $this->session[ $key ];
			} else {

				$maybe_json = json_decode( $this->session[ $key ] );

				// Since json_last_error is PHP 5.3+, we have to rely on a `null` value for failing to parse JSON.
				if ( is_null( $maybe_json ) ) {
					$is_serialized = is_serialized( $this->session[ $key ] );
					if ( $is_serialized ) {
						$value = @unserialize( $this->session[ $key ] );
						$this->set( $key, (array) $value );
						$return = $value;
					} else {
						$return = $this->session[ $key ];
					}
				} else {
					$return = json_decode( $this->session[ $key ], true );
				}

			}
		}

		return $return;
	}

	/**
	 * Set a session variable
	 *
	 * @since 1.5
	 *
	 * @param string $key Session key
	 * @param int|string|array $value Session variable
	 * @return mixed Session variable
	 */
	public function set( $key, $value ) {

		$key = sanitize_key( $key );

		if ( is_array( $value ) ) {
			$this->session[ $key ] = wp_json_encode( $value );
		} else {
			$this->session[ $key ] = esc_attr( $value );
		}

		if( $this->use_php_sessions ) {

			$_SESSION['edd' . $this->prefix ] = $this->session;
		}

		return $this->session[ $key ];
	}

	/**
	 * Set a cookie to identify whether the cart is empty or not
	 *
	 * This is for hosts and caching plugins to identify if caching should be disabled
	 *
	 * @since 1.8
	 * @param bool $set Whether to set or destroy
	 * @return void
	 */
	public function set_cart_cookie( $set = true ) {
		if( ! headers_sent() ) {
			if( $set ) {
				@setcookie( 'edd_items_in_cart', '1', time() + 30 * 60, COOKIEPATH, COOKIE_DOMAIN, false );
			} else {
				if ( isset($_COOKIE['edd_items_in_cart']) ) {
					@setcookie( 'edd_items_in_cart', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, false );
				}
			}
		}
	}

	/**
	 * Force the cookie expiration variant time to 23 hours
	 *
	 * @since 2.0
	 * @param int $exp Default expiration (1 hour)
	 * @return int
	 */
	public function set_expiration_variant_time( $exp ) {
		return ( 30 * 60 * 23 );
	}

	/**
	 * Force the cookie expiration time to 24 hours
	 *
	 * @since 1.9
	 * @param int $exp Default expiration (1 hour)
	 * @return int Cookie expiration time
	 */
	public function set_expiration_time( $exp ) {
		return ( 30 * 60 * 24 );
	}

	/**
	 * Starts a new session if one hasn't started yet.
	 *
	 * @return boolean
	 * Checks to see if the server supports PHP sessions
	 * or if the EDD_USE_PHP_SESSIONS constant is defined
	 *
	 * @since 2.1
	 * @author Daniel J Griffiths
	 * @return boolean $ret True if we are using PHP sessions, false otherwise
	 */
	public function use_php_sessions() {

		$ret = false;

		// If the database variable is already set, no need to run autodetection
		$edd_use_php_sessions = (bool) get_option( 'edd_use_php_sessions' );

		if ( ! $edd_use_php_sessions ) {

			// Attempt to detect if the server supports PHP sessions
			if( function_exists( 'session_start' ) ) {

				$this->set( 'edd_use_php_sessions', 1 );

				if( $this->get( 'edd_use_php_sessions' ) ) {

					$ret = true;

					// Set the database option
					update_option( 'edd_use_php_sessions', true );

				}

			}

		} else {
			$ret = $edd_use_php_sessions;
		}

		// Enable or disable PHP Sessions based on the EDD_USE_PHP_SESSIONS constant
		if ( defined( 'EDD_USE_PHP_SESSIONS' ) && EDD_USE_PHP_SESSIONS ) {
			$ret = true;
		} else if ( defined( 'EDD_USE_PHP_SESSIONS' ) && ! EDD_USE_PHP_SESSIONS ) {
			$ret = false;
		}

		return (bool) apply_filters( 'edd_use_php_sessions', $ret );
	}

	/**
	 * Determines if a user has set the EDD_USE_CART_COOKIE
	 *
	 * @since  2.5
	 * @return bool If the store should use the edd_items_in_cart cookie to help avoid caching
	 */
	public function use_cart_cookie() {
		$ret = true;

		if ( defined( 'EDD_USE_CART_COOKIE' ) && ! EDD_USE_CART_COOKIE ) {
			$ret = false;
		}

		return (bool) apply_filters( 'edd_use_cart_cookie', $ret );
	}

	/**
	 * Determines if we should start sessions
	 *
	 * @since  2.5.11
	 * @return bool
	 */
	public function should_start_session() {

		$start_session = true;

		if( ! empty( $_SERVER[ 'REQUEST_URI' ] ) ) {

			$blacklist = $this->get_blacklist();
			$uri       = ltrim( $_SERVER[ 'REQUEST_URI' ], '/' );
			$uri       = untrailingslashit( $uri );

			if( in_array( $uri, $blacklist ) ) {
				$start_session = false;
			}

			if( false !== strpos( $uri, 'feed=' ) ) {
				$start_session = false;
			}

			if( is_admin() && false === strpos( $uri, 'wp-admin/admin-ajax.php' ) ) {
				// We do not want to start sessions in the admin unless we're processing an ajax request
				$start_session = false;
			}

			if( false !== strpos( $uri, 'wp_scrape_key' ) ) {
				// Starting sessions while saving the file editor can break the save process, so don't start
				$start_session = false;
			}

		}

		return apply_filters( 'edd_start_session', $start_session );

	}

	/**
	 * Retrieve the URI blacklist
	 *
	 * These are the URIs where we never start sessions
	 *
	 * @since  2.5.11
	 * @return array
	 */
	public function get_blacklist() {

		$blacklist = apply_filters( 'edd_session_start_uri_blacklist', array(
			'feed',
			'feed/rss',
			'feed/rss2',
			'feed/rdf',
			'feed/atom',
			'comments/feed'
		) );

		// Look to see if WordPress is in a sub folder or this is a network site that uses sub folders
		$folder = str_replace( network_home_url(), '', get_site_url() );

		if( ! empty( $folder ) ) {
			foreach( $blacklist as $path ) {
				$blacklist[] = $folder . '/' . $path;
			}
		}

		return $blacklist;
	}

	/**
	 * Starts a new session if one hasn't started yet.
	 */
	public function maybe_start_session() {

		if( ! $this->should_start_session() ) {
			return;
		}

		if( ! session_id() && ! headers_sent() ) {
			session_start();
		}
	}

}
