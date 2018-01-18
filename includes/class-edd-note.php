<?php
/**
 * Note Object
 *
 * @package     EDD
 * @subpackage  Classes/Notes
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EDD_Note Class.
 *
 * @since 3.0
 */
class EDD_Note {

	/**
	 * Note ID.
	 *
	 * @since 3.0
	 * @access protected
	 * @var int
	 */
	protected $id;

	/**
	 * Object ID.
	 *
	 * @since 3.0
	 * @access protected
	 * @var int
	 */
	protected $object_id;

	/**
	 * Object Type.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $object_type;

	/**
	 * Note content.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $content;

	/**
	 * User ID.
	 *
	 * @since 3.0
	 * @access protected
	 * @var int
	 */
	protected $user_id;

	/**
	 * Date created.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $date_created;

	/**
	 * Database abstraction.
	 *
	 * @since 3.0
	 * @access protected
	 * @var EDD_DB_Notes
	 */
	protected $db;

	/**
	 * Constructor.
	 *
	 * @since 3.0
	 * @access protected
	 *
	 * @param int $note_id Note ID.
	 */
	public function __construct( $note_id = 0 ) {
		$this->db = EDD()->notes;

		$note = $this->db->get( $note_id );

		if ( is_object( $note ) ) {
			foreach ( get_object_vars( $note ) as $key => $value ) {
				$this->$key = $value;
			}
		}
	}

	/**
	 * Magic __get method to dispatch a call to retrieve a protected property.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @param mixed $key
	 *
	 * @return mixed
	 */
	public function __get( $key ) {
		$key = sanitize_key( $key );

		if ( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		} elseif ( property_exists( $this, $key ) ) {
			return apply_filters( 'edd_note_' . $key, $this->{$key}, $this->id );
		}
	}

	/**
	 * Magic __set method to dispatch a call to update a protected property.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @param string $key Property name.
	 * @param mixed $value Property value.
	 *
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
	 * Magic __isset method to allow empty checks on protected elements.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @param string $key The attribute to get
	 *
	 * @return boolean If the item is set or not
	 */
	public function __isset( $key ) {
		if ( property_exists( $this, $key ) ) {
			return false === empty( $this->{$key} );
		} else {
			return null;
		}
	}

	/**
	 * Create a new note.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @param array $args {
	 *      Note attributes.
	 * }
	 *
	 * @return int Newly created note ID.
	 */
	public function create( $args = array() ) {
		/**
		 * Filters the arguments before being inserted into the database.
		 *
		 * @since 3.0
		 *
		 * @param array $args Note args.
		 */
		$args = apply_filters( 'edd_insert_note', $args );

		$args = $this->sanitize_columns( $args );

		/**
		 * Fires before a note has been inserted into the database.
		 *
		 * @since 3.0
		 *
		 * @param array $args Discount args.
		 */
		do_action( 'edd_pre_insert_note', $args );

		$id = $this->db->insert( $args );

		if ( $id ) {
			$this->id = $id;

			foreach ( $args as $key => $value ) {
				$this->{$key} = $value;
			}
		}

		/**
		 * Fires after a note has been inserted into the database.
		 *
		 * @since 3.0
		 *
		 * @param array $args Note args.
		 * @param int $id Note ID.
		 */
		do_action( 'edd_post_insert_note', $args, $this->id );

		return $id;
	}

	/**
	 * Update an existing note.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @param array $args {
	 *      Note attributes.
	 * }
	 *
	 * * @return bool True on success, false otherwise.
	 */
	public function update( $args = array() ) {
		return $this->db->update( $this->id, $args );
	}

	/**
	 * Delete a note.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @return bool True if deleted, false otherwise.
	 */
	public function delete() {
		$deleted = $this->db->delete( $this->id );

		if ( $deleted ) {
			EDD()->note_meta->delete_all_meta( $this->id );
		}

		return $deleted;
	}

	/**
	 * Add meta to a note.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param string $meta_key   Metadata key.
	 * @param mixed  $meta_value Metadata value.
	 * @param bool   $unique     Optional, default is false. Whether the same key should not be added.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function add_meta( $meta_key = '', $meta_value, $unique = false ) {
		return EDD()->note_meta->add_meta( $this->id, $meta_key, $meta_value, $unique );
	}

	/**
	 * Retrieve meta for a note.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param string $meta_key Metadata key.
	 * @param bool   $single   Whether to return as an array or single value.
	 *
	 * @return mixed string|array Array if $single is false, or value of meta key.
	 */
	public function get_meta( $meta_key = '', $single = true ) {
		return EDD()->note_meta->get_meta( $this->id, $meta_key, $single );
	}

	/**
	 * Update an existing meta field for a note.
	 *
	 * @param string $meta_key   Metadata key.
	 * @param mixed  $meta_value New metadata value.
	 * @param string $prev_value Optional. Previous metadata value.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function update_meta( $meta_key = '', $meta_value, $prev_value = '' ) {
		return EDD()->note_meta->update_meta( $this->id, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Remove meta from a note.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param string $meta_key   Metadata key.
	 * @param string $meta_value Optional. Metadata value.
	 *
	 * @return bool
	 */
	public function delete_meta( $meta_key = '', $meta_value = '' ) {
		return EDD()->note_meta->delete_meta( $this->id, $meta_key, $meta_value );
	}

	/**
	 * Sanitize the data for update/create.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param array $data The data to sanitize.
	 *
	 * @return array $data The sanitized data, based off column defaults.
	 */
	private function sanitize_columns( $data ) {
		$columns        = $this->db->get_columns();
		$default_values = $this->db->get_column_defaults();

		foreach ( $columns as $key => $type ) {
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

				default:
					$data[ $key ] = sanitize_text_field( $data[ $key ] );
					break;
			}
		}

		return $data;
	}
}