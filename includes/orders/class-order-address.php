<?php
/**
 * Orders API - Address Object.
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
 * Order Address Class.
 *
 * @since 3.0
 *
 * @property int $id
 * @property int $order_id
 * @property string $type
 * @property string $status
 * @property string $address
 * @property string $country
 * @property string $region
 * @property string $postal_code
 * @property string $date_created
 * @property string $date_modified
 */
class Order_Address extends Base_Object {

	/**
	 * Order address ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $id;

	/**
	 * Order ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $order_id;

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
	 * Address.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $address;

	/**
	 * Country.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $country;

	/**
	 * Region
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $region;

	/**
	 * Postal code.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $postal_code;

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
