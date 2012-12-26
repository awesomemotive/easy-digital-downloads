<?php
/**
 * Download Reports Table Class
 *
 * @package     Easy Digital Downloads
 * @subpackage  Download Reports List Table Class
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EDD Download Reports Table Class
 *
 * Renders the Download Reports table
 *
 * @access      private
 */

class EDD_Download_Reports_Table extends WP_List_Table {
	public $per_page = 30;


	function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular'  => edd_get_label_singular(),    // singular name of the listed records
			'plural'    => edd_get_label_plural(),    	// plural name of the listed records
			'ajax'      => false             			// does this table support ajax?
		) );

	}


	/**
	 * Output column data
	 *
	 * @access      private
	 * @since       1.3
	 * @return      string
	 */

	function column_default( $item, $column_name ) {
		switch( $column_name ){
			case 'earnings' :
				return edd_currency_filter( edd_format_amount( $item[ $column_name ] ) );
			case 'average_sales' :
				return round( $item[ $column_name ] );
			case 'average_earnings' :
				return edd_currency_filter( edd_format_amount( $item[ $column_name ] ) );
			default:
				return $item[ $column_name ];
		}
	}


	/**
	 * Get the column IDs and names
	 *
	 * @access      private
	 * @since       1.3
	 * @return      array
	 */

	function get_columns() {
		$columns = array(
			'title'     		=> edd_get_label_singular(),
			'sales'  			=> __( 'Sales', 'edd' ),
			'earnings'  		=> __( 'Earnings', 'edd' ),
			'average_sales'  	=> __( 'Monthly Average Sales', 'edd' ),
			'average_earnings'  => __( 'Monthly Average Earnings', 'edd' )
		);
		return $columns;
	}


	/**
	 * Define the sortable columns
	 *
	 * @access      private
	 * @since       1.3
	 * @return      array
	 */

	function get_sortable_columns() {
		return array(
			'title' 	=> array( 'title', true ),
			'sales' 	=> array( 'sales', false ),
			'earnings' 	=> array( 'earnings', false ),
		);
	}


	/**
	 * Retrieve the current page number
	 *
	 * @access      private
	 * @since       1.4
	 * @return      int
	 */

	function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}


	/**
	 * Retrieve the totoal number of downloads
	 *
	 * @access      private
	 * @since       1.4
	 * @return      int
	 */

	function get_total_downloads() {
		$counts = wp_count_posts( 'download' );
		$total  = 0;
		foreach ( $counts as $count )
			$total += $count;
		return $total;
	}


	/**
	 * Show reporting views
	 *
	 * @access      private
	 * @since       1.3
	 * @return      void
	 */

	function bulk_actions() {
		// These aren't really bulk actions but this outputs the markup in the right place
		edd_report_views();
	}


	/**
	 * Retrieve all report data for Downloads
	 *
	 * @access      private
	 * @since       1.3
	 * @return      array
	 */

	function reports_data() {
		$reports_data = array();

		$orderby = isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'title';
		$order   = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';

		$report_args = array(
			'post_type' 	=> 'download',
			'post_status'	=> 'publish',
			'order'			=> $order,
			'posts_per_page'=> $this->per_page,
			'paged'         => $this->get_paged()
		);

		switch( $orderby ) :
			case 'title' :
				$report_args['orderby'] = 'title';
				break;

			case 'sales' :
				$report_args['orderby'] = 'meta_value_num';
				$report_args['meta_key'] = '_edd_download_sales';
				break;

			case 'earnings' :
				$report_args['orderby'] = 'meta_value_num';
				$report_args['meta_key'] = '_edd_download_earnings';
				break;
		endswitch;

		$downloads = get_posts( $report_args );
		if ( $downloads ) {
			foreach ( $downloads as $download ) {
				$reports_data[] = array(
					'ID' 				=> $download->ID,
					'title' 			=> get_the_title( $download->ID ),
					'sales' 			=> edd_get_download_sales_stats( $download->ID ),
					'earnings'			=> edd_get_download_earnings_stats( $download->ID ),
					'average_sales'   	=> edd_get_average_monthly_download_sales( $download->ID ),
					'average_earnings'  => edd_get_average_monthly_download_earnings( $download->ID )
				);
			}
		}

		return $reports_data;
	}


	/**
	 * Setup the final data for the table
	 *
	 * @access      private
	 * @since       1.4
	 * @uses        $this->_column_headers
	 * @uses        $this->items
	 * @uses        $this->get_columns()
	 * @uses        $this->get_sortable_columns()
	 * @uses        $this->get_pagenum()
	 * @uses        $this->set_pagination_args()
	 * @return      array
	 */

	function prepare_items() {
		$columns = $this->get_columns();

		$hidden = array(); // no hidden columns

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$data = $this->reports_data();

		$current_page = $this->get_pagenum();

		$total_items = $this->get_total_downloads();

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page )
			)
		);
	}
}