<?php
/**
 * Logs API - API Request Log Object.
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
 * API Request Log Class.
 *
 * @since 3.0
 *
 * @property int $id
 * @property int $user_id
 * @property string $api_key
 * @property string $token
 * @property string $version
 * @property string $request
 * @property string $error
 * @property string $ip
 * @property string $time
 * @property string $date_created
 * @property string $date_modified
 */
class Api_Request_Log extends Base_Object {

	/**
	 * API request log ID.
	 *
	 * @since  3.0.0
	 * @access protected
	 * @var    int
	 */
	protected $id;

	/**
	 * User ID.
	 *
	 * @since  3.0.0
	 * @access protected
	 * @var    int
	 */
	protected $user_id;

	/**
	 * API key.
	 *
	 * @since  3.0.0
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
	 * @since  3.0.0
	 * @access protected
	 * @var    string
	 */
	protected $request;

	/**
	 * Request errors.
	 *
	 * @since  3.0.0
	 * @access protected
	 * @var    string
	 */
	protected $error;

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
	 * Date modified.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $date_modified;
}
