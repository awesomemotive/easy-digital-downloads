<?php
/**
 * Base Schema Class.
 *
 * @package     EDD
 * @subpackage  Database\Schemas
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */
namespace EDD\Database\Schemas;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class Base extends \EDD\Database\Base {

	/**
	 * Array of database column objects to turn into EDD_DB_Column
	 *
	 * @since 3.0.0
	 * @access public
	 * @var array
	 */
	public $columns = array();
}
