<?php
/**
 * Order History Table.
 *
 * @package     EDD
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Admin\List_Table;

/**
 * EDD_Payment_History_Table Class
 *
 * Displays a list of orders on the 'Orders' page.
 *
 * @since 1.4
 * @since 3.0 Updated to use new query methods.
 *            Updated to use new nomenclature.
 */
class EDD_Payment_History_Table extends List_Table {

	/**
	 * Determines the current Order type.
	 *
	 * @since 3.0
	 *
	 * @var string $type Order type
	 */
	protected $type;

	/**
	 * URL of this page.
	 *
	 * @var string
	 * @since 1.4.1
	 */
	public $base_url;

	/**
	 * Constructor.
	 *
	 * @see WP_List_Table::__construct()
	 *
	 * @since 1.4
	 */
	public function __construct() {

		// Set parent defaults
		parent::__construct( array(
			'singular' => __( 'Order',  'easy-digital-downloads' ),
			'plural'   => __( 'Orders', 'easy-digital-downloads' ),
			'ajax'     => false
		) );

		// Use registered types
		$types = array_keys( edd_get_order_types() );
		if ( ! empty( $_GET['order_type'] ) && in_array( $_GET['order_type'], $types, true ) ) {
			$this->type = sanitize_key( $_GET['order_type'] );

		// Default to 'sale' if type is unrecognized
		} else {
			$this->type = 'sale';
		}

		$this->set_base_url();
		$this->filter_bar_hooks();
		$this->get_payment_counts();
	}

	/**
	 * Set the base URL.
	 *
	 * This retains the current order-type, or 'sale' by default.
	 *
	 * @since 3.0
	 */
	private function set_base_url() {
		// Carry the type over to the base URL
		$this->base_url = edd_get_admin_url( array(
			'page'       => 'edd-payment-history',
			'order_type' => $this->type
		) );
	}

	/**
	 * Hook in filter bar actions
	 *
	 * @since 3.0
	 */
	private function filter_bar_hooks() {
		add_action( 'edd_admin_filter_bar_orders',       array( $this, 'filter_bar_items'     ) );
		add_action( 'edd_after_admin_filter_bar_orders', array( $this, 'filter_bar_searchbox' ) );
	}

	/**
	 * Display advanced filters.
	 *
	 * @since 1.4
	 * @since 3.0 Add a filter for modes.
	 *            Display 'Advanced Filters'
	 */
	public function advanced_filters() {
		// Hide when viewing Refunds.
		if ( 'refund' === $this->type ) {
			return;
		}

		edd_admin_filter_bar( 'orders' );
	}

