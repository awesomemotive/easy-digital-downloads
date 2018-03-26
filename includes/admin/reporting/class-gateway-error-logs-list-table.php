<?php
/**
 * Gateway Error Log View Class
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EDD_Gateway_Error_Log_Table Class
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
	 * @var int
	 * @since 1.4
	 */
	public $per_page = 30;

	/**
	 * Get things started
	 *
	 * @since 1.4
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular' => edd_get_label_singular(),
			'plural'   => edd_get_label_plural(),
			'ajax'     => false,
		) );
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
	 * @since 1.4
	 *
	 * @param array $item Contains all the data of the log item
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'error' :
				return get_the_title( $item['ID'] ) ? get_the_title( $item['ID'] ) : __( 'Payment Error', 'easy-digital-downloads' );
			default:
				return $item[ $column_name ];
		}
	}

	/**
	 * Output Error Message Column
	 *
	 * @since 1.4.4
	 * @param array $item Contains all the data of the log
	 * @return void
	 */
	public function column_message( $item ) {
	?>
		<a href="#TB_inline?width=640&amp;inlineId=log-message-<?php echo $item['ID']; ?>" class="thickbox"><?php _e( 'View Log Message', 'easy-digital-downloads' ); ?></a>
		<div id="log-message-<?php echo $item['ID']; ?>" style="display:none;">
			<?php

			$log_message = get_post_field( 'post_content', $item['ID'] );
			$serialized  = strpos( $log_message, '{"' );

			// Check to see if the log message contains serialized information
			if ( $serialized !== false ) {
				$length = strlen( $log_message ) - $serialized;
				$intro  = substr( $log_message, 0, - $length );
				$data   = substr( $log_message, $serialized, strlen( $log_message ) - 1 );

				echo wpautop( $intro );
				echo '<strong>' . wpautop( __( 'Log data:', 'easy-digital-downloads' ) ) . '</strong>';
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
	 * Retrieve the table columns
	 *
	 * @since 1.4
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'ID'         => __( 'Log ID', 'easy-digital-downloads' ),
			'payment_id' => __( 'Payment ID', 'easy-digital-downloads' ),
			'error'      => __( 'Error', 'easy-digital-downloads' ),
			'message'    => __( 'Error Message', 'easy-digital-downloads' ),
			'gateway'    => __( 'Gateway', 'easy-digital-downloads' ),
			'date'       => __( 'Date', 'easy-digital-downloads' ),
		);

		return $columns;
	}

	/**
	 * Retrieve the current page number
	 *
	 * @since 1.4
	 * @return int Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Outputs the log views
	 *
	 * @since 1.4
	 * @return void
	 */
	public function bulk_actions( $which = '' ) {
		// These aren't really bulk actions but this outputs the markup in the right place
		edd_log_views();
	}

	/**
	 * Gets the log entries for the current view
	 *
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
		$log_query = array(
			'log_type'    => 'gateway_error',
			'paged'       => $paged,
		);

		$logs = $edd_logs->get_connected_logs( $log_query );

		if ( $logs ) {
			foreach ( $logs as $log ) {

				$logs_data[] = array(
					'ID'         => $log->ID,
					'payment_id' => $log->post_parent,
					'error'      => 'error',
					'gateway'    => edd_get_payment_gateway( $log->post_parent ),
					'date'       => $log->post_date,
				);
			}
		}

		return $logs_data;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @since 1.4
	 * @global object $edd_logs EDD Logs Object
	 * @uses EDD_Gateway_Error_Log_Table::get_columns()
	 * @uses WP_List_Table::get_sortable_columns()
	 * @uses EDD_Gateway_Error_Log_Table::get_pagenum()
	 * @uses EDD_Gateway_Error_Log_Table::get_logs()
	 * @uses EDD_Gateway_Error_Log_Table::get_log_count()
	 * @return void
	 */
	public function prepare_items() {
		global $edd_logs;

		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->get_logs();
		$total_items           = $edd_logs->get_log_count( 0, 'gateway_error' );

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
