<?php
/**
 * Empty class for handling fully deprecated classes.
 * This class is used to prevent fatal errors when a class is removed in a future version.
 * Classes are aliased to this class in the `includes/deprecated/classes.php` file.
 *
 * @package EDD
 * @subpackage Deprecated
 * @since 3.2.10
 */

namespace EDD\Deprecated;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Empty class.
 *
 * @since 3.2.10
 */
class EmptyClass {

	/**
	 * Magic method to prevent fatal errors when a class is removed.
	 *
	 * @since 3.2.10
	 * @param string $name Property name.
	 * @return null
	 */
	public function __get( $name ) {
		_edd_deprecated_function( __CLASS__ . '->' . $name, '3.2.10' );

		return isset( $this->$name ) ? $this->$name : null;
	}
}
