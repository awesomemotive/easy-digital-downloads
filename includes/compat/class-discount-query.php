<?php
/**
 * Adjustment Query Class.
 *
 * @package     EDD
 * @subpackage  Database\Queries
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */
namespace EDD\Database\Queries;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class used for querying discounts.
 *
 * @since 3.0
 *
 * @see \EDD\Database\Queries\Adjustment::__construct() for accepted arguments.
 */
class Discount extends Adjustment {

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 3.0
	 * @access protected
	 * @var mixed
	 */
	protected $item_shape = 'EDD_Discount';

	/**
	 * Queries the database and retrieves items or counts.
	 *
	 * This method exists for backwards compatibility with `discount->type`
	 * properties, which is now `discount->amount_type`.
	 *
	 * @since 3.0
	 *
	 * @param string|array $query Array or URL query string of parameters.
	 * @return array|int List of items, or number of items when 'count' is passed as a query var.
	 */
	public function query( $query = array() ) {

		// Switch `type` to `amount_type`
		if ( ! empty( $query['type'] ) ) {
			$query['amount_type'] = $query['type'];
		}

		// Force `type` to `discount`
		$query['type'] = 'discount';

		return parent::query( $query );
	}
}
