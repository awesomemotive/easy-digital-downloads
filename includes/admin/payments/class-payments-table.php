<?php
/**
 * Payment History Table Class
 *
 * @package     EDD
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EDD_Payment_History_Table Class
 *
 * Renders the Payment History table on the Payment History page
 *
 * @since 1.4
 * @since 3.0 Updated to use new query methods.
 */
class EDD_Payment_History_Table extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 1.4
	 */
	public $per_page = 30;

	/**
	 * URL of this page
	 *
	 * @var string
	 * @since 1.4.1
	 */
	public $base_url;

	/**
	 * Total number of payments
	 *
	 * @var int
	 * @since 1.4
	 */
	public $total_count;

	/**
	 * Total number of complete payments
	 *
	 * @var int
	 * @since 1.4
	 */
	public $complete_count;

	/**
	 * Total number of pending payments
	 *
	 * @var int
	 * @since 1.4
	 */
	public $pending_count;

	/**
	 * Total number of processing payments
	 *
	 * @var int
	 * @since 2.8
	 */
	public $processing_count;

	/**
	 * Total number of refunded payments
	 *
	 * @var int
	 * @since 1.4
	 */
	public $refunded_count;

	/**
	 * Total number of failed payments
	 *
	 * @var int
	 * @since 1.4
	 */
	public $failed_count;

	/**
	 * Total number of revoked payments
	 *
	 * @var int
	 * @since 1.4
	 */
	public $revoked_count;

	/**
	 * Total number of abandoned payments
	 *
	 * @var int
	 * @since 1.6
	 */
	public $abandoned_count;

	/**
	 * Get things started
	 *
	 * @since 1.4
	 * @uses EDD_Payment_History_Table::get_payment_counts()
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {

		// Set parent defaults
		parent::__construct( array(
			'singular' => edd_get_label_singular(),
			'plural'   => edd_get_label_plural(),
			'ajax'     => false,
		) );

		$this->get_payment_counts();
		$this->process_bulk_action();
		$this->base_url = admin_url( 'edit.php?post_type=download&page=edd-payment-history' );
	}

	/**
	 * Output advanced filters for payments
	 *
	 * @since 1.4
	 */
	public function advanced_filters() {

		// Get values
		$start_date = isset( $_GET['start-date'] ) ? sanitize_text_field( $_GET['start-date'] ) : null;
		$end_date   = isset( $_GET['end-date']   ) ? sanitize_text_field( $_GET['end-date']   ) : null;
		$gateway    = isset( $_GET['gateway']    ) ? sanitize_key( $_GET['gateway']           ) : 'all';
		$status     = isset( $_GET['status']     ) ? sanitize_key( $_GET['status']            ) : '';
		$clear_url  = add_query_arg( array(
			'post_type' => 'download',
			'page'      => 'edd-payment-history'
		), admin_url( 'edit.php' ) );

		// Gateways
		$all_gateways = edd_get_payment_gateways();

		// No gateways
		if ( empty( $all_gateways ) ) {
			$gateways = array();

		// Add "All" and pluck labels
		} else {
			$gateways = array_merge( array(
				'all' => __( 'All gateways', 'easy-digital-downloads' )
			), wp_list_pluck( $all_gateways, 'admin_label' ) );
		}

		/**
		 * Allow gateways that aren't registered the standard way to be displayed in the dropdown.
		 *
		 * @since 2.8.11
		 */
		$gateways = apply_filters( 'edd_payments_table_gateways', $gateways ); ?>

		<div class="wp-filter" id="edd-filters">
			<div class="filter-items">
				<span id="edd-date-filters">
					<span>
						<label for="start-date"><?php _ex( 'From', 'date filter', 'easy-digital-downloads' ); ?></label>
						<input type="text" id="start-date" name="start-date" class="edd_datepicker" data-format="<?php echo esc_attr( edd_get_date_picker_format() ); ?>" value="<?php echo esc_attr( $start_date ); ?>" placeholder="<?php echo esc_attr( edd_get_date_picker_format() ); ?>"/>
					</span>
					<span>
						<label for="end-date"><?php _ex( 'To', 'date filter', 'easy-digital-downloads' ); ?></label>
						<input type="text" id="end-date" name="end-date" class="edd_datepicker" data-format="<?php echo esc_attr( edd_get_date_picker_format() ); ?>" value="<?php echo esc_attr( $end_date ); ?>" placeholder="<?php echo esc_attr( edd_get_date_picker_format() ); ?>"/>
					</span>
				</span>

				<?php if ( ! empty( $gateways ) ) : ?>

					<span id="edd-payment-gateway-filter">
						<?php echo EDD()->html->select( array(
							'options'          => $gateways,
							'name'             => 'gateway',
							'id'               => 'gateway',
							'selected'         => $gateway,
							'show_option_all'  => false,
							'show_option_none' => false
						) ); ?>
					</span>

				<?php endif; ?>

				<span id="edd-payment-after-core-filters">
					<?php do_action( 'edd_payment_advanced_filters_after_fields' ); ?>

					<input type="submit" class="button-secondary" value="<?php _e( 'Filter', 'easy-digital-downloads' ); ?>"/>

					<?php if ( ! empty( $start_date ) || ! empty( $end_date ) || ( 'all' !== $gateway ) ) : ?>
						<a href="<?php echo esc_url( $clear_url ); ?>" class="button-secondary">
							<?php _e( 'Clear Filter', 'easy-digital-downloads' ); ?>
						</a>
					<?php endif; ?>
				</span>

				<?php if ( ! empty( $status ) ) : ?>
					<input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>"/>
				<?php endif; ?>

			</div>
			<?php do_action( 'edd_payment_advanced_filters_row' ); ?>
			<?php $this->search_box( __( 'Search', 'easy-digital-downloads' ), 'edd-payments' ); ?>
		</div>

		<?php
	}

	/**
	 * Show the search field
	 *
	 * @since 1.4
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
			<?php do_action( 'edd_payment_history_search' ); ?>
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" placeholder="<?php esc_html_e( 'Search payments...', 'easy-digital-downloads' ); ?>" value="<?php _admin_search_query(); ?>" />
		</p>

		<?php
	}

	/**
	 * Retrieve the view types.
	 *
	 * @since 1.4
     *
	 * @return array $views All the views available.
	 */
	public function get_views() {
		$current          = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$total_count      = '&nbsp;<span class="count">(' . $this->total_count    . ')</span>';
		$complete_count   = '&nbsp;<span class="count">(' . $this->complete_count . ')</span>';
		$pending_count    = '&nbsp;<span class="count">(' . $this->pending_count  . ')</span>';
		$processing_count = '&nbsp;<span class="count">(' . $this->processing_count  . ')</span>';
		$refunded_count   = '&nbsp;<span class="count">(' . $this->refunded_count . ')</span>';
		$failed_count     = '&nbsp;<span class="count">(' . $this->failed_count   . ')</span>';
		$abandoned_count  = '&nbsp;<span class="count">(' . $this->abandoned_count . ')</span>';
		$revoked_count    = '&nbsp;<span class="count">(' . $this->revoked_count   . ')</span>';

		$views = array(
			'all'        => sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( array( 'status', 'paged' ) ), $current === 'all' || $current == '' ? ' class="current"' : '', __('All','easy-digital-downloads' ) . $total_count ),
			'publish'    => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'publish', 'paged' => FALSE ) ), $current === 'publish' ? ' class="current"' : '', __('Completed','easy-digital-downloads' ) . $complete_count ),
			'pending'    => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'pending', 'paged' => FALSE ) ), $current === 'pending' ? ' class="current"' : '', __('Pending','easy-digital-downloads' ) . $pending_count ),
			'processing' => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'processing', 'paged' => FALSE ) ), $current === 'processing' ? ' class="current"' : '', __('Processing','easy-digital-downloads' ) . $processing_count ),
			'refunded'   => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'refunded', 'paged' => FALSE ) ), $current === 'refunded' ? ' class="current"' : '', __('Refunded','easy-digital-downloads' ) . $refunded_count ),
			'revoked'    => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'revoked', 'paged' => FALSE ) ), $current === 'revoked' ? ' class="current"' : '', __('Revoked','easy-digital-downloads' ) . $revoked_count ),
			'failed'     => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'failed', 'paged' => FALSE ) ), $current === 'failed' ? ' class="current"' : '', __('Failed','easy-digital-downloads' ) . $failed_count ),
			'abandoned'  => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'abandoned', 'paged' => FALSE ) ), $current === 'abandoned' ? ' class="current"' : '', __('Abandoned','easy-digital-downloads' ) . $abandoned_count ),
		);

		return apply_filters( 'edd_payments_table_views', $views );
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @since 1.4
	 *
     * @return array $columns Array of all the list table columns.
	 */
	public function get_columns() {
		return apply_filters( 'edd_payments_table_columns', array(
			'cb'       => '<input type="checkbox" />', // Render a checkbox instead of text
			'ID'       => __( 'ID', 'easy-digital-downloads' ),
			'email'    => __( 'Email', 'easy-digital-downloads' ),
			'details'  => __( 'Details', 'easy-digital-downloads' ),
			'amount'   => __( 'Amount', 'easy-digital-downloads' ),
			'date'     => __( 'Date', 'easy-digital-downloads' ),
			'customer' => __( 'Customer', 'easy-digital-downloads' ),
			'status'   => __( 'Status', 'easy-digital-downloads' ),
		) );
	}

	/**
	 * Retrieve the table's sortable columns.
	 *
	 * @since 1.4
	 *
     * @return array Array of all the sortable columns.
	 */
	public function get_sortable_columns() {
		return apply_filters( 'edd_payments_table_sortable_columns', array(
			'ID'     => array( 'ID', true ),
			'amount' => array( 'amount', false ),
			'date'   => array( 'date', false ),
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
     * @since 3.0 Updated to use the new EDD\Orders\Order class.
	 *
	 * @param EDD\Orders\Order $order       Order object.
	 * @param string           $column_name The name of the column.
	 *
	 * @return string Column name.
	 */
	public function column_default( $order, $column_name ) {
		switch ( $column_name ) {
			case 'amount' :
				$value = edd_currency_filter( edd_format_amount( $order->get_total() ), $order->get_currency() );
				break;
			case 'date' :
				$value = edd_date_i18n( strtotime( $order->get_date_created() ) );
				break;
			case 'status' :
				$value = $order->get_status();
				break;
			case 'details' :
				$value = '<a href="' . add_query_arg( 'id', $order->get_id(), admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details' ) ) . '">' . __( 'View Order Details', 'easy-digital-downloads' ) . '</a>';
				break;
			default:
				$value = method_exists( $order, 'get_' . $column_name ) ? call_user_func( 'get_' . $column_name, $order ) : '';
				break;
		}
		return apply_filters( 'edd_payments_table_column', $value, $payment->ID, $column_name );
	}

	/**
	 * Render the Email column.
	 *
	 * @since 1.4
     * @since 3.0 Updated to use the new EDD\Orders\Order class.
     *
	 * @param EDD\Orders\Order $order Order object.
	 * @return string Data shown in the Email column
	 */
	public function column_email( $order ) {
		$row_actions = array();

		$email = $order->get_email();

		// Add search term string back to base URL
		$search_terms = ( isset( $_GET['s'] ) ? trim( $_GET['s'] ) : '' );
		if ( ! empty( $search_terms ) ) {
			$this->base_url = add_query_arg( 's', $search_terms, $this->base_url );
		}

		if ( 'publish' === $order->get_status() && ! empty( $email ) ) {
			$row_actions['email_links'] = '<a href="' . add_query_arg( array( 'edd-action' => 'email_links', 'purchase_id' => $order->get_id() ), $this->base_url ) . '">' . __( 'Resend Purchase Receipt', 'easy-digital-downloads' ) . '</a>';
		}

		$row_actions['delete'] = '<a href="' . wp_nonce_url( add_query_arg( array( 'edd-action' => 'delete_payment', 'purchase_id' => $order->get_id() ), $this->base_url ), 'edd_payment_nonce') . '">' . __( 'Delete', 'easy-digital-downloads' ) . '</a>';

		$row_actions = apply_filters( 'edd_payment_row_actions', $row_actions, $payment );

		if ( empty( $email ) ) {
			$email = __( '(unknown)', 'easy-digital-downloads' );
		}

		$value = $email . $this->row_actions( $row_actions );

		return apply_filters( 'edd_payments_table_column', $value, $order->get_id(), 'email' );
	}

	/**
	 * Render the checkbox column.
	 *
	 * @since 1.4
     * @since 3.0 Updated to use the new EDD\Orders\Order class.
	 *
	 * @param EDD\Orders\Order $order Order object.
	 * @return string Displays a checkbox.
	 */
	public function column_cb( $order ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			'payment',
			$order->get_id()
		);
	}

	/**
	 * Render the ID column.
	 *
	 * @since 2.0
     * @since 3.0 Updated to use the new EDD\Orders\Order class.
	 *
	 * @param EDD\Orders\Order $order Order object.
	 * @return string Displays a checkbox.
	 */
	public function column_ID( $order ) {
		return edd_get_option( 'enable_sequential' ) ? $order->get_number() : $order->get_id();
	}

	/**
	 * Render the Customer column.
	 *
	 * @since 2.4.3

     * @param array $payment Contains all the data of the payment.
	 * @return string Data shown in the Customer column.
	 */
	public function column_customer( $payment ) {
		$customer_id = edd_get_payment_customer_id( $payment->ID );

		if ( ! empty( $customer_id ) ) {
			$customer = new EDD_Customer( $customer_id );
			$name = ! empty( $customer->name ) ? $customer->name : '<em>' . __( 'Unnamed Customer','easy-digital-downloads' ) . '</em>';
			$value = '<a href="' . esc_url( admin_url( "edit.php?post_type=download&page=edd-customers&view=overview&id=$customer_id" ) ) . '">#' . $customer->id . ' ' . $name . '</a>';
		} else {
			$email = edd_get_payment_user_email( $payment->ID );
			$value = '<a href="' . esc_url( admin_url( "edit.php?post_type=download&page=edd-payment-history&s=$email" ) ) . '">' . __( '(customer missing)', 'easy-digital-downloads' ) . '</a>';
		}
		return apply_filters( 'edd_payments_table_column', $value, $payment->ID, 'user' );
	}

	/**
	 * Retrieve the bulk actions
	 *
	 * @since 1.4
	 *
     * @return array $actions Bulk actions.
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete'                 => __( 'Delete',                'easy-digital-downloads' ),
			'set-status-publish'     => __( 'Set To Completed',      'easy-digital-downloads' ),
			'set-status-pending'     => __( 'Set To Pending',        'easy-digital-downloads' ),
			'set-status-processing'  => __( 'Set To Processing',     'easy-digital-downloads' ),
			'set-status-refunded'    => __( 'Set To Refunded',       'easy-digital-downloads' ),
			'set-status-revoked'     => __( 'Set To Revoked',        'easy-digital-downloads' ),
			'set-status-failed'      => __( 'Set To Failed',         'easy-digital-downloads' ),
			'set-status-abandoned'   => __( 'Set To Abandoned',      'easy-digital-downloads' ),
			'set-status-preapproval' => __( 'Set To Preapproval',    'easy-digital-downloads' ),
			'set-status-cancelled'   => __( 'Set To Cancelled',      'easy-digital-downloads' ),
			'resend-receipt'         => __( 'Resend Email Receipts', 'easy-digital-downloads' )
		);

		return apply_filters( 'edd_payments_table_bulk_actions', $actions );
	}

	/**
	 * Process the bulk actions.
	 *
	 * @since 1.4
	 */
	public function process_bulk_action() {
		$ids    = isset( $_GET['payment'] ) ? $_GET['payment'] : false;
		$action = $this->current_action();

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		if ( empty( $action ) ) {
			return;
		}

		foreach ( $ids as $id ) {
		    // Detect when a bulk action is being triggered...
			switch ( $this->current_action() ) {
				case 'delete':
					edd_delete_purchase( $id );
					break;

				case 'set-status-publish':
					edd_update_payment_status( $id, 'publish' );
					break;

				case 'set-status-pending':
					edd_update_payment_status( $id, 'pending' );
					break;

				case 'set-status-processing':
					edd_update_payment_status( $id, 'processing' );
					break;

				case 'set-status-refunded':
					edd_update_payment_status( $id, 'refunded' );
					break;

				case 'set-status-revoked':
					edd_update_payment_status( $id, 'revoked' );
					break;

				case 'set-status-failed':
					edd_update_payment_status( $id, 'failed' );
					break;

				case 'set-status-abandoned':
					edd_update_payment_status( $id, 'abandoned' );
					break;

				case 'set-status-preapproval':
					edd_update_payment_status( $id, 'preapproval' );
					break;

				case 'set-status-cancelled':
					edd_update_payment_status( $id, 'cancelled' );
					break;

				case 'resend-receipt':
					edd_email_purchase_receipt( $id, false );
					break;
            }

			do_action( 'edd_payments_table_do_bulk_action', $id, $this->current_action() );
		}
	}

	/**
	 * Retrieve the payment counts.
	 *
	 * @since 1.4
	 */
	public function get_payment_counts() {
		$args = array();

		if( isset( $_GET['user'] ) ) {
			$args['user'] = urldecode( $_GET['user'] );
		} elseif( isset( $_GET['customer'] ) ) {
			$args['customer'] = absint( $_GET['customer'] );
		} elseif( isset( $_GET['s'] ) ) {
			$is_user  = strpos( $_GET['s'], strtolower( 'user:' ) ) !== false;

			if ( $is_user ) {
				$args['user'] = absint( trim( str_replace( 'user:', '', strtolower( $_GET['s'] ) ) ) );
				unset( $args['s'] );
			} else {
				$args['s'] = sanitize_text_field( $_GET['s'] );
			}
		}

		if ( ! empty( $_GET['start-date'] ) ) {
			$args['start-date'] = urldecode( $_GET['start-date'] );
		}

		if ( ! empty( $_GET['end-date'] ) ) {
			$args['end-date'] = urldecode( $_GET['end-date'] );
		}

		if ( ! empty( $_GET['gateway'] ) && $_GET['gateway'] !== 'all' ) {
			$args['gateway'] = $_GET['gateway'];
		}

		$payment_count          = edd_count_payments( $args );
		$this->complete_count   = $payment_count->publish;
		$this->pending_count    = $payment_count->pending;
		$this->processing_count = $payment_count->processing;
		$this->refunded_count   = $payment_count->refunded;
		$this->failed_count     = $payment_count->failed;
		$this->revoked_count    = $payment_count->revoked;
		$this->abandoned_count  = $payment_count->abandoned;

		foreach ( $payment_count as $count ) {
			$this->total_count += $count;
		}
	}

	/**
	 * Retrieve all the data for all the payments.
	 *
	 * @since 1.4
	 *
     * @return array $payment_data Array of all the data for the payments.
	 */
	public function payments_data() {
	    $args = array();

		$per_page   = $this->per_page;
		$orderby    = isset( $_GET['orderby'] )     ? urldecode( $_GET['orderby'] )              : 'ID';
		$order      = isset( $_GET['order'] )       ? $_GET['order']                             : 'DESC';
		$user       = isset( $_GET['user'] )        ? $_GET['user']                              : null;
		$customer   = isset( $_GET['customer'] )    ? $_GET['customer']                          : null;
		$status     = isset( $_GET['status'] )      ? $_GET['status']                            : edd_get_payment_status_keys();
		$meta_key   = isset( $_GET['meta_key'] )    ? $_GET['meta_key']                          : null;
		$year       = isset( $_GET['year'] )        ? $_GET['year']                              : null;
		$month      = isset( $_GET['m'] )           ? $_GET['m']                                 : null;
		$day        = isset( $_GET['day'] )         ? $_GET['day']                               : null;
		$search     = isset( $_GET['s'] )           ? sanitize_text_field( $_GET['s'] )          : null;
		$start_date = isset( $_GET['start-date'] )  ? sanitize_text_field( $_GET['start-date'] ) : null;
		$end_date   = isset( $_GET['end-date'] )    ? sanitize_text_field( $_GET['end-date'] )   : $start_date;
		$gateway    = isset( $_GET['gateway'] )     ? sanitize_text_field( $_GET['gateway'] )    : null;

		/**
		 * Introduced as part of #6063. Allow a gateway to specified based on the context.
		 *
		 * @see   https://github.com/easydigitaldownloads/easy-digital-downloads/issues/6063
		 * @since 2.8.11
		 *
		 * @param string $gateway
		 */
		$gateway = apply_filters( 'edd_payments_table_search_gateway', $gateway );

		if ( ! empty( $search ) ) {
			$status = 'any'; // Force all payment statuses when searching
		}

		if ( $gateway === 'all' ) {
			$gateway = null;
		}

		$args = array(
			'number'     => $per_page,
			'offset'     => isset( $_GET['paged'] ) ? ( ( $_GET['paged'] * $per_page ) - $per_page ) : null,
			'orderby'    => $orderby,
			'order'      => $order,
			'user_id'    => $user,
			'customer_id'=> $customer,
			'status'     => $status,
			'gateway'    => $gateway
		);

		if ( is_string( $search ) && false !== strpos( $search, 'txn:' ) ) {
			$args['search_in_notes'] = true;
			$args['s'] = trim( str_replace( 'txn:', '', $args['s'] ) );
		}

		return edd_get_orders( array_filter( $args ) );
	}

	/**
	 * Setup the final data for the table.
	 *
	 * @since 1.4
	 */
	public function prepare_items() {
		wp_reset_vars( array( 'action', 'payment', 'orderby', 'order', 's' ) );

		$columns  = $this->get_columns();
		$hidden   = array(); // No hidden columns
		$sortable = $this->get_sortable_columns();
		$data     = $this->payments_data();
		$status   = isset( $_GET['status'] ) ? $_GET['status'] : 'any';

		$this->_column_headers = array( $columns, $hidden, $sortable );

		switch ( $status ) {
			case 'publish':
				$total_items = $this->complete_count;
				break;
			case 'pending':
				$total_items = $this->pending_count;
				break;
			case 'processing':
				$total_items = $this->processing_count;
				break;
			case 'refunded':
				$total_items = $this->refunded_count;
				break;
			case 'failed':
				$total_items = $this->failed_count;
				break;
			case 'revoked':
				$total_items = $this->revoked_count;
				break;
			case 'abandoned':
				$total_items = $this->abandoned_count;
				break;
			case 'any':
				$total_items = $this->total_count;
				break;
			default:
				// Retrieve the count of the non-default-EDD status
				$count       = wp_count_posts( 'edd_payment' );
				$total_items = $count->{$status};
		}

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page ),
			)
		);
	}
}
