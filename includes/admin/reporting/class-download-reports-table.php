<?php
/**
 * Download Reports Table Class
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2013, Pippin Williamson
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
 * EDD_Download_Reports_Table Class
 *
 * Renders the Download Reports table
 *
 * @since 1.5
 */
class EDD_Download_Reports_Table extends WP_List_Table {

	/**
	 * @var int Number of items per page
	 * @since 1.5
	 */
	public $per_page = 30;

	/**
	 * @var object Query results
	 * @since 1.5.2
	 */
	private $products;

	/**
	 * Get things started
	 *
	 * @access public
	 * @since 1.5
	 * @see WP_List_Table::__construct()
	 * @return void
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular'  => edd_get_label_singular(),    // Singular name of the listed records
			'plural'    => edd_get_label_plural(),    	// Plural name of the listed records
			'ajax'      => false             			// Does this table support ajax?
		) );

		add_action( 'edd_report_view_actions', array( $this, 'category_filter' ) );

		$this->query();

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
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since 1.5
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
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
	 * Retrieve the table's sortable columns
	 *
	 * @access public
	 * @since 1.4
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'title' 	=> array( 'title', true ),
			'sales' 	=> array( 'sales', false ),
			'earnings' 	=> array( 'earnings', false ),
		);
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
	 * Retrieve the category being viewed
	 *
	 * @access public
	 * @since 1.5.2
	 * @return int Category ID
	 */
	public function get_category() {
		return isset( $_GET['category'] ) ? absint( $_GET['category'] ) : 0;
	}


	/**
	 * Retrieve the total number of downloads
	 *
	 * @access public
	 * @since 1.5
	 * @return int $total Total number of downloads
	 */
	public function get_total_downloads() {
		return $this->products->post_count;
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
	 * Attaches the category filter to the log views
	 *
	 * @access public
	 * @since 1.5.2
	 * @return void
	 */
	public function category_filter() {
		$current_view = isset( $_GET[ 'view' ] ) ? $_GET[ 'view' ] : 'earnings';
		echo EDD()->html->category_dropdown( 'category', $this->get_category() );
	}


	/**
	 * Performs the products query
	 *
	 * @access public
	 * @since 1.5.2
	 * @return void
	 */
	public function query() {

		$orderby  = isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'title';
		$order    = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
		$category = $this->get_category();

		$args = array(
			'post_type' 	=> 'download',
			'post_status'	=> 'publish',
			'order'			=> $order,
			'fields'        => 'ids',
			'posts_per_page'=> $this->per_page,
			'paged'         => $this->get_paged()
		);

		if( ! empty( $category ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'download_category',
					'terms'    => $category
				)
			);
		}

		switch ( $orderby ) :
			case 'title' :
				$args['orderby'] = 'title';
				break;

			case 'sales' :
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = '_edd_download_sales';
				break;

			case 'earnings' :
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = '_edd_download_earnings';
				break;
		endswitch;

		$this->products = new WP_Query( $args );

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

		$downloads = $this->products->posts;

		if ( $downloads ) {
			foreach ( $downloads as $download ) {
				$reports_data[] = array(
					'ID' 				=> $download,
					'title' 			=> get_the_title( $download ),
					'sales' 			=> edd_get_download_sales_stats( $download ),
					'earnings'			=> edd_get_download_earnings_stats( $download ),
					'average_sales'   	=> edd_get_average_monthly_download_sales( $download ),
					'average_earnings'  => edd_get_average_monthly_download_earnings( $download )
				);
			}
		}

		return $reports_data;
	}


	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since 1.5
	 * @uses EDD_Download_Reports_Table::get_columns()
	 * @uses EDD_Download_Reports_Table::get_sortable_columns()
	 * @uses EDD_Download_Reports_Table::reports_data()
	 * @uses EDD_Download_Reports_Table::get_pagenum()
	 * @uses EDD_Download_Reports_Table::get_total_downloads()
	 * @return void
	 */
	public function prepare_items() {
		$columns = $this->get_columns();

		$hidden = array(); // No hidden columns

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