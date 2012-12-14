<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class EDD_Sales_Log_Table extends WP_List_Table {

	function __construct(){
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
			'singular'  => edd_get_label_singular(),    // singular name of the listed records
			'plural'    => edd_get_label_plural(),    	// plural name of the listed records
			'ajax'      => false             			// does this table support ajax?
		) );

	}


	function column_default( $item, $column_name ) {
		switch( $column_name ){
			case 'download' :
				return '<a href="' .
				admin_url( '/post.php?post=' . $item[ $column_name ] . '&action=edit' ) .
				 '" target="_blank">' . get_the_title( $item[ $column_name ] ) . '</a>';
			case 'user_id' :
				return '<a href="' .
					admin_url( 'edit.php?post_type=download&page=edd-payment-history&user=' . urlencode( $item['user_id'] ) ) .
					 '"" target="_blank">' . $item[ 'user_name' ] . '</a>';
			default:
				return $item[ $column_name ];
		}
	}


	function get_columns() {
		$columns = array(
			'ID'		=> __( 'Log ID', 'edd' ),
			'payment_id'=> __( 'Payment ID', 'edd' ),
			'user_id'  	=> __( 'User', 'edd' ),
			'date'  	=> __( 'Date', 'edd' )
		);
		return $columns;
	}

	function bulk_actions() {
		// these aren't really bulk actions but this outputs the markup in the right place
		edd_log_views();
	}

	function logs_data() {

		global $edd_logs;

		$logs_data = array();

		$paged = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;

		$user  = isset( $_GET['user'] ) ? absint( $_GET['user'] ) : false;

		$log_query = array(
			'post_parent' => null,
			'log_type'    => 'sale',
			'paged'       => $paged
		);

		$logs = $edd_logs->get_connected_logs( $log_query );

		if( $logs ) {

			foreach( $logs as $log ) {

				$payment_id = get_post_meta( $log->ID, '_edd_log_payment_id', true );
				$user_info = edd_get_payment_meta_user_info( $payment_id );
				if( is_array( $user_info ) ) {
					$logs_data[] = array(
						'ID' 		=> $log->ID,
						'payment_id'=> $payment_id,
						'user_id'	=> $user_info['id'],
						'user_name'	=> $user_info['first_name'] . ' ' . $user_info['last_name'],
						'date'		=> get_post_field( 'post_date', $payment_id )
					);
				}
			}
		}

		return $logs_data;
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

		global $edd_logs;

		/**
		 * First, lets decide how many records per page to show
		 */
		$per_page = 30;

		$columns = $this->get_columns();

		$hidden = array(); // no hidden columns

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();

		$this->items = $this->logs_data();

		$total_items = $edd_logs->get_log_count( null, 'sale' );

		$this->set_pagination_args( array(
				'total_items' => $total_items,                  	// WE have to calculate the total number of items
				'per_page'    => $per_page,                     	// WE have to determine how many items to show on a page
				'total_pages' => ceil( $total_items / $per_page )   // WE have to calculate the total number of pages
			)
		);
	}

}