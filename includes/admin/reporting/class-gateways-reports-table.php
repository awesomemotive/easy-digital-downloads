<?php
/**
 * Gateways Reports Table Class
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Admin\List_Table;

/**
 * EDD_Gateway_Reports_Table Class
 *
 * Renders the Download Reports table
 *
 * @since 1.5
 */
class EDD_Gateway_Reports_Table extends List_Table {

	/**
	 * Get things started
	 *
	 * @since 1.5
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'report-gateway',
			'plural'   => 'report-gateways',
			'ajax'     => false
		) );
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 2.5
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'label';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @since 1.5
	 *
	 * @param array $item Contains all the data of the downloads
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since 1.5
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		return array(
			'label'          => __( 'Gateway',                'easy-digital-downloads' ),
			'complete_sales' => __( 'Complete Sales',         'easy-digital-downloads' ),
			'pending_sales'  => __( 'Pending / Failed Sales', 'easy-digital-downloads' ),
			'total_sales'    => __( 'Total Sales',            'easy-digital-downloads' )
		);
	}

	/**
	 * Outputs the reporting views
	 *
	 * @since 1.5
	 * @return void
	 */
	public function bulk_actions( $which = '' ) {
		// These aren't really bulk actions but this outputs the markup in
		// the right place.
		edd_report_views();
	}

	/**
	 * Builds and retrieves all of the payment gateways reports data.
	 *
	 * @since 1.5
	 * @deprecated 3.0 Use get_data()
	 *
	 * @return array All the data for customer reports.
	 */
	public function reports_data() {
		_edd_deprecated_function( __METHOD__, '3.0', 'EDD_Gateway_Reports_Table::get_data()' );

		return $this->get_data();
	}

	/**
	 * Retrieves all of the payment gateways reports data.
	 *
	 * @since 3.0
	 *
	 * @return array Payment gateways reports table data.
	 */
	public function get_data() {

		$reports_data = array();
		$gateways     = edd_get_payment_gateways();

		foreach ( $gateways as $gateway_id => $gateway ) {

			$complete_count = edd_count_sales_by_gateway( $gateway_id, edd_get_gross_order_statuses() );
			$pending_count  = edd_count_sales_by_gateway( $gateway_id, edd_get_incomplete_order_statuses() );

			$reports_data[] = array(
				'ID'             => $gateway_id,
				'label'          => $gateway['admin_label'],
				'complete_sales' => edd_format_amount( $complete_count, false ),
				'pending_sales'  => edd_format_amount( $pending_count,  false ),
				'total_sales'    => edd_format_amount( $complete_count + $pending_count, false ),
			);
		}

		return $reports_data;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @since 1.5
	 * @uses EDD_Gateway_Reports_Table::get_columns()
	 * @uses EDD_Gateway_Reports_Table::get_sortable_columns()
	 * @uses EDD_Gateway_Reports_Table::reports_data()
	 * @return void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->get_data();
	}
}

/**
 * Back-compat for typo
 *
 * @see https://github.com/easydigitaldownloads/easy-digital-downloads/issues/6549
 */
class_alias( 'EDD_Gateway_Reports_Table', 'EDD_Gateawy_Reports_Table' );
