<?php
/**
 * Base Custom Database Class.
 *
 * @package     Database
 * @subpackage  Base
 * @copyright   Copyright (c) 2020
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace EDD\Database;

use AllowDynamicProperties;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * The base class that all other database base classes extend.
 *
 * This class attempts to provide some universal immutability to all other
 * classes that extend it, starting with a magic getter, but likely expanding
 * into a magic call handler and others.
 *
 * @since 1.0.0
 */
#[AllowDynamicProperties]
class Base {

	/**
	 * The name of the PHP global that contains the primary database interface.
	 *
	 * For example, WordPress traditionally uses 'wpdb', but other applications
	 * may use something else, or you may be doing something really cool that
	 * requires a custom interface.
	 *
	 * A future version of this utility may abstract this out entirely, so
	 * custom calls to the get_db() should be avoided if at all possible.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $db_global = 'wpdb';

	/** Global Properties *****************************************************/

	/**
	 * Global prefix used for tables/hooks/cache-groups/etc...
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $prefix = 'edd';

	/**
	 * The last database error, if any.
	 *
	 * @since 1.0.0
	 * @var   mixed
	 */
	protected $last_error = false;

	/** Public ****************************************************************/

	/**
	 * Magic isset'ter for immutability.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __isset( $key = '' ) {

		// No more uppercase ID properties ever
		if ( 'ID' === $key ) {
			$key = 'id';
		}

		// Class method to try and call
		$method = "get_{$key}";

		// Return property if exists
		if ( method_exists( $this, $method ) ) {
			return true;

		// Return get method results if exists
		} elseif ( property_exists( $this, $key ) ) {
			return true;
		}

		// Return false if not exists
		return false;
	}

	/**
	 * Magic getter for immutability.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get( $key = '' ) {

		// No more uppercase ID properties ever
		if ( 'ID' === $key ) {
			$key = 'id';
		}

		// Class method to try and call
		$method = "get_{$key}";

		// Return property if exists
		if ( method_exists( $this, $method ) ) {
			return call_user_func( array( $this, $method ) );

		// Return get method results if exists
		} elseif ( property_exists( $this, $key ) ) {
			return $this->{$key};
		}

		// Return null if not exists
		return null;
	}

	/**
	 * Converts the given object to an array.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array version of the given object.
	 */
	public function to_array() {
		return get_object_vars( $this );
	}

	/** Protected *************************************************************/

	/**
	 * Maybe append the prefix to string.
	 *
	 * @since 1.0.0
	 *
	 * @param string $string
	 * @param string $sep
	 * @return string
	 */
	protected function apply_prefix( $string = '', $sep = '_' ) {
		return ! empty( $this->prefix )
			? "{$this->prefix}{$sep}{$string}"
			: $string;
	}

	/**
	 * Return the first letters of a string of words with a separator.
	 *
	 * Used primarily to guess at table aliases when none is manually set.
	 *
	 * Applies the following formatting to a string:
	 * - Trim whitespace
	 * - No accents
	 * - No trailing underscores
	 *
	 * @since 1.0.0
	 *
	 * @param string $string
	 * @param string $sep
	 * @return string
	 */
	protected function first_letters( $string = '', $sep = '_' ) {

		// Set empty default return value
		$retval = '';

		// Bail if empty or not a string
		if ( empty( $string ) || ! is_string( $string ) ) {
			return $retval;
		}

		// Trim spaces off the ends
		$unspace = trim( $string );
		$accents = remove_accents( $unspace );
		$lower   = strtolower( $accents );
		$parts   = explode( $sep, $lower );

		// Loop through parts and concatenate the first letters together
		foreach ( $parts as $part ) {
			$retval .= substr( $part, 0, 1 );
		}

		// Return the result
		return $retval;
	}

	/**
	 * Sanitize a table name string.
	 *
	 * Used to make sure that a table name value meets MySQL expectations.
	 *
	 * Applies the following formatting to a string:
	 * - Trim whitespace
	 * - No accents
	 * - No special characters
	 * - No hyphens
	 * - No double underscores
	 * - No trailing underscores
	 *
	 * @since 1.0.0
	 *
	 * @param string $name The name of the database table
	 *
	 * @return string Sanitized database table name
	 */
	protected function sanitize_table_name( $name = '' ) {

		// Bail if empty or not a string
		if ( empty( $name ) || ! is_string( $name ) ) {
			return false;
		}

		// Trim spaces off the ends
		$unspace = trim( $name );

		// Only non-accented table names (avoid truncation)
		$accents = remove_accents( $unspace );

		// Only lowercase characters, hyphens, and dashes (avoid index corruption)
		$lower   = sanitize_key( $accents );

		// Replace hyphens with single underscores
		$under   = str_replace( '-',  '_', $lower );

		// Single underscores only
		$single  = str_replace( '__', '_', $under );

		// Remove trailing underscores
		$clean   = trim( $single, '_' );

		// Bail if table name was garbaged
		if ( empty( $clean ) ) {
			return false;
		}

		// Return the cleaned table name
		return $clean;
	}

	/**
	 * Set class variables from arguments.
	 *
	 * @since 1.0.0
	 * @param array $args
	 */
	protected function set_vars( $args = array() ) {

		// Bail if empty or not an array
		if ( empty( $args ) ) {
			return;
		}

		// Cast to an array
		if ( ! is_array( $args ) ) {
			$args = (array) $args;
		}

		// Set all properties
		foreach ( $args as $key => $value ) {
			$this->{$key} = $value;
		}
	}

	/**
	 * Return the global database interface.
	 *
	 * See: https://core.trac.wordpress.org/ticket/31556
	 *
	 * @since 1.0.0
	 *
	 * @return \wpdb Database interface, or False if not set
	 */
	protected function get_db() {

		// Default database return value (might change)
		$retval = false;

		// Look for a commonly used global database interface
		if ( isset( $GLOBALS[ $this->db_global ] ) ) {
			$retval = $GLOBALS[ $this->db_global ];
		}

		/*
		 * Developer note:
		 *
		 * It should be impossible for a database table to be interacted with
		 * before the primary database interface it is setup.
		 *
		 * However, because applications are complicated, it is unsafe to assume
		 * anything, so this silently returns false instead of halting everything.
		 *
		 * If you are here because this method is returning false for you, that
		 * means the database table is being invoked too early in the lifecycle
		 * of the application.
		 *
		 * In WordPress, that means before the $wpdb global is created; in other
		 * environments, you will need to adjust accordingly.
		 */

		// Return the database interface
		return $retval;
	}

	/**
	 * Check if an operation succeeded.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $result
	 * @return bool
	 */
	protected function is_success( $result = false ) {

		// Bail if no row exists
		if ( empty( $result ) ) {
			$retval = false;

		// Bail if an error occurred
		} elseif ( is_wp_error( $result ) ) {
			$this->last_error = $result;
			$retval           = false;

		// No errors
		} else {
			$retval = true;
		}

		// Return the result
		return (bool) $retval;
	}
}
