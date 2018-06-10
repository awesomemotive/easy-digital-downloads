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
abstract class Base_Object {

	/**
	 * Object constructor.
	 *
	 * @access public
	 * @since  3.0
	 *
	 * @param mixed $object Object to populate members for.
	 */
	public function __construct( $object = null ) {
		if ( empty( $object ) ) {
			return;
		}

		if ( ! is_object( $object ) ) {
			$object = (object) $object;
		}

		foreach ( get_object_vars( $object ) as $key => $value ) {
			$this->{$key} = $value;
		}
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
	 * Magic __get method to dispatch a call to retrieve a protected property.
	 *
	 * @since 3.0
	 *
	 * @param mixed $key The field you are trying to get
	 *
	 * @return mixed Either the method or property that best matches the key
	 */
	public function __get( $key = '' ) {
		$retval = null;
		$key    = sanitize_key( $key );

		// Never allow for uppercase ID fields in our own
		if ( 'ID' === $key ) {
			$key = 'id';
		}

		// Try the method first
		if ( method_exists( $this, "get_{$key}" ) ) {
			$retval = call_user_func( array( $this, "get_{$key}" ) );

		// Try the property last
		} elseif ( property_exists( $this, $key ) ) {
			$retval = $this->{$key};
		}

		// Return whatever was gettable
		return $retval;
	}
}