<?php


class EDD_Component {

	/**
	 * Database schema definition
	 *
	 * @since 3.0.0
	 *
	 * @var object
	 */
	public $schema = false;

	/**
	 * Database table interface
	 *
	 * @since 3.0.0
	 *
	 * @var object
	 */
	public $table = false;

	/**
	 * Database single object interface
	 *
	 * @since 3.0.0
	 *
	 * @var object
	 */
	public $meta = false;

	/**
	 * Database query interface
	 *
	 * @since 3.0.0
	 *
	 * @var object
	 */
	public $query = false;

	/**
	 * Database single object interface
	 *
	 * @since 3.0.0
	 *
	 * @var object
	 */
	public $object = false;

	/**
	 * Array of interface objects instantiated during init
	 *
	 * @since 3.0.0
	 *
	 * @var array
	 */
	private $interfaces = array();

	/**
	 * Construct an EDD component
	 *
	 * @since 3.0.0
	 * @param array $args
	 */
	public function __construct( $args = array() ) {

		// Parse arguments
		$r = wp_parse_args( $args, array(
			'schema' => false,
			'table'  => false,
			'query'  => false,
			'object' => false,
			'meta'   => false
		) );

		// Setup the component
		$this->init( $r );
	}

	/**
	 * Setup an EDD component based on parsing in constructor
	 *
	 * @since 3.0.0
	 * @param array $args
	 */
	private function init( $args = array() ) {

		// Keys that invoke new classes
		$class_keys = array(
			'schema',
			'table',
			'query',
			'object',
			'meta'
		);

		// Loop through keys and setup
		foreach ( $args as $key => $value ) {
			if ( in_array( $key, $class_keys, true ) && class_exists( $value ) ) {
				$this->interfaces[ $key ] = new $value;
			} else {
				$this->interfaces[ $key ] = $value;
			}
		}
	}

	/**
	 * Return an interface object
	 *
	 * @since 3.0.0
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
