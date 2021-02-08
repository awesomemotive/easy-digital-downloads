<?php
/**
 * Base Custom Database Table Row Class.
 *
 * @package     Database
 * @subpackage  Row
 * @copyright   Copyright (c) 2020
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace EDD\Database;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Base database row class.
 *
 * This class exists solely for other classes to extend (and to encapsulate
 * database schema changes for those objects) to help separate the needs of the
 * application layer from the requirements of the database layer.
 *
 * For example, if a database column is renamed or a return value needs to be
 * formatted differently, this class will make sure old values are still
 * supported and new values do not conflict.
 *
 * @since 1.0.0
 */
class Row extends Base {

	/**
	 * Construct a database object.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed Null by default, Array/Object if not
	 */
	public function __construct( $item = null ) {
		if ( ! empty( $item ) ) {
			$this->init( $item );
		}
	}

	/**
	 * Initialize class properties based on data array.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data
	 */
	private function init( $data = array() ) {
		$this->set_vars( $data );
	}

	/**
	 * Determines whether the current row exists.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function exists() {
		return ! empty( $this->id );
	}
}
