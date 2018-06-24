<?php
/**
 * Logs API - File Download Log Object.
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
 * EDD_File_Download_Log Class.
 *
 * @since 3.0
 *
 * @property int $id
 * @property int $product_id
 * @property int $file_id
 * @property int $order_id
 * @property int $price_id
 * @property int $customer_id
 * @property string $ip
 * @property string $user_agent
 * @property string $date_created
 * @property string $date_modified
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
	 * Product ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $product_id;

	/**
	 * File ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $file_id;

	/**
	 * Order ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $order_id;

	/**
	 * Price ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $price_id;

	/**
	 * Customer ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $customer_id;

	/**
	 * IP.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $ip;

	/**
	 * User Agent.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $user_agent;

	/**
	 * Date created.
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
