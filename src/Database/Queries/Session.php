<?php
/**
 * Session Query Class.
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
 * @see \EDD\Database\Queries\Session::__construct() for accepted arguments.
 */
class Session extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $table_name = 'sessions';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $table_alias = 's';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $table_schema = '\\EDD\\Database\\Schemas\\Sessions';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $item_name = 'session';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $item_name_plural = 'sessions';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 3.3.0
	 * @var mixed
	 */
	protected $item_shape = 'EDD\\Sessions\\Session';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $cache_group = 'sessions';

	/** Methods ***************************************************************/

	/**
	 * Sets up the query, based on the query vars passed.
	 *
	 * @since 3.3.0
	 *
	 * @param string|array $query {
	 *     Optional. Array or query string of query parameters. Default empty.
	 *
	 *     @type int          $id                   An session ID to only return that session. Default empty.
	 *     @type array        $id__in               Array of session IDs to include. Default empty.
	 *     @type array        $id__not_in           Array of session IDs to exclude. Default empty.
	 * }
	 */
	public function __construct( $query = array() ) {
		parent::__construct( $query );
	}

	/**
	 * Return the primary database column name
	 *
	 * @since 1.0.0
	 *
	 * @return string Default "id", Primary column name if not empty
	 */
	protected function get_primary_column_name() {
		return $this->get_column_field( array( 'primary' => true ), 'name', 'session_id' );
	}
}
