<?php
/**
 * Base Log List Table.
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.4
 * @since       3.0 Updated to use the custom tables.
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EDD_Base_Log_List_Table Class
 *
 * @since 3.0
 */
class EDD_Base_Log_List_Table extends WP_List_Table {

	/**
	 * Number of items per page
	 *
	 * @var int
	 * @since 3.0
	 */
	public $per_page = 30;

	/**
	 * Get things started
	 *
	 * @since 3.0
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => edd_get_label_singular(),
			'plural'   => edd_get_label_plural(),
			'ajax'     => false
		) );
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * Removes the referrer nonce from parent class.
	 *
	 * @since 3.0.0
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {
	?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">
			<?php if ( $this->has_items() ) : ?>
				<div class="alignleft actions bulkactions">
					<?php $this->bulk_actions( $which ); ?>
				</div>
			<?php endif;

			$this->extra_tablenav( $which );
			$this->pagination( $which ); ?>

			<br class="clear" />
		</div><?php
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 3.0
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'id';
	}

	/**
	 * Return the current log view
	 *
	 * @since 3.0
	 * @return string
	 */
	public function get_filtered_view() {
		return isset( $_GET['view'] ) && array_key_exists( $_GET['view'], edd_log_default_views() )
			? sanitize_text_field( $_GET['view'] )
			: 'file_downloads';
	}

	/**
	 * Return the user we are filtering logs by, if any
	 *
	 * @since 3.0
	 * @return mixed int If User ID, string If Email/Login
	 */
	public function get_filtered_user() {
		return isset( $_GET['user'] ) ? absint( $_GET['user'] ) : false;
	}

	/**
	 * Return the customer we are filtering logs by, if any
	 *
	 * @since 3.0
	 * @return mixed int If customer ID, string If Email, false if not present
	 */
	public function get_filtered_customer() {
		$ret = false;

		if ( isset( $_GET['customer'] ) ) {
			$customer = new EDD_Customer( sanitize_text_field( $_GET['customer'] ) );
			if ( ! empty( $customer->id ) ) {
				$ret = $customer->id;
			}
		}

		return $ret;
	}

	/**
	 * Return the start-date of the filter
	 *
	 * @since 3.0
     *
	 * @return string Start date to filter by
	 */
	public function get_filtered_start_date() {
		return ! empty( $_GET['start-date'] )
			? sanitize_text_field( $_GET['start-date'] )
			: null;
	}

	/**
	 * Return the end-date of the filter
	 *
	 * @since 3.0
     *
	 * @return string Start date to filter by
	 */
	public function get_filtered_end_date() {
		return ! empty( $_GET['end-date'] )
			? sanitize_text_field( $_GET['end-date'] )
			: null;
	}

	/**
	 * Return the ID of the download we're filtering logs by
	 *
	 * @since 3.0
     *
	 * @return int Download ID.
	 */
	public function get_filtered_download() {
		return ! empty( $_GET['download'] )
			? absint( $_GET['download'] )
			: false;
	}

	/**
	 * Return the ID of the payment we're filtering logs by
	 *
	 * @since 3.0
     *
	 * @return int Payment ID.
	 */
	public function get_filtered_payment() {
		return ! empty( $_GET['payment'] )
			? absint( $_GET['payment'] )
			: false;
	}

	/**
	 * Return the search query string.
	 *
	 * @since 3.0
     *
	 * @return String The search string.
	 */
	public function get_search() {
		return ! empty( $_GET['s'] )
			? urldecode( trim( $_GET['s'] ) )
			: '';
	}

	/**
	 * Gets the meta query for the log query.
	 *
	 * This is used to return log entries that match our search query, user query, or download query.
	 *
	 * @since 3.0
     *
	 * @return array $meta_query
	 */
	public function get_meta_query() {
		return array();
	}

	/**
	 * Retrieve the current page number.
	 *
	 * @since 3.0
     *
	 * @return int Current page number.
	 */
	function get_paged() {
		return isset( $_GET['paged'] )
			? absint( $_GET['paged'] )
			: 1;
	}

	/**
	 * Outputs the log views.
	 *
	 * @since 3.0
	 */
	public function bulk_actions( $which = '' ) {
		return;
	}

