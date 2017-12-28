<?php
/**
 * Registry utility superclass
 *
 * This class should be extended to create object registries.
 *
 * @package     EDD
 * @subpackage  Classes/Utilities
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

/**
 * Defines the construct for building an item registry.
 *
 * @since 3.0.0
 * @abstract
 */
abstract class EDD_Registry extends \ArrayObject {

	/**
	 * Array of registry items.
	 *
	 * @since 3.0
	 * @var   array
	 */
	private $items = array();

	/**
	 * Initializes the registry.
	 *
	 * Each sub-class will need to do various initialization operations in this method.
	 *
	 * @since 3.0
	 */
	abstract public function init();

	/**
	 * Adds an item to the registry.
	 *
	 * @since 3.0
	 *
	 * @throws EDD_Exception If the `$attributes` array is empty.
	 *
	 * @param int    $item_id   Item ID.
	 * @param array  $attributes {
	 *     Item attributes.
	 *
	 *     @type string $class Item handler class.
	 *     @type string $file  Item handler class file.
	 * }
	 * @return true|\WP_Error True if `$attributes` is not empty, otherwise a WP_Error object.
	 */
	public function add_item( $item_id, $attributes ) {
		$result = false;

		if ( ! empty( $attributes ) ) {
			foreach ( $attributes as $attribute => $value ) {
				$this->items[ $item_id ][ $attribute ] = $value;
			}

			$result = true;
		} else {

			$message = sprintf( "The attributes were missing when attempting to add item '%s'.", (string) $item_id );

			throw new \EDD_Exception( $message );
		}

		return $result;
	}

	/**
	 * Removes an item from the registry by ID.
	 *
	 * @since 3.0
	 *
	 * @param string $item_id Item ID.
	 */
	public function remove_item( $item_id ) {
		unset( $this->items[ $item_id ] );
	}

	/**
	 * Retrieves an item and its associated attributes.
	 *
	 * @since 3.0
	 *
	 * @throws EDD_Exception if the item does not exist.
	 *
	 * @param string $item_id Item ID.
	 * @return array Array of attributes for the item if the item is set,
	 *               otherwise an empty array.
	 */
	public function get_item( $item_id ) {

		$result = array();

		if ( isset( $this->items[ $item_id ] ) ) {

			$result = $this->items[ $item_id ];

		} else {

			$message = sprintf( "The item '%s' does not exist.", (string) $item_id );

			throw new \EDD_Exception( $message );
		}

		return $result;
	}

	/**
	 * Retrieves registered items.
	 *
	 * @since 3.0
	 *
	 * @return array The list of registered items.
	 */
	public function get_items() {
		return $this->items;
	}

	/**
	 * Only intended for use by tests.
	 *
	 * @since 3.0
	 */
	public function _reset_items() {
		if ( ! defined( 'WP_TESTS_DOMAIN' ) ) {
			_doing_it_wrong( 'This method is only intended for use in phpunit tests', '3.0' );
		} else {
			$this->items = array();
		}
	}

	/**
	 * Determines whether an item exists.
	 *
	 * @since 3.0
	 *
	 * @param string $offset Item ID.
	 * @return bool True if the item exists, false on failure.
	 */
	public function offsetExists( $offset ) {
		if ( ! is_wp_error( $this->get_item( $offset ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieves an item by its ID.
	 *
	 * Defined only for compatibility with ArrayAccess, use get() directly.
	 *
	 * @since 3.0
	 *
	 * @param string $offset Item ID.
	 * @return mixed|\WP_Error The registered item, if it exists, otherwise a WP_Error object.
	 */
	public function offsetGet( $offset ) {
		return $this->get_item( $offset );
	}

	/**
	 * Adds/overwrites an item in the registry.
	 *
	 * Defined only for compatibility with ArrayAccess, use add_item() directly.
	 *
	 * @since 3.0
	 *
	 * @param string $offset Item ID.
	 * @param mixed  $value  Item attributes.
	 * @return true|\WP_Error True if `$attributes` is not empty, otherwise a WP_Error object.
	 */
	public function offsetSet( $offset, $value ) {
		return $this->add_item( $offset, $value );
	}

	/**
	 * Removes an item from the registry.
	 *
	 * Defined only for compatibility with ArrayAccess, use remove_item() directly.
	 *
	 * @since 3.0
	 *
	 * @param string $offset Item ID.
	 */
	public function offsetUnset( $offset ) {
		$this->remove_item( $offset );
	}

}
