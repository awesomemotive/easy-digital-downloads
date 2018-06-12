<?php
/**
 * Adjustment Object
 *
 * @package     EDD
 * @subpackage  Classes/Adjustments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
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
 * @property int $object_id
 * @property string $object_type
 * @property int $user_id
 * @property string $content
 * @property string $date_completed
 * @property string $date_modified
 */
class Adjustment extends Base_Object {

	/**
	 * Adjustment ID.
	 *
	 * @since 3.0
	 * @access protected
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Adjustment Name.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $name = null;

	/**
	 * Adjustment Code.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $code = null;

	/**
	 * Adjustment Status (Active or Inactive).
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $status = null;

	/**
	 * Adjustment Type (discount, fee, tax, credit).
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $type = null;

	/**
	 * Scope of the discount.
	 *
	 * global     - Applies to all products in the cart, save for those explicitly excluded through excluded_products
	 * not_global - Applies only to the products set in product_reqs
	 *
	 * This used to be called "is_not_global" but was changed to "scope" in 3.0.
	 *
	 * @since 3.0
	 * @access protected
	 * @var bool
	 */
	protected $scope = null;

	/**
	 * Adjustment Type (Percentage or Flat Amount).
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $amount_type = null;

	/**
	 * Adjustment Amount.
	 *
	 * @since 3.0
	 * @access protected
	 * @var mixed float|int
	 */
	protected $amount = null;

	/**
	 * Uses.
	 *
	 * @since 3.0
	 * @access protected
	 * @var int
	 */
	protected $use_count = null;

	/**
	 * Maximum Uses.
	 *
	 * @since 3.0
	 * @access protected
	 * @var int
	 */
	protected $max_uses = null;

	/**
	 * Minimum Amount.
	 *
	 * @since 3.0
	 * @access protected
	 * @var mixed int|float
	 */
	protected $min_cart_price = null;

	/**
	 * Is Single Use per customer?
	 *
	 * @since 3.0
	 * @access protected
	 * @var bool
	 */
	protected $once_per_customer = null;

	/**
	 * Product Condition
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $product_condition = null;

	/**
	 * Created Date.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $date_created = null;

	/**
	 * Start Date.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $start_date = null;

	/**
	 * End Date.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $end_date = null;

	/**
	 * Download Requirements.
	 *
	 * @since 3.0
	 * @access protected
	 * @var array
	 */
	protected $product_reqs = array();

	/**
	 * Excluded Downloads.
	 *
	 * @since 3.0
	 * @access protected
	 * @var array
	 */
	protected $excluded_products = array();

	/**
	 * Constructor.
	 *
	 * @since 3.0
	 * @access protected
	 *
	 * @param \object $adjustment Adjustment data from the database.
	 */
	public function __construct( $adjustment = null ) {
		parent::__construct( $adjustment );
	}
}