	/**
	 * Renders the Reports page views drop down
	 *
	 * @since 3.0
	 * @return void
	 */
	public function log_views() {
		$views        = edd_log_default_views();
		$current_view = $this->get_filtered_view(); ?>

		<select id="edd-logs-view" name="view" class="edd-select-chosen">
			<?php foreach ( $views as $view_id => $label ) : ?>
				<option value="<?php echo esc_attr( $view_id ); ?>" <?php selected( $view_id, $current_view ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>

		<?php
		/**
		 * Fires immediately after the logs view actions are rendered in the Logs screen.
		 *
		 * @since 3.0
		 */
		do_action( 'edd_log_view_actions' );
		?>

		<input type="hidden" name="post_type" value="download" />
		<input type="hidden" name="page" value="edd-tools" />
		<input type="hidden" name="tab" value="logs" />

		<?php
	}
	/**
	 * Sets up the downloads filter
	 *
	 * @since 3.0
	 * @return void
	 */
	public function downloads_filter() {
		$downloads = get_posts( array(
			'post_type'              => 'download',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		) );

		if ( $downloads ) {
			echo '<select name="download" id="edd-log-download-filter" class="edd-select-chosen">';
				echo '<option value="0">' . __( 'All Downloads', 'easy-digital-downloads' ) . '</option>';
				foreach ( $downloads as $download ) {
					echo '<option value="' . $download . '"' . selected( $download, $this->get_filtered_download() ) . '>' . esc_html( get_the_title( $download ) ) . '</option>';
				}
			echo '</select>';
		}
	}

	/**
	 * Gets the log entries for the current view
	 *
	 * @since 3.0
     *
	 * @return array $logs_data Array of all the logs.
	 */
	function get_logs( $log_query = array() ) {
		return array();
	}

	/**
	 * Get the total number of items
	 *
	 * @since 3.0
	 *
	 * @param array $log_query
	 *
	 * @return int
	 */
	public function get_total( $log_query = array() ) {
		return count( array() );
	}

	/**
	 * Setup the final data for the table.
	 *
	 * @since 3.0
	 */
	public function prepare_items() {

		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns()
		);

		$log_query   = $this->get_query_args();
		$this->items = $this->get_logs( $log_query );
		$total_items = $this->get_total( $log_query );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $this->per_page,
			'total_pages' => ceil( $total_items / $this->per_page )
		) );
	}

	/**
	 * Return array of query arguments
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	private function get_query_args() {

		// Pagination
		$paged  = $this->get_paged();
		$offset = ( $paged > 1 )
			? ( ( $paged - 1 ) * $this->per_page )
			: 0;

		// Defaults
		$retval = array(
			'download_id' => $this->get_filtered_download(),
			'customer_id' => $this->get_filtered_customer(),
			'payment_id'  => $this->get_filtered_payment(),
			'meta_query'  => $this->get_meta_query(),
			'offset'      => $offset,
			'number'      => $this->per_page
		);

		// Search
		$search = $this->get_search();
		if ( ! empty( $search ) ) {
			if ( filter_var( $search, FILTER_VALIDATE_IP ) ) {
				$retval['ip'] = $search;

			} elseif ( is_email( $search ) ) {
				$customer = new EDD_Customer( $search );
				if ( ! empty( $customer->id ) ) {
					$retval['customer_id'] = $customer->id;
				}

			} elseif ( is_numeric( $search ) ) {
				$customer = new EDD_Customer( $search );

				if ( ! empty( $customer->id ) ) {
					$retval['customer_id'] = $customer->id;
				} else {
					$this->file_search = true;
				}
			} else {
				$retval['file_id'] = $search;
			}
		}

		// Start date
		$start_date = $this->get_filtered_start_date();
		$end_date   = $this->get_filtered_end_date();

		// Setup original array
		if ( ! empty( $start_date ) || ! empty( $end_date ) ) {
			$retval['date_created_query']['column'] = 'date_created';

			// Start date
			if ( ! empty( $start_date ) ) {
				$retval['date_created_query'][] = array(
					'column' => 'date_created',
					'after'  => date( "Y-m-d H:i:s", strtotime( "{$start_date} midnight" ) )
				);
			}

			// End date
			if ( ! empty( $end_date ) ) {
				$retval['date_created_query'][] = array(
					'column' => 'date_created',
					'before' => date( "Y-m-d H:i:s", strtotime( "{$end_date} + 1 day" ) )
				);
			}
		}

		// Return query arguments
		return $retval;
	}

	/**
	 * Output advanced filters for payments
	 *
	 * @since 3.0
	 */
	public function advanced_filters() {

		// Get values
		$start_date = $this->get_filtered_start_date();
		$end_date   = $this->get_filtered_end_date();
		//! empty( $_GET['end-date']   ) ? sanitize_text_field( $_GET['end-date']   ) : null;
		$download   = $this->get_filtered_download();
		$clear_url  = add_query_arg( array(
			'post_type' => 'download',
			'page'      => 'edd-tools',
			'tab'       => 'logs'
		), admin_url( 'edit.php' ) ); ?>

		<div class="wp-filter" id="edd-filters">
			<div class="filter-items">
				<span id="edd-type-filter">
					<?php $this->log_views(); ?>
				</span>

				<span id="edd-date-filters" class="edd-from-to-wrapper">
					<?php

					echo EDD()->html->date_field( array(
						'id'          => 'start-date',
						'name'        => 'start-date',
						'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' ),
						'value'       => $start_date
					) );

					echo EDD()->html->date_field( array(
						'id'          => 'end-date',
						'name'        => 'end-date',
						'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' ),
						'value'       => $end_date
					) );

				?></span>

				<span id="edd-download-filter">
					<?php $this->downloads_filter(); ?>
				</span>

				<span id="edd-after-core-filters">
					<?php do_action( 'edd_payment_advanced_filters_after_fields' ); ?>

					<input type="submit" class="button-secondary" value="<?php _e( 'Filter', 'easy-digital-downloads' ); ?>"/>

					<?php if ( ! empty( $start_date ) || ! empty( $end_date ) || ! empty( $download ) ) : ?>
						<a href="<?php echo esc_url( $clear_url ); ?>" class="button-secondary">
							<?php _e( 'Clear Filter', 'easy-digital-downloads' ); ?>
						</a>
					<?php endif; ?>
				</span>
			</div>
			<?php do_action( 'edd_payment_advanced_filters_row' ); ?>
			<?php $this->search_box( __( 'Search', 'easy-digital-downloads' ), 'edd-logs' ); ?>
		</div>

		<?php
	}

	/**
	 * Show the search field
	 *
	 * @since 3.0
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 */
	public function search_box( $text, $input_id ) {

		// Bail if no customers and no search
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}

		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}

		?>

		<p class="search-form">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" placeholder="<?php esc_html_e( 'Search logs...', 'easy-digital-downloads' ); ?>" />
		</p>

		<?php
	}
}
