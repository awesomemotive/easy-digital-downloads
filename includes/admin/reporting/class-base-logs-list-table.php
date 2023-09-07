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

use EDD\Admin\List_Table;

/**
 * EDD_Base_Log_List_Table Class
 *
 * @since 3.0
 */
class EDD_Base_Log_List_Table extends List_Table {

	/**
	 * Log type
	 *
	 * @var string
	 */
	protected $log_type = 'logs';

	/**
	 * Get things started
	 *
	 * @since 3.0
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'log',
			'plural'   => 'logs',
			'ajax'     => false
		) );

		$this->filter_bar_hooks();
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
	 * Hook in filter bar actions
	 *
	 * @since 3.0
	 */
	private function filter_bar_hooks() {
		add_action( 'edd_admin_filter_bar_logs',       array( $this, 'filter_bar_items'     ) );
		add_action( 'edd_after_admin_filter_bar_logs', array( $this, 'filter_bar_searchbox' ) );
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
	 * @return int|string|false int If customer ID, string If Email, false if not present
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
		return sanitize_text_field( $this->get_request_var( 'start-date', null ) );
	}

	/**
	 * Return the end-date of the filter
	 *
	 * @since 3.0
     *
	 * @return string End date to filter by
	 */
	public function get_filtered_end_date() {
		return sanitize_text_field( $this->get_request_var( 'end-date', null ) );
	}

	/**
	 * Return the ID of the download we're filtering logs by
	 *
	 * @since 3.0
     *
	 * @return int Download ID.
	 */
	public function get_filtered_download() {
		return absint( $this->get_request_var( 'download', false ) );
	}

	/**
	 * Return the ID of the payment we're filtering logs by
	 *
	 * @since 3.0
     *
	 * @return int Payment ID.
	 */
	public function get_filtered_payment() {
		return absint( $this->get_request_var( 'payment', false ) );
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

		<select id="edd-logs-view" name="view">
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

		<input type="hidden" name="customer" value="<?php echo $this->get_filtered_customer(); ?>" />
		<input type="hidden" name="post_type" value="download" />
		<input type="hidden" name="page" value="edd-tools" />
		<input type="hidden" name="tab" value="logs" />

		<?php
	}
	/**
	 * Sets up the downloads filter
	 *
	 * @since 3.0
	 * @since 3.1 Accepts a download ID to filter by for the selected value.
	 *
	 * @param int $download The filtered download ID, default: 0.
	 *
	 * @return void
	 */
	public function downloads_filter( $download = 0 ) {
		$args = array(
			'id'     => 'edd-log-download-filter',
			'name'   => 'download',
			'chosen' => true,
		);

		if ( ! empty( $download ) ) {
			$args['selected'] = $download;
		}

		echo EDD()->html->product_dropdown( $args );
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
	 * Empty method to hide view links on all logs table
	 *
	 * @since 3.0
	 */
	public function get_views() {
		// Intentionally empty
	}

	/**
	 * Retrieves the logs data.
	 *
	 * @since 3.0
	 *
	 * @return array Logs data.
	 */
	public function get_data() {
		$log_query = $this->get_query_args();

		return $this->get_logs( $log_query );
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

		$this->items = $this->get_data();
		$log_query   = $this->get_query_args( false );
		$total_items = $this->get_total( $log_query );

		$this->set_pagination_args( array(
			'total_pages' => ceil( $total_items / $this->per_page ),
			'total_items' => $total_items,
			'per_page'    => $this->per_page,
		) );
	}

	/**
	 * Return array of query arguments
	 *
	 * @since 3.0
	 *
	 * @param bool $paginate Whether to add pagination arguments
	 *
	 * @return array
	 */
	protected function get_query_args( $paginate = true ) {

		// Defaults
		$retval = array(
			'product_id'  => $this->get_filtered_download(),
			'customer_id' => $this->get_filtered_customer(),
			'order_id'    => $this->get_filtered_payment(),
			'meta_query'  => $this->get_meta_query(),
		);

		// Search
		$search = $this->get_search();
		if ( ! empty( $search ) ) {
			if ( filter_var( $search, FILTER_VALIDATE_IP ) ) {
				$retval['ip'] = $search;
			} elseif ( is_email( $search ) ) {
				if ( 'api_requests' === $this->log_type ) {
					// API requests are linked to user accounts, so we're checking user data here.
					$user = get_user_by( 'email', $search );
					if ( ! empty( $user->ID ) ) {
						$retval['user_id'] = $user->ID;
					} else {
						// This is a fallback to help ensure an invalid email will produce zero results.
						$retval['search'] = $search;
					}
				} else {
					// All other logs are linked to customers.
					$customer = edd_get_customer_by( 'email', $search );
					if ( ! empty( $customer->id ) ) {
						$retval['customer_id'] = $customer->id;
					} else {
						// This is a fallback to help ensure an invalid email will produce zero results.
						$retval['search'] = $search;
					}
				}
			} elseif ( 'api_requests' === $this->log_type && 32 === strlen( $search ) ) {
				// Look for an API key
				$retval['api_key'] = $search;
			} elseif ( 'api_requests' === $this->log_type && stristr( $search, 'token:' ) ) {
				// Look for an API token
				$retval['token'] = str_ireplace( 'token:', '', $search );
			} elseif ( is_numeric( $search ) ) {
				if ( 'api_requests' === $this->log_type ) {
					// API requests are linked to user accounts, so we're checking user data here.
					$user = get_user_by( 'email', $search );
					if ( ! empty( $user->ID ) ) {
						$retval['user_id'] = $user->ID;
					} else {
						$retval['search'] = $search;
					}
				} else {
					// All other logs are linked to customers.
					$customer = edd_get_customer( $search );
					if ( ! empty( $customer->id ) ) {
						$retval['customer_id'] = $customer->id;
					} elseif ( 'file_downloads' === $this->log_type ) {
						$retval['product_id'] = $search;
					} else {
						$retval['search'] = $search;
					}
				}
			} else {
				if ( 'file_downloads' === $this->log_type ) {
					$this->file_search = true;
				} else {
					$retval['search'] = $search;
				}
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
					'after'  => \EDD\Utils\Date::parse( date( 'Y-m-d H:i:s', strtotime( "{$start_date} midnight" ) ), edd_get_timezone_id() )->setTimezone( 'UTC' )->toDateTimeString(),
				);
			}

			// End date
			if ( ! empty( $end_date ) ) {
				$retval['date_created_query'][] = array(
					'column' => 'date_created',
					'before' => \EDD\Utils\Date::parse( date( 'Y-m-d H:i:s', strtotime( "{$end_date} + 1 day" ) ), edd_get_timezone_id() )->setTimezone( 'UTC' )->toDateTimeString(),
				);
			}
		}

		$retval = array_filter( $retval );

		// Return query arguments
		return ( true === $paginate )
			? $this->parse_pagination_args( $retval )
			: $retval;
	}

