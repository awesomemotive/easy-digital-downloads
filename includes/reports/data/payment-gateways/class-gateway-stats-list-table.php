<?php
/**
 * Gateway Stats list table.
 *
 * @package     EDD
 * @subpackage  Reports/Data/Customers
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports\Data\Payment_Gateways;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Stats as Stats;
use EDD\Reports as Reports;
use EDD\Admin\List_Table;

/**
 * Top_Five_Customers_List_Table class.
 *
 * @since 3.0
 */
class Gateway_Stats extends List_Table {

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
			'ajax'     => false,
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
	 * Render each column.
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
	 * Column names.
	 *
	 * @since 3.0
	 *
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		return array(
			'label'          => __( 'Gateway', 'easy-digital-downloads' ),
			'complete_sales' => __( 'Complete Sales', 'easy-digital-downloads' ),
			'pending_sales'  => __( 'Pending / Failed Sales', 'easy-digital-downloads' ),
			'refunded_sales' => __( 'Refunds Issued', 'easy-digital-downloads' ),
			'total_sales'    => __( 'Total Sales', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Build all the reports data
	 *
	 * @since 1.5
	 * @return array All the data for customer reports
	 */
	public function get_data() {
		$filter   = Reports\get_filter_value( 'dates' );
		$currency = Reports\get_filter_value( 'currencies' );

		$reports_data = array();
		$gateways     = edd_get_payment_gateways();

		foreach ( $gateways as $gateway_id => $gateway ) {
			$stats = new Stats();

			$complete_count = $stats->get_gateway_sales( array(
				'range'    => $filter['range'],
				'gateway'  => $gateway_id,
				'status'   => edd_get_gross_order_statuses(),
				'type'     => array( 'sale' ),
				'currency' => $currency,
			) );

			$pending_count = $stats->get_gateway_sales( array(
				'range'    => $filter['range'],
				'gateway'  => $gateway_id,
				'status'   => edd_get_incomplete_order_statuses(),
				'type'     => array( 'sale' ),
				'currency' => $currency,
			) );

			$refunded_count = $stats->get_gateway_sales( array(
				'range'    => $filter['range'],
				'gateway'  => $gateway_id,
				'status'   => array( 'complete' ),
				'type'     => array( 'refund' ),
				'currency' => $currency,
			) );

			$total_count = $stats->get_gateway_sales( array(
				'range'    => $filter['range'],
				'gateway'  => $gateway_id,
				'status'   => 'any',
				'type'     => array( 'sale' ),
				'currency' => $currency,
			) );

			$reports_data[] = array(
				'ID'             => $gateway_id,
				'label'          => '<a href="' . esc_url( edd_get_admin_url( array( 'page' => 'edd-payment-history', 'gateway' => sanitize_key( $gateway_id ) ) ) ) . '">' . esc_html( $gateway['admin_label'] ) . '</a>',
				'complete_sales' => edd_format_amount( $complete_count, false ),
				'pending_sales'  => edd_format_amount( $pending_count, false ),
				'refunded_sales' => edd_format_amount( $refunded_count, false ),
				'total_sales'    => edd_format_amount( $total_count, false ),
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
