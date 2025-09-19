<?php
/**
 * Base Custom Database Table Column Class.
 *
 * @package     Database
 * @subpackage  Column
 * @copyright   Copyright (c) 2020
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace EDD\Database;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Base class used for each column for a custom table.
 *
 * @since 1.0.0
 *
 * @see Column::__construct() for accepted arguments.
 */
class Column extends Base {

	/** Table Attributes ******************************************************/

	/**
	 * Name for the database column.
	 *
	 * Required. Must contain lowercase alphabetical characters only. Use of any
	 * other character (number, ascii, unicode, emoji, etc...) will result in
	 * fatal application errors.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public $name = '';

	/**
	 * Type of database column.
	 *
	 * See: https://dev.mysql.com/doc/en/data-types.html
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public $type = '';

	/**
	 * Length of database column.
	 *
	 * See: https://dev.mysql.com/doc/en/storage-requirements.html
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public $length = false;

	/**
	 * Is integer unsigned?
	 *
	 * See: https://dev.mysql.com/doc/en/numeric-type-overview.html
	 *
	 * @since 1.0.0
	 * @var   bool
	 */
	public $unsigned = true;

	/**
	 * Is integer filled with zeroes?
	 *
	 * See: https://dev.mysql.com/doc/en/numeric-type-overview.html
	 *
	 * @since 1.0.0
	 * @var   bool
	 */
	public $zerofill = false;

	/**
	 * Is data in a binary format?
	 *
	 * See: https://dev.mysql.com/doc/en/binary-varbinary.html
	 *
	 * @since 1.0.0
	 * @var   bool
	 */
	public $binary = false;

	/**
	 * Is null an allowed value?
	 *
	 * See: https://dev.mysql.com/doc/en/data-type-defaults.html
	 *
	 * @since 1.0.0
	 * @var   bool
	 */
	public $allow_null = false;

	/**
	 * Typically empty/null, or date value.
	 *
	 * See: https://dev.mysql.com/doc/en/data-type-defaults.html
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public $default = '';

	/**
	 * auto_increment, etc...
	 *
	 * See: https://dev.mysql.com/doc/en/data-type-defaults.html
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public $extra = '';

	/**
	 * Typically inherited from the database interface (wpdb).
	 *
	 * By default, this will use the globally available database encoding. You
	 * most likely do not want to change this; if you do, you already know what
	 * to do.
	 *
	 * See: https://dev.mysql.com/doc/mysql/en/charset-column.html
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public $encoding = '';

	/**
	 * Typically inherited from the database interface (wpdb).
	 *
	 * By default, this will use the globally available database collation. You
	 * most likely do not want to change this; if you do, you already know what
	 * to do.
	 *
	 * See: https://dev.mysql.com/doc/mysql/en/charset-column.html
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public $collation = '';

	/**
	 * Typically empty; probably ignore.
	 *
	 * By default, columns do not have comments. This is unused by any other
	 * relative code, but you can include less than 1024 characters here.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public $comment = '';

	/** Special Attributes ****************************************************/

	/**
	 * Is this the primary column?
	 *
	 * By default, columns are not the primary column. This is used by the Query
	 * class for several critical functions, including (but not limited to) the
	 * cache key, meta-key relationships, auto-incrementing, etc...
	 *
	 * @since 1.0.0
	 * @var   bool
	 */
	public $primary = false;

	/**
	 * Is this the column used as a created date?
	 *
	 * By default, columns do not represent the date a value was first entered.
	 * This is used by the Query class to set its value automatically to the
	 * current datetime value immediately before insert.
	 *
	 * @since 1.0.0
	 * @var   bool
	 */
	public $created = false;

	/**
	 * Is this the column used as a modified date?
	 *
	 * By default, columns do not represent the date a value was last changed.
	 * This is used by the Query class to update its value automatically to the
	 * current datetime value immediately before insert|update.
	 *
	 * @since 1.0.0
	 * @var   bool
	 */
	public $modified = false;

