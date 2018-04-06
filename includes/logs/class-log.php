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
	 * @param \object $log Log data from the database.
	 */
	public function __construct( $log = null ) {
		if ( is_object( $log ) ) {
			foreach ( get_object_vars( $log ) as $key => $value ) {
				$this->{$key} = $value;
			}
		}
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