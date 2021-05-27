<?php
/**
 * Sandhills Development Persistent Dismissible Utility
 *
 * @package SandhillsDev
 * @subpackage Utilities
 */
namespace Sandhills\Utils;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * This class_exists() check avoids a fatal error if this class exists in more
 * than one included plugin/theme, and should not be removed.
 */
if ( ! class_exists( 'Sandhills\Utils\Persistent_Dismissible' ) ) :

	/**
	 * Class for encapsulating the logic required to maintain a relationship between
	 * the database, a dismissible UI element with an optional lifespan, and a
	 * user's desire to dismiss that UI element.
	 *
	 * Think of this like a WordPress Transient, but without in-memory cache support
	 * and that uses the `wp_usermeta` database table instead of `wp_options`.
	 *
	 * @version 1.0.0
	 */
	class Persistent_Dismissible {

		/**
		 * Get the value of a persistent dismissible.
		 *
		 * @since 1.0.0
		 * @param array $args See parse_args().
		 * @return mixed User meta value on success, false on failure.
		 */
		public static function get( $args = array() ) {

			// Parse arguments.
			$r = self::parse_args( $args );

			// Bail if invalid arguments.
			if ( ! self::check_args( $r ) ) {
				return false;
			}

			// Get prefixed option names.
			$eol_id       = self::get_eol_id( $r );
			$prefix       = self::get_prefix( $r );
			$prefixed_id  = $prefix . $r['id'];
			$prefixed_eol = $prefix . $eol_id;

			// Get return value & end-of-life.
			$retval   = get_user_meta( $r['user_id'], $prefixed_id,  true );
			$lifespan = get_user_meta( $r['user_id'], $prefixed_eol, true );

			// Prefer false over default return value of get_user_meta()
			if ( '' === $retval ) {
				$retval = false;
			}

			// If end-of-life, delete it. This needs to be inside get() because we
			// are not relying on WP Cron for garbage collection. This mirrors
			// behavior found inside of WordPress core.
			if ( self::is_eol( $lifespan ) ) {
				delete_user_option( $r['user_id'], $r['id'], $r['global'] );
				delete_user_option( $r['user_id'], $eol_id,  $r['global'] );
				$retval = false;
			}

			// Return the value.
			return $retval;
		}

		/**
		 * Set the value of a persistent dismissible.
		 *
		 * @since 1.0.0
		 * @param array $args See parse_args().
		 * @return int|bool User meta ID if the option didn't exist, true on
		 *                  successful update, false on failure.
		 */
		public static function set( $args = array() ) {

			// Parse arguments.
			$r = self::parse_args( $args );

			// Bail if invalid arguments.
			if ( ! self::check_args( $r ) ) {
				return false;
			}

			// Get lifespan and prefixed option names.
			$lifespan     = self::get_lifespan( $r );
			$eol_id       = self::get_eol_id( $r );
			$prefix       = self::get_prefix( $r );
			$prefixed_id  = $prefix . $r['id'];
			$prefixed_eol = $prefix . $eol_id;

			// No dismissible data, so add it.
			if ( '' === get_user_meta( $r['user_id'], $prefixed_id, true ) ) {

				// Add lifespan.
				if ( ! empty( $lifespan ) ) {
					add_user_meta( $r['user_id'], $prefixed_eol, $lifespan, true );
				}

				// Add dismissible data.
				$retval = add_user_meta( $r['user_id'], $prefixed_id, $r['value'], true );

				// Dismissible data found in database.
			} else {

				// Plan to update.
				$update = true;

				// Dismissible to update has new lifespan.
				if ( ! empty( $lifespan ) ) {

					// If lifespan is requested but the dismissible has no end-of-life,
					// delete them both and re-create them, to avoid race conditions.
					if ( '' === get_user_meta( $r['user_id'], $prefixed_eol, true ) ) {
						delete_user_option( $r['user_id'], $r['id'], $r['global'] );
						add_user_meta( $r['user_id'], $prefixed_eol, $lifespan, true );
						$retval = add_user_meta( $r['user_id'], $prefixed_id, $r['value'], true );
						$update = false;

						// Update the lifespan.
					} else {
						update_user_option( $r['user_id'], $eol_id, $lifespan, $r['global'] );
					}
				}

				// Update the dismissible value.
				if ( ! empty( $update ) ) {
					$retval = update_user_option( $r['user_id'], $r['id'], $r['value'], $r['global'] );
				}
			}

			// Return the value.
			return $retval;
		}

		/**
		 * Delete a persistent dismissible.
		 *
		 * @since 1.0.0
		 * @param array $args See parse_args().
		 * @return bool True on success, false on failure.
		 */
		public static function delete( $args = array() ) {

			// Parse arguments.
			$r = self::parse_args( $args );

			// Bail if invalid arguments.
			if ( ! self::check_args( $r ) ) {
				return false;
			}

			// Get the end-of-life ID.
			$eol_id = self::get_eol_id( $r );

			// Delete.
			delete_user_option( $r['user_id'], $r['id'], $r['global'] );
			delete_user_option( $r['user_id'], $eol_id,  $r['global'] );

			// Success.
			return true;
		}

		/**
		 * Parse array of key/value arguments.
		 *
		 * Used by get(), set(), and delete(), to ensure default arguments are set.
		 *
		 * @since 1.0.0
		 * @param array|string $args {
		 *     Array or string of arguments to identify the persistent dismissible.
		 *
		 *     @type string      $id       Required. ID of the persistent dismissible.
		 *     @type string      $user_id  Optional. User ID. Default to current user ID.
		 *     @type int|string  $value    Optional. Value to store. Default to true.
		 *     @type int|string  $life     Optional. Lifespan. Default to 0 (infinite)
		 *     @type bool        $global   Optional. Multisite, all sites. Default true.
		 * }
		 * @return array
		 */
		private static function parse_args( $args = array() ) {
			return wp_parse_args( $args, array(
				'id'      => '',
				'user_id' => get_current_user_id(),
				'value'   => true,
				'life'    => 0,
				'global'  => true,
			) );
		}

		/**
		 * Check that required arguments exist.
		 *
		 * @since 1.0.0
		 * @param array $args See parse_args().
		 * @return bool True on success, false on failure.
		 */
		private static function check_args( $args = array() ) {
			return ! empty( $args['id'] ) && ! empty( $args['user_id'] );
		}

		/**
		 * Get the string used to prefix user meta for non-global dismissibles.
		 *
		 * @since 1.0.0
		 * @global WPDB $wpdb
		 * @param array $args See parse_args().
		 * @return string Maybe includes the blog prefix.
		 */
		private static function get_prefix( $args = array() ) {
			global $wpdb;

			// Default value
			$retval = '';

			// Maybe append the blog prefix for non-global dismissibles
			if ( empty( $args['global'] ) ) {
				$retval = $wpdb->get_blog_prefix();
			}

			// Return
			return $retval;
		}

		/**
		 * Get the lifespan for a persistent dismissible.
		 *
		 * @since 1.0.0
		 * @param array $args See parse_args().
		 * @return int
		 */
		private static function get_lifespan( $args = array() ) {
			return ! empty( $args['life'] ) && is_numeric( $args['life'] )
				? time() + absint( $args['life'] )
				: 0;
		}

		/**
		 * Get the string used to identify the ID for storing the end-of-life.
		 *
		 * @since 1.0.0
		 * @param array $args See parse_args().
		 * @return string '_eol' appended to the ID (for its end-of-life timestamp).
		 */
		private static function get_eol_id( $args = array() ) {
			return sanitize_key( $args['id'] ) . '_eol';
		}

		/**
		 * Check whether a timestamp is beyond the current time.
		 *
		 * @since 1.0.0
		 * @param int $timestamp A Unix timestamp. Default 0.
		 * @return bool True if end-of-life, false if not.
		 */
		private static function is_eol( $timestamp = 0 ) {
			return is_numeric( $timestamp ) && ( $timestamp < time() );
		}
	}

endif;
