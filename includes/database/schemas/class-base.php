<?php
/**
 * Base Schema Class.
 *
 * @package     EDD
 * @subpackage  Database\Schemas
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Database\Schemas;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class Base extends \EDD\Database\Base {

	/**
	 * Array of database column objects to turn into \EDD\Database\Column
	 *
	 * @since 3.0
	 * @access public
	 * @var array
	 */
	protected $columns = array();

	/**
	 * Invoke new column objects based on array of column data
	 *
	 * @since 3.0
	 * @access public
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
}
