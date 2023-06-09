<?php
/**
 * Utility class to handle operations on an array of objects or arrays.
 *
 * @since 3.1.2
 * @package   EDD\Utils
 */
namespace EDD\Utils;

defined( 'ABSPATH' ) || exit;

class ListHandler {

	/**
	 * The array to be handled.
	 *
	 * @var array
	 */
	private $array;

	/**
	 * ListHandler constructor.
	 *
	 * @param array $array The array to be handled.
	 */
	public function __construct( $array ) {
		$this->array = $array;
	}

	/**
	 * Gets the key of the array with the searched value.
	 *
	 * @since 3.1.2
	 * @param mixed  $search The value to search for.
	 * @param string $type   The type of search to perform. Currently supports 'min' or 'max'.
	 * @return int|string|false The key of the array with the searched value.
	 */
	public function search( $search, $type = 'min' ) {
		if ( empty( $this->array ) || ! is_array( $this->array ) ) {
			return false;
		}

		if ( $this->is_array_associative() ) {
			return array_search( $search, $this->array ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		}

		if ( in_array( $type, array( 'min', 'max' ), true ) ) {
			$plucked = $this->pluck( $search );
			if ( empty( $plucked ) ) {
				return false;
			}
			$search = call_user_func( $type, $plucked );
		}

		foreach ( $this->array as $key => $value ) {
			$result = array_search( $search, $value ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			if ( $result ) {
				return $key;
			}
		}

		return false;
	}

	/**
	 * Checks if the array is associative.
	 *
	 * @since 3.1.2
	 * @return bool
	 */
	private function is_array_associative() {
		return count( $this->array ) === count( $this->array, COUNT_RECURSIVE );
	}

	/**
	 * Plucks a certain field out of each element in the input array.
	 *
	 * This has the same functionality and prototype of
	 * array_column() (PHP 5.5) but also supports objects.
	 *
	 * This is a near copy of the pluck() method from the WP_List_Util class, but
	 * that errors if a nonexistent field is passed. This version does not.
	 *
	 * @since 3.1.2
	 * @param int|string $field     Field to fetch from the object or array.
	 * @param int|string $index_key Optional. Field from the element to use as keys for the new array.
	 * @return array Array of found values. If `$index_key` is set, an array of found values with keys
	 *               corresponding to `$index_key`. If `$index_key` is null, array keys from the original
	 *               `$list` will be preserved in the results.
	 */
	private function pluck( $field, $index_key = null ) {
		$newlist = array();

		if ( ! is_string( $field ) && ! is_int( $field ) ) {
			return $newlist;
		}

		if ( ! $index_key ) {
			/*
			 * This is simple. Could at some point wrap array_column()
			 * if we knew we had an array of arrays.
			 */
			foreach ( $this->array as $key => $value ) {
				if ( is_object( $value ) ) {
					$newlist[ $key ] = $value->$field;
				} elseif ( is_array( $value ) && isset( $value[ $field ] ) ) {
					$newlist[ $key ] = $value[ $field ];
				}
			}

			return $newlist;
		}

		/*
		 * When index_key is not set for a particular item, push the value
		 * to the end of the stack. This is how array_column() behaves.
		 */
		foreach ( $this->array as $value ) {
			if ( is_object( $value ) ) {
				if ( isset( $value->$index_key ) ) {
					$newlist[ $value->$index_key ] = $value->$field;
				} else {
					$newlist[] = $value->$field;
				}
			} elseif ( is_array( $value ) && isset( $value[ $field ] ) ) {
				if ( isset( $value[ $index_key ] ) ) {
					$newlist[ $value[ $index_key ] ] = $value[ $field ];
				} else {
					$newlist[] = $value[ $field ];
				}
			}
		}

		return $newlist;
	}
}
