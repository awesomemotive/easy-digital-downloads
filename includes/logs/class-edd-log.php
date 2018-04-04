<?php
/**
 * Logs API - Log Object
 *
 * @package     EDD
 * @subpackage  Logs
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */
namespace EDD\Logs;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Log Class.
 *
 * @since 3.0.0
 */
class Log {

	/**
	 * Log ID.
	 *
	 * @since  3.0.0
	 * @access protected
	 * @var    int
	 */
	protected $id;

	/**
	 * Object ID.
	 *
	 * @since  3.0.0
	 * @access protected
	 * @var    int
	 */
	protected $object_id;

	/**
	 * Object type.
	 *
	 * @since  3.0.0
	 * @access protected
	 * @var    string
	 */
	protected $object_type;

	/**
	 * Log type.
	 *
	 * @since  3.0.0
	 * @access protected
	 * @var    string
	 */
	protected $type;

	/**
	 * Log title.
	 *
	 * @since  3.0.0
	 * @access protected
	 * @var    string
	 */
	protected $title;

	/**
	 * Log content.
	 *
	 * @since  3.0.0
	 * @access protected
	 * @var    string
	 */
	protected $content;

	/**
	 * Date log was created.
	 *
	 * @since  3.0.0
	 * @access protected
	 * @var    string
	 */
	protected $date_created;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param int|object $data Log data, or a log ID.
	 */
	public function __construct( $data ) {
		$log = null;

		if ( is_object( $data ) ) {
			$log = $data;
		} else if ( is_numeric( $data ) ) {
			$log = edd_get_log( $data);
		}

		if ( $log ) {
			$this->setup_log( $log );
		}
	}

	/**
	 * Magic __get method to dispatch a call to retrieve a protected property.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $key
	 * @return mixed
	 */
	public function __get( $key ) {
		$key = sanitize_key( $key );

		if ( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		} elseif ( property_exists( $this, $key ) ) {
			return apply_filters( 'edd_log_' . $key, $this->{$key}, $this->id );
		}
	}

	/**
	 * Magic __set method to dispatch a call to update a protected property.
	 *
	 * @since 3.0.0
	 *
	 * @param string $key Property name.
	 * @param mixed $value Property value.
	 * @return mixed False if property doesn't exist, or returns the value from the dispatched method.
	 */
	public function __set( $key, $value ) {
		$key = sanitize_key( $key );

		// Only real properties can be saved.
		$keys = array_keys( get_class_vars( get_called_class() ) );

		if ( ! in_array( $key, $keys ) ) {
			return false;
		}

		// Dispatch to setter method if value needs to be sanitized
		if ( method_exists( $this, 'set_' . $key ) ) {
			return call_user_func( array( $this, 'set_' . $key ), $key, $value );
		} else {
			$this->{$key} = $value;
		}
	}

	/**
	 * Magic __isset method to allow empty checks on protected elements
	 *
	 * @since 3.0.0
	 *
	 * @param string $key The attribute to get.
	 * @return boolean If the item is set or not.
	 */
	public function __isset( $key ) {
		if ( property_exists( $this, $key ) ) {
			return false === empty( $this->{$key} );
		} else {
			return null;
		}
	}

	/**
	 * Converts the instance of the EDD_Discount object into an array for special cases.
	 *
	 * @since 2.7
	 *
	 * @return array EDD_Discount object as an array.
	 */
	public function array_convert() {
		return get_object_vars( $this );
	}

