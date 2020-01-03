<?php
/**
 * Logs API - Log Object
 *
 * @package     EDD
 * @subpackage  Logs
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Logs;

use EDD\Base_Object;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Log Class.
 *
 * @since 3.0
 *
 * @property int $id
 * @property int $object_id
 * @property string $object_type
 * @property int $user_id
 * @property string $type
 * @property string $title
 * @property string $content
 * @property string $date_created
 * @property string $date_modified
 */
class Log extends Base_Object {

	/**
	 * Log ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $id;

	/**
	 * Object ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $object_id;

	/**
	 * Object type.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $object_type;

	/**
	 * User ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $user_id;

	/**
	 * Log type.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $type;

	/**
	 * Log title.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $title;

	/**
	 * Log content.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $content;

	/**
	 * Date log was created.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $date_created;

	/**
	 * Date log was last modified.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $date_modified;
}