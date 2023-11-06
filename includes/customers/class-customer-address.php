<?php
/**
 * Customers API - Address Object.
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
 * Customer Address Class.
 *
 * @since 3.0
 *
 * @property int $id
 * @property int $customer_id
 * @property string $type
 * @property string $status
 * @property string $name
 * @property string $address
 * @property string $address2
 * @property string $city
 * @property string $region
 * @property string $postal_code
 * @property string $country
 * @property string $date_created
 * @property string $date_modified
 * @property bool   $is_primary
 */
class Customer_Address extends Base_Object {

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
	 * Name.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $name;

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

	/**
	 * Is primary.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    bool
	 */
	protected $is_primary;
}
