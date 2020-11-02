<?php
/**
 * Base Custom Database Table Schema Class.
 *
 * @package     Database
 * @subpackage  Schema
 * @copyright   Copyright (c) 2020
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace EDD\Database;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * A base database table schema class, which houses the collection of columns
 * that a table is made out of.
 *
 * This class is intended to be extended for each unique database table,
 * including global tables for multisite, and users tables.
 *
 * @since 1.0.0
 */
class Schema extends Base {

	/**
	 * Array of database column objects to turn into Column.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	protected $columns = array();

	/**
	 * Invoke new column objects based on array of column data.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Bail if no columns
		if ( empty( $this->columns ) || ! is_array( $this->columns ) ) {
			return;
		}

		// Juggle original columns array
		$columns = $this->columns;
		$this->columns = array();

		// Loop through columns and create objects from them
		foreach ( $columns as $column ) {
			if ( is_array( $column ) ) {
				$this->columns[] = new Column( $column );
			} elseif ( $column instanceof Column ) {
				$this->columns[] = $column;
			}
		}
	}

	/**
	 * Return the schema in string form.
	 *
	 * @since 1.0.0
	 *
	 * @return string Calls get_create_string() on every column.
	 */
	protected function to_string() {

		// Default return value
		$retval = '';

		// Bail if no columns to convert
		if ( empty( $this->columns ) ) {
			return $retval;
		}

		// Loop through columns...
		foreach ( $this->columns as $column_info ) {
			if ( method_exists( $column_info, 'get_create_string' ) ) {
				$retval .= '\n' . $column_info->get_create_string() . ', ';
			}
		}

		// Return the string
		return $retval;
	}
}
