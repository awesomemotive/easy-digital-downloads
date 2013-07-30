<?php
/**
 * Sales Log View Class
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EDD_Sales_Log_Table Class
 *
 * Renders the sales log list table
 *
 * @since 1.4
 */
class EDD_Sales_Log_Table extends WP_List_Table {
	/**
	 * Number of results to show per page
	 *
	 * @since 1.4
	 * @var int
	 */
	public $per_page = 30;

	/**
	 * Get things started
	 *
	 * @access public
	 * @since 1.4
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

		add_action( 'edd_log_view_actions', array( $this, 'downloads_filter' ) );
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since 1.4
	 *
	 * @param array $item Contains all the data of the discount code
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ){
			case 'download' :
				return '<a href="' . add_query_arg( 'download', $item[ $column_name ] ) . '" >' . get_the_title( $item[ $column_name ] ) . '</a>';

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
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since 1.4
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
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
	 * @access public
	 * @since 1.4
	 * @return int Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Retrieves the user we are filtering logs by, if any
	 *
	 * @access public
	 * @since 1.4
	 * @return mixed int If User ID, string If Email/Login
	 */
	public function get_filtered_user() {
		return isset( $_GET['user'] ) ? absint( $_GET['user'] ) : false;
	}

	/**
	 * Retrieves the ID of the download we're filtering logs by
	 *
	 * @access public
	 * @since 1.4
	 * @return int Download ID
	 */
	public function get_filtered_download() {
		return ! empty( $_GET['download'] ) ? absint( $_GET['download'] ) : false;
	}

	/**
	 * Retrieves the search query string
	 *
	 * @access public
	 * @since 1.4
	 * @return mixed string If search is present, false otherwise
	 */
	public function get_search() {
		return ! empty( $_GET['s'] ) ? urldecode( trim( $_GET['s'] ) ) : false;
	}

	/**
	 * Gets the meta query for the log query
	 *
	 * This is used to return log entries that match our search query, user query, or download query
	 *
	 * @access public
	 * @since 1.4
	 * @return array $meta_query
	 */
	public function get_meta_query() {
		$user = $this->get_filtered_user();

		$meta_query = array();

		if( $user ) {
			// Show only logs from a specific user
			$meta_query[] = array(
				'key'   => '_edd_log_user_id',
				'value' => $user
			);
		}

		$search = $this->get_search();
		if ( $search ) {
			if ( is_email( $search ) ) {
				// This is an email search. We use this to ensure it works for guest users and logged-in users
				$key     = '_edd_log_user_info';
				$compare = 'LIKE';
			} else {
				// Look for a user
				$key = '_edd_log_user_id';
				$compare = 'LIKE';

				if ( ! is_numeric( $search ) ) {
					// Searching for user by username
					$user = get_user_by( 'login', $search );

					if ( $user ) {
						// Found one, set meta value to user's ID
						$search = $user->ID;
					} else {
						// No user found so let's do a real search query
						$users = new WP_User_Query( array(
							'search'         => $search,
							'search_columns' => array( 'user_url', 'user_nicename' ),
							'number'         => 1,
							'fields'         => 'ids'
						) );

						$found_user = $users->get_results();

						if ( $found_user ) {
							$search = $found_user[0];
						}
					}
				}
			}

			if ( ! $this->file_search ) {
				// Meta query only works for non file name searche
				$meta_query[] = array(
					'key'     => $key,
					'value'   => $search,
					'compare' => $compare
				);

			}
		}

		return $meta_query;
	}

	/**
	 * Outputs the log views
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	function bulk_actions() {
		// These aren't really bulk actions but this outputs the markup in the right place
		edd_log_views();
	}

	/**
	 * Sets up the downloads filter
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function downloads_filter() {
		$downloads = get_posts( array(
			'post_type'      => 'download',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'fields'         => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false
		) );

		if ( $downloads ) {
			echo '<select name="download" id="edd-log-download-filter">';
				echo '<option value="0">' . __( 'All', 'edd' ) . '</option>';
				foreach ( $downloads as $download ) {
					echo '<option value="' . $download . '"' . selected( $download, $this->get_filtered_download() ) . '>' . esc_html( get_the_title( $download ) ) . '</option>';
				}
			echo '</select>';
		}
	}

	/**
	 * Gets the log entries for the current view
	 *
	 * @access public
	 * @since 1.4
	 * @global object $edd_logs EDD Logs Object
	 * @return array $logs_data Array of all the Log entires
	 */
	public function get_logs() {
		global $edd_logs;

		// Prevent the queries from getting cached. Without this there are occasional memory issues for some installs
		wp_suspend_cache_addition( true );

		$logs_data = array();
		$paged     = $this->get_paged();
		$download  = empty( $_GET['s'] ) ? $this->get_filtered_download() : null;
		$user      = $this-> get_filtered_user();

		$log_query = array(
			'post_parent' => $download,
			'log_type'    => 'sale',
			'paged'       => $paged,
			'meta_query'  => $this->get_meta_query()
		);

		$logs = $edd_logs->get_connected_logs( $log_query );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				$payment_id = get_post_meta( $log->ID, '_edd_log_payment_id', true );

				// Make sure this payment hasn't been deleted
				if ( get_post( $payment_id ) ) :
					$user_info  = edd_get_payment_meta_user_info( $payment_id );
					$cart_items = edd_get_payment_meta_cart_details( $payment_id );
					$amount     = 0;
					if ( is_array( $cart_items ) && is_array( $user_info ) ) {
						foreach ( $cart_items as $item ) {
							$price_override = isset( $item['price'] ) ? $item['price'] : null;
							if ( isset( $item['id'] ) && $item['id'] == $log->post_parent ) {
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

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since 1.4
	 * @global object $edd_logs EDD Logs Object
	 * @uses EDD_Sales_Log_Table::get_columns()
	 * @uses WP_List_Table::get_sortable_columns()
	 * @uses EDD_Sales_Log_Table::get_pagenum()
	 * @uses EDD_Sales_Log_Table::get_logs()
	 * @uses EDD_Sales_Log_Table::get_log_count()
	 * @return void
	 */
	public function prepare_items() {
		global $edd_logs;

		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$current_page          = $this->get_pagenum();
		$this->items           = $this->get_logs();
		$total_items           = $edd_logs->get_log_count( $this->get_filtered_download(), 'sale', $this->get_meta_query() );

		$this->set_pagination_args( array(
				'total_items'  => $total_items,
				'per_page'     => $this->per_page,
				'total_pages'  => ceil( $total_items / $this->per_page )
			)
		);
	}
}