<?php
/**
 * Tax Rate Object.
 *
 * @package     EDD
 * @subpackage  Adjustments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Adjustments;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Tax Rate Class.
 *
 * @since 3.0
 *
 * @property int $id
 * @property string $country
 * @property string $region
 */
class Tax_Rate extends Adjustment {

	/**
	 * ID.
	 *
	 * @since 3.0
	 * @access protected
	 * @var int
	 */
	protected $id;

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
	 * Constructor.
	 *
	 * @since 3.0
	 *
	 * @param mixed $object Object to populate members for.
	 */
	public function __construct( $object = null ) {
		parent::__construct( $object );

		$this->country = $this->name;
		$this->region  = $this->description;
	}
}