<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class EDD_File_Downloads_Log_Table extends WP_List_Table {

	var $per_page = 30;

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
					admin_url( '/edit.php?post_type=download&page=edd-payment-history&user=' . urlencode( $item[ $column_name ] ) ) .
					 '" target="_blank">' . $item[ 'user_name' ] . '</a>';
			default:
				return $item[ $column_name ];
		}
	}


	function get_columns() {
		$columns = array(
			'ID'		=> __( 'Log ID', 'edd' ),
			'download'	=> edd_get_label_singular(),
			'user_id'  	=> __( 'User', 'edd' ),
			'payment_id'=> __( 'Payment ID', 'edd' ),
			'file'  	=> __( 'File', 'edd' ),
			'ip'  		=> __( 'IP Address', 'edd' ),
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

		$logs = $edd_logs->get_logs( null, 'file_download', $paged );

		if( $logs ) {

			foreach( $logs as $log ) {

				$user_info 	= get_post_meta( $log->ID, '_edd_log_user_info', true );
				$payment_id = get_post_meta( $log->ID, '_edd_log_payment_id', true );
				$ip 		= get_post_meta( $log->ID, '_edd_log_ip', true );
				$user_id 	= isset( $user_info['id']) ? $user_info['id'] : 0;
				$user_data 	= get_userdata( $user_id );
				$files 		= edd_get_download_files( $log->post_parent );
				$file_id 	= (int) get_post_meta( $log->ID, '_edd_log_file_id', true );
				$file_id 	= $file_id !== false ? $file_id : 0;
				$file_name 	= isset( $files[ $file_id ]['name'] ) ? $files[ $file_id ]['name'] : null;

				$logs_data[] = array(
					'ID' 		=> $log->ID,
					'download'	=> $log->post_parent,
					'payment_id'=> $payment_id,
					'user_id'	=> $user_data ? $user_data->ID : $user_info['email'],
					'user_name'	=> $user_data ? $user_data->display_name : $user_info['email'],
					'file'		=> $file_name,
					'ip'		=> $ip,
					'date'		=> $log->post_date
				);
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
		$per_page = $this->per_page;
	   
		$columns = $this->get_columns();

		$hidden = array(); // no hidden columns

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		 
		$current_page = $this->get_pagenum();
	
		$this->items = $this->logs_data();

		$total_items = $edd_logs->get_log_count( null, 'file_download' );

		$this->set_pagination_args( array(
				'total_items' => $total_items,                  	// WE have to calculate the total number of items
				'per_page'    => $per_page,                     	// WE have to determine how many items to show on a page
				'total_pages' => ceil( $total_items / $per_page )   // WE have to calculate the total number of pages
			)
		);
	}
   
}