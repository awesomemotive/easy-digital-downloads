<?php
/**
 * Reports API - Base object
 *
 * @package     EDD
 * @subpackage  Reports
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports\Data;

use EDD\Utils\Error_Logger_Interface as Error_Logger;

/**
 * Represents an abstract base reports object.
 *
 * @since 3.0
 */
abstract class Base_Object implements Error_Logger {

	/**
	 * Object ID.
	 *
	 * @since 3.0
	 * @var   string
	 */
	public $object_id;

	/**
	 * Object label.
	 *
	 * @since 3.0
	 * @var   string
	 */
	public $label;

	/**
	 * Holds errors related to instantiating the object.
	 *
	 * @since 3.0
	 * @var   \WP_Error
	 */
	protected $errors;

	/**
	 * Constructs the object.
	 *
	 * @since 3.0
	 *
	 * @param array $args Arguments for instantiating the object.
	 */
	public function __construct( $args ) {
		$this->setup_error_logger();
		$this->set_props( $args );
	}

	/**
	 * Sets props for the object.
	 *
	 * @since 3.0
	 *
	 * @param array $attributes Object attributes.
	 */
	public function set_props( $attributes ) {
		if ( ! empty( $attributes['id'] ) ) {

			$this->set_id( $attributes['id'] );

		} else {

			$this->errors->add( 'missing_object_id', 'The object ID is missing.', $attributes );

		}

		if ( ! empty( $attributes['label'] ) ) {

			$this->set_label( $attributes['label'] );

		} else {

			$this->errors->add( 'missing_object_label', 'The object label is missing.', $attributes );

		}
	}

	/**
	 * Retrieves the object ID.
	 *
	 * @since 3.0
	 *
	 * @return string Object ID.
	 */
	public function get_id() {
		return $this->object_id;
	}

	/**
	 * Sets the object ID.
	 *
	 * @since 3.0
	 *
	 * @param string $object_id Object ID
	 * @return void
	 */
	private function set_id( $object_id ) {
		$this->object_id = sanitize_key( $object_id );
	}

	/**
	 * Retrieves the global label for the current object.
	 *
	 * @since 3.0
	 *
	 * @return string Object label string.
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Sets the object label.
	 *
	 * @since 3.0
	 *
	 * @param string $label Object label.
	 * @return void
	 */
	private function set_label( $label ) {
		$this->label = $label;
	}

	/**
	 * Renders the object via its display callback.
	 *
	 * Each sub-class must define its own display() method.
	 *
	 * @since 3.0
	 */
	abstract public function display();

	/**
	 * Determines whether the object has generated errors during instantiation.
	 *
	 * @since 3.0
	 *
	 * @return bool True if errors have been logged, otherwise false.
	 */
	public function has_errors() {
		if ( method_exists( $this->errors, 'has_errors' ) ) {
			return $this->errors->has_errors();
		} else {
			$errors = $this->errors->get_error_codes();

			return ! empty( $errors );
		}
	}

	/**
	 * Retrieves any logged errors for the object.
	 *
	 * @since 3.0
	 *
	 * @return \WP_Error WP_Error object for the current object.
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Sets up the WP_Error instance for logging errors.
	 *
	 * @since 3.0
	 */
	public function setup_error_logger() {
		if ( ! isset( $this->errors ) ) {
			$this->errors = new \WP_Error();
		}
	}

}
