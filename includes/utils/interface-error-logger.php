<?php
namespace EDD\Utils;

/**
 * Defines error logging methods for use by an implementing class.
 *
 * @since 3.0
 */
interface Error_Logger_Interface {

	/**
	 * Determines whether the object has generated errors during instantiation.
	 *
	 * @since 3.0
	 *
	 * @return bool True if errors have been logged, otherwise false.
	 */
	public function has_errors();

	/**
	 * Retrieves any logged errors for the object.
	 *
	 * @since 3.0
	 *
	 * @return \WP_Error WP_Error object for the current object.
	 */
	public function get_errors();

	/**
	 * Sets up the errors instance.
	 *
	 * @since 3.0
	 */
	public function setup_error_logger();

}