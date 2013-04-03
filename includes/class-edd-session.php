<?php
/**
 * EDD Session
 *
 * This is a wrapper calss for WP_Session and handles the storage of cart items, purchase sessions, etc
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
	 * Get things started
	 *
	 * Defines our WP_Session constants, includes the necessary libraries and
	 * retrieves the WP Session instance
	 *
	 * @access public
	 * @since 1.5
	 * @return void
	 */
	public function __construct() {
		if ( ! defined( 'WP_SESSION_COOKIE' ) )
			define( 'WP_SESSION_COOKIE', 'wordpress_wp_session' );

		if ( ! class_exists( 'Recursive_ArrayAccess' ) )
			require_once EDD_PLUGIN_DIR . 'includes/libraries/class-recursive-arrayaccess.php';

		if ( ! class_exists( 'WP_Session' ) ) {
			require_once EDD_PLUGIN_DIR . 'includes/libraries/class-wp-session.php';
			require_once EDD_PLUGIN_DIR . 'includes/libraries/wp-session.php';
		}

		if ( empty( $this->session ) )
			add_action( 'plugins_loaded', array( $this, 'init' ) );
		else
			add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Setup the WP_Session instance
	 *
	 * @access public
	 * @since 1.5
	 * @return void
	 */
	public function init() {
		$this->session = WP_Session::get_instance();
		return $this->session;
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
	 * @access public
	 * @since 1.5
	 * @param string $key Session key
	 * @param string $variable Session variable
	 * @return array Session variable
	 */
	public function set( $key, $value ) {
		$key = sanitize_key( $key );

		if ( is_array( $value ) )
			$this->session[ $key ] = serialize( $value );
		else
			$this->session[ $key ] = $value;

		return $this->session[ $key ];
	}
}