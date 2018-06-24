<?php
/**
 * Earnings by Taxonomy list table.
 *
 * @package     EDD
 * @subpackage  Reports/Data/File_Downloads
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports\Data\Downloads;

use EDD\Reports as Reports;
use EDD\Orders as Orders;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Earnings_By_Taxonomy_List_Table class.
 *
 * @since 3.0
 */
class Earnings_By_Taxonomy_List_Table extends \WP_List_Table {

	/**
	 * Query the database and fetch the top five most downloaded products.
	 *
	 * @since 3.0
	 *
	 * @return array Taxonomies.
	 */
	public function taxonomy_data() {
		$filter = Reports\get_filter_value( 'dates' );

		return array();
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @since 3.0
	 *
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		return array(
			'taxonomy'       => __( 'Taxonomy', 'easy-digital-downloads' ),
			'total_sales'    => __( 'Total Sales', 'easy-digital-downloads' ),
			'total_earnings' => __( 'Total Earnings', 'easy-digital-downloads' ),
			'avg_sales'      => __( 'Monthly Sales Average', 'easy-digital-downloads' ),
			'avg_earnings'   => __( 'Monthly Earnings Average', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Setup the final data for the table.
	 *
	 * @since 3.0
	 */
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->taxonomy_data();
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 3.0
	 */
	public function no_items() {
		esc_html_e( 'No taxonomies found.', 'easy-digital-downloads' );
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 3.0
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'name';
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