<?php
/**
 * Tax Rate Object.
 *
 * @package     EDD
 * @subpackage  Tax_Rates
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Tax_Rates;

use EDD\Base_Object;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Tax Rate Class.
 *
 * @since 3.0
 *
 * @property int $id
 * @property string $status
 * @property string $country
 * @property string $region
 * @property string $scope
 * @property float $rate
 * @property string $start_date
 * @property string $end_date
 * @property string $date_created
 * @property string $date_modified
 */
class Tax_Rate extends Base_Object {

	/**
	 * ID.
	 *
	 * @since 3.0
	 * @access protected
	 * @var int
	 */
	protected $id;

	/**
	 * Status.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $status;

	/**
	 * Country.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $country;

	/**
	 * Region.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $region;

	/**
	 * Scope.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $scope;

	/**
	 * Rate.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    float
	 */
	protected $rate;

	/**
	 * Start Date.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $start_date;

	/**
	 * End Date.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $end_date;

	/**
	 * Date Created.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $date_created;

	/**
	 * Date Modified.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $date_modified;
}