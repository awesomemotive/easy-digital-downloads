<?php
/**
 * Logs API - File Download Log Object.
 *
 * @package     EDD
 * @subpackage  Logs
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Logs;

use EDD\Base_Object;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_File_Download_Log Class.
 *
 * @since 3.0
 */
class File_Download_Log extends Base_Object{

	/**
	 * File download log ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $id;

	/**
	 * Download ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $download_id;

	/**
	 * File ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $file_id;

	/**
	 * Payment ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $payment_id;

	/**
	 * Price ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $price_id;

	/**
	 * User ID of the user who downloaded the file.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $user_id;

	/**
	 * Email address of the user who downloaded the file.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $email;

	/**
	 * IP address of the client that downloaded the file.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $ip;

	/**
	 * Date log was created.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $date_created;
}
