<?php
/**
 * Component Class.
 *
 * @package     EDD
 * @subpackage  Core
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */
namespace EDD;

/**
 * Component Class.
 *
 * @since 3.0
 */
class Component {

	/**
	 * Database schema definition
	 *
	 * @since 3.0
	 *
	 * @var object
	 */
	public $schema = false;

	/**
	 * Database table interface
	 *
	 * @since 3.0
	 *
	 * @var object
	 */
	public $table = false;

	/**
	 * Database single object interface
	 *
	 * @since 3.0
	 *
	 * @var object
	 */
	public $meta = false;

	/**
	 * Database query interface
	 *
	 * @since 3.0
	 *
	 * @var object
	 */
	public $query = false;

	/**
	 * Database single object interface
	 *
	 * @since 3.0
	 *
	 * @var object
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
	 * Array of class keys
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	private $class_keys = array(
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

		// Parse arguments
		$r = wp_parse_args( $args, $this->class_keys );

		// Setup the component
		$this->init( $r );
	}

	/**
	 * Setup an EDD component based on parsing in constructor
	 *
	 * @since 3.0
	 * @param array $args
	 */
	private function init( $args = array() ) {
		$keys = array_keys( $this->class_keys );

		foreach ( $args as $key => $value ) {
			if ( in_array( $key, $keys, true ) && class_exists( $value ) ) {
				$this->interfaces[ $key ] = new $value;
			} else {
				$this->{$key} = $value;
			}
		}
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
}
