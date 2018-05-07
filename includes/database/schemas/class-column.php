<?php
/**
 * Base Schema Column Class.
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

/**
 * Base class used for each column for a custom table.
 *
 * @since 3.0
 *
 * @see \EDD\Database\Schemas\Column::__construct() for accepted arguments.
 */
class Column {

	/**
	 * Name for the database column
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	public $name = '';

	/**
	 * Type of database column
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	public $type = '';

	/**
	 * Length of database column
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	public $length = false;

	/**
	 * Is integer unsigned?
	 *
	 * @since 3.0
	 * @access public
	 * @var bool
	 */
	public $unsigned = true;

	/**
	 * Is integer filled with zeroes?
	 *
	 * @since 3.0
	 * @access public
	 * @var bool
	 */
	public $zerofill = false;

	/**
	 * Is data in a binary format?
	 *
	 * @since 3.0
	 * @access public
	 * @var bool
	 */
	public $binary = false;

	/**
	 * Is null an allowed value?
	 *
	 * @since 3.0
	 * @access public
	 * @var bool
	 */
	public $allow_null = false;

	/**
	 * Typically empty/null, or date value
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	public $default = '';

	/**
	 * auto_increment, etc...
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	public $extra = '';

	/**
	 * Typically inherited from wpdb.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	public $encoding = '';

	/**
	 * Typically inherited from wpdb
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	public $collation = '';

	/**
	 * Typically empty; probably ignore.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	public $comment = '';

	/**
	 * What is the string-replace pattern?
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	public $pattern = '';

	/**
	 * Is this the primary column?
	 *
	 * @since 3.0
	 * @access public
	 * @var bool
	 */
	public $primary = false;

	/**
	 * Is this the column used as a created date?
	 *
	 * @since 3.0
	 * @access public
	 * @var bool
	 */
	public $created = false;

	/**
	 * Is this the column used as a modified date?
	 *
	 * @since 3.0
	 * @access public
	 * @var bool
	 */
	public $modified = false;

	/**
	 * Is this column searchable?
	 *
	 * @since 3.0
	 * @access public
	 * @var bool
	 */
	public $searchable = false;

	/**
	 * Is this column a date (that uses WP_Date_Query?)
	 *
	 * @since 3.0
	 * @access public
	 * @var bool
	 */
	public $date_query = false;

	/**
	 * Is this column used in orderby?
	 *
	 * @since 3.0
	 * @access public
	 * @var bool
	 */
	public $sortable = false;

	/**
	 * Is __in supported?
	 *
	 * @since 3.0
	 * @access public
	 * @var bool
	 */
	public $in = true;

	/**
	 * Is __not_in supported?
	 *
	 * @since 3.0
	 * @access public
	 * @var bool
	 */
	public $not_in = true;

	/**
	 * Does this column have its own cache key?
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	public $cache_key = false;

	/**
	 * Does this column fire a transition action when it's value changes?
	 *
	 * @since 3.0
	 * @access public
	 * @var bool
	 */
	public $transition = false;

	/**
	 * Array of possible aliases this column can be referred to as.
	 *
	 *
	 * @since 3.0
	 * @access public
	 * @var array
	 */
	public $aliases = array();

	/**
	 * Sets up the order query, based on the query vars passed.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @param string|array $args {
	 *     Optional. Array or query string of order query parameters. Default empty.
	 *
	 *     @type string   $name        Name of database column
	 *     @type string   $type        Type of database column
	 *     @type integer  $length      Length of database column
	 *     @type boolean  $unsigned    Is integer unsigned?
	 *     @type boolean  $zerofill    Is integer filled with zeroes?
	 *     @type boolean  $binary      Is data in a binary format?
	 *     @type boolean  $allow_null  Is null an allowed value?
	 *     @type mixed    $default     Typically empty/null, or date value
	 *     @type string   $extra       auto_increment, etc...
	 *     @type string   $encoding    Typically inherited from wpdb
	 *     @type string   $collation   Typically inherited from wpdb
	 *     @type string   $comment     Typically empty
	 *     @type boolean  $pattern     What is the string-replace pattern?
	 *     @type boolean  $primary     Is this the primary column?
	 *     @type boolean  $created     Is this the column used as a created date?
	 *     @type boolean  $modified    Is this the column used as a modified date?
	 *     @type boolean  $searchable  Is this column searchable?
	 *     @type boolean  $sortable    Is this column used in orderby?
	 *     @type boolean  $date_query  Is this column a datetime?
	 *     @type boolean  $in          Is __in supported?
	 *     @type boolean  $not_in      Is __not_in supported?
	 * }
	 */
	public function __construct( $args = array() ) {

		// Parse arguments
		$r = $this->parse_args( $args );

		// Maybe set arguments
		if ( ! empty( $r ) ) {
			$this->set_args( $r );
		}
	}

