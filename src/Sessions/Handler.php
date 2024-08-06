<?php

namespace EDD\Sessions;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handler Class
 *
 * @since 3.3.0
 */
class Handler {
	use Traits\Legacy;
	use Traits\Cookie;

	/**
	 * Dirty flag.
	 *
	 * @since 3.3.0
	 * @var bool
	 */
	protected $dirty = false;

	/**
	 * The session data.
	 *
	 * @since 3.3.0
	 * @var array
	 */
	protected $data = array();

	/**
	 * The prefix for guest session keys.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	private $guest_prefix = 'e_';

	/**
	 * The session.
	 *
	 * @since 3.3.0
	 * @var Session
	 */
	private $session;

	/**
	 * The session cookie name.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	private $session_key;

	/**
	 * The session cookie expiration.
	 *
	 * @since 3.3.0
	 * @var int
	 */
	private $session_expiry;

	/**
	 * One hour before session cookie expiration.
	 *
	 * @since 3.3.0
	 * @var int
	 */
	private $session_expiring;

	/**
	 * Whether the session is active.
	 *
	 * @since 3.3.0
	 * @var bool
	 */
	private $is_active = false;

	/**
	 * The session manager.
	 *
	 * @var EDD\Sessions\Managers\Manager
	 */
	private $manager;

	/**
	 * The class constructor.
	 */
	public function __construct() {
		if ( $this->use_php_sessions() ) {
			$this->manager = new Managers\PHP();
		} else {
			$this->manager = new Managers\Database();
		}
		add_action( 'shutdown', array( $this, 'save' ), 20 );
		add_action( 'wp_logout', array( $this, 'destroy' ) );
	}

	/**
	 * Gets the session data for a specific key.
	 * Legacy method.
	 *
	 * @since 3.3.0
	 * @param string $key           The key to get.
	 * @param mixed  $default_value The default value to return if the key is not set.
	 * @return mixed
	 */
	public function get( $key, $default_value = null ) {
		if ( ! $this->is_active ) {
			$this->maybe_start_session();
		}
		$key = sanitize_key( $key );

		if ( ! isset( $this->data[ $key ] ) ) {
			return $default_value;
		}

		return $this->data[ $key ];
	}

	/**
	 * Sets the session data for a specific key.
	 * Legacy method.
	 *
	 * @since 3.3.0
	 * @param string $key   The key to set.
	 * @param mixed  $value The value to set.
	 * @return mixed
	 */
	public function set( $key, $value ) {
		if ( ! $this->is_active ) {
			$this->set_cart_cookie();
			$this->maybe_start_session( true );
		}
		if ( ! is_array( $this->data ) ) {
			$this->data = array();
		}
		$key = sanitize_key( $key );
		if ( ! array_key_exists( $key, $this->data ) || $value !== $this->get( $key ) ) {
			if ( ! empty( $value ) ) {
				$this->data[ $key ] = $this->sanitize( $value );
			} elseif ( isset( $this->data[ $key ] ) ) {
				unset( $this->data[ $key ] );
			}
			$this->dirty = true;
			if ( \EDD\Utils\Request::is_request( 'ajax' ) ) {
				$this->save();
			}
		}

		return isset( $this->data[ $key ] ) ? $this->data[ $key ] : null;
	}

	/**
	 * Setup cookie and customer ID.
	 * Legacy method.
	 *
	 * @since 3.3.0
	 * @param bool $needs_cookie Whether the session needs a cookie.
	 */
	public function maybe_start_session( $needs_cookie = false ) {
		if ( $this->is_active ) {
			return;
		}
		if ( ! $this->is_request_valid_for_session() ) {
			return;
		}

		/**
		 * Allow developers to disable session creation (legacy filter).
		 *
		 * @param bool $start_session Whether to start the session.
		 */
		if ( ! apply_filters( 'edd_start_session', true ) ) {
			return;
		}

		$cookie = $this->get_session_cookie();
		if ( ! $cookie && ! $needs_cookie ) {
			return;
		}
		$this->manager->start();
		if ( ! $cookie ) {
			$this->set_session_cookie();
			$this->data      = $this->get_session_data();
			$this->is_active = true;
			return;
		}

		$this->session_key      = $cookie['session_key'];
		$this->data             = $this->get_session_data();
		$this->session_expiry   = $cookie['session_expiration'];
		$this->session_expiring = $cookie['session_expiring'];
		$this->has_cookie       = true;
		$this->is_active        = true;

		if ( ! $this->is_session_cookie_valid() ) {
			edd_debug_log( 'Session cookie invalid for: ' . $this->session_key );
			$this->destroy();
			$this->is_active = false;
		}

		// Update session if it's close to expiring.
		if ( time() > $this->session_expiring ) {
			$this->session_expiry   = $this->get_session_expiry( true );
			$this->session_expiring = $this->get_session_expiring( true );
			$this->set_session_cookie();
			$this->dirty = true;
			$this->save();
			edd_debug_log( sprintf( 'Session expiration for %1$s was updated to: %2$s', $this->session_key, $this->session_expiry ) );
		}
	}

