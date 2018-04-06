<?php
/**
 * Base Core Object.
 *
 * @package     EDD
 * @subpackage  Core
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */
namespace EDD;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Implements a base object to be extended by core objects.
 *
 * @since 3.0.0
 * @abstract
 */
abstract class Base_Object {

	/**
	 * Object constructor.
	 *
	 * @access public
	 * @since  1.9
	 *
	 * @param mixed $object Object to populate members for.
	 */
	public function __construct( $object = null ) {
		if ( $object ) {
			foreach ( get_object_vars( $object ) as $key => $value ) {
				$this->{$key} = $value;
			}
		}
	}

	/**
	 * Converts the given object to an array.
	 *
	 * @since 3.0.0
	 *
	 * @return array Array version of the given object.
	 */
	public function to_array() {
		return get_object_vars( $this );
	}
}