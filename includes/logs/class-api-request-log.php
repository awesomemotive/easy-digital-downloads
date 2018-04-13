<?php
/**
 * Logs API - API Request Log Object.
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
 * API Request Log Class.
 *
 * @since 3.0.0
 */
class Api_Request_Log extends Base_Object {

	/**
	 * API request log ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $id;

	/**
	 * User ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $user_id;

	/**
	 * API key.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $api_key;

	/**
	 * API token.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $token;

	/**
	 * API version.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $version;

	/**
	 * API request.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $request;

	/**
	 * IP.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $ip;

	/**
	 * Speed of the API request.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    float
	 */
	protected $time;

	/**
	 * Date log was created.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $date_created;

	/**
	 * Retrieve the API request log ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Retrieve the user ID of the user making the API request.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	/**
	 * Retrieve the API key of the user.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_api_key() {
		return $this->api_key;
	}

	/**
	 * Retrieve the token of the user.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_token() {
		return $this->token;
	}

	/**
	 * Retrieve the version of the API used.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieve the API request.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_request() {
		return $this->request;
	}

	/**
	 * Retrieve IP address of the client making the API request.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_ip() {
		return $this->ip;
	}

	/**
	 * Retrieve speed of the API request.
	 *
	 * @since 3.0.0
	 *
	 * @return float
	 */
	public function get_time() {
		return $this->time;
	}

	/**
	 * Retrieve the date the API request was created.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_date_created() {
		return $this->date_created;
	}
}