	/**
	 * Output filter bar items
	 *
	 * @since 3.0
	 */
	public function filter_bar_items() {

		// Get values
		$start_date                = isset( $_GET['start-date'] ) ? sanitize_text_field( $_GET['start-date'] ) : null;
		$end_date                  = isset( $_GET['end-date'] ) ? sanitize_text_field( $_GET['end-date'] ) : null;
		$gateway                   = isset( $_GET['gateway'] ) ? sanitize_key( $_GET['gateway'] ) : 'all';
		$mode                      = isset( $_GET['mode'] ) ? sanitize_key( $_GET['mode'] ) : 'all';
		$order_total_filter_type   = isset( $_GET['order-amount-filter-type'] ) ? sanitize_text_field( $_GET['order-amount-filter-type'] ) : false;
		$order_total_filter_amount = isset( $_GET['order-amount-filter-value'] ) ? sanitize_text_field( $_GET['order-amount-filter-value'] ) : '';
		$country                   = isset( $_GET['order-country-filter-value'] ) ? sanitize_text_field( $_GET['order-country-filter-value'] ) : '';
		$region                    = isset( $_GET['order-region-filter-value'] ) ? sanitize_text_field( $_GET['order-region-filter-value'] ) : '';

		$status     = $this->get_status();
		$clear_url  = $this->base_url;

		// Filters
		$all_modes    = edd_get_payment_modes();
		$all_gateways = edd_get_payment_gateways();

		// Advanced filters
		$advanced_filters_applied = (bool) ! empty( $order_total_filter_amount ) || ! empty( $country ) || ! empty( $region );
		$advanced_filters_applied = apply_filters( 'edd_orders_table_advanced_filters_applied', $advanced_filters_applied );

		$maybe_show_filters = ( true === $advanced_filters_applied )
			? 'open'
			: '';

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
		$gateways = apply_filters( 'edd_payments_table_gateways', $gateways );

		// Output the items
		if ( ! empty( $modes ) ) : ?>

			<span id="edd-mode-filter">
				<?php echo EDD()->html->select( array(
					'options'          => $modes,
					'name'             => 'mode',
					'id'               => 'mode',
					'selected'         => $mode,
					'chosen'           => true,
					'show_option_all'  => false,
					'show_option_none' => false
				) ); ?>
			</span>

		<?php endif; ?>

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

		?></span><?php

		if ( ! empty( $gateways ) ) : ?>

			<span id="edd-gateway-filter">
				<?php echo EDD()->html->select( array(
					'options'          => $gateways,
					'name'             => 'gateway',
					'id'               => 'gateway',
					'selected'         => $gateway,
					'chosen'           => true,
					'show_option_all'  => false,
					'show_option_none' => false
				) ); ?>
			</span>

		<?php endif; ?>

		<span id="edd-advanced-filters" class="<?php echo esc_attr( $maybe_show_filters ); ?>">
			<input type="button" class="edd-advanced-filters-button button-secondary" value="<?php esc_html_e( 'More', 'easy-digital-downloads' ); ?>"/>

			<div class="inside">
				<fieldset>
					<legend for="order-amount-filter-type"><?php esc_html_e( 'Total is', 'easy-digital-downloads' ); ?></legend>
					<?php
					$options = array(
						'=' => __( 'equal to', 'easy-digital-downloads' ),
						'>' => __( 'greater than', 'easy-digital-downloads' ),
						'<' => __( 'less than', 'easy-digital-downloads' ),
					);

					echo EDD()->html->select( array(
						'id'               => 'order-amount-filter-type',
						'name'             => 'order-amount-filter-type',
						'options'          => $options,
						'selected'         => $order_total_filter_type,
						'show_option_all'  => false,
						'show_option_none' => false,
					) );
					?>

					<input type="number" name="order-amount-filter-value" min="0" step="0.01" value="<?php echo esc_attr( $order_total_filter_amount ); ?>"/>
				</fieldset>

				<fieldset>
					<legend><?php esc_html_e( 'Country & Region', 'easy-digital-downloads' ); ?></legend>
					<?php
					echo EDD()->html->select( array(
						'name'             => 'order-country-filter-value',
						'class'            => 'edd_countries_filter',
						'options'          => edd_get_country_list(),
						'chosen'           => true,
						'selected'         => $country,
						'show_option_none' => false,
						'placeholder'      => __( 'Choose a Country', 'easy-digital-downloads' ),
						'show_option_all'  => __( 'All Countries', 'easy-digital-downloads' ),
						'data'             => array(
							'nonce' => wp_create_nonce( 'edd-country-field-nonce' )
						)
					) );
					echo EDD()->html->select( array(
						'name'             => 'order-region-filter-value',
						'class'            => 'edd_regions_filter',
						'options'          => edd_get_shop_states( $country ),
						'chosen'           => true,
						'selected'         => $region,
						'show_option_none' => false,
						'placeholder'      => __( 'Choose a Region', 'easy-digital-downloads' ),
						'show_option_all'  => __( 'All Regions', 'easy-digital-downloads' ),
					) );
				?>
				</fieldset>

				<?php

				// Third party plugin support
				if ( has_action( 'edd_payment_advanced_filters_after_fields' ) ) : ?>

					<fieldset class="edd-add-on-filters">
						<legend><?php esc_html_e( 'Extras', 'easy-digital-downloads' ); ?></legend>

						<?php do_action( 'edd_payment_advanced_filters_after_fields' ); ?>

					</fieldset>

				<?php endif; ?>
			</div>
		</span>

		<span id="edd-after-core-filters">
			<input type="submit" class="button-secondary" value="<?php esc_html_e( 'Filter', 'easy-digital-downloads' ); ?>"/>

			<?php if ( ! empty( $start_date ) || ! empty( $end_date ) || ! empty( $order_total_filter_type ) || ( 'all' !== $gateway ) ) : ?>
				<a href="<?php echo esc_url( $clear_url ); ?>" class="button-secondary">
					<?php esc_html_e( 'Clear', 'easy-digital-downloads' ); ?>
				</a>
			<?php endif; ?>
		</span>

		<?php if ( ! empty( $status ) ) : ?>
			<input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>"/>
		<?php endif;
	}

