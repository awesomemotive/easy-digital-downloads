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
	 * Total number of payments, grouped by status
	 *
	 * @var array
	 * @since 3.0
	 */
	public $counts = array();

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
			'singular' => __( 'Order',  'easy-digital-downloads' ),
			'plural'   => __( 'Orders', 'easy-digital-downloads' ),
			'ajax'     => false
		) );

		$this->base_url = add_query_arg( array(
			'post_type' => 'download',
			'page'      => 'edd-payment-history'
		), admin_url( 'edit.php' ) );

		$this->process_bulk_action();
		$this->get_payment_counts();
	}

	public function get_status() {
		return isset( $_GET['status'] )
			? sanitize_key( $_GET['status'] )
			: '';
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
		$mode       = isset( $_GET['mode']       ) ? sanitize_key( $_GET['mode']              ) : 'all';
		$status     = $this->get_status();
		$clear_url  = $this->base_url;

		// Filters
		$all_modes    = edd_get_payment_modes();
		$all_gateways = edd_get_payment_gateways();

		// No modes
		if ( empty( $all_modes ) ) {
			$modes = array();

		// Add "All" and pluck labels
		} else {
			$modes = array_merge( array(
				'all' => __( 'All modes', 'easy-digital-downloads' )
			), wp_list_pluck( $all_modes, 'admin_label' ) );
		}

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
				<?php if ( ! empty( $modes ) ) : ?>

					<span id="edd-mode-filter">
						<?php echo EDD()->html->select( array(
							'options'          => $modes,
							'name'             => 'mode',
							'id'               => 'mode',
							'selected'         => $mode,
							'show_option_all'  => false,
							'show_option_none' => false
						) ); ?>
					</span>

				<?php endif; ?>

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

					<span id="edd-gateway-filter">
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

				<span id="edd-after-core-filters">
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
	 * Message to be displayed when there are no items
	 *
	 * @since 3.0
	 * @access public
	 */
	public function no_items() {
		_e( 'No orders found.', 'easy-digital-downloads' );
	}

	/**
	 * Retrieve the view types.
	 *
	 * @since 1.4
     *
	 * @return array $views All the views available.
	 */
	public function get_views() {
		$current = isset( $_GET['status'] )
			? sanitize_key( $_GET['status'] )
			: '';

		$url   = remove_query_arg( array( 'status', 'paged' ) );
		$class = in_array( $current, array( '', 'all' ) ) ? ' class="current"' : '';
		$count = '&nbsp;<span class="count">(' . esc_attr( $this->counts['total'] ) . ')</span>';
		$label = __( 'All',  'easy-digital-downloads' ) . $count;
		$views = array(
			'all' => sprintf( '<a href="%s"%s>%s</a>', $url, $class, $label )
		);

		$counts = $this->counts;
		unset( $counts['total'] );

		// Loop through known statuses
		foreach ( $counts as $status => $count ) {
			$url              = add_query_arg( array( 'status' => $status, 'paged' => false ) );
			$class            = ( $current === $status ) ? ' class="current"' : '';
			$count            = '&nbsp;<span class="count">(' . $this->counts[ $status ] . ')</span>';
			$label            = edd_get_payment_status_label( $status ) . $count;
			$views[ $status ] = sprintf( '<a href="%s"%s>%s</a>', $url, $class, $label );
		}

		// Filter & return
		return (array) apply_filters( 'edd_payments_table_views', $views );
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
			'number'   => __( 'Number',   'easy-digital-downloads' ),
			'customer' => __( 'Customer', 'easy-digital-downloads' ),
			'gateway'  => __( 'Gateway',  'easy-digital-downloads' ),
			'amount'   => __( 'Amount',   'easy-digital-downloads' ),
			'date'     => __( 'Date',     'easy-digital-downloads' )
		) );
	}

	/**
	 * Retrieve the sortable columns.
	 *
	 * @since 1.4
	 *
     * @return array Array of all the sortable columns.
	 */
	public function get_sortable_columns() {
		return apply_filters( 'edd_payments_table_sortable_columns', array(
			'number'   => array( 'number',   true  ),
			'customer' => array( 'customer', false ),
			'gateway'  => array( 'gateway',  false ),
			'amount'   => array( 'amount',   false ),
			'date'     => array( 'date',     false )
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
		return 'number';
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
			case 'amount':
				$value = edd_currency_filter( edd_format_amount( $order->total ), $order->currency );
				break;
			case 'date':
				$value = edd_date_i18n( $order->date_created, 'M. d, Y' ) . '<br>' . edd_date_i18n( $order->date_created, 'H:i' );
				break;
			case 'gateway':
				$value = edd_get_gateway_admin_label( $order->gateway );
				break;
			default:
				$value = method_exists( $order, 'get_' . $column_name )
					? call_user_func( array( $order, 'get_' . $column_name ) )
					: '';
				break;
		}

		return apply_filters( 'edd_payments_table_column', $value, $order->id, $column_name );
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

		// Always include the "View" link
		$row_actions = array();

		// Add search term string back to base URL
		$search_terms = isset( $_GET['s'] )
			? trim( $_GET['s'] )
			: '';

		if ( ! empty( $search_terms ) ) {
			$this->base_url = add_query_arg( 's', $search_terms, $this->base_url );
		}

		$email = $order->email;

		// Resend
		if ( 'publish' === $order->status && ! empty( $email ) ) {
			$row_actions['email_links'] = '<a href="' . add_query_arg( array(
				'edd-action'  => 'email_links',
				'purchase_id' => $order->id
			), $this->base_url ) . '">' . __( 'Resend Receipt', 'easy-digital-downloads' ) . '</a>';
		}

		// This exists for backwards compatibility purposes.
		$payment     = edd_get_payment( $order->id );
		$row_actions = apply_filters( 'edd_payment_row_actions', $row_actions, $payment );

		if ( empty( $email ) ) {
			$email = __( '(unknown)', 'easy-digital-downloads' );
		}

		// Concatenate the results
		$value = $email . $this->row_actions( $row_actions );

		// Filter & return
		return apply_filters( 'edd_payments_table_column', $value, $order->id, 'email' );
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
			$order->id
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
	public function column_number( $order ) {
		$state  = '';
		$status = $this->get_status();

		// State
		if ( ( ! empty( $status ) && ( $status !== $order->status ) ) || ( empty( $status ) && ( $order->status !== 'publish' ) ) ) {
			$state = ' &mdash; ' . edd_get_payment_status_label( $order->status );
		}

		// View URL
		$view_url = add_query_arg( array(
			'post_type' => 'download',
			'page'      => 'edd-payment-history',
			'view'      => 'view-order-details',
			'id'        => $order->id
		), admin_url( 'edit.php' ) );

		// Default row actions
		$row_actions = array(
			'view' => '<a href="' . esc_url( $view_url ) . '">' . esc_html__( 'Edit', 'easy-digital-downloads' ) . '</a>',
		);

		// Refund
		if ( 'publish' === $order->status ) {
			$refund_url = add_query_arg( array(), admin_url( 'edit.php' ) );
			$row_actions['refund'] = '<a href="' . esc_url( $refund_url ) . '">' . esc_html__( 'Refund', 'easy-digital-downloads' ) . '</a>';
		}

		// Keep Delete at the end
		$delete_url = wp_nonce_url( add_query_arg( array(
			'edd-action'  => 'delete_payment',
			'purchase_id' => $order->id
		), $this->base_url ), 'edd_payment_nonce' );
		$row_actions['delete'] = '<a href="' . esc_url( $delete_url ) . '">' . esc_html__( 'Delete', 'easy-digital-downloads' ) . '</a>';

		// Row actions
		$actions = $this->row_actions( $row_actions );

		// Primary link
		$link = '<strong><a class="row-title" href="' . esc_url( $view_url ) . '">' . esc_html( $order->number ) . '</a>' . esc_html( $state ) . '</strong>';

		// Concatenate & return the results
		return $link . $actions;
	}

	/**
	 * Render the Customer column.
	 *
	 * @since 2.4.3
     * @since 3.0 Updated to use the new EDD\Orders\Order class.
     *
     * @param EDD\Orders\Order $order Order object.
	 * @return string Data shown in the Customer column.
	 */
	public function column_customer( $order ) {
		$customer_id = $order->customer_id;
		$customer    = edd_get_customer( $customer_id );

		// Actions if exists
		if ( ! empty( $customer ) ) {

			// Use customer name, if exists
			$name = ! empty( $customer->name )
				? $customer->name
				: __( 'No Name', 'easy-digital-downloads' );

			// Link to View Customer
			$url = add_query_arg( array(
				'post_type' => 'download',
				'page'      => 'edd-customers',
				'view'      => 'overview',
				'id'        => $customer_id
			), admin_url( 'edit.php' ) );

			$name = '<a href="' . esc_url( $url ) . '">' . $name . '</a>';
		} else {
			$name = '&mdash;';
		}

		return $name;
	}

	/**
	 * Retrieve the bulk actions
	 *
	 * @since 1.4
	 *
     * @return array $actions Bulk actions.
	 */
	public function get_bulk_actions() {
		return apply_filters( 'edd_payments_table_bulk_actions', array(
			'set-status-publish'     => __( 'Mark Completed',   'easy-digital-downloads' ),
			'set-status-pending'     => __( 'Mark Pending',     'easy-digital-downloads' ),
			'set-status-processing'  => __( 'Mark Processing',  'easy-digital-downloads' ),
			'set-status-refunded'    => __( 'Mark Refunded',    'easy-digital-downloads' ),
			'set-status-revoked'     => __( 'Mark Revoked',     'easy-digital-downloads' ),
			'set-status-failed'      => __( 'Mark Failed',      'easy-digital-downloads' ),
			'set-status-abandoned'   => __( 'Mark Abandoned',   'easy-digital-downloads' ),
			'set-status-preapproval' => __( 'Mark Preapproved', 'easy-digital-downloads' ),
			'set-status-cancelled'   => __( 'Mark Cancelled',   'easy-digital-downloads' ),
			'resend-receipt'         => __( 'Resend  Receipts', 'easy-digital-downloads' ),
			'delete'                 => __( 'Delete',           'easy-digital-downloads' )
		) );
	}

	/**
	 * Process the bulk actions.
	 *
	 * @since 1.4
	 */
	public function process_bulk_action() {
		$action = $this->current_action();
		$ids    = isset( $_GET['payment'] )
			? $_GET['payment']
			: false;

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		if ( empty( $action ) ) {
			return;
		}

		$ids = wp_parse_id_list( $ids );

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

		if ( isset( $_GET['user'] ) ) {
			$args['user'] = urldecode( $_GET['user'] );
		} elseif ( isset( $_GET['customer'] ) ) {
			$args['customer'] = absint( $_GET['customer'] );
		} elseif ( isset( $_GET['s'] ) ) {
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
			$args['gateway'] = sanitize_key( $_GET['gateway'] );
		}

		if ( ! empty( $_GET['mode'] ) && $_GET['mode'] !== 'all' ) {
			$args['mode'] = sanitize_key( $_GET['mode'] );
		}

		$this->counts = edd_get_order_counts( $args );
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
		$paged      = isset( $_GET['paged'] )      ? ( absint( $_GET['paged'] ) * $per_page ) - $per_page : null;
		$user       = isset( $_GET['user'] )       ? absint( $_GET['user'] )                    : null;
		$customer   = isset( $_GET['customer'] )   ? absint( $_GET['customer'] )                : null;
		$orderby    = isset( $_GET['orderby'] )    ? sanitize_key( $_GET['orderby'] )           : 'id';
		$order      = isset( $_GET['order'] )      ? sanitize_key( $_GET['order'] )             : 'DESC';
		$status     = isset( $_GET['status'] )     ? sanitize_key( $_GET['status'] )            : edd_get_payment_status_keys();
		$search     = isset( $_GET['s'] )          ? sanitize_text_field( $_GET['s'] )          : null;
		$start_date = isset( $_GET['start-date'] ) ? sanitize_text_field( $_GET['start-date'] ) : null;
		$end_date   = isset( $_GET['end-date'] )   ? sanitize_text_field( $_GET['end-date'] )   : $start_date;
		$gateway    = isset( $_GET['gateway'] )    ? sanitize_text_field( $_GET['gateway'] )    : null;
		$mode       = isset( $_GET['mode'] )       ? sanitize_text_field( $_GET['mode'] )       : null;

		/**
		 * Introduced as part of #6063. Allow a gateway to specified based on the context.
		 *
		 * @see   https://github.com/easydigitaldownloads/easy-digital-downloads/issues/6063
		 * @since 2.8.11
		 *
		 * @param string $gateway
		 */
		$gateway = apply_filters( 'edd_payments_table_search_gateway', $gateway );

		if ( $gateway === 'all' ) {
			$gateway = null;
		}

		if ( $mode === 'all' ) {
			$mode = null;
		}

		$args = array(
			'number'     => $per_page,
			'offset'     => $paged,
			'orderby'    => $orderby,
			'order'      => $order,
			'user_id'    => $user,
			'customer_id'=> $customer,
			'status'     => $status,
			'gateway'    => $gateway,
			'mode'       => $mode,
			'search'     => $search
		);

		// Search
		if ( is_string( $search ) && ( false !== strpos( $search, 'txn:' ) ) ) {
			$args['search_in_notes'] = true;
			$args['search']          = trim( str_replace( 'txn:', '', $args['search'] ) );
		}

		// Date query
		if ( ! empty( $start_date ) || ! empty( $end_date ) ) {

			// start AND end
			$args['date_query'] = array(
				'relation'  => 'AND'
			);

			// Start (of day)
			if ( ! empty( $start_date ) ) {
				$args['date_query'][] = array(
					'column' => 'date_created',
					'after'  => date( 'Y-m-d 00:00:00', strtotime( $start_date ) )
				);
			}

			// End (of day)
			if ( ! empty( $end_date ) ) {
				$args['date_query'][] = array(
					'column' => 'date_created',
					'before'  => date( 'Y-m-d 23:59:59', strtotime( $end_date ) )
				);
			}
		}

		// No empties
		$r = wp_parse_args( array_filter( $args ) );

		return edd_get_orders( $r );
	}

	/**
	 * Setup the final data for the table.
	 *
	 * @since 1.4
	 */
	public function prepare_items() {
		wp_reset_vars( array( 'action', 'payment', 'orderby', 'order', 's' ) );

		$hidden      = array(); // No hidden columns
		$columns     = $this->get_columns();
		$sortable    = $this->get_sortable_columns();
		$this->items = $this->payments_data();
		$status      = isset( $_GET['status'] )
			? sanitize_key( $_GET['status'] )
			: 'total';

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->set_pagination_args( array(
			'total_items' => $this->counts[ $status ],
			'per_page'    => $this->per_page,
			'total_pages' => ceil( $this->counts[ $status ] / $this->per_page )
		) );
	}
}
