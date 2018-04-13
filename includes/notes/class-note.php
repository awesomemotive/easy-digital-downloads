<?php
/**
 * Note Object
 *
 * @package     EDD
 * @subpackage  Classes/Notes
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */
namespace EDD\Notes;

use EDD\Base_Object;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Note Class.
 *
 * @since 3.0.0
 */
class Note extends Base_Object {

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
	 * @param \object $note Note data from the database.
	 */
	public function __construct( $note = null ) {
		parent::__construct( $note );

		if ( is_object( $note ) ) {
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
	 * Retrieve note ID.
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
	 * Retrieve note content.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_content() {
		return $this->content;
	}

	/**
	 * Retrieve user ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	/**
	 * Retrieve the date the note was created.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_date_created() {
		return $this->date_created;
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
}