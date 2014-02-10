<?php
/**
 * Gateways Reports Table Class
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EDD_Gateawy_Reports_Table Class
 *
 * Renders the Download Reports table
 *
 * @since 1.5
 */
class EDD_Gateawy_Reports_Table extends WP_List_Table {

	/**
	 * @var int Number of items per page
	 * @since 1.5
	 */
	public $per_page = 30;


	/**
	 * Get things started
	 *
	 * @since 1.5
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular'  => edd_get_label_singular(),    // Singular name of the listed records
			'plural'    => edd_get_label_plural(),    	// Plural name of the listed records
			'ajax'      => false             			// Does this table support ajax?
		) );

	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since 1.5
	 *
	 * @param array $item Contains all the data of the downloads
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		switch( $column_name ){
			default:
				return $item[ $column_name ];
		}
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since 1.5
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'label'          => __( 'Gateway', 'edd' ),
			'complete_sales' => __( 'Complete Sales', 'edd' ),
			'pending_sales'  => __( 'Pending / Failed Sales', 'edd' ),
			'total_sales'    => __( 'Total Sales', 'edd' )
		);

		return $columns;
	}


	/**
	 * Retrieve the current page number
	 *
	 * @access public
	 * @since 1.5
	 * @return int Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}


	/**
	 * Outputs the reporting views
	 *
	 * @access public
	 * @since 1.5
	 * @return void
	 */
	public function bulk_actions() {
		// These aren't really bulk actions but this outputs the markup in the right place
		edd_report_views();
	}


	/**
	 * Build all the reports data
	 *
	 * @access public
	 * @since 1.5
	 * @return array $reports_data All the data for customer reports
	 */
	public function reports_data() {

		$reports_data = array();
		$gateways     = edd_get_payment_gateways();

		foreach ( $gateways as $gateway_id => $gateway ) {

			$complete_count = edd_count_sales_by_gateway( $gateway_id, 'publish' );
			$pending_count  = edd_count_sales_by_gateway( $gateway_id, array( 'pending', 'failed' ) );

			$reports_data[] = array(
				'ID'             => $gateway_id,
				'label'          => $gateway['admin_label'],
				'complete_sales' => edd_format_amount( $complete_count, false ),
				'pending_sales'  => edd_format_amount( $pending_count, false ),
				'total_sales'    => edd_format_amount( $complete_count + $pending_count, false )
			);
		}

		return $reports_data;
	}


	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since 1.5
	 * @uses EDD_Gateawy_Reports_Table::get_columns()
	 * @uses EDD_Gateawy_Reports_Table::get_sortable_columns()
	 * @uses EDD_Gateawy_Reports_Table::reports_data()
	 * @return void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->reports_data();

	}
}
