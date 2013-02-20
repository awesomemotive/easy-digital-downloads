<?php
/**
 * API Requests Log View Class
 *
 * @package     Easy Digital Downloads
 * @subpackage  API Requests List Table Log View Class
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EDD API Requests List Table Log View Class
 *
 * Renders the gateway errors list table
 *
 * @access      private
 * @since       1.5
 */

class EDD_API_Request_Log_Table extends WP_List_Table {


	/**
	 * Number of items per page
	 *
	 * @since       1.5
	 */

	public $per_page = 30;


	/**
	 * Get things started
	 *
	 * @access      private
	 * @since       1.5
	 * @return      void
	 */

	function __construct(){
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
			'singular'  => edd_get_label_singular(),    // Singular name of the listed records
			'plural'    => edd_get_label_plural(),    	// Plural name of the listed records
			'ajax'      => false             			// Does this table support ajax?
		) );
	}


	/**
	 * Show the search field
	 *
	 * @access      private
	 * @since       1.5
	 * @return      void
	 */

	function search_box( $text, $input_id ) {
		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array('ID' => 'search-submit') ); ?>
		</p>
		<?php
	}


	/**
	 * Output column data
	 *
	 * @access      private
	 * @since       1.5
	 * @return      string
	 */

	function column_default( $item, $column_name ) {
		switch( $column_name ){
			default:
				return $item[ $column_name ];
		}
	}


	/**
	 * Output Error message column
	 *
	 * @access      private
	 * @since       1.5
	 * @return      void
	 */

	function column_details( $item ) {
	?>
		<a href="#TB_inline?width=640&amp;inlineId=log-details-<?php echo $item['ID']; ?>" class="thickbox" title="<?php _e( 'View Request Details', 'edd' ); ?> "><?php _e( 'View Request', 'edd' ); ?></a>
		<div id="log-details-<?php echo $item['ID']; ?>" style="display:none;">
			<?php

			$request = get_post_field( 'post_excerpt', $item['ID'] );
			$error   = get_post_field( 'post_content', $item['ID'] );
			echo '<p><strong>' . __( 'API Request:', 'edd' ) . '</strong></p>';
			echo '<div>' . $request . '</div>';
			if( ! empty( $error ) ) {
				echo '<p><strong>' . __( 'Error', 'edd' ) . '</strong></p>';
				echo '<div>' . esc_html( $error ) . '</div>';
			}
			echo '<p><strong>' . __( 'API User:', 'edd' ) . '</strong></p>';
			echo '<div>' . get_post_meta( $item['ID'], '_edd_log_user', true ) . '</div>';
			echo '<p><strong>' . __( 'API Key:', 'edd' ) . '</strong></p>';
			echo '<div>' . get_post_meta( $item['ID'], '_edd_log_api_key', true ) . '</div>';
			echo '<p><strong>' . __( 'Request Date:', 'edd' ) . '</strong></p>';
			echo '<div>' . get_post_field( 'post_date', $item['ID'] ) . '</div>';
			?>
		</div>
	<?php
	}


	/**
	 * Setup the column names / IDs
	 *
	 * @access      private
	 * @since       1.5
	 * @return      array
	 */

	function get_columns() {
		$columns = array(
			'ID'         => __( 'Log ID', 'edd' ),
			'details'    => __( 'Request Details', 'edd' ),
			'ip'         => __( 'Request IP', 'edd' ),
			'date'       => __( 'Date', 'edd' )
		);

		return $columns;
	}


	/**
	 * Retrieves the search query string
	 *
	 * @access      private
	 * @since       1.5
	 * @return      mixed String if search is present, false otherwise
	 */

	function get_search() {
		return ! empty( $_GET['s'] ) ? urldecode( trim( $_GET['s'] ) ) : false;
	}


	/**
	 * Gets the meta query for the log query
	 *
	 * This is used to return log entries that match our search query
	 *
	 * @access      private
	 * @since       1.5
	 * @return      array
	 */

	function get_meta_query() {

		$meta_query = array();

		$search = $this->get_search();

		if ( $search ) {

			if ( filter_var( $search, FILTER_VALIDATE_IP ) ) {
				// This is an IP address search
				$key = '_edd_log_request_ip';
			} else if ( is_email( $search ) ) {
				// This is an email search
				$key = '_edd_log_user';
			} else {
				// Look for an API key
				$key = '_edd_log_api_key';
			}

			// Setup the meta query
			$meta_query[] = array(
				'key'     => $key,
				'value'   => $search,
				'compare' => '='
			);
		}

		return $meta_query;
	}


	/**
	 * Retrieve the current page number
	 *
	 * @access      private
	 * @since       1.5
	 * @return      int
	 */

	function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}


	/**
	 * Outputs the log views
	 *
	 * @access      private
	 * @since       1.5
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
	 * @since       1.5
	 * @return      array
	 */

	function get_logs() {
		global $edd_logs;

		$logs_data = array();
		$paged     = $this->get_paged();
		$log_query = array(
			'log_type'    => 'api_requests',
			'paged'       => $paged,
			'meta_query'  => $this->get_meta_query()
		);

		$logs = $edd_logs->get_connected_logs( $log_query );

		if ( $logs ) {
			foreach ( $logs as $log ) {

				$logs_data[] = array(
					'ID'   => $log->ID,
					'ip'   => get_post_meta( $log->ID, '_edd_log_request_ip', true ),
					'date' => $log->post_date
				);
			}
		}

		return $logs_data;
	}


	/**
	 * Setup the final data for the table
	 *
	 * @access      private
	 * @since       1.5
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
		$hidden                = array(); // No hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$current_page          = $this->get_pagenum();
		$this->items           = $this->get_logs();
		$total_items           = $edd_logs->get_log_count( 0, 'api_requests' );

		$this->set_pagination_args( array(
				'total_items'  => $total_items,
				'per_page'     => $this->per_page,
				'total_pages'  => ceil( $total_items / $this->per_page )
			)
		);
	}
}