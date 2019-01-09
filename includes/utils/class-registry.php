<?php
/**
 * Registry utility superclass
 *
 * This class should be extended to create object registries.
 *
 * @package     EDD
 * @subpackage  Classes/Utilities
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Utils;

use EDD\Utils\Exceptions;

/**
 * Defines the construct for building an item registry.
 *
 * @since 3.0.0
 * @abstract
 */
abstract class Registry extends \ArrayObject {

	/**
	 * Item error label.
	 *
	 * Used for customizing exception messages to the current registry instance. Default 'item'.
	 *
	 * @since 3.0
	 * @var   string
	 */
	public static $item_error_label = 'item';

	/**
	 * Adds an item to the registry.
	 *
	 * @since 3.0
	 *
	 * @throws \EDD_Exception If the `$attributes` array is empty.
	 *
	 * @param string $item_id    Item ID.
	 * @param array  $attributes Array of item attributes. Each extending registry will
	 *                           handle item ID and attribute building in different ways.
	 * @return bool True if `$attributes` is not empty, otherwise false.
	 */
	public function add_item( $item_id, $attributes ) {
		$result = false;

		if ( ! empty( $attributes ) ) {

			$this->offsetSet( $item_id, $attributes );

			$result = true;

		} else {

			$message = sprintf(
				'The attributes were missing when attempting to add the \'%1$s\' %2$s.',
				$item_id,
				static::$item_error_label
			);

			throw new Exception( $message );
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
		if ( $this->offsetExists( $item_id ) ) {
			$this->offsetUnset( $item_id );
		}
	}

	/**
	 * Retrieves an item and its associated attributes.
	 *
	 * @since 3.0
	 *
	 * @throws \EDD_Exception if the item does not exist.
	 *
	 * @param string $item_id Item ID.
	 * @return array Array of attributes for the item if the item is set,
	 *               otherwise an empty array.
	 */
	public function get_item( $item_id ) {

		$item = array();

		if ( $this->offsetExists( $item_id ) ) {

			$item = $this->offsetGet( $item_id );

		} else {

			$message = sprintf(
				'The \'%1$s\' %2$s does not exist.',
				$item_id,
				static::$item_error_label
			);

			throw new Exception( $message );
		}

		return $item;
	}

	/**
	 * Retrieves registered items.
	 *
	 * @since 3.0
	 *
	 * @return array The list of registered items.
	 */
	public function get_items() {
		return $this->getArrayCopy();
	}

	/**
	 * Retrieves the value of a given attribute for a given item.
	 *
	 * @since 3.0
	 *
	 * @throws \EDD_Exception if the item does not exist.
	 * @throws \EDD_Exception if the attribute and/or item does not exist.
	 *
	 * @param string $key     Key of the attribute to retrieve.
	 * @param string $item_id Collection to retrieve the attribute from.
	 * @return mixed|null The attribute value if set, otherwise null.
	 */
	public function get_attribute( $key, $item_id ) {
		$attribute = null;
		$item      = $this->get_item( $item_id );

		if ( ! empty( $item[ $key ] ) ) {
			$attribute = $item[ $key ];
		} else {
			throw Exceptions\Attribute_Not_Found::from_attr( $key, $item_id );
		}

		return $attribute;
	}
}
