<?php
/**
 * Email Query Class.
 *
 * @package     EDD
 * @subpackage  Database\Queries
 * @copyright   Copyright (c) 2023, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.0
 */

namespace EDD\Database\Queries;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Database\Query;

/**
 * Class used for querying items.
 *
 * @since 3.3.0
 *
 * @see \EDD\Database\Queries\Query::__construct() for accepted arguments.
 */
class Email extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.3.0
	 * @access public
	 * @var string
	 */
	protected $table_name = 'emails';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 3.3.0
	 * @access public
	 * @var string
	 */
	protected $table_alias = 'e';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 3.3.0
	 * @access public
	 * @var string
	 */
	protected $table_schema = '\\EDD\\Database\\Schemas\\Emails';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 3.3.0
	 * @access public
	 * @var string
	 */
	protected $item_name = 'email';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 3.3.0
	 * @access public
	 * @var string
	 */
	protected $item_name_plural = 'emails';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 3.3.0
	 * @access public
	 * @var mixed
	 */
	protected $item_shape = '\\EDD\\Emails\\Email';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 3.3.0
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'emails';
}
