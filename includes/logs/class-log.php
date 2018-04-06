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

use EDD\Base_Object;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Log Class.
 *
 * @since 3.0.0
 */
class Log extends Base_Object {

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
	 * Retrieve ID of the log.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Retrieve object ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public function get_object_id() {
		return $this->object_id;
	}

	/**
	 * Retrieve object type.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_object_type() {
		return $this->object_type;
	}

	/**
	 * Retrieve the type of log.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Retrieve the title of the log.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Retrieve the content of the log.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_content() {
		return $this->content;
	}

	/**
	 * Retrieve the date the log was created.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_date_created() {
		return $this->date_created;
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
}