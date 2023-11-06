<?php
/**
 * Note Object
 *
 * @package     EDD
 * @subpackage  Classes/Notes
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Notes;

use EDD\Base_Object;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Note Class.
 *
 * @since 3.0
 *
 * @property int $id
 * @property int $object_id
 * @property string $object_type
 * @property int $user_id
 * @property string $content
 * @property string $date_created
 * @property string $date_modified
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
	 * Date modified.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $date_modified;

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
}