	/**
	 * Output advanced filters for payments
	 *
	 * @since 3.0
	 */
	public function advanced_filters() {
		edd_admin_filter_bar( 'logs' );
	}

	/**
	 * Output filter bar items
	 *
	 * @since 3.0
	 */
	public function filter_bar_items() {

		// Get values
		$start_date = $this->get_filtered_start_date();
		$end_date   = $this->get_filtered_end_date();
		$download   = $this->get_filtered_download();
		$customer   = $this->get_filtered_customer();
		$view       = $this->get_filtered_view();
		$clear_url  = edd_get_admin_url( array(
			'page' => 'edd-tools',
			'tab'  => 'logs',
			'view' => sanitize_key( $view ),
		) ); ?>

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
			<?php $this->downloads_filter( $download ); ?>
		</span>

		<?php if ( ! empty( $customer ) ) : ?>

			<span id="edd-customer-filter">
				<?php printf( esc_html__( 'Customer ID: %d', 'easy-digital-downloads' ), $customer ); ?>
			</span>

		<?php endif; ?>

		<input type="submit" class="button-secondary" value="<?php esc_attr_e( 'Filter', 'easy-digital-downloads' ); ?>"/>

		<?php if ( ! empty( $start_date ) || ! empty( $end_date ) || ! empty( $download ) || ! empty( $customer ) ) : ?>
			<a href="<?php echo esc_url( $clear_url ); ?>" class="button-secondary">
				<?php esc_html_e( 'Clear', 'easy-digital-downloads' ); ?>
			</a>
		<?php endif; ?>

		<?php
	}

	/**
	 * Output the filter bar searchbox
	 *
	 * @since 3.0
	 */
	public function filter_bar_searchbox() {
		do_action( 'edd_logs_advanced_filters_row' );

		$this->search_box( __( 'Search', 'easy-digital-downloads' ), 'edd-logs' );
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