	/**
	 * Is this the column used as a unique universal identifier?
	 *
	 * By default, columns are not UUIDs. This is used by the Query class to
	 * generate a unique string that can be used to identify a row in a database
	 * table, typically in such a way that is unrelated to the row data itself.
	 *
	 * @since 1.0.0
	 * @var   bool
	 */
	public $uuid = false;

	/** Query Attributes ******************************************************/

	/**
	 * What is the string-replace pattern?
	 *
	 * By default, column patterns will be guessed based on their type. Set this
	 * manually to `%s|%d|%f` only if you are doing something weird, or are
	 * explicitly storing numeric values in text-based column types.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public $pattern = '';

	/**
	 * Is this column searchable?
	 *
	 * By default, columns are not searchable. When `true`, the Query class will
	 * add this column to the results of search queries.
	 *
	 * Avoid setting to `true` on large blobs of text, unless you've optimized
	 * your database server to accommodate these kinds of queries.
	 *
	 * @since 1.0.0
	 * @var   bool
	 */
	public $searchable = false;

	/**
	 * Is this column a date?
	 *
	 * By default, columns do not support date queries. When `true`, the Query
	 * class will accept complex statements to help narrow results down to
	 * specific periods of time for values in this column.
	 *
	 * @since 1.0.0
	 * @var   bool
	 */
	public $date_query = false;

	/**
	 * Is this column used in orderby?
	 *
	 * By default, columns are not sortable. This ensures that the database
	 * table does not perform costly operations on unindexed columns or columns
	 * of an inefficient type.
	 *
	 * You can safely turn this on for most numeric columns, indexed columns,
	 * and text columns with intentionally limited lengths.
	 *
	 * @since 1.0.0
	 * @var   bool
	 */
	public $sortable = false;

	/**
	 * Is __in supported?
	 *
	 * By default, columns support being queried using an `IN` statement. This
	 * allows the Query class to retrieve rows that match your array of values.
	 *
	 * Consider setting this to `false` for longer text columns.
	 *
	 * @since 1.0.0
	 * @var   bool
	 */
	public $in = true;

	/**
	 * Is __not_in supported?
	 *
	 * By default, columns support being queried using a `NOT IN` statement.
	 * This allows the Query class to retrieve rows that do not match your array
	 * of values.
	 *
	 * Consider setting this to `false` for longer text columns.
	 *
	 * @since 1.0.0
	 * @var   bool
	 */
	public $not_in = true;

	/**
	 * Is __compare supported?
	 *
	 * Allows for comparing two columns in the same table.
	 *
	 * @var bool
	 */
	public $compare = false;

	/** Cache Attributes ******************************************************/

	/**
	 * Does this column have its own cache key?
	 *
	 * By default, only primary columns are used as cache keys. If this column
	 * is unique, or is frequently used to get database results, you may want to
	 * consider setting this to true.
	 *
	 * Use in conjunction with a database index for speedy queries.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public $cache_key = false;

	/** Action Attributes *****************************************************/

	/**
	 * Does this column fire a transition action when it's value changes?
	 *
	 * By default, columns do not fire transition actions. In some cases, it may
	 * be desirable to know when a database value changes, and what the old and
	 * new values are when that happens.
	 *
	 * The Query class is responsible for triggering the event action.
	 *
	 * @since 1.0.0
	 * @var   bool
	 */
	public $transition = false;

	/** Callback Attributes ***************************************************/

	/**
	 * Maybe validate this data before it is written to the database.
	 *
	 * By default, column data is validated based on the type of column that it
	 * is. You can set this to a callback function of your choice to override
	 * the default validation behavior.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public $validate = '';

	/**
	 * Array of capabilities used to interface with this column.
	 *
	 * These are used by the Query class to allow and disallow CRUD access to
	 * column data, typically based on roles or capabilities.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	public $caps = array();

	/**
	 * Array of possible aliases this column can be referred to as.
	 *
	 * These are used by the Query class to allow for columns to be renamed
	 * without requiring complex architectural backwards compatibility support.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	public $aliases = array();

	/**
	 * Array of possible relationships this column has with columns in other
	 * database tables.
	 *
	 * These are typically unenforced foreign keys, and are used by the Query
	 * class to help prime related items.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	public $relationships = array();

	/** Methods ***************************************************************/

