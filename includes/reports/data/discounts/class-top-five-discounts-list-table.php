<?php
/**
 * Top Five Discounts list table.
 *
 * @package     EDD
 * @subpackage  Reports/Data/Customers
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports\Data\Discounts;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Load \EDD_Customer_Reports_Table if not loaded
if ( ! class_exists( '\EDD_Customer_Reports_Table' ) ) {
	require_once EDD_PLUGIN_DIR . 'includes/admin/customers/class-customer-table.php';
}

/**
 * Top_Five_Discounts_List_Table class.
 *
 * @since 3.0
 */
class Top_Five_Discounts_List_Table extends \EDD_Discount_Codes_Table {

	/**
	 * Query the database and fetch the top five discounts.
	 *
	 * @since 3.0
	 *
	 * @return array $data Customers.
	 */
	public function discount_codes_data() {

		return edd_get_discounts( array(

		) );
	}

	/**
	 * Return empty array to disable sorting.
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array();
	}

	/**
	 * Return empty array to remove bulk actions.
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array();
	}

	/**
	 * Hide pagination.
	 *
	 * @since 3.0
	 *
	 * @param string $which
	 */
	protected function pagination( $which ) {

	}

	/**
	 * Hide table navigation.
	 *
	 * @since 3.0
	 *
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {

	}
}