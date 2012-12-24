<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class EDD_Customer_Reports_Table extends WP_List_Table {


	/**
	 * Number of results to show per page
	 *
	 * @since       1.4
	 */

	public $per_page = 30;


	function __construct(){
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular'  => __( 'Customer', 'edd' ),     // singular name of the listed records
			'plural'    => __( 'Customers', 'edd' ),    // plural name of the listed records
			'ajax'      => false             			// does this table support ajax?
		) );

	}


	function column_default( $item, $column_name ) {
		switch( $column_name ){
			case 'name' :
				return '<a href="' . esc_url(
						admin_url( '/edit.php?post_type=download&page=edd-payment-history&user=' . urlencode( $item['email'] ) )
					) . '">' . esc_html( $item[ $column_name ] ) . '</a>';
			case 'amount_spent' :
				return edd_currency_filter( edd_format_amount( $item[ $column_name ] ) );
			default:
				return $item[ $column_name ];
		}
	}


	function get_columns(){
		$columns = array(
			'name'     		=> __( 'Name', 'edd' ),
			'email'     	=> __( 'Email', 'edd' ),
			'num_purchases' => __( 'Number of Purchases', 'edd' ),
			'amount_spent'  => __( 'Total Amount Spent', 'edd' )
		);
		return $columns;
	}

	function bulk_actions() {
		// these aren't really bulk actions but this outputs the markup in the right place
		edd_report_views();
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


	function get_total_customers() {
		global $wpdb;

		$count = $wpdb->get_col( "SELECT COUNT(DISTINCT meta_value) FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_user_email'" );

		return $count[0];
	}


	function reports_data() {
		global $wpdb;

		$reports_data = array();
		$paged        = $this->get_paged();
		$offset       = $this->per_page * ( $paged - 1 );
		$customers    = $wpdb->get_col( "SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_user_email' ORDER BY meta_id DESC LIMIT $this->per_page OFFSET $offset" );

		if( $customers ) {
			foreach( $customers as $customer_email ) {

				$wp_user = get_user_by( 'email', $customer_email );

				$user_id = $wp_user ? $wp_user->ID : 0;

				$reports_data[] = array(
					'ID' 			=> $user_id,
					'name' 			=> $wp_user ? $wp_user->display_name : __( 'Guest', 'edd' ),
					'email' 		=> $customer_email,
					'num_purchases'	=> edd_count_purchases_of_customer( $customer_email ),
					'amount_spent'	=> edd_purchase_total_of_user( $customer_email )
				);
			}
		}
		return $reports_data;
	}


	/** ************************************************************************
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 **************************************************************************/
	function prepare_items() {

		$columns = $this->get_columns();

		$hidden = array(); // no hidden columns

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();

		$total_items = $this->get_total_customers();

		//$data = array_slice( $data,( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->items = $this->reports_data();

		$this->set_pagination_args( array(
			'total_items' => $total_items,                  	// WE have to calculate the total number of items
			'per_page'    => $this->per_page,                     	// WE have to determine how many items to show on a page
			'total_pages' => ceil( $total_items / $this->per_page )   // WE have to calculate the total number of pages
		) );
	}

}