	/**
	 * Sets up the order query, based on the query vars passed.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array $args {
	 *     Optional. Array or query string of order query parameters. Default empty.
	 *
	 *     @type string   $name           Name of database column
	 *     @type string   $type           Type of database column
	 *     @type int      $length         Length of database column
	 *     @type bool     $unsigned       Is integer unsigned?
	 *     @type bool     $zerofill       Is integer filled with zeroes?
	 *     @type bool     $binary         Is data in a binary format?
	 *     @type bool     $allow_null     Is null an allowed value?
	 *     @type mixed    $default        Typically empty/null, or date value
	 *     @type string   $extra          auto_increment, etc...
	 *     @type string   $encoding       Typically inherited from wpdb
	 *     @type string   $collation      Typically inherited from wpdb
	 *     @type string   $comment        Typically empty
	 *     @type bool     $pattern        What is the string-replace pattern?
	 *     @type bool     $primary        Is this the primary column?
	 *     @type bool     $created        Is this the column used as a created date?
	 *     @type bool     $modified       Is this the column used as a modified date?
	 *     @type bool     $uuid           Is this the column used as a universally unique identifier?
	 *     @type bool     $searchable     Is this column searchable?
	 *     @type bool     $sortable       Is this column used in orderby?
	 *     @type bool     $date_query     Is this column a datetime?
	 *     @type bool     $in             Is __in supported?
	 *     @type bool     $not_in         Is __not_in supported?
	 *     @type bool     $compare        Is __compare supported?
	 *     @type bool     $cache_key      Is this column queried independently?
	 *     @type bool     $transition     Does this column transition between changes?
	 *     @type string   $validate       A callback function used to validate on save.
	 *     @type array    $caps           Array of capabilities to check.
	 *     @type array    $aliases        Array of possible column name aliases.
	 *     @type array    $relationships  Array of columns in other tables this column relates to.
	 * }
	 */
	public function __construct( $args = array() ) {

		// Parse arguments
		$r = $this->parse_args( $args );

		// Maybe set variables from arguments
		if ( ! empty( $r ) ) {
			$this->set_vars( $r );
		}
	}

	/** Argument Handlers *****************************************************/

	/**
	 * Parse column arguments
	 *
	 * @since 1.0.0
	 * @param array $args Default empty array.
	 * @return array
	 */
	private function parse_args( $args = array() ) {

		// Parse arguments
		$r = wp_parse_args( $args, array(

			// Table
			'name'       => '',
			'type'       => '',
			'length'     => '',
			'unsigned'   => false,
			'zerofill'   => false,
			'binary'     => false,
			'allow_null' => false,
			'default'    => '',
			'extra'      => '',
			'encoding'   => $this->get_db()->charset,
			'collation'  => $this->get_db()->collate,
			'comment'    => '',

			// Query
			'pattern'    => false,
			'searchable' => false,
			'sortable'   => false,
			'date_query' => false,
			'transition' => false,
			'in'         => true,
			'not_in'     => true,
			'compare'    => false,

			// Special
			'primary'    => false,
			'created'    => false,
			'modified'   => false,
			'uuid'       => false,

			// Cache
			'cache_key'  => false,

			// Validation
			'validate'   => '',

			// Capabilities
			'caps'          => array(),

			// Backwards Compatibility
			'aliases'       => array(),

			// Column Relationships
			'relationships' => array()
		) );

		// Force some arguments for special column types
		$r = $this->special_args( $r );

		// Set the args before they are sanitized
		$this->set_vars( $r );

		// Return array
		return $this->validate_args( $r );
	}

