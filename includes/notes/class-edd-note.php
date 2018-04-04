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
namespace EDD\Notes;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Note Class.
 *
 * @since 3.0
 */
class Note {

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
	 * Comment ID.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $comment_ID;

	/**
	 * ID of the post the comment is associated with.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $comment_post_ID = 0;

	/**
	 * Comment author name.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $comment_author = '';

	/**
	 * Comment author email address.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $comment_author_email = '';

	/**
	 * Comment author URL.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $comment_author_url = '';

	/**
	 * Comment author IP address (IPv4 format).
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $comment_author_IP = '';

	/**
	 * Comment date in YYYY-MM-DD HH:MM:SS format.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $comment_date = '0000-00-00 00:00:00';

	/**
	 * Comment GMT date in YYYY-MM-DD HH::MM:SS format.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $comment_date_gmt = '0000-00-00 00:00:00';

	/**
	 * Comment content.
	 *
	 * @since 3.0
	 * @var string
	 */
	public $comment_content;

	/**
	 * Comment karma count.
	 *
	 * @since 3.0
	 * @var int
	 */
	public $comment_karma = 0;

	/**
	 * Comment approval status.
	 *
	 * @since 3.0
	 * @var string
	 */
	public $comment_approved = '1';

	/**
	 * Comment author HTTP user agent.
	 *
	 * @since 3.0
	 * @var string
	 */
	public $comment_agent = '';

	/**
	 * Comment type.
	 *
	 * @since 3.0
	 * @var string
	 */
	public $comment_type = '';

	/**
	 * Parent comment ID.
	 *
	 * @since 3.0
	 * @var int
	 */
	public $comment_parent = 0;

	/**
	 * Constructor.
	 *
	 * @since 3.0
	 * @access protected
	 *
	 * @param int|object $data Note data or note ID.
	 */
	public function __construct( $data ) {
		$note = null;

		if ( is_object( $data ) ) {
			$note = $data;
		} else if ( is_numeric( $data ) ) {
			$note = edd_get_note( $data);
		}

		if ( $note ) {
			foreach ( get_object_vars( $note ) as $key => $value ) {
				$this->$key = $value;
			}

			/**
			 * We fill object vars which have the same name as the object vars in WP_Comment for backwards compatibility
			 * purposes.
			 */
			$this->comment_ID       = absint( $this->id );
			$this->comment_post_ID  = $this->object_id;
			$this->comment_type     = $this->object_type;
			$this->comment_date     = $this->date_created;
			$this->comment_date_gmt = $this->date_created;
			$this->comment_content  = $this->content;
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
		if ( ! in_array( $key, array( 'comment_ID', 'comment_post_ID' ), true ) ) {
			$key = sanitize_key( $key );
		}

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

		$id = edd_add_note( $args );

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
		return edd_update_note( $this->id, $args );
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
		$deleted = edd_delete_note( $this->id );

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
		return edd_add_note_meta( $this->id, $meta_key, $meta_value, $unique );
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
		return edd_get_note_meta( $this->id, $meta_key, $single );
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
		return edd_update_note_meta( $this->id, $meta_key, $meta_value, $prev_value );
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
		return edd_delete_note_meta( $this->id, $meta_key, $meta_value );
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
		$default_values = array();

		foreach ( $data as $key => $type ) {
			// Only sanitize data that we were provided
			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}

			switch ( $type ) {
				case '%s':
					if ( 'email' == $key ) {
						$data[$key] = sanitize_email( $data[$key] );
					} elseif ( 'notes' == $key ) {
						$data[$key] = strip_tags( $data[$key] );
					} else {
						$data[$key] = sanitize_text_field( $data[$key] );
					}
					break;
				case '%d':
					if ( ! is_numeric( $data[$key] ) || (int) $data[$key] !== absint( $data[$key] ) ) {
						$data[$key] = $default_values[$key];
					} else {
						$data[$key] = absint( $data[$key] );
					}
					break;
				case '%f':
					// Convert what was given to a float
					$value = floatval( $data[$key] );

					if ( ! is_float( $value ) ) {
						$data[$key] = $default_values[$key];
					} else {
						$data[$key] = $value;
					}
					break;
				default:
					$data[$key] = sanitize_text_field( $data[$key] );
					break;
			}
		}

		return $data;
	}
}