	/**
	 * Set column arguments
	 *
	 * @since 3.0
	 * @param array $args
	 */
	private function set_args( $args = array() ) {
		foreach ( $args as $key => $value ) {
			$this->{$key} = $value;
		}
	}

	/**
	 * Parse column arguments
	 *
	 * @since 3.0
	 * @access public
	 * @param array $args
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
			'encoding'   => $GLOBALS['wpdb']->charset,
			'collation'  => $GLOBALS['wpdb']->collate,
			'comment'    => '',

			// Query
			'pattern'    => false,
			'searchable' => false,
			'sortable'   => false,
			'date_query' => false,
			'transition' => false,
			'in'         => true,
			'not_in'     => true,

			// Special
			'primary'    => false,
			'created'    => false,
			'modified'   => false,

			// Cache
			'cache_key'  => false,

			// Backwards Compatibility
			'aliases'    => array()
		) );

		// Primary key columns are always cached
		if ( true === $r['primary'] ) {
			$r['cache_key'] = true;
		}

		// Set the args before they are sanitized
		$this->set_args( $r );

		// Return array
		return $this->sanitize_args( $r );
	}

	/**
	 * Sanitize arguments after they are parsed.
	 *
	 * @since 3.0
	 * @access private
	 * @param array $args
	 * @return array
	 */
	private function sanitize_args( $args = array() ) {

		// Sanitization callbacks
		$callbacks = array(
			'name'       => 'sanitize_key',
			'type'       => 'strtoupper',
			'length'     => 'intval',
			'unsigned'   => 'wp_validate_boolean',
			'zerofill'   => 'wp_validate_boolean',
			'binary'     => 'wp_validate_boolean',
			'allow_null' => 'wp_validate_boolean',
			'default'    => 'wp_kses_data',
			'extra'      => 'wp_kses_data',
			'encoding'   => 'wp_kses_data',
			'collation'  => 'wp_kses_data',
			'comment'    => 'wp_kses_data',
			'pattern'    => array( $this, 'sanitize_pattern' ),
			'primary'    => 'wp_validate_boolean',
			'created'    => 'wp_validate_boolean',
			'modified'   => 'wp_validate_boolean',
			'searchable' => 'wp_validate_boolean',
			'sortable'   => 'wp_validate_boolean',
			'date_query' => 'wp_validate_boolean',
			'transition' => 'wp_validate_boolean',
			'in'         => 'wp_validate_boolean',
			'not_in'     => 'wp_validate_boolean',
			'cache_key'  => 'wp_validate_boolean',
			'aliases'    => array( $this, 'sanitize_aliases' )
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
	 * Return if a column type is numeric or not.
	 *
	 * @since 3.0
	 * @access public
	 * @return boolean
	 */
	public function is_numeric() {
		return (bool) in_array( strtolower( $this->type ), array(
			'tinyint',
			'int',
			'mediumint',
			'bigint'
		), true );
	}

	/**
	 * Sanitize aliases array using `sanitize_key()`
	 *
	 * @since 3.0
	 * @param array $aliases
	 * @return array
	 */
	private function sanitize_aliases( $aliases = array() ) {
		return array_map( 'sanitize_key', $aliases );
	}

	/**
	 * Sanitize a pattern
	 *
	 * @since 3.0
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
}