	/**
	 * Setup object vars.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @param object $log Log data.
	 *
	 * @return bool Object var initialisation successful or not.
	 */
	private function setup_log( $log ) {
		if ( null === $log ) {
			return false;
		}

		if ( ! is_object( $log ) ) {
			return false;
		}

		if ( is_wp_error( $log ) ) {
			return false;
		}

		/**
		 * Fires before the instance of the Log object is set up.
		 *
		 * @since 3.0.0
		 *
		 * @param object EDD\Logs\Log      Instance of the log object.
		 * @param object              $log Log object returned from the database.
		 */
		do_action( 'edd_pre_setup_log', $this, $log );

		foreach ( get_object_vars( $log ) as $key => $value ) {
			$this->$key = $value;
		}

		/**
		 * Fires after the instance of the Log object is set up. Allows extensions to add items to this object via hook.
		 *
		 * @since 3.0.0
		 *
		 * @param object EDD\Logs\Log      Instance of the log object.
		 * @param object              $log Log object returned from the database.
		 */
		do_action( 'edd_post_setup_log', $this, $log );

		if ( ! empty( $this->id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Create a new log.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args {
	 *      Log attributes.
	 *
	 *      @type int    $object_id   Object ID.
	 *      @type string $object_type Object type.
	 *      @type string $type        Log type.
	 *      @type string $title       Log title.
	 *      @type string $content     Log content.
	 * }
	 *
	 * @return int Newly created log ID.
	 */
	public function create( $args = array() ) {
		/**
		 * Filters the arguments before being inserted into the database.
		 *
		 * @since 3.0.0
		 *
		 * @param array $args Discount args.
		 */
		$args = apply_filters( 'edd_insert_log', $args );

		$args = $this->sanitize_columns( $args );

		/**
		 * Fires before a log has been inserted into the database.
		 *
		 * @since 3.0.0
		 *
		 * @param array $args Discount args.
		 */
		do_action( 'edd_pre_insert_log', $args );

		$id = edd_add_log( $args );

		if ( $id ) {
			$this->id = $id;

			foreach ( $args as $key => $value ) {
				$this->{$key} = $value;
			}
		}

		/**
		 * Fires after a log has been inserted into the database.
		 *
		 * @since 3.0.0
		 *
		 * @param array $args Log args.
		 * @param int $id Log ID.
		 */
		do_action( 'edd_post_insert_log', $args, $this->id );

		return $id;
	}

	/**
	 * Update an existing log.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args {
	 *      Log attributes.
	 *
	 *      @type int    $object_id   Object ID.
	 *      @type string $object_type Object type.
	 *      @type string $type        Log type.
	 *      @type string $title       Log title.
	 *      @type string $content     Log content.
	 * }
	 * @return bool True on success, false otherwise.
	 */
	public function update( $args = array() ) {
		return edd_update_log( $this->id, $args );
	}

	/**
	 * Delete log.
	 *
	 * @since 3.0.0
	 *
	 * @return int|bool Number of rows deleted, false on error.
	 */
	public function delete() {
		$deleted = edd_delete_log( $this->id );

		return $deleted;
	}

	/**
	 * Retrieve log meta field for a log.
	 *
	 * @since 3.0.0
	 *
	 * @param string $meta_key The meta key to retrieve.
	 * @param bool   $single   Whether to return a single value.
	 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
	 */
	public function get_meta( $meta_key = '', $single = true ) {
		return edd_get_log_meta( $this->id, $meta_key, $single );
	}

	/**
	 * Add meta data field to a log.
	 *
	 * @since 3.0.0
	 *
	 * @param string $meta_key   Metadata name.
	 * @param mixed  $meta_value Metadata value.
	 * @param bool   $unique     Optional, default is false. Whether the same key should not be added.
	 * @return bool True on success, false otherwise.
	 */
	public function add_meta( $meta_key = '', $meta_value, $unique = false ) {
		return edd_add_log_meta( $this->id, $meta_key, $meta_value, $unique );
	}

	/**
	 * Update discount meta field based on log ID.
	 *
	 * @since 3.0.0
	 *
	 * @param string $meta_key   Metadata key.
	 * @param mixed  $meta_value Metadata value.
	 * @param mixed  $prev_value Optional. Previous value to check before removing.
	 * @return bool True on success, false otherwise.
	 */
	public function update_meta( $meta_key = '', $meta_value, $prev_value = '' ) {
		return edd_update_log_meta( $this->id, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Remove metadata matching criteria from a log.
	 *
	 * @since 3.0.0
	 *
	 * @param string $meta_key   Metadata key.
	 * @param mixed  $meta_value Optional. Metadata value.
	 * @return bool True on success, false otherwise.
	 */
	public function delete_meta( $meta_key = '', $meta_value = '' ) {
		return edd_delete_log_meta( $this->id, $meta_key, $meta_value );
	}

	/**
	 * Sanitize the data for update/create.
	 *
	 * @since 3.0.0
	 *
	 * @param array $data The data to sanitize.
	 * @return array $data The sanitized data, based off column defaults.
	 */
	private function sanitize_columns( $data ) {
		$default_values = array();

		foreach ( $data as $key => $type ) {
			// Only sanitize data that we were provided
			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}

			switch ( $type ) {
				case '%s':
					$data[ $key ] = sanitize_text_field( $data[ $key ] );
					break;

				case '%d':
					if ( ! is_numeric( $data[ $key ] ) || absint( $data[ $key ] ) !== (int) $data[ $key ] ) {
						$data[ $key ] = $default_values[ $key ];
					} else {
						$data[ $key ] = absint( $data[ $key ] );
					}
					break;

				case '%f':
					$value = floatval( $data[ $key ] );

					if ( ! is_float( $value ) ) {
						$data[ $key ] = $default_values[ $key ];
					} else {
						$data[ $key ] = $value;
					}
					break;

				default:
					$data[ $key ] = sanitize_text_field( $data[ $key ] );
					break;
			}
		}

		return $data;
	}
}