<?php

/**
 * EDD_DB_Schema Base class
 *
 * @package Plugins/EDD/Database/Schema/Base
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class EDD_DB_Schema {

	/**
	 * Array of database column objects to turn into EDD_DB_Column
	 *
	 * @since 3.0.0
	 * @access public
	 * @var array
	 */
	public $columns = array();
}