	/**
	 * Output the filter bar searchbox
	 *
	 * @since 3.0
	 */
	public function filter_bar_searchbox() {
		do_action( 'edd_payment_advanced_filters_row' );

		$this->search_box( esc_html__( 'Search', 'easy-digital-downloads' ), 'edd-payments' );
	}

	/**
	 * Show the search field.
	 *
	 * @since 1.4
	 *
	 * @param string $text     Label for the search box.
	 * @param string $input_id ID of the search box.
	 */
	public function search_box( $text, $input_id ) {

		// Bail if no customers and no search.
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
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" placeholder="<?php esc_html_e( 'Search orders...', 'easy-digital-downloads' ); ?>" value="<?php _admin_search_query(); ?>" />
		</p>

		<?php
	}

	/**
	 * Message to be displayed when there are no items.
	 *
	 * @since 3.0
	 */
	public function no_items() {
		esc_html_e( 'No orders found.', 'easy-digital-downloads' );
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @since 1.4
	 *
	 * @return array $columns Array of all the list table columns.
	 */
	public function get_columns() {
		$columns = array(
			'cb'       => '<input type="checkbox" />', // Render a checkbox instead of text
			'number'   => __( 'Number',    'easy-digital-downloads' ),
			'customer' => __( 'Customer',  'easy-digital-downloads' ),
			'gateway'  => __( 'Gateway',   'easy-digital-downloads' ),
			'amount'   => __( 'Total',    'easy-digital-downloads' ),
			'date'     => __( 'Date', 'easy-digital-downloads' ),
			'status'   => __( 'Status', 'easy-digital-downloads' ),
		);

		if ( 'refund' === $this->type ) {
			unset( $columns['status'] );
		}

		/**
		 * Filters the columns for Orders and Refunds table.
		 *
		 * @since unknown
		 *
		 * @param array $columns Table columns.
		 */
		$columns = apply_filters( 'edd_payments_table_columns', $columns );

		return $columns;
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
			'number'   => array( 'id',           true  ),
			'status'   => array( 'status',       false ),
			'customer' => array( 'customer_id',  false ),
			'gateway'  => array( 'gateway',      false ),
			'amount'   => array( 'total',        false ),
			'date'     => array( 'date_created', false )
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
		$timezone_abbreviation = edd_get_timezone_abbr();
		switch ( $column_name ) {
			case 'amount':
				$value = edd_currency_filter( edd_format_amount( $order->total ), $order->currency );
				break;
			case 'date':
				$value = '<time datetime="' . esc_attr( EDD()->utils->date( $order->date_created, null, true )->toDateTimeString() ) . '">' . edd_date_i18n( $order->date_created, 'M. d, Y' ) . '<br>' . edd_date_i18n( strtotime( $order->date_created ), 'H:i' ) . ' ' . $timezone_abbreviation . '</time>';
				break;
			case 'gateway':
				$value = edd_get_gateway_admin_label( $order->gateway );

				if ( empty( $value ) ) {
					$value = '&mdash;';
				}

				break;
			case 'status':
				$value = edd_get_order_status_badge( $order->status );
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
			'order',
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
		$status = $this->get_status();

		// View URL
		$view_url = edd_get_admin_url( array(
			'page' => 'edd-payment-history',
			'view' => 'sale' === $order->type
				? 'view-order-details'
				: 'view-refund-details',
			'id'   => $order->id,
		) );

		// Default row actions
		$row_actions = array(
			'view' => '<a href="' . esc_url( $view_url ) . '">' . esc_html__( 'Edit', 'easy-digital-downloads' ) . '</a>',
		);

		// Resend Receipt
		if ( 'sale' === $this->type && 'complete' === $order->status && ! empty( $order->email ) ) {
			$row_actions['email_links'] = '<a href="' . esc_url( add_query_arg( array(
					'edd-action'  => 'email_links',
					'purchase_id' => $order->id
				), $this->base_url ) ) . '">' . __( 'Resend Receipt', 'easy-digital-downloads' ) . '</a>';
		}

		// Keep Delete at the end
		if ( edd_is_order_trashable( $order->id ) ) {
			$trash_url = wp_nonce_url( add_query_arg( array(
				'edd-action'  => 'trash_order',
				'purchase_id' => $order->id,
			), $this->base_url ), 'edd_payment_nonce' );
			$row_actions['trash'] = '<a href="' . esc_url( $trash_url ) . '">' . esc_html__( 'Trash', 'easy-digital-downloads' ) . '</a>';
		} elseif ( edd_is_order_restorable( $order->id ) ) {
			$restore_url = wp_nonce_url( add_query_arg( array(
				'edd-action'  => 'restore_order',
				'purchase_id' => $order->id,
			), $this->base_url ), 'edd_payment_nonce' );
			$row_actions['restore'] = '<a href="' . esc_url( $restore_url ) . '">' . esc_html__( 'Restore', 'easy-digital-downloads' ) . '</a>';

			$delete_url = wp_nonce_url( add_query_arg( array(
				'edd-action'  => 'delete_order',
				'purchase_id' => $order->id,
			), $this->base_url ), 'edd_payment_nonce' );
			$row_actions['delete'] = '<a href="' . esc_url( $delete_url ) . '">' . esc_html__( 'Delete Permanently', 'easy-digital-downloads' ) . '</a>';

			unset( $row_actions['view'] );
		}

		if ( has_filter( 'edd_payment_row_actions' ) ) {
			$payment = edd_get_payment( $order->id );

			/**
			 * Filters the row actions.
			 *
			 * @deprecated 3.0
			 *
			 * @param array             $row_actions
			 * @param EDD_Payment|false $payment
			 */
			$row_actions = apply_filters_deprecated( 'edd_payment_row_actions', array( $row_actions, $payment ), '3.0', 'edd_order_row_actions' );
		}

		/**
		 * Filters the row actions.
		 *
		 * @param array            $row_actions Array of row actions.
		 * @param EDD\Orders\Order $order       Order object.
		 *
		 * @since 3.0
		 */
		$row_actions = apply_filters( 'edd_order_row_actions', $row_actions, $order );

		// Row actions
		$actions = $this->row_actions( $row_actions );

		// Primary link
		$order_number = 'sale' === $order->type ? $order->get_number() : $order->order_number;
		$link         = edd_is_order_restorable( $order->id ) ? '<span class="row-title">' . esc_html( $order_number ) . '</span>' : '<a class="row-title" href="' . esc_url( $view_url ) . '">' . esc_html( $order_number ) . '</a>';

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
			$url = edd_get_admin_url( array(
				'page' => 'edd-customers',
				'view' => 'overview',
				'id'   => $customer_id,
			) );

			$name = '<a href="' . esc_url( $url ) . '">' . $name . '</a>';
		} else {
			$name = '&mdash;';
		}

		/**
		 * Filters the output of the Email column in the Payments table.
		 *
		 * @since 1.4
		 * @since 3.0 Run manually inside of the `customer` column for backwards compatibility.
		 *
		 * @param string $name Customer name.
		 * @param int    $order_id ID of the Payment/Order.
		 * @param string $column_name Name of the current column (email).
		 */
		$name = apply_filters( 'edd_payments_table_column', $name, $order->id, 'email' );

		return $name;
	}

	/**
	 * Retrieve the bulk actions.
	 *
	 * @since 1.4
	 *
	 * @return array $actions Bulk actions.
	 */
	public function get_bulk_actions() {
		if ( 'refund' !== $this->type ) {
			$action = array(
				'set-status-complete'     => __( 'Mark Completed',   'easy-digital-downloads' ),
				'set-status-pending'     => __( 'Mark Pending',     'easy-digital-downloads' ),
				'set-status-processing'  => __( 'Mark Processing',  'easy-digital-downloads' ),
				'set-status-refunded'    => __( 'Mark Refunded',    'easy-digital-downloads' ),
				'set-status-revoked'     => __( 'Mark Revoked',     'easy-digital-downloads' ),
				'set-status-failed'      => __( 'Mark Failed',      'easy-digital-downloads' ),
				'set-status-abandoned'   => __( 'Mark Abandoned',   'easy-digital-downloads' ),
				'set-status-preapproval' => __( 'Mark Preapproved', 'easy-digital-downloads' ),
				'set-status-cancelled'   => __( 'Mark Cancelled',   'easy-digital-downloads' ),
				'resend-receipt'         => __( 'Resend Receipts', 'easy-digital-downloads' ),
			);
		} else {
			$action = array();
		}

		if ( 'trash' === $this->get_status() ) {
			$action = array(
				'restore' => __( 'Restore', 'easy-digital-downloads' ),
			);
		} else {
			$action['trash'] = __( 'Move to Trash', 'easy-digital-downloads' );
		}

		return apply_filters( 'edd_payments_table_bulk_actions', $action );
	}

	/**
	 * Process the bulk actions.
	 *
	 * @since 1.4
	 * @since 3.0 Updated to display _doing_it_wrong().
	 *
	 * @see edd_orders_list_table_process_bulk_actions()
	 */
	public function process_bulk_action() {
		_doing_it_wrong( __FUNCTION__, 'Orders list table bulk actions are now handled by edd_orders_list_table_process_bulk_actions(). Please do not call this method directly.', 'EDD 3.0' );
	}

	/**
	 * Retrieve the payment counts.
	 *
	 * @since 1.4
	 */
	public function get_payment_counts() {

		// Get the args (without pagination)
		$args = $this->parse_args( false );

		unset( $args['status'], $args['status__not_in'], $args['status__in'] );

		// Get order counts by type
		$this->counts = edd_get_order_counts( $args );
	}

	/**
	 * Retrieves all the data for all the orders.
	 *
	 * @since 1.4
	 * @deprecated 3.0 Use get_data()
	 *
	 * @return array $payment_data Array of all the data for the orders.
	 */
	public function payments_data() {
		_edd_deprecated_function( __METHOD__, '3.0', 'EDD_Payment_History_Table::get_data()' );

		return $this->get_data();
	}

	/**
	 * Retrieves all of the orders data based on current filters.
	 *
	 * @since 3.0
	 *
	 * @return array Orders table data.
	 */
	public function get_data() {

		// Parse args (with pagination)
		$this->args = $this->parse_args( true );

		// Force EDD\Orders\Order objects to be returned
		$this->args['output'] = 'orders';

		if ( empty( $this->args['status'] ) ) {
			$this->args['status__not_in'] = array( 'trash' );
		}

		// Get data
		$items = edd_get_orders( $this->args );

		// Get customer IDs and count from payments
		$customer_ids = array_unique( wp_list_pluck( $items, 'customer_id' ) );
		$cust_count   = count( $customer_ids );

		// Maybe prime customer objects (if more than number of queries)
		if ( $cust_count > 1 ) {
			edd_get_customers( array(
				'id__in'        => $customer_ids,
				'no_found_rows' => true,
				'number'        => $cust_count
			) );
		}

		// Return items
		return $items;
	}

	/**
	 * Retrieves the Payments table views.
	 *
	 * @since 1.4
	 *
	 * @return array $views Available views.
	 */
	public function get_views() {
		$views = parent::get_views();

		/**
		 * Filters the Payment table's views.
		 *
		 * @since 1.4
		 *
		 * @param array $views Payment table's views.
		 */
		$views = apply_filters( 'edd_payments_table_views', $views );

		return $views;
	}

	/**
	 * Builds an array of arguments for getting orders for the list table, counts, and pagination.
	 *
	 * @since 3.0
	 *
	 * @param bool $paginate Whether to add pagination arguments
	 *
	 * @return array Array of arguments to use for querying orders.
	 */
	private function parse_args( $paginate = true ) {
		$status     = $this->get_status();
		$user       = isset( $_GET['user'] )       ? absint( $_GET['user'] )                    : null;
		$customer   = isset( $_GET['customer'] )   ? absint( $_GET['customer'] )                : null;
		$search     = isset( $_GET['s'] )          ? sanitize_text_field( $_GET['s'] )          : null;
		$start_date = isset( $_GET['start-date'] ) ? sanitize_text_field( $_GET['start-date'] ) : null;
		$end_date   = isset( $_GET['end-date'] )   ? sanitize_text_field( $_GET['end-date'] )   : $start_date;
		$gateway    = isset( $_GET['gateway'] )    ? sanitize_text_field( $_GET['gateway'] )    : null;
		$mode       = isset( $_GET['mode'] )       ? sanitize_text_field( $_GET['mode'] )       : null;
		$type       = isset( $_GET['order_type'] ) ? sanitize_text_field( $_GET['order_type'] ) : 'sale';

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
			'user'        => $user,
			'customer_id' => $customer,
			'status'      => $status,
			'gateway'     => $gateway,
			'mode'        => $mode,
			'type'        => $type,
			'search'      => $search,
		);

		// Search
		if ( is_string( $search ) && ( false !== strpos( $search, 'txn:' ) ) ) {
			$args['search_in_notes'] = true;
			$args['search']          = trim( str_replace( 'txn:', '', $args['s'] ) );
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

		// Maybe filter by order amount.
		if ( isset( $_GET['order-amount-filter-type'] ) && isset( $_GET['order-amount-filter-value'] ) ) {
			if ( ! is_null( $_GET['order-amount-filter-value'] ) && '' !== $_GET['order-amount-filter-value'] ) {
				$filter_type   = sanitize_text_field( $_GET['order-amount-filter-type'] );
				$filter_amount = floatval( sanitize_text_field( $_GET['order-amount-filter-value'] ) );

				$args['compare'] = array(
					array(
						'key'     => 'total',
						'value'   => $filter_amount,
						'compare' => $filter_type,
					),
				);
			}
		}

		// Maybe filter by country.
		if ( isset( $_GET['order-country-filter-value'] ) ) {
			$country = ! empty( $_GET['order-country-filter-value'] )
				? sanitize_text_field( $_GET['order-country-filter-value'] )
				: '';

			$args['country'] = $country;
		}

		// Maybe filter by region.
		if ( isset( $_GET['order-region-filter-value'] ) ) {
			$region = ! empty( $_GET['order-region-filter-value'] )
				? sanitize_text_field( $_GET['order-region-filter-value'] )
				: '';

			$args['region'] = $region;
		}

		// Return args, possibly with pagination
		return ( true === $paginate )
			? $this->parse_pagination_args( $args )
			: $args;
	}

	/**
	 * Setup the final data for the table.
	 *
	 * @since 1.4
	 */
	public function prepare_items() {
		wp_reset_vars( array( 'action', 'order', 'orderby', 'order', 's' ) );

		$hidden      = array(); // No hidden columns
		$columns     = $this->get_columns();
		$sortable    = $this->get_sortable_columns();
		$status      = $this->get_status( 'total' );
		$this->items = $this->get_data();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		if ( empty( $this->counts[ $status ] ) ) {
			return;
		}

		$this->set_pagination_args( array(
			'total_pages' => ceil( $this->counts[ $status ] / $this->per_page ),
			'total_items' => $this->counts[ $status ],
			'per_page'    => $this->per_page,
		) );
	}
}
