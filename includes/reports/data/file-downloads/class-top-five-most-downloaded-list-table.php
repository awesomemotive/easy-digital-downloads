<?php
/**
 * Top Five Most Downloaded Products list table.
 *
 * @package     EDD
 * @subpackage  Reports/Data/File_Downloads
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports\Data\File_Downloads;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Reports as Reports;
use EDD\Stats as Stats;
use EDD\Admin\List_Table;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Top_Five_Most_Downloaded_List_Table class.
 *
 * @since 3.0
 */
class Top_Five_Most_Downloaded_List_Table extends List_Table {

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

		return $stats->get_most_downloaded_products( array(
			'number' => 5,
			'range'  => $filter['range']
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
			'name'           => __( 'Name',           'easy-digital-downloads' ),
			'download_count' => __( 'File Downloads', 'easy-digital-downloads' ),
			'price'          => __( 'Price',          'easy-digital-downloads' ),
			'sales'          => __( 'Sales',          'easy-digital-downloads' ),
			'earnings'       => __( 'Earnings',       'easy-digital-downloads' )
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

		// Download title
		return $download->object->get_name();
	}

	/**
	 * Render the Download Count Column.
	 *
	 * @since 3.0
	 *
	 * @param \stdClass $download Download object.
	 * @return string Data shown in the Download Count column.
	 */
	public function column_download_count( $download ) {
		if ( ! $download->object instanceof \EDD_Download ) {
			return '&mdash;';
		}

		return $download->total;
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

		if ( $download->object->has_variable_prices() ) {
			return edd_price_range( $download->object->ID );
		} else {
			return edd_price( $download->object->ID, false );
		}
	}

	public function column_sales( $download ) {
		if ( ! $download->object instanceof \EDD_Download ) {
			return '&mdash;';
		}

		return current_user_can( 'view_product_stats', $download->object->ID )
			? edd_get_download_sales_stats( $download->object->ID )
			: '&mdash;';
	}

	public function column_earnings( $download ) {
		if ( ! $download->object instanceof \EDD_Download ) {
			return '&mdash;';
		}

		return current_user_can( 'view_product_stats', $download->object->ID )
			? edd_currency_filter( edd_format_amount( edd_get_download_earnings_stats( $download->object->ID ) ) )
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