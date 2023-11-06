<?php
/**
 * Base Core Object.
 *
 * @package     EDD
 * @subpackage  Core
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Implements a base object to be extended by core objects.
 *
 * @since 3.0
 * @abstract
 */
#[\AllowDynamicProperties]
abstract class Base_Object {

	/**
	 * The UUID for the object.
	 *
	 * @since 3.0
	 * @access protected
	 * @var    string
	 */
	protected $uuid;

	/**
	 * Object constructor.
	 *
	 * @since 3.0
	 *
	 * @param mixed $args Object to populate members for.
	 */
	public function __construct( $args = null ) {
		$this->set_vars( $args );
	}

	/**
	 * Magic isset'ter for immutability.
	 *
	 * @since 3.0
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
	 * @since 3.0
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
	 * @since 3.0
	 *
	 * @return array Array version of the given object.
	 */
	public function to_array() {
		return get_object_vars( $this );
	}

	/**
	 * Set class variables from arguments.
	 *
	 * @since 3.0
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
}
