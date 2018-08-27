<?php
/**
 * Discount Query Class.
 *
 * @package     EDD
 * @subpackage  Database\Queries
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Compat;

use EDD\Database\Queries as Queries;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class used for querying discounts.
 *
 * @since 3.0
 *
 * @see \EDD\Database\Queries\Adjustment::__construct() for accepted arguments.
 */
class Discount_Query extends Queries\Adjustment {

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 3.0
	 * @access protected
	 * @var mixed
	 */
	protected $item_shape = 'EDD_Discount';

	/**
	 * Swap out types in a query.
	 *
	 * @since 3.0
	 *
	 * @param array $query Array of query arguments
	 * @return array
	 */
	public function query( $query = array() ) {
		return parent::query( $this->swap_types( $query ) );
	}

	/**
	 * Swap out types in an item.
	 *
	 * @since 3.0
	 *
	 * @param array $item Array of item arguments
	 * @return array
	 */
	public function filter_item( $item = array() ) {
		return parent::filter_item( $this->swap_types( $item ) );
	}

	/**
	 * Swap out the type arguments.
	 *
	 * @since 3.0
	 *
	 * @param array $args
	 * @return array
	 */
	private function swap_types( $args = array() ) {

		// Switch `type` to `amount_type`
		if ( empty( $args['amount_type'] ) && ! empty( $args['type'] ) ) {
			$args['amount_type'] = $args['type'];
		}

		// Force `type` to `discount`
		$args['type'] = 'discount';

		// Return swapped arguments
		return $args;
	}
}
