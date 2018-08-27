<?php
/**
 * Customers API - Email Address Object.
 *
 * @package     EDD
 * @subpackage  Customers
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Customers;

use EDD\Base_Object;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Customer Email Address Class.
 *
 * @since 3.0
 *
 * @property int $id
 * @property int $customer_id
 * @property string $type
 * @property string $status
 * @property string $email
 * @property string $date_created
 * @property string $date_modified
 */
class Customer_Email_Address extends Base_Object {

	/**
	 * Customer address ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $id;

	/**
	 * Customer ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $customer_id;

	/**
	 * Type.
	 *
	 * @since  3.0.0
	 * @access protected
	 * @var    string
	 */
	protected $type;

	/**
	 * Status.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $status;

	/**
	 * Email address.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $email;

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
