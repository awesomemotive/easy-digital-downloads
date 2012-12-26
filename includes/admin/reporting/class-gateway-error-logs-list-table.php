<?php
/**
 * Gateway Error Log View Class
 *
 * @package     Easy Digital Downloads
 * @subpackage  Gateway Errors List Table Log View Class
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
 * EDD Gateway Errors List Table Log View Class
 *
 * Renders the gateway errors list table
 *
 * @access      private
 * @since       1.4
 */

class EDD_Gateway_Error_Log_Table extends WP_List_Table {


	/**
	 * Number of items per page
	 *
	 * @since       1.4
	 */

	public $per_page = 30;


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
			case 'error' :
				return get_the_title( $item['ID'] ) ? get_the_title( $item['ID'] ) : __( 'Payment Error', 'edd' );
			case 'gateway' :
				return edd_get_payment_gateway( $item['payment_id'] );
			case 'buyer' :
				$user = ! empty( $item['user_id'] ) ? $item['user_id'] : $item['buyer'];
				return '<a href="' . admin_url( 'edit.php?post_type=download&page=edd-payment-history&user=' ) . urlencode( $user ) . '">' . $item['buyer'] . '</a>';
			default:
				return $item[ $column_name ];
		}
	}


	/**
	 * Output Error message column
	 *
	 * @access      private
	 * @since       1.4
	 * @return      void
	 */

	function column_message( $item ) {
	?>
		<a href="#TB_inline?width=640&amp;inlineId=log-message-<?php echo $item['ID']; ?>" class="thickbox" title="<?php _e( 'View Log Message', 'edd' ); ?> "><?php _e( 'View Log Message', 'edd' ); ?></a>
		<div id="log-message-<?php echo $item['ID']; ?>" style="display:none;">
			<?php

			$log_message = get_post_field( 'post_content', $item['ID'] );
			$serialized  = strpos( $log_message, '{"' );

			// Check to see if the log message contains serialized information
			if ( $serialized !== false ) {
				$length  = strlen( $log_message ) - $serialized;
				$intro   = substr( $log_message, 0, - $length );
				$data    = substr( $log_message, $serialized, strlen( $log_message ) - 1 );

				echo wpautop( $intro );
				echo wpautop( __( '<strong>Log data:</strong>') );
				echo '<div style="word-wrap: break-word;">' . wpautop( $data ) . '</div>';
			} else {
				// No serialized data found
				echo wpautop( $log_message );
			}
			?>
		</div>
	<?php
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
			'ID'         => __( 'Log ID', 'edd' ),
			'payment_id' => __( 'Payment ID', 'edd' ),
			'error'      => __( 'Error', 'edd' ),
			'message'    => __( 'Error Message', 'edd' ),
			'gateway'    => __( 'Gateway', 'edd' ),
			'buyer'      => __( 'Buyer', 'edd' ),
			'date'       => __( 'Date', 'edd' )
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
	 * Outputs the log views
	 *
	 * @access      private
	 * @since       1.4
	 * @return      void
	 */

	function bulk_actions() {
		// These aren't really bulk actions but this outputs the markup in the right place
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
		$paged     = $this->get_paged();
		$log_query = array(
			'log_type'    => 'gateway_error',
			'paged'       => $paged
		);

		$logs = $edd_logs->get_connected_logs( $log_query );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				$user_info  = edd_get_payment_meta_user_info( $log->post_parent );
				$user_id 	= isset( $user_info['id']) ? $user_info['id'] : 0;
				$user_data 	= get_userdata( $user_id );

				$logs_data[] = array(
					'ID'         => $log->ID,
					'payment_id' => $log->post_parent,
					'error'      => 'error',
					'gateway'    => 'gateway',
					'user_id'    => $user_id,
					'buyer'	     => $user_data ? $user_data->display_name : $user_info['email'],
					'date'	     => $log->post_date
				);
			}
		}

		return $logs_data;
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
		global $edd_logs;

		$columns               = $this->get_columns();
		$hidden                = array(); // no hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$current_page          = $this->get_pagenum();
		$this->items           = $this->get_logs();
		$total_items           = $edd_logs->get_log_count( 0, 'gateway_error' );

		$this->set_pagination_args( array(
				'total_items'  => $total_items,
				'per_page'     => $this->per_page,
				'total_pages'  => ceil( $total_items / $this->per_page )
			)
		);
	}
}