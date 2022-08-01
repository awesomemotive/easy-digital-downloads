<?php
/**
 * Adjustment Object.
 *
 * @package     EDD
 * @subpackage  Adjustments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Adjustments;

use EDD\Base_Object;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Adjustment Class.
 *
 * @since 3.0
 *
 * @property int $id
 * @property int $parent
 * @property string $name
 * @property string $code
 * @property string $status
 * @property string $type
 * @property string $scope
 * @property string $amount_type
 * @property float $amount
 * @property string $description
 * @property int $max_uses
 * @property int $use_count
 * @property int $once_per_customer
 * @property float $min_charge_amount
 * @property string $start_date
 * @property string $end_date
 * @property string $date_created
 * @property string $date_modified
 */
class Adjustment extends Base_Object {

	/**
	 * ID.
	 *
	 * @since 3.0
	 * @access protected
	 * @var int
	 */
	protected $id;

	/**
	 * Name.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $name;

	/**
	 * Code.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $code;

	/**
	 * Status.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $status;

	/**
	 * Adjustment Type (discount, fee, tax, credit).
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $type;

	/**
	 * Scope of the adjustment.
	 *
	 * global     - Applies to all products in the cart, save for those explicitly excluded through excluded_products
	 * not_global - Applies only to the products set in product_reqs
	 *
	 * This used to be called "is_not_global" but was changed to "scope" in 3.0.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $scope;

	/**
	 * Adjustment Type (Percentage or Flat Amount).
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $amount_type;

	/**
	 * Adjustment Amount.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    mixed float|int
	 */
	protected $amount = null;

	/**
	 * Maximum Uses.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $max_uses = null;

	/**
	 * Use Count.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $use_count = null;

	/**
	 * Minimum Amount.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    mixed int|float
	 */
	protected $min_charge_amount;

	/**
	 * Is Single Use per customer?
	 *
	 * @since  3.0
	 * @access protected
	 * @var    bool
	 */
	protected $once_per_customer = null;

	/**
	 * Created Date.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $date_created;

	/**
	 * Start Date.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string|null
	 */
	protected $start_date = null;

	/**
	 * End Date.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string|null
	 */
	protected $end_date = null;
}
