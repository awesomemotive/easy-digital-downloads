<?php

/**
 * A Base WordPress Database Table class
 *
 * @author  JJJ
 * @link    https://jjj.blog
 * @version 1.4.0
 * @license https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * The base class that all other database base classes extend.
 *
 * This class attempts to provide some universal immutability to all other
 * database interfaces, starting with a magic getter, but likely expanding into
 * a magic call handler and others.
 *
 * @since 3.0.0
 */
class EDD_DB_Base {

	/**
	 * Magic getter for immutability.
	 *
	 * @since 3.0.0
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
		} elseif ( isset( $this->{$key} ) ) {
			return $this->{$key};

		// Return null if not exists
		} else {
			return null;
		}
	}
}
