<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


/**
 * Sets up the WP list table for the Sales Log View
 *
 * @since       1.4
 */

class EDD_Sales_Log_Table extends WP_List_Table {


	/**
	 * Number of results to show per page
	 *
	 * @since       1.4
	 */

	var $per_page = 30;


	/**
	 * Get things started
	 *
	 * @access      private
	 * @since       1.4
	 * @return      void
	 */

	function __construct(){

		global $status, $page;

		//Set parent defaults
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
	 * @since       1.4
	 * @return      string
	 */

	function column_default( $item, $column_name ) {
		switch( $column_name ){
			case 'download' :
				return '<a href="' .
				admin_url( '/post.php?post=' . $item[ $column_name ] . '&action=edit' ) .
				 '" target="_blank">' . get_the_title( $item[ $column_name ] ) . '</a>';

			case 'user_id' :
				return '<a href="' .
					admin_url( 'edit.php?post_type=download&page=edd-payment-history&user=' . urlencode( $item['user_id'] ) ) .
					 '">' . $item[ 'user_name' ] . '</a>';

			case 'amount' :
				return edd_currency_filter( edd_format_amount( $item['amount'] ) );

			default:
				return $item[ $column_name ];
		}
	}


	/**
	 * Setup the column names / IDs
	 *
	 * @access      private
	 * @since       1.4
	 * @return      array
	 */

	function get_columns() {
		$columns = array(
			'ID'		=> __( 'Log ID', 'edd' ),
			'user_id'  	=> __( 'User', 'edd' ),
			'download'  => __( 'Download', 'edd' ),
			'amount'    => __( 'Item Amount', 'edd' ),
			'payment_id'=> __( 'Payment ID', 'edd' ),
			'date'  	=> __( 'Date', 'edd' )
		);
		return $columns;
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
	 * Outputs the log filters filter
	 *
	 * @access      private
	 * @since       1.4
	 * @return      void
	 */

	function bulk_actions() {
		// these aren't really bulk actions but this outputs the markup in the right place
		edd_log_views();
	}


	/**
	 * Gets the log entries for the current view
	 *
	 * @access      private
	 * @since       1.4
	 * @return      array
	 */

	function get_logs() {

		global $edd_logs;

		$logs_data = array();

		$paged = $this->get_paged();

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

				// make sure this payment hasn't been deleted
				if( get_post( $payment_id ) ) :

					$user_info  = edd_get_payment_meta_user_info( $payment_id );
					$cart_items = edd_get_payment_meta_cart_details( $payment_id );
					$amount     = 0;
					//print_r( $cart_items ); exit;
					if( is_array( $cart_items ) && is_array( $user_info ) ) {

						foreach( $cart_items as $item ) {
							$price_override = isset( $item['price'] ) ? $item['price'] : null;
							if( isset( $item['id'] ) && $item['id'] == $log->post_parent ) {
								$amount = edd_get_download_final_price( $item['id'], $user_info, $price_override );
							}
						}

						$logs_data[] = array(
							'ID' 		=> $log->ID,
							'payment_id'=> $payment_id,
							'download'  => $log->post_parent,
							'amount'    => $amount,
							'user_id'	=> $user_info['id'],
							'user_name'	=> $user_info['first_name'] . ' ' . $user_info['last_name'],
							'date'		=> get_post_field( 'post_date', $payment_id )
						);
					}
				endif;
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

		$columns               = $this->get_columns();
		$hidden                = array(); // no hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$current_page          = $this->get_pagenum();
		$this->items           = $this->get_logs();
		$total_items           = $edd_logs->get_log_count( null, 'sale' );

		$this->set_pagination_args( array(
				'total_items'  => $total_items,
				'per_page'     => $this->per_page,
				'total_pages'  => ceil( $total_items / $this->per_page )
			)
		);
	}

}