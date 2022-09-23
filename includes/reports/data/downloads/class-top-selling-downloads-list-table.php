<?php
/**
 * Top Selling Downloads list table.
 *
 * @package     EDD
 * @subpackage  Reports/Data/File_Downloads
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports\Data\Downloads;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Stats as Stats;
use EDD\Reports as Reports;
use EDD\Admin\List_Table;

/**
 * Top_Selling_Downloads_List_Table class.
 *
 * @since 3.0
 */
class Top_Selling_Downloads_List_Table extends List_Table {

	/**
	 * Query the database and fetch the top five most downloaded products.
	 *
	 * @since 3.0
	 *
	 * @return array Downloads.
	 */
	public function get_data() {
		$filter = Reports\get_filter_value( 'dates' );

		$stats = new Stats();

		return $stats->get_most_valuable_order_items( array(
			'number'   => 10,
			'range'    => $filter['range'],
			'currency' => '',
		) );
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
			'name'           => __( 'Name', 'easy-digital-downloads' ),
			'price'          => __( 'Price', 'easy-digital-downloads' ),
			'sales'          => __( 'Sales', 'easy-digital-downloads' ),
			'earnings'       => __( 'Net Earnings', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Render the Name Column.
	 *
	 * @since 3.0
	 *
	 * @param \stdClass $download Download object.
	 * @return string Data shown in the Name column.
	 */
	public function column_name( $download ) {
		if ( ! $download->object instanceof \EDD_Download ) {
			return '&mdash;';
		}

		// Check for variable pricing
		$retval = ! is_null( $download->price_id ) && is_numeric( $download->price_id )
			? edd_get_download_name( $download->object->ID, $download->price_id )
			: edd_get_download_name( $download->object->ID );

		return $retval;
	}

	/**
	 * Render the Price Column.
	 *
	 * @since 3.0
	 *
	 * @param \stdClass $download Download object.
	 * @return string Data shown in the Price column.
	 */
	public function column_price( $download ) {
		if ( ! $download->object instanceof \EDD_Download ) {
			return '&mdash;';
		}

		// Check for variable pricing
		$retval = ! is_null( $download->price_id ) && is_numeric( $download->price_id )
			? edd_price( $download->object->ID, false, $download->price_id )
			: edd_price( $download->object->ID, false );

		return $retval;
	}

	public function column_sales( $download ) {
		if ( ! $download->object instanceof \EDD_Download ) {
			return '&mdash;';
		}

		return current_user_can( 'view_product_stats', $download->object->ID )
			? $download->sales
			: '&mdash;';
	}

	public function column_earnings( $download ) {
		if ( ! $download->object instanceof \EDD_Download ) {
			return '&mdash;';
		}

		return current_user_can( 'view_product_stats', $download->object->ID )
			? edd_currency_filter( edd_format_amount( $download->total ) )
			: '&mdash;';
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
		$this->items           = $this->get_data();
	}

	/**
	 * Get the base URL for the discount list table
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_base_url() {
		return remove_query_arg( edd_admin_removable_query_args(), edd_get_admin_base_url() );
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 3.0
	 */
	public function no_items() {
		esc_html_e( 'No downloads found.', 'easy-digital-downloads' );
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