	/**
	 * Validate arguments after they are parsed.
	 *
	 * @since 1.0.0
	 * @param array $args Default empty array.
	 * @return array
	 */
	private function validate_args( $args = array() ) {

		// Sanitization callbacks
		$callbacks = array(
			'name'          => 'sanitize_key',
			'type'          => 'strtoupper',
			'length'        => 'intval',
			'unsigned'      => 'wp_validate_boolean',
			'zerofill'      => 'wp_validate_boolean',
			'binary'        => 'wp_validate_boolean',
			'allow_null'    => 'wp_validate_boolean',
			'default'       => array( $this, 'sanitize_default' ),
			'extra'         => 'wp_kses_data',
			'encoding'      => 'wp_kses_data',
			'collation'     => 'wp_kses_data',
			'comment'       => 'wp_kses_data',

			'primary'       => 'wp_validate_boolean',
			'created'       => 'wp_validate_boolean',
			'modified'      => 'wp_validate_boolean',
			'uuid'          => 'wp_validate_boolean',

			'searchable'    => 'wp_validate_boolean',
			'sortable'      => 'wp_validate_boolean',
			'date_query'    => 'wp_validate_boolean',
			'transition'    => 'wp_validate_boolean',
			'in'            => 'wp_validate_boolean',
			'not_in'        => 'wp_validate_boolean',
			'compare'       => 'wp_validate_boolean',
			'cache_key'     => 'wp_validate_boolean',

			'pattern'       => array( $this, 'sanitize_pattern'       ),
			'validate'      => array( $this, 'sanitize_validation'    ),
			'caps'          => array( $this, 'sanitize_capabilities'  ),
			'aliases'       => array( $this, 'sanitize_aliases'       ),
			'relationships' => array( $this, 'sanitize_relationships' )
		);

		// Default args array
		$r = array();

		// Loop through and try to execute callbacks
		foreach ( $args as $key => $value ) {

			// Callback is callable
			if ( isset( $callbacks[ $key ] ) && is_callable( $callbacks[ $key ] ) ) {
				$r[ $key ] = call_user_func( $callbacks[ $key ], $value );

			// Callback is malformed so just let it through to avoid breakage
			} else {
				$r[ $key ] = $value;
			}
		}

		// Return sanitized arguments
		return $r;
	}

	/**
	 * Force column arguments for special column types
	 *
	 * @since 1.0.0
	 * @param array $args Default empty array.
	 * @return array
	 */
	private function special_args( $args = array() ) {

		// Primary key columns are always used as cache keys
		if ( ! empty( $args['primary'] ) ) {
			$args['cache_key'] = true;

		// All UUID columns need to follow a very specific pattern
		} elseif ( ! empty( $args['uuid'] ) ) {
			$args['name']       = 'uuid';
			$args['type']       = 'varchar';
			$args['length']     = '100';
			$args['in']         = false;
			$args['not_in']     = false;
			$args['searchable'] = false;
			$args['sortable']   = false;
		}

		// Return args
		return (array) $args;
	}

	/** Public Helpers ********************************************************/

	/**
	 * Return if a column type is numeric or not.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_numeric() {
		return $this->is_type( array(
			'tinyint',
			'smallint',
			'int',
			'mediumint',
			'bigint',
		) );
	}

	/** Private Helpers *******************************************************/

	/**
	 * Return if this column is of a certain type.
	 *
	 * @since 1.0.0
	 * @param mixed $type Default empty string. The type to check. Also accepts an array.
	 * @return bool True if of type, False if not
	 */
	private function is_type( $type = '' ) {

		// If string, cast to array
		if ( is_string( $type ) ) {
			$type = (array) $type;
		}

		// Make them lowercase
		$types = array_map( 'strtolower', $type );

		// Return if match or not
		return (bool) in_array( strtolower( $this->type ), $types, true );
	}

	/** Private Sanitizers ****************************************************/

	/**
	 * Sanitize capabilities array
	 *
	 * @since 1.0.0
	 * @param array $caps Default empty array.
	 * @return array
	 */
	private function sanitize_capabilities( $caps = array() ) {
		return wp_parse_args( $caps, array(
			'select' => 'exist',
			'insert' => 'exist',
			'update' => 'exist',
			'delete' => 'exist'
		) );
	}

