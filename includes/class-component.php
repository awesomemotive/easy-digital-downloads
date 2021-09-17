<?php
/**
 * Component Class.
 *
 * @package     EDD
 * @subpackage  Core
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD;

use EDD\Database;

/**
 * Component Class.
 *
 * @since 3.0
 */
class Component extends Base_Object {

	/**
	 * Name of this component
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * Database schema definition
	 *
	 * @since 3.0
	 *
	 * @var Database\Schema
	 */
	public $schema = false;

	/**
	 * Database table interface
	 *
	 * @since 3.0
	 *
	 * @var Database\Table
	 */
	public $table = false;

	/**
	 * Database single object interface
	 *
	 * @since 3.0
	 *
	 * @var Database\Table
	 */
	public $meta = false;

	/**
	 * Database query interface
	 *
	 * @since 3.0
	 *
	 * @var Database\Query
	 */
	public $query = false;

	/**
	 * Database single object interface
	 *
	 * @since 3.0
	 *
	 * @var Database\Row|object
	 */
	public $object = false;

	/**
	 * Array of interface objects instantiated during init
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	private $interfaces = array();

	/**
	 * Array of interface keys
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	private $interface_keys = array(
		'schema' => false,
		'table'  => false,
		'query'  => false,
		'object' => false,
		'meta'   => false
	);

	/**
	 * Construct an EDD component
	 *
	 * @since 3.0
	 * @param array $args
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );
	}

	/**
	 * Return an interface object
	 *
	 * @since 3.0
	 *
	 * @param string $name
	 * @return object
	 */
	public function get_interface( $name = '' ) {
		return isset( $this->interfaces[ $name ] )
			? $this->interfaces[ $name ]
			: false;
	}

	/**
	 * Setup an EDD component based on parsing in constructor
	 *
	 * @since 3.0
	 * @param array $args
	 */
	protected function set_vars( $args = array() ) {

		// Get the interface keys
		$keys = array_keys( $this->interface_keys );

		// Loop through args...
		foreach ( $args as $key => $value ) {

			// Set arg as a Component Interface
			if ( in_array( $key, $keys, true ) && class_exists( $value ) ) {
				$this->interfaces[ $key ] = new $value;

			// Set arg as a Component property
			} else {
				$this->{$key} = $value;
			}
		}
	}
}
