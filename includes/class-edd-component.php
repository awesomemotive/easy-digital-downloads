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
			'object'
		);

		// Loop through keys and setup
		foreach ( $args as $key => $value ) {
			if ( in_array( $key, $class_keys, true ) && class_exists( $value ) ) {
				$this->{$key} = new $value;
			} else {
				$this->{$key} = $value;
			}
		}

		// Perform anything extra
		$this->init_meta();
	}

	/**
	 * Maybe init the meta data table
	 *
	 * @since 3.0.0
	 */
	private function init_meta() {

		// Bail if no meta to init
		if ( empty( $this->meta ) ) {
			return;
		}

		// Setup the meta data table
		$this->meta = new $this->meta;
	}
}