	/**
	 * Saves the session data to the database and updates the cache.
	 *
	 * @param string $old_session_key The old session key.
	 * @return void
	 */
	public function save( $old_session_key = '' ) {
		if ( ! $this->dirty ) {
			return;
		}

		if ( ! $this->is_request_valid_for_session() ) {
			return;
		}

		if ( ! $this->has_session() ) {
			return;
		}

		// If there is no session key, return.
		if ( empty( $this->get_session_key() ) ) {
			return;
		}

		// Save the session.
		$this->manager->save( $this->session_key, $this->data, $this->get_session_expiry() );

		$this->dirty = false;

		// Delete the old session if it's a guest.
		if ( $old_session_key && $this->should_delete( $old_session_key ) ) {
			$this->manager->delete( $old_session_key );
		}
	}

	/**
	 * Forget all session data without destroying it.
	 *
	 * @since 3.3.0
	 */
	public function forget() {
		\EDD\Utils\Cookies::set( $this->cookie );
		$this->set_cart_cookie( false );

		edd_empty_cart();

		$this->data        = array();
		$this->dirty       = false;
		$this->session_key = $this->get_session_key( true );
	}

	/**
	 * Destroys the session.
	 *
	 * @since 3.3.0
	 */
	public function destroy() {
		$this->manager->delete( $this->get_session_key() );
		$this->forget();
	}

	/**
	 * Get session data.
	 *
	 * @return array
	 */
	private function get_session_data() {
		return $this->manager->get_session_data( $this->get_session_key() );
	}

	/**
	 * Get/set the session expiration.
	 *
	 * @since 3.3.0
	 * @param bool $force Whether to force the expiration time to be set.
	 * @return int
	 */
	private function get_session_expiry( $force = false ) {
		if ( ! $this->session_expiry || $force ) {
			$session = $this->manager->get_session( $this->get_session_key() );
			if ( ! empty( $session->expiry ) ) {
				$this->session_expiry = $session->expiry;
			} else {
				$this->session_expiry = time() + intval( $this->set_expiration_time() );
			}
		}

		return $this->session_expiry;
	}

	/**
	 * Get/set the session expiring (one hour before expiration).
	 *
	 * @since 3.3.0
	 * @param bool $force Whether to force the expiration time to be set.
	 * @return int
	 */
	private function get_session_expiring( $force = false ) {
		if ( ! $this->session_expiring || $force ) {
			$this->session_expiring = $this->get_session_expiry() - HOUR_IN_SECONDS;
		}

		return $this->session_expiring;
	}

	/**
	 * Generate a unique session ID for guests, or return user ID if logged in.
	 * Uses Portable PHP password hashing framework to generate a unique cryptographically strong ID.
	 * Legacy method (name).
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private function get_session_key( $reset = false ) {
		if ( ! is_null( $this->session_key ) && ! $reset ) {
			return $this->session_key;
		}

		if ( is_user_logged_in() ) {
			$session_key = $this->get_logged_in_user_key();
		}

		if ( ! empty( $session_key ) ) {
			$this->session_key = $session_key;
		} else {

			require_once ABSPATH . 'wp-includes/class-phpass.php';
			$hasher = new \PasswordHash( 8, false );

			$this->session_key = $this->guest_prefix . substr( wp_hash( $hasher->get_random_bytes( 32 ) ), 2 );
		}

		return $this->session_key;
	}

	/**
	 * Gets the logged in user key.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private function get_logged_in_user_key() {
		return $this->manager->get_logged_in_user_key();
	}

	/**
	 * Checks if the current session should be deleted.
	 *
	 * @param string $key The session key.
	 * @return bool
	 */
	private function should_delete( $key ) {
		$current_user_id = $this->get_logged_in_user_key();

		return $current_user_id !== $key && ! ( get_user_by( 'id', $key ) instanceof \WP_User );
	}

	/**
	 * Sanitizes sensitive data from the session array.
	 *
	 * @param mixed $data The data to sanitize.
	 * @return mixed
	 */
	private function sanitize( $data ) {
		if ( ! is_array( $data ) ) {
			return esc_attr( $data );
		}
		$disallowed_keys = array(
			'post_data',
			'card_info',
			'gateway',
			'gateway_nonce',
		);
		foreach ( $disallowed_keys as $key ) {
			if ( isset( $data[ $key ] ) ) {
				unset( $data[ $key ] );
			}
		}

		return $data;
	}

	/**
	 * Checks whether the type of request is valid for starting a session.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	private function is_request_valid_for_session() {
		return \EDD\Utils\Request::is_request( array( 'frontend', 'rest' ) );
	}
}
