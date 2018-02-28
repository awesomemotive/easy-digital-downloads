<?php

/**
 * Objects: Base class
 *
 * @package Plugins/EDD/Database/Objects/Base
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class EDD_DB_Object {

	/**
	 * Construct a database object
	 *
	 * @since 3.0.0
	 *
	 * @param mixed Null by default, Array/Object if not
	 */
	public function __construct( $item = null ) {
		if ( ! empty( $item ) ) {
			$this->init( $item );
		}
	}

	/**
	 * Get a property from a class if it exists, or null if not.
	 *
	 * @since 3.0.0
	 *
	 * @param string $prop
	 *
	 * @return mixed Null if not set
	 */
	public function __get( $prop = '' ) {

		// Magically get some properties to avoid known developer issues
		switch ( $prop ) {

			// Swap uppercase ID for correct lowercase id
			case 'ID' :
				$prop = 'id';
				break;
		}

		// Return prop if exists, else null
		return isset( $this->{$prop} )
			? $this->{$prop}
			: null;
	}

	/**
	 * Initialize class properties based on data array
	 *
	 * @since 3.0.0
	 *
	 * @param array $data
	 */
	private function init( $data = array() ) {

		// Convert to an array for speedy looping
		$data = (array) $data;

		// Loop through keys and set object values
		foreach ( $data as $key => $value ) {
			$this->{$key} = $value;
		}
	}

	/**
	 * Return
	 * @since 3.0.0
	 *
	 * @return boolean
	 */
	public function exists() {
		return ! empty( $this->id );
	}
}
