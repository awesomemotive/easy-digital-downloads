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
class File_Download_Log extends Base_Object {

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
	 * User ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $user_id;

	/**
	 * Email address.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $email;

	/**
	 * IP.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $ip;

	/**
	 * Date created.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $date_created;

	/**
	 * Retrieve file download log ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Retrieve download ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public function get_download_id() {
		return $this->download_id;
	}

	/**
	 * Retrieve file ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public function get_file_id() {
		return $this->file_id;
	}

	/**
	 * Retrieve payment ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public function get_payment_id() {
		return $this->payment_id;
	}

	/**
	 * Retrieve price ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public function get_price_id() {
		return $this->price_id;
	}

	/**
	 * Retrieve user ID of the user who downloaded the file.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	/**
	 * Retrieve email address.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Retrieve IP address of client used to download file.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_ip() {
		return $this->ip;
	}

	/**
	 * Retrieve the date the file was downloaded.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_date_created() {
		return $this->date_created;
	}
}
