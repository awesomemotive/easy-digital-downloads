<?php

/**
 * Orders: EDD_DB_Query class
 *
 * @package Plugins/EDD/Database/Queries
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Base class used for querying custom database tables.
 *
 * @since 3.0.0
 *
 * @see EDD_DB_Query::__construct() for accepted arguments.
 */
class EDD_DB_Column {

	/**
	 * Name for the database column
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $name = '';

	/**
	 * Type of database column
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $type = '';

	/**
	 * Sets up the order query, based on the query vars passed.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @param string|array $query {
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
	 *     @type boolean  $primary     Is this the primary column?
	 *     @type boolean  $searchable  Is this column searchable?
	 *     @type boolean  $sortable    Is this column used in orderby?
	 *     @type boolean  $in          Is __in supported?
	 *     @type boolean  $not_in      Is __not_in supported?
	 * }
	 */
	public function __construct( $args = array() ) {

		// Parse arguments
		$r = $this->parse_args( $args );

		// Set object parameters
		foreach ( $r as $key => $value ) {
			$this->{$key} = $value;
		}
	}

	/**
	 * Parse column arguments
	 *
	 * @since 3.0.0
	 * @access public
	 * @param array $args
	 * @return array
	 */
	public function parse_args( $args = array() ) {

		// Parse arguments
		$r = wp_parse_args( $args, array(
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
			'primary'    => false,
			'searchable' => false,
			'sortable'   => false,
			'date_query' => false,
			'in'         => true,
			'not_in'     => true
		) );

		// Sanitize values
		$r['name'] = sanitize_key( $r['name'] );

		// Smart types intelligently set all parameters
		if ( $this->is_smart_type( $r['type'] ) ) {
			$this->set_smart_type( $r['type'] );

		// Check allowed types
		} elseif ( in_array( strtolower( $r['type'] ), $this->get_allowed_types(), true ) ) {
			$r['type'] = strtoupper( $r['type'] );
		}

		// Return array
		return $r;
	}

	/**
	 * Return array of allowed column types.
	 *
	 * @since 3.0.0
	 * @access private
	 * @return array
	 */
	private function get_allowed_types() {
		return (array) apply_filters( 'get_allowed_types', array(
			'int',
			'bigint',
			'varchar',
			'longtext',
			'mediumtext',
			'datetime',
			'double'
		) );
	}

	/**
	 * Return array of smart column types.
	 *
	 * Not implemented yet.
	 *
	 * @since 3.0.0
	 * @access private
	 * @return array
	 */
	private function get_smart_types() {
		return (array) apply_filters( 'get_smart_types', array(
			'wp_type_primary',
			'wp_type_key',
			'wp_type_bigint',
			'wp_type_varchar',
		) );
	}

	/**
	 * Is this column type smart?
	 *
	 * Smart types apply typical WordPress properties to as many column
	 * attributes as possible. For example, BIGINTs are always length(20) and
	 * unsigned, so we can avoid doing some extra work.
	 *
	 * Not implemented yet.
	 *
	 * @since 3.0.0
	 * @access private
	 * @param string $type
	 * @return boolean
	 */
	private function is_smart_type( $type = '' ) {
		$type  = strtolower( $type );
		$pos   = strpos( $type, 'wp_type_', 0 );
		$smart = ( 0 === $pos );

		return (bool) $smart;
	}

	/**
	 * Return if a column type is numeric or not.
	 *
	 * @since 3.0.0
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
}
