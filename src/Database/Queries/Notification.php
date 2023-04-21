<?php
/**
 * Notification Query Class.
 *
 * @package     EDD
 * @subpackage  Database\Queries
 * @copyright   Copyright (c) 2023, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.1.1
 */
namespace EDD\Database\Queries;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Database\Query;

/**
 * Class used for querying items.
 *
 * @since 3.1.1
 *
 * @see \EDD\Database\Queries\Notification::__construct() for accepted arguments.
 */
class Notification extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.1.1
	 * @access public
	 * @var string
	 */
	protected $table_name = 'notifications';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 3.1.1
	 * @access public
	 * @var string
	 */
	protected $table_alias = 'n';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 3.1.1
	 * @access public
	 * @var string
	 */
	protected $table_schema = '\\EDD\\Database\\Schemas\\Notifications';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 3.1.1
	 * @access public
	 * @var string
	 */
	protected $item_name = 'notification';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 3.1.1
	 * @access public
	 * @var string
	 */
	protected $item_name_plural = 'notifications';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 3.1.1
	 * @access public
	 * @var mixed
	 */
	protected $item_shape = 'EDD\\Notifications\\Notification';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 3.1.1
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'notifications';

	/** Methods ***************************************************************/

	/**
	 * Sets up the query, based on the query vars passed.
	 *
	 * @since 3.1.1
	 * @access public
	 *
	 * @param string|array $query {
	 *     Optional. Array or query string of query parameters. Default empty.
	 *
	 *     @type int          $id                   An notification ID to only return that notification. Default empty.
	 *     @type array        $id__in               Array of notification IDs to include. Default empty.
	 *     @type array        $id__not_in           Array of notification IDs to exclude. Default empty.
	 * }
	 */
	public function __construct( $query = array() ) {
		parent::__construct( $query );
	}
}
