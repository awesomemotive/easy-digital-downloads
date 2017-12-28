<?php
/**
 * Log Object
 *
 * @package     EDD
 * @subpackage  Classes/Logs
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EDD Log Class.
 *
 * @since 3.0
 */
class EDD_Log {

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
	 * Log message.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $message;

	/**
	 * Date log was created.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $date_created;

	/**
	 * Database abstraction.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    EDD_DB_Logs
	 */
	protected $db;

	/**
	 * Constructor.
	 *
	 * @since  3.0
	 * @access protected
	 *
	 * @param int $log_id Log ID.
	 */
	public function __construct( $log_id = 0 ) {

	}

	/**
	 * Setup object vars.
	 *
	 * @since 3.0
	 * @access private
	 *
	 * @param object $log Log data.
	 * @return bool Object var initialisation successful or not.
	 */
	private function setup_log( $log ) {

	}
}