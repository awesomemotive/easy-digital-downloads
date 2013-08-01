<?php
/**
 * File Downloads Log View Class
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EDD_File_Downloads_Log_Table Class
 *
 * Renders the file downloads log view
 *
 * @since 1.4
 */
class EDD_File_Downloads_Log_Table extends WP_List_Table {
	/**
	 * Number of items per page
	 *
	 * @var int
	 * @since 1.4
	 */
	public $per_page = 15;

	/**
	 * Are we searching for files?
	 *
	 * @var bool
	 * @since 1.4
	 */
	public $file_search = false;

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
	 * Show the search field
	 *
	 * @since 1.4
	 * @access public
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
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
		switch ( $column_name ) {
			case 'download' :
				return '<a href="' . add_query_arg( 'download', $item[ $column_name ] ) . '" >' . get_the_title( $item[ $column_name ] ) . '</a>';
			case 'user_id' :
				return '<a href="' . add_query_arg( 'user', $item[ $column_name ] ) . '">' . $item[ 'user_name' ] . '</a>';
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
			'download'	=> edd_get_label_singular(),
			'user_id'  	=> __( 'User', 'edd' ),
			'payment_id'=> __( 'Payment ID', 'edd' ),
			'file'  	=> __( 'File', 'edd' ),
			'ip'  		=> __( 'IP Address', 'edd' ),
			'date'  	=> __( 'Date', 'edd' )
		);
		return $columns;
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

		if ( $user ) {
			// Show only logs from a specific user
			$meta_query[] = array(
				'key'   => '_edd_log_user_id',
				'value' => $user
			);
		}

		$search = $this->get_search();

		if ( $search ) {
			if ( filter_var( $search, FILTER_VALIDATE_IP ) ) {
				// This is an IP address search
				$key     = '_edd_log_ip';
				$compare = '=';
			} else if ( is_email( $search ) ) {
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
						} else {
							// No users were found so let's look for file names instead
							$this->file_search = true;
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
	 * Retrieve the current page number
	 *
	 * @access public
	 * @since 1.4
	 * @return int Current page number
	 */
	function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Outputs the log views
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function bulk_actions() {
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
	function get_logs() {
		global $edd_logs;

		// Prevent the queries from getting cached. Without this there are occasional memory issues for some installs
		wp_suspend_cache_addition( true );

		$logs_data = array();
		$paged     = $this->get_paged();
		$download  = empty( $_GET['s'] ) ? $this->get_filtered_download() : null;
		$log_query = array(
			'post_parent'    => $download,
			'log_type'       => 'file_download',
			'paged'          => $paged,
			'meta_query'     => $this->get_meta_query(),
			'posts_per_page' => $this->per_page
		);

		$logs = $edd_logs->get_connected_logs( $log_query );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				$user_info 	 = get_post_meta( $log->ID, '_edd_log_user_info', true );
				$payment_id  = get_post_meta( $log->ID, '_edd_log_payment_id', true );
				$ip 		 = get_post_meta( $log->ID, '_edd_log_ip', true );
				$user_id 	 = isset( $user_info['id']) ? $user_info['id'] : 0;
				$user_data 	 = get_userdata( $user_id );
				$files 		 = edd_get_download_files( $log->post_parent );
				$file_id 	 = (int) get_post_meta( $log->ID, '_edd_log_file_id', true );
				$file_id 	 = $file_id !== false ? $file_id : 0;
				$file_name 	 = isset( $files[ $file_id ]['name'] ) ? $files[ $file_id ]['name'] : null;

				if ( ( $this->file_search && strpos( strtolower( $file_name ), strtolower( $this->get_search() ) ) !== false ) || ! $this->file_search ) {
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
		}

		return $logs_data;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since 1.5
	 * @global object $edd_logs EDD Logs Object
	 * @uses EDD_File_Downloads_Log_Table::get_columns()
	 * @uses WP_List_Table::get_sortable_columns()
	 * @uses EDD_File_Downloads_Log_Table::get_pagenum()
	 * @uses EDD_File_Downloads_Log_Table::get_logs()
	 * @uses EDD_File_Downloads_Log_Table::get_log_count()
	 * @uses WP_List_Table::set_pagination_args()
	 * @return void
	 */
	function prepare_items() {
		global $edd_logs;

		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$current_page          = $this->get_pagenum();
		$this->items           = $this->get_logs();
		$total_items           = $edd_logs->get_log_count( $this->get_filtered_download(), 'file_download', $this->get_meta_query() );
		$this->set_pagination_args( array(
				'total_items'  => $total_items,
				'per_page'     => $this->per_page,
				'total_pages'  => ceil( $total_items / $this->per_page )
			)
		);
	}
}