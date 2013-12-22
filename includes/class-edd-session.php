<?php
/**
 * EDD Session
 *
 * This is a wrapper class for WP_Session / PHP $_SESSION and handles the storage of cart items, purchase sessions, etc
 *
 * @package     EDD
 * @subpackage  Classes/Session
 * @copyright   Copyright (c) 2013, Pippin Williamson
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
	private $session = array();


	/**
	 * Whether to use PHP $_SESSION or WP_Session
	 *
	 * PHP $_SESSION is opt-in only by defining the EDD_USE_PHP_SESSIONS constant
	 *
	 * @var bool
	 * @access private
	 * @since 1.5,1
	 */
	private $use_php_sessions = false;


	/**
	 * Get things started
	 *
	 * Defines our WP_Session constants, includes the necessary libraries and
	 * retrieves the WP Session instance
	 *
	 * @since 1.5
	 */
	public function __construct() {

		$this->use_php_sessions = defined( 'EDD_USE_PHP_SESSIONS' ) && EDD_USE_PHP_SESSIONS;

		if( $this->use_php_sessions ) {

			// Use PHP SESSION (must be enabled via the EDD_USE_PHP_SESSIONS constant)

			if( ! session_id() )
				add_action( 'init', 'session_start', -2 );

		} else {

			// Use WP_Session (default)

			if ( ! defined( 'WP_SESSION_COOKIE' ) )
				define( 'WP_SESSION_COOKIE', 'edd_wp_session' );

			if ( ! class_exists( 'Recursive_ArrayAccess' ) )
				require_once EDD_PLUGIN_DIR . 'includes/libraries/class-recursive-arrayaccess.php';

			if ( ! class_exists( 'WP_Session' ) ) {
				require_once EDD_PLUGIN_DIR . 'includes/libraries/class-wp-session.php';
				require_once EDD_PLUGIN_DIR . 'includes/libraries/wp-session.php';
			}

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
	 * @access public
	 * @since 1.5
	 * @return void
	 */
	public function init() {

		if( $this->use_php_sessions )
			$this->session = isset( $_SESSION['edd'] ) && is_array( $_SESSION['edd'] ) ? $_SESSION['edd'] : array();
		else
			$this->session = WP_Session::get_instance();

		$cart     = $this->get( 'edd_cart' );
		$purchase = $this->get( 'edd_purchase' );

		if( ! empty( $cart ) || ! empty( $purchase ) ) {
			$this->set_cart_cookie();
		} else {
			$this->set_cart_cookie( false );
		}

		return $this->session;
	}


	/**
	 * Retrieve session ID
	 *
	 * @access public
	 * @since 1.6
	 * @return string Session ID
	 */
	public function get_id() {
		return $this->session->session_id;
	}


	/**
	 * Retrieve a session variable
	 *
	 * @access public
	 * @since 1.5
	 * @param string $key Session key
	 * @return string Session variable
	 */
	public function get( $key ) {
		$key = sanitize_key( $key );
		return isset( $this->session[ $key ] ) ? maybe_unserialize( $this->session[ $key ] ) : false;
	}

	/**
	 * Set a session variable
	 *
	 * @since 1.5
	 *
	 * @param $key Session key
	 * @param $value Session variable
	 * @return mixed Session variable
	 */
	public function set( $key, $value ) {
		$key = sanitize_key( $key );

		if ( is_array( $value ) )
			$this->session[ $key ] = serialize( $value );
		else
			$this->session[ $key ] = $value;

		if( $this->use_php_sessions )
			$_SESSION['edd'] = $this->session;

		return $this->session[ $key ];
	}

	/**
	 * Set a cookie to identify whether the cart is empty or not
	 *
	 * This is for hosts and caching plugins to identify if caching should be disabled
	 *
	 * @access public
	 * @since 1.8
	 * @param string $set Whether to set or destroy
	 * @return void
	 */
	public function set_cart_cookie( $set = true ) {
		if( ! headers_sent() ) {
			if( $set ) {
				setcookie( 'edd_items_in_cart', '1', time() + 30 * 60, COOKIEPATH, COOKIE_DOMAIN, false );
			} else {
				setcookie( 'edd_items_in_cart', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, false );
			}
		}
	}
}