	/**
	 * Sanitize aliases array using `sanitize_key()`
	 *
	 * @since 1.0.0
	 * @param array $aliases Default empty array.
	 * @return array
	 */
	private function sanitize_aliases( $aliases = array() ) {
		return array_map( 'sanitize_key', $aliases );
	}

	/**
	 * Sanitize relationships array
	 *
	 * @todo
	 * @since 1.0.0
	 * @param array $relationships Default empty array.
	 * @return array
	 */
	private function sanitize_relationships( $relationships = array() ) {
		return array_filter( $relationships );
	}

	/**
	 * Sanitize the default value
	 *
	 * @since 1.0.0
	 * @param string $default
	 * @return string|null
	 */
	private function sanitize_default( $default = '' ) {

		// Null
		if ( ( true === $this->allow_null ) && is_null( $default ) ) {
			return null;

			// String
		} elseif ( is_string( $default ) ) {
			return wp_kses_data( $default );

			// Integer
		} elseif ( $this->is_numeric( $default ) ) {
			return (int) $default;
		}

		// @todo datetime, decimal, and other column types

		// Unknown, so return the default's default
		return '';
	}

	/**
	 * Sanitize the pattern
	 *
	 * @since 1.0.0
	 * @param mixed $pattern
	 * @return string
	 */
	private function sanitize_pattern( $pattern = false ) {

		// Allowed patterns
		$allowed_patterns = array( '%s', '%d', '%f' );

		// Return pattern if allowed
		if ( in_array( $pattern, $allowed_patterns, true ) ) {
			return $pattern;
		}

		// Fallback to digit or string
		return $this->is_numeric()
			? '%d'
			: '%s';
	}

	/**
	 * Sanitize the validation callback
	 *
	 * @since 1.0.0
	 * @param string $callback Default empty string. A callable PHP function name or method
	 * @return string The most appropriate callback function for the value
	 */
	private function sanitize_validation( $callback = '' ) {

		// Return callback if it's callable
		if ( is_callable( $callback ) ) {
			return $callback;
		}

		// UUID special column
		if ( true === $this->uuid ) {
			$callback = array( $this, 'validate_uuid' );

		// Datetime fallback
		} elseif ( $this->is_type( 'datetime' ) ) {
			$callback = array( $this, 'validate_datetime' );

		// Decimal fallback
		} elseif ( $this->is_type( 'decimal' ) ) {
			$callback = array( $this, 'validate_decimal' );

		// Intval fallback
		} elseif ( $this->is_numeric() ) {
			$callback = 'intval';
		}

		// Return the callback
		return $callback;
	}

	/** Public Validators *****************************************************/

	/**
	 * Fallback to validate a datetime value if no other is set.
	 *
	 * This assumes NO_ZERO_DATES is off or overridden.
	 *
	 * If MySQL drops support for zero dates, this method will need to be
	 * updated to support different default values based on the environment.
	 *
	 * @since 1.0.0
	 * @param string $value Default ''. A datetime value that needs validating
	 * @return string A valid datetime value
	 */
	public function validate_datetime( $value = '' ) {

		// Handle "empty" values
		if ( empty( $value ) || ( '0000-00-00 00:00:00' === $value ) ) {
			$value = ! empty( $this->default )
				? $this->default
				: '';

		// Convert to MySQL datetime format via date() && strtotime
		} elseif ( function_exists( 'date' ) ) {
			$value = date( 'Y-m-d H:i:s', strtotime( $value ) );
		}

		// Return the validated value
		return $value;
	}

