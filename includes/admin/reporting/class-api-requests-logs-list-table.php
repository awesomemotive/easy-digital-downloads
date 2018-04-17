<?php
/**
 * API Requests Log View Class
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EDD_API_Request_Log_Table List Table Class
 *
 * @since 1.5
 * @since 3.0 Updated to use the custom tables and new query classes.
 */
class EDD_API_Request_Log_Table extends WP_List_Table {
	/**
	 * Number of items per page
	 *
	 * @var int
	 * @since 1.5
	 */
	public $per_page = 30;

	/**
	 * Get things started
	 *
	 * @since 1.5
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => edd_get_label_singular(),
			'plural'   => edd_get_label_plural(),
			'ajax'     => false,
		) );
	}

	/**
	 * Show the search field
	 *
	 * @since 1.5
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
	 * Retrieve the table columns
	 *
	 * @since 1.5
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'ID'      => __( 'Log ID', 'easy-digital-downloads' ),
			'details' => __( 'Request Details', 'easy-digital-downloads' ),
			'version' => __( 'API Version', 'easy-digital-downloads' ),
			'ip'      => __( 'Request IP', 'easy-digital-downloads' ),
			'speed'   => __( 'Request Speed', 'easy-digital-downloads' ),
			'date'    => __( 'Date', 'easy-digital-downloads' ),
		);

		return $columns;
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 2.5
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'ID';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @since 1.5
	 *
	 * @param array $item Contains all the data of the api request
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Output Error Message column
	 *
	 * @since 1.5
	 * @param array $item Contains all the data of the log
	 * @return void
	 */
	public function column_details( $item ) {
	?>
		<a href="#TB_inline?width=640&amp;inlineId=log-details-<?php echo $item['ID']; ?>" class="thickbox"><?php _e( 'View Request', 'easy-digital-downloads' ); ?></a>
		<div id="log-details-<?php echo $item['ID']; ?>" style="display:none;">
			<?php

			$request = $item['request'];
			$error   = $item['error'];
			echo '<p><strong>' . __( 'API Request:', 'easy-digital-downloads' ) . '</strong></p>';
			echo '<div>' . $request . '</div>';
			if ( ! empty( $error ) ) {
				echo '<p><strong>' . __( 'Error', 'easy-digital-downloads' ) . '</strong></p>';
				echo '<div>' . esc_html( $error ) . '</div>';
			}
			echo '<p><strong>' . __( 'API User:', 'easy-digital-downloads' ) . '</strong></p>';
			echo '<div>' . $item['user_id'] . '</div>';
			echo '<p><strong>' . __( 'API Key:', 'easy-digital-downloads' ) . '</strong></p>';
			echo '<div>' . $item['api_key'] . '</div>';
			echo '<p><strong>' . __( 'Request Date:', 'easy-digital-downloads' ) . '</strong></p>';
			echo '<div>' . $item['date'] . '</div>';
			?>
		</div>
	<?php
	}

	/**
	 * Retrieves the search query string
	 *
	 * @since 1.5
	 * @return string|false String if search is present, false otherwise
	 */
	public function get_search() {
		return ! empty( $_GET['s'] ) ? urldecode( trim( $_GET['s'] ) ) : false;
	}

	/**
	 * Gets the meta query for the log query
	 *
	 * This is used to return log entries that match our search query
	 *
	 * @since 1.5
	 * @return array $meta_query
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
				$userdata = get_user_by( 'email', $search );

				if( $userdata ) {
					$search = $userdata->ID;
				}

				$key = '_edd_log_user';
			} elseif( strlen( $search ) == 32 ) {
				// Look for an API key
				$key = '_edd_log_key';
			} elseif( stristr( $search, 'token:' ) ) {
				// Look for an API token
				$search = str_ireplace( 'token:', '', $search );
				$key = '_edd_log_token';
			} else {
				// This is (probably) a user ID search
				$userdata = get_userdata( $search );

				if( $userdata ) {
					$search = $userdata->ID;
				}

				$key = '_edd_log_user';
			}

			// Setup the meta query
			$meta_query[] = array(
				'key'     => $key,
				'value'   => $search,
				'compare' => '=',
			);
		}

		return $meta_query;
	}

	/**
	 * Retrieve the current page number
	 *
	 * @since 1.5
	 * @return int Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Outputs the log views
	 *
	 * @since 1.5
	 * @return void
	 */
	function bulk_actions( $which = '' ) {
		// These aren't really bulk actions but this outputs the markup in the right place
		edd_log_views();
	}

	/**
	 * Gets the log entries for the current view
	 *
	 * @since 1.5
	 * @global object $edd_logs EDD Logs Object
	 * @return array $logs_data Array of all the Log entires
	 */
	public function get_logs() {
		$logs_data = array();
		$paged     = $this->get_paged();

		$log_query = array(
			'offset'     => $paged > 1 ? ( ( $paged - 1 ) * $this->per_page ) : 0,
			'meta_query' => $this->get_meta_query(),
			'number'     => $this->per_page,
		);

		$logs = edd_get_api_request_logs( $log_query );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				/** @var $log EDD\Logs\Api_Request_Log */

				$logs_data[] = array(
					'ID'      => $log->get_id(),
					'version' => $log->get_version(),
					'speed'   => $log->get_time(),
					'ip'      => $log->get_ip(),
					'date'    => $log->get_date_created(),
					'api_key' => $log->get_api_key(),
					'request' => $log->get_request(),
					'error'   => $log->get_error(),
					'user_id' => $log->get_user_id(),
				);
			}
		}

		return $logs_data;
	}

	/**
	 * Setup the final data for the table.
	 *
	 * @since 1.5
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable, 'ID' );
		$this->items           = $this->get_logs();
		$total_items           = edd_count_api_request_logs();

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page ),
			)
		);
	}

	/**
	 * Since our "bulk actions" are navigational, we want them to always show, not just when there's items
	 *
	 * @since 2.5
	 * @return bool
	 */
	public function has_items() {
		return true;
	}
}
