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
namespace EDD\Orders;

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
 * @property string $name
 * @property string $first_name
 * @property string $last_name
 * @property string $address
 * @property string $address2
 * @property string $city
 * @property string $region
 * @property string $postal_code
 * @property string $country
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
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $type;

	/**
	 * Name.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $name;

	/**
	 * First name.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $first_name;

	/**
	 * Last name.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $last_name;

	/**
	 * Address.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $address;

	/**
	 * Address line 2.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $address2;

	/**
	 * City.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $city;

	/**
	 * Region.
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
	 * Country.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $country;


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