	/**
	 * Validate a decimal
	 *
	 * (Recommended decimal column length is '18,9'.)
	 *
	 * This is used to validate a mixed value before it is saved into a decimal
	 * column in a database table.
	 *
	 * Uses number_format() which does rounding to the last decimal if your
	 * value is longer than specified.
	 *
	 * @since 1.0.0
	 * @param mixed $value    Default empty string. The decimal value to validate
	 * @param int   $decimals Default 9. The number of decimal points to accept
	 * @return float
	 */
	public function validate_decimal( $value = 0, $decimals = 9 ) {

		// Protect against non-numeric values
		if ( ! is_numeric( $value ) ) {
			$value = 0;
		}

		// Protect against non-numeric decimals
		if ( ! is_numeric( $decimals ) ) {
			$decimals = 9;
		}

		// Is the value negative?
		$negative_exponent = ( $value < 0 )
			? -1
			: 1;

		// Only numbers and period
		$value = preg_replace( '/[^0-9\.]/', '', (string) $value );

		// Format to number of decimals, and cast as float
		$formatted = number_format( $value, $decimals, '.', '' );

		// Adjust for negative values
		$retval = $formatted * $negative_exponent;

		// Return
		return $retval;
	}

	/**
	 * Validate a UUID.
	 *
	 * This uses the v4 algorithm to generate a UUID that is used to uniquely
	 * and universally identify a given database row without any direct
	 * connection or correlation to the data in that row.
	 *
	 * From http://php.net/manual/en/function.uniqid.php#94959
	 *
	 * @since 1.0.0
	 * @param string $value The UUID value (empty on insert, string on update)
	 * @return string Generated UUID.
	 */
	public function validate_uuid( $value = '' ) {

		// Default URN UUID prefix
		$prefix = 'urn:uuid:';

		// Bail if not empty and correctly prefixed
		// (UUIDs should _never_ change once they are set)
		if ( ! empty( $value ) && ( 0 === strpos( $value, $prefix ) ) ) {
			return $value;
		}

		// Put the pieces together
		$value = sprintf( "{$prefix}%04x%04x-%04x-%04x-%04x-%04x%04x%04x",

			// 32 bits for "time_low"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

			// 16 bits for "time_mid"
			mt_rand( 0, 0xffff ),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand( 0, 0x0fff ) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand( 0, 0x3fff ) | 0x8000,

			// 48 bits for "node"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);

		// Return the new UUID
		return $value;
	}

	/** Table Helpers *********************************************************/

	/**
	 * Return a string representation of what this column's properties look like
	 * in a MySQL.
	 *
	 * @todo
	 * @since 1.0.0
	 * @return string
	 */
	public function get_create_string() {

		// Default return val
		$retval = '';

		// Bail if no name
		if ( ! empty( $this->name ) ) {
			$retval .= $this->name;
		}

		// Type
		if ( ! empty( $this->type ) ) {
			$retval .= " {$this->type}";
		}

		// Length
		if ( ! empty( $this->length ) ) {
			$retval .= '(' . $this->length . ')';
		}

		// Unsigned
		if ( ! empty( $this->unsigned ) ) {
			$retval .= " unsigned";
		}

		// Zerofill
		if ( ! empty( $this->zerofill ) ) {
			// TBD
		}

		// Binary
		if ( ! empty( $this->binary ) ) {
			// TBD
		}

		// Allow null
		if ( ! empty( $this->allow_null ) ) {
			$retval .= " NOT NULL ";
		}

		// Default
		if ( ! empty( $this->default ) ) {
			$retval .= " default '{$this->default}'";

		// A literal false means no default value
		} elseif ( false !== $this->default ) {

			// Numeric
			if ( $this->is_numeric() ) {
				$retval .= " default '0'";
			} elseif ( $this->is_type( 'datetime' ) ) {
				$retval .= " default '0000-00-00 00:00:00'";
			} else {
				$retval .= " default ''";
			}
		}

		// Extra
		if ( ! empty( $this->extra ) ) {
			$retval .= " {$this->extra}";
		}

		// Encoding
		if ( ! empty( $this->encoding ) ) {

		} else {

		}

		// Collation
		if ( ! empty( $this->collation ) ) {

		} else {

		}

		// Return the create string
		return $retval;
	}
}
